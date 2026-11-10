<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    private string $schoolNpsn = '10307412';

    private int $counselorId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $now = now();

        $this->counselorId = DB::table('users')->insertGetId([
            'name' => 'Guru BK Mobile Uji',
            'email' => 'bk-mobile@school.test',
            'password' => Hash::make('password123'),
            'role' => 'COUNSELOR',
            'school_npsn' => $this->schoolNpsn,
            'school_name' => 'SMP Negeri Uji Bukittinggi',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function getBearerToken(): string
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'bk-mobile@school.test',
            'password' => 'password123',
        ]);

        return $response->json('data.token');
    }

    public function test_public_can_fetch_categories(): void
    {
        $response = $this->getJson('/api/v1/public/categories');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name', 'status', 'subcategories'],
            ],
        ]);
    }

    public function test_public_can_submit_anonymous_report(): void
    {
        $response = $this->postJson('/api/v1/public/reports', [
            'school_npsn' => $this->schoolNpsn,
            'reporter_role' => 'VICTIM',
            'identity_mode' => 'ANONYMOUS',
            'category' => 'Cyberbullying',
            'description' => 'Ini adalah kronologi pengaduan anonim dari aplikasi mobile.',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'data' => [
                'identity_mode' => 'ANONYMOUS',
                'category' => 'Cyberbullying',
                'status' => 'PENDING_RESPONSE',
            ],
        ]);

        $token = $response->json('data.anonymous_token');
        $this->assertNotNull($token);
        $this->assertEquals(12, strlen($token));

        $caseNumber = $response->json('data.case_number');
        $this->assertNotNull($caseNumber);

        // Verify tracking works with the token
        $trackResponse = $this->postJson('/api/v1/public/reports/track', [
            'tracking_code' => $token,
        ]);

        $trackResponse->assertOk();
        $trackResponse->assertJson([
            'success' => true,
            'data' => [
                'case_number' => $caseNumber,
                'category' => 'Cyberbullying',
                'is_anonymous' => true,
            ],
        ]);
    }

    public function test_anonymous_messaging_channel(): void
    {
        $reportResponse = $this->postJson('/api/v1/public/reports', [
            'school_npsn' => $this->schoolNpsn,
            'reporter_role' => 'STUDENT',
            'identity_mode' => 'ANONYMOUS',
            'category' => 'Cyberbullying',
            'description' => 'Laporan uji pesan anonim pada mobile.',
        ]);

        $token = $reportResponse->json('data.anonymous_token');
        $caseNumber = $reportResponse->json('data.case_number');

        // Reporter sends message
        $sendResponse = $this->postJson("/api/v1/public/anonymous/{$token}/messages", [
            'message' => 'Halo Bu Guru BK, saya sangat takut.',
        ]);
        $sendResponse->assertStatus(201);

        // Fetch messages
        $getMessagesResponse = $this->getJson("/api/v1/public/anonymous/{$token}/messages");
        $getMessagesResponse->assertOk();
        $getMessagesResponse->assertJsonCount(1, 'data.messages');

        // Counselor replies via API
        $bearerToken = $this->getBearerToken();
        $replyResponse = $this->withHeader('Authorization', "Bearer {$bearerToken}")
            ->postJson("/api/v1/cases/{$caseNumber}/anonymous-reply", [
                'message' => 'Tenang ya Nak, identitas kamu aman bersama kami.',
            ]);
        $replyResponse->assertStatus(201);

        // Reporter sees the counselor's reply
        $getMessagesAgain = $this->getJson("/api/v1/public/anonymous/{$token}/messages");
        $getMessagesAgain->assertJsonCount(2, 'data.messages');
    }

    public function test_counselor_auth_and_dashboard_overview(): void
    {
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'bk-mobile@school.test',
            'password' => 'password123',
        ]);

        $loginResponse->assertOk();
        $token = $loginResponse->json('data.token');

        // Get Profile
        $profileResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me');
        $profileResponse->assertOk();
        $profileResponse->assertJsonPath('data.email', 'bk-mobile@school.test');

        // Get Dashboard
        $dashResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/dashboard');
        $dashResponse->assertOk();
        $dashResponse->assertJsonStructure([
            'success',
            'data' => [
                'counts',
                'sla',
                'categories',
                'recent_cases',
            ],
        ]);

        // Logout
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');
        $logoutResponse->assertOk();

        // Attempting to access protected route with deleted token fails
        $afterLogout = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me');
        $afterLogout->assertStatus(401);
    }

    public function test_counselor_case_management_and_action(): void
    {
        $token = $this->getBearerToken();

        // Create a case via public report
        $reportResponse = $this->postJson('/api/v1/public/reports', [
            'school_npsn' => $this->schoolNpsn,
            'reporter_role' => 'VICTIM',
            'identity_mode' => 'CONFIDENTIAL',
            'reporter_name' => 'Budi Siswa',
            'category' => 'Cyberbullying',
            'description' => 'Ada pesan mengancam di grup media sosial.',
        ]);

        $caseNumber = $reportResponse->json('data.case_number');

        // List cases
        $listResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/cases');
        $listResponse->assertOk();
        $listResponse->assertJsonStructure(['success', 'data', 'meta']);

        // Show case details
        $showResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/cases/{$caseNumber}");
        $showResponse->assertOk();
        $showResponse->assertJsonPath('data.case.case_number', $caseNumber);

        // Process Action (Status transition to UNDER_VERIFICATION + note)
        $actionResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/cases/{$caseNumber}/actions", [
                'status' => 'UNDER_VERIFICATION',
                'note_type' => 'INTERNAL',
                'note_content' => 'Telah dilakukan verifikasi akun penyebar pesan.',
                'reason' => 'Verifikasi bukti tangkapan layar',
            ]);

        $actionResponse->assertOk();
        $actionResponse->assertJsonPath('data.status', 'UNDER_VERIFICATION');

        // Add Note
        $noteResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/cases/{$caseNumber}/notes", [
                'note_type' => 'COUNSELING',
                'content' => 'Siswa telah diberikan sesi konseling awal.',
            ]);
        $noteResponse->assertStatus(201);
    }

    public function test_counselor_notifications_api(): void
    {
        $token = $this->getBearerToken();

        $id1 = DB::table('notifications')->insertGetId([
            'type' => 'NEW_REPORT',
            'priority' => 'HIGH',
            'title' => 'Notif API 1',
            'message' => 'Pesan 1',
            'case_number' => null,
            'recipient_name' => 'Guru BK Mobile Uji',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id2 = DB::table('notifications')->insertGetId([
            'type' => 'NEW_REPORT',
            'priority' => 'NORMAL',
            'title' => 'Notif API 2',
            'message' => 'Pesan 2',
            'case_number' => null,
            'recipient_name' => 'Guru BK Mobile Uji',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // List notifications
        $listResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/notifications');
        $listResponse->assertOk();
        $this->assertEquals(2, $listResponse->json('meta.unread_count'));

        // Mark single read
        $readSingle = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/notifications/{$id1}/read");
        $readSingle->assertOk();

        // Mark all read
        $readAll = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/notifications/read-all');
        $readAll->assertOk();

        $listAfter = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/notifications');
        $this->assertEquals(0, $listAfter->json('meta.unread_count'));
    }
}
