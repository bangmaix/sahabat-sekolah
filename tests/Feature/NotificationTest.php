<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private string $schoolNpsn = '10307412';

    private int $counselorId = 0;

    private int $principalId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $now = now();

        $this->counselorId = DB::table('users')->insertGetId([
            'name' => 'Guru BK Sekolah Uji',
            'email' => 'bk-uji@school.test',
            'password' => Hash::make('password'),
            'role' => 'COUNSELOR',
            'school_npsn' => $this->schoolNpsn,
            'school_name' => 'SMP Negeri Uji Bukittinggi',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->principalId = DB::table('users')->insertGetId([
            'name' => 'Kepala Sekolah Uji',
            'email' => 'kepsek-uji@school.test',
            'password' => Hash::make('password'),
            'role' => 'PRINCIPAL',
            'school_npsn' => $this->schoolNpsn,
            'school_name' => 'SMP Negeri Uji Bukittinggi',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function counselorSession(): array
    {
        return [
            'user_id' => $this->counselorId,
            'user_name' => 'Guru BK Sekolah Uji',
            'user_role' => 'COUNSELOR',
            'school_npsn' => $this->schoolNpsn,
        ];
    }

    private function principalSession(): array
    {
        return [
            'user_id' => $this->principalId,
            'user_name' => 'Kepala Sekolah Uji',
            'user_role' => 'PRINCIPAL',
            'school_npsn' => $this->schoolNpsn,
        ];
    }

    public function test_counselor_can_view_notifications_ordered_by_created_at(): void
    {
        $oldDate = Carbon::now()->subDays(2);
        $midDate = Carbon::now()->subDay();
        $newDate = Carbon::now();

        $id1 = DB::table('notifications')->insertGetId([
            'type' => 'NEW_REPORT',
            'priority' => 'NORMAL',
            'title' => 'Notifikasi Lama',
            'message' => 'Pesan notifikasi lama',
            'case_number' => null,
            'recipient_name' => 'Guru BK',
            'created_at' => $oldDate,
            'updated_at' => $oldDate,
        ]);

        $id2 = DB::table('notifications')->insertGetId([
            'type' => 'NEW_REPORT',
            'priority' => 'HIGH',
            'title' => 'Notifikasi Sedang',
            'message' => 'Pesan notifikasi sedang',
            'case_number' => null,
            'recipient_name' => 'Guru BK',
            'created_at' => $midDate,
            'updated_at' => $midDate,
        ]);

        $id3 = DB::table('notifications')->insertGetId([
            'type' => 'STATUS_CHANGED',
            'priority' => 'URGENT',
            'title' => 'Notifikasi Terbaru',
            'message' => 'Pesan notifikasi terbaru',
            'case_number' => null,
            'recipient_name' => 'Guru BK',
            'created_at' => $newDate,
            'updated_at' => $newDate,
        ]);

        $response = $this->withSession($this->counselorSession())
            ->get(route('notifications.index'));

        $response->assertOk();
        $response->assertViewIs('notifications.index');
        $response->assertSee('Notifikasi Sistem');
        $response->assertSee(route('notifications.index'));

        // Check view data order
        $viewNotifications = $response->viewData('notifications');
        $this->assertCount(3, $viewNotifications);
        $this->assertEquals($id3, $viewNotifications[0]->id);
        $this->assertEquals($id2, $viewNotifications[1]->id);
        $this->assertEquals($id1, $viewNotifications[2]->id);

        // Verify HTML content order (latest notification appears before older notifications)
        $content = $response->getContent();
        $posNew = strpos($content, 'Notifikasi Terbaru');
        $posMid = strpos($content, 'Notifikasi Sedang');
        $posOld = strpos($content, 'Notifikasi Lama');

        $this->assertTrue($posNew !== false && $posMid !== false && $posOld !== false);
        $this->assertTrue($posNew < $posMid);
        $this->assertTrue($posMid < $posOld);
    }

    public function test_principal_can_view_notifications_page(): void
    {
        $response = $this->withSession($this->principalSession())
            ->get(route('notifications.index'));

        $response->assertOk();
        $response->assertViewIs('notifications.index');
    }

    public function test_counselor_can_mark_notification_as_read_via_post(): void
    {
        $notifId = DB::table('notifications')->insertGetId([
            'type' => 'NEW_REPORT',
            'priority' => 'NORMAL',
            'title' => 'Uji Tandai Dibaca',
            'message' => 'Pesan uji',
            'case_number' => null,
            'recipient_name' => 'Guru BK',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession($this->counselorSession())
            ->from(route('notifications.index'))
            ->post(route('notifications.read', $notifId));

        $response->assertRedirect(route('notifications.index'));

        $notif = DB::table('notifications')->where('id', $notifId)->first();
        $this->assertNotNull($notif->read_at);
    }

    public function test_counselor_can_mark_notification_as_read_via_get_and_redirect_to_dashboard(): void
    {
        $notifId = DB::table('notifications')->insertGetId([
            'type' => 'NEW_REPORT',
            'priority' => 'NORMAL',
            'title' => 'Uji Klik Dropdown',
            'message' => 'Pesan dropdown',
            'case_number' => null,
            'recipient_name' => 'Guru BK',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession($this->counselorSession())
            ->get(route('notifications.read', $notifId));

        $response->assertRedirect(route('dashboard'));

        $notif = DB::table('notifications')->where('id', $notifId)->first();
        $this->assertNotNull($notif->read_at);
    }

    public function test_sidebar_contains_notification_menu_with_unread_count(): void
    {
        DB::table('notifications')->insert([
            'type' => 'NEW_REPORT',
            'priority' => 'HIGH',
            'title' => 'Notifikasi 1',
            'message' => 'Belum dibaca',
            'read_at' => null,
            'case_number' => null,
            'recipient_name' => 'Guru BK',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession($this->counselorSession())
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(route('notifications.index'));
        $response->assertSee('Notifikasi');
    }

    public function test_counselor_can_mark_selected_notifications_as_read(): void
    {
        $id1 = DB::table('notifications')->insertGetId([
            'type' => 'NEW_REPORT',
            'priority' => 'HIGH',
            'title' => 'Notif 1',
            'message' => 'Pesan 1',
            'case_number' => null,
            'recipient_name' => 'Guru BK',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id2 = DB::table('notifications')->insertGetId([
            'type' => 'NEW_REPORT',
            'priority' => 'HIGH',
            'title' => 'Notif 2',
            'message' => 'Pesan 2',
            'case_number' => null,
            'recipient_name' => 'Guru BK',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id3 = DB::table('notifications')->insertGetId([
            'type' => 'NEW_REPORT',
            'priority' => 'NORMAL',
            'title' => 'Notif 3',
            'message' => 'Pesan 3',
            'case_number' => null,
            'recipient_name' => 'Guru BK',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession($this->counselorSession())
            ->from(route('notifications.index'))
            ->post(route('notifications.read-selected'), [
                'notification_ids' => [$id1, $id2],
            ]);

        $response->assertRedirect(route('notifications.index'));
        $response->assertSessionHas('success');

        $this->assertNotNull(DB::table('notifications')->where('id', $id1)->value('read_at'));
        $this->assertNotNull(DB::table('notifications')->where('id', $id2)->value('read_at'));
        $this->assertNull(DB::table('notifications')->where('id', $id3)->value('read_at'));
    }

    public function test_read_selected_does_not_mark_other_school_notifications_as_read(): void
    {
        $otherSchoolNpsn = '99999999';

        // Case for other school
        $reportId = DB::table('reports')->insertGetId([
            'school_npsn' => $otherSchoolNpsn,
            'report_number' => 'REP-OTHER-001',
            'category' => 'Bullying',
            'description' => 'Kasus sekolah lain',
            'reporter_role' => 'STUDENT',
            'identity_mode' => 'ANONYMOUS',
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otherCaseNumber = 'SS-OTHER-001';
        DB::table('cases')->insert([
            'case_number' => $otherCaseNumber,
            'report_id' => $reportId,
            'school_npsn' => $otherSchoolNpsn,
            'category' => 'Bullying',
            'status' => 'PENDING_RESPONSE',
            'risk_level' => 'HIGH',
            'assigned_to' => 'Guru BK Lain',
            'opened_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otherNotifId = DB::table('notifications')->insertGetId([
            'type' => 'NEW_REPORT',
            'priority' => 'HIGH',
            'title' => 'Notif Sekolah Lain',
            'message' => 'Pesan rahasia',
            'case_number' => $otherCaseNumber,
            'recipient_name' => 'Guru BK',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Counselor from target school attempts to mark other school's notification
        $response = $this->withSession($this->counselorSession())
            ->from(route('notifications.index'))
            ->post(route('notifications.read-selected'), [
                'notification_ids' => [$otherNotifId],
            ]);

        $response->assertRedirect(route('notifications.index'));
        // Must remain unread!
        $this->assertNull(DB::table('notifications')->where('id', $otherNotifId)->value('read_at'));
    }

    public function test_counselor_can_mark_all_notifications_as_read(): void
    {
        $id1 = DB::table('notifications')->insertGetId([
            'type' => 'NEW_REPORT',
            'priority' => 'NORMAL',
            'title' => 'Notif 1',
            'message' => 'Pesan 1',
            'case_number' => null,
            'recipient_name' => 'Guru BK',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id2 = DB::table('notifications')->insertGetId([
            'type' => 'NEW_REPORT',
            'priority' => 'HIGH',
            'title' => 'Notif 2',
            'message' => 'Pesan 2',
            'case_number' => null,
            'recipient_name' => 'Guru BK',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession($this->counselorSession())
            ->from(route('notifications.index'))
            ->post(route('notifications.read-all'));

        $response->assertRedirect(route('notifications.index'));
        $response->assertSessionHas('success');

        $this->assertNotNull(DB::table('notifications')->where('id', $id1)->value('read_at'));
        $this->assertNotNull(DB::table('notifications')->where('id', $id2)->value('read_at'));
    }

    public function test_read_all_only_marks_own_school_notifications_as_read(): void
    {
        $otherSchoolNpsn = '99999999';

        $reportId = DB::table('reports')->insertGetId([
            'school_npsn' => $otherSchoolNpsn,
            'report_number' => 'REP-OTHER-002',
            'category' => 'Bullying',
            'description' => 'Kasus sekolah lain',
            'reporter_role' => 'STUDENT',
            'identity_mode' => 'ANONYMOUS',
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otherCaseNumber = 'SS-OTHER-002';
        DB::table('cases')->insert([
            'case_number' => $otherCaseNumber,
            'report_id' => $reportId,
            'school_npsn' => $otherSchoolNpsn,
            'category' => 'Bullying',
            'status' => 'PENDING_RESPONSE',
            'risk_level' => 'HIGH',
            'assigned_to' => 'Guru BK Lain',
            'opened_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Own school case and notif
        $myReportId = DB::table('reports')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_number' => 'REP-MY-001',
            'category' => 'Bullying',
            'description' => 'Kasus sekolah saya',
            'reporter_role' => 'STUDENT',
            'identity_mode' => 'ANONYMOUS',
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $myCaseNumber = 'SS-MY-001';
        DB::table('cases')->insert([
            'case_number' => $myCaseNumber,
            'report_id' => $myReportId,
            'school_npsn' => $this->schoolNpsn,
            'category' => 'Bullying',
            'status' => 'PENDING_RESPONSE',
            'risk_level' => 'HIGH',
            'assigned_to' => 'Guru BK Sekolah Uji',
            'opened_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $myNotifId = DB::table('notifications')->insertGetId([
            'type' => 'NEW_REPORT',
            'priority' => 'HIGH',
            'title' => 'Notif Sekolah Saya',
            'message' => 'Pesan saya',
            'case_number' => $myCaseNumber,
            'recipient_name' => 'Guru BK Sekolah Uji',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otherNotifId = DB::table('notifications')->insertGetId([
            'type' => 'NEW_REPORT',
            'priority' => 'HIGH',
            'title' => 'Notif Sekolah Lain',
            'message' => 'Pesan lain',
            'case_number' => $otherCaseNumber,
            'recipient_name' => 'Guru BK Sekolah Lain',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession($this->counselorSession())
            ->from(route('notifications.index'))
            ->post(route('notifications.read-all'));

        $response->assertRedirect(route('notifications.index'));

        // My school notification is read
        $this->assertNotNull(DB::table('notifications')->where('id', $myNotifId)->value('read_at'));
        // Other school notification remains unread!
        $this->assertNull(DB::table('notifications')->where('id', $otherNotifId)->value('read_at'));
    }

    public function test_anonymous_message_creates_notification_linked_to_case_number(): void
    {
        $rawToken = 'ABCDEF123456';
        $reportId = DB::table('reports')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_number' => 'REP-ANON-001',
            'category' => 'Bullying',
            'description' => 'Laporan anonim',
            'reporter_role' => 'STUDENT',
            'identity_mode' => 'ANONYMOUS',
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $caseNumber = 'SS-ANON-CASE';
        DB::table('cases')->insert([
            'case_number' => $caseNumber,
            'report_id' => $reportId,
            'school_npsn' => $this->schoolNpsn,
            'category' => 'Bullying',
            'status' => 'PENDING_RESPONSE',
            'risk_level' => 'HIGH',
            'assigned_to' => 'Guru BK Sekolah Uji',
            'opened_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('anonymous_report_tokens')->insert([
            'report_id' => $reportId,
            'token_hash' => hash('sha256', $rawToken),
            'status' => true,
            'expires_at' => now()->addDays(7),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post(route('anonymous.message'), [
            'token' => $rawToken,
            'message' => 'Ini pesan tambahan dari pelapor anonim.',
        ]);

        $response->assertRedirect(route('anonymous.channel', ['token' => $rawToken]));

        $notif = DB::table('notifications')
            ->where('type', 'ANONYMOUS_MESSAGE')
            ->latest('id')
            ->first();

        $this->assertNotNull($notif);
        $this->assertEquals($caseNumber, $notif->case_number);
    }
}
