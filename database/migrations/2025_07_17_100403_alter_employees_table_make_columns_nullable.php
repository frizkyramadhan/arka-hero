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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('emp_pob')->nullable()->change();
            $table->date('emp_dob')->nullable()->change();
            $table->foreignId('religion_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('emp_pob')->nullable(false)->change();
            $table->date('emp_dob')->nullable(false)->change();
            $table->foreignId('religion_id')->nullable(false)->change();
        });
    }
};
