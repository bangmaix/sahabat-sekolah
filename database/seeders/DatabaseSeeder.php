<?php

namespace Database\Seeders;

use App\Helpers\AConnect;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $sekolahList = collect((new AConnect)->getSekolahList());

        // Asumsi data yang ada di API, jika tidak ada fallback ke NPSN sembarang
        $npsnMin = '10307512';
        $npsnMts = '10307512';

        DB::table('users')->insert([
            ['name' => 'Bu Ratna Sari', 'email' => 'bk@sahabat.test', 'password' => Hash::make('password'), 'role' => 'COUNSELOR', 'school_npsn' => $npsnMin, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kepala Sekolah', 'email' => 'kepala@sahabat.test', 'password' => Hash::make('password'), 'role' => 'PRINCIPAL', 'school_npsn' => $npsnMin, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $demoCases = [
            ['SS-2026-000125', 'Cyberbullying', 'Tinggi', 'PENDING_RESPONSE', 'OVERDUE', 'MIN Kota Bukittinggi', 'Siswa menerima ancaman perundungan siber berulang di media sosial dan belum direspon Guru BK.'],
            ['SS-2026-000124', 'Fisik', 'Tinggi', 'PENDING_RESPONSE', 'WARNING', 'MIN Kota Bukittinggi', 'Terjadi intimidasi fisik saat jam istirahat sekolah yang mendekati batas waktu respons.'],
            ['SS-2026-000123', 'Cyberbullying', 'Tinggi', 'IN_HANDLING', 'OVERDUE', 'MIN Kota Bukittinggi', 'Saya melihat pesan intimidatif di grup kelas.'],
            ['SS-2026-000122', 'Verbal', 'Sedang', 'UNDER_VERIFICATION', 'WARNING', 'MIN Kota Bukittinggi', 'Teman saya sering diberi julukan yang merendahkan.'],
            ['SS-2026-000121', 'Sosial', 'Sedang', 'PENDING_RESPONSE', 'ON_TIME', 'MTsN 1 Kota Bukittinggi', 'Saya mengetahui ada pengucilan dalam kelompok belajar.'],
            ['SS-2026-000120', 'Fisik', 'Tinggi', 'IN_HANDLING', 'ON_TIME', 'MTsN 1 Kota Bukittinggi', 'Terjadi dorongan berulang saat jam istirahat.'],
            ['SS-2026-000119', 'Diskriminasi', 'Rendah', 'CLOSED', 'COMPLETED', 'MIN Kota Bukittinggi', 'Komentar yang mempermalukan kondisi pribadi.'],
        ];

        foreach ($demoCases as [$number, $category, $risk, $status, $sla, $school, $description]) {
            $schoolNpsn = $school === 'MIN Kota Bukittinggi' ? $npsnMin : $npsnMts;
            $openedAt = $now->copy()->subDays(rand(1, 9));
            $isPending = ($status === 'PENDING_RESPONSE');
            $isFinished = in_array($status, ['RESOLVED', 'CLOSED'], true);

            $reportId = DB::table('reports')->insertGetId([
                'school_npsn' => $schoolNpsn, 'report_number' => $number, 'reporter_role' => 'WITNESS',
                'identity_mode' => 'CONFIDENTIAL', 'category' => $category, 'description' => $description,
                'submitted_at' => $openedAt, 'created_at' => $openedAt, 'updated_at' => $now,
            ]);
            $caseId = DB::table('cases')->insertGetId([
                'school_npsn' => $schoolNpsn, 'report_id' => $reportId, 'case_number' => $number,
                'category' => $category, 'risk_level' => $risk, 'status' => $status,
                'opened_at' => $openedAt,
                'resolved_at' => $isFinished ? $now->copy()->subDays(2) : null,
                'closed_at' => ($status === 'CLOSED') ? $now->copy()->subDay() : null,
                'created_at' => $openedAt, 'updated_at' => $now,
            ]);

            // SLA deadlines and statuses
            $responseDeadline = $now->copy()->addDay();
            if ($isPending) {
                if ($sla === 'OVERDUE') {
                    $responseDeadline = $now->copy()->subHours(4);
                } elseif ($sla === 'WARNING') {
                    $responseDeadline = $now->copy()->addHours(2);
                }
            }

            DB::table('case_slas')->insert([
                'case_id' => $caseId,
                'status' => $isPending ? $sla : 'COMPLETED',
                'response_deadline' => $responseDeadline,
                'initial_response_at' => $isPending ? null : $now->copy()->subDays(2),
                'resolution_deadline' => $now->copy()->addDays(7),
                'resolution_status' => $isPending ? 'ON_TRACK' : ($isFinished ? 'COMPLETED' : ($sla === 'OVERDUE' ? 'OVERDUE' : ($sla === 'WARNING' ? 'WARNING' : 'ON_TRACK'))),
                'resolved_at' => $isFinished ? $now->copy()->subDays(2) : null,
                'created_at' => $openedAt,
                'updated_at' => $now,
            ]);
        }
    }
}
