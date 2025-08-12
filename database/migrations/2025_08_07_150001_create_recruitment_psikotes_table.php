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
        Schema::create('recruitment_psikotes', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id');
            $table->decimal('online_score', 5, 2)->nullable();
            $table->decimal('offline_score', 5, 2)->nullable();
            $table->enum('result', ['pass', 'fail']);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('reviewed_by');
            $table->timestamp('reviewed_at');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('session_id')->references('id')->on('recruitment_sessions')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index('session_id');
            $table->index('result');
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
        Schema::dropIfExists('recruitment_psikotes');
    }
};
