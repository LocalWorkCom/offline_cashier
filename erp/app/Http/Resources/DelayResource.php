<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DelayResource extends JsonResource
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

        if ($lang == 'ar') {
            $type = $this->time->type == 1 ? 'دقائق' : 'ساعات';
        }else{
            $type = $this->time->type == 1 ? 'minutes' : 'hours';
        }

        return [
            'id' => $this->id,
            'time_id' => $this->time->id ?? null,
            'time' => $this->time->time.' '.$type?? null,
            'employee_id' => $this->employee->id ?? null,
            'employee' => trim($this->employee->first_name . ' ' . $this->employee->last_name),
            'note' => $this->note,
            'deductions'=>DeductionResource::collection($this->delayDeductions),
        ];
    }
}
