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
        Schema::table('administrations', function (Blueprint $table) {
            $table->after('position_id', function ($table) {
                $table->foreignId('grade_id')->nullable()->constrained('grades');
                $table->foreignId('level_id')->nullable()->constrained('levels');
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('administrations', function (Blueprint $table) {
            $table->dropForeign(['grade_id']);
            $table->dropForeign(['level_id']);
            $table->dropColumn(['grade_id', 'level_id']);
        });
    }
};
