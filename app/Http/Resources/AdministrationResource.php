<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdministrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nik' => $this->nik,
            'poh' => $this->poh,
            'doh' => $this->doh,
            'class' => $this->class,
            'foc' => $this->foc,
            'agreement' => $this->agreement,
            'company_program' => $this->company_program,
            'no_fptk' => $this->no_fptk,
            'no_sk_active' => $this->no_sk_active,
            'is_active' => $this->is_active,
            'employee' => $this->whenLoaded('employee', function () {
                if (! $this->employee) {
                    return null;
                }

                return [
                    'fullname' => $this->employee->fullname,
                ];
            }),
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
