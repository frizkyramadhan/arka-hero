<?php

namespace App\Support;

class AdministrationAssignmentInput
{
    /**
     * @param  array<int, mixed>  $input
     * @return array<int, array{project_id: int, position_id: int, is_active: int}>
     */
    public static function rows(array $input, ?int $primaryProjectId = null): array
    {
        $rows = [];
        $seen = [];

        foreach ($input as $row) {
            if (! is_array($row)) {
                continue;
            }

            $projectId = (int) ($row['project_id'] ?? 0);
            $positionId = (int) ($row['position_id'] ?? 0);
            if ($projectId === 0 && $positionId === 0) {
                continue;
            }
            if ($projectId === 0 || $positionId === 0) {
                throw new \InvalidArgumentException('Each posting needs a project and a position.');
            }
            if ($primaryProjectId && $projectId === $primaryProjectId) {
                throw new \InvalidArgumentException('Acting position must use a different project from the primary project.');
            }
            if (isset($seen[$projectId])) {
                throw new \InvalidArgumentException('Each project can have only one acting position.');
            }

            $seen[$projectId] = true;
            $rows[] = [
                'project_id' => $projectId,
                'position_id' => $positionId,
                'is_active' => (int) ($row['is_active'] ?? 0) === 1 ? 1 : 0,
            ];
        }

        return $rows;
    }
}
