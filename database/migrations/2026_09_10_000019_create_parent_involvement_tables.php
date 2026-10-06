<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->string('school_npsn', 20);
            $table->string('full_name');
            $table->string('contact')->nullable();
            $table->timestamps();
        });
        Schema::create('case_parent_involvements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained('parents')->cascadeOnDelete();
            $table->string('status', 20)->default('NOT_REQUIRED');
            $table->text('reason')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['case_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_parent_involvements');
        Schema::dropIfExists('parents');
    }
};