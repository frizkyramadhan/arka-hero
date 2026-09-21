<?php

use App\Models\LetterNumber;
use App\Models\VehicleAssignment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rewrite existing FOA Nos to FOA-{project_code}-{sequence}.
 * Letter numbers are left unchanged.
 */
return new class extends Migration
{
    public function up(): void
    {
        VehicleAssignment::query()
            ->orderBy('created_at')
            ->each(function (VehicleAssignment $assignment) {
                $letter = $assignment->letter_number_id
                    ? LetterNumber::with('project')->find($assignment->letter_number_id)
                    : null;

                $letterString = $letter?->letter_number
                    ?: $assignment->letter_number
                    ?: $assignment->form_number;

                $projectCode = $letter?->project?->project_code
                    ?? $letter?->project_code
                    ?? null;

                if (! $projectCode && $assignment->project_id) {
                    $projectCode = DB::table('projects')
                        ->where('id', $assignment->project_id)
                        ->value('project_code');
                }

                $newFormNumber = VehicleAssignment::formatFormNumber(
                    (string) $letterString,
                    $projectCode ? (string) $projectCode : null
                );

                if ($newFormNumber === $assignment->form_number) {
                    return;
                }

                $assignment->forceFill(['form_number' => $newFormNumber])->saveQuietly();
            });
    }

    public function down(): void
    {
        // Irreversible: old FOA4965-style values are not stored separately.
    }
};
