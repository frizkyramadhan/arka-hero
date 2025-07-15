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
        Schema::create('recruitment_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('session_number', 50)->unique()->comment('RSN/2024/01/001');

            // Core Relationship
            $table->uuid('fptk_id')->comment('Link to FPTK');
            $table->uuid('candidate_id')->comment('Link to Candidate');

            // Session Context
            $table->date('applied_date');
            $table->string('source', 100)->comment('Application source');

            // Current Assessment Progress
            $table->enum('current_stage', [
                'cv_review',
                'psikotes',
                'tes_teori',
                'interview_hr',
                'interview_user',
                'offering',
                'mcu',
                'hire',
                'onboarding'
            ])->default('cv_review');
            $table->enum('stage_status', ['pending', 'in_progress', 'completed', 'failed', 'skipped'])->default('pending');

            // Timeline Tracking
            $table->timestamp('stage_started_at')->nullable();
            $table->timestamp('stage_completed_at')->nullable();

            // Progress Management
            $table->decimal('overall_progress', 5, 2)->default(0)->comment('Percentage completion');
            $table->text('next_action')->nullable();
            $table->unsignedBigInteger('responsible_person_id')->nullable();

            // Final Decision
            $table->enum('final_status', ['in_process', 'hired', 'rejected', 'withdrawn', 'cancelled'])->default('in_process');
            $table->timestamp('final_decision_date')->nullable();
            $table->unsignedBigInteger('final_decision_by')->nullable();
            $table->text('final_decision_notes')->nullable();

            // Duration tracking JSON field for stage durations
            $table->json('stage_durations')->nullable()->comment('Duration tracking for each stage');

            $table->timestamps();

            // Foreign Keys
            $table->foreign('fptk_id')->references('id')->on('recruitment_requests')->onDelete('cascade');
            $table->foreign('candidate_id')->references('id')->on('recruitment_candidates')->onDelete('cascade');
            $table->foreign('responsible_person_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('final_decision_by')->references('id')->on('users')->onDelete('set null');

            // Business Rule: 1 candidate can only have 1 session per FPTK
            $table->unique(['fptk_id', 'candidate_id'], 'unique_fptk_candidate_session');

            // Indexes
            $table->index('current_stage');
            $table->index('final_status');
            $table->index('applied_date');
            $table->index('fptk_id');
            $table->index('candidate_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruitment_sessions');
    }
};
