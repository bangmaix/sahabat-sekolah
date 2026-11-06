<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\AppStorage;
use App\Http\Controllers\Controller;
use App\Mail\CaseEscalatedPrincipalMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CaseController extends Controller
{
    /**
     * Get paginated list of cases for the user's school.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->attributes->get('api_user');
        $schoolNpsn = $user->school_npsn;

        $query = DB::table('cases')
            ->join('reports', 'cases.report_id', '=', 'reports.id')
            ->leftJoin('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->where('cases.school_npsn', $schoolNpsn)
            ->select(
                'cases.id',
                'cases.case_number',
                'cases.category',
                'cases.risk_level',
                'cases.status',
                'cases.assigned_to',
                'cases.opened_at',
                'cases.created_at',
                'cases.updated_at',
                'reports.reporter_role',
                'reports.identity_mode',
                'reports.description',
                'case_slas.status as sla_status',
                'case_slas.response_deadline',
                'case_slas.initial_response_at',
                'case_slas.resolution_status',
                'case_slas.resolution_deadline'
            );

        if ($status = $request->query('status')) {
            $query->where('cases.status', $status);
        }

        if ($riskLevel = $request->query('risk_level')) {
            $query->where('cases.risk_level', $riskLevel);
        }

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('cases.case_number', 'like', "%{$search}%")
                    ->orWhere('reports.description', 'like', "%{$search}%")
                    ->orWhere('cases.category', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) ($request->query('per_page', 15)), 100);
        $cases = $query->orderByDesc('cases.created_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $cases->items(),
            'meta' => [
                'current_page' => $cases->currentPage(),
                'last_page' => $cases->lastPage(),
                'per_page' => $cases->perPage(),
                'total' => $cases->total(),
            ],
        ]);
    }

    /**
     * Get complete details of a specific case.
     */
    public function show(Request $request, string $caseNumber): JsonResponse
    {
        $user = $request->attributes->get('api_user');
        $schoolNpsn = $user->school_npsn;

        $case = DB::table('cases')
            ->join('reports', 'cases.report_id', '=', 'reports.id')
            ->leftJoin('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->where('cases.case_number', $caseNumber)
            ->where('cases.school_npsn', $schoolNpsn)
            ->leftJoin('reporter_identities', 'reports.id', '=', 'reporter_identities.report_id')
            ->select(
                'cases.*',
                'reports.reporter_role',
                'reports.identity_mode',
                'reports.description',
                'reports.submitted_at',
                'reporter_identities.full_name as reporter_name',
                'reporter_identities.contact as reporter_contact',
                'reporter_identities.access_level as reporter_access_level',
                'case_slas.status as sla_status',
                'case_slas.response_deadline',
                'case_slas.initial_response_at',
                'case_slas.resolution_status',
                'case_slas.resolution_deadline'
            )
            ->first();

        if (! $case) {
            return response()->json([
                'success' => false,
                'message' => 'Kasus tidak ditemukan atau berada di luar wewenang sekolah Anda.',
            ], 404);
        }

        // Hide reporter identity if identity mode is ANONYMOUS
        if ($case->identity_mode === 'ANONYMOUS') {
            $case->reporter_name = 'Anonim (Disamarkan)';
            $case->reporter_contact = null;
        }

        $history = DB::table('case_status_histories')->where('case_id', $case->id)->orderByDesc('created_at')->get();
        $actions = DB::table('case_actions')->where('case_id', $case->id)->orderByDesc('created_at')->get();
        $notes = DB::table('case_notes')->where('case_id', $case->id)->orderByDesc('created_at')->get();
        $evidences = DB::table('case_evidences')->where('case_id', $case->id)->orderByDesc('created_at')->get();
        $resolutionDocuments = DB::table('case_resolution_documents')->where('case_id', $case->id)->orderByDesc('created_at')->get();
        $parentInvolvements = DB::table('case_parent_involvements')
            ->leftJoin('parents', 'case_parent_involvements.parent_id', '=', 'parents.id')
            ->where('case_parent_involvements.case_id', $case->id)
            ->select('case_parent_involvements.*', 'parents.full_name as parent_name', 'parents.contact as parent_contact')
            ->get();
        $participants = DB::table('case_participants')->where('case_id', $case->id)->get();
        $escalations = DB::table('case_escalations')->where('case_id', $case->id)->orderByDesc('created_at')->get();

        $anonymousMessages = [];
        if ($case->identity_mode === 'ANONYMOUS') {
            $anonymousMessages = DB::table('anonymous_messages')
                ->where('report_id', $case->report_id)
                ->orderBy('created_at')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'case' => $case,
                'status_history' => $history,
                'actions' => $actions,
                'notes' => $notes,
                'evidences' => $evidences,
                'resolution_documents' => $resolutionDocuments,
                'parent_involvements' => $parentInvolvements,
                'participants' => $participants,
                'escalations' => $escalations,
                'anonymous_messages' => $anonymousMessages,
            ],
        ]);
    }

    /**
     * Unified case action handling: updates status, logs actions, recalculates SLA, and attaches notes/documents.
     */
    public function processAction(Request $request, string $caseNumber): JsonResponse
    {
        $user = $request->attributes->get('api_user');
        $case = DB::table('cases')
            ->where('case_number', $caseNumber)
            ->where('school_npsn', $user->school_npsn)
            ->first();

        if (! $case) {
            return response()->json(['success' => false, 'message' => 'Kasus tidak ditemukan.'], 404);
        }

        $data = $request->validate([
            'status' => ['nullable', 'in:UNDER_VERIFICATION,IN_HANDLING,RESOLVED,CLOSED'],
            'note_type' => ['nullable', 'in:INTERNAL,PARENT_COMMUNICATION,MEDIATION,COUNSELING,OTHER'],
            'note_content' => ['nullable', 'string', 'max:2000'],
            'reason' => ['nullable', 'string', 'max:500'],
            'document_type' => ['nullable', 'in:EVIDENCE,RESOLUTION'],
            'document_file' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,mp4,mov'],
        ]);

        $allowedTransitions = [
            'PENDING_RESPONSE' => ['UNDER_VERIFICATION', 'IN_HANDLING', 'RESOLVED'],
            'UNDER_VERIFICATION' => ['IN_HANDLING', 'RESOLVED'],
            'IN_HANDLING' => ['RESOLVED'],
            'RESOLVED' => ['CLOSED'],
            'CLOSED' => [],
        ];

        if (! empty($data['status'])) {
            $valid = in_array($data['status'], $allowedTransitions[$case->status] ?? [], true);
            if (! $valid) {
                return response()->json([
                    'success' => false,
                    'message' => "Transisi status dari {$case->status} ke {$data['status']} tidak diizinkan.",
                ], 422);
            }

            if ($data['status'] === 'CLOSED') {
                $hasValidResolution = DB::table('case_resolution_documents')
                    ->where('case_id', $case->id)
                    ->where('verification_status', 'VALID')
                    ->exists();

                if (! $hasValidResolution && empty($data['document_file'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kasus belum memiliki dokumen penyelesaian valid untuk ditutup.',
                    ], 422);
                }
            }
        }

        $now = now();
        $userName = $user->name ?? 'Petugas BK';

        DB::transaction(function () use ($case, $data, $request, $now, $userName) {
            $nextStatus = $data['status'] ?? null;

            // Auto-transition from PENDING_RESPONSE if counselor provided note or document
            if (! $nextStatus && $case->status === 'PENDING_RESPONSE' && (! empty($data['note_content']) || $request->hasFile('document_file'))) {
                $nextStatus = 'UNDER_VERIFICATION';
            }

            if ($nextStatus && $nextStatus !== $case->status) {
                DB::table('cases')->where('id', $case->id)->update([
                    'status' => $nextStatus,
                    'updated_at' => $now,
                    'resolved_at' => $nextStatus === 'RESOLVED' ? $now : null,
                    'closed_at' => $nextStatus === 'CLOSED' ? $now : null,
                ]);

                DB::table('case_status_histories')->insert([
                    'case_id' => $case->id,
                    'old_status' => $case->status,
                    'new_status' => $nextStatus,
                    'reason' => $data['reason'] ?? ($data['note_content'] ? 'Tindakan penanganan diberikan' : 'Pembaruan status kasus'),
                    'changed_by' => $userName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('case_actions')->insert([
                    'case_id' => $case->id,
                    'action_type' => $nextStatus === 'UNDER_VERIFICATION' ? 'VERIFICATION' : ($nextStatus === 'IN_HANDLING' ? 'HANDLING' : 'RESOLUTION'),
                    'description' => $data['reason'] ?: 'Status kasus diperbarui menjadi '.str_replace('_', ' ', $nextStatus),
                    'performed_by' => $userName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if ($case->status === 'PENDING_RESPONSE') {
                    $config = DB::table('sla_configurations')->where('risk_level', $case->risk_level)->first();
                    $resolutionDays = $config ? $config->resolution_time_days : 14;

                    $currentSla = DB::table('case_slas')->where('case_id', $case->id)->first();
                    $isOverdue = $currentSla && ($currentSla->status === 'OVERDUE' || ($currentSla->response_deadline && Carbon::parse($currentSla->response_deadline)->isPast()));

                    DB::table('case_slas')->where('case_id', $case->id)->update([
                        'status' => $isOverdue ? 'OVERDUE' : 'ON_TIME',
                        'initial_response_at' => $now,
                        'overdue_at' => $isOverdue ? ($currentSla->overdue_at ?? $now) : null,
                        'resolution_status' => 'ON_TIME',
                        'resolution_deadline' => $now->copy()->addDays($resolutionDays),
                        'updated_at' => $now,
                    ]);
                }

                if (in_array($nextStatus, ['RESOLVED', 'CLOSED'], true)) {
                    DB::table('case_slas')->where('case_id', $case->id)->update([
                        'resolution_status' => 'COMPLETED',
                        'resolved_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                if (function_exists('notifyCounselor')) {
                    notifyCounselor('STATUS_CHANGED', 'Status kasus diperbarui', $case->case_number.' sekarang '.str_replace('_', ' ', $nextStatus).'.', $case->case_number, 'HIGH');
                }
            }

            if (! empty($data['note_content'])) {
                $noteType = $data['note_type'] ?? 'INTERNAL';
                DB::table('case_notes')->insert([
                    'case_id' => $case->id,
                    'note_type' => $noteType,
                    'content' => $data['note_content'],
                    'visibility' => 'PRIVATE_BK',
                    'created_by' => $userName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if ($request->hasFile('document_file')) {
                $file = $request->file('document_file');
                $docType = $data['document_type'] ?? ($nextStatus === 'CLOSED' || $nextStatus === 'RESOLVED' ? 'RESOLUTION' : 'EVIDENCE');
                $path = AppStorage::store($file, 'case-documents', 'private');

                $payload = [
                    'case_id' => $case->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'uploaded_by' => $userName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if ($docType === 'RESOLUTION') {
                    DB::table('case_resolution_documents')->insert($payload + ['verification_status' => 'VALID']);
                } else {
                    DB::table('case_evidences')->insert($payload + [
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }
        });

        $updatedCase = DB::table('cases')->where('id', $case->id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Aksi penanganan kasus berhasil diproses.',
            'data' => [
                'case_number' => $case->case_number,
                'status' => $updatedCase->status,
                'updated_at' => $updatedCase->updated_at,
            ],
        ]);
    }

    /**
     * Add a case note directly.
     */
    public function addNote(Request $request, string $caseNumber): JsonResponse
    {
        $user = $request->attributes->get('api_user');
        $case = DB::table('cases')->where('case_number', $caseNumber)->where('school_npsn', $user->school_npsn)->first();

        if (! $case) {
            return response()->json(['success' => false, 'message' => 'Kasus tidak ditemukan.'], 404);
        }

        $data = $request->validate([
            'note_type' => ['required', 'in:INTERNAL,PARENT_COMMUNICATION,MEDIATION,COUNSELING,OTHER'],
            'content' => ['required', 'string', 'min:3', 'max:5000'],
        ]);

        $noteId = DB::table('case_notes')->insertGetId([
            'case_id' => $case->id,
            'note_type' => $data['note_type'],
            'content' => $data['content'],
            'visibility' => 'PRIVATE_BK',
            'created_by' => $user->name ?? 'Petugas BK',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Catatan kasus berhasil disimpan.',
            'data' => [
                'id' => $noteId,
                'note_type' => $data['note_type'],
                'content' => $data['content'],
                'created_at' => now()->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Escalate case to Principal.
     */
    public function escalate(Request $request, string $caseNumber): JsonResponse
    {
        $user = $request->attributes->get('api_user');
        $case = DB::table('cases')->where('case_number', $caseNumber)->where('school_npsn', $user->school_npsn)->first();

        if (! $case) {
            return response()->json(['success' => false, 'message' => 'Kasus tidak ditemukan.'], 404);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $now = now();
        $userName = $user->name ?? 'Guru BK';

        DB::table('case_escalations')->insert([
            'case_id' => $case->id,
            'escalation_type' => 'MANUAL',
            'reason' => $data['reason'],
            'escalated_by' => $userName,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('notifications')->insert([
            'recipient_name' => 'Kepala Sekolah',
            'type' => 'MANUAL_ESCALATION',
            'title' => 'Kasus dieskalasikan',
            'message' => $case->case_number.' membutuhkan perhatian Kepala Sekolah: '.$data['reason'],
            'priority' => 'URGENT',
            'case_number' => $case->case_number,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Send email to principals if configured
        try {
            $principals = DB::table('users')
                ->where('school_npsn', $case->school_npsn)
                ->where('role', 'PRINCIPAL')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();

            if ($principals->isNotEmpty()) {
                $schoolName = DB::table('users')->where('school_npsn', $case->school_npsn)->value('school_name') ?? $case->school_npsn;
                $escalationPayload = [
                    'case_number' => $case->case_number,
                    'school_npsn' => $case->school_npsn,
                    'school_name' => $schoolName,
                    'category' => $case->category,
                    'risk_level' => $case->risk_level,
                    'escalated_by' => $userName,
                    'reason' => $data['reason'],
                    'escalated_at' => $now->toDateTimeString(),
                ];

                foreach ($principals as $principal) {
                    Mail::to($principal->email)->queue(new CaseEscalatedPrincipalMail($escalationPayload, $principal->name));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim email eskalasi: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Kasus berhasil dieskalasikan ke Kepala Sekolah.',
        ]);
    }

    /**
     * Update or add parent involvement.
     */
    public function updateParentInvolvement(Request $request, string $caseNumber): JsonResponse
    {
        $user = $request->attributes->get('api_user');
        $case = DB::table('cases')->where('case_number', $caseNumber)->where('school_npsn', $user->school_npsn)->first();

        if (! $case) {
            return response()->json(['success' => false, 'message' => 'Kasus tidak ditemukan.'], 404);
        }

        $data = $request->validate([
            'parent_name' => ['required', 'string', 'min:2', 'max:255'],
            'parent_contact' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:NOT_REQUIRED,PENDING,APPROVED,CONTACTED,COMPLETED'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $parent = DB::table('parents')->where('school_npsn', $case->school_npsn)->where('full_name', $data['parent_name'])->first();
        $parentId = $parent?->id ?: DB::table('parents')->insertGetId([
            'school_npsn' => $case->school_npsn,
            'full_name' => $data['parent_name'],
            'contact' => $data['parent_contact'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $now = now();
        DB::table('case_parent_involvements')->updateOrInsert(
            ['case_id' => $case->id, 'parent_id' => $parentId],
            [
                'status' => $data['status'],
                'reason' => $data['reason'] ?? null,
                'contacted_at' => in_array($data['status'], ['CONTACTED', 'COMPLETED'], true) ? $now : null,
                'completed_at' => $data['status'] === 'COMPLETED' ? $now : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Pelibatan orang tua berhasil diperbarui.',
        ]);
    }

    /**
     * Add participant to the case (victim, witness, alleged perpetrator).
     */
    public function addParticipant(Request $request, string $caseNumber): JsonResponse
    {
        $user = $request->attributes->get('api_user');
        $case = DB::table('cases')->where('case_number', $caseNumber)->where('school_npsn', $user->school_npsn)->first();

        if (! $case) {
            return response()->json(['success' => false, 'message' => 'Kasus tidak ditemukan.'], 404);
        }

        $data = $request->validate([
            'participant_type' => ['required', 'in:REPORTER,VICTIM,ALLEGED_PERPETRATOR,WITNESS,OTHER'],
            'display_name' => ['required', 'string', 'min:2', 'max:255'],
            'identity_visibility' => ['nullable', 'in:CASE_RESTRICTED,CASE_FULL,CASE_SENSITIVE'],
            'student_nisn' => ['nullable', 'string', 'max:50'],
        ]);

        $studentId = null;
        if (! empty($data['student_nisn'])) {
            $student = DB::table('students')
                ->where('school_npsn', $case->school_npsn)
                ->where('student_number', $data['student_nisn'])
                ->first();

            if ($student) {
                $studentId = $student->id;
            } else {
                $studentId = DB::table('students')->insertGetId([
                    'school_npsn' => $case->school_npsn,
                    'full_name' => $data['display_name'],
                    'student_number' => $data['student_nisn'],
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $id = DB::table('case_participants')->insertGetId([
            'case_id' => $case->id,
            'participant_type' => $data['participant_type'],
            'student_id' => $studentId,
            'display_name' => $data['display_name'],
            'identity_visibility' => $data['identity_visibility'] ?? 'CASE_RESTRICTED',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pihak terlibat berhasil ditambahkan.',
            'data' => [
                'id' => $id,
                'display_name' => $data['display_name'],
                'participant_type' => $data['participant_type'],
            ],
        ], 201);
    }

    /**
     * Counselor reply to anonymous reporter in anonymous chat channel.
     */
    public function replyAnonymous(Request $request, string $caseNumber): JsonResponse
    {
        $user = $request->attributes->get('api_user');
        $case = DB::table('cases')->where('case_number', $caseNumber)->where('school_npsn', $user->school_npsn)->first();

        if (! $case) {
            return response()->json(['success' => false, 'message' => 'Kasus tidak ditemukan.'], 404);
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $now = now();
        $id = DB::table('anonymous_messages')->insertGetId([
            'report_id' => $case->report_id,
            'sender_type' => 'COUNSELOR',
            'message' => $data['message'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesan tanggapan berhasil dikirim ke pelapor anonim.',
            'data' => [
                'id' => $id,
                'sender_type' => 'COUNSELOR',
                'message' => $data['message'],
                'created_at' => $now->toIso8601String(),
            ],
        ], 201);
    }
}
