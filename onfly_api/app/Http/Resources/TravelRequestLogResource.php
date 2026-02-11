<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelRequestLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'travel_request_id' => $this->travel_request_id,
            'travel_request_code' => $this->travelRequest?->code,
            'changed_by' => $this->changedBy ? [
                'id' => $this->changedBy->id,
                'name' => $this->changedBy->name,
                'email' => $this->changedBy->email,
            ] : null,
            'from_status' => $this->from_status,
            'to_status' => $this->to_status,
            'created_at' => $this->created_at,
        ];
    }
}
