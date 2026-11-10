<?php

namespace Tests\Feature;

use App\Mail\CaseEscalatedPrincipalMail;
use App\Mail\NewReportCounselorMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationMailTest extends TestCase
{
    use RefreshDatabase;

    private string $schoolNpsn = '10307412';

    private string $otherSchoolNpsn = '10307415';

    private int $counselorId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $now = now();

        // Counselor for target school
        $this->counselorId = DB::table('users')->insertGetId([
            'name' => 'Guru BK Sekolah Uji',
            'email' => 'bk-target@school.test',
            'password' => Hash::make('password'),
            'role' => 'COUNSELOR',
            'school_npsn' => $this->schoolNpsn,
            'school_name' => 'SMP Negeri Uji Bukittinggi',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Counselor for other school
        DB::table('users')->insert([
            'name' => 'Guru BK Sekolah Lain',
            'email' => 'bk-other@school.test',
            'password' => Hash::make('password'),
            'role' => 'COUNSELOR',
            'school_npsn' => $this->otherSchoolNpsn,
            'school_name' => 'SMP Lain',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Principal for target school
        DB::table('users')->insert([
            'name' => 'Kepala Sekolah Uji',
            'email' => 'kepsek-target@school.test',
            'password' => Hash::make('password'),
            'role' => 'PRINCIPAL',
            'school_npsn' => $this->schoolNpsn,
            'school_name' => 'SMP Negeri Uji Bukittinggi',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Principal for other school
        DB::table('users')->insert([
            'name' => 'Kepala Sekolah Lain',
            'email' => 'kepsek-other@school.test',
            'password' => Hash::make('password'),
            'role' => 'PRINCIPAL',
            'school_npsn' => $this->otherSchoolNpsn,
            'school_name' => 'SMP Lain',
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

    public function test_new_report_sends_email_to_school_counselors(): void
    {
        Mail::fake();

        $response = $this->post('/reports', [
            'school_npsn' => $this->schoolNpsn,
            'reporter_role' => 'WITNESS',
            'identity_mode' => 'CONFIDENTIAL',
            'reporter_name' => 'Pelapor Uji',
            'category' => 'Cyberbullying',
            'description' => 'Ini kronologi dugaan perundungan yang dilaporkan untuk pengujian email notifikasi.',
        ]);

        $response->assertSessionHasNoErrors();

        Mail::assertQueued(NewReportCounselorMail::class, function (NewReportCounselorMail $mail) {
            return $mail->hasTo('bk-target@school.test') &&
                $mail->reportData['school_npsn'] === $this->schoolNpsn &&
                $mail->reportData['category'] === 'Cyberbullying' &&
                $mail->reportData['risk_level'] === 'HIGH' &&
                $mail->recipientName === 'Guru BK Sekolah Uji';
        });

        Mail::assertNotQueued(NewReportCounselorMail::class, function (NewReportCounselorMail $mail) {
            return $mail->hasTo('bk-other@school.test');
        });
    }

    public function test_escalation_by_counselor_sends_email_to_school_principal(): void
    {
        $now = now();
        $caseNumber = 'SS-2026-TEST01';

        $reportId = DB::table('reports')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_number' => $caseNumber,
            'reporter_role' => 'VICTIM',
            'identity_mode' => 'CONFIDENTIAL',
            'category' => 'Perundungan Fisik',
            'description' => 'Dugaan pemukulan dan intimidasi berulang kali saat jam istirahat.',
            'submitted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('cases')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_id' => $reportId,
            'case_number' => $caseNumber,
            'category' => 'Perundungan Fisik',
            'risk_level' => 'HIGH',
            'status' => 'IN_HANDLING',
            'opened_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Mail::fake();

        $response = $this->withSession($this->counselorSession())
            ->post('/cases/'.$caseNumber.'/escalate', [
                'reason' => 'Perlu tindakan dan pemanggilan orang tua tingkat pimpinan sekolah.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Kasus berhasil dieskalasikan kepada Kepala Sekolah.');

        Mail::assertQueued(CaseEscalatedPrincipalMail::class, function (CaseEscalatedPrincipalMail $mail) use ($caseNumber) {
            return $mail->hasTo('kepsek-target@school.test') &&
                $mail->caseData['case_number'] === $caseNumber &&
                $mail->caseData['school_npsn'] === $this->schoolNpsn &&
                $mail->escalationData['escalated_by'] === 'Guru BK Sekolah Uji' &&
                $mail->escalationData['reason'] === 'Perlu tindakan dan pemanggilan orang tua tingkat pimpinan sekolah.' &&
                $mail->recipientName === 'Kepala Sekolah Uji';
        });

        Mail::assertNotQueued(CaseEscalatedPrincipalMail::class, function (CaseEscalatedPrincipalMail $mail) {
            return $mail->hasTo('kepsek-other@school.test');
        });
    }

    public function test_new_report_counselor_mail_renders_properly(): void
    {
        $mailable = new NewReportCounselorMail(
            reportData: [
                'case_number' => 'SS-2026-000123',
                'school_npsn' => '10307412',
                'school_name' => 'SMP Negeri 2 Bukittinggi',
                'category' => 'Cyberbullying',
                'subcategory' => 'Ancaman digital',
                'risk_level' => 'HIGH',
                'reporter_role' => 'VICTIM',
                'identity_mode' => 'CONFIDENTIAL',
                'description' => 'Ini adalah kronologi pengujian render mailable.',
                'submitted_at' => now(),
                'case_url' => 'http://localhost/cases/SS-2026-000123',
            ],
            recipientName: 'Ibu Ratna',
        );

        $mailable->assertSeeInHtml('SS-2026-000123');
        $mailable->assertSeeInHtml('SMP Negeri 2 Bukittinggi');
        $mailable->assertSeeInHtml('Cyberbullying');
        $mailable->assertSeeInHtml('Ancaman digital');
        $mailable->assertSeeInHtml('Ibu Ratna');
        $mailable->assertSeeInHtml('1 × 24 jam');
        $mailable->assertSeeInHtml('http://localhost/cases/SS-2026-000123');
    }

    public function test_case_escalated_principal_mail_renders_properly(): void
    {
        $mailable = new CaseEscalatedPrincipalMail(
            caseData: [
                'case_number' => 'SS-2026-000456',
                'school_npsn' => '10307412',
                'school_name' => 'SMP Negeri 2 Bukittinggi',
                'category' => 'Perundungan Fisik',
                'subcategory' => null,
                'risk_level' => 'HIGH',
                'status' => 'IN_HANDLING',
                'description' => 'Deskripsi kasus fisik untuk pengujian render.',
                'case_url' => 'http://localhost/cases/SS-2026-000456',
            ],
            escalationData: [
                'escalated_by' => 'Guru BK Ratna',
                'reason' => 'Pelaku membutuhkan pembinaan khusus bersama pimpinan sekolah.',
                'escalated_at' => now(),
            ],
            recipientName: 'Bapak Kepala Sekolah',
        );

        $mailable->assertSeeInHtml('SS-2026-000456');
        $mailable->assertSeeInHtml('SMP Negeri 2 Bukittinggi');
        $mailable->assertSeeInHtml('Perundungan Fisik');
        $mailable->assertSeeInHtml('Guru BK Ratna');
        $mailable->assertSeeInHtml('Pelaku membutuhkan pembinaan khusus bersama pimpinan sekolah.');
        $mailable->assertSeeInHtml('Bapak Kepala Sekolah');
        $mailable->assertSeeInHtml('http://localhost/cases/SS-2026-000456');
    }
}
