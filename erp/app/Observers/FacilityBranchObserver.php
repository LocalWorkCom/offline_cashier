<?php

namespace App\Observers;

use App\Models\FacilityBranch;
use App\Models\FacilityBranchLog;
use Illuminate\Support\Str;

class FacilityBranchObserver
{
    public function created(FacilityBranch $facilityBranch)
    {
        $this->createLog($facilityBranch, 'created');
    }

    public function updated(FacilityBranch $facilityBranch)
    {
        if ($facilityBranch->wasChanged()) {
            $changes = $this->getChangedValues($facilityBranch);

            if (!empty($changes)) {
                $this->createLog($facilityBranch, 'updated', $changes);
            }
        }
    }

    public function deleted(FacilityBranch $facilityBranch)
    {
        $this->createLog($facilityBranch, 'deleted');
    }

    private function createLog(FacilityBranch $facilityBranch, string $action, array $changes = null)
    {
        $logValues = $changes ?? $this->formatAttributesWithRelations($facilityBranch);

        FacilityBranchLog::create([
            'facility_branch_id' => $facilityBranch->id,
            'action' => $action,
            'log_values' => $logValues,
            'log_timestamp' => now(),
            'created_by' => auth('employee')->id(),
        ]);
    }

    private function formatAttributesWithRelations(FacilityBranch $facilityBranch): array
    {
        $data = [];

        foreach ($facilityBranch->getAttributes() as $key => $value) {
            if (Str::endsWith($key, '_id')) {
                $relationName = Str::before($key, '_id');

                if (method_exists($facilityBranch, $relationName)) {
                    $related = $facilityBranch->$relationName;
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

    private function getChangedValues(FacilityBranch $facilityBranch): array
    {
        $changes = [];
        $original = $facilityBranch->getOriginal();

        foreach ($facilityBranch->getChanges() as $key => $newValue) {
            if (!in_array($key, ['updated_at', 'modified_by'])) {
                if (Str::endsWith($key, '_id')) {
                    $relationName = Str::before($key, '_id');

                    if (method_exists($facilityBranch, $relationName)) {
                         $relatedClass = get_class($facilityBranch->$relationName);
                        $oldModel = $relatedClass::find($original[$key] ?? null);
                        $newModel = $facilityBranch->$relationName;

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
