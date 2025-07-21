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
        Schema::table('recruitment_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('approval_flow_id')->nullable()->after('id');
            $table->foreign('approval_flow_id')->references('id')->on('approval_flows')->onDelete('set null');
            $table->index('approval_flow_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recruitment_requests', function (Blueprint $table) {
            $table->dropForeign(['approval_flow_id']);
            $table->dropIndex(['approval_flow_id']);
            $table->dropColumn('approval_flow_id');
        });
    }
};
