<?php

namespace App\Http\Resources;

use App\Models\Journal;

class InsuranceResource extends AbstractResource
{
    /**
     * Format a single item
     */
    protected function formatItem($item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
            'subscription_num' => $item->subscription_num,
            'automatic_transfer' => (bool) $item->automatic_transfer,
            'company_percentage' => (double) $item->company_percentage,
            'employee_percentage' => (double) $item->employee_percentage,
            'subscription_expenses' => (double) $item->subscription_expenses,
            'journals' => !empty($item->journal_ids) ? Journal::whereIn('id', $item->journal_ids)->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                ];
            }) : [],
            'is_active' => (bool) $item->is_active,
            'created_by' => $item->createdByUser?->name,
            'modified_by' => $item->modifiedByUser?->name,
            'deleted_by' => $item->deletedByUser?->name,
        ];
    }
}
