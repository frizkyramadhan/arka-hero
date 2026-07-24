<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * acted_at stores when each approver actually approved/rejected.
     * Do not rely on updated_at — closing is_open on document completion
     * was overwriting every plan's updated_at to the final close time.
     */
    public function up()
    {
        Schema::table('approval_plans', function (Blueprint $table) {
            $table->timestamp('acted_at')->nullable()->after('approval_order');
        });

        // Best-effort backfill: use current updated_at for already-decided plans.
        // Note: plans closed via mass is_open update may already share one timestamp.
        DB::table('approval_plans')
            ->whereIn('status', [1, 2])
            ->whereNull('acted_at')
            ->update([
                'acted_at' => DB::raw('updated_at'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('approval_plans', function (Blueprint $table) {
            $table->dropColumn('acted_at');
        });
    }
};
