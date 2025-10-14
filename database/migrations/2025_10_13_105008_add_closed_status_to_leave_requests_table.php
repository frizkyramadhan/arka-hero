<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify enum to add 'closed' status
        DB::statement("ALTER TABLE `leave_requests` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'cancelled', 'auto_approved', 'closed') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum to original values
        DB::statement("ALTER TABLE `leave_requests` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'cancelled', 'auto_approved') DEFAULT 'pending'");
    }
};
