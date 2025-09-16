<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficialtravelResource extends JsonResource
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
            'official_travel_number' => $this->official_travel_number,
            'official_travel_date' => $this->official_travel_date,
            'status' => $this->status,
            'purpose' => $this->purpose,
            'destination' => $this->destination,
            'duration' => $this->duration,
            'departure_from' => $this->departure_from,
            'is_claimed' => $this->is_claimed,
            'claimed_at' => $this->claimed_at,
            'traveler' => $this->whenLoaded('traveler', function () {
                $traveler = [
                    'id' => $this->traveler->id,
                    'employee_id' => $this->traveler->employee_id,
                    'project_id' => $this->traveler->project_id,
                    'position_id' => $this->traveler->position_id,
                    'nik' => $this->traveler->nik,
                    'class' => $this->traveler->class,
                    'is_active' => $this->traveler->is_active
                ];

                if ($this->traveler->relationLoaded('employee')) {
                    $traveler['employee'] = [
                        'id' => $this->traveler->employee->id,
                        'fullname' => $this->traveler->employee->fullname,
                        'emp_pob' => $this->traveler->employee->emp_pob,
                        'emp_dob' => $this->traveler->employee->emp_dob,
                        'blood_type' => $this->traveler->employee->blood_type,
                        'nationality' => $this->traveler->employee->nationality,
                        'gender' => $this->traveler->employee->gender,
                        'marital' => $this->traveler->employee->marital,
                        'address' => $this->traveler->employee->address,
                        'village' => $this->traveler->employee->village,
                        'ward' => $this->traveler->employee->ward,
                        'district' => $this->traveler->employee->district,
                        'city' => $this->traveler->employee->city,
                        'phone' => $this->traveler->employee->phone,
                        'email' => $this->traveler->employee->email,
                    ];
                }

                if ($this->traveler->relationLoaded('position')) {
                    $position = [
                        'id' => $this->traveler->position->id,
                        'position_name' => $this->traveler->position->position_name
                    ];

                    if ($this->traveler->position->relationLoaded('department')) {
                        $position['department'] = [
                            'id' => $this->traveler->position->department->id,
                            'department_name' => $this->traveler->position->department->department_name
                        ];
                    }

                    $traveler['position'] = $position;
                }

                if ($this->traveler->relationLoaded('project')) {
                    $traveler['project'] = [
                        'id' => $this->traveler->project->id,
                        'project_code' => $this->traveler->project->project_code,
                        'project_name' => $this->traveler->project->project_name
                    ];
                }

                return $traveler;
            }),
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'project_code' => $this->project->project_code,
                    'project_name' => $this->project->project_name,
                    'project_location' => $this->project->project_location,
                    'bowheer' => $this->project->bowheer,
                    'project_status' => $this->project->project_status,
                ];
            }),
            'transportation' => $this->whenLoaded('transportation', function () {
                return [
                    'id' => $this->transportation->id,
                    'transportation_name' => $this->transportation->transportation_name,
                    'transportation_status' => $this->transportation->transportation_status,
                ];
            }),
            'accommodation' => $this->whenLoaded('accommodation', function () {
                return [
                    'id' => $this->accommodation->id,
                    'accommodation_name' => $this->accommodation->accommodation_name,
                    'accommodation_status' => $this->accommodation->accommodation_status,
                ];
            }),
            'details' => $this->whenLoaded('details', function () {
                return $this->details->map(function ($detail) {
                    $follower = null;
                    if ($detail->relationLoaded('follower')) {
                        $follower = [
                            'id' => $detail->follower->id,
                            'employee_id' => $detail->follower->employee_id,
                            'project_id' => $detail->follower->project_id,
                            'position_id' => $detail->follower->position_id,
                            'nik' => $detail->follower->nik,
                            'class' => $detail->follower->class,
                            'is_active' => $detail->follower->is_active
                        ];

                        if ($detail->follower->relationLoaded('employee')) {
                            $follower['employee'] = [
                                'id' => $detail->follower->employee->id,
                                'fullname' => $detail->follower->employee->fullname,
                                'emp_pob' => $detail->follower->employee->emp_pob,
                                'emp_dob' => $detail->follower->employee->emp_dob,
                                'blood_type' => $detail->follower->employee->blood_type,
                                'nationality' => $detail->follower->employee->nationality,
                                'gender' => $detail->follower->employee->gender,
                                'marital' => $detail->follower->employee->marital,
                                'address' => $detail->follower->employee->address,
                                'village' => $detail->follower->employee->village,
                                'ward' => $detail->follower->employee->ward,
                                'district' => $detail->follower->employee->district,
                                'city' => $detail->follower->employee->city,
                                'phone' => $detail->follower->employee->phone,
                                'email' => $detail->follower->employee->email,
                            ];
                        }

                        if ($detail->follower->relationLoaded('position')) {
                            $position = [
                                'id' => $detail->follower->position->id,
                                'position_name' => $detail->follower->position->position_name
                            ];

                            if ($detail->follower->position->relationLoaded('department')) {
                                $position['department'] = [
                                    'id' => $detail->follower->position->department->id,
                                    'department_name' => $detail->follower->position->department->department_name
                                ];
                            }

                            $follower['position'] = $position;
                        }

                        if ($detail->follower->relationLoaded('project')) {
                            $follower['project'] = [
                                'id' => $detail->follower->project->id,
                                'project_code' => $detail->follower->project->project_code,
                                'project_name' => $detail->follower->project->project_name
                            ];
                        }
                    }

                    return [
                        'id' => $detail->id,
                        'follower' => $follower
                    ];
                });
            }),
            'stops' => $this->whenLoaded('stops', function () {
                return $this->stops->map(function ($stop) {
                    $stopData = [
                        'id' => $stop->id,
                        'arrival_at_destination' => $stop->arrival_at_destination,
                        'arrival_remark' => $stop->arrival_remark,
                        'arrival_timestamps' => $stop->arrival_timestamps,
                        'departure_from_destination' => $stop->departure_from_destination,
                        'departure_remark' => $stop->departure_remark,
                        'departure_timestamps' => $stop->departure_timestamps,
                        'created_at' => $stop->created_at,
                        'updated_at' => $stop->updated_at,
                    ];

                    // Add arrival checker if loaded
                    if ($stop->relationLoaded('arrivalChecker') && $stop->arrivalChecker) {
                        $stopData['arrival_checker'] = [
                            'id' => $stop->arrivalChecker->id,
                            'name' => $stop->arrivalChecker->name,
                            'email' => $stop->arrivalChecker->email,
                            'user_status' => $stop->arrivalChecker->user_status,
                        ];
                    }

                    // Add departure checker if loaded
                    if ($stop->relationLoaded('departureChecker') && $stop->departureChecker) {
                        $stopData['departure_checker'] = [
                            'id' => $stop->departureChecker->id,
                            'name' => $stop->departureChecker->name,
                            'email' => $stop->departureChecker->email,
                            'user_status' => $stop->departureChecker->user_status,
                        ];
                    }

                    return $stopData;
                })->values();
            }),
            // Legacy single recommender/approver removed; use approval_plans instead
            'approval_plans' => $this->whenLoaded('approval_plans', function () {
                return $this->approval_plans->map(function ($plan) {
                    return [
                        'id' => $plan->id,
                        'approver' => [
                            'id' => optional($plan->approver)->id,
                            'name' => optional($plan->approver)->name,
                            'email' => optional($plan->approver)->email,
                        ],
                        'status' => $plan->status,
                        'remarks' => $plan->remarks,
                        'is_open' => $plan->is_open,
                        'is_read' => $plan->is_read,
                        'created_at' => $plan->created_at,
                        'updated_at' => $plan->updated_at,
                    ];
                })->values();
            }),
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                    'user_status' => $this->creator->user_status,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
