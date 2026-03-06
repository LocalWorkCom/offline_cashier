<?php

namespace App\Http\Controllers\Api\ClientAPIs;

use App\Http\Controllers\Controller;

use App\Models\ChatChannel;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Traits\ChatTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Events\OrderTransactionEvent;
use App\Models\Employee;
use App\Models\TableReservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class OrderTrackingController extends Controller
{
    use ChatTrait;

    /**
     * Display a listing of the resource.
     */
    public function ordersStatuses(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $user = Auth::user();

        $orders = Order::where('client_id', $user->id)
            ->with(['branch', 'tracking'])
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.',
                'errorData' => ['error' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.'],
                'data' => null
            ], 200);
        }

        $responseData = $orders->map(function ($order) use ($lang) {
            $orderStatus = [
                'order_id' => $order->id,
                'created_at' => $order->created_at,
                'status' => array_search($order->status, Order::$statusMap),
                'tracking_status' => array_search(
                    $order->tracking->last()->order_status,
                    OrderTracking::$statusMap
                ) ?? null,
            ];
            return $orderStatus;
        });
        $response = [
            'orderStatuses' => $responseData,
        ];
        return ResponseWithSuccessData($lang, $response, 1);
    }
    public function trackOrder(Request $request, $orderId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        // $user = Auth::user();

        $orders = Order::where('id', $orderId)
            ->whereIn('status', ['pending', 'inprogress', 'packing'])
            ->whereIn('type', ['Delivery', 'Takeaway'])
            ->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetailsWithoutCancel.dish', 'orderDetails.dishAddons'])
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.',
                'errorData' => ['error' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.'],
                'data' => null
            ], 200);
        }

        $responseData = $orders->map(function ($order) use ($lang) {
            $maxDishTime = $order->orderDetailsWithoutCancel->max(function ($detail) {
                return $detail->dish->time ?? 0;
            });
            $orderTracking = [
                'order_type' => $order->type,
                'order_id' => $order->id,
                'created_at' => $order->created_at,
                'status' => array_search($order->status, Order::$statusMap),
                'tracking_status' => array_search(
                    $order->tracking->last()->order_status,
                    OrderTracking::$statusMap
                ) ?? null,
                'delivery_time' => $order->Branch->delivery_time + $maxDishTime ?? null,
                'pickup_time' => $maxDishTime,
                'takeaway_time' => $order->takeaway_pickup_time ?? null,
            ];

            $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';

            $orderDetails = $order->orderDetails->map(function ($detail) use ($lang, $order) {
                $addons = $detail->dishAddons->filter(
                    fn($addon) => $addon->Addon?->addons?->{($lang === 'ar' ? 'name_ar' : 'name_en')}
                );
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
                return [
                    'dish_id' => $detail->dish_id,
                    'dish_name' => $detail->dish->name ?? null,
                    'size_id' => $detail->dish_size_id ?? null,
                    'size' => $detail->dish_size_id ?
                        (($lang === 'ar') ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
                        : null,
                    'quantity' => $detail->quantity,
                    'total_dish_price' => formatFloat($total),
                    'note' => $detail->note,
                    'addons' => $detail->dishAddons->map(function ($addon) use ($lang) {
                        return ($lang === 'ar') ? $addon->Addon->addons->name_ar ?? null : $addon->Addon->addons->name_en ?? null;
                        //'price' => $order->tax_application == 0
                        // ? $addon->price_before_tax + ($addon->service_fees ?? 0)
                        // : $addon->price_after_tax + ($addon->service_fees ?? 0)
                    }),
                ];
            });
            $subtotal = $order->total_price_befor_tax;
            if ($order->tax_application == 1) {
                $subtotal += $order->tax_value;
            }
            $orderSummary = [
                'order_number' => $order->order_number,
                'subtotal_price' => formatFloat($subtotal),
                'delivery_fees' => formatFloat($order->delivery_fees ?? null),
                'service_fees' => formatFloat($order->service_fees ?? null),
                'tax_value' => formatFloat($order->tax_value ?? null),
                'tax_apply' => !empty($order->tax_value) || $order->tax_value != 0,
                'tax_application' => $order->tax_application == 1 ? true : false,
                'tax_percentage' => formatFloat($order->tax_percentage ?? null),
                'coupon_id' => $order->coupon_id ?? null,
                'coupon_code' => $order->coupon?->code ?? null,
                'coupon_value' => formatFloat($order->coupon_value ?? null),
                'total_price' => formatFloat($order->total_price_after_tax),
            ];
            if ($order->tracking->last()->order_status == 'on_way') {

                $contact = $this->checkChatChannel($order);
            } else {
                $contact = [
                    'branch_id' => $order->branch->id ?? null,
                    'branch_name' => $order->branch->name ?? null,
                    'branch_phone' => $order->branch->phone ?? null,
                ];
            }

            return [
                'order_tracking' => $orderTracking,
                'order_details' => $orderDetails,
                'order_summary' => $orderSummary,
                'payment_method' => $order->orderTransactions->first()->payment_method,
                'currency_symbol' => $currencySymbol,
                'notes' => $order->note,
                'contact' => $contact,
            ];
        });
        $response = [
            'orderData' => $responseData,
        ];
        return ResponseWithSuccessData($lang, $response, 1);
    }
    public function listOrders(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $user = Auth::user();

        $orders = Order::where('client_id', $user->id)->where('branch_id', $request->branch_id)
            ->whereIn('type', ['Delivery', 'Takeaway'])
            ->orderBy('created_at', 'DESC')->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons'])
            ->get();

        $reservations = TableReservation::with([
            'client',
            'tables',
            'branch.country',
            'order.orderDetails.dish',
            'order.orderDetails.dishSize',
            'order.orderAddons.Addon.addons',
            'order.orderTransactions',
            'transaction',
            'floorPartition'
        ])
            ->where('client_id', $user->id)
            ->where('branch_id', $request->branch_id)
            ->orderBy('created_at', 'DESC')
            ->get();

        if ($orders->isEmpty() && $reservations->isEmpty()) {
            return response()->json([
                'status' => false,
                'code' => 200,
                'message' => $lang == 'en' ? 'No orders or reservations exist' : 'لا توجد طلبات أو حجوزات.',
                'data' => null
            ], 200);
        }

        $responseData = $orders->map(function ($order) use ($lang) {
            $orderTracking = [
                'order_id' => $order->id,
                'order_type' => $order->type,
                'order_number' => $order->order_number,
                'created_at' => $order->created_at,
                'status' => array_search($order->status, Order::$statusMap),
                'tracking_status' => array_search(
                    $order->tracking->last()->order_status,
                    OrderTracking::$statusMap
                ) ?? null,
            ];

            $orderDetails = $order->orderDetails->map(function ($detail) use ($lang, $order) {
                $addons = $detail->dishAddons->filter(
                    fn($addon) => $addon->Addon?->addons?->{($lang === 'ar' ? 'name_ar' : 'name_en')}
                );
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
                return [
                    'dish_id' => $detail->dish_id,
                    'dish_name' => $detail->dish->name ?? null,
                    'size_id' => $detail->dish_size_id ?? null,
                    'size' => $detail->dish_size_id ?
                        (($lang === 'ar') ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
                        : null,
                    'quantity' => $detail->quantity,
                    'total_dish_price' => formatFloat($total),
                    'note' => $detail->note,
                    'addons' => $detail->dishAddons->map(function ($addon) use ($lang) {
                        return [
                            'addon_category_id' => $addon->Addon->addon_category_id,
                            'addon_id' => $addon->Addon->addon_id,
                            'addon_name' => ($lang === 'ar') ? $addon->Addon->addons->name_ar ?? null : $addon->Addon->addons->name_en ?? null,
                        ];
                    }),
                ];
            });
            $orderSummary = [
                'total_price' => formatFloat($order->total_price_after_tax),
            ];
            $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';
            $orderItemsCount = $order->orderDetails->sum('quantity');
            return [
                'order_tracking' => $orderTracking,
                'order_items_count' => $orderItemsCount,
                'order_details' => $orderDetails,
                'order_summary' => $orderSummary,
                'currency_symbol' => $currencySymbol,
            ];
        });

        // Map Reservations
        $reservationsData = $reservations->map(function ($reservation) use ($lang) {
            $reservationDetails = [
                'reservation_id' => $reservation->id,
                'reservation_number' => $reservation->reservation_number,
                'created_at' => $reservation->created_at,
                'branch_id' => $reservation->branch_id,
                'branch_address' => app()->getLocale() == 'ar' ? $reservation->branch->address_ar : $reservation->branch->address_en,
                'status' => $reservation->status,
                'table_id' => $reservation->tables->id,
                'table_number' => $reservation->tables->table_number,
                'floor_partition_id' => $reservation->floor_partition_id ?? null,
                'floor_partition_name' => $lang === 'ar' ? $reservation->floorPartition?->name_ar : $reservation->floorPartition?->name_en,
                'reservation_type' => $reservation->reservation_type,
                'date' => $reservation->date ? $reservation->date->format('Y-m-d') : null,
                'time_from' => $reservation->time_from ? $reservation->time_from->format('h:i A') : null,
                'time_to' => $reservation->time_to ? $reservation->time_to->format('h:i A') : null,
                'adult' => $reservation->adult,
                'kids' => $reservation->kids,
                'personal_type' => $reservation->personal_type,
                'table_type' => $reservation->tables->type == 1 ? ($lang === 'ar' ? 'داخلي' : 'Inside') : ($lang === 'ar' ? 'خارجي' : 'Outside'),
            ];

            $transactionDetails = [];
            if ($reservation->reservation_type === 'without' && $reservation->transaction) {
                $transaction = $reservation->transaction;
                $transactionDetails = [[
                    'transaction_id' => $transaction->id,
                    'payment_status' => $transaction->payment_status,
                    'payment_method' => $transaction->payment_method,
                    'paid' => formatFloat($transaction->paid),
                    'created_at' => $transaction->created_at,
                ]];
            } elseif ($reservation->reservation_type === 'with' && $reservation->order && $reservation->order->orderTransactions) {
                $transaction = $reservation->order->transaction;
                $transactionDetails = [[
                    'transaction_id' => $transaction->id,
                    'payment_status' => $transaction->payment_status,
                    'payment_method' => $transaction->payment_method,
                    'paid' => formatFloat($transaction->paid),
                    'created_at' => $transaction->created_at,
                ]];
            }

            $orderDetails = null;
            $orderSummary = null;
            $orderItemsCount = 0;

            if ($reservation->reservation_type === 'with' && $reservation->order) {
                $orderDetails = $reservation->order->orderDetails->map(function ($detail) use ($lang, $reservation) {
                    $addons = $reservation->order->orderAddons->filter(
                        fn($addon) => $addon->order_details_id == $detail->id &&
                            $addon->Addon?->addons?->{($lang === 'ar' ? 'name_ar' : 'name_en')}
                    );

                    $total = $reservation->order->tax_application == 0
                        ? $detail->price_befor_tax + $addons->sum('price_before_tax')
                        : $detail->price_after_tax + $addons->sum('price_after_tax');

                    return [
                        'dish_id' => $detail->dish_id,
                        'dish_name' => $lang === 'ar' ? $detail->dish->name_ar ?? null : $detail->dish->name_en ?? null,
                        'size_id' => $detail->dish_size_id ?? null,
                        'size' => $detail->dish_size_id
                            ? ($lang === 'ar' ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
                            : null,
                        'quantity' => $detail->quantity,
                        'total_dish_price' => formatFloat($total),
                        'note' => $detail->note,
                        'addons' => $addons->map(function ($addon) use ($lang, $reservation) {
                            return [
                                'addon_category_id' => $addon->Addon->addon_category_id,
                                'addon_id' => $addon->Addon->addon_id,
                                'addon_name' => $lang === 'ar' ? $addon->Addon->addons->name_ar ?? null : $addon->Addon->addons->name_en ?? null,
                                'price' => formatFloat($reservation->order->tax_application == 0 ? $addon->price_before_tax : $addon->price_after_tax)
                            ];
                        })->values(),
                    ];
                });

                $orderSummary = [
                    'preparation_appointment' => app()->getLocale() === 'ar'
                        ? ($reservation->order->preparation_appointment === 'at' ? 'عند الوصول' : ($reservation->order->preparation_appointment === 'before' ? 'قبل الوصول' : null))
                        : $reservation->order->preparation_appointment ?? null,
                    'total_price' => formatFloat($reservation->order->total_price_after_tax),
                ];

                $orderItemsCount = $reservation->order->orderDetails->sum('quantity');
            }

            $currencySymbol = $reservation->branch?->country?->currency_symbol ?? 'ج.م';

            return [
                'reservation_details' => $reservationDetails,
                'transaction_details' => $transactionDetails,
                'order_items_count' => $orderItemsCount,
                'order_details' => $orderDetails,
                'order_summary' => $orderSummary,
                'currency_symbol' => $currencySymbol,
            ];
        });

        $response = [
            'orders' => $responseData,
            'reservations' => $reservationsData,
        ];
        return ResponseWithSuccessData($lang, $response, 1);
    }
    public function paymentDetails(Request $request, $orderId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $user = Auth::user();

        $order = Order::where('client_id', $user->id)
            ->where('id', $orderId)
            ->with([
                'branch',
                'tracking',
                'orderDetails.dish',
                'orderDetails.dishAddons',
                'orderTransactions',
                'orderDetailsWithoutCancel.dish'
            ])
            ->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.',
                'errorData' => ['error' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.'],
                'data' => null
            ], 200);
        }
        $maxDishTime = $order->orderDetailsWithoutCancel->max(function ($detail) {
            return $detail->dish->time ?? 0;
        });
        $orderTracking = [
            'order_id' => $order->id,
            'order_type' => $order->type,
            'order_number' => $order->order_number,
            'created_at' => $order->created_at,
            'status' => array_search($order->status, Order::$statusMap),
            'tracking_status' => array_search(
                $order->tracking->last()->order_status,
                OrderTracking::$statusMap
            ) ?? null,
            'delivery_time' => $order->Branch->delivery_time + $maxDishTime ?? null,
            'pickup_time' => $maxDishTime,
            'takeaway_time' => $order->takeaway_pickup_time ?? null,
        ];

        $orderDetails = $order->orderDetails->map(function ($detail) use ($lang, $order) {
            $addons = $detail->dishAddons->filter(
                fn($addon) => $addon->Addon?->addons?->{($lang === 'ar' ? 'name_ar' : 'name_en')}
            );
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
            return [
                'dish_id' => $detail->dish_id,
                'dish_name' => $detail->dish->name ?? null,
                'size_id' => $detail->dish_size_id ?? null,
                'size' => $detail->dish_size_id ?
                    (($lang === 'ar') ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
                    : null,
                'quantity' => $detail->quantity,
                'total_dish_price' => formatFloat($total),
                'note' => $detail->note,
                'addons' => $detail->dishAddons->map(function ($addon) use ($lang) {
                    return [
                        'addon_category_id' => $addon->Addon->addon_category_id,
                        'addon_id' => $addon->Addon->addon_id,
                        'addon_name' => ($lang === 'ar') ? $addon->Addon->addons->name_ar ?? null : $addon->Addon->addons->name_en ?? null,
                    ];
                }),
            ];
        });
        $subtotal = $order->total_price_befor_tax;
        if ($order->tax_application == 1) {
            $subtotal += $order->tax_value;
        }
        $orderSummary = [
            'order_number' => $order->order_number,
            'subtotal_price' => formatFloat($subtotal),
            'delivery_fees' => formatFloat($order->delivery_fees ?? null),
            'service_fees' => formatFloat($order->service_fees ?? null),
            'tax_value' => formatFloat($order->tax_value ?? null),
            'tax_apply' => !empty($order->tax_value) || $order->tax_value != 0,
            'tax_application' => $order->tax_application == 1 ? true : false,
            'tax_percentage' => formatFloat($order->tax_percentage ?? null),
            'coupon_id' => $order->coupon_id ?? null,
            'coupon_code' => $order->coupon?->code ?? null,
            'coupon_value' => formatFloat($order->coupon_value ?? null),
            'total_price' => formatFloat($order->total_price_after_tax),
        ];
        $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';

        $responseData = [
            'order_tracking' => $orderTracking,
            'order_details' => $orderDetails,
            'order_summary' => $orderSummary,
            'payment_method' => $order->orderTransactions->first()->payment_method,
            'currency_symbol' => $currencySymbol,
        ];

        return ResponseWithSuccessData($lang, $responseData, 1);
    }
}
