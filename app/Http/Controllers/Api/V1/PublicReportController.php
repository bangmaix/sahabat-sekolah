<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\AConnect;
use App\Helpers\ExifHelper;
use App\Helpers\SlaCalculator;
use App\Http\Controllers\Controller;
use App\Mail\NewReportCounselorMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PublicReportController extends Controller
{
    /**
     * Get list of schools for reporting options.
     */
    public function schools(): JsonResponse
    {
        $aConnect = new AConnect;
        $schools = $aConnect->getSekolahList();

        if ($schools->isEmpty()) {
            $schools = DB::table('schools')
                ->select('npsn', 'nama_sekolah', 'jenjang', 'alamat_jalan', 'status_sekolah')
                ->orderBy('nama_sekolah')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $schools,
        ]);
    }

    /**
     * Get bullying classification categories and subcategories.
     */
    public function categories(): JsonResponse
    {
        $categories = DB::table('bullying_categories')
            ->where('status', true)
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                $category->subcategories = DB::table('bullying_subcategories')
                    ->where('category_id', $category->id)
                    ->where('status', true)
                    ->orderBy('name')
                    ->get();

                return $category;
            });

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Submit a new bullying report from the mobile app (anonymous, confidential, or identified).
     */
    public function createReport(Request $request): JsonResponse
    {
        $data = $request->validate([
            'school_npsn' => ['required', 'string', 'max:20'],
            'reporter_role' => ['required', 'in:VICTIM,WITNESS,PARENT,TEACHER,STUDENT,OTHER'],
            'identity_mode' => ['required', 'in:ANONYMOUS,CONFIDENTIAL,IDENTIFIED'],
            'reporter_name' => ['nullable', 'required_unless:identity_mode,ANONYMOUS', 'string', 'max:255'],
            'reporter_contact' => ['nullable', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:80'],
            'subcategory' => ['nullable', 'string', 'max:120'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'evidences' => ['nullable', 'array', 'max:5'],
            'evidences.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,mp4,mov'],
        ], [
            'school_npsn.required' => 'Pilih sekolah tempat kejadian.',
            'reporter_role.required' => 'Pilih peran pelapor.',
            'identity_mode.required' => 'Pilih mode identitas laporan.',
            'reporter_name.required_unless' => 'Nama pelapor wajib diisi jika mode identitas Rahasia atau Terbuka.',
            'category.required' => 'Pilih kategori kejadian perundungan.',
            'description.required' => 'Ceritakan kronologi kejadian perundungan.',
            'description.min' => 'Cerita kejadian minimal 10 karakter agar informasi laporan cukup jelas.',
        ]);

        $result = DB::transaction(function () use ($data, $request) {
            $number = 'SS-'.now()->format('Y').'-'.str_pad((string) ((DB::table('reports')->max('id') ?? 0) + 1), 6, '0', STR_PAD_LEFT);
            $now = now();

            $reportData = [
                'school_npsn' => $data['school_npsn'],
                'report_number' => $number,
                'reporter_role' => $data['reporter_role'],
                'identity_mode' => $data['identity_mode'],
                'category' => $data['category'],
                'subcategory' => $data['subcategory'] ?? null,
                'description' => $data['description'],
                'submitted_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $reportId = DB::table('reports')->insertGetId($reportData);

            $risk = in_array($data['category'], ['Cyberbullying', 'Fisik', 'Perundungan Bernuansa Seksual'], true) ? 'HIGH' : 'MEDIUM';

            $caseId = DB::table('cases')->insertGetId([
                'school_npsn' => $data['school_npsn'],
                'report_id' => $reportId,
                'case_number' => $number,
                'category' => $data['category'],
                'risk_level' => $risk,
                'status' => 'PENDING_RESPONSE',
                'opened_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $config = DB::table('sla_configurations')->where('risk_level', $risk)->first();
            $responseHours = $config ? $config->response_time_hours : 24;

            DB::table('case_slas')->insert([
                'case_id' => $caseId,
                'status' => 'ON_TIME',
                'response_deadline' => SlaCalculator::calculateDeadline($now, $responseHours, $risk),
                'resolution_deadline' => null,
                'resolution_status' => 'ON_TRACK',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($data['identity_mode'] !== 'ANONYMOUS') {
                DB::table('reporter_identities')->insert([
                    'report_id' => $reportId,
                    'full_name' => $data['reporter_name'] ?? null,
                    'contact' => $data['reporter_contact'] ?? null,
                    'access_level' => $data['identity_mode'] === 'IDENTIFIED' ? 'CASE_FULL' : 'CASE_RESTRICTED',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $uploadedFiles = [];
            if ($request->hasFile('evidences')) {
                foreach ($request->file('evidences') as $file) {
                    $path = ExifHelper::stripAndStore($file, 'evidences');
                    $evidenceId = DB::table('case_evidences')->insertGetId([
                        'case_id' => $caseId,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_by' => $data['reporter_name'] ?? 'Pelapor',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $uploadedFiles[] = [
                        'id' => $evidenceId,
                        'file_name' => $file->getClientOriginalName(),
                    ];
                }
            }

            if (function_exists('notifyCounselor')) {
                notifyCounselor('NEW_REPORT', 'Laporan baru masuk', $number.' menunggu respons awal dalam 1 × 24 jam.', $number, 'HIGH');
            }

            $anonymousToken = null;
            if ($data['identity_mode'] === 'ANONYMOUS') {
                $anonymousToken = Str::upper(Str::random(12));
                DB::table('anonymous_report_tokens')->insert([
                    'report_id' => $reportId,
                    'token_hash' => hash('sha256', $anonymousToken),
                    'expires_at' => $now->copy()->addMonths(6),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return [
                'case_number' => $number,
                'report_number' => $number,
                'identity_mode' => $data['identity_mode'],
                'category' => $data['category'],
                'risk_level' => $risk,
                'status' => 'PENDING_RESPONSE',
                'anonymous_token' => $anonymousToken,
                'submitted_at' => $now->toIso8601String(),
                'target_response_hours' => $responseHours,
                'evidences_count' => count($uploadedFiles),
            ];
        });

        // Send email to counselors asynchronously if configured
        try {
            $recipients = DB::table('users')
                ->where('school_npsn', $data['school_npsn'])
                ->where('role', 'COUNSELOR')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();

            $isFallback = false;
            if ($recipients->isEmpty()) {
                $recipients = DB::table('users')
                    ->where('school_npsn', $data['school_npsn'])
                    ->where('role', 'PRINCIPAL')
                    ->whereNotNull('email')
                    ->where('email', '!=', '')
                    ->get();
                $isFallback = true;
            }

            if ($recipients->isEmpty()) {
                $recipients = DB::table('users')
                    ->where('role', 'ADMIN')
                    ->whereNotNull('email')
                    ->where('email', '!=', '')
                    ->get();
                $isFallback = true;
            }

            if ($recipients->isNotEmpty()) {
                $schoolName = DB::table('users')
                    ->where('school_npsn', $data['school_npsn'])
                    ->value('school_name') ?? $data['school_npsn'];

                $reportPayload = [
                    'case_number' => $result['case_number'],
                    'school_npsn' => $data['school_npsn'],
                    'school_name' => $schoolName,
                    'reporter_role' => $data['reporter_role'],
                    'identity_mode' => $data['identity_mode'],
                    'category' => $data['category'],
                    'description' => $data['description'],
                    'risk_level' => $result['risk_level'],
                    'submitted_at' => now()->toDateTimeString(),
                    'case_url' => route('cases.show', $result['case_number']),
                    'is_fallback' => $isFallback,
                ];

                foreach ($recipients as $recipient) {
                    $recipientLabel = $recipient->name ?? ($isFallback ? ($recipient->role === 'ADMIN' ? 'Admin Kota' : 'Kepala Sekolah') : 'Guru BK');
                    Mail::to($recipient->email)->queue(new NewReportCounselorMail($reportPayload, $recipientLabel));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim email notifikasi pelaporan: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dibuat. Terima kasih telah berani bersuara!',
            'data' => $result,
        ], 201);
    }

    /**
     * Track status of an existing report using case_number/report_number or anonymous_token.
     */
    public function trackReport(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tracking_code' => ['required', 'string', 'min:6', 'max:50'],
        ]);

        $code = trim(strtoupper($data['tracking_code']));

        // Try by anonymous token first (12 characters)
        $tokenRow = DB::table('anonymous_report_tokens')
            ->where('token_hash', hash('sha256', $code))
            ->where('status', true)
            ->first();

        if ($tokenRow) {
            $case = DB::table('cases')
                ->join('reports', 'cases.report_id', '=', 'reports.id')
                ->leftJoin('case_slas', 'cases.id', '=', 'case_slas.case_id')
                ->where('cases.report_id', $tokenRow->report_id)
                ->select('cases.case_number', 'cases.category', 'cases.status', 'cases.opened_at', 'reports.submitted_at', 'case_slas.status as sla_status')
                ->first();

            if ($case) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'case_number' => $case->case_number,
                        'category' => $case->category,
                        'status' => $case->status,
                        'status_label' => $this->statusLabel($case->status),
                        'submitted_at' => $case->submitted_at,
                        'is_anonymous' => true,
                        'has_active_channel' => true,
                    ],
                ]);
            }
        }

        // Try by case number or report number
        $case = DB::table('cases')
            ->join('reports', 'cases.report_id', '=', 'reports.id')
            ->where('cases.case_number', $code)
            ->orWhere('reports.report_number', $code)
            ->select('cases.case_number', 'cases.category', 'cases.status', 'cases.opened_at', 'reports.submitted_at', 'reports.identity_mode')
            ->first();

        if (! $case) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan atau token tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'case_number' => $case->case_number,
                'category' => $case->category,
                'status' => $case->status,
                'status_label' => $this->statusLabel($case->status),
                'submitted_at' => $case->submitted_at,
                'is_anonymous' => $case->identity_mode === 'ANONYMOUS',
            ],
        ]);
    }

    /**
     * Get two-way message history for an anonymous report.
     */
    public function anonymousMessages(string $token): JsonResponse
    {
        $tokenUpper = strtoupper(trim($token));
        $reportToken = DB::table('anonymous_report_tokens')
            ->where('token_hash', hash('sha256', $tokenUpper))
            ->where('status', true)
            ->first();

        if (! $reportToken || ($reportToken->expires_at && now()->greaterThan($reportToken->expires_at))) {
            return response()->json([
                'success' => false,
                'message' => 'Token anonim tidak valid atau sudah kedaluwarsa.',
            ], 404);
        }

        DB::table('anonymous_report_tokens')
            ->where('id', $reportToken->id)
            ->update(['last_access_at' => now(), 'updated_at' => now()]);

        $messages = DB::table('anonymous_messages')
            ->where('report_id', $reportToken->report_id)
            ->orderBy('created_at')
            ->get()
            ->map(fn ($msg) => [
                'id' => $msg->id,
                'sender_type' => $msg->sender_type,
                'message' => $msg->message,
                'created_at' => $msg->created_at,
            ]);

        $caseNumber = DB::table('cases')->where('report_id', $reportToken->report_id)->value('case_number');

        return response()->json([
            'success' => true,
            'data' => [
                'case_number' => $caseNumber,
                'messages' => $messages,
            ],
        ]);
    }

    /**
     * Send additional message from anonymous reporter to school counselor.
     */
    public function sendAnonymousMessage(Request $request, string $token): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $tokenUpper = strtoupper(trim($token));
        $reportToken = DB::table('anonymous_report_tokens')
            ->where('token_hash', hash('sha256', $tokenUpper))
            ->where('status', true)
            ->first();

        if (! $reportToken || ($reportToken->expires_at && now()->greaterThan($reportToken->expires_at))) {
            return response()->json([
                'success' => false,
                'message' => 'Token anonim tidak valid atau sudah kedaluwarsa.',
            ], 404);
        }

        $now = now();
        $messageId = DB::table('anonymous_messages')->insertGetId([
            'report_id' => $reportToken->report_id,
            'sender_type' => 'REPORTER',
            'message' => $data['message'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $caseNumber = DB::table('cases')->where('report_id', $reportToken->report_id)->value('case_number');

        if (function_exists('notifyCounselor')) {
            notifyCounselor('ANONYMOUS_MESSAGE', 'Pesan anonim baru', 'Pelapor mengirim informasi tambahan pada laporan.', $caseNumber, 'HIGH');
        }

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil dikirim.',
            'data' => [
                'id' => $messageId,
                'sender_type' => 'REPORTER',
                'message' => $data['message'],
                'created_at' => $now->toIso8601String(),
            ],
        ], 201);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'PENDING_RESPONSE' => 'Menunggu Respons Awal',
            'UNDER_VERIFICATION' => 'Dalam Verifikasi',
            'IN_HANDLING' => 'Dalam Penanganan',
            'RESOLVED' => 'Terselesaikan',
            'CLOSED' => 'Kasus Ditutup',
            default => str_replace('_', ' ', $status),
        };
    }
}
