<?php

namespace App\Observers;

use App\Models\Insurance;
use App\Models\InsuranceLog;

class InsuranceObserver
{
    public function created(Insurance $insurance): void
    {
        $this->createLog($insurance, 'created');
    }

    public function updated(Insurance $insurance): void
    {
        // Only log if there are actual changes (not just timestamps)
        if ($insurance->wasChanged() && !$insurance->wasChanged('updated_at')) {
            $changes = $this->getChangedValues($insurance);

            if (!empty($changes)) {
                $this->createLog($insurance, 'updated', $changes);
            }
        }
    }

    public function deleted(Insurance $insurance): void
    {
        $insurance->update(['deleted_by' => auth('employee')->id()]);
        $this->createLog($insurance, 'deleted');
    }

    private function createLog(Insurance $insurance, string $action, array $changes = null)
    {
        InsuranceLog::create([
            'insurance_id' => $insurance->id,
            'action' => $action,
            'log_values' => $changes ?? $insurance->getAttributes(),
            'log_timestamp' => now(),
            'created_by' => auth('employee')->id(),
        ]);
    }

    private function getChangedValues(Insurance $insurance): array
    {
        $changes = [];
        $original = $insurance->getOriginal();

        foreach ($insurance->getChanges() as $key => $newValue) {
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
