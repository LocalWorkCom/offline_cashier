<?php

namespace App\Http\Resources\Finance;

class FacilityCompanyResource extends AbstractFinanceResource
{
    public function formatItem($item): array
    {
        return [
            'id' => $item->id,
            'company_id' => $item->company_id,
            'facility_id' => $item->facility_id,
            'company' => $item->company->name ?? '',
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
