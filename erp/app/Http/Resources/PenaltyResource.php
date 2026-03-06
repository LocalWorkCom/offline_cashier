<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenaltyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'ar');

        $reasonField = $lang === 'en' ? 'reason_en' : 'reason_ar';

        $penaltyType = $this->reason->type ?? null;
//        dd((!in_array($penaltyType, ['bonus_loss', 'allowance_reduction'])) ?  );
        return array_filter([
            'id' => $this->id,
            'employee_id' => $this->employee->id ?? null,
            'employee' => trim($this->employee->first_name . ' ' . $this->employee->last_name),
            'reason_id' => $this->reason->id ?? null,
            'reason' => $this->reason->{$reasonField} ?? null,
            'penalty_type' => $penaltyType,
            'penalty_type_code' => $this->reason->code ?? null,
            'amount' => $this->amount ?? null,
            'calculation_type' => $this->calculation_type ?? null,
            'bonus_type' => !in_array($penaltyType, ['salary_deduction','fine', 'allowance_reduction']) ? $this->bonus_type : null,
            'allowance_type' => !in_array($penaltyType, ['salary_deduction','fine', 'bonus_loss']) ? $this->allowance_type : null,
            'violation_type_id' => !in_array($penaltyType, ['salary_deduction','bonus_loss', 'allowance_reduction']) ? $this->violation_type_id : null,
            'effective_date' => $this->effective_date ?? null,
            'end_date' => $this->end_date ?? null,
            'status' => $this->approval->status ?? null,
            'note' => $this->note,
            'documents' => $this->documents ?? null,
            // 'deductions' => DeductionResource::collection($this->penaltyDeductions),
        ], fn ($value) => $value !== null); // remove null values from the response
    }
}
