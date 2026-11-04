<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('recruitment_sessions')) {
            return;
        }

        // MySQL ENUM: add forwarded for reuse/continue-to-next-FPTK outcome.
        DB::statement("ALTER TABLE recruitment_sessions MODIFY COLUMN status ENUM('in_process', 'hired', 'rejected', 'withdrawn', 'cancelled', 'forwarded') NOT NULL DEFAULT 'in_process'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('recruitment_sessions')) {
            return;
        }

        DB::table('recruitment_sessions')
            ->where('status', 'forwarded')
            ->update(['status' => 'cancelled']);

        DB::statement("ALTER TABLE recruitment_sessions MODIFY COLUMN status ENUM('in_process', 'hired', 'rejected', 'withdrawn', 'cancelled') NOT NULL DEFAULT 'in_process'");
    }
};
