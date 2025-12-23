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
        // Drop dependent tables first (those with foreign keys to rosters)
        Schema::dropIfExists('roster_daily_status');
        Schema::dropIfExists('roster_adjustments');
        Schema::dropIfExists('roster_histories');
        Schema::dropIfExists('roster_cycles');

        // Now drop old rosters table
        Schema::dropIfExists('rosters');

        // Create new clean rosters table
        Schema::create('rosters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('employee_id', 36);
            $table->bigInteger('administration_id')->unsigned();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('administration_id')->references('id')->on('administrations')->onDelete('cascade');

            // Index for better performance
            $table->unique(['employee_id', 'administration_id']);
            $table->index('administration_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rosters');
    }
};
