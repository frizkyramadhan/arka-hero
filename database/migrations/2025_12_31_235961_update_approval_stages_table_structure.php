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
        Schema::table('approval_stages', function (Blueprint $table) {
            // Check if foreign key constraint exists before dropping
            $foreignKeys = $this->getForeignKeys('approval_stages');

            // Drop foreign key constraint if it exists
            if (in_array('approval_stages_project_id_foreign', $foreignKeys)) {
                $table->dropForeign('approval_stages_project_id_foreign');
            } else {
                // Try to drop by column name if constraint name not found
                try {
                    $table->dropForeign(['project_id']);
                } catch (\Exception $e) {
                    // Foreign key doesn't exist, continue
                }
            }

            // Drop columns that will move to details table
            $table->dropColumn(['project_id', 'department_id']);

            // Update unique constraint to new structure
            $table->dropUnique('unique_approval_stage_combination');
            $table->unique(['document_type', 'approver_id', 'approval_order'], 'unique_approval_stage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_stages', function (Blueprint $table) {
            // Add back the dropped columns
            $table->foreignId('project_id')->after('document_type')->constrained('projects');
            $table->string('department_id', 255)->after('project_id');

            // Restore original unique constraint
            $table->dropUnique('unique_approval_stage');
            $table->unique(
                ['project_id', 'department_id', 'document_type', 'approver_id', 'approval_order'],
                'unique_approval_stage_combination'
            );
        });
    }

    /**
     * Get foreign key constraints for a table
     */
    private function getForeignKeys($tableName)
    {
        $foreignKeys = [];

        try {
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$tableName]);

            foreach ($constraints as $constraint) {
                $foreignKeys[] = $constraint->CONSTRAINT_NAME;
            }
        } catch (\Exception $e) {
            // If we can't get foreign keys, return empty array
            $foreignKeys = [];
        }

        return $foreignKeys;
    }
};
