<?php

namespace App\Http\Resources\Finance;

use App\Models\Facility;

class CurrencyShowResource extends AbstractFinanceResource
{
    protected function formatItem($item): array
    {
        $lang = request()->header('lang', 'en');
        $employee = auth('employee')->user();
        $facility_id = $employee->employeeFacility->facility_id;

        // هات كل الـ exchange مش أول واحد بس
        $exchanges = $item->currencyExchange()
            // ->where('is_active', 1)
            ->where('facility_id', $facility_id)
            ->get();

        $exchangeValue = $exchanges->where('is_active', 1)->first()->exchange_value ?? 1;

        $facility = Facility::find($facility_id);
        $is_default = $facility && $facility->currency_id == $item->id;

        return [
            'id' => $item->id,
            'name' => $lang === "ar" ? $item->currency_ar : $item->currency_en,
            'currency_ar' => $item->currency_ar,
            'currency_en' => $item->currency_en,
            'currency_symbol' => $item->currency_symbol,
            'is_default' => (bool) $is_default,
            'is_active' => $item->is_active,
            'decimal_number' => $item->decimal_number,
            'currency_code' => $item->currency_code,
            'exchange_value' => $exchangeValue,

            // رجّع كل الـ exchanges
            'currency_exchang' => $exchanges->map(function ($exchange) use ($lang) {
                return [
                    'exchange_currency_id' => $exchange->exchange_currency_id,
                    'exchange_currency_name' => $lang === 'ar'
                        ? $exchange->exchangeCurrency?->currency_ar
                        : $exchange->exchangeCurrency?->currency_en,
                    'exchange_type' => $lang === 'ar' ? "يدوى" : "Manual",
                    'exchange_value' => $exchange->exchange_value,
                    'exchange_value_before' => $exchange->exchange_value_before,
                    'date' => $exchange->date,
                    'created_by' => $exchange->createdBy?->first_name . ' ' . $exchange->createdBy?->last_name,
                    'modified_by' => $exchange->modifiedBy?->first_name . ' ' . $exchange->modifiedBy?->last_name,
                    'deleted_by' => $exchange->deletedBy?->first_name . ' ' . $exchange->deletedBy?->last_name,
                ];
            }),
        ];
    }

}
