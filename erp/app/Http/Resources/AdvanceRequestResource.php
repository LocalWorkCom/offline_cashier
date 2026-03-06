<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdvanceRequestResource extends JsonResource
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
            if($this->status == 0){
                $status = 'rejected request';
            }
            else if ($this->status == 1){
                $status = 'pending request';
            }
            else{
                $status = 'approved request';
            }
        } else{
            if($this->status == 0){
                $status = 'طلب مرفوض';
            }
            else if ($this->status == 1){
                $status = 'طلب معلق';
            }
            else{
                $status = 'طلب مقبول';
            }
        }
        return [
            'id' => $this->id,
            'advance_setting_id' => $this->advance_setting_id,
            'employee_id' => $this->employee_id,
            'employee' => trim($this->employee->first_name . ' ' . $this->employee->last_name),
            'reason' => $this->reason,
            'status' => $status,
        ];
    }
}
