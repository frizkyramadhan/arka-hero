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
        Schema::create('document_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 100)->comment('Type of document (e.g., officialtravel, recruitment_request, employee_registration)');
            $table->string('document_id', 255)->comment('ID of the document (UUID or regular ID)');
            $table->unsignedBigInteger('approval_flow_id')->comment('Reference to approval flow');
            $table->unsignedBigInteger('current_stage_id')->nullable()->comment('Current stage in the approval process');
            $table->enum('overall_status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending')->comment('Overall approval status');
            $table->unsignedBigInteger('submitted_by')->comment('User who submitted for approval');
            $table->timestamp('submitted_at')->comment('When the document was submitted for approval');
            $table->timestamp('completed_at')->nullable()->comment('When the approval process was completed');
            $table->json('metadata')->nullable()->comment('Additional document-specific data');
            $table->timestamps();

            // Indexes
            $table->index(['document_type', 'document_id'], 'idx_document');
            $table->index('overall_status', 'idx_status');
            $table->index('submitted_by', 'idx_submitted_by');
            $table->index('submitted_at', 'idx_submitted_at');
            $table->index('completed_at', 'idx_completed_at');

            // Foreign keys
            $table->foreign('approval_flow_id')->references('id')->on('approval_flows');
            $table->foreign('current_stage_id')->references('id')->on('approval_stages');
            $table->foreign('submitted_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_approvals');
    }
};
