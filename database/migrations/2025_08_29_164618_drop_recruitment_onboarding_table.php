<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('recruitment_onboardings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('recruitment_onboardings', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id');
            $table->date('onboarding_date');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('reviewed_by');
            $table->timestamp('reviewed_at');
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('recruitment_sessions')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
