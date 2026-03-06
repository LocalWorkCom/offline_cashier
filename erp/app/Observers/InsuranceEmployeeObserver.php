<?php

namespace App\Observers;

use App\Models\InsuranceEmployee;
use App\Models\InsuranceEmployeeLog;

class InsuranceEmployeeObserver
{

    public function created(InsuranceEmployee $insuranceEmployee): void
    {
        $this->createLog($insuranceEmployee, 'created');
    }

    public function updated( InsuranceEmployee $insuranceEmployee): void
    {
        // Only log if there are actual changes (not just timestamps)
        if ($insuranceEmployee->wasChanged() && !$insuranceEmployee->wasChanged('updated_at')) {
            $changes = $this->getChangedValues($insuranceEmployee);

            if (!empty($changes)) {
                $this->createLog($insuranceEmployee, 'updated', $changes);
            }
        }
    }

    public function deleted(InsuranceEmployee $insuranceEmployee): void
    {
        $insuranceEmployee->update(['deleted_by' => auth('employee')->id()]);
        $this->createLog($insuranceEmployee, 'deleted');
    }

    private function createLog(InsuranceEmployee $insuranceEmployee, string $action, array $changes = null)
    {
        InsuranceEmployeeLog::create([
            'insurance_employee_id' => $insuranceEmployee->id,
            'action' => $action,
            'log_values' => $changes ?? $insuranceEmployee->getAttributes(),
            'log_timestamp' => now(),
            'created_by' => auth('employee')->id(),
        ]);
    }

    private function getChangedValues(InsuranceEmployee $insuranceEmployee): array
    {
        $changes = [];
        $original = $insuranceEmployee->getOriginal();

        foreach ($insuranceEmployee->getChanges() as $key => $newValue) {
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
