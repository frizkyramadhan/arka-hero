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
            // Drop the existing foreign key constraint
            $table->dropForeign(['closed_by']);
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            // Change the column type to match users table (bigint unsigned)
            $table->unsignedBigInteger('closed_by')->nullable()->change();
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            // Add new foreign key constraint to users table
            $table->foreign('closed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // Drop the users foreign key constraint
            $table->dropForeign(['closed_by']);

            // Change back to char(36) for employees table
            $table->char('closed_by', 36)->nullable()->change();

            // Add back the employees foreign key constraint
            $table->foreign('closed_by')->references('id')->on('employees')->onDelete('set null');
        });
    }
};
