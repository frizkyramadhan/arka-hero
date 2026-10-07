<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('recruitment_request_closures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('recruitment_request_id');
            $table->string('close_reason', 30);
            $table->text('close_notes')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamp('closed_at');
            $table->unsignedBigInteger('reopened_by')->nullable();
            $table->timestamp('reopened_at')->nullable();
            $table->text('reopen_reason')->nullable();
            $table->timestamps();

            $table->foreign('recruitment_request_id', 'rr_closures_request_fk')
                ->references('id')->on('recruitment_requests')->onDelete('cascade');
            $table->foreign('closed_by', 'rr_closures_closed_by_fk')
                ->references('id')->on('users')->onDelete('restrict');
            $table->foreign('reopened_by', 'rr_closures_reopened_by_fk')
                ->references('id')->on('users')->onDelete('restrict');

            $table->index('recruitment_request_id', 'rr_closures_request_idx');
            $table->index('reopened_at', 'rr_closures_reopened_at_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('recruitment_request_closures');
    }
};
