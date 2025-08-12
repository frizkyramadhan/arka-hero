<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
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
        Log::info('Starting recruitment assessment data migration...');

        try {
            // Check if old table exists
            if (!Schema::hasTable('recruitment_assessments')) {
                Log::info('Old recruitment_assessments table does not exist. Skipping migration.');
                return;
            }

            // Get all existing assessments
            $assessments = DB::table('recruitment_assessments')->get();
            Log::info("Found {$assessments->count()} assessments to migrate");

            foreach ($assessments as $assessment) {
                $this->migrateAssessment($assessment);
            }

            Log::info('Recruitment assessment data migration completed successfully');
        } catch (\Exception $e) {
            Log::error('Error during recruitment assessment data migration: ' . $e->getMessage());
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
        Log::info('Rolling back recruitment assessment data migration...');

        try {
            // Delete all data from new tables
            DB::table('recruitment_cv_reviews')->delete();
            DB::table('recruitment_psikotes')->delete();
            DB::table('recruitment_tes_teori')->delete();
            DB::table('recruitment_interviews')->delete();
            DB::table('recruitment_offerings')->delete();
            DB::table('recruitment_mcu')->delete();
            DB::table('recruitment_hiring')->delete();
            DB::table('recruitment_onboarding')->delete();

            Log::info('Recruitment assessment data rollback completed');
        } catch (\Exception $e) {
            Log::error('Error during recruitment assessment data rollback: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Migrate individual assessment to new structure
     */
    private function migrateAssessment($assessment)
    {
        $assessmentData = json_decode($assessment->assessment_data ?? '{}', true) ?: [];

        switch ($assessment->assessment_type) {
            case 'cv_review':
                $this->migrateCvReview($assessment, $assessmentData);
                break;

            case 'psikotes':
                $this->migratePsikotes($assessment, $assessmentData);
                break;

            case 'tes_teori':
                $this->migrateTesTeori($assessment, $assessmentData);
                break;

            case 'interview_hr':
                $this->migrateInterview($assessment, $assessmentData, 'hr');
                break;

            case 'interview_user':
                $this->migrateInterview($assessment, $assessmentData, 'user');
                break;

            case 'offering':
                $this->migrateOffering($assessment, $assessmentData);
                break;

            case 'mcu':
                $this->migrateMcu($assessment, $assessmentData);
                break;

            case 'hire':
                $this->migrateHiring($assessment, $assessmentData);
                break;

            case 'onboarding':
                $this->migrateOnboarding($assessment, $assessmentData);
                break;

            default:
                Log::warning("Unknown assessment type: {$assessment->assessment_type}");
                break;
        }
    }

    /**
     * Migrate CV Review data
     */
    private function migrateCvReview($assessment, $data)
    {
        $decision = $this->determineCvDecision($assessment, $data);

        DB::table('recruitment_cv_reviews')->insert([
            'session_id' => $assessment->session_id,
            'decision' => $decision,
            'notes' => $assessment->assessor_notes,
            'reviewed_by' => $assessment->assessor_ids[0] ?? 1, // Use first assessor or default
            'reviewed_at' => $assessment->completed_at ?? $assessment->created_at,
            'created_at' => $assessment->created_at,
            'updated_at' => $assessment->updated_at,
        ]);

        Log::info("Migrated CV review for session {$assessment->session_id}");
    }

    /**
     * Migrate Psikotes data
     */
    private function migratePsikotes($assessment, $data)
    {
        $result = $this->determinePsikotesResult($assessment, $data);

        DB::table('recruitment_psikotes')->insert([
            'session_id' => $assessment->session_id,
            'online_score' => $data['online_score'] ?? $data['personality_score'] ?? null,
            'offline_score' => $data['offline_score'] ?? $data['iq_score'] ?? null,
            'result' => $result,
            'notes' => $assessment->assessor_notes,
            'reviewed_by' => $assessment->assessor_ids[0] ?? 1,
            'reviewed_at' => $assessment->completed_at ?? $assessment->created_at,
            'created_at' => $assessment->created_at,
            'updated_at' => $assessment->updated_at,
        ]);

        Log::info("Migrated Psikotes for session {$assessment->session_id}");
    }

    /**
     * Migrate Tes Teori data
     */
    private function migrateTesTeori($assessment, $data)
    {
        $result = $this->determineTesTeoriResult($assessment, $data);

        DB::table('recruitment_tes_teori')->insert([
            'session_id' => $assessment->session_id,
            'score' => $data['score'] ?? $data['technical_score'] ?? $assessment->overall_score,
            'result' => $result,
            'notes' => $assessment->assessor_notes,
            'reviewed_by' => $assessment->assessor_ids[0] ?? 1,
            'reviewed_at' => $assessment->completed_at ?? $assessment->created_at,
            'created_at' => $assessment->created_at,
            'updated_at' => $assessment->updated_at,
        ]);

        Log::info("Migrated Tes Teori for session {$assessment->session_id}");
    }

    /**
     * Migrate Interview data
     */
    private function migrateInterview($assessment, $data, $type)
    {
        $result = $this->determineInterviewResult($assessment, $data);

        DB::table('recruitment_interviews')->insert([
            'session_id' => $assessment->session_id,
            'type' => $type,
            'result' => $result,
            'notes' => $assessment->assessor_notes,
            'reviewed_by' => $assessment->assessor_ids[0] ?? 1,
            'reviewed_at' => $assessment->completed_at ?? $assessment->created_at,
            'created_at' => $assessment->created_at,
            'updated_at' => $assessment->updated_at,
        ]);

        Log::info("Migrated {$type} interview for session {$assessment->session_id}");
    }

    /**
     * Migrate Offering data
     */
    private function migrateOffering($assessment, $data)
    {
        $result = $this->determineOfferingResult($assessment, $data);

        DB::table('recruitment_offerings')->insert([
            'session_id' => $assessment->session_id,
            'offering_letter_number' => $data['offering_letter_number'] ?? null,
            'result' => $result,
            'notes' => $assessment->assessor_notes,
            'reviewed_by' => $assessment->assessor_ids[0] ?? 1,
            'reviewed_at' => $assessment->completed_at ?? $assessment->created_at,
            'created_at' => $assessment->created_at,
            'updated_at' => $assessment->updated_at,
        ]);

        Log::info("Migrated Offering for session {$assessment->session_id}");
    }

    /**
     * Migrate MCU data
     */
    private function migrateMcu($assessment, $data)
    {
        $result = $this->determineMcuResult($assessment, $data);

        DB::table('recruitment_mcu')->insert([
            'session_id' => $assessment->session_id,
            'result' => $result,
            'notes' => $assessment->assessor_notes,
            'reviewed_by' => $assessment->assessor_ids[0] ?? 1,
            'reviewed_at' => $assessment->completed_at ?? $assessment->created_at,
            'created_at' => $assessment->created_at,
            'updated_at' => $assessment->updated_at,
        ]);

        Log::info("Migrated MCU for session {$assessment->session_id}");
    }

    /**
     * Migrate Hiring data
     */
    private function migrateHiring($assessment, $data)
    {
        DB::table('recruitment_hiring')->insert([
            'session_id' => $assessment->session_id,
            'agreement_type' => $data['agreement_type'] ?? 'pkwt',
            'letter_number' => $data['letter_number'] ?? null,
            'notes' => $assessment->assessor_notes,
            'reviewed_by' => $assessment->assessor_ids[0] ?? 1,
            'reviewed_at' => $assessment->completed_at ?? $assessment->created_at,
            'created_at' => $assessment->created_at,
            'updated_at' => $assessment->updated_at,
        ]);

        Log::info("Migrated Hiring for session {$assessment->session_id}");
    }

    /**
     * Migrate Onboarding data
     */
    private function migrateOnboarding($assessment, $data)
    {
        DB::table('recruitment_onboarding')->insert([
            'session_id' => $assessment->session_id,
            'onboarding_date' => $data['onboarding_date'] ?? $data['start_date'] ?? null,
            'notes' => $assessment->assessor_notes,
            'reviewed_by' => $assessment->assessor_ids[0] ?? 1,
            'reviewed_at' => $assessment->completed_at ?? $assessment->created_at,
            'created_at' => $assessment->created_at,
            'updated_at' => $assessment->updated_at,
        ]);

        Log::info("Migrated Onboarding for session {$assessment->session_id}");
    }

    /**
     * Determine CV Review decision
     */
    private function determineCvDecision($assessment, $data)
    {
        if ($assessment->status === 'completed' && $assessment->overall_score >= 70) {
            return 'recommended';
        }

        if ($assessment->status === 'failed') {
            return 'not_recommended';
        }

        // Try to determine from assessment data
        if (isset($data['decision'])) {
            return $data['decision'] === 'pass' ? 'recommended' : 'not_recommended';
        }

        // Default based on status
        return $assessment->status === 'completed' ? 'recommended' : 'not_recommended';
    }

    /**
     * Determine Psikotes result
     */
    private function determinePsikotesResult($assessment, $data)
    {
        if ($assessment->status === 'failed') {
            return 'fail';
        }

        if ($assessment->status === 'completed') {
            // Check online score
            if (isset($data['online_score']) && $data['online_score'] < 40) {
                return 'fail';
            }

            // Check offline score
            if (isset($data['offline_score']) && $data['offline_score'] < 8) {
                return 'fail';
            }

            return 'pass';
        }

        return 'fail';
    }

    /**
     * Determine Tes Teori result
     */
    private function determineTesTeoriResult($assessment, $data)
    {
        if ($assessment->status === 'failed') {
            return 'fail';
        }

        if ($assessment->status === 'completed') {
            $score = $data['score'] ?? $assessment->overall_score;
            return $score >= 75 ? 'pass' : 'fail';
        }

        return 'fail';
    }

    /**
     * Determine Interview result
     */
    private function determineInterviewResult($assessment, $data)
    {
        if ($assessment->status === 'failed') {
            return 'not_recommended';
        }

        if ($assessment->status === 'completed') {
            $score = $data['overall_score'] ?? $assessment->overall_score;

            // HR interview threshold: 70
            if (str_contains($assessment->assessment_type, 'hr')) {
                return $score >= 70 ? 'recommended' : 'not_recommended';
            }

            // User interview threshold: 75
            return $score >= 75 ? 'recommended' : 'not_recommended';
        }

        return 'not_recommended';
    }

    /**
     * Determine Offering result
     */
    private function determineOfferingResult($assessment, $data)
    {
        if ($assessment->status === 'failed') {
            return 'rejected';
        }

        if (isset($data['status'])) {
            return $data['status'];
        }

        return $assessment->status === 'completed' ? 'accepted' : 'negotiating';
    }

    /**
     * Determine MCU result
     */
    private function determineMcuResult($assessment, $data)
    {
        if ($assessment->status === 'failed') {
            return 'unfit';
        }

        if (isset($data['overall_health'])) {
            return $data['overall_health'];
        }

        return $assessment->status === 'completed' ? 'fit' : 'follow_up';
    }
};
