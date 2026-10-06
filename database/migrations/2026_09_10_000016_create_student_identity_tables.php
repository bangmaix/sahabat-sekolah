<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('school_npsn', 20);
            $table->string('full_name');
            $table->string('student_number')->nullable();
            $table->string('class_name')->nullable();
            $table->string('status', 20)->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('reporter_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->unique()->constrained('reports')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('full_name')->nullable();
            $table->string('contact')->nullable();
            $table->string('access_level', 30)->default('CASE_RESTRICTED');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporter_identities');
        Schema::dropIfExists('students');
    }
};