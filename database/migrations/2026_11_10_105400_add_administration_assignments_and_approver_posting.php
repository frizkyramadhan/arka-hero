<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('administration_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('administration_id')->constrained('administrations')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects');
            $table->foreignId('position_id')->constrained('positions');
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->unique(['administration_id', 'project_id']);
        });

        Schema::table('approval_plans', function (Blueprint $table) {
            $table->string('approver_position_name')->nullable()->after('approval_order');
            $table->string('approver_project_label')->nullable()->after('approver_position_name');
        });

        $duplicateActiveNik = DB::table('administrations')
            ->select('nik')
            ->where('is_active', 1)
            ->whereNotNull('nik')
            ->where('nik', '!=', '')
            ->groupBy('nik')
            ->havingRaw('COUNT(*) > 1')
            ->limit(1)
            ->get();

        if ($duplicateActiveNik->isEmpty()) {
            Schema::table('administrations', function (Blueprint $table) {
                $table->string('active_nik')->nullable()->storedAs(
                    "IF(is_active = 1 AND nik IS NOT NULL AND nik <> '', nik, NULL)"
                );
                $table->unique('active_nik');
            });
        }

        $this->assignEddyPostings();
    }

    public function down()
    {
        if (Schema::hasColumn('administrations', 'active_nik')) {
            Schema::table('administrations', function (Blueprint $table) {
                $table->dropUnique(['active_nik']);
                $table->dropColumn('active_nik');
            });
        }

        Schema::table('approval_plans', function (Blueprint $table) {
            $table->dropColumn(['approver_position_name', 'approver_project_label']);
        });

        Schema::dropIfExists('administration_assignments');
    }

    /**
     * One administration (NIK 13282). Signatures read these rows, not the home project/position.
     */
    private function assignEddyPostings(): void
    {
        $administrationId = DB::table('administrations')
            ->where('nik', '13282')
            ->where('is_active', 1)
            ->value('id');
        $apsId = DB::table('projects')->where('project_code', 'APS')->value('id');
        $hoId = DB::table('projects')->where('project_code', '000H')->value('id');
        $projectManagerId = DB::table('positions')
            ->join('departments', 'departments.id', '=', 'positions.department_id')
            ->where('positions.position_name', 'Project Manager')
            ->where('departments.department_name', 'Operation')
            ->value('positions.id');
        $hcsManagerId = DB::table('positions')->where('position_name', 'HCS Manager')->value('id');

        if (! $administrationId || ! $apsId || ! $hoId || ! $projectManagerId || ! $hcsManagerId) {
            return;
        }

        $now = now();
        foreach ([[$apsId, $projectManagerId], [$hoId, $hcsManagerId]] as [$projectId, $positionId]) {
            DB::table('administration_assignments')->updateOrInsert(
                ['administration_id' => $administrationId, 'project_id' => $projectId],
                [
                    'position_id' => $positionId,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
};
