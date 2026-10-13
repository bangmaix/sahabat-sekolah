<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('module');
            $table->timestamps();
        });
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        $now = now();
        DB::table('roles')->insert([
            ['code' => 'COUNSELOR', 'name' => 'Guru Bimbingan Konseling', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'PRINCIPAL', 'name' => 'Kepala Sekolah', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $permissions = [
            ['code' => 'REPORT_VIEW', 'name' => 'Melihat inbox laporan', 'module' => 'REPORT'],
            ['code' => 'CASE_VIEW', 'name' => 'Melihat detail kasus', 'module' => 'CASE'],
            ['code' => 'CASE_UPDATE', 'name' => 'Mengubah status kasus', 'module' => 'CASE'],
            ['code' => 'CASE_NOTE_CREATE', 'name' => 'Membuat catatan kasus', 'module' => 'CASE'],
            ['code' => 'RISK_ASSESS', 'name' => 'Menilai risiko kasus', 'module' => 'RISK'],
            ['code' => 'EVIDENCE_UPLOAD', 'name' => 'Mengunggah bukti', 'module' => 'EVIDENCE'],
            ['code' => 'CASE_ESCALATE', 'name' => 'Melakukan eskalasi', 'module' => 'ESCALATION'],
            ['code' => 'NOTIFICATION_VIEW', 'name' => 'Melihat notifikasi', 'module' => 'NOTIFICATION'],
            ['code' => 'MONITORING_VIEW', 'name' => 'Melihat monitoring sekolah', 'module' => 'MONITORING'],
        ];
        DB::table('permissions')->insert(array_map(fn ($permission) => $permission + ['created_at' => $now, 'updated_at' => $now], $permissions));
        $roles = DB::table('roles')->pluck('id', 'code');
        $permissionIds = DB::table('permissions')->pluck('id', 'code');
        $counselor = ['REPORT_VIEW', 'CASE_VIEW', 'CASE_UPDATE', 'CASE_NOTE_CREATE', 'RISK_ASSESS', 'EVIDENCE_UPLOAD', 'CASE_ESCALATE', 'NOTIFICATION_VIEW'];
        $principal = ['REPORT_VIEW', 'CASE_VIEW', 'NOTIFICATION_VIEW', 'MONITORING_VIEW'];
        foreach ($counselor as $code) {
            DB::table('role_permissions')->insert(['role_id' => $roles['COUNSELOR'], 'permission_id' => $permissionIds[$code]]);
        }
        foreach ($principal as $code) {
            DB::table('role_permissions')->insert(['role_id' => $roles['PRINCIPAL'], 'permission_id' => $permissionIds[$code]]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
