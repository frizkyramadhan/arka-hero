<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_bot_subscribers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('telegram_user_id')->unique();
            $table->string('telegram_username', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['is_active', 'telegram_user_id']);
            $table->index('user_id');
        });

        Schema::create('fuel_bot_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_uuid')->unique();
            $table->unsignedBigInteger('telegram_user_id')->index();
            $table->bigInteger('chat_id');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('status', 40)->default('received')->index();
            $table->string('receipt_path')->nullable();
            $table->string('telegram_file_id')->nullable();
            $table->json('parsed_json')->nullable();
            $table->string('ai_model')->nullable();
            $table->string('caption')->nullable();
            $table->uuid('fuel_record_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('fuel_record_id')->references('id')->on('fuel_records')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_bot_submissions');
        Schema::dropIfExists('fuel_bot_subscribers');
    }
};
