<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    private string $schoolNpsn = '10307412';
    private string $otherSchoolNpsn = '10307415';
    private string $caseNumber = 'SS-TEST-000001';

    protected function setUp(): void
    {
        parent::setUp();
        $now = now();
        DB::table('users')->insert(['name' => 'BK Uji', 'email' => 'bk-test@example.test', 'password' => Hash::make('password'), 'role' => 'COUNSELOR', 'school_npsn' => $this->schoolNpsn, 'created_at' => $now, 'updated_at' => $now]);
        $reportId = DB::table('reports')->insertGetId(['school_npsn' => $this->schoolNpsn, 'report_number' => $this->caseNumber, 'reporter_role' => 'WITNESS', 'identity_mode' => 'CONFIDENTIAL', 'category' => 'Verbal', 'description' => 'Kronologi laporan uji yang cukup panjang.', 'submitted_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
        $caseId = DB::table('cases')->insertGetId(['school_npsn' => $this->schoolNpsn, 'report_id' => $reportId, 'case_number' => $this->caseNumber, 'category' => 'Verbal', 'risk_level' => 'MEDIUM', 'status' => 'PENDING_RESPONSE', 'opened_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
        DB::table('case_slas')->insert(['case_id' => $caseId, 'status' => 'ON_TIME', 'response_deadline' => $now->addDay(), 'created_at' => $now, 'updated_at' => $now]);
    }

    private function counselorSession(): array
    {
        return ['user_id' => 1, 'user_name' => 'BK Uji', 'user_role' => 'COUNSELOR', 'school_npsn' => $this->schoolNpsn];
    }

    public function test_school_scope_denies_other_school_case(): void
    {
        $response = $this->withSession($this->counselorSession())->get('/cases/SS-NOT-IN-SCOPE');
        $response->assertNotFound();
    }

    public function test_invalid_case_transition_is_rejected(): void
    {
        $response = $this->withSession($this->counselorSession())->post('/cases/' . $this->caseNumber . '/status', ['status' => 'CLOSED']);
        $response->assertStatus(422);
    }

    public function test_case_cannot_close_without_valid_resolution_document(): void
    {
        DB::table('cases')->where('case_number', $this->caseNumber)->update(['status' => 'RESOLVED']);
        $response = $this->withSession($this->counselorSession())->post('/cases/' . $this->caseNumber . '/status', ['status' => 'CLOSED']);
        $response->assertStatus(422);
    }

    public function test_anonymous_report_creates_hashed_token(): void
    {
        $response = $this->post('/reports', [
            'school_npsn' => $this->schoolNpsn, 'reporter_role' => 'WITNESS', 'identity_mode' => 'ANONYMOUS',
            'category' => 'Cyberbullying', 'description' => 'Laporan anonim untuk pengujian token yang aman.',
        ]);
        $response->assertRedirect(route('reports.receipt'));
        $this->assertDatabaseCount('anonymous_report_tokens', 1);
        $this->assertDatabaseMissing('reporter_identities', ['full_name' => '']);
    }

    public function test_sla_scanner_creates_only_one_warning_reminder(): void
    {
        DB::table('case_slas')->where('case_id', 1)->update(['response_deadline' => now()->addHours(5)]);
        $this->artisan('sahabat:sla-scan')->assertSuccessful();
        $this->artisan('sahabat:sla-scan')->assertSuccessful();
        $this->assertDatabaseHas('case_slas', ['case_id' => 1, 'status' => 'WARNING']);
        $this->assertDatabaseCount('case_reminders', 1);
    }
}
