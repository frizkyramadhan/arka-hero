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
        Schema::create('leave_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained()->onDelete('cascade');
            $table->integer('annual_eligibility')->default(0);
            $table->integer('lsl_eligibility')->default(0);
            $table->integer('outstanding_lsl')->default(0);
            $table->integer('accumulated_leave')->default(0);
            $table->integer('entitlement')->default(0);
            $table->integer('less_this_leave')->default(0);
            $table->integer('paid_out')->default(0);
            $table->integer('balance')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_calculations');
    }
};
