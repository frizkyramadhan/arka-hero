<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Legacy LOT rows could have no officialtravel_stops until first stamp; destination lived on officialtravels.
     * Itinerary is authoritative on officialtravel_stops; seed one stop from the header when missing, then clear header.
     */
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('officialtravels') || ! DB::getSchemaBuilder()->hasTable('officialtravel_stops')) {
            return;
        }

        $rows = DB::table('officialtravels')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('officialtravel_stops')
                    ->whereColumn('officialtravel_stops.official_travel_id', 'officialtravels.id');
            })
            ->whereNotNull('officialtravels.destination')
            ->where('officialtravels.destination', '!=', '')
            ->get(['id', 'destination']);

        $now = now();
        foreach ($rows as $row) {
            $dest = preg_replace('/\s+/u', ' ', trim((string) $row->destination));
            if ($dest === '') {
                continue;
            }

            // Legacy header → satu stop sama seperti alur lama `arrivalStamp` (bukan destinasi manual proyek).
            $insert = [
                'official_travel_id' => $row->id,
                'sort_order' => 0,
                'destination' => $dest,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (Schema::hasColumn('officialtravel_stops', 'is_manual')) {
                $insert['is_manual'] = false;
            }

            DB::table('officialtravel_stops')->insert($insert);
        }

        DB::table('officialtravels')->update(['destination' => '']);

        Schema::table('officialtravels', function (Blueprint $table) {
            $table->string('destination')->default('')->change();
        });
    }

    public function down(): void
    {
        // Not reversible: header text was merged into stops or cleared intentionally.
    }
};
