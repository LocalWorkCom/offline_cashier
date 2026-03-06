<?php

namespace App\Http\Resources\Finance;

class JournalEntryResource extends AbstractFinanceResource
{
    /**
     * Format a single item
     */
    protected function formatItem($item): array
    {
        $lang = request()->header('lang', 'en');

        $details = $item->journalEntryDetails()
            ->where('journal_entry_id', $item->id)
            ->get();

        $detailsCollection = $details->isNotEmpty() ? $details : collect();

        return [
            'id' => $item->id,
            'facility_id' => $item->facility_id,
            'facility' => $lang === 'ar' ? $item->facility?->name_ar : $item->facility?->name_en,
            'currency_id' => $item->currency_id,
            'currency' => $lang === 'ar' ? $item->currency?->currency_ar : $item->currency?->currency_en,
            'journal_entry_department_id' => $item->journal_entry_department_id,
            'journalEntryDepartment' => $lang === 'ar' ? $item->journalEntryDepartment?->name_ar : $item->journalEntryDepartment?->name_en,
            'journal_entry_numner' => $item->journal_entry_numner,
            'ledger_number' => $item->ledger_number,
            'account_type' => $item->account_type,
            'date' => $item->date,
            'description' => $item->description,
            'file' => $item->file,
            'is_repeated' => $item->is_repeated,
            'repeated_count' => $item->repeated_count,
            'repeated_type' => $item->repeated_type,
            'status' => $item->status,
            'is_active' => $item->is_active,
            'total_credit_facility' => $item->total_credit,
            'total_debit_facility' => $item->total_debit,
            'total_credit' => $item->current_total_credit,
            'total_debit' => $item->current_total_debit,
            'details' => $detailsCollection->map(function ($detail) use ($lang) {
                return [
                    'id'               => $detail->id,
                    'journal_id'       => $detail->journal_id,
                    'journal_name'     => $lang === 'ar' ? $detail->journal?->name_ar : $detail->journal?->name_en,
                    'name'             => $detail->name,
                    'credit'           => $detail->current_credit ?? 0,
                    'debit'            => $detail->current_debit ?? 0,
                    'customer_id'      => $detail->customer_id,
                    'customer_name' => $lang === 'ar' ? $detail->customer?->name_ar : $detail->customer?->name_en,
                    'vendor_id'        => $detail->vendor_id,
                    'vendor_name' => $lang === 'ar' ? $detail->vendor?->name_ar : $detail->vendor?->name_en,
                    'cost_center_id'   => $detail->cost_center_id,
                    'cost_center_name' => $lang === 'ar' ? $detail->costCenter?->name_ar : $detail->costCenter?->name_en,
                    'description'      => $detail->description,
                ];
            })->toArray(),
            'created_by' => $item->createdBy?->first_name,
            'modified_by' => $item->modifiedBy?->first_name,
            'deleted_by' => $item->deletedBy?->first_name,
        ];
    }
}
