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
        Schema::table('letter_subjects', function (Blueprint $table) {
            // First, drop the unique constraint if it exists
            $table->dropUnique('letter_subjects_document_model_unique');
            $table->dropColumn('document_model');
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
            $table->string('document_model', 100)->nullable()->unique();
        });
    }
};
