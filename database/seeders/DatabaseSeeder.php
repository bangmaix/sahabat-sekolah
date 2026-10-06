<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
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
        $sekolahList = collect((new \App\Helpers\AConnect())->getSekolahList());
        
        // Asumsi data yang ada di API, jika tidak ada fallback ke NPSN sembarang
        $npsnMin = '10307512';
        $npsnMts = '10307512';

        DB::table('users')->insert([
            ['name' => 'Bu Ratna Sari', 'email' => 'bk@sahabat.test', 'password' => Hash::make('password'), 'role' => 'COUNSELOR', 'school_npsn' => $npsnMin, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kepala Sekolah', 'email' => 'kepala@sahabat.test', 'password' => Hash::make('password'), 'role' => 'PRINCIPAL', 'school_npsn' => $npsnMin, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $demoCases = [
            ['SS-2026-000123', 'Cyberbullying', 'Tinggi', 'IN_HANDLING', 'OVERDUE', 'MIN Kota Bukittinggi', 'Saya melihat pesan intimidatif di grup kelas.'],
            ['SS-2026-000122', 'Verbal', 'Sedang', 'UNDER_VERIFICATION', 'WARNING', 'MIN Kota Bukittinggi', 'Teman saya sering diberi julukan yang merendahkan.'],
            ['SS-2026-000121', 'Sosial', 'Sedang', 'PENDING_RESPONSE', 'ON_TIME', 'MTsN 1 Kota Bukittinggi', 'Saya mengetahui ada pengucilan dalam kelompok belajar.'],
            ['SS-2026-000120', 'Fisik', 'Tinggi', 'IN_HANDLING', 'ON_TIME', 'MTsN 1 Kota Bukittinggi', 'Terjadi dorongan berulang saat jam istirahat.'],
            ['SS-2026-000119', 'Diskriminasi', 'Rendah', 'CLOSED', 'COMPLETED', 'MIN Kota Bukittinggi', 'Komentar yang mempermalukan kondisi pribadi.'],
        ];

        foreach ($demoCases as [$number, $category, $risk, $status, $sla, $school, $description]) {
            $schoolNpsn = $school === 'MIN Kota Bukittinggi' ? $npsnMin : $npsnMts;
            $reportId = DB::table('reports')->insertGetId([
                'school_npsn' => $schoolNpsn, 'report_number' => $number, 'reporter_role' => 'WITNESS',
                'identity_mode' => 'CONFIDENTIAL', 'category' => $category, 'description' => $description,
                'submitted_at' => $now->copy()->subDays(rand(0, 9)), 'created_at' => $now, 'updated_at' => $now,
            ]);
            $caseId = DB::table('cases')->insertGetId([
                'school_npsn' => $schoolNpsn, 'report_id' => $reportId, 'case_number' => $number,
                'category' => $category, 'risk_level' => $risk, 'status' => $status,
                'opened_at' => $now->copy()->subDays(rand(0, 9)), 'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('case_slas')->insert([
                'case_id' => $caseId, 'status' => $sla, 'response_deadline' => $now->copy()->addDay(),
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }
}
