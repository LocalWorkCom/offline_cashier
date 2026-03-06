<?php

namespace App\Http\Resources\purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorBasicResource extends JsonResource
{
    public function toArray($request)
    {
        $lang = $request->header('lang', 'ar');
        $typeTranslations = [
            'individual'   => ['ar' => 'فردي', 'en' => 'Individual'],
            'company'      => ['ar' => 'شركة', 'en' => 'Company'],
            'local_market' => ['ar' => 'السوق المحلي', 'en' => 'Local Market'],
        ];
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address'=>$this->address,
            'tax_card_number' => $this->info?->tax_card_number,
            'commercial_registration_number' => $this->info?->commercial_registration_number,
            'type' => [
                'id' => $this->type,
                'value' => $typeTranslations[$this->type][$lang] ?? $this->type,
            ],
        ];
    }
}
