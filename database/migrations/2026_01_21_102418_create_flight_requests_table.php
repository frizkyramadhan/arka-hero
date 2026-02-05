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
        Schema::create('flight_requests', function (Blueprint $table) {
            // Primary Key
            $table->uuid('id')->primary();

            // Form Identity
            $table->string('form_number', 50)->unique()->nullable();
            $table->enum('request_type', ['standalone', 'leave_based', 'travel_based']);

            // Employee Information
            $table->uuid('employee_id')->nullable(); // Nullable: bisa manual input atau dari employee table
            $table->unsignedBigInteger('administration_id')->nullable(); // FK ke administrations (snapshot saat FR dibuat)
            $table->string('employee_name')->nullable(); // Nama manual jika tidak ada employee_id
            $table->string('nik', 50)->nullable();
            $table->string('position')->nullable();
            $table->string('department')->nullable(); // String: dari administration.department_id
            $table->string('project')->nullable(); // String: dari administration.project_id
            $table->string('phone_number', 20)->nullable();

            // Travel Information
            $table->text('purpose_of_travel');
            $table->string('total_travel_days')->nullable();

            // Reference to Source Document
            $table->uuid('leave_request_id')->nullable();
            $table->uuid('official_travel_id')->nullable();

            // Status & Workflow
            $table->enum('status', [
                'draft',
                'submitted',
                'approved',
                'issued', // HCS issued the LG (Letter of Guarantee)
                'completed',
                'rejected',
                'cancelled'
            ])->default('draft');

            // Manual Approvers (JSON array of user IDs)
            $table->json('manual_approvers')->nullable();

            // Timestamps & Users
            $table->unsignedBigInteger('requested_by'); // FK to users.id (not UUID)
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Rejection & Cancellation
            $table->text('rejection_reason')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable(); // FK to users.id (not UUID)
            $table->timestamp('cancelled_at')->nullable();

            // Additional Info
            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamps();

            // Foreign Keys
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('administration_id')->references('id')->on('administrations')->onDelete('set null');
            // department & project: No FK constraint (string from administration)
            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onDelete('set null');
            $table->foreign('official_travel_id')->references('id')->on('officialtravels')->onDelete('set null');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('form_number');
            $table->index('employee_id');
            $table->index('administration_id');
            $table->index('status');
            $table->index('request_type');
            $table->index('leave_request_id');
            $table->index('official_travel_id');
            $table->index('requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_requests');
    }
};
