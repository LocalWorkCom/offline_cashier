<?php

namespace App\Http\Resources\Inventory;

use App\Traits\Paginatable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestItemResource extends JsonResource
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
        $lang = $request->header('lang', 'ar');

        // Base item data
        $data = [
            'id'                   => $this->id,
            'pr_id'                => $this->purchase_request_id,
            'note'                 => $this->note,
            'ordered_quantity'     => (float) $this->ordered_quantity,
            'current_stock_quantity' => (float) $this->current_stock_level,
            'unit' => $this->unit ? [
                'id'   => $this->unit->id,
                'name' => $this->unit->name,
            ] : null,
        ];
        // Module-specific data
        if ($request->attributes->get('module') === 'purchase' && $this->product) {
            $data['product'] = [
                'id'         => $this->product->id,
                'name'       => $this->product->product?->name,            ];

            $data['category'] = [ 'id'   => $this->product->product->Category->id,
                    'name' => $this->product->product->Category->name,];
            $data['brand'] = [ 'id'   => $this->product->brand->id,
                'name' => $this->product->brand->name,];

        } else {
            // Default product info for other modules
            $data['product'] = $this->product ? [
                'id'   => $this->product->id,
                'name' => $this->product->product?->name,
            ] : null;
        }

        return $data;
    }
}
