<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            // 'item_code_id' => $this->item_code_id,
            'name_en'      => $this->name_en,
            'name_ar'      => $this->name_ar,
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'type'         => $this->type,
            'is_active'    => $this->is_active,
            'code'         => $this->code,
            'time'         => $this->time,
            'description'  => $this->description_en, // Or however you define description
            'ingredients'  => IngredientResource::collection($this->whenLoaded('ingredients')),
            'images'       => RecipeImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
