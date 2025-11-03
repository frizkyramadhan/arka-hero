<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
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
                if (!$this->employee) {
                    return null;
                }
                return [
                    'id' => $this->employee->id,
                    'fullname' => $this->employee->fullname,
                    'emp_pob' => $this->employee->emp_pob,
                    'emp_dob' => $this->employee->emp_dob,
                    'blood_type' => $this->employee->blood_type,
                    'nationality' => $this->employee->nationality,
                    'gender' => $this->employee->gender,
                    'marital' => $this->employee->marital,
                    'address' => $this->employee->address,
                    'village' => $this->employee->village,
                    'ward' => $this->employee->ward,
                    'district' => $this->employee->district,
                    'city' => $this->employee->city,
                    'phone' => $this->employee->phone,
                    'email' => $this->employee->email,
                    'religion' => $this->employee->relationLoaded('religion') && $this->employee->religion ? [
                        'id' => $this->employee->religion->id,
                        'religion_name' => $this->employee->religion->religion_name
                    ] : null,
                ];
            }),
            'position' => $this->whenLoaded('position', function () {
                if (!$this->position) {
                    return null;
                }
                $position = [
                    'id' => $this->position->id,
                    'position_name' => $this->position->position_name,
                ];

                if ($this->position->relationLoaded('department') && $this->position->department) {
                    $position['department'] = [
                        'id' => $this->position->department->id,
                        'department_name' => $this->position->department->department_name
                    ];
                }

                return $position;
            }),
            'project' => $this->whenLoaded('project', function () {
                if (!$this->project) {
                    return null;
                }
                return [
                    'id' => $this->project->id,
                    'project_code' => $this->project->project_code,
                    'project_name' => $this->project->project_name
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
