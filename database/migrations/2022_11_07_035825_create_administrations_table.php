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
        Schema::create('administrations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('employee_id')->references('id')->on('employees');
            $table->foreignId('project_id')->constrained('projects');
            $table->foreignId('position_id')->constrained('positions');
            $table->string('nik');
            $table->string('class');
            $table->date('doh');
            $table->string('poh');
            $table->string('basic_salary')->nullable();
            $table->string('site_allowance')->nullable();
            $table->string('other_allowance')->nullable();
            $table->date('termination_date')->nullable();
            $table->string('termination_reason')->nullable();
            $table->string('coe_no')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->boolean('is_active')->default(1);
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
        Schema::dropIfExists('administrations');
    }
};
