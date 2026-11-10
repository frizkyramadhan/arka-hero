<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Primary posting stays on administrations. Acting rows are other projects only.
     * NIK 13282 keeps project APS and takes the Operation Project Manager title.
     * HO HCS Manager remains the acting position.
     */
    public function up()
    {
        $administration = DB::table('administrations')
            ->where('nik', '13282')
            ->where('is_active', 1)
            ->first();
        $apsId = DB::table('projects')->where('project_code', 'APS')->value('id');
        $projectManagerId = DB::table('positions')
            ->join('departments', 'departments.id', '=', 'positions.department_id')
            ->where('positions.position_name', 'Project Manager')
            ->where('departments.department_name', 'Operation')
            ->value('positions.id');

        if (! $administration || ! $apsId || ! $projectManagerId || (int) $administration->project_id !== (int) $apsId) {
            return;
        }

        DB::table('administrations')
            ->where('id', $administration->id)
            ->update([
                'position_id' => $projectManagerId,
                'updated_at' => now(),
            ]);

        DB::table('administration_assignments')
            ->where('administration_id', $administration->id)
            ->where('project_id', $apsId)
            ->delete();
    }

    public function down()
    {
        $administration = DB::table('administrations')
            ->where('nik', '13282')
            ->where('is_active', 1)
            ->first();
        $apsId = DB::table('projects')->where('project_code', 'APS')->value('id');
        $hcsManagerId = DB::table('positions')->where('position_name', 'HCS Manager')->value('id');

        if (! $administration || ! $apsId || ! $hcsManagerId) {
            return;
        }

        DB::table('administrations')
            ->where('id', $administration->id)
            ->update([
                'position_id' => $hcsManagerId,
                'updated_at' => now(),
            ]);

        DB::table('administration_assignments')->updateOrInsert(
            [
                'administration_id' => $administration->id,
                'project_id' => $apsId,
            ],
            [
                'position_id' => DB::table('positions')
                    ->join('departments', 'departments.id', '=', 'positions.department_id')
                    ->where('positions.position_name', 'Project Manager')
                    ->where('departments.department_name', 'Operation')
                    ->value('positions.id'),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
};
