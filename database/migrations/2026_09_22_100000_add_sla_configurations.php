<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('risk_level', 20)->unique(); // LOW, MEDIUM, HIGH, CRITICAL
            $table->integer('response_time_hours');
            $table->integer('resolution_time_days');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Insert defaults
        $now = now();
        DB::table('sla_configurations')->insert([
            ['risk_level' => 'CRITICAL', 'response_time_hours' => 1, 'resolution_time_days' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['risk_level' => 'HIGH', 'response_time_hours' => 4, 'resolution_time_days' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['risk_level' => 'MEDIUM', 'response_time_hours' => 12, 'resolution_time_days' => 7, 'created_at' => $now, 'updated_at' => $now],
            ['risk_level' => 'LOW', 'response_time_hours' => 24, 'resolution_time_days' => 14, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('case_slas', function (Blueprint $table) {
            $table->timestamp('resolution_deadline')->nullable()->after('response_deadline');
            $table->string('resolution_status', 20)->default('ON_TRACK')->after('resolution_deadline');
            $table->timestamp('resolved_at')->nullable()->after('initial_response_at');
        });
    }

    public function down(): void
    {
        Schema::table('case_slas', function (Blueprint $table) {
            $table->dropColumn(['resolution_deadline', 'resolution_status', 'resolved_at']);
        });
        Schema::dropIfExists('sla_configurations');
    }
};
