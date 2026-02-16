<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds submitted_by_user: user-submitted FPTK (from my-requests) stored as draft;
     * HR can assign letter number and approve later.
     */
    public function up()
    {
        Schema::table('recruitment_requests', function (Blueprint $table) {
            $table->boolean('submitted_by_user')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('recruitment_requests', function (Blueprint $table) {
            $table->dropColumn('submitted_by_user');
        });
    }
};
