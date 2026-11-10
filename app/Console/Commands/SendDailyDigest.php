<?php

namespace App\Console\Commands;

use App\Helpers\AConnect;
use App\Mail\DailyDigestMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDailyDigest extends Command
{
    protected $signature = 'sahabat:send-daily-digest {--school= : Filter spesifik berdasarkan NPSN sekolah}';

    protected $description = 'Kirimkan email rekapitulasi harian pagi (07:00 WIB) kepada Guru BK dan Kepala Sekolah';

    public function handle(): int
    {
        $this->info('Memulai pengiriman Daily Digest Pagi...');

        $schoolFilter = $this->option('school');
        $schoolsMap = (new AConnect)->getSekolahMap();
        $now = now();
        $dateFormatted = $now->locale('id')->isoFormat('dddd, D MMMM Y');

        $schoolsQuery = DB::table('users')
            ->whereNotNull('school_npsn')
            ->where('school_npsn', '!=', '')
            ->distinct()
            ->pluck('school_npsn');

        if ($schoolFilter) {
            $schoolsQuery = collect([$schoolFilter]);
        }

        $sentCount = 0;

        foreach ($schoolsQuery as $npsn) {
            $staff = DB::table('users')
                ->where('school_npsn', $npsn)
                ->whereIn('role', ['COUNSELOR', 'PRINCIPAL'])
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->where(function ($q) {
                    $q->where('status', 'ACTIVE')->orWhereNull('status');
                })
                ->get();

            if ($staff->isEmpty()) {
                continue;
            }

            $schoolName = DB::table('users')
                ->where('school_npsn', $npsn)
                ->whereNotNull('school_name')
                ->value('school_name') ?? ($schoolsMap[$npsn] ?? "Sekolah NPSN {$npsn}");

            // Pending response cases
            $pendingCases = DB::table('cases')
                ->leftJoin('case_slas', 'cases.id', '=', 'case_slas.case_id')
                ->where('cases.school_npsn', $npsn)
                ->where('cases.status', 'PENDING_RESPONSE')
                ->select('cases.case_number', 'cases.category', 'cases.risk_level', 'cases.assigned_to', 'case_slas.response_deadline')
                ->orderBy('case_slas.response_deadline')
                ->get()
                ->map(fn ($c) => (array) $c)
                ->all();

            // Overdue cases
            $overdueCases = DB::table('cases')
                ->join('case_slas', 'cases.id', '=', 'case_slas.case_id')
                ->where('cases.school_npsn', $npsn)
                ->whereIn('cases.status', ['PENDING_RESPONSE', 'UNDER_VERIFICATION', 'IN_HANDLING'])
                ->where(function ($q) use ($now) {
                    $q->where('case_slas.status', 'OVERDUE')
                        ->orWhere('case_slas.resolution_status', 'OVERDUE')
                        ->orWhere(function ($sub) use ($now) {
                            $sub->where('cases.status', 'PENDING_RESPONSE')
                                ->where('case_slas.response_deadline', '<=', $now);
                        });
                })
                ->select('cases.case_number', 'cases.category', 'cases.risk_level', 'cases.assigned_to', 'case_slas.response_deadline')
                ->get()
                ->map(fn ($c) => (array) $c)
                ->all();

            $inHandlingCount = DB::table('cases')
                ->where('school_npsn', $npsn)
                ->where('status', 'IN_HANDLING')
                ->count();

            // Jika tidak ada kasus aktif dan tidak ada overdue, lewati atau kirim ringkasan bersih
            $digestData = [
                'school_name' => $schoolName,
                'school_npsn' => $npsn,
                'date' => $dateFormatted,
                'pending_cases' => $pendingCases,
                'overdue_cases' => $overdueCases,
                'in_handling_count' => $inHandlingCount,
                'dashboard_url' => route('dashboard'),
            ];

            foreach ($staff as $user) {
                try {
                    Mail::to($user->email)->queue(new DailyDigestMail($digestData, $user->name));
                    $sentCount++;
                } catch (\Throwable $e) {
                    Log::error("Gagal mengirim daily digest ke {$user->email}: ".$e->getMessage());
                }
            }
        }

        $this->info("Daily Digest selesai: {$sentCount} email diantrekan.");

        return self::SUCCESS;
    }
}
