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
        Schema::table('letter_subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('letter_category_id')->nullable()->after('subject_name');
        });

        // Update existing records
        DB::statement('
            UPDATE letter_subjects ls
            JOIN letter_categories lc ON ls.category_code = lc.category_code
            SET ls.letter_category_id = lc.id
        ');

        Schema::table('letter_subjects', function (Blueprint $table) {
            $table->dropIndex('letter_subjects_category_code_index');
            $table->dropColumn('category_code');
            $table->foreign('letter_category_id')->references('id')->on('letter_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('letter_subjects', function (Blueprint $table) {
            $table->string('category_code', 10)->nullable()->after('subject_name');
            $table->dropForeign(['letter_category_id']);
        });

        DB::statement('
            UPDATE letter_subjects ls
            JOIN letter_categories lc ON ls.letter_category_id = lc.id
            SET ls.category_code = lc.category_code
        ');

        Schema::table('letter_subjects', function (Blueprint $table) {
            $table->dropColumn('letter_category_id');
            $table->index('category_code');
        });
    }
};
