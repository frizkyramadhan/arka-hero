<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_consumption_requests', function (Blueprint $table) {
            $table->string('zoom_passcode')->nullable()->after('zoom_join_url');
        });
    }

    public function down(): void
    {
        Schema::table('room_consumption_requests', function (Blueprint $table) {
            $table->dropColumn('zoom_passcode');
        });
    }
};
