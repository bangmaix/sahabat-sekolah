<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SlaResponseLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private string $schoolNpsn = '10307414';

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

    public function test_responding_to_overdue_case_keeps_sla_status_overdue(): void
    {
        $now = now();
        $caseNumber = 'SS-OVERDUE-RESP';

        $reportId = DB::table('reports')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_number' => $caseNumber,
            'reporter_role' => 'VICTIM',
            'identity_mode' => 'CONFIDENTIAL',
            'category' => 'Cyberbullying',
            'description' => 'Laporan perundungan yang terlambat direspon oleh guru BK.',
            'submitted_at' => $now->copy()->subHours(30),
            'created_at' => $now->copy()->subHours(30),
            'updated_at' => $now->copy()->subHours(30),
        ]);

        $caseId = DB::table('cases')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_id' => $reportId,
            'case_number' => $caseNumber,
            'category' => 'Cyberbullying',
            'risk_level' => 'HIGH',
            'status' => 'PENDING_RESPONSE',
            'opened_at' => $now->copy()->subHours(30),
            'created_at' => $now->copy()->subHours(30),
            'updated_at' => $now->copy()->subHours(30),
        ]);

        DB::table('case_slas')->insert([
            'case_id' => $caseId,
            'status' => 'OVERDUE',
            'response_deadline' => $now->copy()->subHours(6),
            'initial_response_at' => null,
            'created_at' => $now->copy()->subHours(30),
            'updated_at' => $now->copy()->subHours(6),
        ]);

        // Counselor responds by moving status to UNDER_VERIFICATION
        $response = $this->withSession($this->counselorSession())->post('/cases/'.$caseNumber.'/status', [
            'status' => 'UNDER_VERIFICATION',
            'reason' => 'Mulai memverifikasi laporan.',
        ]);

        $response->assertRedirect();

        // SLA status in case_slas table MUST remain OVERDUE
        $sla = DB::table('case_slas')->where('case_id', $caseId)->first();
        $this->assertEquals('OVERDUE', $sla->status);
        $this->assertNotNull($sla->initial_response_at);
        $this->assertNotNull($sla->resolution_deadline);

        // Case detail show page must display OVERDUE
        $showResponse = $this->withSession($this->counselorSession())->get('/cases/'.$caseNumber);
        $showResponse->assertOk();
        $showResponse->assertSee('OVERDUE');

        // Inbox table must display OVERDUE in SLA column
        $inboxResponse = $this->withSession($this->counselorSession())->get(route('reports.inbox'));
        $inboxResponse->assertOk();
        $inboxResponse->assertSee('sla-overdue', false);
    }

    public function test_responding_before_deadline_sets_sla_status_on_time(): void
    {
        $now = now();
        $caseNumber = 'SS-ONTIME-RESP';

        $reportId = DB::table('reports')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_number' => $caseNumber,
            'reporter_role' => 'VICTIM',
            'identity_mode' => 'CONFIDENTIAL',
            'category' => 'Verbal',
            'description' => 'Laporan perundungan yang direspon tepat waktu oleh guru BK.',
            'submitted_at' => $now->copy()->subHours(2),
            'created_at' => $now->copy()->subHours(2),
            'updated_at' => $now->copy()->subHours(2),
        ]);

        $caseId = DB::table('cases')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_id' => $reportId,
            'case_number' => $caseNumber,
            'category' => 'Verbal',
            'risk_level' => 'MEDIUM',
            'status' => 'PENDING_RESPONSE',
            'opened_at' => $now->copy()->subHours(2),
            'created_at' => $now->copy()->subHours(2),
            'updated_at' => $now->copy()->subHours(2),
        ]);

        DB::table('case_slas')->insert([
            'case_id' => $caseId,
            'status' => 'ON_TIME',
            'response_deadline' => $now->copy()->addHours(22),
            'initial_response_at' => null,
            'created_at' => $now->copy()->subHours(2),
            'updated_at' => $now->copy()->subHours(2),
        ]);

        // Counselor responds on time
        $response = $this->withSession($this->counselorSession())->post('/cases/'.$caseNumber.'/status', [
            'status' => 'UNDER_VERIFICATION',
            'reason' => 'Respons tepat waktu.',
        ]);

        $response->assertRedirect();

        // SLA status in case_slas table MUST be ON_TIME
        $sla = DB::table('case_slas')->where('case_id', $caseId)->first();
        $this->assertEquals('ON_TIME', $sla->status);
        $this->assertNotNull($sla->initial_response_at);

        // Case detail show page must display ON_TIME
        $showResponse = $this->withSession($this->counselorSession())->get('/cases/'.$caseNumber);
        $showResponse->assertOk();
        $showResponse->assertSee('ON_TIME');
    }

    public function test_inbox_sla_filters_correctly_separate_overdue_and_on_time(): void
    {
        $now = now();
        $overdueCaseNum = 'SS-FILTER-OVERDUE';
        $onTimeCaseNum = 'SS-FILTER-ONTIME';

        // 1. Overdue case (responded late)
        $r1 = DB::table('reports')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_number' => $overdueCaseNum,
            'reporter_role' => 'VICTIM',
            'identity_mode' => 'CONFIDENTIAL',
            'category' => 'Cyberbullying',
            'description' => 'Kasus yang responnya terlambat.',
            'submitted_at' => $now->copy()->subHours(30),
            'created_at' => $now->copy()->subHours(30),
            'updated_at' => $now->copy()->subHours(30),
        ]);
        $c1 = DB::table('cases')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_id' => $r1,
            'case_number' => $overdueCaseNum,
            'category' => 'Cyberbullying',
            'risk_level' => 'HIGH',
            'status' => 'UNDER_VERIFICATION',
            'opened_at' => $now->copy()->subHours(30),
            'created_at' => $now->copy()->subHours(30),
            'updated_at' => $now,
        ]);
        DB::table('case_slas')->insert([
            'case_id' => $c1,
            'status' => 'OVERDUE',
            'response_deadline' => $now->copy()->subHours(6),
            'initial_response_at' => $now,
            'resolution_status' => 'ON_TIME',
            'resolution_deadline' => $now->copy()->addDays(14),
            'created_at' => $now->copy()->subHours(30),
            'updated_at' => $now,
        ]);

        // 2. On-time case (responded on time)
        $r2 = DB::table('reports')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_number' => $onTimeCaseNum,
            'reporter_role' => 'VICTIM',
            'identity_mode' => 'CONFIDENTIAL',
            'category' => 'Verbal',
            'description' => 'Kasus yang responnya tepat waktu.',
            'submitted_at' => $now->copy()->subHours(2),
            'created_at' => $now->copy()->subHours(2),
            'updated_at' => $now->copy()->subHours(2),
        ]);
        $c2 = DB::table('cases')->insertGetId([
            'school_npsn' => $this->schoolNpsn,
            'report_id' => $r2,
            'case_number' => $onTimeCaseNum,
            'category' => 'Verbal',
            'risk_level' => 'MEDIUM',
            'status' => 'UNDER_VERIFICATION',
            'opened_at' => $now->copy()->subHours(2),
            'created_at' => $now->copy()->subHours(2),
            'updated_at' => $now,
        ]);
        DB::table('case_slas')->insert([
            'case_id' => $c2,
            'status' => 'ON_TIME',
            'response_deadline' => $now->copy()->addHours(22),
            'initial_response_at' => $now,
            'resolution_status' => 'ON_TIME',
            'resolution_deadline' => $now->copy()->addDays(14),
            'created_at' => $now->copy()->subHours(2),
            'updated_at' => $now,
        ]);

        // Filter by OVERDUE
        $resOverdue = $this->withSession($this->counselorSession())->get(route('reports.inbox', ['sla_status' => 'OVERDUE']));
        $resOverdue->assertOk();
        $resOverdue->assertSee($overdueCaseNum);
        $resOverdue->assertDontSee($onTimeCaseNum);

        // Filter by ON_TIME
        $resOnTime = $this->withSession($this->counselorSession())->get(route('reports.inbox', ['sla_status' => 'ON_TIME']));
        $resOnTime->assertOk();
        $resOnTime->assertSee($onTimeCaseNum);
        $resOnTime->assertDontSee($overdueCaseNum);
    }
}
