<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $module = $request->attributes->get('module');
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $type = $request->input('type') ?? 'basic';
        $store = $this->productStores()->first();


        if ($module === 'procurement') {
            switch ($type) {

                case 'basic':
                    return [
                        'id_auto' => $this->id_auto,
                        'product_name' => $lang === 'ar'
                            ? $this->product?->name
                            : $this->product?->name_en,
                        'brand_name' => $lang === 'ar'
                            ? $this->brand?->name_ar
                            : $this->brand?->name_en,
                        'barcode' => $this->openingBalance?->barcode,
                        'category' => [
                            'id' => $this->product->category?->id,
                            'name_ar' => $this->product->category?->name_ar,
                            'name_en' => $this->product->category?->name_en,
                            'name' => $lang === 'ar'
                                ? $this->product->category?->name_ar
                                : $this->product->category?->name_en,
                        ],
                        'sub_category' => $this->product->subCategory ? [
                            'id' => $this->product->subCategory->id,
                            'name_ar' => $this->product->subCategory->name_ar,
                            'name_en' => $this->product->subCategory->name_en,
                            'name' => $lang === 'ar'
                                ? $this->product->subCategory->name_ar
                                : $this->product->subCategory->name_en,
                        ] : null,
                        'id' => $this->id,
                        'name' => $lang === 'ar' ? $this->brand?->name_ar : $this->brand?->name_en,
                        'stock_market' => (bool) ($this->brand?->stock_market ?? false),
                        'avg_price' => $this->brand?->stock_market ? (float) ($this->brand?->avg_price ?? 0) : null,
                        'sku' => $this->brand?->sku,
                        'note' => $this->brand?->note,
                        'status' => $this->brand?->status ?? 'active',
                        'image' => $this->image,
                        'created_at' => formatDateTime($this->created_at, $this->lang)
                    ];

                /**
                 * PURCHASE — UNIT
                 */
                case 'unit':
                    return [
                        'id_auto' => $this->id_auto,
                        'product_name' => $lang === 'ar'
                            ? $this->product?->name
                            : $this->product?->name_en,
                        'barcode' => $this->openingBalance?->barcode,
                        'base_unit' => $lang === 'ar'
                            ? $this->baseUnit?->name_ar
                            : $this->baseUnit?->name_en,
                        'default_unit' => $lang === 'ar'
                            ? $this->defaultUnit?->name_ar
                            : $this->defaultUnit?->name_en,
                        'conversion_units' => $this->units->map(function ($unit) {
                            return [
                                'id' => $unit->id,
                                'first_unit_id' => $unit->first_unit_id,
                                'second_unit_id' => $unit->second_unit_id,
                                'factor' => $unit->factor,
                            ];
                        })->values(),
                        'id' => $this->id,

                        'name' => $lang === 'ar' ? $this->brand->name_ar : $this->brand->name_en,
                        'stock_market' => (bool) ($this->brand->stock_market ?? false),
                        'avg_price' => $this->brand->stock_market ? (float) ($this->brand->avg_price ?? 0) : null,
                        'sku' => $this->brand->sku ?? null,
                        'note' => $this->brand->note ?? null,
                        'status' => $this->brand->status ?? 'active',
                        'created_at' => formatDateTime($this->created_at, $this->lang)

                    ];

                /**
                 * PURCHASE — EXPIRATION
                 */
                case 'expiration':
                    return [
                        'id_auto' => $this->id_auto,
                        'product_name' => $lang === 'ar'
                            ? $this->product?->name
                            : $this->product?->name_en,
                        'is_have_expired' => (bool)$this->is_have_expired,
                        'validity_period' => $this->validity_period,
                        'production_date' => $this->openingBalance?->production_date ?? $this->created_at,
                        'expiration_date' => $this->openingBalance?->expiration_date,
                        'id' => $this->id,
                        'name' => $lang === 'ar' ? $this->brand->name_ar : $this->brand->name_en,
                        'stock_market' => (bool) ($this->brand->stock_market ?? false),
                        'avg_price' => $this->brand->stock_market ? (float) ($this->brand->avg_price ?? 0) : null,
                        'sku' => $this->brand->sku ?? null,
                        'note' => $this->brand->note ?? null,
                        'status' => $this->brand->status ?? 'active',
                        'created_at' => formatDateTime($this->created_at, $this->lang)

                    ];

                /**
                 * PURCHASE — STORAGE
                 */

                case 'storage':
                    $store = $this->productStores()->first();
                    if (!$this->openingBalance) {
                        $current_quantity = 0;
                    } else {
                        $current_quantity = getProductQuantity(
                            $this->id,
                            'base',
                            $this->openingBalance?->barcode,
                        );
                    }
                    return [
                        'id_auto' => $this->id_auto,
                        'product_name' => $lang === 'ar'
                            ? $this->product?->name
                            : $this->product?->name_en,
                        'current_quantity' => $current_quantity,
                        'min_limit' => $store?->min_limit,
                        'max_limit' => $store?->max_limit,
                        'id' => $this->id,
                        'name' => $lang === 'ar' ? $this->brand->name_ar : $this->brand->name_en,
                        'stock_market' => (bool) ($this->brand->stock_market ?? false),
                        'avg_price' => $this->brand->stock_market ? (float) ($this->brand->avg_price ?? 0) : null,
                        'sku' => $this->brand->sku ?? null,
                        'validity_period' => $this->validity_period,
                        'note' => $this->brand->note ?? null,
                        'status' => $this->brand->status ?? 'active',
                        'created_at' => formatDateTime($this->created_at, $this->lang)

                    ];
            }
        }

        switch ($type) {
            case 'unit':
                return [
                    'product_brand_id' => $this->id,
                    'product_id' => $this->product_id,
                    'product_name' => $lang === 'ar'
                        ? $this->product?->name
                        : $this->product?->name_en,
                    'barcode' => $this->openingBalance?->barcode,
                    'base_unit_id' => $this->base_unit_id,
                    'default_unit_id' => $this->default_unit_id,
                    'base_unit' => $lang === 'ar'
                        ? $this->baseUnit?->name_ar
                        : $this->baseUnit?->name_en,
                    'default_unit' => $lang === 'ar'
                        ? $this->defaultUnit?->name_ar
                        : $this->defaultUnit?->name_en,
                    'conversion_units' => $this->units->map(function ($unit) {
                        return [
                            'id' => $unit->id,
                            'first_unit_id' => $unit->first_unit_id,
                            'second_unit_id' => $unit->second_unit_id,
                            'factor' => $unit->factor,
                        ];
                    })->values(),
                ];

            case 'expiration':
                return [
                    'product_brand_id' => $this->id,
                    'product_id' => $this->product_id,
                    'product_name' => $lang === 'ar'
                        ? $this->product?->name
                        : $this->product?->name_en,
                    'barcode' => $this->openingBalance?->barcode,
                    'is_have_expired' => (bool)$this->is_have_expired,
                    'is_reusable' => (bool)$this->is_reusable,
                    'production_date' => $this->openingBalance?->production_date,
                    'expiration_date' => $this->openingBalance?->expiration_date,
                    'storage_location_id' => $store?->storage_location_id,
                    'storage_location' => optional($store?->storageLocation)->name,
                    'shelve_id' => $store?->shelve_id,
                    'zone_name' => optional($store?->zone)->name,
                    'zone_id' => $store?->zone_id,
                    'shelve_name' => optional($store?->shelve)->identifier,
                    'min_limit' => $this->productStores()->first()?->min_limit,
                    'max_limit' => $this->productStores()->first()?->max_limit,
                ];

            case 'storage':
                return [
                    'product_brand_id' => $this->id,
                    'product_id' => $this->product_id,
                    'barcode' => $this->openingBalance?->barcode,
                    'product_name' => $lang === 'ar'
                        ? $this->product?->name
                        : $this->product?->name_en,
                    'storage_location_id' => $store?->storage_location_id,
                    'storage_location_name' => optional($store?->storageLocation)->name,
                    'max_limit' => $store?->max_limit,
                    'min_limit' => $store?->min_limit,
                    'shelve_name' => optional($store?->shelve)->name,
                    'zone_name' => optional($store?->zone)->name,
                ];

            default:
                $store = $this->productStores()->first();
                return [
                    'product_brand_id' => $this->id,
                    'brand_id' => $this->brand_id,
                    'product_id' => $this->product_id,
                    'category_id' => $this->product?->category_id,
                    'storage_location_id' => $store?->storage_location_id,
                    'storage_location' => optional($store?->storageLocation)->name,
                    'shelve_id' => $store?->shelve_id,
                    'shelve_name' => optional($store?->shelve)->identifier,
                    'product_name' => $lang === 'ar'
                        ? $this->product?->name
                        : $this->product?->name_en,
                    'brand_name' => $lang === 'ar'
                        ? $this->brand?->name_ar
                        : $this->brand?->name_en,
                    'barcode' => $this->openingBalance?->barcode,
                    'brand_image' => $this->image,
                    'category_name' => $this->product?->category
                        ? ($lang === 'ar'
                            ? $this->product->category->name_ar
                            : $this->product->category->name_en)
                        : null,
                ];
        }
    }
}
