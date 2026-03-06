<?php

namespace App\Http\Resources\Finance;

class VendorResource extends AbstractFinanceResource
{
    /**
     * Format a single item
     */
    protected function formatItem($item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name_ar,
            'phone' => $item->phone,
        ];
    }
}
