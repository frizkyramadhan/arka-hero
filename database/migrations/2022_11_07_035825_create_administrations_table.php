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
            $table->decimal('basic_salary', 9, 2)->nullable();
            $table->decimal('site_allowance', 9, 2)->nullable();
            $table->decimal('other_allowance', 9, 2)->nullable();
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
