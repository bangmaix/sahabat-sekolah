<?php

namespace Tests\Feature;

use App\Mail\PasswordChangedMail;
use App\Mail\ResetPasswordMail;
use App\Mail\SuspectedBreachAdminAlertMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private string $userEmail = 'guru.bk@sekolah.test';

    private int $userId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $now = now();
        $this->userId = DB::table('users')->insertGetId([
            'name' => 'Guru BK Ratna',
            'email' => $this->userEmail,
            'password' => Hash::make('passwordLama123'),
            'role' => 'COUNSELOR',
            'school_npsn' => '10307412',
            'school_name' => 'SMP Negeri Uji Bukittinggi',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function test_user_can_view_forgot_password_screen(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertOk();
        $response->assertSee('Lupa Kata Sandi?');
    }

    public function test_login_screen_links_to_forgot_password(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee(route('password.request'));
        $response->assertSee('Lupa password?');
    }

    public function test_submitting_forgot_password_queues_reset_email(): void
    {
        Mail::fake();

        $response = $this->post('/forgot-password', [
            'email' => $this->userEmail,
        ]);

        $response->assertSessionHas('status');

        $resetToken = DB::table('password_reset_tokens')->where('email', $this->userEmail)->first();
        $this->assertNotNull($resetToken);

        Mail::assertQueued(ResetPasswordMail::class, function (ResetPasswordMail $mail) {
            return $mail->hasTo($this->userEmail) &&
                str_contains($mail->resetData['reset_url'], 'reset-password/') &&
                $mail->resetData['expires_minutes'] === 60;
        });
    }

    public function test_user_can_view_reset_password_screen_with_token(): void
    {
        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => $this->userEmail,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $response = $this->get("/reset-password/{$token}?email=".urlencode($this->userEmail));

        $response->assertOk();
        $response->assertSee('Atur Kata Sandi Baru');
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => $this->userEmail,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $this->userEmail,
            'password' => 'PasswordBaru456#',
            'password_confirmation' => 'PasswordBaru456#',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        // Token reset harus sudah dihapus dari tabel
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $this->userEmail,
        ]);

        // Verifikasi password baru dapat digunakan untuk login
        $updatedUser = DB::table('users')->where('id', $this->userId)->first();
        $this->assertTrue(Hash::check('PasswordBaru456#', $updatedUser->password));
    }

    public function test_cannot_reset_password_with_invalid_token(): void
    {
        $validToken = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => $this->userEmail,
            'token' => Hash::make($validToken),
            'created_at' => now(),
        ]);

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token-123456',
            'email' => $this->userEmail,
            'password' => 'PasswordBaru456#',
            'password_confirmation' => 'PasswordBaru456#',
        ]);

        $response->assertSessionHas('error', 'Token reset kata sandi tidak valid.');
    }

    public function test_cannot_reset_password_with_expired_token(): void
    {
        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => $this->userEmail,
            'token' => Hash::make($token),
            'created_at' => now()->subMinutes(65), // Kedaluwarsa > 60 menit
        ]);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $this->userEmail,
            'password' => 'PasswordBaru456#',
            'password_confirmation' => 'PasswordBaru456#',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_mobile_api_forgot_password_and_reset_password(): void
    {
        Mail::fake();

        // 1. API Forgot Password
        $forgotResponse = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $this->userEmail,
        ]);

        $forgotResponse->assertOk();
        $forgotResponse->assertJson([
            'success' => true,
        ]);

        Mail::assertQueued(ResetPasswordMail::class);

        // 2. Set token langsung untuk simulasi reset API
        $token = Str::random(64);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $this->userEmail],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // 3. API Reset Password
        $resetResponse = $this->postJson('/api/v1/auth/reset-password', [
            'email' => $this->userEmail,
            'token' => $token,
            'password' => 'PasswordMobileBaru789#',
            'password_confirmation' => 'PasswordMobileBaru789#',
        ]);

        $resetResponse->assertOk();
        $resetResponse->assertJson([
            'success' => true,
        ]);

        $updatedUser = DB::table('users')->where('id', $this->userId)->first();
        $this->assertTrue(Hash::check('PasswordMobileBaru789#', $updatedUser->password));
    }

    public function test_user_can_change_own_password_and_receives_security_notification_email(): void
    {
        Mail::fake();

        $session = [
            'user_id' => $this->userId,
            'user_name' => 'Guru BK Ratna',
            'user_role' => 'COUNSELOR',
            'school_npsn' => '10307412',
        ];

        $response = $this->withSession($session)->post('/change-password', [
            'current_password' => 'passwordLama123',
            'password' => 'PasswordBaru999#',
            'password_confirmation' => 'PasswordBaru999#',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $updatedUser = DB::table('users')->where('id', $this->userId)->first();
        $this->assertTrue(Hash::check('PasswordBaru999#', $updatedUser->password));

        Mail::assertQueued(PasswordChangedMail::class, function (PasswordChangedMail $mail) {
            return $mail->hasTo($this->userEmail) &&
                str_contains($mail->changeData['unauthorized_url'], 'security/unauthorized-password-change') &&
                $mail->changeData['email'] === $this->userEmail;
        });
    }

    public function test_user_cannot_change_password_with_incorrect_current_password(): void
    {
        $session = [
            'user_id' => $this->userId,
            'user_name' => 'Guru BK Ratna',
            'user_role' => 'COUNSELOR',
            'school_npsn' => '10307412',
        ];

        $response = $this->withSession($session)->post('/change-password', [
            'current_password' => 'salah123',
            'password' => 'PasswordBaru999#',
            'password_confirmation' => 'PasswordBaru999#',
        ]);

        $response->assertSessionHas('error', 'Kata sandi saat ini tidak cocok.');

        $user = DB::table('users')->where('id', $this->userId)->first();
        $this->assertTrue(Hash::check('passwordLama123', $user->password));
    }

    public function test_unauthorized_password_change_report_deactivates_account_and_notifies_admin(): void
    {
        Mail::fake();

        // Admin to receive emergency security notification
        DB::table('users')->insert([
            'name' => 'Admin Keamanan',
            'email' => 'admin-sec@sahabat.test',
            'password' => Hash::make('adminPass123'),
            'role' => 'ADMIN',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $validSignedUrl = URL::temporarySignedRoute(
            'security.report-unauthorized',
            now()->addDays(7),
            ['id' => $this->userId]
        );

        $response = $this->get($validSignedUrl);

        $response->assertOk();
        $response->assertSee('Akun Anda Telah Dinonaktifkan Sementara');
        $response->assertSee($this->userEmail);

        // Assert user status is now INACTIVE
        $user = DB::table('users')->where('id', $this->userId)->first();
        $this->assertEquals('INACTIVE', $user->status);

        // Assert urgent notification inserted for Admin
        $this->assertDatabaseHas('notifications', [
            'recipient_name' => 'Administrator',
            'type' => 'SECURITY_ALERT',
            'priority' => 'URGENT',
        ]);

        // Assert audit trail logged
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->userId,
            'action' => 'SUSPECTED_ACCOUNT_BREACH',
        ]);

        // Assert admin received emergency email
        Mail::assertQueued(SuspectedBreachAdminAlertMail::class, function (SuspectedBreachAdminAlertMail $mail) {
            return $mail->hasTo('admin-sec@sahabat.test') &&
                $mail->incidentData['victim_email'] === $this->userEmail &&
                $mail->incidentData['victim_name'] === 'Guru BK Ratna';
        });

        // Assert inactive user cannot log in
        $loginResponse = $this->post('/login', [
            'email' => $this->userEmail,
            'password' => 'passwordLama123',
        ]);

        $loginResponse->assertSessionHas('error');
        $this->assertStringContainsString('dinonaktifkan sementara', session('error'));
    }

    public function test_tampered_unauthorized_url_is_forbidden(): void
    {
        $validSignedUrl = URL::temporarySignedRoute(
            'security.report-unauthorized',
            now()->addDays(7),
            ['id' => $this->userId]
        );

        $tamperedUrl = $validSignedUrl.'&tampered=1';

        $response = $this->get($tamperedUrl);
        $response->assertStatus(403);
    }

    public function test_security_emails_render_properly(): void
    {
        // 1. PasswordChangedMail
        $mail1 = new PasswordChangedMail([
            'name' => 'Ratna Sari',
            'email' => 'ratna@sekolah.test',
            'changed_at' => '23 Oktober 2026, 17:00',
            'ip_address' => '192.168.1.10',
            'user_agent' => 'Mozilla/5.0 TestBrowser',
            'unauthorized_url' => 'http://localhost/security/unauthorized-password-change/1?sig=test',
        ], 'Ratna Sari');

        $mail1->assertSeeInHtml('Ratna Sari');
        $mail1->assertSeeInHtml('ratna@sekolah.test');
        $mail1->assertSeeInHtml('192.168.1.10');
        $mail1->assertSeeInHtml('Amankan Akun Saya (Bukan Saya)');

        // 2. SuspectedBreachAdminAlertMail
        $mail2 = new SuspectedBreachAdminAlertMail([
            'victim_name' => 'Ratna Sari',
            'victim_email' => 'ratna@sekolah.test',
            'victim_role' => 'COUNSELOR',
            'victim_school' => 'SMP Negeri 2 Bukittinggi',
            'reported_at' => '23 Oktober 2026, 17:05',
            'ip_address' => '192.168.1.10',
            'user_agent' => 'Mozilla/5.0 TestBrowser',
            'admin_users_url' => 'http://localhost/admin/users',
        ], 'Admin Utama');

        $mail2->assertSeeInHtml('PERINGATAN INSIDEN KEAMANAN');
        $mail2->assertSeeInHtml('ratna@sekolah.test');
        $mail2->assertSeeInHtml('NONAKTIF (INACTIVE)');
        $mail2->assertSeeInHtml('SMP Negeri 2 Bukittinggi');
        $mail2->assertSeeInHtml('http://localhost/admin/users');
    }
}
