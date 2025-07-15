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
        Schema::create('recruitment_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('session_id')->comment('Link to session');
            $table->string('document_type', 100)->comment('cv, certificate, test_result, etc.');
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path', 500);
            $table->integer('file_size');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('uploaded_by');
            $table->boolean('is_verified')->default(false);
            $table->uuid('related_assessment_id')->nullable()->comment('Link to specific assessment if applicable');

            $table->timestamps();

            // Foreign Keys
            $table->foreign('session_id')->references('id')->on('recruitment_sessions')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users');
            $table->foreign('related_assessment_id')->references('id')->on('recruitment_assessments')->onDelete('set null');

            // Indexes
            $table->index('document_type');
            $table->index('session_id');
            $table->index('uploaded_by');
            $table->index('is_verified');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruitment_documents');
    }
};
