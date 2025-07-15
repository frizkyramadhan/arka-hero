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
        Schema::create('recruitment_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Letter numbering integration fields
            $table->foreignId('letter_number_id')->nullable()->constrained('letter_numbers');
            $table->string('letter_number', 50)->nullable();

            $table->string('request_number', 50)->unique()->comment('No.000/HCS-HO/PRF/1/2017');

            // Basic Information
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('level_id');
            $table->integer('required_qty');
            $table->date('required_date');
            $table->enum('employment_type', ['pkwtt', 'pkwt', 'harian', 'magang']);

            // Request Reason
            $table->enum('request_reason', ['replacement_resign', 'replacement_promotion', 'additional_workplan', 'other']);
            $table->text('other_reason')->nullable();

            // Job Requirements
            $table->text('job_description')->nullable();
            $table->enum('required_gender', ['male', 'female', 'any'])->default('any');
            $table->integer('required_age_min')->nullable();
            $table->integer('required_age_max')->nullable();
            $table->enum('required_marital_status', ['single', 'married', 'any'])->default('any');
            $table->string('required_education')->nullable();
            $table->text('required_skills')->nullable();
            $table->text('required_experience')->nullable();
            $table->text('required_physical')->nullable();
            $table->text('required_mental')->nullable();
            $table->text('other_requirements')->nullable();

            // Approval Workflow
            $table->unsignedBigInteger('requested_by');
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'cancelled', 'closed'])->default('draft');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Approval Hierarchy
            $table->unsignedBigInteger('known_by')->nullable()->comment('HR&GA Section Head who acknowledges');
            $table->unsignedBigInteger('approved_by_pm')->nullable()->comment('Project Manager who approves');
            $table->unsignedBigInteger('approved_by_director')->nullable()->comment('Director/Manager who approves');

            // Position Tracking
            $table->integer('positions_filled')->default(0)->comment('Tracking filled positions');

            $table->timestamps();

            // Foreign Keys
            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('position_id')->references('id')->on('positions');
            $table->foreign('level_id')->references('id')->on('levels');
            $table->foreign('requested_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('known_by')->references('id')->on('users');
            $table->foreign('approved_by_pm')->references('id')->on('users');
            $table->foreign('approved_by_director')->references('id')->on('users');

            // Indexes
            $table->index('letter_number_id');
            $table->index('letter_number');
            $table->index('status');
            $table->index('required_date');
            $table->index('department_id');
            $table->index('position_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruitment_requests');
    }
};
