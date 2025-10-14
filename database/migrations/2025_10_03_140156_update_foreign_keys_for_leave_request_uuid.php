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
        // Update leave_calculations table
        Schema::table('leave_calculations', function (Blueprint $table) {
            // Drop existing foreign key constraint
            $table->dropForeign(['leave_request_id']);

            // Change column type to UUID
            $table->string('leave_request_id', 36)->change();

            // Recreate foreign key constraint
            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onDelete('cascade');
        });

        // Update roster_adjustments table
        Schema::table('roster_adjustments', function (Blueprint $table) {
            // Drop existing foreign key constraint
            $table->dropForeign(['leave_request_id']);

            // Change column type to UUID
            $table->string('leave_request_id', 36)->nullable()->change();

            // Recreate foreign key constraint
            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert leave_calculations table
        Schema::table('leave_calculations', function (Blueprint $table) {
            // Drop existing foreign key constraint
            $table->dropForeign(['leave_request_id']);

            // Change column type back to bigint
            $table->unsignedBigInteger('leave_request_id')->change();

            // Recreate foreign key constraint
            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onDelete('cascade');
        });

        // Revert roster_adjustments table
        Schema::table('roster_adjustments', function (Blueprint $table) {
            // Drop existing foreign key constraint
            $table->dropForeign(['leave_request_id']);

            // Change column type back to bigint
            $table->unsignedBigInteger('leave_request_id')->nullable()->change();

            // Recreate foreign key constraint
            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onDelete('set null');
        });
    }
};
