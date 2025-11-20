<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('letter_numbers', function (Blueprint $table) {
            // Drop existing unique constraint for letter_number + year
            $table->dropUnique('letter_numbers_letter_number_year_unique');

            // Add composite unique constraint for letter_number + year + project_id
            // This allows same letter number in different projects for the same category/year
            $table->unique(['letter_number', 'year', 'project_id'], 'letter_numbers_letter_number_year_project_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letter_numbers', function (Blueprint $table) {
            // Drop the composite unique constraint with project_id
            $table->dropUnique('letter_numbers_letter_number_year_project_unique');

            // Restore the original unique constraint for letter_number + year
            $table->unique(['letter_number', 'year'], 'letter_numbers_letter_number_year_unique');
        });
    }
};
