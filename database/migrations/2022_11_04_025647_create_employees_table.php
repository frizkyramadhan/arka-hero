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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('slug', 255)->nullable();
            $table->string('emp_pob');
            $table->date('emp_dob');
            $table->string('blood_type')->nullable();
            $table->foreignId('religion_id')->constrained('religions');
            $table->string('nationality')->nullable();
            $table->foreignId('gender_id')->constrained('genders');
            $table->string('marital')->nullable();
            $table->string('address')->nullable();
            $table->string('village')->nullable();
            $table->string('ward')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->string('identity_card')->nullable();
            $table->string('image', 255)->nullable();
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
        Schema::dropIfExists('employees');
    }
};
