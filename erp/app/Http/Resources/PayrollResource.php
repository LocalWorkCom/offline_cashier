<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'employee_id'=>$this->employee_id,
            'employee_name'=>$this->employee->first_name.' '.$this->employee->last_name,
            'base_salary'=>$this->base_salary,
            'bonus'=>$this->bonus,
            'deductions'=>$this->deductions,
            'advance'=>$this->advance,
            'taxes'=>$this->taxes,
            'insurance'=>$this->insurance,
            'net_salary'=>$this->net_salary,
            'pay_date'=>$this->pay_date,
        ];
    }
}
