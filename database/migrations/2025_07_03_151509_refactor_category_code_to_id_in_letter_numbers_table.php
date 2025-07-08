<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            $table->unsignedBigInteger('letter_category_id')->nullable()->after('subject_id');
        });

        // Update existing records
        DB::statement('
            UPDATE letter_numbers ln
            JOIN letter_categories lc ON ln.category_code = lc.category_code
            SET ln.letter_category_id = lc.id
        ');

        Schema::table('letter_numbers', function (Blueprint $table) {
            $table->dropIndex('letter_numbers_category_code_year_index');
            $table->dropColumn('category_code');
            $table->foreign('letter_category_id')->references('id')->on('letter_categories');
            $table->index(['letter_category_id', 'year']);
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
            $table->string('category_code', 10)->nullable()->after('subject_id');
            $table->dropForeign(['letter_category_id']);
            $table->dropIndex('letter_numbers_letter_category_id_year_index');
        });

        DB::statement('
            UPDATE letter_numbers ln
            JOIN letter_categories lc ON ln.letter_category_id = lc.id
            SET ln.category_code = lc.category_code
        ');

        Schema::table('letter_numbers', function (Blueprint $table) {
            $table->dropColumn('letter_category_id');
            $table->index(['category_code', 'year']);
        });
    }
};
