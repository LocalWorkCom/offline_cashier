<?php

namespace App\Http\Resources\Finance;

class CostCenterLogResource extends AbstractFinanceResource
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
            'cost_center' => $item->costCenter ? new CostCenterResource($item->costCenter) : null,
            'created_at' => $item->created_at->format('Y-m-d H:i:s'),
            'created_by' => $item->createdByUser?->name,
        ];
    }
}
