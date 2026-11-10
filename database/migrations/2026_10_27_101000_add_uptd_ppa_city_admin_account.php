<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // 1. Perbarui deskripsi role CITY_ADMIN agar mencerminkan Satgas PPKSP / Multi-Instansi Kota
        DB::table('roles')->where('code', 'CITY_ADMIN')->update([
            'name' => 'Satgas PPKSP / Instansi Kota (Dinas Pendidikan & UPTD PPA)',
            'updated_at' => $now,
        ]);

        // 2. Buat / Perbarui Akun Resmi UPTD PPA (DP3APPKB Kota Bukittinggi)
        DB::table('users')->updateOrInsert(
            ['email' => 'ppa@sahabat.test'],
            [
                'name' => 'UPTD PPA Kota Bukittinggi',
                'password' => Hash::make('Ppa#Bkt2026!Secure'),
                'role' => 'CITY_ADMIN',
                'school_npsn' => null,
                'school_name' => 'UPTD PPA - DP3APPKB Kota Bukittinggi',
                'status' => 'ACTIVE',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'ppa@sahabat.test')->delete();

        DB::table('roles')->where('code', 'CITY_ADMIN')->update([
            'name' => 'Dinas Pendidikan Kota Bukittinggi (City Admin)',
            'updated_at' => now(),
        ]);
    }
};
