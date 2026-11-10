<?php

namespace Tests\Feature;

use App\Mail\AdminResetPasswordMail;
use App\Mail\UserCreatedMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private int $adminId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            '*/v1/data-sekolah*' => Http::response([
                'data' => [
                    ['npsn' => '10300074', 'nama_sekolah' => 'SMP Negeri 2 Bukittinggi'],
                ],
            ], 200),
            '*/rest/v1/sekolah*' => Http::response([
                ['npsn' => '10300074', 'nama' => 'SMP Negeri 2 Bukittinggi'],
            ], 200),
        ]);

        $now = now();
        $this->adminId = DB::table('users')->insertGetId([
            'name' => 'Admin Sistem',
            'email' => 'admin-test@example.test',
            'password' => Hash::make('password'),
            'role' => 'ADMIN',
            'school_npsn' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function adminSession(): array
    {
        return [
            'user_id' => $this->adminId,
            'user_name' => 'Admin Sistem',
            'user_role' => 'ADMIN',
            'school_npsn' => null,
        ];
    }

    public function test_admin_dashboard_renders_successfully_with_empty_cases(): void
    {
        $response = $this->withSession($this->adminSession())->get(route('admin.index'));

        $response->assertStatus(200);
        $response->assertViewHas('monthlyLabels');
        $response->assertViewHas('monthlyCounts');
        $response->assertViewHas('slaChartData');
        $response->assertViewHas('statusChartData');

        $monthlyLabels = $response->viewData('monthlyLabels');
        $monthlyCounts = $response->viewData('monthlyCounts');
        $slaChartData = $response->viewData('slaChartData');
        $statusChartData = $response->viewData('statusChartData');

        $this->assertCount(12, $monthlyLabels);
        $this->assertCount(12, $monthlyCounts);
        $this->assertEquals(0, array_sum($monthlyCounts));
        $this->assertEquals(0, $slaChartData['total']);
        $this->assertEquals(0, $statusChartData['total']);

        // Assert asset and containers exist in view
        $response->assertSee('js/apexcharts.min.js');
        $response->assertSee('id="chart-monthly"', false);
        $response->assertSee('Belum ada data SLA');
        $response->assertSee('Belum ada data status');
    }

    public function test_admin_dashboard_renders_chart_data_accurately_with_cases(): void
    {
        $now = now();

        $reportId = DB::table('reports')->insertGetId([
            'school_npsn' => '10300074',
            'report_number' => 'TEST-001',
            'reporter_role' => 'VICTIM',
            'identity_mode' => 'CONFIDENTIAL',
            'category' => 'Perundungan Verbal',
            'description' => 'Contoh pengaduan tes',
            'submitted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $caseId = DB::table('cases')->insertGetId([
            'school_npsn' => '10300074',
            'report_id' => $reportId,
            'case_number' => 'CAS-2026-0001',
            'category' => 'Perundungan Verbal',
            'status' => 'IN_HANDLING',
            'risk_level' => 'HIGH',
            'opened_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('case_slas')->insert([
            'case_id' => $caseId,
            'status' => 'ON_TRACK',
            'response_deadline' => $now->copy()->addHours(24),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $response = $this->withSession($this->adminSession())->get(route('admin.index'));

        $response->assertStatus(200);

        $monthlyCounts = $response->viewData('monthlyCounts');
        $slaChartData = $response->viewData('slaChartData');
        $statusChartData = $response->viewData('statusChartData');

        $this->assertEquals(1, array_sum($monthlyCounts));
        $this->assertEquals(1, $slaChartData['total']);
        $this->assertEquals(1, $statusChartData['total']);
        $this->assertContains('On Track', $slaChartData['labels']);
        $this->assertContains('Ditangani', $statusChartData['labels']);

        $response->assertSee('id="chart-sla"', false);
        $response->assertSee('id="chart-case-status"', false);
    }

    public function test_store_user_fails_gracefully_when_email_already_exists(): void
    {
        $response = $this->withSession($this->adminSession())
            ->from('/admin/users')
            ->post('/admin/users', [
                'name' => 'Duplikat Admin',
                'email' => 'admin-test@example.test',
                'role' => 'COUNSELOR',
                'school_npsn' => '10300074',
            ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHasErrors(['email']);
        $this->assertEquals(
            'Email ini sudah terdaftar dalam sistem.',
            session('errors')->first('email')
        );
    }

    public function test_store_user_normalizes_email_and_prevents_case_insensitive_duplicate(): void
    {
        Mail::fake();

        // First user with mixed case and whitespace
        $response = $this->withSession($this->adminSession())
            ->from('/admin/users')
            ->post('/admin/users', [
                'name' => 'Guru BK Baru',
                'email' => '  Bk-Baru@School.Test  ',
                'role' => 'COUNSELOR',
                'school_npsn' => '10300074',
            ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Guru BK Baru',
            'email' => 'bk-baru@school.test',
        ]);

        // Second attempt with exact or uppercase version
        $responseDuplicate = $this->withSession($this->adminSession())
            ->from('/admin/users')
            ->post('/admin/users', [
                'name' => 'Guru BK Lain',
                'email' => 'BK-BARU@SCHOOL.TEST',
                'role' => 'COUNSELOR',
                'school_npsn' => '10300074',
            ]);

        $responseDuplicate->assertRedirect('/admin/users');
        $responseDuplicate->assertSessionHasErrors(['email']);
    }

    public function test_store_user_generates_random_8_character_password_and_queues_welcome_email(): void
    {
        Mail::fake();

        $response = $this->withSession($this->adminSession())
            ->from('/admin/users')
            ->post('/admin/users', [
                'name' => 'Guru BK Baru',
                'email' => 'guru.baru@sekolah.test',
                'role' => 'COUNSELOR',
                'school_npsn' => '10300074',
            ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');
        $this->assertStringContainsString('guru.baru@sekolah.test', session('success'));

        $user = DB::table('users')->where('email', 'guru.baru@sekolah.test')->first();
        $this->assertNotNull($user);
        $this->assertNotEquals('password', $user->password);

        Mail::assertQueued(UserCreatedMail::class, function (UserCreatedMail $mail) use ($user) {
            $hasTo = $mail->hasTo('guru.baru@sekolah.test');
            $password = $mail->userData['password'];
            $lengthIs8 = strlen($password) === 8;
            $passwordMatchesHash = Hash::check($password, $user->password);
            $roleMatches = $mail->userData['role'] === 'COUNSELOR';
            $schoolMatches = $mail->userData['school_name'] === 'SMP Negeri 2 Bukittinggi';
            $recipientNameMatches = $mail->recipientName === 'Guru BK Baru';

            return $hasTo && $lengthIs8 && $passwordMatchesHash && $roleMatches && $schoolMatches && $recipientNameMatches;
        });
    }

    public function test_user_created_mail_renders_properly(): void
    {
        $mailable = new UserCreatedMail(
            userData: [
                'name' => 'Budi Pratama',
                'email' => 'budi.pratama@sekolah.test',
                'role' => 'COUNSELOR',
                'school_name' => 'SMP Negeri 2 Bukittinggi',
                'password' => 'Xy7K9mP2',
                'login_url' => 'http://localhost/login',
            ],
            recipientName: 'Budi Pratama',
        );

        $mailable->assertSeeInHtml('Budi Pratama');
        $mailable->assertSeeInHtml('budi.pratama@sekolah.test');
        $mailable->assertSeeInHtml('Guru BK');
        $mailable->assertSeeInHtml('SMP Negeri 2 Bukittinggi');
        $mailable->assertSeeInHtml('Xy7K9mP2');
        $mailable->assertSeeInHtml('http://localhost/login');
        $mailable->assertSeeInHtml('KATA SANDI SEMENTARA');
    }

    public function test_admin_can_reset_user_password_to_random_and_queues_email(): void
    {
        Mail::fake();

        $counselorId = DB::table('users')->insertGetId([
            'name' => 'Guru Konseling',
            'email' => 'counselor@school.test',
            'password' => Hash::make('passwordLama123'),
            'role' => 'COUNSELOR',
            'school_npsn' => '10300074',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession($this->adminSession())
            ->post("/admin/users/{$counselorId}/reset-password");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $resetTokenEntry = DB::table('password_reset_tokens')->where('email', 'counselor@school.test')->first();
        $this->assertNotNull($resetTokenEntry);

        Mail::assertQueued(AdminResetPasswordMail::class, function (AdminResetPasswordMail $mail) {
            $hasTo = $mail->hasTo('counselor@school.test');
            $hasUrl = str_contains($mail->userData['reset_url'], 'reset-password/');
            $roleMatches = $mail->userData['role'] === 'COUNSELOR';
            $expiresMatches = ($mail->userData['expires_minutes'] ?? null) === 60;

            return $hasTo && $hasUrl && $roleMatches && $expiresMatches;
        });
    }

    public function test_admin_reset_password_mail_renders_properly(): void
    {
        $mailable = new AdminResetPasswordMail(
            userData: [
                'name' => 'Guru Konseling',
                'email' => 'counselor@school.test',
                'role' => 'COUNSELOR',
                'school_name' => 'SMP Negeri 2 Bukittinggi',
                'reset_url' => 'http://localhost/reset-password/sample-token?email=counselor@school.test',
                'expires_minutes' => 60,
            ],
            recipientName: 'Guru Konseling',
        );

        $mailable->assertSeeInHtml('Guru Konseling');
        $mailable->assertSeeInHtml('counselor@school.test');
        $mailable->assertSeeInHtml('Atur Ulang Kata Sandi');
        $mailable->assertSeeInHtml('60 menit');
    }

    public function test_admin_can_create_city_admin_user_and_queues_email(): void
    {
        Mail::fake();

        $response = $this->withSession($this->adminSession())
            ->from('/admin/users')
            ->post('/admin/users', [
                'name' => 'Petugas Disdikpora',
                'email' => 'petugas.disdik@bukittinggi.go.id',
                'role' => 'CITY_ADMIN',
                'school_npsn' => null,
            ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $user = DB::table('users')->where('email', 'petugas.disdik@bukittinggi.go.id')->first();
        $this->assertNotNull($user);
        $this->assertEquals('CITY_ADMIN', $user->role);
        $this->assertEquals('Dinas Pendidikan Kota Bukittinggi', $user->school_name);
        $this->assertNull($user->school_npsn);

        Mail::assertQueued(UserCreatedMail::class, function (UserCreatedMail $mail) {
            return $mail->hasTo('petugas.disdik@bukittinggi.go.id')
                && $mail->userData['role'] === 'CITY_ADMIN'
                && $mail->userData['school_name'] === 'Dinas Pendidikan Kota Bukittinggi';
        });
    }

    public function test_admin_can_update_user_to_city_admin(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Staff Uji',
            'email' => 'staff.uji@example.test',
            'password' => Hash::make('password'),
            'role' => 'COUNSELOR',
            'school_npsn' => '10300074',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession($this->adminSession())
            ->put("/admin/users/{$userId}", [
                'name' => 'Staff Uji Diperbarui',
                'role' => 'CITY_ADMIN',
                'school_npsn' => null,
                'status' => 'ACTIVE',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $updated = DB::table('users')->where('id', $userId)->first();
        $this->assertEquals('CITY_ADMIN', $updated->role);
        $this->assertEquals('Dinas Pendidikan Kota Bukittinggi', $updated->school_name);
        $this->assertNull($updated->school_npsn);
    }

    public function test_admin_can_create_city_admin_user_specifically_for_uptd_ppa(): void
    {
        Mail::fake();

        $response = $this->withSession($this->adminSession())
            ->from('/admin/users')
            ->post('/admin/users', [
                'name' => 'Petugas UPTD PPA',
                'email' => 'petugas.ppa@bukittinggikota.go.id',
                'role' => 'CITY_ADMIN',
                'city_agency' => 'UPTD PPA - DP3APPKB Kota Bukittinggi',
                'school_npsn' => null,
            ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $user = DB::table('users')->where('email', 'petugas.ppa@bukittinggikota.go.id')->first();
        $this->assertNotNull($user);
        $this->assertEquals('CITY_ADMIN', $user->role);
        $this->assertEquals('UPTD PPA - DP3APPKB Kota Bukittinggi', $user->school_name);
        $this->assertNull($user->school_npsn);

        Mail::assertQueued(UserCreatedMail::class, function (UserCreatedMail $mail) {
            return $mail->hasTo('petugas.ppa@bukittinggikota.go.id')
                && $mail->userData['role'] === 'CITY_ADMIN'
                && $mail->userData['school_name'] === 'UPTD PPA - DP3APPKB Kota Bukittinggi';
        });
    }
}
