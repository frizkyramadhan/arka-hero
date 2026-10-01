<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('recruitment_candidates', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->after('candidate_number');
            $table->foreign('project_id')->references('id')->on('projects');
        });
    }

    public function down()
    {
        Schema::table('recruitment_candidates', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
    }
};
