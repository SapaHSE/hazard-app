<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'type'        => $this->type,
            'severity'    => $this->severity,
            'status'      => $this->status,
            'location'    => $this->location,
            'reported_by' => new UserResource($this->whenLoaded('user')),
            'photos'      => ReportPhotoResource::collection($this->whenLoaded('photos')),
            'created_at'  => $this->created_at?->toDateTimeString(),
            'updated_at'  => $this->updated_at?->toDateTimeString(),
        ];
    }
}