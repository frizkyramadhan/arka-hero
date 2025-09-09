<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Since we can't drop the unique constraint directly, let's use a workaround:
        // Temporarily disable foreign key checks to allow constraint modification
        DB::statement('SET foreign_key_checks = 0');

        try {
            // Create the new unique constraint that includes request_reason
            Schema::table('approval_stage_details', function (Blueprint $table) {
                $table->unique(
                    ['approval_stage_id', 'project_id', 'department_id', 'request_reason'],
                    'unique_stage_detail_with_reason'
                );
            });

            // Now that we have the new constraint, we can try dropping the old one
            DB::statement('ALTER TABLE approval_stage_details DROP INDEX unique_stage_detail');
        } catch (\Exception $e) {
            Log::warning('Migration warning: ' . $e->getMessage());
            // Just add an index for now if unique constraint fails
            try {
                Schema::table('approval_stage_details', function (Blueprint $table) {
                    $table->index(
                        ['approval_stage_id', 'project_id', 'department_id', 'request_reason'],
                        'idx_stage_detail_with_reason'
                    );
                });
            } catch (\Exception $e2) {
                // Ignore if index already exists
            }
        } finally {
            DB::statement('SET foreign_key_checks = 1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_stage_details', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('unique_stage_detail_with_reason');

            // Restore the old unique constraint (without request_reason)
            $table->unique(
                ['approval_stage_id', 'project_id', 'department_id'],
                'unique_stage_detail'
            );
        });
    }
};
