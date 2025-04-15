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
            'official_travel_status' => $this->official_travel_status,
            'approval_status' => $this->approval_status,
            'is_claimed' => $this->is_claimed,
            'claimed_at' => $this->claimed_at,
            'departure_at_destination' => $this->departure_at_destination,
            'arrival_at_destination' => $this->arrival_at_destination,
            'traveler' => $this->whenLoaded('traveler'),
            'project' => $this->whenLoaded('project'),
            'transportation' => $this->whenLoaded('transportation'),
            'accommodation' => $this->whenLoaded('accommodation'),
            'details' => $this->whenLoaded('details'),
            'recommender' => $this->whenLoaded('recommender'),
            'approver' => $this->whenLoaded('approver'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
