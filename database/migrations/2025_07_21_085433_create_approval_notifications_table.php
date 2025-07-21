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
        Schema::create('approval_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_approval_id')->comment('Reference to document approval');
            $table->unsignedBigInteger('recipient_id')->comment('User who should receive the notification');
            $table->enum('notification_type', ['pending', 'approved', 'rejected', 'escalation'])->comment('Type of notification');
            $table->timestamp('sent_at')->nullable()->comment('When the notification was sent');
            $table->timestamp('read_at')->nullable()->comment('When the notification was read');
            $table->timestamps();

            // Indexes
            $table->index('recipient_id', 'idx_recipient');
            $table->index('sent_at', 'idx_sent_at');
            $table->index('read_at', 'idx_read_at');
            $table->index('notification_type', 'idx_notification_type');

            // Foreign keys
            $table->foreign('document_approval_id')->references('id')->on('document_approvals')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_notifications');
    }
};
