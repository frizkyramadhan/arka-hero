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
        $columns = Schema::getColumnListing('letter_numbers');

        if (in_array('project_code', $columns)) {
            // Already migrated, skip
            return;
        }

        if (in_array('project_id', $columns)) {
            // Drop foreign key constraint
            try {
                Schema::table('letter_numbers', function (Blueprint $table) {
                    $table->dropForeign(['project_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }

            // Change project_id to string type first
            DB::statement('ALTER TABLE letter_numbers MODIFY project_id VARCHAR(50) NULL');

            // Rename column
            Schema::table('letter_numbers', function (Blueprint $table) {
                $table->renameColumn('project_id', 'project_code');
            });

            // Update data: convert IDs to project_codes
            DB::statement('
                UPDATE letter_numbers ln
                LEFT JOIN projects p ON CAST(ln.project_code AS UNSIGNED) = p.id
                SET ln.project_code = p.project_code
                WHERE p.id IS NOT NULL
            ');
        } else {
            // Add project_code column if doesn't exist
            Schema::table('letter_numbers', function (Blueprint $table) {
                $table->string('project_code', 50)->nullable();
            });
        }

        // Add index
        Schema::table('letter_numbers', function (Blueprint $table) {
            $table->index('project_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = Schema::getColumnListing('letter_numbers');

        if (in_array('project_id', $columns)) {
            // Already rolled back, skip
            return;
        }

        if (in_array('project_code', $columns)) {
            // Rename back to project_id
            Schema::table('letter_numbers', function (Blueprint $table) {
                $table->renameColumn('project_code', 'project_id');
            });

            // Change back to integer and set foreign key
            DB::statement('ALTER TABLE letter_numbers MODIFY project_id BIGINT UNSIGNED NULL');

            // Update data: convert project_codes back to IDs
            DB::statement('
                UPDATE letter_numbers ln
                LEFT JOIN projects p ON ln.project_id = p.project_code
                SET ln.project_id = p.id
            ');

            // Add back foreign key
            Schema::table('letter_numbers', function (Blueprint $table) {
                $table->foreign('project_id')->references('id')->on('projects');
            });
        }
    }
};
