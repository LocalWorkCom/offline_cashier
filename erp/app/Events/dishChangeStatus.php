<?php

namespace App\Events;

use App\Models\Dish;
use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class dishChangeStatus implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        // $this->recipients = $recipients;
        $this->data = $data;
    }

    public function broadcastOn(): array
    {
        $channels = [];

        // Dynamic channels for each dish
        if (is_array($this->data['dish_ids'])) {
            foreach ($this->data['dish_ids'] as $dishId) {
                Log::info("Broadcasting dish ID: {$dishId} for order {$this->data['order_id']}");
                $channels[] = new Channel("dish-{$dishId}-order-{$this->data['order_id']}");
            }
        } else {
            $channels[] = new Channel("dish-{$this->data['dish_ids']}-order-{$this->data['order_id']}");
        }


        // Add the public/global channel
        $channels[] = new Channel("dish-order-statuses-changed");
        Log::info("Broadcasting to public channel for order {$this->data['order_id']} that data is " . json_encode($this->data));

        Log::info("Broadcasting to channels: " . implode(', ', array_map(function ($channel) {
            return $channel->name;
        }, $channels)));
        log::info("s edit: " . json_encode($this->data));
        return $channels;
    }


    public function broadcastAs()
    {
        return 'Dish-status';
    }
    public function broadcastWith(): array
    {
        $order = Order::whereHas('orderDetails')
            ->with([
                'tracking',
                'table',
                'orderTransactions',
                'Client',
                'address',
                'delivery',
                'Branch.country',
                'orderDetails',
                'orderDetails.dish',
                'orderDetails.dishSize',
                'orderDetails.coupon',
                'orderDetails.dishAddons',
                'orderDetails.dishAddons.Addon.addons',
                'orderDetails.dish.dishAddonsDetails',
                'orderDetails.dish.dishAddonsDetails.addons'
            ])
            ->withSum('orderDetailsWithoutCancel', 'quantity')
            ->withSum('orderDetails', 'quantity')
            ->find($this->data['order_id']);

        if ($order) {
            $orderItemsCount = $order->status === 'cancelled'
                ? ($order->order_details_sum_quantity ?? 0)
                : ($order->order_details_without_cancel_sum_quantity ?? 0);
            $maxDishTime = $order->orderDetails->max('preparation_time');
            $lang = app()->setLocale('ar');


            $orderData = [
                'order_type' => $order->type,
                'hasCoupon' => $order->coupon_id ? true : false,
                'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : ($order->address ? $order->address->user_name : $order->client_name),
                'client_phone' => $order->Client->flag != 'unknown' ? $order->Client->phone : ($order->address ? $order->address->address_phone : $order->client_phone),
                'status' => $order->tracking->last()->order_status ?? null,
                'payment_status' => $order->orderTransactions->last()->payment_status ?? null,
                'order_id' => $order->id,
                'created_at' => $order->created_at,
                'time' => $order->time,
                'date' => $order->date,
                'order_number' => $order->order_number,
                'order_items_count' => $orderItemsCount,
            ];

            $orderItems = [];
            $orderItems = $order->orderDetails->map(function ($detail) use ($lang, $order) {
                // Include cancelled addons if order is cancelled or if the dish itself is cancelled
                $includeCancelledAddons = $order->status === 'cancelled' || $detail->status === 'cancel';

                $preparation_time = Dish::where('id', $detail->dish_id)->value('time') ?? 0;

                $addons = $detail->dishAddons
                    ->filter(function ($addon) use ($lang, $includeCancelledAddons) {
                        $hasValidName = $addon->Addon?->addons?->{($lang === 'ar' ? 'name_ar' : 'name_en')} ?? false;

                        if ($includeCancelledAddons) {
                            return $hasValidName;
                        } else {
                            return $addon->status !== 'cancel' && $hasValidName;
                        }
                    });

                if ($order->tax_application == 0) {
                    $orderDetailTotal = $detail->price_befor_tax;
                    $addonsTotal = $addons->sum(function ($addon) {
                        return $addon->price_before_tax;
                    });
                } else {
                    $orderDetailTotal = $detail->price_after_tax;
                    $addonsTotal = $addons->sum(function ($addon) {
                        return $addon->price_after_tax;
                    });
                }
                $total = $orderDetailTotal + $addonsTotal;

                if ($order->tax_application == 0) {
                    $orderDetailTotal = $detail->price_before_coupon;
                    $addonsTotal = $addons->sum(function ($addon) {
                        return $addon->price_before_coupon;
                    });
                } else {
                    $orderDetailTotal = $detail->price_after_tax;
                    $addonsTotal = $addons->sum(function ($addon) {
                        return $addon->price_after_tax;
                    });
                }
                $totalBeforeCoupon = $orderDetailTotal + $addonsTotal;

                // Check if dish has coupon_id - only apply coupon if it exists
                $dishCouponId = $detail->coupon_id ?? null;
                $dishCouponValue = $dishCouponId ? ($detail->coupon_value ?? 0) : 0;

                return [
                    'order_detail_id' => $detail->id,
                    'dish_name' => $detail->dish->name ?? null,
                    'dish_name_ar' => $detail->dish->name_ar ?? null,
                    'dish_name_en' => $detail->dish->name_en ?? null,
                    'dish_order' => $detail->dish_order ?? null,
                    'dish_status' => $detail->status ?? null,
                    'dish_time' => $preparation_time,
                    'note'=>$detail->note ?? null,
                    'size' => $detail->dish_size_id ?
                        (($lang === 'ar') ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
                        : null,
                    'size_name_ar' => $detail->dish_size_id ? $detail->dishSize->size_name_ar ?? null : null,
                    'size_name_en' => $detail->dish_size_id ? $detail->dishSize->size_name_en ?? null : null,
                    'addons' => $detail->dishAddons->map(function ($addon) use ($lang) {
                        return [
                            'addon_category_id' => $addon->Addon?->addon_category_id,
                            'addon_id' => $addon->Addon?->addon_id,
                            'addon_name' => $addon->Addon?->addons?->name ?? null,
                            'addon_name_ar' => $addon->Addon?->addons?->name_ar ?? null,
                            'addon_name_en' => $addon->Addon?->addons?->name_en ?? null,
                            'addon_status' => $addon->status,
                        ];
                    }),
                    'dish_addons' => $detail->dish->dishAddonsDetails->map(function ($addon) use ($lang) {
                        return [
                            'addon_category_id' => $addon->addon_category_id,
                            'addon_id' => $addon->addon_id,
                            'addon_name' =>  $addon->addons?->name ?? null,
                            'addon_name_ar' => $addon->addons?->name_ar ?? null,
                            'addon_name_en' => $addon->addons?->name_en ?? null,
                        ];
                    }),
                    'quantity' => $detail->quantity,
                    'total_dish_price' => formatFloat($totalBeforeCoupon),
                    'total_dish_price_coupon_applied' => formatFloat($total),
                    'coupon_value' => formatFloat($dishCouponValue),
                    'coupon_title' => $dishCouponId ? $detail->coupon?->title : null
                ];
            });
            if ($order->type == 'dine-in') {
                $orderData['table_number'] = $order->table->table_number ?? null;
            }
            if ($order->type == 'Delivery') {
                $orderData['delivery_id'] = $order->delivery?->id ?? null;
                $orderData['delivery_name'] = $order->delivery?->first_name . ' ' . $order->delivery?->last_name ?? null;
            }
            $totalPrice = $order->total_price_after_tax;
            $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';
            return [
                'order_id' => $order->id,
                'status' => $this->data['status'],
                'order_details' => $orderData,
                'order_items'   => $orderItems,
                'total_price'   => formatFloat($totalPrice),
                'currency_symbol' => $currencySymbol ?? 'ج.م',
            ];
        }
        return [
            'order_details' => null,
            'order_items'   => null,
            'total_price'   => 0,
            'currency_symbol' => 'ج.م',
        ];
    }
}
