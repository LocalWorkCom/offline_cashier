<?php

namespace App\Http\Resources\Finance;

class CustomerResource extends AbstractFinanceResource
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
            'phone' => $item->phone,
        ];
    }
}
