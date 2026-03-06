<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenaltyDeductionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'ar');
        $reason = $lang == 'ar' ? $this->penalty->reason->reason_ar : $this->penalty->reason->reason_en;
        $punishment = $lang == 'ar' ? $this->penalty->reason->punishment_ar : $this->penalty->reason->punishment_en;
        return [
            'id'=>$this->id,
            'employee_id'=>$this->employee_id,
            'employee_name'=>$this->penalty->employee->first_name.' '.$this->penalty->employee->last_name,
            'penalty_id'=>$this->penalty_id,
            'penalty_reason'=> $reason,
            'penalty_punishment'=> $punishment,
            'deduction_amount' => $this->deduction_amount,
        ];
    }
}
