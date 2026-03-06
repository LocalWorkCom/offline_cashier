<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderDashboardController extends Controller
{
    public function listOrders(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);

        $orders = Order::with(['branch', 'tracking', 'orderDetailsWithoutCancel.dish', 'orderDetailsWithoutCancel.dishAddons', 'table', 'orderTransactions', 'Client', 'latestTracking'])
            ->orderBy('updated_at', 'desc')->orderBy('created_at', 'desc');

        $response = paginateOrGetAll($orders, $request, ['']);

        if ($response['data']) {
            $response['data'] = collect($response['data'])->map(function ($order) {
                $currencySymbol = $order->branch?->country?->currency_symbol ?? 'ج.م';
                return [
                    'id' => $order->id,
                    'invoice_number' => $order->invoice_number,
                    'created_at' => $order->date,
                    'order_type' => __('order.' . strtolower($order->type)), //$order->type,
                    'order_type_en' => $order->type,
                    'branch_name' => $order->branch?->name ?? null,
                    'currency_symbol' => $currencySymbol,
                    // 'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : ($order->address ? $order->address->user_name : $order->client_name),
                    // 'client_phone' => $order->Client->flag != 'unknown' ? $order->Client->phone : ($order->address ? $order->address->address_phone : $order->client_phone),
                    'client_name' => $order->responsible_person,
                    'total_price' => $order->total_price_after_tax,
                    'order_status'    => $order->latestTracking
                        ? __('order.' . strtolower($order->latestTracking->order_status))
                        : ($order->status == 'packing'
                            ? __('order.readyForPickup')
                            : __('order.' . strtolower($order->status))),
                    'order_status_en' => $order->latestTracking
                        ? strtolower($order->latestTracking->order_status)
                        : ($order->status == 'packing'
                            ? 'readyForPickup'
                            : $order->status),
                    // 'tracking_status' => $order->tracking->last()->order_status ?? null,
                    'payment_status' => __('order.' . strtolower($order->orderTransactions->last()->payment_status)), //$order->orderTransactions->last()->payment_status ?? null,
                    // 'payment_method' => $order->orderTransactions->last()->payment_method ?? null
                ];
            })->values();
        }

        return ResponseWithSuccessDataPaginated($lang, $response, 1);
    }
}
