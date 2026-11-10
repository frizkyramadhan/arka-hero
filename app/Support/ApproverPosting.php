<?php

namespace App\Support;

use App\Models\ApprovalPlan;

class ApproverPosting
{
    /**
     * Jabatan shown on a document. A saved decision keeps its snapshot.
     */
    public static function caption(ApprovalPlan $plan): string
    {
        if (filled($plan->approver_position_name) || filled($plan->approver_project_label)) {
            return self::display($plan->approver_position_name, $plan->approver_project_label);
        }

        $live = self::live($plan);

        return self::display($live['position'], $live['project_label']);
    }

    /**
     * @return array{position: ?string, project_label: ?string}
     */
    public static function live(ApprovalPlan $plan): array
    {
        $user = $plan->approver;
        if (! $user || ! $user->getKey()) {
            return ['position' => null, 'project_label' => null];
        }

        $user->loadMissing(['employee.administrations' => function ($query) {
            $query->where('is_active', 1)->with([
                'position',
                'project',
                'assignments' => function ($query) {
                    $query->where('is_active', 1)->with(['position', 'project']);
                },
            ]);
        }]);

        $assignments = [];
        $home = null;
        foreach ($user->employee?->administrations ?? [] as $administration) {
            if ($home === null) {
                $home = [
                    'project_id' => (int) $administration->project_id,
                    'position_name' => $administration->position?->position_name,
                    'project_name' => $administration->project?->project_name,
                    'project_code' => $administration->project?->project_code,
                ];
            }

            foreach ($administration->assignments as $assignment) {
                $assignments[] = [
                    'project_id' => (int) $assignment->project_id,
                    'is_active' => (int) $assignment->is_active,
                    'position_name' => $assignment->position?->position_name,
                    'project_name' => $assignment->project?->project_name,
                    'project_code' => $assignment->project?->project_code,
                ];
            }
        }

        return self::pick($assignments, self::documentProjectId($plan), $home);
    }

    public static function documentProjectId(ApprovalPlan $plan): ?int
    {
        $projectId = match ($plan->document_type) {
            'leave_request' => $plan->leaveRequest?->administration?->project_id,
            'officialtravel' => $plan->officialtravel?->official_travel_origin,
            'recruitment_request' => $plan->recruitmentRequest?->project_id,
            'overtime_request' => $plan->overtimeRequest?->project_id,
            'room_consumption_request' => $plan->roomConsumptionRequest?->project_id,
            'supply_order' => $plan->supplyOrder?->project_id,
            'flight_request' => $plan->flightRequest?->administration?->project_id,
            'flight_request_issuance' => $plan->flightRequestIssuance
                ?->flightRequests()
                ->with('administration')
                ->first()
                ?->administration
                ?->project_id,
            default => null,
        };

        return $projectId === null || $projectId === '' ? null : (int) $projectId;
    }

    /**
     * @param  array<int, array{project_id: int, is_active: int, position_name: ?string, project_name: ?string, project_code: ?string}>  $assignments
     * @param  array{project_id?: int, position_name: ?string, project_name: ?string, project_code: ?string}|null  $home
     * @return array{position: ?string, project_label: ?string}
     */
    public static function pick(array $assignments, ?int $projectId, ?array $home): array
    {
        if ($projectId && $home && (int) ($home['project_id'] ?? 0) === $projectId) {
            return [
                'position' => $home['position_name'] ?? null,
                'project_label' => self::projectLabel($home['project_name'] ?? null, $home['project_code'] ?? null),
            ];
        }

        if ($projectId) {
            foreach ($assignments as $assignment) {
                if ((int) $assignment['is_active'] === 1 && (int) $assignment['project_id'] === $projectId) {
                    return [
                        'position' => $assignment['position_name'] ?? null,
                        'project_label' => self::projectLabel($assignment['project_name'] ?? null, $assignment['project_code'] ?? null),
                    ];
                }
            }
        }

        if ($home) {
            return [
                'position' => $home['position_name'] ?? null,
                'project_label' => self::projectLabel($home['project_name'] ?? null, $home['project_code'] ?? null),
            ];
        }

        return ['position' => null, 'project_label' => null];
    }

    public static function projectLabel(?string $projectName, ?string $projectCode): ?string
    {
        $name = trim((string) $projectName);
        if ($name !== '' && str_contains($name, ' - ')) {
            return trim(explode(' - ', $name, 2)[0]);
        }

        $code = trim((string) $projectCode);

        return $code !== '' ? $code : ($name !== '' ? $name : null);
    }

    public static function display(?string $position, ?string $projectLabel): string
    {
        $position = trim((string) $position);
        $projectLabel = trim((string) $projectLabel);

        if ($position !== '' && $projectLabel !== '') {
            return $position.' - '.$projectLabel;
        }

        if ($position !== '') {
            return $position;
        }

        return $projectLabel !== '' ? $projectLabel : '—';
    }
}
