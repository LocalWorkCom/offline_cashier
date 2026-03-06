<?php

namespace App\Observers;

use App\Models\Facility;
use App\Models\FacilityLog;

class FacilityObserver
{
    public function created(Facility $facility)
    {
        $this->createFacilityLog($facility, 'created');
    }

    public function updated(Facility $facility)
    {
        // Only log if there are actual changes (not just timestamps)
        if ($facility->wasChanged()) {
            $changes = $this->getChangedValues($facility);

            if (!empty($changes)) {
                $this->createFacilityLog($facility, 'updated', $changes);
            }
        }
    }

    public function deleted(Facility $facility)
    {
        $this->createFacilityLog($facility, 'deleted');
    }

    private function createFacilityLog(Facility $facility, string $action, array $changes = null)
    {
        FacilityLog::create([
            'facility_id' => $facility->id,
            'action' => $action,
            'log_values' => $changes ?? $facility->getAttributes(),
            'log_timestamp' => now(),
            'created_by' => auth('employee')->id(),
        ]);
    }

    private function getChangedValues(Facility $facility): array
    {
        $changes = [];
        $original = $facility->getOriginal();

        foreach ($facility->getChanges() as $key => $newValue) {
            // Skip timestamps and the updated_by field
            if (!in_array($key, ['updated_at', 'modified_by'])) {
                $changes[$key] = [
                    'old' => $original[$key] ?? null,
                    'new' => $newValue
                ];
            }
        }

        return $changes;
    }
}
