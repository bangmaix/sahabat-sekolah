<?php

namespace App\Http\Controllers;

use App\Helpers\AConnect;
use App\Helpers\AnonymousTokenHelper;
use App\Helpers\ExifHelper;
use App\Helpers\SlaCalculator;
use App\Mail\NewReportCounselorMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function create(Request $request)
    {
        $schools = (new AConnect)->getSekolahList();
        $selectedNpsn = (string) $request->query('npsn', '');

        return view('reports.create', [
            'schools' => $schools,
            'selectedNpsn' => $selectedNpsn,
            'categories' => DB::table('bullying_categories')->where('status', true)->orderBy('name')->get()->map(function ($category) {
                $category->subcategories = DB::table('bullying_subcategories')->where('category_id', $category->id)->where('status', true)->orderBy('name')->get();

                return $category;
            }),
        ]);
    }

    public function inbox(Request $request)
    {
        $query = DB::table('cases')
            ->join('reports', 'cases.report_id', '=', 'reports.id')
            ->leftJoin('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->where('cases.school_npsn', session('school_npsn'))
            ->select(
                'cases.*',
                'reports.description',
                'reports.reporter_role',
                'reports.identity_mode',
                'case_slas.status as sla_status',
                'case_slas.response_deadline',
                'case_slas.initial_response_at',
                'case_slas.resolution_status',
                'case_slas.resolution_deadline'
            );

        if ($status = $request->input('status')) {
            if ($status !== 'ALL') {
                $query->where('cases.status', $status);
            }
        } else {
            $query->where('cases.status', '!=', 'CLOSED');
        }

        if ($risk = $request->input('risk_level')) {
            $query->where('cases.risk_level', $risk);
        }

        if ($sla = $request->input('sla_status')) {
            if ($sla === 'COMPLETED') {
                $query->where(function ($q) {
                    $q->whereIn('cases.status', ['RESOLVED', 'CLOSED'])
                        ->orWhere('case_slas.resolution_status', 'COMPLETED')
                        ->orWhere('case_slas.status', 'COMPLETED');
                });
            } elseif ($sla === 'OVERDUE') {
                $query->where(function ($q) {
                    $q->where('case_slas.status', 'OVERDUE')
                        ->orWhere('case_slas.resolution_status', 'OVERDUE')
                        ->orWhere(function ($sub) {
                            $sub->where('cases.status', 'PENDING_RESPONSE')
                                ->whereNotNull('case_slas.response_deadline')
                                ->where('case_slas.response_deadline', '<=', now());
                        })
                        ->orWhere(function ($sub) {
                            $sub->whereIn('cases.status', ['UNDER_VERIFICATION', 'IN_HANDLING'])
                                ->whereNotNull('case_slas.resolution_deadline')
                                ->where('case_slas.resolution_deadline', '<=', now());
                        });
                });
            } elseif ($sla === 'WARNING') {
                $query->where(function ($q) {
                    $q->where('case_slas.status', 'WARNING')
                        ->orWhere('case_slas.resolution_status', 'WARNING');
                });
            } elseif ($sla === 'ON_TIME') {
                $query->where(function ($q) {
                    $q->where('case_slas.status', 'ON_TIME')
                        ->where(function ($sub) {
                            $sub->whereNull('case_slas.resolution_status')
                                ->orWhereIn('case_slas.resolution_status', ['ON_TIME', 'ON_TRACK']);
                        })
                        ->where(function ($sub) {
                            $sub->where('cases.status', '!=', 'PENDING_RESPONSE')
                                ->orWhere('case_slas.response_deadline', '>', now());
                        })
                        ->where(function ($sub) {
                            $sub->whereNotIn('cases.status', ['UNDER_VERIFICATION', 'IN_HANDLING'])
                                ->orWhereNull('case_slas.resolution_deadline')
                                ->orWhere('case_slas.resolution_deadline', '>', now());
                        });
                });
            }
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('cases.case_number', 'like', '%'.$search.'%')
                    ->orWhere('cases.category', 'like', '%'.$search.'%')
                    ->orWhere('reports.description', 'like', '%'.$search.'%');
            });
        }

        return view('reports.inbox', [
            'cases' => $query->orderByDesc('cases.created_at')->get(),
            'filters' => $request->only(['status', 'risk_level', 'sla_status', 'search']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_npsn' => ['required', 'string'],
            'reporter_role' => ['required', 'in:VICTIM,WITNESS,CONCERNED_PERSON'],
            'identity_mode' => ['required', 'in:IDENTIFIED,CONFIDENTIAL,ANONYMOUS'],
            'reporter_name' => ['nullable', 'string', 'max:255', 'required_unless:identity_mode,ANONYMOUS'],
            'reporter_contact' => ['nullable', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:255', 'exists:bullying_categories,name'],
            'subcategory' => ['nullable', 'string', 'max:255', 'exists:bullying_subcategories,name'],
            'description' => ['required', 'string', 'min:20'],
            'evidences' => ['nullable', 'array', 'max:5'],
            'evidences.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ], [
            'school_npsn.required' => 'Pilih sekolah terlebih dahulu.',
            'reporter_role.required' => 'Pilih peran pelapor (Saya korban, Saya saksi, atau Saya mengetahui kejadian).',
            'identity_mode.required' => 'Pilih mode identitas laporan.',
            'reporter_name.required_unless' => 'Nama pelapor wajib diisi jika mode identitas Rahasia atau Terbuka.',
            'category.required' => 'Pilih kategori kejadian perundungan.',
            'description.required' => 'Ceritakan kronologi kejadian perundungan.',
            'description.min' => 'Cerita kejadian minimal 20 karakter agar informasi laporan cukup jelas.',
        ]);

        $caseNumber = DB::transaction(function () use ($data, $request) {
            $number = 'SS-'.now()->format('Y').'-'.str_pad((string) ((DB::table('reports')->max('id') ?? 0) + 1), 6, '0', STR_PAD_LEFT);
            $now = now();
            $reportData = $data;
            unset($reportData['reporter_name'], $reportData['reporter_contact'], $reportData['evidences']);

            $reportId = DB::table('reports')->insertGetId([
                ...$reportData, 'report_number' => $number, 'submitted_at' => $now,
                'created_at' => $now, 'updated_at' => $now,
            ]);

            $descLower = Str::lower($data['description']);
            $crisisKeywords = ['bunuh diri', 'ingin mati', 'akhiri hidup', 'senjata tajam', 'pisau', 'celurit', 'parang', 'perkosa', 'pelecehan seksual berat', 'ancaman pembunuhan'];
            $isCrisis = false;
            foreach ($crisisKeywords as $kw) {
                if (str_contains($descLower, $kw)) {
                    $isCrisis = true;
                    break;
                }
            }

            if ($isCrisis) {
                $risk = 'CRITICAL';
            } elseif (in_array($data['category'], ['Cyberbullying', 'Fisik', 'Perundungan Bernuansa Seksual', 'Kekerasan Serius', 'Pemerasan'], true)) {
                $risk = 'HIGH';
            } else {
                $risk = 'MEDIUM';
            }

            $caseId = DB::table('cases')->insertGetId([
                'school_npsn' => $data['school_npsn'], 'report_id' => $reportId, 'case_number' => $number,
                'category' => $data['category'], 'risk_level' => $risk, 'status' => 'PENDING_RESPONSE',
                'opened_at' => $now, 'created_at' => $now, 'updated_at' => $now,
            ]);

            $config = DB::table('sla_configurations')->where('risk_level', $risk)->first();
            $responseHours = $config ? $config->response_time_hours : ($risk === 'CRITICAL' ? 1 : ($risk === 'HIGH' ? 4 : 24));
            $resolutionDays = $config ? $config->resolution_time_days : 14;

            DB::table('case_slas')->insert([
                'case_id' => $caseId,
                'status' => 'ON_TIME',
                'response_deadline' => SlaCalculator::calculateDeadline($now, $responseHours, $risk),
                'resolution_deadline' => null, // dihitung ketika direspon
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
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            if ($request->hasFile('evidences')) {
                foreach ($request->file('evidences') as $file) {
                    $path = ExifHelper::stripAndStore($file, 'evidences');
                    DB::table('case_evidences')->insert([
                        'case_id' => $caseId,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_by' => $data['reporter_name'] ?? 'Pelapor',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            notifyCounselor('NEW_REPORT', 'Laporan baru masuk', $number.' menunggu respons awal dalam '.$responseHours.' jam.', $number, $risk === 'CRITICAL' ? 'URGENT' : 'HIGH');

            $anonymousToken = null;
            if ($data['identity_mode'] === 'ANONYMOUS') {
                $anonymousToken = AnonymousTokenHelper::generate();
                DB::table('anonymous_report_tokens')->insert([
                    'report_id' => $reportId,
                    'token_hash' => AnonymousTokenHelper::hash($anonymousToken),
                    'expires_at' => $now->copy()->addMonths(6),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            auditAction('CREATE_REPORT', 'REPORT', $number, [
                'school_npsn' => $data['school_npsn'],
                'category' => $data['category'],
                'risk_level' => $risk,
                'identity_mode' => $data['identity_mode'],
            ], null, $data['identity_mode'] === 'ANONYMOUS');

            return ['number' => $number, 'token' => $anonymousToken, 'risk' => $risk, 'is_crisis' => $isCrisis];
        });

        $recipients = DB::table('users')
            ->where('school_npsn', $data['school_npsn'])
            ->where('role', 'COUNSELOR')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        $isFallback = false;
        if ($recipients->isEmpty()) {
            // Fallback ke Kepala Sekolah jika belum ada Guru BK terdaftar (misal Madrasah/Sekolah Kecil)
            $recipients = DB::table('users')
                ->where('school_npsn', $data['school_npsn'])
                ->where('role', 'PRINCIPAL')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();
            $isFallback = true;
        }

        if ($recipients->isEmpty()) {
            // Fallback ke Admin Kota jika sekolah belum memiliki staf terdaftar di sistem
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
                ->whereNotNull('school_name')
                ->value('school_name');

            if (! $schoolName) {
                $schoolsMap = (new AConnect)->getSekolahMap();
                $schoolName = $schoolsMap[$data['school_npsn']] ?? 'Sekolah';
            }

            $mailData = [
                'case_number' => $caseNumber['number'],
                'school_npsn' => $data['school_npsn'],
                'school_name' => $schoolName,
                'category' => $data['category'],
                'subcategory' => $data['subcategory'] ?? null,
                'risk_level' => $caseNumber['risk'] ?? 'MEDIUM',
                'reporter_role' => $data['reporter_role'],
                'identity_mode' => $data['identity_mode'],
                'reporter_name' => $data['reporter_name'] ?? null,
                'description' => $data['description'],
                'submitted_at' => now(),
                'case_url' => route('cases.show', $caseNumber['number']),
                'is_fallback' => $isFallback,
            ];

            foreach ($recipients as $recipient) {
                try {
                    $recipientLabel = $recipient->name ?? ($isFallback ? ($recipient->role === 'ADMIN' ? 'Admin Kota' : 'Kepala Sekolah') : 'Guru BK');
                    Mail::to($recipient->email)->queue(new NewReportCounselorMail(
                        reportData: $mailData,
                        recipientName: $recipientLabel,
                    ));
                } catch (\Throwable $e) {
                    Log::error('Gagal memasukkan notifikasi email laporan baru ke antrean (queue): '.$e->getMessage(), [
                        'recipient_email' => $recipient->email,
                        'case_number' => $caseNumber['number'],
                    ]);
                }
            }
        }

        if ($caseNumber['token']) {
            return redirect()->route('reports.receipt')->with([
                'report_number' => $caseNumber['number'],
                'anonymous_token' => $caseNumber['token'],
                'is_crisis' => $caseNumber['is_crisis'] ?? false,
            ]);
        }

        if (session()->has('user_id')) {
            return redirect()->route('cases.show', $caseNumber['number'])
                ->with('success', 'Laporan berhasil dibuat dan masuk ke antrean Guru BK.');
        }

        return redirect()->route('reports.receipt')->with([
            'report_number' => $caseNumber['number'],
            'is_crisis' => $caseNumber['is_crisis'] ?? false,
        ]);
    }

    public function receipt()
    {
        abort_unless(session()->has('report_number'), 404);

        return view('reports.receipt', [
            'reportNumber' => session('report_number'),
            'anonymousToken' => session('anonymous_token'),
            'isCrisis' => session('is_crisis', false),
        ]);
    }

    public function track(Request $request)
    {
        $rawQuery = (string) ($request->input('q') ?? $request->input('token') ?? $request->input('report_number') ?? '');
        $query = trim($rawQuery);
        $searched = ($query !== '');
        $case = null;
        $report = null;
        $messages = collect();
        $isAnonymousOwner = false;
        $activeStep = 1;
        $histories = collect();
        $schoolName = null;
        $token = null;

        if ($searched) {
            // 1. Cek apakah query cocok dengan token anonim (4 kata atau hash)
            $tokenHash = AnonymousTokenHelper::hash($query);
            $tokenRecord = DB::table('anonymous_report_tokens')
                ->where('token_hash', $tokenHash)
                ->where('status', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->first();

            if ($tokenRecord) {
                $isAnonymousOwner = true;
                $token = $query;
                DB::table('anonymous_report_tokens')
                    ->where('id', $tokenRecord->id)
                    ->update(['last_access_at' => now(), 'updated_at' => now()]);

                $report = DB::table('reports')->where('id', $tokenRecord->report_id)->first();
                if ($report) {
                    $case = DB::table('cases')->where('report_id', $report->id)->first();
                    $messages = DB::table('anonymous_messages')
                        ->where('report_id', $report->id)
                        ->orderBy('created_at')
                        ->get();
                }
            } else {
                // 2. Cek apakah query cocok dengan nomor laporan atau nomor kasus
                $cleanQuery = strtoupper($query);
                $case = DB::table('cases')
                    ->where('case_number', $cleanQuery)
                    ->first();

                if (! $case) {
                    $report = DB::table('reports')->where('report_number', $cleanQuery)->first();
                    if ($report) {
                        $case = DB::table('cases')->where('report_id', $report->id)->first();
                    }
                } else {
                    $report = DB::table('reports')->where('id', $case->report_id)->first();
                }
            }

            if ($case && $report) {
                // Ambil SLA
                $case->sla = DB::table('case_slas')->where('case_id', $case->id)->first();

                // Ambil Nama Sekolah
                $schoolName = DB::table('users')
                    ->where('school_npsn', $case->school_npsn)
                    ->whereNotNull('school_name')
                    ->value('school_name');

                if (! $schoolName) {
                    try {
                        $schoolsMap = (new AConnect)->getSekolahMap();
                        $schoolName = $schoolsMap[$case->school_npsn] ?? null;
                    } catch (\Throwable $e) {
                        $schoolName = null;
                    }
                }
                $schoolName = $schoolName ?: 'Sekolah Lingkup Kota Bukittinggi';

                // Ambil riwayat perubahan status
                $histories = DB::table('case_status_histories')
                    ->where('case_id', $case->id)
                    ->orderBy('created_at', 'asc')
                    ->get();

                // Mapping 5 tahapan utama alur kasus
                $statusStepMap = [
                    'PENDING_RESPONSE' => 1,
                    'UNDER_VERIFICATION' => 2,
                    'WAITING_REPORTER_INFO' => 3,
                    'IN_HANDLING' => 3,
                    'RESOLVED' => 4,
                    'CLOSED' => 5,
                ];
                $activeStep = $statusStepMap[$case->status] ?? 1;
            }
        }

        return view('reports.track', compact(
            'query',
            'searched',
            'case',
            'report',
            'isAnonymousOwner',
            'token',
            'messages',
            'activeStep',
            'histories',
            'schoolName'
        ));
    }
}
