<?php

namespace App\Http\Resources;

class CurrencyResource extends AbstractResource
{
    /**
     * Format a single item
     */
    protected function formatItem($item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'symbol' => $item->currency_symbol,
            'is_default' => (bool) $item->is_default,
            'currency_code' => $item->currency_code,
            'original_names' => [
                'ar' => $item->currency_ar,
                'en' => $item->currency_en,
            ]
        ];
    }
}
