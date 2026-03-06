<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CustomFieldResource extends JsonResource
{
    private $lang;

    public function __construct($resource, $lang = 'en')
    {
        parent::__construct($resource);
        $this->lang = $lang;
    }

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'name' => $this->getTranslatedName(),
            'value' => $this->value,
            'required' => $this->required,
            'type' => $this->type,
            'category_id' => $this->category_id,
            'visible' => $this->visible ,
            'category' => $this->getCategoryData(),
            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
            'created_by' => $this->getEmployeeName($this->createdBy),
            'modify_by' => $this->getEmployeeName($this->modifiedBy),
        ];
    }

    private function getTranslatedName()
    {
        return $this->lang == 'en' && !empty($this->name_en) ? $this->name_en : $this->name_ar;
    }

    private function getCategoryData()
    {
        if (!$this->category) {
            return null;
        }

        return [
            'id' => $this->category->id,
            'name_ar' => $this->category->name_ar,
            'name_en' => $this->category->name_en,
            'name' => $this->getTranslatedCategoryName(),
        ];
    }

    private function getTranslatedCategoryName()
    {
        if (!$this->category) {
            return null;
        }
        return $this->lang == 'en' && !empty($this->category->name_en) 
            ? $this->category->name_en 
            : $this->category->name_ar;
    }

    private function formatDateTime($date)
    {
        return $date ? Carbon::parse($date)->format('Y-m-d H:i:s') : null;
    }

    private function getEmployeeName($employee)
    {
        if (!$employee) {
            return null;
        }

        if (is_array($employee)) {
            return trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));
        }

        return trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
    }
}