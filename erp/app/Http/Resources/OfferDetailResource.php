<?php

namespace App\Http\Resources;

use App\Models\Dish;
use App\Models\DishAddon;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferDetailResource extends JsonResource
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
            'offer_id' => $this->offer_id,
            'offer_name' => $this->offer->{"name_{$lang}"} ?? null,
            'is_active' => $this->offer ? ($this->offer->is_active == 0 ? false : true) : false,
            'offer_type' => $this->getOfferTypeName($lang),
            'type_id' => $this->type_id,
            'type_name' => $this->getTypeName($lang),
            'count' => $this->count,
        ];
    }
}
