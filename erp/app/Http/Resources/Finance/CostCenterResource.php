<?php

namespace App\Http\Resources\Finance;

class CostCenterResource extends AbstractFinanceResource
{
    /**
     * Format a single item
     */
    protected function formatItem($item): array
    {
        $lang = request()->header('lang', 'en');

        return [
            'id' => $item->id,
            'name' => $item->name,
            // 'description' => $item->description,
            'is_active' => $item->is_active,
            'facility_id' => $item->facility_id,
            'facility' => $item->facility?->name,
            'company_id' => $item->company_id,
            'company' => $item->company?->name,
            'branch_id' => $item->branch_id,
            'branch' => $item->branch?->name,
            'code' => $item->code,
            'debit' => $item->debit,
            'credit' => $item->credit,
            'balance' => $item->balance,
            'type' => $item->type,
            'type_name' => $this->getTypeLabel($item->type, $lang),
            'parent_id' => $item->parent_id,
            'parent' => $item->parent?->name,
            'children' => $this->formatChildren($item->child),
            'has_transaction' => (count($item->journalEntryDetails) > 0) ? true : false , //0 no, 1 yes - form journal entry details
            'created_by' => $item->createdByUser?->name,
            'modified_by' => $item->modifiedByUser?->name,
            'deleted_by' => $item->deletedByUser?->name,
        ];
    }

    private function getTypeLabel($type, $lang)
    {
        return match ((int) $type) {
            1 => $lang === 'ar' ? 'مستقل' : 'Independent',
            2 => $lang === 'ar' ? 'شركة' : 'Company',
            3 => $lang === 'ar' ? 'فرع' : 'Branch',
            default => $lang === 'ar' ? 'غير محدد' : 'Unknown',
        };
    }

    private function formatChildren($children)
    {
        if (!$children || $children->isEmpty()) {
            return [];
        }

        return $children->map(function ($child) {
            return array_merge(
                (new self($child))->toArray(request()),
                ['children' => $this->formatChildren($child->child)]
            );
        })->all();
    }
}
