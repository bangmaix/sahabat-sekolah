<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        DB::table('permissions')->insertOrIgnore(['code' => 'STATISTICS_VIEW', 'name' => 'Melihat statistik agregat', 'module' => 'REPORT', 'created_at' => $now, 'updated_at' => $now]);
        $permissionId = DB::table('permissions')->where('code', 'STATISTICS_VIEW')->value('id');
        foreach (['COUNSELOR', 'PRINCIPAL'] as $roleCode) {
            DB::table('role_permissions')->insertOrIgnore(['role_id' => DB::table('roles')->where('code', $roleCode)->value('id'), 'permission_id' => $permissionId]);
        }
    }

    public function down(): void
    {
        DB::table('permissions')->where('code', 'STATISTICS_VIEW')->delete();
    }
};
