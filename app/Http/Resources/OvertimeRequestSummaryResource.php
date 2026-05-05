<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OvertimeRequestSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'register_number' => $this->register_number,
            'status' => $this->status,
            'overtime_date' => $this->overtime_date?->format('Y-m-d'),
            'remarks' => $this->remarks,
            'requested_at' => $this->requested_at?->format('Y-m-d H:i:s'),
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'rejected_at' => $this->rejected_at?->format('Y-m-d H:i:s'),
            'finished_at' => $this->finished_at?->format('Y-m-d H:i:s'),
            'finished_remarks' => $this->finished_remarks,
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'project_code' => $this->project->project_code,
                    'project_name' => $this->project->project_name,
                ];
            }),
            'employee' => $this->whenLoaded('details', function () {
                $detail = $this->details->first();

                if (
                    $detail?->relationLoaded('administration')
                    && $detail->administration
                    && $detail->administration->relationLoaded('employee')
                    && $detail->administration->employee
                ) {
                    return ['fullname' => $detail->administration->employee->fullname];
                }

                return null;
            }),
            'details' => $this->whenLoaded('details', function () {
                return $this->details->map(function ($detail) {
                    $employeePayload = null;
                    if (
                        $detail->relationLoaded('administration')
                        && $detail->administration
                        && $detail->administration->relationLoaded('employee')
                        && $detail->administration->employee
                    ) {
                        $employeePayload = ['fullname' => $detail->administration->employee->fullname];
                    }

                    return [
                        'id' => $detail->id,
                        'administration_id' => $detail->administration_id,
                        'time_in' => $detail->time_in,
                        'time_out' => $detail->time_out,
                        'work_description' => $detail->work_description,
                        'sort_order' => $detail->sort_order,
                        'employee' => $employeePayload,
                        'administration' => $detail->relationLoaded('administration') && $detail->administration ? [
                            'id' => $detail->administration->id,
                            'nik' => $detail->administration->nik,
                            'employee_id' => $detail->administration->employee_id,
                        ] : null,
                    ];
                });
            }),
        ];
    }
}
