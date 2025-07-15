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
        Schema::create('recruitment_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('session_id')->comment('Link to session (not candidate directly)');
            $table->enum('assessment_type', ['cv_review', 'psikotes', 'tes_teori', 'interview_hr', 'interview_user', 'mcu']);

            // Scheduling Information
            $table->timestamp('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->string('location')->nullable();
            $table->text('meeting_link')->nullable();

            // Assessment Setup
            $table->json('assessor_ids')->nullable()->comment('Array of user IDs');
            $table->integer('duration_minutes')->nullable();

            // Results & Evaluation
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'failed', 'cancelled', 'no_show'])->default('scheduled');
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->decimal('max_score', 5, 2)->nullable();
            $table->decimal('passing_score', 5, 2)->nullable();

            // Flexible Assessment Data (JSON for extensibility)
            $table->json('assessment_data')->nullable()->comment('Flexible assessment data storage');
            /*
            Example assessment_data structure:
            - cv_review: {"education_match": 4, "experience_match": 5, "skills_match": 4}
            - psikotes: {"personality_score": 85, "iq_score": 120, "eq_score": 90}
            - tes_teori: {"technical_score": 88, "general_score": 92}
            - interview_hr: {"communication": 4, "attitude": 5, "cultural_fit": 4}
            - interview_user: {"technical_skill": 4, "experience": 5, "problem_solving": 4}
            - mcu: {"blood_pressure": "120/80", "heart_rate": "72", "overall_health": "fit"}
            */

            // Recommendations & Notes
            $table->enum('recommendation', ['strongly_recommend', 'recommend', 'neutral', 'not_recommend', 'medical_unfit'])->nullable();
            $table->text('assessor_notes')->nullable();
            $table->text('candidate_feedback')->nullable();

            // Supporting Documents
            $table->json('result_documents')->nullable()->comment('File paths for test results, reports, etc.');

            // Timeline
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('session_id')->references('id')->on('recruitment_sessions')->onDelete('cascade');

            // Business Rule: Only one assessment per type per session
            $table->unique(['session_id', 'assessment_type'], 'unique_session_assessment');

            // Indexes
            $table->index('assessment_type');
            $table->index('status');
            $table->index('scheduled_date');
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruitment_assessments');
    }
};
