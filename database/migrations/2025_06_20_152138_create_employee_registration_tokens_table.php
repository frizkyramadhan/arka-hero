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
        Schema::create('employee_registration_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email');
            $table->string('token')->unique();
            $table->enum('status', ['pending', 'used', 'expired'])->default('pending');
            $table->datetime('expires_at');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['token', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_registration_tokens');
    }
};
