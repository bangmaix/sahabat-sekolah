<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->unsignedTinyInteger('category_score');
            $table->unsignedTinyInteger('safety_score');
            $table->unsignedTinyInteger('repetition_score');
            $table->unsignedTinyInteger('impact_score');
            $table->unsignedSmallInteger('final_score');
            $table->string('risk_level', 20);
            $table->string('assessed_by')->default('Bu Ratna Sari');
            $table->timestamps();
        });

        Schema::create('case_risk_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('old_risk_level', 20)->nullable();
            $table->string('new_risk_level', 20);
            $table->unsignedSmallInteger('score');
            $table->string('reason')->nullable();
            $table->string('changed_by')->default('Bu Ratna Sari');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_risk_histories');
        Schema::dropIfExists('case_risk_assessments');
    }
};
