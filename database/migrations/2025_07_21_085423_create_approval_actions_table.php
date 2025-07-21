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
        Schema::create('approval_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_approval_id')->comment('Reference to document approval');
            $table->unsignedBigInteger('approval_stage_id')->comment('Reference to approval stage');
            $table->unsignedBigInteger('approver_id')->comment('User who performed the action');
            $table->enum('action', ['approved', 'rejected', 'forwarded', 'delegated', 'cancelled', 'escalated'])->comment('Type of action performed');
            $table->text('comments')->nullable()->comment('Comments from the approver');
            $table->timestamp('action_date')->comment('When the action was performed');
            $table->unsignedBigInteger('forwarded_to')->nullable()->comment('User forwarded to (for forwarding actions)');
            $table->unsignedBigInteger('delegated_to')->nullable()->comment('User delegated to (for delegation actions)');
            $table->boolean('is_automatic')->default(false)->comment('Whether this action was automatic');
            $table->json('metadata')->nullable()->comment('Additional action metadata');
            $table->timestamps();

            // Indexes
            $table->index(['document_approval_id', 'approval_stage_id'], 'idx_document_stage');
            $table->index('approver_id', 'idx_approver');
            $table->index('action_date', 'idx_action_date');
            $table->index('action', 'idx_action');
            $table->index('is_automatic', 'idx_is_automatic');

            // Foreign keys
            $table->foreign('document_approval_id')->references('id')->on('document_approvals')->onDelete('cascade');
            $table->foreign('approval_stage_id')->references('id')->on('approval_stages');
            $table->foreign('approver_id')->references('id')->on('users');
            $table->foreign('forwarded_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('delegated_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_actions');
    }
};
