<?php

namespace App\Http\Resources;

class InsuranceEmployeeResource extends AbstractResource
{
    /**
     * Format a single item
     */
    protected function formatItem($item): array
    {
        return [
            'id' => $item->id,
            'insurance' => $item->insurance ? new InsuranceResource($item->insurance) : null,
            'employee' => $item->employee ? new InsuranceResource($item->employee) : null,
            'amount' => $item->amount,
            'date' => $item->date,
            'is_active' => (bool) $item->is_active,
            'created_by' => $item->createdByUser?->name,
            'modified_by' => $item->modifiedByUser?->name,
            'deleted_by' => $item->deletedByUser?->name,
        ];
    }
}
