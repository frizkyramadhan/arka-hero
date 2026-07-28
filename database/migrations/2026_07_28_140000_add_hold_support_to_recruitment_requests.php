<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('recruitment_requests', function (Blueprint $table) {
            $table->string('status_before_hold', 50)->nullable()->after('status');
        });

        Schema::create('recruitment_request_holds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('recruitment_request_id');
            $table->unsignedBigInteger('held_by');
            $table->timestamp('held_at');
            $table->text('hold_reason');
            $table->unsignedBigInteger('released_by')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->text('release_reason')->nullable();
            $table->timestamps();

            $table->foreign('recruitment_request_id', 'rr_holds_request_fk')
                ->references('id')->on('recruitment_requests')->onDelete('cascade');
            $table->foreign('held_by', 'rr_holds_held_by_fk')
                ->references('id')->on('users')->onDelete('restrict');
            $table->foreign('released_by', 'rr_holds_released_by_fk')
                ->references('id')->on('users')->onDelete('restrict');

            $table->index('recruitment_request_id', 'rr_holds_request_idx');
            $table->index('released_at', 'rr_holds_released_at_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('recruitment_request_holds');

        Schema::table('recruitment_requests', function (Blueprint $table) {
            $table->dropColumn('status_before_hold');
        });
    }
};
