<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PositionResource extends JsonResource
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
            'position_name' => $this->position_name,
            'position_status' => $this->position_status,
            'department' => $this->whenLoaded('department'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
