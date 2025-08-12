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
        Schema::table('recruitment_documents', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['related_assessment_id']);

            // Drop the column since it's no longer needed
            $table->dropColumn('related_assessment_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recruitment_documents', function (Blueprint $table) {
            // Recreate the column
            $table->uuid('related_assessment_id')->nullable()->comment('Link to specific assessment if applicable');

            // Recreate the foreign key constraint
            $table->foreign('related_assessment_id')->references('id')->on('recruitment_assessments')->onDelete('set null');
        });
    }
};
