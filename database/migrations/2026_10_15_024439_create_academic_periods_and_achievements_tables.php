<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Academic Periods (Master Periode Dapodik / Semester)
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->string('semester_id', 10)->unique();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_fallback')->default(false);
            $table->integer('order_no')->default(0);
            $table->timestamps();
        });

        // 2. Achievement Categories (Kategori Prestasi Siswa)
        Schema::create('achievement_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // 3. Student Achievements (Data Prestasi Siswa)
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id();
            $table->string('school_npsn', 20)->index();
            $table->string('nisn', 20)->index();
            $table->string('student_name');
            $table->string('class_name')->nullable();
            $table->string('semester_id', 10)->nullable();
            $table->foreignId('category_id')->constrained('achievement_categories')->cascadeOnDelete();
            $table->string('achievement_name');
            $table->string('achievement_level', 50); // SEKOLAH, KECAMATAN, KOTA, PROVINSI, NASIONAL, INTERNASIONAL
            $table->string('ranking', 100); // Juara 1, Juara 2, Juara 3, Harapan 1, Medali Emas, dll.
            $table->string('organizer')->nullable(); // Penyelenggara
            $table->date('achievement_date');
            $table->text('description')->nullable();
            $table->string('certificate_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $now = now();

        // Seed initial academic periods
        DB::table('academic_periods')->insert([
            ['semester_id' => '20261', 'name' => 'Tahun Ajaran 2026/2027 Ganjil', 'is_active' => true, 'is_fallback' => false, 'order_no' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['semester_id' => '20252', 'name' => 'Tahun Ajaran 2025/2026 Genap', 'is_active' => false, 'is_fallback' => true, 'order_no' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['semester_id' => '20251', 'name' => 'Tahun Ajaran 2025/2026 Ganjil', 'is_active' => false, 'is_fallback' => false, 'order_no' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['semester_id' => '20242', 'name' => 'Tahun Ajaran 2024/2025 Genap', 'is_active' => false, 'is_fallback' => false, 'order_no' => 4, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Seed initial achievement categories
        $categories = [
            ['code' => 'AKADEMIK', 'name' => 'Akademik', 'description' => 'Sains, Matematika, Karya Tulis Ilmiah, Olimpiade Mata Pelajaran, Cerdas Cermat'],
            ['code' => 'OLAHRAGA', 'name' => 'Olahraga', 'description' => 'Atletik, Pencak Silat, Renang, Sepak Bola, Bulu Tangkis, Futsal, dan cabor lainnya'],
            ['code' => 'SENI_BUDAYA', 'name' => 'Seni & Budaya', 'description' => 'Tari Tradisional/Kreasi, Musik, Menyanyi/Vokal, Melukis/Menggambar, Sastra, Teater'],
            ['code' => 'KEAGAMAAN', 'name' => 'Keagamaan', 'description' => 'Musabaqah Tilawatil Quran (MTQ), Tahfidz Quran, Da\'i Cilik, Kaligrafi, Hadrah'],
            ['code' => 'RISET_TEKNOLOGI', 'name' => 'Riset & Teknologi', 'description' => 'Robotika, Pemrograman/Coding, Inovasi Digital, Desain Grafis, Animasi'],
            ['code' => 'KEPEMIMPINAN', 'name' => 'Kepemimpinan & Organisasi', 'description' => 'Pramuka Garuda, PMR/KSR, Pasukan Pengibar Bendera (Paskibra), OSIS Teladan'],
        ];

        foreach ($categories as $cat) {
            DB::table('achievement_categories')->insert($cat + ['status' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Add RBAC permissions if roles and permissions tables exist
        if (Schema::hasTable('permissions') && Schema::hasTable('roles') && Schema::hasTable('role_permissions')) {
            DB::table('permissions')->insertOrIgnore([
                ['code' => 'ACHIEVEMENT_VIEW', 'name' => 'Melihat data prestasi siswa', 'module' => 'ACHIEVEMENT', 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'ACHIEVEMENT_MANAGE', 'name' => 'Mengelola data prestasi siswa', 'module' => 'ACHIEVEMENT', 'created_at' => $now, 'updated_at' => $now],
            ]);

            $roles = DB::table('roles')->pluck('id', 'code');
            $permissions = DB::table('permissions')->pluck('id', 'code');

            if (isset($roles['COUNSELOR'])) {
                if (isset($permissions['ACHIEVEMENT_VIEW'])) {
                    DB::table('role_permissions')->insertOrIgnore(['role_id' => $roles['COUNSELOR'], 'permission_id' => $permissions['ACHIEVEMENT_VIEW']]);
                }
                if (isset($permissions['ACHIEVEMENT_MANAGE'])) {
                    DB::table('role_permissions')->insertOrIgnore(['role_id' => $roles['COUNSELOR'], 'permission_id' => $permissions['ACHIEVEMENT_MANAGE']]);
                }
            }

            if (isset($roles['PRINCIPAL']) && isset($permissions['ACHIEVEMENT_VIEW'])) {
                DB::table('role_permissions')->insertOrIgnore(['role_id' => $roles['PRINCIPAL'], 'permission_id' => $permissions['ACHIEVEMENT_VIEW']]);
            }

            if (isset($roles['ADMIN']) && isset($permissions['ACHIEVEMENT_VIEW'])) {
                DB::table('role_permissions')->insertOrIgnore(['role_id' => $roles['ADMIN'], 'permission_id' => $permissions['ACHIEVEMENT_VIEW']]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->whereIn('code', ['ACHIEVEMENT_VIEW', 'ACHIEVEMENT_MANAGE'])->delete();
        }

        Schema::dropIfExists('student_achievements');
        Schema::dropIfExists('achievement_categories');
        Schema::dropIfExists('academic_periods');
    }
};
