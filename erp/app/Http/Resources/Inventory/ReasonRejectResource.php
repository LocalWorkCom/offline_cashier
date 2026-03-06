<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReasonRejectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'en');

        return [
            'id'          => $this->id,
            'name'      => $this->name,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'is_active' => $this->is_active,
            'created_at' => formatDateTime($this->created_at, $this->lang,'date'),
            'updated_at' => formatDateTime($this->updated_at, $this->lang,'date'),
        ];
    }
}
