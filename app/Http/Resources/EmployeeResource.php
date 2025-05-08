<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'fullname' => $this->fullname,
            'emp_pob' => $this->emp_pob,
            'emp_dob' => $this->emp_dob,
            'blood_type' => $this->blood_type,
            'religion' => $this->whenLoaded('religion', function () {
                return [
                    'id' => $this->religion->id,
                    'religion_name' => $this->religion->religion_name
                ];
            }),
            'nationality' => $this->nationality,
            'gender' => $this->gender,
            'marital' => $this->marital,
            'address' => $this->address,
            'village' => $this->village,
            'ward' => $this->ward,
            'district' => $this->district,
            'city' => $this->city,
            'phone' => $this->phone,
            'email' => $this->email,
            'identity_card' => $this->identity_card,
            'administrations' => $this->whenLoaded('administrations', function () {
                return $this->administrations->map(function ($administration) {
                    $data = [
                        'id' => $administration->id,
                        'nik' => $administration->nik,
                        'poh' => $administration->poh,
                        'doh' => $administration->doh,
                        'class' => $administration->class,
                        'foc' => $administration->foc,
                        'is_active' => $administration->is_active
                    ];

                    // Check if position relationship is loaded
                    if ($administration->relationLoaded('position')) {
                        $position = [
                            'id' => $administration->position->id,
                            'position_name' => $administration->position->position_name
                        ];

                        // Check if department relationship is loaded on position
                        if ($administration->position->relationLoaded('department')) {
                            $position['department'] = [
                                'id' => $administration->position->department->id,
                                'department_name' => $administration->position->department->department_name
                            ];
                        }

                        $data['position'] = $position;
                    }

                    // Check if project relationship is loaded
                    if ($administration->relationLoaded('project')) {
                        $data['project'] = [
                            'id' => $administration->project->id,
                            'project_code' => $administration->project->project_code,
                            'project_name' => $administration->project->project_name
                        ];
                    }

                    return $data;
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
