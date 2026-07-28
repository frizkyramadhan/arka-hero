<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('man_power_plans', function (Blueprint $table) {
            $table->string('status_before_hold', 50)->nullable()->after('status');
        });

        // Expand status enum to include on_hold (MySQL)
        DB::statement("ALTER TABLE man_power_plans MODIFY COLUMN status ENUM('active', 'closed', 'on_hold') NOT NULL DEFAULT 'active'");

        Schema::create('man_power_plan_holds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('man_power_plan_id');
            $table->unsignedBigInteger('held_by');
            $table->timestamp('held_at');
            $table->text('hold_reason');
            $table->unsignedBigInteger('released_by')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->text('release_reason')->nullable();
            $table->timestamps();

            $table->foreign('man_power_plan_id', 'mpp_holds_plan_fk')
                ->references('id')->on('man_power_plans')->onDelete('cascade');
            $table->foreign('held_by', 'mpp_holds_held_by_fk')
                ->references('id')->on('users')->onDelete('restrict');
            $table->foreign('released_by', 'mpp_holds_released_by_fk')
                ->references('id')->on('users')->onDelete('restrict');

            $table->index('man_power_plan_id', 'mpp_holds_plan_idx');
            $table->index('released_at', 'mpp_holds_released_at_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('man_power_plan_holds');

        // Move any on_hold rows back to active before shrinking enum
        DB::table('man_power_plans')->where('status', 'on_hold')->update(['status' => 'active']);
        DB::statement("ALTER TABLE man_power_plans MODIFY COLUMN status ENUM('active', 'closed') NOT NULL DEFAULT 'active'");

        Schema::table('man_power_plans', function (Blueprint $table) {
            $table->dropColumn('status_before_hold');
        });
    }
};
