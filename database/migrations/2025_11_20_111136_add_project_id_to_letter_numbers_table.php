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
        // Add project_id column after year
        Schema::table('letter_numbers', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->after('year');
        });

        // Migrate existing data: set project_id from project_code if match found
        DB::statement('
            UPDATE letter_numbers ln
            LEFT JOIN projects p ON ln.project_code = p.project_code
            SET ln.project_id = p.id
            WHERE p.id IS NOT NULL
        ');

        // Add foreign key constraint
        Schema::table('letter_numbers', function (Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
        });

        // Add index for project_id
        Schema::table('letter_numbers', function (Blueprint $table) {
            $table->index('project_id');
        });

        // Add composite index for category/year/project for sequence lookup
        Schema::table('letter_numbers', function (Blueprint $table) {
            $table->index(['letter_category_id', 'year', 'project_id'], 'idx_category_year_project');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letter_numbers', function (Blueprint $table) {
            // Drop composite index
            $table->dropIndex('idx_category_year_project');

            // Drop foreign key
            $table->dropForeign(['project_id']);

            // Drop column
            $table->dropColumn('project_id');
        });
    }
};
