<?php

namespace App\Http\Controllers;

use App\Helpers\AConnect;
use App\Helpers\AppStorage;
use App\Mail\CaseEscalatedPrincipalMail;
use App\Models\AcademicPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CaseController extends Controller
{
    public function show(string $caseNumber)
    {
        $query = DB::table('cases')
            ->join('reports', 'cases.report_id', '=', 'reports.id')
            ->leftJoin('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->where('cases.case_number', $caseNumber)
            ->leftJoin('reporter_identities', 'reports.id', '=', 'reporter_identities.report_id')
            ->leftJoin('students', 'reporter_identities.student_id', '=', 'students.id')
            ->select('cases.*', 'reports.reporter_role', 'reports.identity_mode', 'reports.description', 'reports.submitted_at', 'reporter_identities.full_name as reporter_name', 'reporter_identities.contact as reporter_contact', 'reporter_identities.access_level as reporter_access_level', 'students.class_name as reporter_class', 'students.student_number as reporter_student_number', 'case_slas.status as sla_status', 'case_slas.response_deadline', 'case_slas.initial_response_at', 'case_slas.resolution_status', 'case_slas.resolution_deadline');

        if (session('school_npsn')) {
            $query->where('cases.school_npsn', session('school_npsn'));
        }

        $case = $query->firstOrFail();

        $schoolName = DB::table('users')->where('school_npsn', $case->school_npsn)->value('school_name');
        $case->school_name = $schoolName ?? '-';

        // Evaluate SLA dynamically for display if SLA scan hasn't run yet
        if ($case->status === 'PENDING_RESPONSE') {
            if ($case->sla_status !== 'OVERDUE' && $case->response_deadline && Carbon::parse($case->response_deadline)->isPast()) {
                $case->sla_status = 'OVERDUE';
            }
        } else {
            $isInitialResponseOverdue = ($case->sla_status === 'OVERDUE')
                || ($case->initial_response_at && $case->response_deadline && Carbon::parse($case->initial_response_at)->gt(Carbon::parse($case->response_deadline)));

            if ($isInitialResponseOverdue) {
                $case->sla_status = 'OVERDUE';
            } elseif (in_array($case->status, ['UNDER_VERIFICATION', 'IN_HANDLING'])) {
                $resolutionDeadline = $case->resolution_deadline ?? DB::table('case_slas')->where('case_id', $case->id)->value('resolution_deadline');
                if ($resolutionDeadline && Carbon::parse($resolutionDeadline)->isPast()) {
                    $case->sla_status = 'OVERDUE';
                }
            }
        }

        DB::table('case_access_logs')->insert([
            'user_id' => session('user_id'), 'user_name' => session('user_name'), 'case_id' => $case->id,
            'action' => 'VIEW_CASE', 'access_level' => in_array(session('user_role'), ['PRINCIPAL', 'CITY_ADMIN']) ? 'CASE_SUMMARY' : 'CASE_FULL',
            'reason' => 'Case detail dibuka', 'ip_address' => request()->ip(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        auditAction('VIEW_CASE', 'CASE', $case->case_number);

        $history = DB::table('case_status_histories')
            ->where('case_id', $case->id)
            ->orderBy('created_at')
            ->get();
        $actions = DB::table('case_actions')
            ->where('case_id', $case->id)
            ->orderByDesc('created_at')
            ->get();
        $evidences = DB::table('case_evidences')->where('case_id', $case->id)->latest()->get();
        $resolutionDocuments = DB::table('case_resolution_documents')->where('case_id', $case->id)->latest()->get();
        $riskAssessment = DB::table('case_risk_assessments')->where('case_id', $case->id)->latest()->first();
        $riskHistory = DB::table('case_risk_histories')->where('case_id', $case->id)->latest()->get();
        $notes = DB::table('case_notes')->where('case_id', $case->id)->latest()->get();
        $escalations = DB::table('case_escalations')->where('case_id', $case->id)->latest()->get();
        $participants = DB::table('case_participants')
            ->leftJoin('students', 'case_participants.student_id', '=', 'students.id')
            ->where('case_participants.case_id', $case->id)
            ->where('case_participants.status', 'ACTIVE')
            ->select('case_participants.*', 'students.student_number as student_nisn', 'students.class_name as student_class')
            ->orderBy('case_participants.participant_type')
            ->get();
        $parentInvolvements = DB::table('case_parent_involvements')
            ->join('parents', 'case_parent_involvements.parent_id', '=', 'parents.id')
            ->where('case_parent_involvements.case_id', $case->id)
            ->select('case_parent_involvements.*', 'parents.full_name as parent_name', 'parents.contact as parent_contact')
            ->latest()
            ->get();

        $students = [];
        if (session('user_role') === 'COUNSELOR') {
            try {
                $activePeriod = AcademicPeriod::where('is_active', true)->first();
                $fallbackPeriod = AcademicPeriod::where('is_fallback', true)->orderBy('order_no')->first();

                $aConnect = new AConnect;
                $activeSemesterId = $activePeriod?->semester_id ?? '20252';
                $schoolNpsn = $case->school_npsn ?: session('school_npsn');

                // Cek id dari semester aktif sekarang apakah data siswa sudah ada
                $rawStudents = $schoolNpsn ? $aConnect->getSiswaSekolah($schoolNpsn, $activeSemesterId) : [];

                // Jika belum ada data siswa, cek menggunakan id semester cadangan
                if (
                    empty($rawStudents) &&
                    $fallbackPeriod &&
                    $fallbackPeriod->semester_id !== $activeSemesterId &&
                    $schoolNpsn
                ) {
                    $fallbackStudents = $aConnect->getSiswaSekolah($schoolNpsn, $fallbackPeriod->semester_id);
                    if (! empty($fallbackStudents)) {
                        $rawStudents = $fallbackStudents;
                    }
                }

                // Ambil NISN dan nama saja sebagai data
                $students = collect($rawStudents)->map(fn ($s) => [
                    'nisn' => trim((string) ($s['nisn'] ?? '')),
                    'nama' => trim((string) ($s['nm_siswa'] ?? '')),
                    'kelas' => trim((string) ($s['nm_kelas'] ?? '')),
                ])->filter(fn ($s) => ! empty($s['nisn']) && ! empty($s['nama']))->values()->all();
            } catch (\Throwable $e) {
                Log::warning('Gagal memuat data siswa AConnect di CaseController: '.$e->getMessage());
                $students = [];
            }
        }

        $schoolStaff = DB::table('users')
            ->where('school_npsn', $case->school_npsn)
            ->where(function ($q) {
                $q->where('status', 'ACTIVE')->orWhereNull('status');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('cases.show', compact('case', 'history', 'actions', 'evidences', 'resolutionDocuments', 'riskAssessment', 'riskHistory', 'notes', 'escalations', 'participants', 'parentInvolvements', 'students', 'schoolStaff'));
    }

    public function reassign(Request $request, string $caseNumber)
    {
        $case = scopedCase($caseNumber);

        $data = $request->validate([
            'assigned_to' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'assigned_to.required' => 'Pilih atau masukkan nama penanggung jawab kasus.',
            'reason.required' => 'Tuliskan alasan pengalihan penanggung jawab kasus.',
            'reason.min' => 'Alasan pengalihan minimal 5 karakter.',
        ]);

        $oldAssignee = $case->assigned_to;

        DB::table('cases')->where('id', $case->id)->update([
            'assigned_to' => $data['assigned_to'],
            'updated_at' => now(),
        ]);

        DB::table('case_notes')->insert([
            'case_id' => $case->id,
            'note_type' => 'INTERNAL',
            'content' => "Pengalihan Penanggung Jawab Kasus dari '{$oldAssignee}' ke '{$data['assigned_to']}'. Alasan: {$data['reason']}",
            'visibility' => 'PRIVATE_BK',
            'created_by' => session('user_name', 'Staf'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        auditAction('REASSIGN_CASE', 'CASE', $caseNumber, [
            'old_assignee' => $oldAssignee,
            'new_assignee' => $data['assigned_to'],
            'reason' => $data['reason'],
        ]);

        notifyCounselor(
            'CASE_REASSIGNED',
            'Pengalihan Penanggung Jawab Kasus',
            "Kasus {$caseNumber} dialihkan kepada {$data['assigned_to']}.",
            $caseNumber,
            'NORMAL'
        );

        return back()->with('success', "Penanggung jawab kasus {$caseNumber} berhasil dialihkan ke {$data['assigned_to']}.");
    }

    public function updateParentInvolvement(Request $request, string $caseNumber)
    {
        $data = $request->validate([
            'parent_name' => ['required', 'string', 'min:2', 'max:255'],
            'parent_contact' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:NOT_REQUIRED,PENDING,APPROVED,CONTACTED,COMPLETED'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $case = scopedCase($caseNumber);
        $parent = DB::table('parents')->where('school_npsn', $case->school_npsn)->where('full_name', $data['parent_name'])->first();
        $parentId = $parent?->id ?: DB::table('parents')->insertGetId(['school_npsn' => $case->school_npsn, 'full_name' => $data['parent_name'], 'contact' => $data['parent_contact'], 'created_at' => now(), 'updated_at' => now()]);

        $now = now();
        DB::table('case_parent_involvements')->updateOrInsert(
            ['case_id' => $case->id, 'parent_id' => $parentId],
            ['status' => $data['status'], 'reason' => $data['reason'] ?? null, 'contacted_at' => in_array($data['status'], ['CONTACTED', 'COMPLETED'], true) ? $now : null, 'completed_at' => $data['status'] === 'COMPLETED' ? $now : null, 'updated_at' => $now, 'created_at' => $now]
        );

        auditAction('UPDATE_PARENT_INVOLVEMENT', 'CASE', $case->case_number, ['parent' => $data['parent_name'], 'status' => $data['status']]);
        notifyCounselor('PARENT_INVOLVEMENT', 'Pelibatan orang tua diperbarui', 'Status pelibatan orang tua pada '.$case->case_number.' menjadi '.$data['status'].'.', $case->case_number);

        return back()->with('success', 'Pelibatan orang tua berhasil diperbarui.');
    }

    public function addParticipant(Request $request, string $caseNumber)
    {
        $data = $request->validate([
            'participant_type' => ['required', 'in:REPORTER,VICTIM,ALLEGED_PERPETRATOR,WITNESS,OTHER'],
            'display_name' => ['required', 'string', 'min:2', 'max:255'],
            'identity_visibility' => ['required', 'in:CASE_RESTRICTED,CASE_FULL,CASE_SENSITIVE'],
            'student_nisn' => ['nullable', 'string', 'max:50'],
        ]);

        $case = scopedCase($caseNumber);
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

        DB::table('case_participants')->insert([
            'case_id' => $case->id,
            'participant_type' => $data['participant_type'],
            'student_id' => $studentId,
            'display_name' => $data['display_name'],
            'identity_visibility' => $data['identity_visibility'],
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        auditAction('ADD_PARTICIPANT', 'CASE', $case->case_number, [
            'participant_type' => $data['participant_type'],
            'visibility' => $data['identity_visibility'],
            'student_nisn' => $data['student_nisn'] ?? null,
            'student_id' => $studentId,
        ]);

        return back()->with('success', 'Pihak terkait berhasil ditambahkan.');
    }

    public function escalate(Request $request, string $caseNumber)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'escalate_to' => ['nullable', 'in:PRINCIPAL,CITY_ADMIN,CITY_DISDIK,CITY_PPA,CITY_SATGAS'],
        ]);

        $case = scopedCase($caseNumber);
        $escalatedBy = session('user_name', 'Guru BK');
        $now = now();
        $target = $data['escalate_to'] ?? 'PRINCIPAL';

        if ($target === 'CITY_PPA') {
            $notifiedTo = 'UPTD PPA - DP3APPKB Kota Bukittinggi';
        } elseif ($target === 'CITY_SATGAS') {
            $notifiedTo = 'Satgas PPKSP Kota Bukittinggi (Disdik & UPTD PPA)';
        } elseif ($target === 'CITY_DISDIK' || $target === 'CITY_ADMIN') {
            $notifiedTo = 'Dinas Pendidikan Kota Bukittinggi';
        } else {
            $notifiedTo = 'Kepala Sekolah';
        }

        DB::table('case_escalations')->insert([
            'case_id' => $case->id,
            'escalation_type' => 'MANUAL',
            'reason' => $data['reason'],
            'escalated_by' => $escalatedBy,
            'notified_to' => $notifiedTo,
            'status' => 'SENT',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('notifications')->insert([
            'recipient_name' => $notifiedTo,
            'type' => 'MANUAL_ESCALATION',
            'title' => 'Kasus dieskalasikan ke '.$notifiedTo,
            'message' => $case->case_number.' membutuhkan perhatian '.$notifiedTo.'.',
            'priority' => 'URGENT',
            'case_number' => $case->case_number,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        auditAction('ESCALATE_CASE', 'CASE', $case->case_number, [
            'reason' => $data['reason'],
            'escalate_to' => $target,
            'notified_to' => $notifiedTo,
        ]);

        if ($target === 'CITY_PPA') {
            $recipients = DB::table('users')
                ->where('role', 'CITY_ADMIN')
                ->where(function ($query) {
                    $query->where('school_name', 'LIKE', '%PPA%')
                        ->orWhere('school_name', 'LIKE', '%DP3APPKB%')
                        ->orWhere('email', 'LIKE', '%ppa%');
                })
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();

            if ($recipients->isEmpty()) {
                $recipients = DB::table('users')
                    ->where('role', 'CITY_ADMIN')
                    ->whereNotNull('email')
                    ->where('email', '!=', '')
                    ->get();
            }
        } elseif ($target === 'CITY_DISDIK' || $target === 'CITY_ADMIN') {
            $recipients = DB::table('users')
                ->where('role', 'CITY_ADMIN')
                ->where(function ($query) {
                    $query->where('school_name', 'LIKE', '%Pendidikan%')
                        ->orWhere('email', 'LIKE', '%dinas%');
                })
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();

            if ($recipients->isEmpty()) {
                $recipients = DB::table('users')
                    ->where('role', 'CITY_ADMIN')
                    ->whereNotNull('email')
                    ->where('email', '!=', '')
                    ->get();
            }
        } elseif ($target === 'CITY_SATGAS') {
            $recipients = DB::table('users')
                ->where('role', 'CITY_ADMIN')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();
        } else {
            $recipients = DB::table('users')
                ->where('school_npsn', $case->school_npsn)
                ->where('role', 'PRINCIPAL')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();

            if ($recipients->isEmpty()) {
                $recipients = DB::table('users')
                    ->where('role', 'CITY_ADMIN')
                    ->whereNotNull('email')
                    ->where('email', '!=', '')
                    ->get();

                if ($recipients->isEmpty()) {
                    $recipients = DB::table('users')
                        ->where('role', 'ADMIN')
                        ->whereNotNull('email')
                        ->where('email', '!=', '')
                        ->get();
                }
                $notifiedTo = 'Dinas Pendidikan Kota Bukittinggi (Fallback)';
            }
        }

        // Global fallback to ADMIN if no recipient found
        if ($recipients->isEmpty()) {
            $recipients = DB::table('users')
                ->where('role', 'ADMIN')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();
        }

        if ($recipients->isNotEmpty()) {
            $schoolName = DB::table('users')
                ->where('school_npsn', $case->school_npsn)
                ->whereNotNull('school_name')
                ->value('school_name');

            if (! $schoolName) {
                $schoolsMap = (new AConnect)->getSekolahMap();
                $schoolName = $schoolsMap[$case->school_npsn] ?? 'Sekolah';
            }

            $caseReport = DB::table('cases')
                ->join('reports', 'cases.report_id', '=', 'reports.id')
                ->where('cases.id', $case->id)
                ->select('reports.description', 'reports.category', 'reports.subcategory')
                ->first();

            $caseData = [
                'case_number' => $case->case_number,
                'school_npsn' => $case->school_npsn,
                'school_name' => $schoolName,
                'category' => $caseReport->category ?? $case->category,
                'subcategory' => $caseReport->subcategory ?? null,
                'risk_level' => $case->risk_level,
                'status' => $case->status,
                'description' => $caseReport->description ?? '',
                'case_url' => route('cases.show', $case->case_number),
            ];

            $escalationData = [
                'escalated_by' => $escalatedBy,
                'reason' => $data['reason'],
                'escalated_at' => $now,
                'notified_to' => $notifiedTo,
            ];

            foreach ($recipients as $recipient) {
                try {
                    Mail::to($recipient->email)->queue(new CaseEscalatedPrincipalMail(
                        caseData: $caseData,
                        escalationData: $escalationData,
                        recipientName: $recipient->name ?? $notifiedTo,
                    ));
                } catch (\Throwable $e) {
                    Log::error('Gagal memasukkan notifikasi email eskalasi ke antrean (queue): '.$e->getMessage(), [
                        'recipient_email' => $recipient->email,
                        'case_number' => $case->case_number,
                    ]);
                }
            }
        }

        return back()->with('success', "Kasus berhasil dieskalasikan kepada {$notifiedTo}.");
    }

    public function addNote(Request $request, string $caseNumber)
    {
        $data = $request->validate([
            'note_type' => ['required', 'in:VERIFICATION,HANDLING,COUNSELING,FOLLOW_UP,INTERNAL'],
            'content' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $case = scopedCase($caseNumber);

        DB::table('case_notes')->insert([
            'case_id' => $case->id, 'note_type' => $data['note_type'], 'content' => $data['content'],
            'visibility' => 'PRIVATE_BK', 'created_by' => session('user_name', 'Guru BK'), 'created_at' => now(), 'updated_at' => now(),
        ]);

        auditAction('CREATE_NOTE', 'CASE', $case->case_number, ['note_type' => $data['note_type']]);
        notifyCounselor('NOTE_ADDED', 'Catatan kasus ditambahkan', 'Catatan '.strtolower($data['note_type']).' ditambahkan ke '.$caseNumber.'.', $caseNumber);

        return back()->with('success', 'Catatan kasus berhasil disimpan.');
    }

    public function assessRisk(Request $request, string $caseNumber)
    {
        $data = $request->validate([
            'category_score' => ['required', 'integer', 'min:0', 'max:25'],
            'safety_score' => ['required', 'integer', 'min:0', 'max:25'],
            'repetition_score' => ['required', 'integer', 'min:0', 'max:25'],
            'impact_score' => ['required', 'integer', 'min:0', 'max:25'],
            'reason' => ['nullable', 'string', 'max:300'],
        ]);

        $case = scopedCase($caseNumber);
        $score = $data['category_score'] + $data['safety_score'] + $data['repetition_score'] + $data['impact_score'];
        $risk = $score >= 76 ? 'CRITICAL' : ($score >= 51 ? 'HIGH' : ($score >= 26 ? 'MEDIUM' : 'LOW'));
        $now = now();

        DB::transaction(function () use ($case, $data, $score, $risk, $now) {
            DB::table('case_risk_assessments')->insert([
                'case_id' => $case->id, 'category_score' => $data['category_score'], 'safety_score' => $data['safety_score'],
                'repetition_score' => $data['repetition_score'], 'impact_score' => $data['impact_score'], 'final_score' => $score,
                'risk_level' => $risk, 'assessed_by' => session('user_name', 'Guru BK'), 'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('case_risk_histories')->insert([
                'case_id' => $case->id, 'old_risk_level' => $case->risk_level, 'new_risk_level' => $risk,
                'score' => $score, 'reason' => $data['reason'] ?? null, 'changed_by' => session('user_name', 'Guru BK'),
                'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('cases')->where('id', $case->id)->update(['risk_level' => $risk, 'updated_at' => $now]);

            if ($case->risk_level !== $risk) {
                $config = DB::table('sla_configurations')->where('risk_level', $risk)->first();
                $responseHours = $config ? $config->response_time_hours : 24;
                $resolutionDays = $config ? $config->resolution_time_days : 14;

                $slaUpdate = ['updated_at' => $now];
                if ($case->status === 'PENDING_RESPONSE') {
                    $slaUpdate['response_deadline'] = Carbon::parse($case->opened_at)->addHours($responseHours);
                } else {
                    $initialResponse = DB::table('case_slas')->where('case_id', $case->id)->value('initial_response_at') ?? $now;
                    $slaUpdate['resolution_deadline'] = Carbon::parse($initialResponse)->addDays($resolutionDays);
                }
                DB::table('case_slas')->where('case_id', $case->id)->update($slaUpdate);
            }

            auditAction('ASSESS_RISK', 'CASE', $case->case_number, ['risk_level' => $risk, 'score' => $score]);
            notifyCounselor('RISK_ASSESSED', 'Penilaian risiko tersimpan', $case->case_number.' memiliki level risiko '.$risk.' dengan skor '.$score.'.', $case->case_number, in_array($risk, ['HIGH', 'CRITICAL'], true) ? 'URGENT' : 'NORMAL');
        });

        return back()->with('success', 'Penilaian risiko berhasil disimpan.');
    }

    public function uploadDocument(Request $request, string $caseNumber)
    {
        $data = $request->validate([
            'document_type' => ['required', 'in:EVIDENCE,RESOLUTION'],
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,mp4,mov,mp3,wav'],
        ]);

        $case = scopedCase($caseNumber);
        $file = $request->file('file');
        $path = AppStorage::store($file, 'case-documents', 'private');
        $now = now();

        $payload = [
            'case_id' => $case->id, 'file_name' => $file->getClientOriginalName(), 'file_path' => $path,
            'uploaded_by' => session('user_name', 'Guru BK'), 'created_at' => $now, 'updated_at' => $now,
        ];

        if ($data['document_type'] === 'RESOLUTION') {
            DB::table('case_resolution_documents')->insert($payload + ['verification_status' => 'VALID']);
        } else {
            DB::table('case_evidences')->insert($payload + ['mime_type' => $file->getMimeType(), 'file_size' => $file->getSize()]);
        }

        auditAction('UPLOAD_DOCUMENT', 'CASE', $case->case_number, ['file_name' => $file->getClientOriginalName(), 'document_type' => $data['document_type']]);
        notifyCounselor('DOCUMENT_UPLOADED', 'Dokumen kasus diunggah', $file->getClientOriginalName().' ditambahkan ke '.$caseNumber.'.', $caseNumber);

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function downloadDocument(string $caseNumber, string $documentType, int $id)
    {
        $case = scopedCase($caseNumber);
        abort_unless(in_array($documentType, ['evidence', 'resolution'], true), 404);

        $table = $documentType === 'evidence' ? 'case_evidences' : 'case_resolution_documents';
        $document = DB::table($table)->where('id', $id)->where('case_id', $case->id)->firstOrFail();

        auditAction('DOWNLOAD_DOCUMENT', 'CASE', $case->case_number, ['document_id' => $id, 'document_type' => $documentType]);
        DB::table('case_access_logs')->insert([
            'user_id' => session('user_id'), 'user_name' => session('user_name'), 'case_id' => $case->id,
            'action' => 'DOWNLOAD_DOCUMENT', 'access_level' => 'CASE_SENSITIVE', 'reason' => 'Dokumen kasus dilihat',
            'ip_address' => request()->ip(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        return AppStorage::response($document->file_path, $document->file_name);
    }

    public function printReport(string $caseNumber)
    {
        $baseCase = scopedCase($caseNumber);

        abort_unless(in_array($baseCase->status, ['RESOLVED', 'CLOSED']), 403, 'Hanya kasus yang sudah selesai yang dapat dicetak.');

        $case = DB::table('cases')
            ->join('reports', 'cases.report_id', '=', 'reports.id')
            ->leftJoin('reporter_identities', 'reports.id', '=', 'reporter_identities.report_id')
            ->leftJoin('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->where('cases.id', $baseCase->id)
            ->select('cases.*', 'reports.reporter_role', 'reports.identity_mode', 'reports.description', 'reports.submitted_at', 'reporter_identities.full_name as reporter_name', 'reporter_identities.contact as reporter_contact', 'reporter_identities.access_level as reporter_access_level', 'case_slas.status as sla_status', 'case_slas.response_deadline', 'case_slas.initial_response_at', 'case_slas.resolution_status', 'case_slas.resolution_deadline')
            ->first();

        $schoolName = DB::table('users')->where('school_npsn', $case->school_npsn)->value('school_name');
        $case->school_name = $schoolName ?? '-';

        if ($case->sla_status !== 'OVERDUE') {
            if ($case->initial_response_at && $case->response_deadline && Carbon::parse($case->initial_response_at)->gt(Carbon::parse($case->response_deadline))) {
                $case->sla_status = 'OVERDUE';
            }
        }

        $participants = DB::table('case_participants')->where('case_id', $case->id)->get();
        $history = DB::table('case_status_histories')->where('case_id', $case->id)->orderBy('created_at', 'asc')->get();
        $actions = DB::table('case_access_logs')->where('case_id', $case->id)->orderBy('created_at', 'asc')->get();
        $notes = DB::table('case_notes')->where('case_id', $case->id)->orderBy('created_at', 'asc')->get();

        DB::table('case_access_logs')->insert([
            'user_id' => session('user_id'), 'user_name' => session('user_name'), 'case_id' => $case->id,
            'action' => 'PRINT_REPORT', 'access_level' => 'CASE_FULL', 'reason' => 'Mencetak laporan kasus',
            'ip_address' => request()->ip(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        return view('cases.print', compact('case', 'participants', 'history', 'actions', 'notes'));
    }

    public function updateStatus(Request $request, string $caseNumber)
    {
        $data = $request->validate([
            'status' => ['required', 'in:UNDER_VERIFICATION,IN_HANDLING,WAITING_REPORTER_INFO,RESOLVED,CLOSED'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $allowed = [
            'PENDING_RESPONSE' => ['UNDER_VERIFICATION'],
            'UNDER_VERIFICATION' => ['IN_HANDLING', 'WAITING_REPORTER_INFO', 'RESOLVED'],
            'WAITING_REPORTER_INFO' => ['UNDER_VERIFICATION', 'IN_HANDLING'],
            'IN_HANDLING' => ['WAITING_REPORTER_INFO', 'RESOLVED'],
            'RESOLVED' => ['CLOSED'],
        ];

        $case = scopedCase($caseNumber);
        abort_unless(in_array($data['status'], $allowed[$case->status] ?? [], true), 422, 'Transisi status tidak diizinkan.');

        if ($data['status'] === 'CLOSED') {
            abort_unless(
                DB::table('case_resolution_documents')->where('case_id', $case->id)->where('verification_status', 'VALID')->exists(),
                422,
                'Kasus tidak dapat ditutup karena belum memiliki dokumen penyelesaian yang valid.'
            );
        }

        DB::transaction(function () use ($case, $data) {
            $now = now();

            $caseUpdate = [
                'status' => $data['status'],
                'updated_at' => $now,
            ];
            if ($data['status'] === 'RESOLVED') {
                $caseUpdate['resolved_at'] = $now;
            } elseif ($data['status'] === 'CLOSED') {
                $caseUpdate['closed_at'] = $now;
            }

            DB::table('cases')->where('id', $case->id)->update($caseUpdate);

            DB::table('case_status_histories')->insert([
                'case_id' => $case->id, 'old_status' => $case->status, 'new_status' => $data['status'],
                'reason' => $data['reason'] ?? null, 'changed_by' => session('user_name', 'Guru BK'),
                'created_at' => $now, 'updated_at' => $now,
            ]);

            DB::table('case_actions')->insert([
                'case_id' => $case->id,
                'action_type' => $data['status'] === 'UNDER_VERIFICATION' ? 'VERIFICATION' : ($data['status'] === 'IN_HANDLING' ? 'HANDLING' : 'RESOLUTION'),
                'description' => $data['reason'] ?: 'Status kasus diperbarui menjadi '.str_replace('_', ' ', $data['status']),
                'performed_by' => session('user_name', 'Guru BK'), 'created_at' => $now, 'updated_at' => $now,
            ]);

            if ($case->status === 'PENDING_RESPONSE') {
                $config = DB::table('sla_configurations')->where('risk_level', $case->risk_level)->first();
                $resolutionDays = $config ? $config->resolution_time_days : 14;

                $currentSla = DB::table('case_slas')->where('case_id', $case->id)->first();
                $isResponseOverdue = false;
                if ($currentSla) {
                    $isResponseOverdue = ($currentSla->status === 'OVERDUE')
                        || ($currentSla->response_deadline && Carbon::parse($currentSla->response_deadline)->isPast());
                }
                $newResponseStatus = $isResponseOverdue ? 'OVERDUE' : 'ON_TIME';

                DB::table('case_slas')->where('case_id', $case->id)->update([
                    'status' => $newResponseStatus,
                    'initial_response_at' => $now,
                    'overdue_at' => $isResponseOverdue ? ($currentSla->overdue_at ?? $now) : null,
                    'resolution_status' => 'ON_TIME',
                    'resolution_deadline' => $now->copy()->addDays($resolutionDays),
                    'updated_at' => $now,
                ]);
            }

            if (in_array($data['status'], ['RESOLVED', 'CLOSED'])) {
                DB::table('case_slas')->where('case_id', $case->id)->update([
                    'resolution_status' => 'COMPLETED',
                    'resolved_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            notifyCounselor('STATUS_CHANGED', 'Status kasus diperbarui', $case->case_number.' sekarang '.str_replace('_', ' ', $data['status']).'.', $case->case_number, 'HIGH');
            auditAction('CHANGE_STATUS', 'CASE', $case->case_number, ['status' => $data['status']], ['status' => $case->status]);
        });

        return back()->with('success', 'Status kasus berhasil diperbarui.');
    }

    public function processAction(Request $request, string $caseNumber)
    {
        $data = $request->validate([
            'status' => ['nullable', 'in:UNDER_VERIFICATION,IN_HANDLING,WAITING_REPORTER_INFO,RESOLVED,CLOSED'],
            'status_reason' => ['nullable', 'string', 'max:500'],
            'note_type' => ['nullable', 'in:VERIFICATION,HANDLING,COUNSELING,FOLLOW_UP,INTERNAL', 'required_with:note_content'],
            'note_content' => ['nullable', 'string', 'min:5', 'max:2000', 'required_with:note_type'],
            'document_type' => ['nullable', 'in:EVIDENCE,RESOLUTION', 'required_with:document_file'],
            'document_file' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,mp4,mov,mp3,wav', 'required_with:document_type'],
        ]);

        $case = scopedCase($caseNumber);
        $now = now();
        $messages = [];

        DB::transaction(function () use ($case, $data, $now, &$messages, $request) {
            // 1. Update Status
            if (! empty($data['status'])) {
                $allowed = [
                    'PENDING_RESPONSE' => ['UNDER_VERIFICATION'],
                    'UNDER_VERIFICATION' => ['IN_HANDLING', 'WAITING_REPORTER_INFO', 'RESOLVED'],
                    'WAITING_REPORTER_INFO' => ['UNDER_VERIFICATION', 'IN_HANDLING'],
                    'IN_HANDLING' => ['WAITING_REPORTER_INFO', 'RESOLVED'],
                    'RESOLVED' => ['CLOSED'],
                ];

                if (in_array($data['status'], $allowed[$case->status] ?? [], true)) {
                    // Check resolution document if closing
                    if ($data['status'] === 'CLOSED') {
                        $hasDoc = DB::table('case_resolution_documents')->where('case_id', $case->id)->where('verification_status', 'VALID')->exists();
                        $uploadingResolution = ! empty($data['document_file']) && $data['document_type'] === 'RESOLUTION';

                        if (! $hasDoc && ! $uploadingResolution) {
                            throw ValidationException::withMessages(['status' => 'Kasus tidak dapat ditutup karena belum memiliki dokumen penyelesaian yang valid.']);
                        }
                    }

                    $caseUpdate = [
                        'status' => $data['status'],
                        'updated_at' => $now,
                    ];
                    if ($data['status'] === 'RESOLVED') {
                        $caseUpdate['resolved_at'] = $now;
                    } elseif ($data['status'] === 'CLOSED') {
                        $caseUpdate['closed_at'] = $now;
                    }

                    DB::table('cases')->where('id', $case->id)->update($caseUpdate);

                    DB::table('case_status_histories')->insert([
                        'case_id' => $case->id, 'old_status' => $case->status, 'new_status' => $data['status'],
                        'reason' => $data['status_reason'] ?? null, 'changed_by' => session('user_name', 'Guru BK'),
                        'created_at' => $now, 'updated_at' => $now,
                    ]);

                    DB::table('case_actions')->insert([
                        'case_id' => $case->id,
                        'action_type' => $data['status'] === 'UNDER_VERIFICATION' ? 'VERIFICATION' : ($data['status'] === 'IN_HANDLING' ? 'HANDLING' : 'RESOLUTION'),
                        'description' => $data['status_reason'] ?: 'Status kasus diperbarui menjadi '.str_replace('_', ' ', $data['status']),
                        'performed_by' => session('user_name', 'Guru BK'), 'created_at' => $now, 'updated_at' => $now,
                    ]);

                    if ($case->status === 'PENDING_RESPONSE') {
                        $config = DB::table('sla_configurations')->where('risk_level', $case->risk_level)->first();
                        $resolutionDays = $config ? $config->resolution_time_days : 14;

                        $currentSla = DB::table('case_slas')->where('case_id', $case->id)->first();
                        $isResponseOverdue = false;
                        if ($currentSla) {
                            $isResponseOverdue = ($currentSla->status === 'OVERDUE')
                                || ($currentSla->response_deadline && Carbon::parse($currentSla->response_deadline)->isPast());
                        }
                        $newResponseStatus = $isResponseOverdue ? 'OVERDUE' : 'ON_TIME';

                        DB::table('case_slas')->where('case_id', $case->id)->update([
                            'status' => $newResponseStatus,
                            'initial_response_at' => $now,
                            'overdue_at' => $isResponseOverdue ? ($currentSla->overdue_at ?? $now) : null,
                            'resolution_status' => 'ON_TIME',
                            'resolution_deadline' => $now->copy()->addDays($resolutionDays),
                            'updated_at' => $now,
                        ]);
                    }

                    if (in_array($data['status'], ['RESOLVED', 'CLOSED'])) {
                        DB::table('case_slas')->where('case_id', $case->id)->update([
                            'resolution_status' => 'COMPLETED',
                            'resolved_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }

                    notifyCounselor('STATUS_CHANGED', 'Status kasus diperbarui', $case->case_number.' sekarang '.str_replace('_', ' ', $data['status']).'.', $case->case_number, 'HIGH');
                    auditAction('CHANGE_STATUS', 'CASE', $case->case_number, ['status' => $data['status']], ['status' => $case->status]);
                    $messages[] = 'Status berhasil diperbarui.';
                }
            } elseif ($case->status === 'PENDING_RESPONSE' && (! empty($data['note_content']) || ! empty($data['document_file']))) {
                // Counselor responded by adding a note or document without explicitly picking status dropdown
                DB::table('cases')->where('id', $case->id)->update([
                    'status' => 'UNDER_VERIFICATION',
                    'updated_at' => $now,
                ]);

                DB::table('case_status_histories')->insert([
                    'case_id' => $case->id, 'old_status' => 'PENDING_RESPONSE', 'new_status' => 'UNDER_VERIFICATION',
                    'reason' => 'Respons awal diberikan oleh Guru BK melalui catatan/dokumen', 'changed_by' => session('user_name', 'Guru BK'),
                    'created_at' => $now, 'updated_at' => $now,
                ]);

                DB::table('case_actions')->insert([
                    'case_id' => $case->id,
                    'action_type' => 'VERIFICATION',
                    'description' => 'Respons awal dicatat dalam penanganan kasus',
                    'performed_by' => session('user_name', 'Guru BK'), 'created_at' => $now, 'updated_at' => $now,
                ]);

                $config = DB::table('sla_configurations')->where('risk_level', $case->risk_level)->first();
                $resolutionDays = $config ? $config->resolution_time_days : 14;

                $currentSla = DB::table('case_slas')->where('case_id', $case->id)->first();
                $isResponseOverdue = false;
                if ($currentSla) {
                    $isResponseOverdue = ($currentSla->status === 'OVERDUE')
                        || ($currentSla->response_deadline && Carbon::parse($currentSla->response_deadline)->isPast());
                }
                $newResponseStatus = $isResponseOverdue ? 'OVERDUE' : 'ON_TIME';

                DB::table('case_slas')->where('case_id', $case->id)->update([
                    'status' => $newResponseStatus,
                    'initial_response_at' => $now,
                    'overdue_at' => $isResponseOverdue ? ($currentSla->overdue_at ?? $now) : null,
                    'resolution_status' => 'ON_TIME',
                    'resolution_deadline' => $now->copy()->addDays($resolutionDays),
                    'updated_at' => $now,
                ]);

                $messages[] = 'Respons awal berhasil dicatat dan status masuk ke Verifikasi.';
            }

            // 2. Add Note
            if (! empty($data['note_content']) && ! empty($data['note_type'])) {
                DB::table('case_notes')->insert([
                    'case_id' => $case->id, 'note_type' => $data['note_type'], 'content' => $data['note_content'],
                    'visibility' => 'PRIVATE_BK', 'created_by' => session('user_name', 'Guru BK'), 'created_at' => $now, 'updated_at' => $now,
                ]);

                auditAction('CREATE_NOTE', 'CASE', $case->case_number, ['note_type' => $data['note_type']]);
                notifyCounselor('NOTE_ADDED', 'Catatan kasus ditambahkan', 'Catatan '.strtolower($data['note_type']).' ditambahkan ke '.$case->case_number.'.', $case->case_number);
                $messages[] = 'Catatan berhasil disimpan.';
            }

            // 3. Upload Document
            if ($request->hasFile('document_file') && ! empty($data['document_type'])) {
                $file = $request->file('document_file');
                $path = AppStorage::store($file, 'case-documents', 'private');

                $payload = [
                    'case_id' => $case->id, 'file_name' => $file->getClientOriginalName(), 'file_path' => $path,
                    'uploaded_by' => session('user_name', 'Guru BK'), 'created_at' => $now, 'updated_at' => $now,
                ];

                if ($data['document_type'] === 'RESOLUTION') {
                    DB::table('case_resolution_documents')->insert($payload + ['verification_status' => 'VALID']);
                } else {
                    DB::table('case_evidences')->insert($payload + ['mime_type' => $file->getMimeType(), 'file_size' => $file->getSize()]);
                }

                auditAction('UPLOAD_DOCUMENT', 'CASE', $case->case_number, ['file_name' => $file->getClientOriginalName(), 'document_type' => $data['document_type']]);
                notifyCounselor('DOCUMENT_UPLOADED', 'Dokumen kasus diunggah', $file->getClientOriginalName().' ditambahkan ke '.$case->case_number.'.', $case->case_number);
                $messages[] = 'Dokumen berhasil diunggah.';
            }
        });

        if (empty($messages)) {
            return back()->with('info', 'Tidak ada tindakan yang dilakukan.');
        }

        return back()->with('success', implode(' ', $messages));
    }
}
