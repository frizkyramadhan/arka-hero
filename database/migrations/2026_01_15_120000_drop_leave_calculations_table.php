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
        // Table leave_calculations is no longer used.
        // It has been replaced by runtime calculations based on leave_entitlements and leave_requests.
        Schema::dropIfExists('leave_calculations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the table with the latest known structure
        Schema::create('leave_calculations', function (Blueprint $table) {
            $table->id();
            // leave_requests now uses UUID (string 36) as primary key
            $table->string('leave_request_id', 36);
            $table->integer('annual_eligibility')->default(0);
            $table->integer('lsl_eligibility')->default(0);
            $table->integer('outstanding_lsl')->default(0);
            $table->integer('accumulated_leave')->default(0);
            $table->integer('entitlement')->default(0);
            $table->integer('less_this_leave')->default(0);
            $table->integer('paid_out')->default(0);
            $table->integer('balance')->default(0);
            $table->timestamps();

            $table->foreign('leave_request_id')
                ->references('id')
                ->on('leave_requests')
                ->onDelete('cascade');
        });
    }
};

