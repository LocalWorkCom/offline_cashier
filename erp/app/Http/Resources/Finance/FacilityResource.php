<?php

namespace App\Http\Resources\Finance;
class FacilityResource extends AbstractFinanceResource
{
    public function formatItem($item): array
    {
        $lang = request()->header('lang', 'en');

        return [
            'id'                          => $item->id,
            'name'                        => $item->name_ar,
            'is_active'                   => $item->is_active,
            'currency_id'                 => $item->currency_id,
            'currency'                    => $lang === 'ar' ? $item->currency?->currency_ar : $item->currency?->currency_en,
            'country_id'                  => $item->country_id,
            'country'                     => $lang === 'ar' ? $item->country?->name_ar : $item->country?->name_en,
            'code'                        => $item->code,
            'logo'                        => $item->logo,
            'email'                       => $item->email,
            'address'                     => $item->address,
            'tax_id_number'               => $item->tax_id_number,
            'commercial_registration'    => $item->commercial_registration,
            'commercial_registration_number' => $item->commercial_registration_number,
            'language'                    => $item->language,
            'vat_registration_number'     => $item->vat_registration_number,
            'created_at'                  => $item->created_at?->toDateTimeString(),
            'updated_at'                  => $item->updated_at?->toDateTimeString(),
            'deleted_at'                  => $item->deleted_at?->toDateTimeString(),
            'created_by'                  => $item->creator?->full_name ?? '',
            'modified_by'                 => $item->modifier?->full_name ?? '',
            'deleted_by'                  => $item->deleter?->full_name ?? '',
        ];
    }
}
