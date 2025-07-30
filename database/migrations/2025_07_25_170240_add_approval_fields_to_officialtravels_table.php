<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('officialtravels', function (Blueprint $table) {
            // Add approval-related fields first
            $table->timestamp('submit_at')->nullable();
            $table->timestamp('approved_at')->nullable();
        });

        // Rename column in separate operation
        Schema::table('officialtravels', function (Blueprint $table) {
            $table->renameColumn('official_travel_status', 'status');
        });

        // Alter status column to enum
        Schema::table('officialtravels', function (Blueprint $table) {
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'cancelled', 'closed'])->default('draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert status column to string first
        Schema::table('officialtravels', function (Blueprint $table) {
            $table->string('status')->change();
        });

        // Rename column back
        Schema::table('officialtravels', function (Blueprint $table) {
            $table->renameColumn('status', 'official_travel_status');
        });

        // Then drop the added columns
        Schema::table('officialtravels', function (Blueprint $table) {
            $table->dropColumn(['submit_at', 'approved_at']);
        });
    }
};
