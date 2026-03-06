<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdvanceSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'min_salary' => $this->min_salary,
            'amount' => $this->amount,
            'months' => $this->months,
            'amount_per_month' => $this->amount_per_month
        ];
    }
}
