<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Log::info('Starting to drop old recruitment tables...');

        try {
            // Drop recruitment_offers table first (if exists)
            if (Schema::hasTable('recruitment_offers')) {
                Schema::dropIfExists('recruitment_offers');
                Log::info('Dropped recruitment_offers table');
            }

            // Drop recruitment_assessments table (if exists)
            if (Schema::hasTable('recruitment_assessments')) {
                Schema::dropIfExists('recruitment_assessments');
                Log::info('Dropped recruitment_assessments table');
            }

            Log::info('Successfully dropped old recruitment tables');
        } catch (\Exception $e) {
            Log::error('Error dropping old recruitment tables: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Log::info('Recreating old recruitment tables...');

        try {
            // Recreate recruitment_assessments table
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

            // Recreate recruitment_offers table
            Schema::create('recruitment_offers', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('session_id');
                $table->decimal('salary_offered', 12, 2)->nullable();
                $table->string('position_offered')->nullable();
                $table->date('start_date')->nullable();
                $table->enum('status', ['pending', 'accepted', 'rejected', 'negotiating'])->default('pending');
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('offered_by')->nullable();
                $table->timestamp('offered_at')->nullable();
                $table->timestamps();

                // Foreign Keys
                $table->foreign('session_id')->references('id')->on('recruitment_sessions')->onDelete('cascade');
                $table->foreign('offered_by')->references('id')->on('users')->onDelete('set null');

                // Indexes
                $table->index('session_id');
                $table->index('status');
                $table->index('offered_at');
            });

            Log::info('Successfully recreated old recruitment tables');
        } catch (\Exception $e) {
            Log::error('Error recreating old recruitment tables: ' . $e->getMessage());
            throw $e;
        }
    }
};
