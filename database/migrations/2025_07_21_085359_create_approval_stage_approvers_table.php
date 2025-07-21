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
        Schema::create('approval_stage_approvers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approval_stage_id')->comment('Reference to approval stage');
            $table->enum('approver_type', ['user', 'role', 'department'])->comment('Type of approver');
            $table->unsignedBigInteger('approver_id')->comment('ID of user, role, or department');
            $table->boolean('is_backup')->default(false)->comment('Whether this is a backup approver');
            $table->json('approval_condition')->nullable()->comment('Conditions when this approver is required');
            $table->timestamps();

            // Indexes
            $table->index(['approval_stage_id', 'approver_type', 'approver_id'], 'idx_stage_approver');
            $table->index('approver_type', 'idx_approver_type');
            $table->index('is_backup', 'idx_is_backup');

            // Foreign keys
            $table->foreign('approval_stage_id')->references('id')->on('approval_stages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_stage_approvers');
    }
};
