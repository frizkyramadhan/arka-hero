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
        Schema::create('recruitment_candidates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('candidate_number', 50)->unique()->comment('CAND/2024/01/0001');

            // Personal Information
            $table->string('fullname');
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('education_level', 100)->nullable();
            $table->integer('experience_years')->nullable();

            // CV Details
            $table->string('cv_file_path', 500)->nullable();
            $table->text('skills')->nullable();
            $table->text('previous_companies')->nullable();
            $table->decimal('current_salary', 15, 2)->nullable();
            $table->decimal('expected_salary', 15, 2)->nullable();

            // Global Status (across all applications)
            $table->enum('global_status', ['available', 'in_process', 'hired', 'blacklisted'])->default('available');

            $table->timestamps();

            // Indexes
            $table->index('email');
            $table->index('global_status');
            $table->index('fullname');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruitment_candidates');
    }
};
