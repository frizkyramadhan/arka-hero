<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'back_to_work_date' => $this->back_to_work_date?->format('Y-m-d'),
            'total_days' => $this->total_days,
            'reason' => $this->reason,
            'requested_at' => $this->requested_at?->format('Y-m-d H:i:s'),
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'closed_at' => $this->closed_at?->format('Y-m-d H:i:s'),
            'supporting_document' => $this->supporting_document,
            'leave_type' => $this->whenLoaded('leaveType', function () {
                return [
                    'id' => $this->leaveType->id,
                    'code' => $this->leaveType->code ?? null,
                    'name' => $this->leaveType->name,
                    'category' => $this->leaveType->category,
                ];
            }),
            'administration' => $this->whenLoaded('administration', function () {
                if (! $this->administration) {
                    return null;
                }

                return [
                    'id' => $this->administration->id,
                    'nik' => $this->administration->nik,
                    'is_active' => $this->administration->is_active,
                ];
            }),
            'cancellations' => $this->whenLoaded('cancellations', function () {
                return $this->cancellations->map(static function ($c) {
                    return [
                        'id' => $c->id,
                        'days_to_cancel' => $c->days_to_cancel,
                        'reason' => $c->reason,
                        'status' => $c->status,
                        'requested_by' => $c->requested_by,
                        'requested_at' => $c->requested_at?->format('Y-m-d H:i:s'),
                        'confirmed_by' => $c->confirmed_by,
                        'confirmed_at' => $c->confirmed_at?->format('Y-m-d H:i:s'),
                        'confirmation_notes' => $c->confirmation_notes,
                    ];
                })->values()->all();
            }),
        ];
    }
}
