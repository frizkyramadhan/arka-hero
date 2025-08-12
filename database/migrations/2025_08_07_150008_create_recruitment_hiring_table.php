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
        Schema::create('recruitment_hiring', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id');
            $table->enum('agreement_type', ['pkwt', 'pkwtt']);
            $table->string('letter_number')->nullable(); // PKWT letter number
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('reviewed_by');
            $table->timestamp('reviewed_at');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('session_id')->references('id')->on('recruitment_sessions')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index('session_id');
            $table->index('agreement_type');
            $table->index('letter_number');
            $table->index('reviewed_by');
            $table->index('reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruitment_hiring');
    }
};
