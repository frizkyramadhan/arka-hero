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
        Schema::create('registration_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('registration_id')->constrained('employee_registrations');
            $table->string('document_type'); // ktp, cv, ijazah, etc
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path');
            $table->integer('file_size');
            $table->string('mime_type');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->index(['registration_id']);
            $table->index(['document_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('registration_documents');
    }
};
