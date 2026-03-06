<?php

namespace App\Http\Resources\Inventory;

use App\Traits\Paginatable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestDamyItemResource extends JsonResource
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
            'pr_id'      => $this->pr_id,
            'name' => $this->name,
                        'category' => $this->category,
            'brand' => $this->brand,

            'unit' => $this->unit,
            'note'=> $this->note,

            'quantity'        => (float) $this->ordered_quantity,
            'has_added'         =>  $this->status,
        ];
    }
}
