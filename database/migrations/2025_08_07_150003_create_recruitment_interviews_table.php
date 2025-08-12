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
        Schema::create('recruitment_interviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id');
            $table->enum('type', ['hr', 'user']);
            $table->enum('result', ['recommended', 'not_recommended']);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('reviewed_by');
            $table->timestamp('reviewed_at');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('session_id')->references('id')->on('recruitment_sessions')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index('session_id');
            $table->index('type');
            $table->index('result');
            $table->index('reviewed_by');
            $table->index('reviewed_at');

            // Unique constraint: one interview per type per session
            $table->unique(['session_id', 'type'], 'unique_session_interview_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruitment_interviews');
    }
};
