<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Align enum columns to new stage flow
        try {
            DB::statement("ALTER TABLE recruitment_sessions MODIFY COLUMN current_stage ENUM('cv_review','psikotes','tes_teori','interview_hr','interview_user','offering','mcu','hire','onboarding') NOT NULL DEFAULT 'cv_review'");
        } catch (\Throwable $e) {
            // ignore if DB/driver doesn't support or column not existing yet
        }

        try {
            DB::statement("ALTER TABLE recruitment_sessions MODIFY COLUMN stage_status ENUM('pending','in_progress','completed','failed','skipped') NOT NULL DEFAULT 'pending'");
        } catch (\Throwable $e) {
            // ignore
        }

        Schema::table('recruitment_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('recruitment_sessions', 'stage_started_at')) {
                $table->timestamp('stage_started_at')->nullable()->after('stage_status');
            }
            if (!Schema::hasColumn('recruitment_sessions', 'stage_completed_at')) {
                $table->timestamp('stage_completed_at')->nullable()->after('stage_started_at');
            }
            if (!Schema::hasColumn('recruitment_sessions', 'overall_progress')) {
                $table->decimal('overall_progress', 5, 2)->default(0)->after('stage_completed_at');
            }
            if (!Schema::hasColumn('recruitment_sessions', 'next_action')) {
                $table->text('next_action')->nullable()->after('overall_progress');
            }
            if (!Schema::hasColumn('recruitment_sessions', 'responsible_person_id')) {
                $table->unsignedBigInteger('responsible_person_id')->nullable()->after('next_action');
                $table->foreign('responsible_person_id')->references('id')->on('users')->onDelete('set null');
            }

            // Final decision fields
            if (!Schema::hasColumn('recruitment_sessions', 'final_decision_date')) {
                $table->timestamp('final_decision_date')->nullable()->after('status');
            }
            if (!Schema::hasColumn('recruitment_sessions', 'final_decision_by')) {
                $table->unsignedBigInteger('final_decision_by')->nullable()->after('final_decision_date');
                $table->foreign('final_decision_by')->references('id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('recruitment_sessions', 'final_decision_notes')) {
                $table->text('final_decision_notes')->nullable()->after('final_decision_by');
            }

            // Stage durations JSON
            if (!Schema::hasColumn('recruitment_sessions', 'stage_durations')) {
                $table->json('stage_durations')->nullable()->after('final_decision_notes');
            }

            // Tracking
            if (!Schema::hasColumn('recruitment_sessions', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('candidate_id');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            }

            // Remove legacy/unused columns if present
            if (Schema::hasColumn('recruitment_sessions', 'final_status')) {
                $table->dropColumn('final_status');
            }
        });

        // Indexes (safe to attempt create; DB will ignore duplicates on some drivers, otherwise wrap try/catch)
        try {
            Schema::table('recruitment_sessions', function (Blueprint $table) {
                $table->index('current_stage');
                $table->index('status');
                $table->index('applied_date');
                $table->index('fptk_id');
                $table->index('candidate_id');
            });
        } catch (\Throwable $e) {
            // ignore if already indexed
        }
    }

    public function down(): void
    {
        Schema::table('recruitment_sessions', function (Blueprint $table) {
            // Best-effort revert of added columns
            if (Schema::hasColumn('recruitment_sessions', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('recruitment_sessions', 'responsible_person_id')) {
                $table->dropForeign(['responsible_person_id']);
                $table->dropColumn('responsible_person_id');
            }
            if (Schema::hasColumn('recruitment_sessions', 'final_decision_by')) {
                $table->dropForeign(['final_decision_by']);
                $table->dropColumn('final_decision_by');
            }
            if (Schema::hasColumn('recruitment_sessions', 'final_decision_date')) {
                $table->dropColumn('final_decision_date');
            }
            if (Schema::hasColumn('recruitment_sessions', 'final_decision_notes')) {
                $table->dropColumn('final_decision_notes');
            }
            if (Schema::hasColumn('recruitment_sessions', 'stage_durations')) {
                $table->dropColumn('stage_durations');
            }
            if (Schema::hasColumn('recruitment_sessions', 'next_action')) {
                $table->dropColumn('next_action');
            }
            if (Schema::hasColumn('recruitment_sessions', 'overall_progress')) {
                $table->dropColumn('overall_progress');
            }
            if (Schema::hasColumn('recruitment_sessions', 'stage_completed_at')) {
                $table->dropColumn('stage_completed_at');
            }
            if (Schema::hasColumn('recruitment_sessions', 'stage_started_at')) {
                $table->dropColumn('stage_started_at');
            }
        });

        // Note: enums not reverted to previous sets to avoid data loss
    }
};
