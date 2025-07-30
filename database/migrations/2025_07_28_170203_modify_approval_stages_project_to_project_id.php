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
        Schema::table('approval_stages', function (Blueprint $table) {
            // Add new project_id column
            $table->foreignId('project_id')->after('id')->constrained('projects');

            // Drop the old project column
            $table->dropColumn('project');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('approval_stages', function (Blueprint $table) {
            // Add back the old project column
            $table->string('project', 255)->after('id');

            // Drop the foreign key and project_id column
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
    }
};
