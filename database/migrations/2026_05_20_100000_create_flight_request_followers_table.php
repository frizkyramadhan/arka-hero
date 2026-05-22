<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('flight_request_followers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('flight_request_id');
            $table->unsignedInteger('sort_order')->default(1);
            $table->uuid('employee_id')->nullable();
            $table->unsignedBigInteger('administration_id')->nullable();
            $table->string('follower_name')->nullable();
            $table->string('nik', 50)->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->string('project')->nullable();
            $table->timestamps();

            $table->foreign('flight_request_id')
                ->references('id')
                ->on('flight_requests')
                ->onDelete('cascade');

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('set null');

            $table->foreign('administration_id')
                ->references('id')
                ->on('administrations')
                ->onDelete('set null');

            $table->index('flight_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_request_followers');
    }
};
