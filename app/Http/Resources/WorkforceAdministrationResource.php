<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Administrasi untuk API workforce — termasuk terminasi (PHK/resign).
 */
class WorkforceAdministrationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nik' => $this->nik,
            'is_active' => (int) $this->is_active,
            'termination_date' => $this->termination_date?->format('Y-m-d'),
            'termination_reason' => $this->termination_reason,
            'poh' => $this->poh,
            'doh' => $this->doh?->format('Y-m-d'),
            'position' => $this->whenLoaded('position', function () {
                if (! $this->position) {
                    return null;
                }
                $position = [
                    'id' => $this->position->id,
                    'position_name' => $this->position->position_name,
                ];

                if ($this->position->relationLoaded('department') && $this->position->department) {
                    $position['department'] = [
                        'id' => $this->position->department->id,
                        'department_name' => $this->position->department->department_name,
                    ];
                }

                return $position;
            }),
            'project' => $this->whenLoaded('project', function () {
                if (! $this->project) {
                    return null;
                }

                return [
                    'id' => $this->project->id,
                    'project_code' => $this->project->project_code,
                    'project_name' => $this->project->project_name,
                ];
            }),
        ];
    }
}
