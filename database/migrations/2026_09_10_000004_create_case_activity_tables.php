<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->string('reason')->nullable();
            $table->string('changed_by')->default('Bu Ratna Sari');
            $table->timestamps();
        });

        Schema::create('case_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('action_type', 40);
            $table->text('description');
            $table->string('performed_by')->default('Bu Ratna Sari');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_actions');
        Schema::dropIfExists('case_status_histories');
    }
};
