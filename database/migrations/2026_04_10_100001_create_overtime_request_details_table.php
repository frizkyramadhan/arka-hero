<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_request_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('overtime_request_id');
            $table->foreign('overtime_request_id')->references('id')->on('overtime_requests')->cascadeOnDelete();
            $table->unsignedBigInteger('administration_id');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->text('work_description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('administration_id')->references('id')->on('administrations')->onDelete('cascade');
            $table->index('overtime_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_request_details');
    }
};
