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
            // Add unique constraint to document_model column
            $table->unique(['document_model'], 'letter_subjects_document_model_unique');
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
            // Drop unique constraint
            $table->dropUnique('letter_subjects_document_model_unique');
        });
    }
};
