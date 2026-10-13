<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bullying_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::create('bullying_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('bullying_categories')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('default_risk_level', 20)->default('LOW');
            $table->unsignedTinyInteger('risk_score')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->unique(['category_id', 'code']);
        });

        $now = now();
        $categories = [
            ['code' => 'PHYSICAL', 'name' => 'Perundungan Fisik', 'description' => 'Tindakan fisik yang menyebabkan rasa sakit atau intimidasi'],
            ['code' => 'VERBAL', 'name' => 'Perundungan Verbal', 'description' => 'Ucapan, hinaan, ancaman, atau perkataan merendahkan'],
            ['code' => 'SOCIAL', 'name' => 'Perundungan Sosial', 'description' => 'Pengucilan dan perusakan relasi sosial'],
            ['code' => 'CYBER', 'name' => 'Cyberbullying', 'description' => 'Perundungan melalui media digital'],
            ['code' => 'SEXUAL', 'name' => 'Perundungan Bernuansa Seksual', 'description' => 'Tindakan atau komunikasi bernuansa seksual yang tidak pantas'],
            ['code' => 'OTHER', 'name' => 'Lainnya', 'description' => 'Bentuk perundungan lain'],
        ];
        DB::table('bullying_categories')->insert(array_map(fn ($category) => $category + ['created_at' => $now, 'updated_at' => $now], $categories));
        $ids = DB::table('bullying_categories')->pluck('id', 'code');
        $subcategories = [
            ['category_id' => $ids['PHYSICAL'], 'code' => 'PUSHING', 'name' => 'Mendorong atau menjegal', 'default_risk_level' => 'MEDIUM', 'risk_score' => 35],
            ['category_id' => $ids['PHYSICAL'], 'code' => 'HITTING', 'name' => 'Memukul atau menendang', 'default_risk_level' => 'HIGH', 'risk_score' => 60],
            ['category_id' => $ids['VERBAL'], 'code' => 'INSULT', 'name' => 'Hinaan atau julukan merendahkan', 'default_risk_level' => 'MEDIUM', 'risk_score' => 30],
            ['category_id' => $ids['VERBAL'], 'code' => 'THREAT', 'name' => 'Ancaman', 'default_risk_level' => 'HIGH', 'risk_score' => 55],
            ['category_id' => $ids['SOCIAL'], 'code' => 'EXCLUSION', 'name' => 'Pengucilan', 'default_risk_level' => 'MEDIUM', 'risk_score' => 30],
            ['category_id' => $ids['CYBER'], 'code' => 'DIGITAL_THREAT', 'name' => 'Ancaman digital', 'default_risk_level' => 'HIGH', 'risk_score' => 60],
            ['category_id' => $ids['CYBER'], 'code' => 'IMAGE_SHARING', 'name' => 'Penyebaran gambar tanpa izin', 'default_risk_level' => 'HIGH', 'risk_score' => 65],
            ['category_id' => $ids['SEXUAL'], 'code' => 'SEXUAL_HARASSMENT', 'name' => 'Komunikasi seksual tidak pantas', 'default_risk_level' => 'CRITICAL', 'risk_score' => 80],
            ['category_id' => $ids['OTHER'], 'code' => 'OTHER', 'name' => 'Bentuk lainnya', 'default_risk_level' => 'LOW', 'risk_score' => 10],
        ];
        DB::table('bullying_subcategories')->insert(array_map(fn ($subcategory) => $subcategory + ['created_at' => $now, 'updated_at' => $now], $subcategories));
    }

    public function down(): void
    {
        Schema::dropIfExists('bullying_subcategories');
        Schema::dropIfExists('bullying_categories');
    }
};
