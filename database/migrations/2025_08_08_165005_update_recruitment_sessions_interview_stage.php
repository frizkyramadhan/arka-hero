<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added this import for DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First, update existing interview_hr and interview_user stages to 'interview'
        DB::statement("UPDATE recruitment_sessions SET current_stage = 'interview' WHERE current_stage IN ('interview_hr', 'interview_user')");

        // Then modify the enum to remove interview_hr and interview_user, add interview
        DB::statement("ALTER TABLE recruitment_sessions MODIFY COLUMN current_stage ENUM('cv_review','psikotes','tes_teori','interview','offering','mcu','hire','onboarding') NOT NULL DEFAULT 'cv_review'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to original enum
        DB::statement("ALTER TABLE recruitment_sessions MODIFY COLUMN current_stage ENUM('cv_review','psikotes','tes_teori','interview_hr','interview_user','offering','mcu','hire','onboarding') NOT NULL DEFAULT 'cv_review'");
    }
};
