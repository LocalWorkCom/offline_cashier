<?php

namespace App\Http\Resources\purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class PricingDealShipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        // Translation mappings
        $typeTranslations = [
            'self'    => ['ar' => 'ذاتي',   'en' => 'Self'],
            'vendor'  => ['ar' => 'مورد',   'en' => 'Vendor'],
            'external' => ['ar' => 'خارجي',  'en' => 'External'],
        ];
        $pricingBasisTranslations = [
            'per order'   => ['ar' => 'لكل طلب',    'en' => 'Per Order'],
            'per shipment'      => ['ar' => 'لكل شحنه',    'en' => 'per shipment'],
            'per unit basis' => ['ar' => 'لكل وحده اساسيه',    'en' => 'per unit basis'],
            'free' => ['ar' => 'مجانى', 'en' => 'free'],
        ];

        return [
            'type' => [
                'key'   => $this->type,
                'value' => $typeTranslations[$this->type][$lang] ?? null
            ],
            'from_address' => $this->from_address,
            'to_address'   => $this->to_address,
            'price'        => $this->price,
            'pricing_basis' => [
                'key'   => $this->pricing_basis,
                'value' => $pricingBasisTranslations[$this->pricing_basis][$lang] ?? null
            ],
            'vendor'       => $this->vendor->name ?? null,
            'notes'        => $this->notes,
            'file'         => $this->file,
        ];
    }
}
