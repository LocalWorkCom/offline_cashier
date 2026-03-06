<?php

namespace App\Http\Resources;

use App\Http\Resources\AbstractResource;

class InsuranceEmployeeLogResource extends AbstractResource
{
    /**
     * Format a single item
     */
    protected function formatItem($item): array
    {
        return [
            'id' => $item->id,
            'action' => $item->action,
            'log_values' => $item->log_values,
            'log_timestamp' => $item->log_timestamp->format('Y-m-d H:i:s'),
            'insurance_employee' => $item->insuranceEmployee ? new InsuranceEmployeeResource($item->insuranceEmployee) : null,
            'created_at' => $item->created_at->format('Y-m-d H:i:s'),
            'created_by' => $item->createdByUser?->name,
        ];
    }
}
