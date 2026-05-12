<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('officialtravel_stops', function (Blueprint $table) {
            $table->unsignedSmallInteger('sort_order')->default(0)->after('official_travel_id');
            $table->text('destination')->nullable()->after('sort_order');
        });

        // Backfill destination from parent LOT; sort_order from creation order
        if (Schema::hasTable('officialtravel_stops') && Schema::hasTable('officialtravels')) {
            $stops = DB::table('officialtravel_stops')
                ->join('officialtravels', 'officialtravel_stops.official_travel_id', '=', 'officialtravels.id')
                ->select(
                    'officialtravel_stops.id',
                    'officialtravels.destination as ot_destination',
                    'officialtravel_stops.official_travel_id'
                )
                ->orderBy('officialtravel_stops.official_travel_id')
                ->orderBy('officialtravel_stops.id')
                ->get();

            $orderByTravel = [];
            foreach ($stops as $row) {
                $tid = $row->official_travel_id;
                $orderByTravel[$tid] = ($orderByTravel[$tid] ?? -1) + 1;
                DB::table('officialtravel_stops')
                    ->where('id', $row->id)
                    ->update([
                        'sort_order' => $orderByTravel[$tid],
                        'destination' => $row->ot_destination,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('officialtravel_stops', function (Blueprint $table) {
            $table->dropColumn(['sort_order', 'destination']);
        });
    }
};
