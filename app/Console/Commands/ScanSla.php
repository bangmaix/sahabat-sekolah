<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ScanSla extends Command
{
    protected $signature = 'sahabat:sla-scan';

    protected $description = 'Refreshes response SLA statuses and creates overdue alerts';

    public function handle(): int
    {
        $now = now();
        $warningLimit = $now->copy()->addHours(6);
        $updated = 0;

        $slas = DB::table('case_slas')
            ->join('cases', 'case_slas.case_id', '=', 'cases.id')
            ->whereIn('cases.status', ['PENDING_RESPONSE', 'UNDER_VERIFICATION', 'IN_HANDLING'])
            ->select('case_slas.*', 'cases.case_number', 'cases.status as case_status', 'cases.school_npsn')
            ->get();

        // Cache counselor names per school to avoid repeated queries
        $counselorNameCache = [];

        foreach ($slas as $sla) {
            $nextStatus = null;
            $priority = 'NORMAL';
            $title = null;
            $message = null;

            $isResponseSla = $sla->case_status === 'PENDING_RESPONSE';

            if ($isResponseSla) {
                if ($sla->response_deadline <= $now && $sla->status !== 'OVERDUE') {
                    $nextStatus = 'OVERDUE';
                    $priority = 'URGENT';
                    $title = 'SLA respons terlewati';
                    $message = $sla->case_number.' belum menerima respons awal.';
                } elseif ($sla->response_deadline <= $warningLimit && $sla->status === 'ON_TIME') {
                    $nextStatus = 'WARNING';
                    $priority = 'HIGH';
                    $title = 'SLA mendekati batas waktu';
                    $message = $sla->case_number.' membutuhkan respons awal segera.';
                }
            } else {
                if ($sla->resolution_deadline && $sla->resolution_deadline <= $now && $sla->resolution_status !== 'OVERDUE') {
                    $nextStatus = 'OVERDUE';
                    $priority = 'URGENT';
                    $title = 'SLA penyelesaian terlewati';
                    $message = $sla->case_number.' telah melewati batas waktu penyelesaian.';
                } elseif ($sla->resolution_deadline && $sla->resolution_deadline <= $warningLimit && $sla->resolution_status === 'ON_TIME') {
                    $nextStatus = 'WARNING';
                    $priority = 'HIGH';
                    $title = 'SLA penyelesaian mendekati batas waktu';
                    $message = $sla->case_number.' harus segera diselesaikan.';
                }
            }

            if ($nextStatus) {
                $schoolNpsn = (string) ($sla->school_npsn ?? '');
                if ($schoolNpsn !== '' && ! isset($counselorNameCache[$schoolNpsn])) {
                    $counselorName = DB::table('users')
                        ->where('school_npsn', $schoolNpsn)
                        ->where('role', 'COUNSELOR')
                        ->value('name');

                    if (! $counselorName) {
                        $counselorName = DB::table('users')
                            ->where('school_npsn', $schoolNpsn)
                            ->where('role', 'PRINCIPAL')
                            ->value('name');
                    }

                    $counselorNameCache[$schoolNpsn] = $counselorName ?: 'Guru BK';
                }

                $recipientName = ($schoolNpsn !== '' && isset($counselorNameCache[$schoolNpsn]))
                    ? $counselorNameCache[$schoolNpsn]
                    : 'Guru BK';

                $reminderType = $nextStatus === 'OVERDUE' ? 'OVERDUE' : 'WARNING';

                $updateData = ['updated_at' => $now];
                if ($isResponseSla) {
                    $updateData['status'] = $nextStatus;
                    $updateData['warning_at'] = $nextStatus === 'WARNING' ? $now : $sla->warning_at;
                    $updateData['overdue_at'] = $nextStatus === 'OVERDUE' ? $now : null;
                } else {
                    $updateData['resolution_status'] = $nextStatus;
                }

                DB::table('case_slas')->where('id', $sla->id)->update($updateData);
                DB::table('case_reminders')->insertOrIgnore([
                    'case_id' => $sla->case_id, 'reminder_type' => $reminderType,
                    'scheduled_at' => $now, 'sent_at' => $now,
                    'recipient_name' => $recipientName, 'status' => 'SENT',
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                DB::table('notifications')->insert([
                    'recipient_name' => $recipientName, 'type' => 'SLA_'.$nextStatus,
                    'title' => $title, 'message' => $message, 'priority' => $priority,
                    'case_number' => $sla->case_number, 'created_at' => $now, 'updated_at' => $now,
                ]);
                if ($nextStatus === 'OVERDUE') {
                    DB::table('case_escalations')->insert([
                        'case_id' => $sla->case_id, 'escalation_type' => 'AUTOMATIC',
                        'reason' => 'SLA respons awal terlewati.', 'escalated_by' => 'Sistem SLA',
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                    DB::table('notifications')->insert([
                        'recipient_name' => 'Kepala Sekolah', 'type' => 'SLA_ESCALATION',
                        'title' => 'SLA overdue memerlukan perhatian', 'message' => $message,
                        'priority' => 'URGENT', 'case_number' => $sla->case_number,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
                $updated++;
            }
        }

        Cache::put('sahabat_sla_last_scan_at', $now->toIso8601String(), 86400);
        Cache::put('sahabat_sla_last_scan_updated', $updated, 86400);

        $this->info("SLA scan selesai: {$updated} status diperbarui.");

        return self::SUCCESS;
    }
}
