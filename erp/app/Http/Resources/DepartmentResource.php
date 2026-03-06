<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $module = $request->attributes->get('module');

        $data = [
            "id" => $this->id,
            "name_en" => $this->name_en,
            "name_ar" => $this->name_ar,
            "description_en" => $this->description_en,
            "description_ar" => $this->description_ar,
            "instructions_en" => $this->instructions_en,
            "instructions_ar" => $this->instructions_ar,
            "parent_id" => $this->parent_id,
            'created_at' => formatDateTime($this->created_at, $this->lang,'date'),
            'updated_at' => formatDateTime($this->created_at, $this->lang,'date'),

        ];
        if ($module === 'procurement') {
            $data['name'] = $this->name;
            $data['created_at'] = $this->created_at;
            $data['updated_at'] = $this->updated_at;
            $data['status'] = $this->status == 'active' ? 1 : 0;
        }
        if ($module === 'hr' || $module === null) {
            $data['branch'] = new BranchResource($this->whenLoaded('branch'));
            $data['employees_count'] = $this->employees_count; // from withCount
            $data['employees']     = EmployeeResource::collection($this->whenLoaded('employees'));
        }
        if ($module === 'inventory') {
            // $data['status'] = $this->status;
        }
        return $data;
    }
}
