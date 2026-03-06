<?php

namespace App\Http\Resources\purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'ar');
        $egyptSymbol = $lang == 'ar' ? 'ج م' : 'egp';

        return [
            'id' => $this->id,
            'name_en' => $this->currency_en,
            'name_ar' => $this->currency_ar,
            'name' =>  $lang == 'ar' ? $this->currency_ar : $this->currency_en,
            'is_default' => $this->is_default,
            'symbole' => $this->currency_symbol,
            'price_egp' =>  $this->price_egp,
            'egypt_symbole' => $egyptSymbol,
            'convert_format' => '1' . $this->currency_symbol . '=' . $this->price_egp .' '. $egyptSymbol,
            'is_active' =>  $this->is_active,
            'created_at' => formatDateTime($this->created_at, $this->lang),
            'updated_at' => formatDateTime($this->updated_at, $this->lang),
        ];
    }
}
