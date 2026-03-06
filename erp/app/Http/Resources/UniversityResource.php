<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UniversityResource extends JsonResource
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
            'name_ar' => $this->name,
            'name_en' => $this->name,
            'image' => $this->logo,
            'country' => [
                'id' => $this->country?->id,
                'name' => $this->country?->name,
            ],
            'employees_count' => $this->employees_count,
        ];
    }
}
