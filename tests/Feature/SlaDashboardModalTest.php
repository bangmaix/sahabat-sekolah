<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SlaDashboardModalTest extends TestCase
{
    use RefreshDatabase;

    private string $schoolNpsn = '10307412';

    private int $counselorId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'https://api.bukittinggikota.go.id/*' => Http::response([
                'data' => [
                    ['npsn' => $this->schoolNpsn, 'nama_sekolah' => 'SMP Negeri Uji'],
                ],
            ], 200),
        ]);

        $now = now();
        $this->counselorId = DB::table('users')->insertGetId([
            'name' => 'Bu Ratna Sari',
            'email' => 'bk@example.test',
            'password' => Hash::make('password'),
            'role' => 'COUNSELOR',
            'school_npsn' => $this->schoolNpsn,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function counselorSession(): array
    {
        return [
            'user_id' => $this->counselorId,
            'user_name' => 'Bu Ratna Sari',
            'user_role' => 'COUNSELOR',
            'school_npsn' => $this->schoolNpsn,
        ];
    }

    public function test_dashboard_displays_sla_modal_when_unresponded_cases_are_overdue(): void
    {
        $now = now();
        $caseNumber = 'SS-OVERDUE-001';

        $reportId = DB::table('reports')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_number' => $caseNumber,
            'reporter_role' => 'VICTIM',
            'identity_mode' => 'CONFIDENTIAL',
            'category' => 'Cyberbullying',
            'description' => 'Ada pesan ancaman di media sosial yang ditujukan ke saya.',
            'submitted_at' => $now->copy()->subHours(8),
            'created_at' => $now->copy()->subHours(8),
            'updated_at' => $now->copy()->subHours(8),
        ]);

        $caseId = DB::table('cases')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_id' => $reportId,
            'case_number' => $caseNumber,
            'category' => 'Cyberbullying',
            'risk_level' => 'HIGH',
            'status' => 'PENDING_RESPONSE',
            'opened_at' => $now->copy()->subHours(8),
            'created_at' => $now->copy()->subHours(8),
            'updated_at' => $now->copy()->subHours(8),
        ]);

        DB::table('case_slas')->insert([
            'case_id' => $caseId,
            'status' => 'ON_TIME',
            'response_deadline' => $now->copy()->subHours(4), // Overdue
            'initial_response_at' => null,
            'created_at' => $now->copy()->subHours(8),
            'updated_at' => $now->copy()->subHours(8),
        ]);

        $response = $this->withSession($this->counselorSession())->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('slaAlertSummary', function ($summary) {
            return $summary['total'] === 1 && $summary['overdue'] === 1 && $summary['near_overdue'] === 0;
        });
        $response->assertSee('id="slaAlertModal"', false);
        $response->assertSee('Peringatan SLA: Laporan Butuh Respons Segera');
        $response->assertSee($caseNumber);
        $response->assertSee('SLA Terlewat');
    }

    public function test_dashboard_displays_sla_modal_when_unresponded_cases_are_near_overdue(): void
    {
        $now = now();
        $caseNumber = 'SS-WARNING-002';

        $reportId = DB::table('reports')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_number' => $caseNumber,
            'reporter_role' => 'WITNESS',
            'identity_mode' => 'CONFIDENTIAL',
            'category' => 'Verbal',
            'description' => 'Ejekan berulang di koridor kelas.',
            'submitted_at' => $now->copy()->subHours(1),
            'created_at' => $now->copy()->subHours(1),
            'updated_at' => $now->copy()->subHours(1),
        ]);

        $caseId = DB::table('cases')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_id' => $reportId,
            'case_number' => $caseNumber,
            'category' => 'Verbal',
            'risk_level' => 'MEDIUM',
            'status' => 'PENDING_RESPONSE',
            'opened_at' => $now->copy()->subHours(1),
            'created_at' => $now->copy()->subHours(1),
            'updated_at' => $now->copy()->subHours(1),
        ]);

        DB::table('case_slas')->insert([
            'case_id' => $caseId,
            'status' => 'ON_TIME',
            'response_deadline' => $now->copy()->addHours(3), // Within 6 hours => Near overdue
            'initial_response_at' => null,
            'created_at' => $now->copy()->subHours(1),
            'updated_at' => $now->copy()->subHours(1),
        ]);

        $response = $this->withSession($this->counselorSession())->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('slaAlertSummary', function ($summary) {
            return $summary['total'] === 1 && $summary['overdue'] === 0 && $summary['near_overdue'] === 1;
        });
        $response->assertSee('id="slaAlertModal"', false);
        $response->assertSee($caseNumber);
        $response->assertSee('Mendekati Batas');
    }

    public function test_dashboard_does_not_display_modal_when_no_overdue_or_near_overdue(): void
    {
        $now = now();
        $caseNumber = 'SS-ONTIME-003';

        $reportId = DB::table('reports')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_number' => $caseNumber,
            'reporter_role' => 'WITNESS',
            'identity_mode' => 'CONFIDENTIAL',
            'category' => 'Fisik',
            'description' => 'Laporan yang aman dan tenggat masih panjang.',
            'submitted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $caseId = DB::table('cases')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_id' => $reportId,
            'case_number' => $caseNumber,
            'category' => 'Fisik',
            'risk_level' => 'LOW',
            'status' => 'PENDING_RESPONSE',
            'opened_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('case_slas')->insert([
            'case_id' => $caseId,
            'status' => 'ON_TIME',
            'response_deadline' => $now->copy()->addHours(20), // > 6 hours => Not near overdue
            'initial_response_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $response = $this->withSession($this->counselorSession())->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('slaAlertSummary', function ($summary) {
            return $summary['total'] === 0;
        });
        $response->assertDontSee('id="slaAlertModal"', false);
    }

    public function test_dashboard_does_not_display_modal_for_cases_that_are_already_responded(): void
    {
        $now = now();
        $caseNumber = 'SS-HANDLING-004';

        $reportId = DB::table('reports')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_number' => $caseNumber,
            'reporter_role' => 'VICTIM',
            'identity_mode' => 'CONFIDENTIAL',
            'category' => 'Cyberbullying',
            'description' => 'Kasus yang sudah dalam penanganan Guru BK.',
            'submitted_at' => $now->copy()->subHours(12),
            'created_at' => $now->copy()->subHours(12),
            'updated_at' => $now->copy()->subHours(12),
        ]);

        $caseId = DB::table('cases')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_id' => $reportId,
            'case_number' => $caseNumber,
            'category' => 'Cyberbullying',
            'risk_level' => 'HIGH',
            'status' => 'IN_HANDLING', // Already responded and in handling
            'opened_at' => $now->copy()->subHours(12),
            'created_at' => $now->copy()->subHours(12),
            'updated_at' => $now->copy()->subHours(12),
        ]);

        DB::table('case_slas')->insert([
            'case_id' => $caseId,
            'status' => 'OVERDUE',
            'response_deadline' => $now->copy()->subHours(6),
            'initial_response_at' => null,
            'created_at' => $now->copy()->subHours(12),
            'updated_at' => $now->copy()->subHours(12),
        ]);

        $response = $this->withSession($this->counselorSession())->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('slaAlertSummary', function ($summary) {
            return $summary['total'] === 0;
        });
        $response->assertViewHas('slaAlertCases', function ($cases) {
            return $cases->isEmpty();
        });
        $response->assertDontSee('id="slaAlertModal"', false);
    }

    public function test_dashboard_does_not_display_modal_for_cases_that_are_already_resolved_or_closed(): void
    {
        $now = now();
        $resolvedCase = 'SS-RESOLVED-005';
        $closedCase = 'SS-CLOSED-006';

        foreach ([$resolvedCase => 'RESOLVED', $closedCase => 'CLOSED'] as $number => $status) {
            $reportId = DB::table('reports')->insertGetId([
                'school_npsn' => $this->schoolNpsn,
                'report_number' => $number,
                'reporter_role' => 'VICTIM',
                'identity_mode' => 'CONFIDENTIAL',
                'category' => 'Verbal',
                'description' => 'Kasus sudah selesai atau ditutup.',
                'submitted_at' => $now->copy()->subDays(3),
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now->copy()->subDays(3),
            ]);

            $caseId = DB::table('cases')->insertGetId([
                'school_npsn' => $this->schoolNpsn,
                'report_id' => $reportId,
                'case_number' => $number,
                'category' => 'Verbal',
                'risk_level' => 'MEDIUM',
                'status' => $status,
                'opened_at' => $now->copy()->subDays(3),
                'resolved_at' => $now->copy()->subDay(),
                'closed_at' => $status === 'CLOSED' ? $now->copy()->subDay() : null,
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now->copy()->subDays(3),
            ]);

            DB::table('case_slas')->insert([
                'case_id' => $caseId,
                'status' => 'OVERDUE',
                'response_deadline' => $now->copy()->subDays(2),
                'initial_response_at' => null,
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now->copy()->subDays(3),
            ]);
        }

        $response = $this->withSession($this->counselorSession())->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('slaAlertSummary', function ($summary) {
            return $summary['total'] === 0;
        });
        $response->assertDontSee('id="slaAlertModal"', false);
    }

    public function test_dashboard_only_includes_strictly_unresponded_cases_when_mixed_cases_exist(): void
    {
        $now = now();
        $unrespondedOverdue = 'SS-UNRESPONDED-007';
        $handlingOverdue = 'SS-HANDLING-008';
        $closedOverdue = 'SS-CLOSED-009';

        // 1. Unresponded overdue case (PENDING_RESPONSE) -> SHOULD be included
        $r1 = DB::table('reports')->insertGetId([
            'school_npsn' => $this->schoolNpsn, 'report_number' => $unrespondedOverdue,
            'reporter_role' => 'VICTIM', 'identity_mode' => 'CONFIDENTIAL',
            'category' => 'Cyberbullying', 'description' => 'Laporan perundungan baru belum disentuh sama sekali.',
            'submitted_at' => $now->copy()->subHours(10), 'created_at' => $now->copy()->subHours(10), 'updated_at' => $now->copy()->subHours(10),
        ]);
        $c1 = DB::table('cases')->insertGetId([
            'school_npsn' => $this->schoolNpsn, 'report_id' => $r1, 'case_number' => $unrespondedOverdue,
            'category' => 'Cyberbullying', 'risk_level' => 'HIGH', 'status' => 'PENDING_RESPONSE',
            'opened_at' => $now->copy()->subHours(10), 'created_at' => $now->copy()->subHours(10), 'updated_at' => $now->copy()->subHours(10),
        ]);
        DB::table('case_slas')->insert([
            'case_id' => $c1, 'status' => 'ON_TIME', 'response_deadline' => $now->copy()->subHours(2),
            'initial_response_at' => null, 'created_at' => $now->copy()->subHours(10), 'updated_at' => $now->copy()->subHours(10),
        ]);

        // 2. Handled case (IN_HANDLING) with overdue deadline -> MUST NOT be in modal
        $r2 = DB::table('reports')->insertGetId([
            'school_npsn' => $this->schoolNpsn, 'report_number' => $handlingOverdue,
            'reporter_role' => 'WITNESS', 'identity_mode' => 'CONFIDENTIAL',
            'category' => 'Verbal', 'description' => 'Laporan yang sedang ditangani Guru BK.',
            'submitted_at' => $now->copy()->subHours(10), 'created_at' => $now->copy()->subHours(10), 'updated_at' => $now->copy()->subHours(10),
        ]);
        $c2 = DB::table('cases')->insertGetId([
            'school_npsn' => $this->schoolNpsn, 'report_id' => $r2, 'case_number' => $handlingOverdue,
            'category' => 'Verbal', 'risk_level' => 'MEDIUM', 'status' => 'IN_HANDLING',
            'opened_at' => $now->copy()->subHours(10), 'created_at' => $now->copy()->subHours(10), 'updated_at' => $now->copy()->subHours(10),
        ]);
        DB::table('case_slas')->insert([
            'case_id' => $c2, 'status' => 'OVERDUE', 'response_deadline' => $now->copy()->subHours(2),
            'initial_response_at' => null, 'created_at' => $now->copy()->subHours(10), 'updated_at' => $now->copy()->subHours(10),
        ]);

        // 3. Closed case (CLOSED) with overdue deadline -> MUST NOT be in modal
        $r3 = DB::table('reports')->insertGetId([
            'school_npsn' => $this->schoolNpsn, 'report_number' => $closedOverdue,
            'reporter_role' => 'WITNESS', 'identity_mode' => 'CONFIDENTIAL',
            'category' => 'Fisik', 'description' => 'Laporan yang sudah ditutup.',
            'submitted_at' => $now->copy()->subDays(2), 'created_at' => $now->copy()->subDays(2), 'updated_at' => $now->copy()->subDays(2),
        ]);
        $c3 = DB::table('cases')->insertGetId([
            'school_npsn' => $this->schoolNpsn, 'report_id' => $r3, 'case_number' => $closedOverdue,
            'category' => 'Fisik', 'risk_level' => 'LOW', 'status' => 'CLOSED',
            'opened_at' => $now->copy()->subDays(2), 'closed_at' => $now->copy()->subDay(),
            'created_at' => $now->copy()->subDays(2), 'updated_at' => $now->copy()->subDays(2),
        ]);
        DB::table('case_slas')->insert([
            'case_id' => $c3, 'status' => 'OVERDUE', 'response_deadline' => $now->copy()->subDay(),
            'initial_response_at' => null, 'created_at' => $now->copy()->subDays(2), 'updated_at' => $now->copy()->subDays(2),
        ]);

        $response = $this->withSession($this->counselorSession())->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('slaAlertSummary', function ($summary) {
            return $summary['total'] === 1 && $summary['overdue'] === 1;
        });
        $response->assertViewHas('slaAlertCases', function ($cases) use ($unrespondedOverdue, $handlingOverdue, $closedOverdue) {
            return $cases->count() === 1
                && $cases->first()->case_number === $unrespondedOverdue
                && ! $cases->pluck('case_number')->contains($handlingOverdue)
                && ! $cases->pluck('case_number')->contains($closedOverdue);
        });
        $response->assertSee('id="slaAlertModal"', false);
        $response->assertSee($unrespondedOverdue);
        $response->assertSee('Belum Direspon');
    }
}
