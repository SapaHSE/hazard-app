<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'qr_code'         => $this->qr_code,
            'location'        => $this->location,
            'status'          => $this->status,
            'last_checked_at' => $this->last_checked_at?->format('d F Y'),
            'next_check_at'   => $this->next_check_at?->format('d F Y'),
            'registered_by'   => new UserResource($this->whenLoaded('user')),
        ];
    }
}