<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('participant_type', 30);
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('display_name');
            $table->string('identity_visibility', 30)->default('CASE_RESTRICTED');
            $table->string('status', 20)->default('ACTIVE');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_participants');
    }
};
