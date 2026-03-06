<?php

namespace App\Http\Resources\purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingDealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'ar');
        $basic = $request->query('basic', false); // e.g., ?basic=1

        if ($basic) {
            return [
                'id' => $this->id,
                'vendor' => $this->vendor?->name,
                'updated_at' => formatDateTime($this->updated_at, $lang),
                'period' => $this->period,
                'total_after_negotiated' => $this->total_after_negotiated ?? 0,
                'shipment_note' => $this->shipment?->notes,
            ];
        }
        // Translation arrays
        $statusTranslations = [
            'available' => ['ar' => 'متاح', 'en' => 'Available'],
            'expired'   => ['ar' => 'منتهي', 'en' => 'Expired'],
        ];

        $dealTypeTranslations = [
            'one_time' => ['ar' => 'مرة واحدة', 'en' => 'One Time'],
            'contract' => ['ar' => 'عقد', 'en' => 'Contract'],
        ];

        return [
            'id'     => $this->id,
            'vendor_name'=>$this->vendor?->name,
            'vendor' => new VendorBasicResource($this->whenLoaded('vendor')),
            'deal_type' => [
                'key'   => $this->deal_type,
                'value' => $dealTypeTranslations[$this->deal_type][$lang] ?? $this->deal_type,
            ],
            'file'       => $this->file,
            'start_date' => $this->start_date,
            'end_date'   => $this->end_date,
            'period'     => $this->period,
            'total_before_negotiated' => $this->total_before_negotiated ?? 0,
            'total_after_negotiated'  => $this->total_after_negotiated ?? 0,
            'delivery_date'           => $this->delivery_date,
            'payment_terms'           => $this->payment_terms,
            'penalty_clause'          => $this->penalty_clause,
            'discount_by_quantity'    => $this->discount_by_quantity,
            'terms_conditions'        => $this->terms_conditions,
            'notes'                   => $this->notes,
            'status' => [
                'key'   => $this->status ? 'available' : 'expired',
                'value' => $statusTranslations[$this->status ? 'available' : 'expired'][$lang] ?? ($this->status ? 'available' : 'expired'),
            ],
            'is_active' => $this->is_active,
            'items_count' => $this->items?->count() ?? 0,
            'items' => PricingDealItemResource::collection($this->whenLoaded('items')),
            'shipment' => new PricingDealShipmentResource($this->whenLoaded('shipment')),
            'created_at' => formatDateTime($this->created_at, $lang),
            'updated_at' => formatDateTime($this->updated_at, $lang),
        ];
    }
}
