<?php

namespace App\Observers;

use App\Models\CostCenter;
use App\Models\CostCenterLog;

class CostCenterObserver
{

    public function created(CostCenter $costCenter): void
    {
        $this->createLog($costCenter, 'created');
    }

    public function updated(CostCenter $costCenter): void
    {
        // Only log if there are actual changes (not just timestamps)
        if ($costCenter->wasChanged() && !$costCenter->wasChanged('updated_at')) {
            $changes = $this->getChangedValues($costCenter);

            if (!empty($changes)) {
                $this->createLog($costCenter, 'updated', $changes);
            }
        }
    }

    public function deleted(CostCenter $costCenter): void
    {
        $costCenter->update(['deleted_by' => auth('employee')->id()]);
        $this->createLog($costCenter, 'deleted');
    }

    private function createLog(CostCenter $costCenter, string $action, array $changes = null)
    {
        CostCenterLog::create([
            'CostCenter_id' => $costCenter->id,
            'action' => $action,
            'log_values' => $changes ?? $costCenter->getAttributes(),
            'log_timestamp' => now(),
            'created_by' => auth('employee')->id(),
        ]);
    }

    private function getChangedValues(CostCenter $costCenter): array
    {
        $changes = [];
        $original = $costCenter->getOriginal();

        foreach ($costCenter->getChanges() as $key => $newValue) {
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
