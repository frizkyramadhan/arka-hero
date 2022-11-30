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
        Schema::create('emrgcalls', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('employee_id')->references('id')->on('employees');
            $table->string('emrg_call_name')->nullable();
            $table->string('emrg_call_relation')->nullable();
            $table->string('emrg_call_phone')->nullable();
            $table->string('emrg_call_address')->nullable();
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
        Schema::dropIfExists('emrgcalls');
    }
};
