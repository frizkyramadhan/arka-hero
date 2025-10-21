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
        // Drop roster_templates table as it's no longer needed
        // Roster configuration is now handled directly in levels table
        Schema::dropIfExists('roster_templates');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate roster_templates table if needed to rollback
        Schema::create('roster_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('work_days');
            $table->integer('off_days');
            $table->integer('cycle_length');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
};
