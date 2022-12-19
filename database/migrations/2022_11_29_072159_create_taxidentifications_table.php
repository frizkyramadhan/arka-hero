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
        Schema::create('taxidentifications', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('employee_id')->references('id')->on('employees');
            $table->string('tax_no')->nullable();
            $table->date('tax_valid_date')->nullable();
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
        Schema::dropIfExists('taxidentifications');
    }
};
