<?php

namespace App\Http\Resources\purchase;

use App\Traits\Paginatable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderItemResource extends JsonResource
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
            'id'            => $this->id,
            'po_id'         => $this->po_id,
            'product' => $this->product?[
                'id'=>$this->product->id,
                'name'=>$this->product->name

            ]:null,
            'category' => $this->category?[
                'id'=>$this->category->id,
                'name'=>$this->category->name

            ]:null,
            'brand' => $this->brand?[
                'id'=>$this->brand->id,
                'name'=>$this->brand->name

            ]:null,
            'unit' => $this->unit?[
                'id'=>$this->unit->id,
                'name'=>$this->unit->name

            ]:null,
            'quantity'=>$this->quantity,
            'price'=>$this->price,
            'sub_total'=>$this->sub_total,
            'note'=>$this->notes
            ];
    }
}
