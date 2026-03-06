<?php

namespace App\Http\Resources\Inventory;

use Carbon\Carbon;
use App\Models\Unit;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UnitResource extends ResourceCollection
{
    private $lang;
    private $inventoryActive;
    private $purchasesActive;

    public function __construct($resource, $lang = 'en')
    {
        parent::__construct($resource);
        $this->lang = $lang;
        // Check module statuses
        $this->inventoryActive = checkModuleStatus('Inventory');
        $this->purchasesActive = checkModuleStatus('Purchases');
    }

    public function toArray($request)
    {
        return $this->collection->map(function ($unit) {
            return $this->formatUnitResponse($unit);
        })->toArray();
    }

    private function formatUnitResponse($unit)
    {
        $isArray = is_array($unit);

        // Prepare unit data
        $unitData = [
            'id' => $isArray ? $unit['id'] : $unit->id,
            'name' => $this->getTranslatedField($unit, 'name'),
            'name_ar' => $isArray ? $unit['name_ar'] : $unit->name_ar,
            'name_en' => $isArray ? $unit['name_en'] : $unit->name_en,
            'abbreviation' => $isArray ? ($unit['abbreviation'] ?? null) : ($unit->abbreviation ?? null),
            'description' => $this->getTranslatedField($unit, 'description'),
            'description_ar' => $isArray ? ($unit['description_ar'] ?? null) : $unit->description_ar,
            'description_en' => $isArray ? ($unit['description_en'] ?? null) : $unit->description_en,
            'active' => (bool) ($isArray ? ($unit['active'] ?? 1) : ($unit->active ?? 1)),
            'created_at' => $this->formatDateTime($isArray ? $unit['created_at'] : $unit->created_at),
            'updated_at' => $this->formatDateTime($isArray ? $unit['updated_at'] : $unit->updated_at),
            'created_by' => $this->getEmployeeName($isArray ? ($unit['created_by'] ?? null) : ($unit->createdBy ?? null)),
            'modify_by' => $this->getEmployeeName($isArray ? ($unit['modify_by'] ?? null) : ($unit->modifiedBy ?? null)),
        ];

        // Build response based on module status
        $response = $unitData;

        return $response;
    }

    private function getTranslatedField($item, $field)
    {
        $isArray = is_array($item);
        $fieldAr = $field . '_ar';
        $fieldEn = $field . '_en';

        if ($isArray) {
            return $this->lang == 'en' && !empty($item[$fieldEn]) ? $item[$fieldEn] : $item[$fieldAr];
        } else {
            return $this->lang == 'en' && !empty($item->$fieldEn) ? $item->$fieldEn : $item->$fieldAr;
        }
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
