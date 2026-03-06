<?php

namespace App\Http\Resources\Inventory;

use App\Traits\Paginatable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DspItemResource extends JsonResource
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
        $firstStore = $this->item?->productStores()?->first();

        return [
            'id'          => $this->id,
            'dsp_id'      => $this->dsp_id,

            'category' => $this->category ? [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ] : null,

            'item' => $this->item ? [
                'id'   => $this->item->id,
                'name' => $this->item->product?->name,
                'code' => $this->item->product?->code ?? null,
                'storage_location_id' => optional($firstStore)->storage_location_id,
                'storage_location' => optional(optional($firstStore)->storageLocation)->name,
                'shelve_id' => optional($firstStore)->shelve_id,
                'shelve_name' => optional(optional($firstStore)->shelve)->identifier,
            ] : null,

            'unit' => $this->unit ? [
                'id'   => $this->unit->id,
                'name' => $this->unit->name,
            ] : null,
            'received_quantity' => $this->received_quantity,

            'quantity'        => (float) $this->quantity,
            'notes'           => $this->notes,
            'issues' => DspReturnItemResource::collection($this->whenLoaded('issues')),
        ];
    }
}
