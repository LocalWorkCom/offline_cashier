<?php

namespace App\Http\Resources\purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'ar');

        // Translate enum 'type'
        $typeTranslations = [
            'individual'   => ['ar' => 'فردي', 'en' => 'Individual'],
            'company'      => ['ar' => 'شركة', 'en' => 'Company'],
            'local_market' => ['ar' => 'السوق المحلي', 'en' => 'Local Market'],
        ];

        // Translate communication methods
        $commTranslations = [
            'email' => ['ar' => 'البريد الإلكتروني', 'en' => 'Email'],
            'phone' => ['ar' => 'الهاتف', 'en' => 'Phone'],
            'whatsapp' => ['ar' => 'واتساب', 'en' => 'WhatsApp'],
        ];

        return [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'name' => $this->name,

            'type' => [
                'id' => $this->type,
                'value' => $typeTranslations[$this->type][$lang] ?? $this->type,
            ],

            'phone' => $this->phone,
            'country_code' => $this->country ? $this->country->phone_code : null,

            'address' => $this->resource->address ?? '',
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'email' => $this->email,

            'communication_method' => collect($this->communication_method)->map(function ($method) use ($lang, $commTranslations) {
                return [
                    'id' => $method,
                    'value' => $commTranslations[$method][$lang] ?? $method
                ];
            }),

            'remaining_credit' => $this->remaining_credit,
            'credit_balance' => $this->credit_balance,
            'rate' => $this->rate,
            'is_active' => $this->is_active,
            'payment_methods' => $this->paymentMethods->map(function ($method) use ($lang) {
                return [
                    'id' => $method->id,
                    'name' => $lang === 'ar' ? $method->name_ar : $method->name_en,
                ];
            }),

            'payment_types' => $this->paymentTypes->map(function ($type) use ($lang) {
                return [
                    'id' => $type->id,
                    'name' => $lang === 'ar' ? $type->name_ar : $type->name_en,
                ];
            }),

            'categories' => $this->categories->map(function ($cat) use ($lang) {
                return [
                    'id' => $cat->category_id,
                    'name' => $lang === 'ar' ? $cat->category->name_ar : $cat->category->name_en,
                    'sub_category_id' => $cat->sub_category_id,
                    'sub_category_name' => $cat->subCategory ? ($lang === 'ar' ? $cat->subCategory->name_ar : $cat->subCategory->name_en) : null
                ];
            }),

            'tax_card_number' => $this->info?->tax_card_number,
            'commercial_registration_number' => $this->info?->commercial_registration_number,
            'contact_name' => $this->info?->contact_name,
            'contact_country_code' => $this->info?->country_code,

            'contact_phone' => $this->info?->contact_phone,
            'contact_email' => $this->info?->contact_email,

            'created_at' => formatDateTime($this->created_at, $lang),
            'updated_at' => formatDateTime($this->updated_at, $lang),
        ];
    }
}
