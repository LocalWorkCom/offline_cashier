<?php

namespace App\Http\Resources\purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'ar');
        return [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'name' =>  $lang == 'ar' ? $this->name_ar : $this->name_en,
            'percentage' => $this->percentage,
            'is_default' => $this->is_default,
                        'is_active' => $this->is_active,

            'created_at' => formatDateTime($this->created_at, $this->lang),
            'updated_at' => formatDateTime($this->updated_at, $this->lang),
        ];
    }
}
