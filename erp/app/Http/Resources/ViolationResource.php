<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViolationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'violation_name' => $this->name,
            'max_repeat'    => $this->max_repeat,
            'within_period' => $this->within_period,
            'penalties' => $this->whenLoaded('violationPenalties', function () {
                return $this->violationPenalties->map(function ($penalty) {
                    return [
                        'id'         => $penalty->penalty?->id,
                        'order'      => $penalty->order_penalty,
                        'reason_ar'  => $penalty->penalty?->reason_ar,
                        'reason_en'  => $penalty->penalty?->reason_en,
                        'punishment_ar' => $penalty->penalty?->punishment_ar,
                        'punishment_en' => $penalty->penalty?->punishment_en,
                        'note'       => $penalty->penalty?->note,
                        'code'       => $penalty->penalty?->code,
                        'type'       => $penalty->penalty?->type,
                        'requires_approval' => $penalty->penalty?->requires_approval,
                    ];
                });
            }),

        ];
    }
}
