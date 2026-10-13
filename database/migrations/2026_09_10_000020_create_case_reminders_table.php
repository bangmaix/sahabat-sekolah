<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_slas', function (Blueprint $table) {
            $table->timestamp('warning_at')->nullable();
        });
        Schema::create('case_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('reminder_type', 20);
            $table->timestamp('scheduled_at');
            $table->timestamp('sent_at')->nullable();
            $table->string('recipient_name')->default('Bu Ratna Sari');
            $table->string('status', 20)->default('PENDING');
            $table->timestamps();
            $table->unique(['case_id', 'reminder_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_reminders');
        Schema::table('case_slas', function (Blueprint $table) {
            $table->dropColumn('warning_at');
        });
    }
};
