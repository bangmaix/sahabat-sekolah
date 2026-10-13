<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
        });

        Schema::create('case_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('uploaded_by')->default('Bu Ratna Sari');
            $table->timestamps();
        });

        Schema::create('case_resolution_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('verification_status')->default('VALID');
            $table->string('uploaded_by')->default('Bu Ratna Sari');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_resolution_documents');
        Schema::dropIfExists('case_evidences');
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn(['resolved_at', 'closed_at']);
        });
    }
};
