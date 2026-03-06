<?php

namespace App\Http\Resources;

use App\Models\BranchMenu;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    protected string $type;
    protected string $lang;
    protected int $maxDishTime;

    public function __construct($resource, string $type = 'simple', string $lang = 'ar', int $maxDishTime = 0)
    {
        parent::__construct($resource);
        $this->type = $type;
        $this->lang = $lang;
        $this->maxDishTime = $maxDishTime;
    }

    public function toArray($request)
    {
        $order = $this->resource;

        $base = [
            'Order' => [
                'order_details' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $this->type === 'cashier' ? $order->tracking->last()->order_status : $order->status,
                    'table_number' => $order->Table->table_number ?? null,
                    'created_at' => $order->created_at->toIso8601String(),
                    'order_items_count' => $order->orderDetails->count(),
                    'order_type' => $order->type,
                    'payment_status' => $order->orderTransactions->last()->payment_status ?? null,
                ],
                'total_price' => $order->total_price_after_tax,
                'invoice' => [
                    'invoice_number' => $order->invoice_number,
                    'invoice_print_status' => $order->print_status,
                    'order_time' => $this->maxDishTime,
                ],
                'currency_symbol' => $order->Branch->country->currency_symbol ?? null,
                'orderId' => $order->id,
            ]
        ];

        // Order items
        $base['Order']['order_items'] = $order->orderDetails->map(function ($item) use ($order) {
            return $this->mapOrderItem($order, $item);
        })->toArray();

        return $base;
    }

    /**
     * Map each order item depending on type.
     */
    private function mapOrderItem($order, $item): array
    {
        $includeCancelledAddons = $order->status === 'cancelled' || $item->status === 'cancel';

        // Filter addons
        $addons = $item->dishAddons->filter(function ($addon) use ($includeCancelledAddons) {
            $hasValidName = $addon->Addon?->addons?->{($this->lang === 'ar' ? 'name_ar' : 'name_en')} ?? false;

            return $includeCancelledAddons ? $hasValidName : ($addon->status !== 'cancel' && $hasValidName);
        });
        $preparation_time = Dish::where('id', $item->dish_id)->value('time') ?? 0;

        // Calculate totals (before/after coupon + tax)
        [$totalBeforeCoupon, $totalAfterCoupon, $couponId, $couponValue] = $this->calculateTotals($order, $item, $addons);

        // === Waiter type (full details) ===
        if ($this->type === 'waiter') {
            $branch_menu = BranchMenu::where('branch_id', $order->branch_id)
                ->where('dish_id', $item->dish_id)
                ->with('branchMenuAddons', 'branchMenuSizes')
                ->first();

            return [
                'item_id' => $item->id,
                'dish_id' => $item->dish_id,
                'dish_menu_id' => $branch_menu->id ?? null,
                'dish_name' => $item->dish->name ?? null,
                'dish_name_ar' => $item->dish->name_ar,
                'dish_name_en' => $item->dish->name_en,
                'quantity' => $item->quantity,
                'dish_order' => $item->dish_order ?? null,
                'size_id' => $item->dish_size_id ?? null,
                'size_menu_id' => $branch_menu?->branchMenuSizes()
                    ->where('branch_id', $order->branch_id)
                    ->where('dish_size_id', $item->dish_size_id)
                    ->value('id') ?? null,
                'size' => $item->dish_size_id
                    ? (($this->lang === 'ar') ? $item->dishSize->size_name_ar ?? null : $item->dishSize->size_name_en ?? null)
                    : "",
                'size_name_ar' => $item->dish_size_id ? $item->dishSize->size_name_ar : null,
                'size_name_en' => $item->dish_size_id ? $item->dishSize->size_name_en : null,
                'note' => $item->note ?? "",
                'addons' => $addons->map(function ($addon) use ($order, $branch_menu) {
                    return [
                        'addon_category_id' => $addon->Addon?->addon_category_id,
                        'addon_id' => $addon->Addon?->addon_id,
                        'addon_menu_id' => $branch_menu?->branchMenuAddons()
                            ->where('branch_id', $order->branch_id)
                            ->where('dish_addon_id', $addon->Addon?->id)
                            ->value('id'),
                        'addon_name' => ($this->lang === 'ar')
                            ? ($addon->Addon->addons->name_ar ?? null)
                            : ($addon->Addon->addons->name_en ?? null),
                        'addon_name_ar' => $addon->Addon?->addons?->name_ar ?? null,
                        'addon_name_en' => $addon->Addon?->addons?->name_en ?? null,
                        'addon_status' => $addon->status,
                    ];
                }),
                'total_dish_price' => formatFloat($totalBeforeCoupon),
                'final_price' => $item->price_after_tax,
                'total_dish_price_coupon_applied' => formatFloat($totalAfterCoupon),
                'dish_status' => $item->status,
                'image' => $item->dish->image ?? null,
                'coupon_value' => formatFloat($couponValue),
                'coupon_title' => $couponId ? $item->coupon?->title : null
            ];
        }

        // === Simple type (less details) ===
        return [
            'item_id' => $item->id,
            'dish_id' => $item->dish_id,
            "dish_menu_id" => $item->branch_menu_id,
            'dish_name' => $item->dish->name ?? null,
            'dish_name_ar' => $item->dish->name_ar,
            'dish_name_en' => $item->dish->name_en,
            'quantity' => $item->quantity,
            'dish_order' => $item->dish_order ?? null,
            'total_dish_price' => formatFloat($totalBeforeCoupon),
            'final_price' => $item->price_after_tax,
            'dish_status' => $item->status,
            'dish_time' => $preparation_time,
            'image' => $item->dish->image ?? null,
            'total_dish_price_coupon_applied' => formatFloat($totalAfterCoupon),
            'coupon_value' => formatFloat($couponValue),
            'coupon_title' => $couponId ? $item->coupon?->title : null
        ];
    }

    /**
     * Handle all total calculations in one place.
     */
    private function calculateTotals($order, $item, $addons): array
    {
        if ($order->tax_application == 0) {
            $orderDetailTotal = $item->price_befor_tax;
            $addonsTotal = $addons->sum(fn($addon) => $addon->price_before_tax);
        } else {
            $orderDetailTotal = $item->price_after_tax;
            $addonsTotal = $addons->sum(fn($addon) => $addon->price_after_tax);
        }
        $totalAfterCoupon = $orderDetailTotal + $addonsTotal;

        if ($order->tax_application == 0) {
            $orderDetailTotal = $item->price_before_coupon;
            $addonsTotal = $addons->sum(fn($addon) => $addon->price_before_coupon);
        } else {
            $orderDetailTotal = $item->price_after_tax;
            $addonsTotal = $addons->sum(fn($addon) => $addon->price_after_tax);
        }
        $totalBeforeCoupon = $orderDetailTotal + $addonsTotal;

        $dishCouponId = $item->coupon_id ?? null;
        $dishCouponValue = $dishCouponId ? ($item->coupon_value ?? 0) : 0;

        return [$totalBeforeCoupon, $totalAfterCoupon, $dishCouponId, $dishCouponValue];
    }
}
