<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportViolatonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
     public function toArray($request)
    {
        return [
            'employee_id'      => $this->employee_id,
            'employee_name'    => $this->employee?->first_name . ' ' . $this->employee?->last_name,
            'violations'       => $this->employee?->violations->map(function ($violation) {
                return [
                    'violation_name' => $violation->violationPenalty->violation->name ?? null,
                    'penalty_name'   => $violation->violationPenalty->penalty->name ?? null,
                    'order_penalty'  => $violation->violationPenalty->order_penalty ?? null,
                    'assigned_at'    => $violation->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'total_violations' => $this->total_violations,
        ];
    }
}
