<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngredientResource extends JsonResource
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
      'recipe_id'      => $this->recipe_id,
      'product_brand_id'     => $this->product_brand_id,
      'quantity'       => $this->quantity,
      'loss_percent'   => $this->loss_percent,
      'product'        => $this->when(
        $this->relationLoaded('product') && $this->product?->relationLoaded('product'),
        fn() => new ProductResource($this->product->product)
      ),
    ];
  }
}
