<?php

namespace App\Http\Resources\Finance;

class JournalResource extends AbstractFinanceResource
{
    /**
     * Format a single item
     */
    protected function formatItem($item): array
    {
        $lang = request()->header('lang', 'en');

        $active = $lang == "ar" ? "مفعل" : "active";
        if($item->is_active == 1){
            $active = $lang == "ar" ? "مفعل" : "active";
        }
        if($item->is_active == 2){
            $active = $lang == "ar" ? "مأرشف" : "archived";
        }
        if($item->is_active == 0){
            $active = $lang == "ar" ? "غير مفعل" : "not active";
        }
        return [
            'id' => $item->id,
            'name' => $lang === 'ar' ? $item->name_ar : $item->name_en,
            'name_ar' => $item->name_ar,
            'name_en' => $item->name_en,
            'description' => $lang === 'ar' ? $item->description_ar : $item->description_ar,
            'is_active' => $item->is_active,
            'facility_id' => $item->facility_id,
            'facility' => $lang === 'ar' ? $item->facility?->name_ar : $item->facility?->name_en,
            'currency_id' => $item->currency_id,
            'currency' => $lang === 'ar' ? $item->currency?->currency_ar : $item->currency?->currency_en,
            'code' => $item->code,
            'type' => $item->type,
            'level' => $item->level,
            'account_type' => $item->account_type,
            'debit' => $item->debit,
            'credit' => $item->credit,
            'balance' => $item->balance,
            'childern_count' => 0,
            'has_transaction' => (count($item->journalEntryDetails) > 0) ? true : false , //0 no, 1 yes - form journal entry details
            'status' => $active,
            'parent_id' => $item->parent_id,
            'children' => $this->formatChildren($item->children),
            'created_by' => $item->createdBy?->first_name . ' ' . $item->createdBy?->last_name,
            'modified_by' => $item->modifiedBy?->first_name . ' ' . $item->modifiedBy?->last_name,
            'deleted_by' => $item->deletedBy?->first_name . ' ' . $item->deletedBy?->last_name,
        ];
    }

    private function formatChildren($children)
    {
        if (!$children || $children->isEmpty()) {
            return [];
        }
        return $children->map(function ($child) {
            return array_merge(
                (new self($child))->toArray(request()),
                ['children' => $this->formatChildren($child->children)]
            );
        })->all();
    }
}
