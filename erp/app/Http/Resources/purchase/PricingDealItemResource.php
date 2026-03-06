<?php

namespace App\Http\Resources\purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingDealItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'ar');
           return [
            'id'        => $this->id,
            'product'   => $this->product->name ?? null,
            'brand'     => $this->brand->name ?? null,
            'category'  => $this->category->name ?? null,
            'unit'      => $this->unit->name ?? null,
            'quantity'  => $this->quantity,
            'unit_price' => $this->unit_price,
            'discount'   => $this->discount,
            'tax'        => $this->tax,
            'net_amount' => $this->net_amount,
            'notes'      => $this->notes,
        ];
    }
}
