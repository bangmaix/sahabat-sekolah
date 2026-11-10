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

        // SMP Negeri 2 Bukittinggi data
        $smpn2Npsn = '10300074';
        $smpn2Name = 'SMP NEGERI 2 BUKITTINGGI';

        $schoolsMap = (new AConnect)->getSekolahMap();
        if (isset($schoolsMap[$smpn2Npsn])) {
            $smpn2Name = $schoolsMap[$smpn2Npsn];
        }

        // Akun Admin, Guru BK, dan Principal dengan password aman dan sekolah terkait
        $seedUsers = [
            [
                'email' => 'admin@sahabat.test',
                'name' => 'Administrator Sahabat Sekolah',
                'password' => Hash::make('Admin#Sahabat2026!Secure'),
                'role' => 'ADMIN',
                'school_npsn' => null,
                'school_name' => null,
            ],
            [
                'email' => 'bk@sahabat.test',
                'name' => 'Bu Ratna Sari',
                'password' => Hash::make('GuruBK#SMPN2Bkt2026!'),
                'role' => 'COUNSELOR',
                'school_npsn' => $smpn2Npsn,
                'school_name' => $smpn2Name,
            ],
            [
                'email' => 'kepala@sahabat.test',
                'name' => 'Kepala Sekolah SMP Negeri 2 Bukittinggi',
                'password' => Hash::make('Kepsek#SMPN2Bkt2026!'),
                'role' => 'PRINCIPAL',
                'school_npsn' => $smpn2Npsn,
                'school_name' => $smpn2Name,
            ],
            [
                'email' => 'dinas@sahabat.test',
                'name' => 'Dinas Pendidikan Kota Bukittinggi',
                'password' => Hash::make('Dinas#Bkt2026!Secure'),
                'role' => 'CITY_ADMIN',
                'school_npsn' => null,
                'school_name' => 'Dinas Pendidikan & Kebudayaan Kota Bukittinggi',
            ],
            [
                'email' => 'ppa@sahabat.test',
                'name' => 'UPTD PPA Kota Bukittinggi',
                'password' => Hash::make('Ppa#Bkt2026!Secure'),
                'role' => 'CITY_ADMIN',
                'school_npsn' => null,
                'school_name' => 'UPTD PPA - DP3APPKB Kota Bukittinggi',
            ],
        ];

        foreach ($seedUsers as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                array_merge($user, [
                    'updated_at' => $now,
                ])
            );
        }

        $demoCases = [
            ['SS-2026-000125', 'Cyberbullying', 'Tinggi', 'PENDING_RESPONSE', 'OVERDUE', $smpn2Name, 'Siswa menerima ancaman perundungan siber berulang di media sosial dan belum direspon Guru BK.'],
            ['SS-2026-000124', 'Fisik', 'Tinggi', 'PENDING_RESPONSE', 'WARNING', $smpn2Name, 'Terjadi intimidasi fisik saat jam istirahat sekolah yang mendekati batas waktu respons.'],
            ['SS-2026-000123', 'Cyberbullying', 'Tinggi', 'IN_HANDLING', 'OVERDUE', $smpn2Name, 'Saya melihat pesan intimidatif di grup kelas.'],
            ['SS-2026-000122', 'Verbal', 'Sedang', 'UNDER_VERIFICATION', 'WARNING', $smpn2Name, 'Teman saya sering diberi julukan yang merendahkan.'],
            ['SS-2026-000121', 'Sosial', 'Sedang', 'PENDING_RESPONSE', 'ON_TIME', $smpn2Name, 'Saya mengetahui ada pengucilan dalam kelompok belajar.'],
            ['SS-2026-000120', 'Fisik', 'Tinggi', 'IN_HANDLING', 'ON_TIME', $smpn2Name, 'Terjadi dorongan berulang saat jam istirahat.'],
            ['SS-2026-000119', 'Diskriminasi', 'Rendah', 'CLOSED', 'COMPLETED', $smpn2Name, 'Komentar yang mempermalukan kondisi pribadi.'],
        ];

        foreach ($demoCases as [$number, $category, $risk, $status, $sla, $school, $description]) {
            $schoolNpsn = $smpn2Npsn;
            $openedAt = $now->copy()->subDays(rand(1, 9));
            $isPending = ($status === 'PENDING_RESPONSE');
            $isFinished = in_array($status, ['RESOLVED', 'CLOSED'], true);

            $existingReport = DB::table('reports')->where('report_number', $number)->first();
            if ($existingReport) {
                DB::table('reports')->where('id', $existingReport->id)->update([
                    'school_npsn' => $schoolNpsn,
                    'category' => $category,
                    'description' => $description,
                    'updated_at' => $now,
                ]);
                $reportId = $existingReport->id;
            } else {
                $reportId = DB::table('reports')->insertGetId([
                    'school_npsn' => $schoolNpsn,
                    'report_number' => $number,
                    'reporter_role' => 'WITNESS',
                    'identity_mode' => 'CONFIDENTIAL',
                    'category' => $category,
                    'description' => $description,
                    'submitted_at' => $openedAt,
                    'created_at' => $openedAt,
                    'updated_at' => $now,
                ]);
            }

            $existingCase = DB::table('cases')->where('case_number', $number)->first();
            if ($existingCase) {
                DB::table('cases')->where('id', $existingCase->id)->update([
                    'school_npsn' => $schoolNpsn,
                    'category' => $category,
                    'risk_level' => $risk,
                    'status' => $status,
                    'updated_at' => $now,
                ]);
                $caseId = $existingCase->id;
            } else {
                $caseId = DB::table('cases')->insertGetId([
                    'school_npsn' => $schoolNpsn,
                    'report_id' => $reportId,
                    'case_number' => $number,
                    'category' => $category,
                    'risk_level' => $risk,
                    'status' => $status,
                    'assigned_to' => 'Bu Ratna Sari',
                    'opened_at' => $openedAt,
                    'resolved_at' => $isFinished ? $now->copy()->subDays(2) : null,
                    'closed_at' => ($status === 'CLOSED') ? $now->copy()->subDay() : null,
                    'created_at' => $openedAt,
                    'updated_at' => $now,
                ]);
            }

            // SLA deadlines and statuses
            $responseDeadline = $now->copy()->addDay();
            if ($isPending) {
                if ($sla === 'OVERDUE') {
                    $responseDeadline = $now->copy()->subHours(4);
                } elseif ($sla === 'WARNING') {
                    $responseDeadline = $now->copy()->addHours(2);
                }
            }

            DB::table('case_slas')->updateOrInsert(
                ['case_id' => $caseId],
                [
                    'status' => $isPending ? $sla : 'COMPLETED',
                    'response_deadline' => $responseDeadline,
                    'initial_response_at' => $isPending ? null : $now->copy()->subDays(2),
                    'resolution_deadline' => $now->copy()->addDays(7),
                    'resolution_status' => $isPending ? 'ON_TRACK' : ($isFinished ? 'COMPLETED' : ($sla === 'OVERDUE' ? 'OVERDUE' : ($sla === 'WARNING' ? 'WARNING' : 'ON_TRACK'))),
                    'resolved_at' => $isFinished ? $now->copy()->subDays(2) : null,
                    'created_at' => $openedAt,
                    'updated_at' => $now,
                ]
            );
        }

        // Seed contoh prestasi siswa untuk SMP Negeri 2 Bukittinggi
        $counselorUser = DB::table('users')->where('email', 'bk@sahabat.test')->first();
        $categoriesMap = DB::table('achievement_categories')->pluck('id', 'code');

        if ($categoriesMap->isNotEmpty()) {
            $sampleAchievements = [
                [
                    'school_npsn' => $smpn2Npsn,
                    'nisn' => '0112345678',
                    'student_name' => 'Muhammad Fajar Pratama',
                    'class_name' => '8A',
                    'semester_id' => '20261',
                    'category_id' => $categoriesMap['AKADEMIK'] ?? 1,
                    'achievement_name' => 'Olimpiade Sains Nasional (OSN) Matematika Tingkat Kota',
                    'achievement_level' => 'KOTA',
                    'ranking' => 'Juara 1',
                    'organizer' => 'Dinas Pendidikan dan Kebudayaan Kota Bukittinggi',
                    'achievement_date' => $now->copy()->subMonths(2)->format('Y-m-d'),
                    'description' => 'Meraih medali emas dan predikat peserta terbaik seleksi OSN Matematika jenjang SMP se-Kota Bukittinggi.',
                    'created_by' => $counselorUser?->id,
                ],
                [
                    'school_npsn' => $smpn2Npsn,
                    'nisn' => '0113456789',
                    'student_name' => 'Aisyah Nur Ramadhani',
                    'class_name' => '9C',
                    'semester_id' => '20261',
                    'category_id' => $categoriesMap['OLAHRAGA'] ?? 2,
                    'achievement_name' => 'Kejuaraan Pencak Silat Remaja Putri Piala Walikota',
                    'achievement_level' => 'KOTA',
                    'ranking' => 'Medali Emas',
                    'organizer' => 'IPSI Kota Bukittinggi',
                    'achievement_date' => $now->copy()->subMonth()->format('Y-m-d'),
                    'description' => 'Juara tanding kelas C putri tingkat remaja setelah memenangkan 4 laga berturut-turut.',
                    'created_by' => $counselorUser?->id,
                ],
                [
                    'school_npsn' => $smpn2Npsn,
                    'nisn' => '0114567890',
                    'student_name' => 'Rizky Hidayatullah',
                    'class_name' => '8B',
                    'semester_id' => '20261',
                    'category_id' => $categoriesMap['KEAGAMAAN'] ?? 4,
                    'achievement_name' => 'Musabaqah Tilawatil Quran (MTQ) Cabang Hifzhil Quran 5 Juz',
                    'achievement_level' => 'PROVINSI',
                    'ranking' => 'Juara 2',
                    'organizer' => 'LPTQ Sumatera Barat',
                    'achievement_date' => $now->copy()->subWeeks(3)->format('Y-m-d'),
                    'description' => 'Mewakili Kota Bukittinggi pada ajang MTQ Provinsi Sumatera Barat.',
                    'created_by' => $counselorUser?->id,
                ],
                [
                    'school_npsn' => $smpn2Npsn,
                    'nisn' => '0115678901',
                    'student_name' => 'Zahra Putri Kirana',
                    'class_name' => '7E',
                    'semester_id' => '20261',
                    'category_id' => $categoriesMap['SENI_BUDAYA'] ?? 3,
                    'achievement_name' => 'Festival & Lomba Seni Siswa Nasional (FLS2N) Tari Kreasi',
                    'achievement_level' => 'KOTA',
                    'ranking' => 'Juara 1',
                    'organizer' => 'Dinas Pendidikan dan Kebudayaan Kota Bukittinggi',
                    'achievement_date' => $now->copy()->subDays(10)->format('Y-m-d'),
                    'description' => 'Menampilkan Tari Piring Kreasi Baru dan berhasil mewakili kota ke tingkat provinsi.',
                    'created_by' => $counselorUser?->id,
                ],
            ];

            foreach ($sampleAchievements as $ach) {
                DB::table('student_achievements')->updateOrInsert(
                    [
                        'school_npsn' => $ach['school_npsn'],
                        'nisn' => $ach['nisn'],
                        'achievement_name' => $ach['achievement_name'],
                    ],
                    array_merge($ach, [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])
                );
            }
        }
    }
}
