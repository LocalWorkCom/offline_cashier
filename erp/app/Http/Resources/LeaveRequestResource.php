<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    // dd($this->employees);
    $lang = $request->header('lang', 'en');

    return [
      "id" => $this->id,
      "from" => $this->from,
      "to" => $this->to,
      "leave_count" => $this->leave_count,
      "reason" => $this->reason,
      "status" => $this->status,
      "file" => $this->file,
      "position" => $this->position
        ? $this->position->only('id', 'name_en', 'name_ar')
        : null,
      "department" => $this->employees && $this->employees->department
        ? collect(new DepartmentResource($this->employees->department))->only('id', 'name_en', 'name_ar')
        : null,
      "employee" => $this->employees ? $this->employees->only('id', 'first_name', 'last_name', 'employee_code', 'email', 'phone_number') : null,
      "leave_types" => $this->leaveTypes ? [
        'id' => $this->leaveTypes->id,
        'name' => $lang == 'en' ? $this->leaveTypes->name_en : $this->leaveTypes->name_ar
      ] : null,
    ];
  }
}
