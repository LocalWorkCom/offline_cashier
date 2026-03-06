<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DelayDeductionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'ar');
        if ($lang == 'ar') {
            $type = $this->delay->time->type == 1 ? 'دقائق' : 'ساعات';
        }else{
            $type = $this->delay->time->type == 1 ? 'minutes' : 'hours';
        }
        $punishment = $lang == 'ar' ? $this->delay->time->punishment_ar : $this->delay->time->punishment_en;
        return [
            'id'=>$this->id,
            'employee_id'=>$this->employee_id,
            'employee_name'=>$this->delay->employee->first_name.' '.$this->delay->employee->last_name,
            'delay_id'=>$this->delay_id,
            'delay_time'=> $this->delay->time->time.' '.$type,
            'delay_punishment'=> $punishment,
            'deduction_amount' => $this->deduction_amount,
            ];
    }
}
