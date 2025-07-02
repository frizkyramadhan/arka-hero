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
        Schema::create('letter_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('subject_name', 200);
            $table->string('category_code', 10);

            // Document Integration Field
            $table->string('document_model', 100)->nullable();

            $table->boolean('is_active')->default(1);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            // Indexes
            $table->index('category_code');
            $table->index('document_model');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('letter_subjects');
    }
};
