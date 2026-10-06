<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('schools');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not reversing since the API is the source of truth
    }
};
