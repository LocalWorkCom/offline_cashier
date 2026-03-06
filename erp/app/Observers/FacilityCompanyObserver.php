<?php

namespace App\Observers;

use App\Models\FacilityCompany;
use App\Models\FacilityCompanyLog;
use Illuminate\Support\Str;

class FacilityCompanyObserver
{
    public function created(FacilityCompany $facilityCompany)
    {
        $this->createLog($facilityCompany, 'created');
    }

    public function updated(FacilityCompany $facilityCompany)
    {
        if ($facilityCompany->wasChanged()) {
            $changes = $this->getChangedValues($facilityCompany);

            if (!empty($changes)) {
                $this->createLog($facilityCompany, 'updated', $changes);
            }
        }
    }

    public function deleted(FacilityCompany $facilityCompany)
    {
        $this->createLog($facilityCompany, 'deleted');
    }

    private function createLog(FacilityCompany $facilityCompany, string $action, array $changes = null)
    {
        $logValues = $changes ?? $this->formatAttributesWithRelations($facilityCompany);

        FacilityCompanyLog::create([
            'facility_company_id' => $facilityCompany->id,
            'action' => $action,
            'log_values' => $logValues,
            'log_timestamp' => now(),
            'created_by' => auth('employee')->id(),
        ]);
    }

    private function formatAttributesWithRelations(FacilityCompany $facilityCompany): array
    {
        $data = [];

        foreach ($facilityCompany->getAttributes() as $key => $value) {
            if (Str::endsWith($key, '_id')) {
                $relationName = Str::before($key, '_id');

                if (method_exists($facilityCompany, $relationName)) {
                    $related = $facilityCompany->$relationName;
                    $data[$key] = $related ? ($related->name ?? $related->title ?? $related->id) : null;
                } else {
                    $data[$key] = $value;
                }
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    private function getChangedValues(FacilityCompany $facilityCompany): array
    {
        $changes = [];
        $original = $facilityCompany->getOriginal();

        foreach ($facilityCompany->getChanges() as $key => $newValue) {
            if (!in_array($key, ['updated_at', 'modified_by'])) {
                if (Str::endsWith($key, '_id')) {
                    $relationName = Str::before($key, '_id');

                    if (method_exists($facilityCompany, $relationName)) {
                        $relatedClass = get_class($facilityCompany->$relationName);
                        $oldModel = $relatedClass::find($original[$key] ?? null);
                        $newModel = $facilityCompany->$relationName;

                        $changes[$key] = [
                            'old' => $oldModel?->name ?? $oldModel?->title ?? $original[$key] ?? null,
                            'new' => $newModel?->name ?? $newModel?->title ?? $newValue
                        ];
                    } else {
                        $changes[$key] = [
                            'old' => $original[$key] ?? null,
                            'new' => $newValue
                        ];
                    }
                } else {
                    $changes[$key] = [
                        'old' => $original[$key] ?? null,
                        'new' => $newValue
                    ];
                }
            }
        }

        return $changes;
    }
}
