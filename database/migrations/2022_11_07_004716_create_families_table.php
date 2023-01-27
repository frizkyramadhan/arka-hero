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
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('employee_id')->references('id')->on('employees');
            $table->string('family_relationship');
            $table->string('family_name');
            $table->string('family_birthplace')->nullable();
            $table->date('family_birthdate')->nullable();
            $table->string('family_remarks')->nullable();
            $table->string('bpjsks_no')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('families');
    }
};
