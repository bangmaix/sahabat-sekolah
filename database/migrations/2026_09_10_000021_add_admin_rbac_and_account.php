<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        DB::table('roles')->insertOrIgnore(['code' => 'ADMIN', 'name' => 'Administrator', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('permissions')->insertOrIgnore([
            ['code' => 'USER_MANAGE', 'name' => 'Mengelola pengguna', 'module' => 'ADMIN', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'SCHOOL_MANAGE', 'name' => 'Mengelola sekolah', 'module' => 'ADMIN', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'MASTER_DATA_MANAGE', 'name' => 'Mengelola master data', 'module' => 'ADMIN', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $roleId = DB::table('roles')->where('code', 'ADMIN')->value('id');
        foreach (['USER_MANAGE', 'SCHOOL_MANAGE', 'MASTER_DATA_MANAGE'] as $code) {
            DB::table('role_permissions')->insertOrIgnore(['role_id' => $roleId, 'permission_id' => DB::table('permissions')->where('code', $code)->value('id')]);
        }
        DB::table('users')->updateOrInsert(['email' => 'admin@sahabat.test'], ['name' => 'Administrator Sahabat Sekolah', 'password' => Hash::make('password'), 'role' => 'ADMIN', 'school_npsn' => null, 'created_at' => $now, 'updated_at' => $now]);
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'admin@sahabat.test')->delete();
        DB::table('roles')->where('code', 'ADMIN')->delete();
        DB::table('permissions')->whereIn('code', ['USER_MANAGE', 'SCHOOL_MANAGE', 'MASTER_DATA_MANAGE'])->delete();
    }
};