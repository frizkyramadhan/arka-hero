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
        Schema::create('employee_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('token_id')->constrained('employee_registration_tokens');
            $table->json('personal_data'); // Store all form data as JSON
            $table->json('document_files')->nullable(); // Store info about uploaded documents
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->datetime('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['token_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_registrations');
    }
};
