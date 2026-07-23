<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_consumption_requests', function (Blueprint $table) {
            $table->string('zoom_topic')->nullable()->after('zoom_meeting_id');
        });
    }

    public function down(): void
    {
        Schema::table('room_consumption_requests', function (Blueprint $table) {
            $table->dropColumn('zoom_topic');
        });
    }
};
