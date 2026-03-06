<?php

namespace App\Services\ProcurementServices;

use App\Models\Country;
use App\Models\HighValueRule;
use App\Models\PaymentType;
use App\Models\Vendor;
use App\Models\PaymentInterval;
use App\Models\PricingDeal;
use App\Models\PricingDealItem;
use App\Models\PricingDealShipment;
use App\Models\PurchasingBudget;
use App\Models\PurchasingBudgetLog;
use App\Models\VendorCategory;
use App\Models\VendorInfo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PricingDealService
{
    public function index(Request $request)
    {
        $query = PricingDeal::with([
            'vendor',
            'items.product',
            'items.brand',
            'items.category',
            'items.unit',
            'shipment.vendor'
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->boolean('not_expired')) {
            $query->whereDate('expiration_date', '>=', now());
        }

        if ($request->filled('vendor_id')) {
            $query->whereHas('shipment', function ($q) use ($request) {
                $q->where('vendor_id', $request->vendor_id);
            });
        }

        if ($request->filled('product_id')) {
            $productIds = is_array($request->product_id)
                ? $request->product_id
                : [$request->product_id];

            $query->whereHas('items', function ($q) use ($productIds) {
                $q->whereIn('product_id', $productIds);
            });
        }

        return $query->orderByDesc('created_at')
            ->orderByDesc('updated_at');
    }
    public function show(Request $request, $id)
    {
        $deal = PricingDeal::with([
            'vendor',
            'items.product',
            'items.brand',
            'items.category',
            'items.unit',
            'shipment.vendor'
        ])->findOrFail($id);
        return $deal;
    }
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $total_before_negotiated = 0;

            $deal = PricingDeal::create([
                'vendor_id' => $request->vendor_id,
                'deal_type' => $request->deal_type,
                'file' => 'test',
                'start_date' => $request->start,
                'end_date' => $request->end,
                'period' => $request->period,
                'total_before_negotiated' => 0,
                'total_after_negotiated' => $request->total_after_negotiated ?? 0,
                'delivery_date' => $request->delivery_date,
                'payment_terms' => $request->payment_terms,
                'penalty_clause' => $request->penalty_clause,
                'discount_by_quantity' => $request->discount_by_quantity,
                'terms_conditions' => $request->terms_conditions,
                'notes' => $request->notes,
            ]);

            if ($request->hasFile('file')) {
                UploadFile('images/pricingdeals', 'file', $deal, $request->file('file'));
            }

            foreach ($request->items as $item) {
                $quantity = $item['quantity'] ?? 1;
                $unit_price = $item['unit_price'] ?? 0;
                $taxPercent = $item['tax'] ?? 0;
                $discountPercent = $item['discount'] ?? 0;

                $totalPrice = $unit_price * $quantity;

                $discountAmount = ($discountPercent / 100) * $totalPrice;
                $taxAmount = ($taxPercent / 100) * ($totalPrice - $discountAmount);

                $net_amount = ($totalPrice + $taxAmount) - $discountAmount;

                // Save the item
                PricingDealItem::create([
                    'pricing_deal_id' => $deal->id,
                    'product_id' => $item['product_id'] ?? null,
                    'brand_id' => $item['brand_id'] ?? null,
                    'category_id' => $item['category_id'] ?? null,
                    'unit_id' => $item['unit_id'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unit_price,
                    'discount' => $discountPercent,
                    'tax' => $taxPercent,
                    'net_amount' => $net_amount,
                    'notes' => $item['notes'] ?? null,
                ]);
            }
            // Update deal total_before_negotiated
            $deal->update(['total_before_negotiated' => $total_before_negotiated]);

            if ($request->has('shipment')) {
                $shipment = PricingDealShipment::create([
                    'pricing_deal_id' => $deal->id,
                    'vendor_id' => $request->shipment['vendor_id'] ?? null,
                    'from_address' => $request->shipment['from_address'] ?? null,
                    'to_address' => $request->shipment['to_address'] ?? null,
                    'price' => $request->shipment['price'] ?? null,
                    'pricing_basis' => $request->shipment['pricing_basis'] ?? null,
                    'notes' => $request->shipment['notes'] ?? null,
                    'type_shipment' => $request->shipment['type'] ?? null,
                    'file' => 'test',
                ]);

                if (isset($request->shipment['file']) && $request->shipment['file'] instanceof \Illuminate\Http\UploadedFile) {
                    UploadFile('images/pricingdeals/shipment', 'file', $shipment, $request->shipment['file']);
                }
            }

            DB::commit();

            return  $deal;
        } catch (\Exception $e) {
            DB::rollBack();
            return ['error' => $e->getMessage()];
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $deal = PricingDeal::findOrFail($id);

            $total_before_negotiated = 0;

            // Update main deal
            $deal->update([
                'vendor_id' => $request->vendor_id,
                'deal_type' => $request->deal_type,
                'start_date' => $request->start,
                'end_date' => $request->end,
                'period' => $request->period,
                'total_before_negotiated' => 0, // will be updated later
                'total_after_negotiated' => $request->total_after_negotiated,
                'delivery_date' => $request->delivery_date,
                'payment_terms' => $request->payment_terms,
                'penalty_clause' => $request->penalty_clause,
                'discount_by_quantity' => $request->discount_by_quantity,
                'terms_conditions' => $request->terms_conditions,
                'notes' => $request->notes,
            ]);

            // Update main deal file
            if ($request->hasFile('file')) {
                UploadFile('images/pricingdeals', 'file', $deal, $request->file('file'));
            }

            $existingItemIds = [];

            foreach ($request->items as $item) {
                $quantity = $item['quantity'] ?? 1;
                $unit_price = $item['unit_price'] ?? 0;
                $taxPercent = $item['tax'] ?? 0;
                $discountPercent = $item['discount'] ?? 0;

                $totalPrice = $unit_price * $quantity;

                $discountAmount = ($discountPercent / 100) * $totalPrice;
                $taxAmount = ($taxPercent / 100) * ($totalPrice - $discountAmount);

                $net_amount = ($totalPrice + $taxAmount) - $discountAmount;
                $total_before_negotiated += $net_amount;

                if (isset($item['id'])) {
                    // Update existing item
                    $dealItem = PricingDealItem::find($item['id']);
                    if ($dealItem) {
                        $dealItem->update([
                            'product_id' => $item['product_id'] ?? $dealItem->product_id,
                            'brand_id' => $item['brand_id'] ?? $dealItem->brand_id,
                            'category_id' => $item['category_id'] ?? $dealItem->category_id,
                            'unit_id' => $item['unit_id'] ?? $dealItem->unit_id,
                            'quantity' => $quantity,
                            'unit_price' => $unit_price,
                            'discount' => $discountPercent,
                            'tax' => $taxPercent,
                            'net_amount' => $net_amount,
                            'notes' => $item['notes'] ?? $dealItem->notes,
                        ]);
                        $existingItemIds[] = $dealItem->id;
                    }
                } else {
                    // Create new item
                    $newItem = PricingDealItem::create([
                        'pricing_deal_id' => $deal->id,
                        'product_id' => $item['product_id'] ?? null,
                        'brand_id' => $item['brand_id'] ?? null,
                        'category_id' => $item['category_id'] ?? null,
                        'unit_id' => $item['unit_id'] ?? null,
                        'quantity' => $quantity,
                        'unit_price' => $unit_price,
                        'discount' => $discountPercent,
                        'tax' => $taxPercent,
                        'net_amount' => $net_amount,
                        'notes' => $item['notes'] ?? null,
                    ]);
                    $existingItemIds[] = $newItem->id;
                }
            }

            PricingDealItem::where('pricing_deal_id', $deal->id)
                ->whereNotIn('id', $existingItemIds)
                ->delete();

            $deal->update(['total_before_negotiated' => $total_before_negotiated]);

            if ($request->has('shipment')) {
                $shipment = $deal->shipment;

                if ($shipment) {
                    $shipment->update([
                        'vendor_id' => $request->shipment['vendor_id'] ?? $shipment->vendor_id,
                        'from_address' => $request->shipment['from_address'] ?? $shipment->from_address,
                        'to_address' => $request->shipment['to_address'] ?? $shipment->to_address,
                        'price' => $request->shipment['price'] ?? $shipment->price,
                        'type_shipment' => $request->shipment['type'] ?? $shipment->type_shipment,
                        'pricing_basis' => $request->shipment['pricing_basis'] ?? $shipment->pricing_basis,
                        'notes' => $request->shipment['notes'] ?? $shipment->notes,
                    ]);

                    if ($request->hasFile('shipment.file')) {
                        UploadFile('images/pricingdeals/shipment', 'file', $shipment, $request->file('shipment.file'));
                    }
                } else {
                    $shipment = PricingDealShipment::create([
                        'pricing_deal_id' => $deal->id,
                        'vendor_id' => $request->shipment['vendor_id'] ?? null,
                        'from_address' => $request->shipment['from_address'] ?? null,
                        'to_address' => $request->shipment['to_address'] ?? null,
                        'price' => $request->shipment['price'] ?? null,
                        'pricing_basis' => $request->shipment['pricing_basis'] ?? null,
                        'type_shipment' => $request->shipment['type'] ?? null,
                        'notes' => $request->shipment['notes'] ?? null,
                        'file' => null,
                    ]);

                    if ($request->hasFile('shipment.file')) {
                        UploadFile('images/pricingdeals/shipment', 'file', $shipment, $request->file('shipment.file'));
                    }
                }
            }

            DB::commit();
            return $deal;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
