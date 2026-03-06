<?php

namespace App\Http\Resources;

use App\Models\BranchMenu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
   public function toArray($request)
    {
      return [
            'id'      => $this->id,
            'code'    => $this->code,
            'type'    => $this->type,
            'value'   => $this->value,
            'is_active' => $this->is_active,
            'minimum_spend' => $this->minimum_spend,
            'usage_limit' => $this->usage_limit,
            'start_date' => $this->start_date ? \Carbon\Carbon::parse($this->start_date)->format('Y-m-d H:i') : null,
            'end_date'   => $this->end_date ? \Carbon\Carbon::parse($this->end_date)->format('Y-m-d H:i') : null,

            'branches' => $this->branches->map(function ($branch) {
                $dishIds = json_decode($branch->pivot->dish_ids, true) ?? [];

                $dishes = BranchMenu::with('dish')
                    ->whereIn('dish_id', $dishIds)
                    ->where('branch_id', $branch->id) // make sure dishes belong to branch
                    ->get()
                    ->map(function ($menu) {
                        return [
                            'id'   => $menu->dish_id,
                            'name' => $menu->dish->name_ar, // or name_en if needed
                        ];
                    });

                return [
                    'id'     => $branch->id,
                    'name'   => $branch->name,
                    'dishes' => $dishes,
                ];
            }),
        ];

    }
}
