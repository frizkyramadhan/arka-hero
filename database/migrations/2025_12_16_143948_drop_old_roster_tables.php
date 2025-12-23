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
        // Drop old roster_templates table (others already dropped in create_new_rosters_table)
        Schema::dropIfExists('roster_templates');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to recreate old tables
    }
};
