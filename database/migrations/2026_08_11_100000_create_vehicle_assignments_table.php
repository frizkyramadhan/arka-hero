<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('form_number', 20)->unique();
            $table->date('assignment_date');
            $table->uuid('driver_employee_id');
            $table->string('driver_name');
            $table->unsignedBigInteger('driver_user_id')->nullable();
            $table->string('origin_destination');
            $table->boolean('origin_is_manual')->default(false);
            $table->text('remarks')->nullable();
            $table->uuid('vehicle_id');
            $table->string('vehicle_kode', 50);
            $table->string('license_plate', 20);
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('requested_by');
            $table->string('status', 30)->default('draft');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('driver_employee_id')->references('id')->on('employees')->cascadeOnUpdate();
            $table->foreign('driver_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->cascadeOnUpdate();
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
            $table->foreign('requested_by')->references('id')->on('users')->cascadeOnUpdate();
            $table->foreign('closed_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['assignment_date', 'vehicle_id']);
            $table->index(['driver_employee_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_assignments');
    }
};
