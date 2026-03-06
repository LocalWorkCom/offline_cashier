<?php

namespace App\Http\Resources;

use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'ar');
        if ($lang == 'en') {
            $name = 'name_en';
        }
        else{
            $name = 'name_ar';
        }
        return
            [
                'id' => $this->id,
                'employee_code' => $this->employee_code,
                'user_id' => $this->user_id,
                'name' => $this->first_name.' '.$this->last_name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'position'=> $this->position->$name??null,
                'department'=> $this->department->$name??null,
                'delays'=>DelayResource::collection($this->delays),
                'penalties'=>PenaltyResource::collection($this->penalties),
                'advances'=>AdvanceResource::collection($this->advances),
                'payrolls'=>PayrollResource::collection($this->payrolls),
            ];
    }
}
