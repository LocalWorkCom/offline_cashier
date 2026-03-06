<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'brand_id'       => $this->brand_id,
            'type'           => $this->type,
            'main_unit_id'   => $this->main_unit_id,
            'currency_code'  => $this->currency_code,
            'category_id'    => $this->category_id,
            'is_valid'       => $this->is_valid,
            'sku'            => $this->sku,
            'barcode'        => $this->barcode,
            'code'           => $this->code,
            'is_remind'      => $this->is_remind,
            'is_have_expired' => $this->is_have_expired,
            'name'           => $this->name,
            'image'          => $this->image,
            'description'    => $this->description,
        ];
    }
}
