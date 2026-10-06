<?php

namespace App\Http\Controllers;

use App\Helpers\AConnect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CaseController extends Controller
{
    public function show(string $caseNumber)
    {
        $schoolsMap = (new AConnect)->getSekolahMap();
        $case = DB::table('cases')
            ->join('reports', 'cases.report_id', '=', 'reports.id')
            ->leftJoin('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->where('cases.case_number', $caseNumber)
            ->where('cases.school_npsn', session('school_npsn'))
            ->leftJoin('reporter_identities', 'reports.id', '=', 'reporter_identities.report_id')
            ->select('cases.*', 'reports.reporter_role', 'reports.identity_mode', 'reports.description', 'reports.submitted_at', 'reporter_identities.full_name as reporter_name', 'reporter_identities.contact as reporter_contact', 'reporter_identities.access_level as reporter_access_level', 'case_slas.status as sla_status', 'case_slas.response_deadline')
            ->firstOrFail();

        $case->school_name = $schoolsMap[$case->school_npsn] ?? '-';

        DB::table('case_access_logs')->insert([
            'user_id' => session('user_id'), 'user_name' => session('user_name'), 'case_id' => $case->id,
            'action' => 'VIEW_CASE', 'access_level' => session('user_role') === 'PRINCIPAL' ? 'CASE_SUMMARY' : 'CASE_FULL',
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
        $participants = DB::table('case_participants')->where('case_id', $case->id)->where('status', 'ACTIVE')->orderBy('participant_type')->get();
        $parentInvolvements = DB::table('case_parent_involvements')
            ->join('parents', 'case_parent_involvements.parent_id', '=', 'parents.id')
            ->where('case_parent_involvements.case_id', $case->id)
            ->select('case_parent_involvements.*', 'parents.full_name as parent_name', 'parents.contact as parent_contact')
            ->latest()
            ->get();

        return view('cases.show', compact('case', 'history', 'actions', 'evidences', 'resolutionDocuments', 'riskAssessment', 'riskHistory', 'notes', 'escalations', 'participants', 'parentInvolvements'));
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
        ]);

        $case = scopedCase($caseNumber);
        DB::table('case_participants')->insert([
            'case_id' => $case->id, 'participant_type' => $data['participant_type'], 'display_name' => $data['display_name'],
            'identity_visibility' => $data['identity_visibility'], 'created_at' => now(), 'updated_at' => now(),
        ]);

        auditAction('ADD_PARTICIPANT', 'CASE', $case->case_number, ['participant_type' => $data['participant_type'], 'visibility' => $data['identity_visibility']]);

        return back()->with('success', 'Pihak terkait berhasil ditambahkan.');
    }

    public function escalate(Request $request, string $caseNumber)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);

        $case = scopedCase($caseNumber);

        DB::table('case_escalations')->insert([
            'case_id' => $case->id, 'escalation_type' => 'MANUAL', 'reason' => $data['reason'],
            'escalated_by' => session('user_name', 'Bu Ratna Sari'), 'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('notifications')->insert([
            'recipient_name' => 'Kepala Sekolah', 'type' => 'MANUAL_ESCALATION',
            'title' => 'Kasus dieskalasikan', 'message' => $case->case_number.' membutuhkan perhatian Kepala Sekolah.',
            'priority' => 'URGENT', 'case_number' => $case->case_number, 'created_at' => now(), 'updated_at' => now(),
        ]);

        auditAction('ESCALATE_CASE', 'CASE', $case->case_number, ['reason' => $data['reason']]);

        return back()->with('success', 'Kasus berhasil dieskalasikan kepada Kepala Sekolah.');
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
            'visibility' => 'PRIVATE_BK', 'created_by' => 'Bu Ratna Sari', 'created_at' => now(), 'updated_at' => now(),
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
                'risk_level' => $risk, 'assessed_by' => 'Bu Ratna Sari', 'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('case_risk_histories')->insert([
                'case_id' => $case->id, 'old_risk_level' => $case->risk_level, 'new_risk_level' => $risk,
                'score' => $score, 'reason' => $data['reason'] ?? null, 'changed_by' => 'Bu Ratna Sari',
                'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('cases')->where('id', $case->id)->update(['risk_level' => $risk, 'updated_at' => $now]);
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
        $path = $file->store('case-documents', 'local');
        $now = now();

        $payload = [
            'case_id' => $case->id, 'file_name' => $file->getClientOriginalName(), 'file_path' => $path,
            'uploaded_by' => 'Bu Ratna Sari', 'created_at' => $now, 'updated_at' => $now,
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

        abort_unless(Storage::disk('local')->exists($document->file_path), 404, 'File tidak ditemukan.');

        auditAction('DOWNLOAD_DOCUMENT', 'CASE', $case->case_number, ['document_id' => $id, 'document_type' => $documentType]);
        DB::table('case_access_logs')->insert([
            'user_id' => session('user_id'), 'user_name' => session('user_name'), 'case_id' => $case->id,
            'action' => 'DOWNLOAD_DOCUMENT', 'access_level' => 'CASE_SENSITIVE', 'reason' => 'Dokumen kasus diunduh',
            'ip_address' => request()->ip(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    public function updateStatus(Request $request, string $caseNumber)
    {
        $data = $request->validate([
            'status' => ['required', 'in:UNDER_VERIFICATION,IN_HANDLING,RESOLVED,CLOSED'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $allowed = [
            'PENDING_RESPONSE' => ['UNDER_VERIFICATION'],
            'UNDER_VERIFICATION' => ['IN_HANDLING', 'RESOLVED'],
            'IN_HANDLING' => ['RESOLVED'],
            'RESOLVED' => ['CLOSED'],
        ];

        $case = scopedCase($caseNumber);
        abort_unless(in_array($data['status'], $allowed[$case->status] ?? [], true), 422, 'Transisi status tidak diizinkan.');

        if ($data['status'] === 'CLOSED') {
            if (!DB::table('case_resolution_documents')->where('case_id', $case->id)->where('verification_status', 'VALID')->exists()) {
                return back()->with('error', 'Kasus tidak dapat ditutup karena belum memiliki dokumen penyelesaian yang valid.');
            }
        }

        DB::transaction(function () use ($case, $data) {
            $now = now();

            DB::table('cases')->where('id', $case->id)->update([
                'status' => $data['status'],
                'updated_at' => $now,
            ]);

            DB::table('case_status_histories')->insert([
                'case_id' => $case->id, 'old_status' => $case->status, 'new_status' => $data['status'],
                'reason' => $data['reason'] ?? null, 'changed_by' => 'Bu Ratna Sari',
                'created_at' => $now, 'updated_at' => $now,
            ]);

            DB::table('case_actions')->insert([
                'case_id' => $case->id,
                'action_type' => $data['status'] === 'UNDER_VERIFICATION' ? 'VERIFICATION' : ($data['status'] === 'IN_HANDLING' ? 'HANDLING' : 'RESOLUTION'),
                'description' => $data['reason'] ?: 'Status kasus diperbarui menjadi '.str_replace('_', ' ', $data['status']),
                'performed_by' => 'Bu Ratna Sari', 'created_at' => $now, 'updated_at' => $now,
            ]);

            if ($case->status === 'PENDING_RESPONSE') {
                $config = DB::table('sla_configurations')->where('risk_level', $case->risk_level)->first();
                $resolutionDays = $config ? $config->resolution_time_days : 14;

                DB::table('case_slas')->where('case_id', $case->id)->update([
                    'status' => 'COMPLETED', 
                    'initial_response_at' => $now, 
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
}
