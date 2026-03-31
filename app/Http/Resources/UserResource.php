<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'email'       => $this->email,
            'employee_id' => $this->employee_id,
            'department'  => $this->department,
            'position'    => $this->position,
            'role'        => $this->role,
            'avatar_url'  => $this->avatar_url
                ? asset('storage/' . $this->avatar_url)
                : null,
            'created_at'  => $this->created_at?->toDateTimeString(),
        ];
    }
}