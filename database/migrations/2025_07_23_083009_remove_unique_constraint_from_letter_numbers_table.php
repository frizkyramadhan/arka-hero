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
        Schema::table('letter_numbers', function (Blueprint $table) {
            // Drop the existing unique constraint on letter_number
            $table->dropUnique(['letter_number']);

            // Add composite unique constraint for letter_number and year
            // This allows same letter number in different years for annual_reset categories
            $table->unique(['letter_number', 'year'], 'letter_numbers_letter_number_year_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('letter_numbers', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('letter_numbers_letter_number_year_unique');

            // Restore the original unique constraint on letter_number
            $table->unique('letter_number');
        });
    }
};
