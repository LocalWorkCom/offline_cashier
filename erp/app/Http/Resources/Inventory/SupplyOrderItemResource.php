<?php

namespace App\Http\Resources\Inventory;

use App\Traits\Paginatable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplyOrderItemResource extends JsonResource
{
    use Paginatable;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'en');
        return [
            'id'          => $this->id,
            'so_id'      => $this->supply_order_id,
            'product' => $this->product_brand_id ? [
                'id'   => $this->product_brand_id,
                'name' => $this->productBrand?->product?->name,
            ] : null,


            'unit' => $this->unit ? [
                'id'   => $this->unit_id,
                'name' => $this->unit?->name,
            ] : null,
            'note' => $this->notes,

            'ordered_quantity'        => (float) $this->quantity,
        ];
    }
}
