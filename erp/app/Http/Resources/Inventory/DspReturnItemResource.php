<?php

namespace App\Http\Resources\Inventory;

use App\Models\DspItemIssue;
use App\Traits\Paginatable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DspReturnItemResource extends JsonResource
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
        return [
            'id' => $this->id,
            'issue_type_id' => $this->issue_type_id,
            'quantity' => $this->quantity,
            'unit' => optional($this->unit)->name,
            'notes' => $this->notes,
        ];
    }
}
