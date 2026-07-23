<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_consumption_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('letter_number_id')->nullable();
            $table->string('letter_number')->nullable();
            $table->string('request_number')->nullable()->index();

            $table->uuid('meeting_room_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('requested_by');

            $table->string('meeting_title');
            $table->date('meeting_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('attendees_count')->default(1);
            $table->text('facilities')->nullable();
            $table->boolean('need_zoom')->default(false);

            $table->json('manual_approvers')->nullable();
            $table->string('status', 30)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();

            // Phase 2 Zoom / IT WO stubs
            $table->string('it_wo_id')->nullable();
            $table->string('it_wo_number')->nullable();
            $table->string('zoom_meeting_id')->nullable();
            $table->string('zoom_join_url')->nullable();
            $table->string('zoom_sync_status', 30)->default('not_required');

            $table->timestamps();

            $table->foreign('letter_number_id')->references('id')->on('letter_numbers')->nullOnDelete();
            $table->foreign('meeting_room_id')->references('id')->on('meeting_rooms')->restrictOnDelete();
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('requested_by')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['status', 'meeting_date']);
            $table->index(['meeting_room_id', 'meeting_date']);
            $table->index('requested_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_consumption_requests');
    }
};
