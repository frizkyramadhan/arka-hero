<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_notification_sends', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 64);
            $table->string('document_id', 64);
            $table->string('event', 64);
            $table->unsignedBigInteger('recipient_user_id');
            // 0 = no related approval plan (MySQL UNIQUE treats NULL as distinct)
            $table->unsignedBigInteger('approval_plan_id')->default(0);
            // '' = immediate send; Y-m-d for daily reminder dedupe
            $table->string('dedupe_day', 10)->default('');
            $table->timestamps();

            $table->unique(
                ['document_type', 'document_id', 'event', 'recipient_user_id', 'approval_plan_id', 'dedupe_day'],
                'document_notification_sends_unique'
            );
            $table->index(['document_type', 'document_id']);
            $table->index('recipient_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_notification_sends');
    }
};
