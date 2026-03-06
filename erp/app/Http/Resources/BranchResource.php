<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
   public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'is_open' => $this->is_open,
            'is_branch_open' => $this->is_branch_open,
            // 'pivot' => $this->whenPivotLoaded('branch_coupon', function () {
            //     return $this->pivot;
            // }),
        ];
    }
}
