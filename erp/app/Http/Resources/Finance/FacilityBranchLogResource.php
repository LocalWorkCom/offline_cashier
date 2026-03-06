<?php

namespace App\Http\Resources\Finance;

class FacilityBranchLogResource extends AbstractFinanceResource
{
    public function formatItem($item): array
    {
        return [
            'id' => $item->id,
            'facility_branch_id' => $item->facility_branch_id,
            'action' => $item->action,
            'log_values' => $item->log_values,
            'log_timestamp' => $item->log_timestamp,
            'created_by' => $item->creator->full_name ?? '',
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ];

    }
}
