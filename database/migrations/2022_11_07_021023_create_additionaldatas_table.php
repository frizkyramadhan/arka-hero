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
        Schema::create('additionaldatas', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('employee_id')->references('id')->on('employees');
            $table->string('cloth_size')->nullable();
            $table->string('pants_size')->nullable();
            $table->string('shoes_size')->nullable();
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->string('glasses')->nullable();
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
        Schema::dropIfExists('additionaldatas');
    }
};
