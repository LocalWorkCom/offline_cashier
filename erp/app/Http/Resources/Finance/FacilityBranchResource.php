<?php

namespace App\Http\Resources\Finance;

class FacilityBranchResource extends AbstractFinanceResource
{
    public function formatItem($item): array
    {
        return [
            'id' => $item->id,
            'branch_id' => $item->branch_id,
            'facility_id' => $item->facility_id,
            'branch' => $item->branch->name ?? '',
            'facility' => $item->facility->name ?? '',
            'is_active' => $item->is_active,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
            'created_by' => $item->creator->full_name ?? '',
            'modified_by' => $item->modifier->full_name ?? '',
            'deleted_by' => $item->deleter->full_name ?? '',
        ];

    }
}
