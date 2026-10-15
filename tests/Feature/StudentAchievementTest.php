<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\AchievementCategory;
use App\Models\StudentAchievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StudentAchievementTest extends TestCase
{
    use RefreshDatabase;

    private string $schoolNpsn = '10307412';

    private string $otherSchoolNpsn = '10307415';

    private int $counselorId = 0;

    private int $adminId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $now = now();

        $this->counselorId = DB::table('users')->insertGetId([
            'name' => 'Guru BK Uji',
            'email' => 'bk-prestasi@example.test',
            'password' => Hash::make('password'),
            'role' => 'COUNSELOR',
            'school_npsn' => $this->schoolNpsn,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->adminId = DB::table('users')->insertGetId([
            'name' => 'Admin Sistem',
            'email' => 'admin-prestasi@example.test',
            'password' => Hash::make('password'),
            'role' => 'ADMIN',
            'school_npsn' => $this->schoolNpsn,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function counselorSession(string $schoolNpsn = '10307412'): array
    {
        return [
            'user_id' => $this->counselorId,
            'user_name' => 'Guru BK Uji',
            'user_role' => 'COUNSELOR',
            'school_npsn' => $schoolNpsn,
        ];
    }

    private function adminSession(): array
    {
        return [
            'user_id' => $this->adminId,
            'user_name' => 'Admin Sistem',
            'user_role' => 'ADMIN',
            'school_npsn' => $this->schoolNpsn,
        ];
    }

    public function test_effective_semester_id_prefers_active_and_falls_back(): void
    {
        AcademicPeriod::query()->delete();

        AcademicPeriod::create([
            'semester_id' => '20261',
            'name' => '2026/2027 Ganjil',
            'is_active' => true,
            'is_fallback' => false,
            'order_no' => 1,
        ]);

        AcademicPeriod::create([
            'semester_id' => '20252',
            'name' => '2025/2026 Genap',
            'is_active' => false,
            'is_fallback' => true,
            'order_no' => 2,
        ]);

        $this->assertSame('20261', AcademicPeriod::effectiveSemesterId());

        // Ketika tidak ada yang aktif, ambil fallback
        AcademicPeriod::where('semester_id', '20261')->update(['is_active' => false]);
        $this->assertSame('20252', AcademicPeriod::effectiveSemesterId());
    }

    public function test_fetch_student_uses_fallback_semester_when_active_semester_has_no_data(): void
    {
        // 20261 belum ditarik Dapodik -> kembalikan kosong
        // 20252 sudah ada -> kembalikan data siswa
        Http::fake([
            '*/v1/data-siswa/10307412/20261/0081234567' => Http::response(['data' => []], 200),
            '*/v1/data-siswa/10307412/20252/0081234567' => Http::response([
                'data' => [
                    'nisn' => '0081234567',
                    'nm_siswa' => 'Ahmad Pratama',
                    'nm_kelas' => 'VIII-B',
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->counselorSession())
            ->getJson('/achievements/fetch-student?nisn=0081234567');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nisn', '0081234567')
            ->assertJsonPath('data.student_name', 'Ahmad Pratama')
            ->assertJsonPath('data.class_name', 'VIII-B')
            ->assertJsonPath('data.semester_id', '20252')
            ->assertJsonPath('data.is_fallback', true);
    }

    public function test_fetch_student_returns_not_found_when_both_semesters_fail(): void
    {
        Http::fake([
            '*/v1/data-siswa/*' => Http::response(['data' => []], 200),
        ]);

        $response = $this->withSession($this->counselorSession())
            ->getJson('/achievements/fetch-student?nisn=9999999999');

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_counselor_can_open_create_page_with_students_dropdown(): void
    {
        Http::fake([
            '*/v1/data-kelas/10307412/20261' => Http::response([
                'data' => [
                    ['rombongan_belajar_id' => 'rombel-8b', 'nm_kelas' => '8B'],
                ],
            ], 200),
            '*/v1/data-kelas/10307412/20261/rombel-8b' => Http::response([
                'data' => [
                    ['nisn' => '0081234567', 'nm_siswa' => 'Ahmad Pratama', 'nis' => '1001', 'jenis_kelamin' => 'L'],
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->counselorSession())
            ->get('/achievements/create');

        $response->assertOk()
            ->assertSee('Tambah Prestasi Siswa')
            ->assertSee('Ahmad Pratama')
            ->assertSee('0081234567')
            ->assertSee('student_lookup')
            ->assertSee('Tarik Data');
    }

    public function test_counselor_create_page_falls_back_when_active_semester_has_no_classes(): void
    {
        Http::fake([
            '*/v1/data-kelas/10307412/20261' => Http::response(['data' => []], 200),
            '*/v1/data-kelas/10307412/20252' => Http::response([
                'data' => [
                    ['rombongan_belajar_id' => 'rombel-7a', 'nm_kelas' => '7A'],
                ],
            ], 200),
            '*/v1/data-kelas/10307412/20252/rombel-7a' => Http::response([
                'data' => [
                    ['nisn' => '0098765432', 'nm_siswa' => 'Budi Santoso', 'nis' => '2001', 'jenis_kelamin' => 'L'],
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->counselorSession())
            ->get('/achievements/create');

        $response->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('0098765432')
            ->assertSee('SEMESTER CADANGAN');
    }

    public function test_counselor_can_store_and_view_student_achievement(): void
    {
        $category = AchievementCategory::where('code', 'AKADEMIK')->firstOrFail();

        $payload = [
            'nisn' => '0081234567',
            'student_name' => 'Ahmad Pratama',
            'class_name' => 'VIII-B',
            'semester_id' => '20252',
            'category_id' => $category->id,
            'achievement_name' => 'Olimpiade Sains Nasional Matematika',
            'achievement_level' => 'KOTA',
            'ranking' => 'Juara 1',
            'organizer' => 'Dinas Pendidikan Kota Bukittinggi',
            'achievement_date' => '2026-09-15',
            'description' => 'Meraih medali emas tingkat kota',
        ];

        $response = $this->withSession($this->counselorSession())
            ->post('/achievements', $payload);

        $response->assertRedirect(route('achievements.index'));
        $this->assertDatabaseHas('student_achievements', [
            'school_npsn' => $this->schoolNpsn,
            'nisn' => '0081234567',
            'achievement_name' => 'Olimpiade Sains Nasional Matematika',
            'ranking' => 'Juara 1',
        ]);

        // Cek audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'CREATE_ACHIEVEMENT',
            'resource_type' => 'STUDENT_ACHIEVEMENT',
        ]);

        // Tampilkan daftar dan detail
        $achievement = StudentAchievement::where('nisn', '0081234567')->firstOrFail();

        $indexResponse = $this->withSession($this->counselorSession())
            ->get('/achievements');
        $indexResponse->assertOk()
            ->assertSee('Tambah Prestasi')
            ->assertSee(route('achievements.create'))
            ->assertSee('Ahmad Pratama')
            ->assertSee('Olimpiade Sains Nasional Matematika');

        $showResponse = $this->withSession($this->counselorSession())
            ->get('/achievements/'.$achievement->id);
        $showResponse->assertOk()
            ->assertSee('Ahmad Pratama')
            ->assertSee('Juara 1');
    }

    public function test_counselor_can_update_and_delete_achievement(): void
    {
        $category = AchievementCategory::where('code', 'OLAHRAGA')->firstOrFail();

        $achievement = StudentAchievement::create([
            'school_npsn' => $this->schoolNpsn,
            'nisn' => '0087654321',
            'student_name' => 'Siti Nurhaliza',
            'class_name' => 'IX-A',
            'semester_id' => '20261',
            'category_id' => $category->id,
            'achievement_name' => 'Kejuaraan Renang Pelajar',
            'achievement_level' => 'PROVINSI',
            'ranking' => 'Juara 2',
            'organizer' => 'PRSI Sumbar',
            'achievement_date' => '2026-08-20',
            'created_by' => $this->counselorId,
        ]);

        // Update
        $updatePayload = [
            'category_id' => $category->id,
            'achievement_name' => 'Kejuaraan Renang Pelajar Tingkat Sumbar',
            'achievement_level' => 'PROVINSI',
            'ranking' => 'Juara 1',
            'organizer' => 'PRSI Sumbar',
            'achievement_date' => '2026-08-20',
            'description' => 'Koreksi peringkat menjadi Juara 1',
        ];

        $updateResponse = $this->withSession($this->counselorSession())
            ->post('/achievements/'.$achievement->id, $updatePayload);

        $updateResponse->assertRedirect(route('achievements.show', $achievement->id));
        $this->assertDatabaseHas('student_achievements', [
            'id' => $achievement->id,
            'ranking' => 'Juara 1',
            'achievement_name' => 'Kejuaraan Renang Pelajar Tingkat Sumbar',
        ]);

        // Delete
        $deleteResponse = $this->withSession($this->counselorSession())
            ->post('/achievements/'.$achievement->id.'/delete');

        $deleteResponse->assertRedirect(route('achievements.index'));
        $this->assertDatabaseMissing('student_achievements', [
            'id' => $achievement->id,
        ]);
    }

    public function test_achievements_are_scoped_by_school_npsn(): void
    {
        $category = AchievementCategory::where('code', 'SENI_BUDAYA')->firstOrFail();

        $otherSchoolAchievement = StudentAchievement::create([
            'school_npsn' => $this->otherSchoolNpsn,
            'nisn' => '0091122334',
            'student_name' => 'Budi Santoso',
            'category_id' => $category->id,
            'achievement_name' => 'Festival Tari Tradisional',
            'achievement_level' => 'KOTA',
            'ranking' => 'Juara 3',
            'achievement_date' => '2026-07-10',
        ]);

        // Counselor sekolah $this->schoolNpsn tidak boleh bisa melihat prestasi sekolah lain
        $response = $this->withSession($this->counselorSession())
            ->get('/achievements/'.$otherSchoolAchievement->id);

        $response->assertNotFound();
    }

    public function test_admin_can_manage_achievement_categories_and_academic_periods(): void
    {
        // 1. Admin tambah kategori prestasi
        $categoryResponse = $this->withSession($this->adminSession())
            ->post('/admin/master-data/achievement-categories', [
                'name' => 'Kewirausahaan Siswa',
                'description' => 'Prestasi business plan & startup pelajar',
            ]);
        $categoryResponse->assertRedirect();
        $this->assertDatabaseHas('achievement_categories', [
            'name' => 'Kewirausahaan Siswa',
            'code' => 'KEWIRAUSAHAAN_SISWA',
            'status' => true,
        ]);

        $newCategory = AchievementCategory::where('code', 'KEWIRAUSAHAAN_SISWA')->firstOrFail();

        // 2. Admin toggle status kategori prestasi
        $toggleResponse = $this->withSession($this->adminSession())
            ->post('/admin/master-data/achievement-categories/'.$newCategory->id.'/toggle');
        $toggleResponse->assertRedirect();
        $this->assertDatabaseHas('achievement_categories', [
            'id' => $newCategory->id,
            'status' => false,
        ]);

        // 3. Admin tambah periode akademik baru
        $periodResponse = $this->withSession($this->adminSession())
            ->post('/admin/master-data/academic-periods', [
                'semester_id' => '20271',
                'name' => 'Tahun Ajaran 2027/2028 Ganjil',
            ]);
        $periodResponse->assertRedirect();
        $this->assertDatabaseHas('academic_periods', [
            'semester_id' => '20271',
            'name' => 'Tahun Ajaran 2027/2028 Ganjil',
            'is_active' => false,
        ]);

        $newPeriod = AcademicPeriod::where('semester_id', '20271')->firstOrFail();

        // 4. Admin aktifkan periode baru
        $setActiveResponse = $this->withSession($this->adminSession())
            ->post('/admin/master-data/academic-periods/'.$newPeriod->id.'/set-active');
        $setActiveResponse->assertRedirect();
        $this->assertDatabaseHas('academic_periods', [
            'id' => $newPeriod->id,
            'is_active' => true,
        ]);

        // 5. Admin jadikan periode baru sebagai fallback
        $setFallbackResponse = $this->withSession($this->adminSession())
            ->post('/admin/master-data/academic-periods/'.$newPeriod->id.'/set-fallback');
        $setFallbackResponse->assertRedirect();
        $this->assertDatabaseHas('academic_periods', [
            'id' => $newPeriod->id,
            'is_fallback' => true,
        ]);
    }

    public function test_achievements_are_grouped_by_nisn_on_index_page(): void
    {
        $category = AchievementCategory::firstOrFail();

        // Buat 2 prestasi untuk siswa yang sama (NISN 0081111111)
        StudentAchievement::create([
            'school_npsn' => $this->schoolNpsn,
            'nisn' => '0081111111',
            'student_name' => 'Farhan Alamsyah',
            'class_name' => 'IX-A',
            'category_id' => $category->id,
            'achievement_name' => 'Juara 1 Catur Kota',
            'achievement_level' => 'KOTA',
            'ranking' => 'Juara 1',
            'achievement_date' => '2026-08-10',
            'created_by' => $this->counselorId,
        ]);

        StudentAchievement::create([
            'school_npsn' => $this->schoolNpsn,
            'nisn' => '0081111111',
            'student_name' => 'Farhan Alamsyah',
            'class_name' => 'IX-A',
            'category_id' => $category->id,
            'achievement_name' => 'Medali Emas OSN Nasional',
            'achievement_level' => 'NASIONAL',
            'ranking' => 'Juara 1',
            'achievement_date' => '2026-09-20',
            'created_by' => $this->counselorId,
        ]);

        // Buat 1 prestasi untuk siswa lain (NISN 0082222222)
        StudentAchievement::create([
            'school_npsn' => $this->schoolNpsn,
            'nisn' => '0082222222',
            'student_name' => 'Nadia Putri',
            'class_name' => 'VIII-B',
            'category_id' => $category->id,
            'achievement_name' => 'Juara 3 Pidato Bahasa Inggris',
            'achievement_level' => 'KECAMATAN',
            'ranking' => 'Juara 3',
            'achievement_date' => '2026-07-15',
            'created_by' => $this->counselorId,
        ]);

        $response = $this->withSession($this->counselorSession())
            ->get('/achievements');

        $response->assertOk()
            ->assertSee('Farhan Alamsyah')
            ->assertSee('0081111111')
            ->assertSee('2 Prestasi')
            ->assertSee('Medali Emas OSN Nasional')
            ->assertSee('Juara 1 Catur Kota')
            ->assertSee('Nadia Putri')
            ->assertSee('0082222222')
            ->assertSee('1 Prestasi')
            ->assertSee('Berdasarkan NISN');
    }

    public function test_student_profile_displays_cumulative_portfolio_by_nisn(): void
    {
        $category = AchievementCategory::firstOrFail();

        StudentAchievement::create([
            'school_npsn' => $this->schoolNpsn,
            'nisn' => '0085556667',
            'student_name' => 'Rizky Pratama',
            'class_name' => 'VII-C',
            'category_id' => $category->id,
            'achievement_name' => 'Lomba Robotik Kreatif',
            'achievement_level' => 'PROVINSI',
            'ranking' => 'Juara 2',
            'achievement_date' => '2026-06-11',
            'created_by' => $this->counselorId,
        ]);

        StudentAchievement::create([
            'school_npsn' => $this->schoolNpsn,
            'nisn' => '0085556667',
            'student_name' => 'Rizky Pratama',
            'class_name' => 'VII-C',
            'category_id' => $category->id,
            'achievement_name' => 'Lomba Karya Tulis Ilmiah Remaja',
            'achievement_level' => 'NASIONAL',
            'ranking' => 'Juara 1',
            'achievement_date' => '2026-09-01',
            'created_by' => $this->counselorId,
        ]);

        $response = $this->withSession($this->counselorSession())
            ->get('/achievements/student/0085556667');

        $response->assertOk()
            ->assertSee('Rizky Pratama')
            ->assertSee('0085556667')
            ->assertSee('Lomba Robotik Kreatif')
            ->assertSee('Lomba Karya Tulis Ilmiah Remaja')
            ->assertSee('2')
            ->assertSee('Nasional');
    }

    public function test_student_profile_returns_404_for_unknown_nisn_or_other_school(): void
    {
        $category = AchievementCategory::firstOrFail();

        // Prestasi di sekolah lain
        StudentAchievement::create([
            'school_npsn' => $this->otherSchoolNpsn,
            'nisn' => '0098889990',
            'student_name' => 'Siswa Sekolah Lain',
            'category_id' => $category->id,
            'achievement_name' => 'Prestasi Luar',
            'achievement_level' => 'KOTA',
            'ranking' => 'Juara 1',
            'achievement_date' => '2026-05-01',
        ]);

        // NISN tidak ada di sistem
        $responseNotFound = $this->withSession($this->counselorSession())
            ->get('/achievements/student/0000000000');
        $responseNotFound->assertNotFound();

        // NISN milik sekolah lain
        $responseOtherSchool = $this->withSession($this->counselorSession())
            ->get('/achievements/student/0098889990');
        $responseOtherSchool->assertNotFound();
    }
}
