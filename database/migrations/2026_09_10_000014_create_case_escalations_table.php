<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_escalations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('escalation_type', 20);
            $table->text('reason');
            $table->string('escalated_by')->default('Bu Ratna Sari');
            $table->string('notified_to')->default('Kepala Sekolah');
            $table->string('status', 20)->default('SENT');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_escalations');
    }
};
