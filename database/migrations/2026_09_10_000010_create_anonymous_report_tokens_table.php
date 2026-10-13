<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anonymous_report_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->unique()->constrained('reports')->cascadeOnDelete();
            $table->string('token_hash')->unique();
            $table->timestamp('last_access_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anonymous_report_tokens');
    }
};
