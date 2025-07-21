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
        Schema::create('approval_flows', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('Name of the approval flow');
            $table->text('description')->nullable()->comment('Description of the approval flow');
            $table->string('document_type', 100)->comment('Type of document this flow applies to (e.g., officialtravel, recruitment_request, employee_registration)');
            $table->boolean('is_active')->default(true)->comment('Whether this flow is active');
            $table->unsignedBigInteger('created_by')->nullable()->comment('User who created this flow');
            $table->timestamps();

            // Indexes
            $table->index('document_type', 'idx_document_type');
            $table->index('is_active', 'idx_is_active');
            $table->index('created_by', 'idx_created_by');

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_flows');
    }
};
