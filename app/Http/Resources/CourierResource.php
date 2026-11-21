<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'phone'            => $this->phone,
            'email'            => $this->email,
            'nik'              => $this->nik,
            'address'          => $this->address,
            'city'             => $this->city,
            'vehicle_type'     => $this->vehicle_type,
            'vehicle_plate'    => $this->vehicle_plate,
            'level'            => $this->level,
            'is_active'        => $this->is_active,
            'rating'           => (float) $this->rating,
            'total_deliveries' => $this->total_deliveries,
            'registered_at'    => $this->registered_at?->toDateString(),
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
