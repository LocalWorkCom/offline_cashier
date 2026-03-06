<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
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
            'country' => $this->country?->name,
            'city' => $this->city?->name,
            'area' => $this->area?->name,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address_type' => $this->address_type,
            'building' => $this->building,
            'street' => $this->street,
            'floor_number' => $this->floor_number,
            'apartment_number' => $this->apartment_number,
            'notes' => $this->notes,
            'country_code' => $this->country_code,
            'phone' => $this->address_phone,
            'is_default' => (bool) $this->is_default,
        ];
    }
}
