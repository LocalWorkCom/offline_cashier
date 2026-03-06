<?php

namespace App\Http\Resources\Inventory;

use App\Traits\Paginatable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WasteReportResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'en');

        return [
            'id'             => $this->id,
            'report_number'         => $this->report_number,
            'name'         => $this->name,

            'date' => $this->date,

            'time' => $lang == 'ar'
                ? Carbon::parse($this->date)->format('h:i') . ' ' .
                (Carbon::parse($this->date)->format('A') == 'AM' ? 'صباحًا' : 'مساءً')
                : Carbon::parse($this->date)->format('h:i A'),
            'status'     => [
                'key' => $this->status,
                'name' => $this->status_label,
            ],
            'employee'  => $this->employee ? [
                'id'   => $this->employee_id,
                'name' => $this->employee->first_name . ' ' . $this->employee->last_name,
            ] : null,

            'firstQualityEmployee' => $this->firstQualityOfficer ? [
                'id'   => $this->first_quality_officer_id,
                'name' => $this->firstQualityOfficer->first_name . ' ' . $this->secondQualityOfficer->last_name
            ] : null,

            'secondQualityEmployee' => $this->secondQualityOfficer ? [
                'id'   => $this->second_quality_officer_id,
                'name' => $this->secondQualityOfficer->first_name . ' ' . $this->secondQualityOfficer->last_name
            ] : null,

            'store' => $this->store ? [
                'id' => $this->store_id,
                'name' => $this->store->name
            ] : null,

            'items' => WasteReportItemResource::collection($this->items),
        ];
    }
}
