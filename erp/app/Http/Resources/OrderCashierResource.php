<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderCashierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $maxDishTime = $this->orderDetailsWithoutCancel->max(fn($detail) => $detail->dish->time ?? 0);

        return [
            'Order' => [
                'order_details' => [
                    'order_id' => $this->id,
                    'order_number' => $this->order_number,
                    'status' => $this->status,
                    'table_number' => $this->Table->table_number ?? null,
                    'created_at' => $this->created_at->toIso8601String(),
                    'order_items_count' => $this->orderDetails->count(),
                    'order_type' => $this->type,
                ],
                'total_price' => $this->total_price_after_tax,
                'order_items' => $this->orderDetails->map(function ($item) {
                    return [
                        'item_id' => $item->id,
                        'dish_id' => $item->dish_id,
                        'dish_name' => $item->dish->name ?? null,
                        'quantity' => $item->quantity,
                        'dish_order' => $item->dish_order ?? null,
                        'total_dish_price' => $item->price_befor_tax,
                        'final_price' => $item->price_befor_tax,
                        'dish_status' => $item->status,
                        'image' => $item->dish->image ?? null,
                    ];
                })->toArray(),
                'invoice' => [
                    'invoice_number' => $this->invoice_number,
                    'invoice_print_status' => $this->print_status,
                    'order_time' => $maxDishTime,
                ],
                'currency_symbol' => $this->Branch->country->currency_symbol ?? null,
                'orderId' => $this->id,
                // 'date' => now()->toDateString()  // Output: "2025-07-29" (Y-m-d format)

            ]
        ];
    }
}
