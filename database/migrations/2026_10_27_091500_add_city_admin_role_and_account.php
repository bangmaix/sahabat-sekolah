<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // 1. Tambah Role CITY_ADMIN
        DB::table('roles')->insertOrIgnore([
            'code' => 'CITY_ADMIN',
            'name' => 'Dinas Pendidikan Kota Bukittinggi (City Admin)',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $roleId = DB::table('roles')->where('code', 'CITY_ADMIN')->value('id');

        // 2. Hubungkan Permissions yang relevan untuk CITY_ADMIN
        $permissions = [
            'MONITORING_VIEW',
            'REPORT_VIEW',
            'CASE_VIEW',
            'NOTIFICATION_VIEW',
            'STATISTICS_VIEW',
            'SCHOOL_MANAGE',
            'USER_MANAGE',
        ];

        foreach ($permissions as $code) {
            $permId = DB::table('permissions')->where('code', $code)->value('id');
            if ($permId && $roleId) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_id' => $roleId,
                    'permission_id' => $permId,
                ]);
            }
        }

        // 3. Buat / Perbarui Akun Resmi Dinas Pendidikan Kota Bukittinggi (Level 3 Escalation Account)
        DB::table('users')->updateOrInsert(
            ['email' => 'dinas@sahabat.test'],
            [
                'name' => 'Dinas Pendidikan Kota Bukittinggi',
                'password' => Hash::make('Dinas#Bkt2026!Secure'),
                'role' => 'CITY_ADMIN',
                'school_npsn' => null,
                'school_name' => 'Dinas Pendidikan & Kebudayaan Kota Bukittinggi',
                'status' => 'ACTIVE',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'dinas@sahabat.test')->delete();
        $roleId = DB::table('roles')->where('code', 'CITY_ADMIN')->value('id');
        if ($roleId) {
            DB::table('role_permissions')->where('role_id', $roleId)->delete();
            DB::table('roles')->where('id', $roleId)->delete();
        }
    }
};
