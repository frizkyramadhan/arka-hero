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
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->text('reason')->nullable()->change();
        });

        // Drop foreign key constraints first
        Schema::table('leave_calculations', function (Blueprint $table) {
            $table->dropForeign(['leave_request_id']);
        });

        Schema::table('roster_adjustments', function (Blueprint $table) {
            $table->dropForeign(['leave_request_id']);
        });

        // Change ID from integer to UUID
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropPrimary(['id']);
            $table->uuid('id')->primary()->change();
        });

        // Update foreign key columns to UUID
        Schema::table('leave_calculations', function (Blueprint $table) {
            $table->string('leave_request_id', 36)->change();
        });

        Schema::table('roster_adjustments', function (Blueprint $table) {
            $table->string('leave_request_id', 36)->nullable()->change();
        });

        // Recreate foreign key constraints
        Schema::table('leave_calculations', function (Blueprint $table) {
            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onDelete('cascade');
        });

        Schema::table('roster_adjustments', function (Blueprint $table) {
            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraints first
        Schema::table('leave_calculations', function (Blueprint $table) {
            $table->dropForeign(['leave_request_id']);
        });

        Schema::table('roster_adjustments', function (Blueprint $table) {
            $table->dropForeign(['leave_request_id']);
        });

        // Revert foreign key columns to bigint
        Schema::table('leave_calculations', function (Blueprint $table) {
            $table->unsignedBigInteger('leave_request_id')->change();
        });

        Schema::table('roster_adjustments', function (Blueprint $table) {
            $table->unsignedBigInteger('leave_request_id')->nullable()->change();
        });

        // Revert ID back to integer
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropPrimary(['id']);
            $table->unsignedBigInteger('id')->primary()->change();
        });

        // Recreate foreign key constraints
        Schema::table('leave_calculations', function (Blueprint $table) {
            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onDelete('cascade');
        });

        Schema::table('roster_adjustments', function (Blueprint $table) {
            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onDelete('set null');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->text('reason')->nullable(false)->change();
        });
    }
};
