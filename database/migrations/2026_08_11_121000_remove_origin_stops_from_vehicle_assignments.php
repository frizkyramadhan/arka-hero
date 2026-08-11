<?php

use App\Models\VehicleAssignmentStop;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove legacy origin trip-stops; origin lives on vehicle_assignments header only.
        $origins = DB::table('vehicle_assignment_stops')
            ->where('stop_type', 'origin')
            ->get(['id', 'assignment_id']);

        if ($origins->isEmpty()) {
            return;
        }

        DB::table('vehicle_assignment_stops')
            ->where('stop_type', 'origin')
            ->delete();

        $assignmentIds = $origins->pluck('assignment_id')->unique();
        foreach ($assignmentIds as $assignmentId) {
            $stops = DB::table('vehicle_assignment_stops')
                ->where('assignment_id', $assignmentId)
                ->orderBy('sequence')
                ->orderBy('created_at')
                ->get(['id']);

            $seq = 0;
            foreach ($stops as $stop) {
                DB::table('vehicle_assignment_stops')
                    ->where('id', $stop->id)
                    ->update(['sequence' => $seq++]);
            }
        }
    }

    public function down(): void
    {
        // Irreversible data cleanup — origin rows are not restored.
    }
};
