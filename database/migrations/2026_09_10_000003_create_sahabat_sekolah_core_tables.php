<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // API AConnect will be used for schools data
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('school_npsn', 20);
            $table->string('report_number')->unique();
            $table->string('reporter_role', 40);
            $table->string('identity_mode', 40);
            $table->string('category', 80);
            $table->string('subcategory', 120)->nullable();
            $table->text('description');
            $table->timestamp('submitted_at');
            $table->timestamps();
        });

        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('school_npsn', 20);
            $table->foreignId('report_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('case_number')->unique();
            $table->string('category', 80);
            $table->string('risk_level', 20);
            $table->string('status', 30);
            $table->string('assigned_to')->default('Bu Ratna Sari');
            $table->timestamp('opened_at');
            $table->timestamps();
        });

        Schema::create('case_slas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status', 20);
            $table->timestamp('response_deadline');
            $table->timestamp('initial_response_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_slas');
        Schema::dropIfExists('cases');
        Schema::dropIfExists('reports');
        // schools table removed
    }
};
