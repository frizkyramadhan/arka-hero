<?php

namespace App\Support;

use App\Models\Administration;

class LotOriginPosting
{
    /**
     * Title, business unit, and department for a LOT traveler.
     * Employees without an acting position keep their administration posting.
     *
     * @return array{position_name: ?string, project_name: ?string, project_code: ?string, department_name: ?string, department_id: ?int, project_id: ?int}
     */
    public static function forAdministration(?Administration $administration, $originProjectId): array
    {
        $primary = self::describe($administration);
        if (! $administration || ! self::hasActing($administration)) {
            return $primary;
        }

        $origin = $originProjectId === null || $originProjectId === '' ? null : (int) $originProjectId;

        return self::pick($primary, self::actingRows($administration), $origin);
    }

    public static function hasActing(Administration $administration): bool
    {
        $administration->loadMissing(['assignments' => function ($query) {
            $query->where('is_active', 1);
        }]);

        return $administration->assignments->isNotEmpty();
    }

    /**
     * @param  array{project_id: ?int, position_name: ?string, project_name: ?string, project_code: ?string, department_name: ?string, department_id: ?int}  $primary
     * @param  array<int, array{project_id: int, is_active: int, position_name: ?string, project_name: ?string, project_code: ?string, department_name: ?string, department_id: ?int}>  $actingRows
     * @return array{project_id: ?int, position_name: ?string, project_name: ?string, project_code: ?string, department_name: ?string, department_id: ?int}
     */
    public static function pick(array $primary, array $actingRows, ?int $originProjectId): array
    {
        $active = array_values(array_filter($actingRows, function ($row) {
            return (int) ($row['is_active'] ?? 0) === 1;
        }));

        if ($active === [] || ! $originProjectId) {
            return $primary;
        }

        if ((int) ($primary['project_id'] ?? 0) === $originProjectId) {
            return $primary;
        }

        foreach ($active as $row) {
            if ((int) $row['project_id'] === $originProjectId) {
                return [
                    'project_id' => (int) $row['project_id'],
                    'position_name' => $row['position_name'] ?? null,
                    'project_name' => $row['project_name'] ?? null,
                    'project_code' => $row['project_code'] ?? null,
                    'department_name' => $row['department_name'] ?? null,
                    'department_id' => isset($row['department_id']) ? (int) $row['department_id'] : null,
                ];
            }
        }

        return $primary;
    }

    /**
     * @return array{project_id: ?int, position_name: ?string, project_name: ?string, project_code: ?string, department_name: ?string, department_id: ?int}
     */
    public static function describe(?Administration $administration): array
    {
        if (! $administration) {
            return [
                'project_id' => null,
                'position_name' => null,
                'project_name' => null,
                'project_code' => null,
                'department_name' => null,
                'department_id' => null,
            ];
        }

        $administration->loadMissing(['position.department', 'project']);

        return [
            'project_id' => $administration->project_id ? (int) $administration->project_id : null,
            'position_name' => $administration->position?->position_name,
            'project_name' => $administration->project?->project_name,
            'project_code' => $administration->project?->project_code,
            'department_name' => $administration->position?->department?->department_name,
            'department_id' => $administration->position?->department_id ? (int) $administration->position->department_id : null,
        ];
    }

    /**
     * @return array<int, array{project_id: int, is_active: int, position_name: ?string, project_name: ?string, project_code: ?string, department_name: ?string, department_id: ?int}>
     */
    public static function actingRows(Administration $administration): array
    {
        $administration->loadMissing(['assignments' => function ($query) {
            $query->where('is_active', 1)->with(['position.department', 'project']);
        }]);

        return $administration->assignments->map(function ($assignment) {
            return [
                'project_id' => (int) $assignment->project_id,
                'is_active' => (int) $assignment->is_active,
                'position_name' => $assignment->position?->position_name,
                'project_name' => $assignment->project?->project_name,
                'project_code' => $assignment->project?->project_code,
                'department_name' => $assignment->position?->department?->department_name,
                'department_id' => $assignment->position?->department_id ? (int) $assignment->position->department_id : null,
            ];
        })->values()->all();
    }
}
