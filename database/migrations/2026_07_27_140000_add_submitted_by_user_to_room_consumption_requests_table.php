<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_consumption_requests', function (Blueprint $table) {
            $table->boolean('submitted_by_user')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('room_consumption_requests', function (Blueprint $table) {
            $table->dropColumn('submitted_by_user');
        });
    }
};
