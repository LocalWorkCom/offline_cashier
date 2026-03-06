<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdvanceResource extends JsonResource
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
            'request_id' => $this->request_id,
            'employee_id' => $this->employee_id,
            'employee' => trim($this->employee->first_name . ' ' . $this->employee->last_name),
            'approval_date' => $this->approval_date,
            'starting_date' => $this->starting_date,
            'ending_date' => $this->ending_date,
            'amount_per_month' => $this->amount_per_month,
            'note' => $this->note,
            ];
    }
}
