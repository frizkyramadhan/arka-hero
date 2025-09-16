<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Migrate existing arrival/departure data to stops table
        $officialtravels = DB::table('officialtravels')
            ->where(function ($query) {
                $query->whereNotNull('arrival_at_destination')
                    ->orWhereNotNull('departure_from_destination');
            })
            ->get();

        foreach ($officialtravels as $travel) {
            // Create stop record with existing data
            DB::table('officialtravel_stops')->insert([
                'official_travel_id' => $travel->id,
                'arrival_at_destination' => $travel->arrival_at_destination,
                'arrival_check_by' => $travel->arrival_check_by,
                'arrival_remark' => $travel->arrival_remark,
                'arrival_timestamps' => $travel->arrival_timestamps,
                'departure_from_destination' => $travel->departure_from_destination,
                'departure_check_by' => $travel->departure_check_by,
                'departure_remark' => $travel->departure_remark,
                'departure_timestamps' => $travel->departure_timestamps,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Remove old arrival/departure fields from officialtravels table
        Schema::table('officialtravels', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['arrival_check_by']);
            $table->dropForeign(['departure_check_by']);

            // Drop columns
            $table->dropColumn([
                'arrival_at_destination',
                'arrival_check_by',
                'arrival_remark',
                'arrival_timestamps',
                'departure_from_destination',
                'departure_check_by',
                'departure_remark',
                'departure_timestamps'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Re-add arrival/departure fields to officialtravels table
        Schema::table('officialtravels', function (Blueprint $table) {
            $table->datetime('arrival_at_destination')->nullable();
            $table->foreignId('arrival_check_by')->nullable()->constrained('users');
            $table->text('arrival_remark')->nullable();
            $table->timestamp('arrival_timestamps')->nullable();
            $table->datetime('departure_from_destination')->nullable();
            $table->foreignId('departure_check_by')->nullable()->constrained('users');
            $table->text('departure_remark')->nullable();
            $table->timestamp('departure_timestamps')->nullable();
        });

        // Migrate data back from stops table
        $stops = DB::table('officialtravel_stops')->get();

        foreach ($stops as $stop) {
            DB::table('officialtravels')
                ->where('id', $stop->official_travel_id)
                ->update([
                    'arrival_at_destination' => $stop->arrival_at_destination,
                    'arrival_check_by' => $stop->arrival_check_by,
                    'arrival_remark' => $stop->arrival_remark,
                    'arrival_timestamps' => $stop->arrival_timestamps,
                    'departure_from_destination' => $stop->departure_from_destination,
                    'departure_check_by' => $stop->departure_check_by,
                    'departure_remark' => $stop->departure_remark,
                    'departure_timestamps' => $stop->departure_timestamps
                ]);
        }

        // Drop stops table
        Schema::dropIfExists('officialtravel_stops');
    }
};
