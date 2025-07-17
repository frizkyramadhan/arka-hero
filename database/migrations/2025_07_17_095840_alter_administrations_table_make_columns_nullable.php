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
            $table->foreignId('project_id')->nullable()->change();
            $table->foreignId('position_id')->nullable()->change();
            $table->string('nik')->nullable()->change();
            $table->string('class')->nullable()->change();
            $table->date('doh')->nullable()->change();
            $table->string('poh')->nullable()->change();
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
            $table->foreignId('project_id')->nullable(false)->change();
            $table->foreignId('position_id')->nullable(false)->change();
            $table->string('nik')->nullable(false)->change();
            $table->string('class')->nullable(false)->change();
            $table->date('doh')->nullable(false)->change();
            $table->string('poh')->nullable(false)->change();
        });
    }
};
