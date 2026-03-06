<?php

namespace App\Http\Controllers\Api\DeliveryAPIs;

use App\Events\orderChangeStatus;
use App\Http\Controllers\Controller;
use App\Models\ChatChannel;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Traits\ChatTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon as SupportCarbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Pusher\Pusher;

class DeliveryOrderController extends Controller
{
    use ChatTrait;

    public function orderLocation(Request $request, $orderId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithUnauthorizedRequest($lang, 4);
        }

        $orders = Order::where('branch_id', $employee->branch_id)->where('status', 'packing')
            ->where('delivery_id', $employee->id)
            ->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons', 'address'])->where('id', $orderId)
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
            return [
                'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : $order->address->user_name,
                'order_number' => $order->order_number,
                'created_at' => $order->created_at,
                'client_address' => $order->address->address,
                'address_latitude' => $order->address->latitude,
                'address_longitude' => $order->address->longtitude,
                'client_phone' => $order->Client->flag != 'unknown' ? $order->Client->phone : $order->address->address_phone,
                'address_phone' => $order->address->address_phone,
                'address_notes' => $order->address->notes,
                'tracking_status' => array_search(
                    $order->tracking->last()->order_status,
                    OrderTracking::$statusMap
                ),
            ];
        });
        $response = [
            'orderData' => $responseData,
        ];
        return ResponseWithSuccessData($lang, $response, 1);
    }
    public function orderDetails(Request $request, $orderId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithUnauthorizedRequest($lang, 4);
        }

        $orders = Order::where('branch_id', $employee->branch_id)
            ->where('delivery_id', $employee->id)
            ->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons', 'address'])->where('id', $orderId)
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
            $clientDetails = [
                'created_at' => $order->created_at,
                'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : $order->address->user_name,
                'client_phone' => $order->Client->flag != 'unknown' ? $order->Client->phone : $order->address->address_phone,
                'client_address' => $order->address->address,
                'address_latitude' => $order->address->latitude,
                'address_longitude' => $order->address->longtitude,
                'address_phone' => $order->address->address_phone,
                'address_notes' => $order->address->notes,
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
                    'dish_image' => $detail->dish->image ?? null,
                    'size' => $detail->dish_size_id ?
                        (($lang === 'ar') ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
                        : null,
                    'quantity' => $detail->quantity,
                    'total_dish_price' => formatFloat($total),
                ];
            });
            $trackingStatus = array_search(
                $order->tracking->last()->order_status,
                OrderTracking::$statusMap
            );
            $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';

            return [
                'tracking_status' => $trackingStatus,
                'client_details' => $clientDetails,
                'order_details' => $orderDetails,
                'total_price' => formatFloat($order->total_price_after_tax),
                'currency_symbol' => $currencySymbol,
                'payment_method' => $order->orderTransactions->first()->payment_method,
                'order_notes' => $order->note,
            ];
        });
        $response = [
            'orderData' => $responseData,
        ];
        return ResponseWithSuccessData($lang, $response, 1);
    }
    public function activeOrders(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithUnauthorizedRequest($lang, 4);
        }

        $ordersQuery = Order::where('branch_id', $employee->branch_id)
            ->where('delivery_id', $employee->id)
            ->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons', 'table', 'address'])
            ->where('type', 'Delivery')
            ->where('status', 'packing');

        $orders = $ordersQuery->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => $lang == 'en' ? 'No orders found.' : 'لا يوجد طلبات.',
                'data' => null
            ], 200);
        }

        $responseData = $orders->map(function ($order) use ($lang) {
            $trackingStatus = array_search(
                $order->tracking->last()->order_status,
                OrderTracking::$statusMap
            );
            $orderData = [
                'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : $order->address->user_name,
                'tracking_status' => $trackingStatus,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'created_at' => $order->created_at,
                'address' => $order->address->address,
                'client_phone' => $order->Client->flag != 'unknown' ? $order->Client->phone : $order->address->address_phone,
                'address_phone' => $order->address->address_phone,
                'address_latitude' => $order->address->latitude,
                'address_longitude' => $order->address->longtitude,
                'address_notes' => $order->address->notes,
            ];

            return $orderData;
        });
        $response = [
            'orders' => $responseData,
        ];
        return ResponseWithSuccessData($lang, $response, 1);
    }
    public function pastOrders(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithUnauthorizedRequest($lang, 4);
        }

        $selectedDate = $request->input('date');

        $ordersQuery = Order::where('branch_id', $employee->branch_id)
            ->where('delivery_id', $employee->id)
            ->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons', 'table', 'address'])
            ->where('type', 'Delivery')
            ->whereHas('tracking', function ($query) {
                $query->whereIn('order_status', ['delivered', 'completed', 'cancelled']);
            });

        if ($selectedDate) {
            $ordersQuery->whereDate('created_at', $selectedDate);
        }

        $orders = $ordersQuery->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => $lang == 'en' ? 'No orders found.' : 'لا يوجد طلبات.',
                'data' => null
            ], 200);
        }

        $responseData = $orders->map(function ($order) use ($lang) {
            $orderStatus = array_search(
                $order->status,
                Order::$statusMap
            );
            $trackingStatus = array_search(
                $order->tracking->last()->order_status,
                OrderTracking::$statusMap
            );
            $orderData = [
                'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : $order->address->user_name,
                'order_status' => $orderStatus,
                'tracking_status' => $trackingStatus,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'created_at' => $order->created_at,
                'address' => $order->address ? ($order->address->address ?? null) : null,
                'client_phone' => $order->Client->flag != 'unknown' ? $order->Client->phone : $order->address->address_phone,
                'address_phone' => $order->address ? ($order->address->address_phone ?? null) : null,
                'address_latitude' => $order->address->latitude,
                'address_longitude' => $order->address->longtitude,
                'address_notes' => $order->address->notes,
            ];

            return $orderData;
        });
        $response = [
            'orders' => $responseData,
        ];
        return ResponseWithSuccessData($lang, $response, 1);
    }
    public function recentOrders(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithUnauthorizedRequest($lang, 4);
        }

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $ordersQuery = Order::where('branch_id', $employee->branch_id)
            ->where('delivery_id', $employee->id)
            ->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons', 'table', 'address'])
            ->where('type', 'Delivery')
            ->whereHas('tracking', function ($query) {
                $query->whereIn('order_status', ['delivered', 'completed', 'cancelled']);
            })
            ->where(function ($query) use ($today, $yesterday) {
                $query->whereDate('created_at', $today)
                    ->orWhereDate('created_at', $yesterday);
            });

        $orders = $ordersQuery->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => $lang == 'en' ? 'No orders found.' : 'لا يوجد طلبات.',
                'data' => null
            ], 200);
        }

        $formatOrderData = function ($order) use ($lang) {
            $orderStatus = array_search(
                $order->status,
                Order::$statusMap
            );
            $trackingStatus = array_search(
                $order->tracking->last()->order_status,
                OrderTracking::$statusMap
            );
            return [
                'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : $order->address->user_name,
                'order_status' => $orderStatus,
                'tracking_status' => $trackingStatus,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'created_at' => $order->created_at,
                'address' => $order->address ? ($order->address->address ?? null) : null,
                'client_phone' => $order->Client->flag != 'unknown' ? $order->Client->phone : $order->address->address_phone,
                'address_phone' => $order->address ? ($order->address->address_phone ?? null) : null,
                'address_latitude' => $order->address->latitude,
                'address_longitude' => $order->address->longtitude,
                'address_notes' => $order->address->notes,
            ];
        };

        $todayOrders = $orders->filter(function ($order) use ($today) {
            return Carbon::parse($order->created_at)->isSameDay($today);
        })->map($formatOrderData);

        $yesterdayOrders = $orders->filter(function ($order) use ($yesterday) {
            return Carbon::parse($order->created_at)->isSameDay($yesterday);
        })->map($formatOrderData);

        $response = [
            'today_orders' => $todayOrders->values()->all(),
            'yesterday_orders' => $yesterdayOrders->values()->all(),
        ];

        return ResponseWithSuccessData($lang, $response, 1);
    }
    public function updateTrackingStatus(Request $request, $orderId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithUnauthorizedRequest($lang, 4);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:on_way,delivered',
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }
        $order = Order::where('branch_id', $employee->branch_id)
            // ->where(function ($query) {
            //     $query->where('status', 'packing')
            //         ->orWhere('status', 'on_way');
            // })
            ->where('id', $orderId)
            ->first();
        if (!$order) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Order not found or not eligible for status update' : 'الطلب غير موجود أو غير مؤهل لتحديث الحالة',
                'errorData' => ['error' => $lang == 'en' ? 'Order not found or not eligible for status update' : 'الطلب غير موجود أو غير مؤهل لتحديث الحالة'],
            ], 200);
        }
        if ($employee->flag == 'driver' && $order->delivery_id != $employee->id) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Sorry, this order does not belong to you' : 'عفوا هذا الطلب غير خاص بك',
                'errorData' => ['error' => $lang == 'en' ? 'Sorry, this order does not belong to you' : 'عفوا هذا الطلب غير خاص بك'],
            ], 200);
        }
        // if ($order->tracking()->latest()=== 'packing' && $request->status == 'delivered') {
        //     return response()->json([
        //         'status' => false,
        //         'code' => 400,
        //         'message' => $lang == 'en' ? 'Order not eligible for status update' : 'الطلب غير مؤهل لتحديث الحالة',
        //         'errorData' => ['error' => $lang == 'en' ? 'Order not eligible for status update' : 'الطلب غير مؤهل لتحديث الحالة'],
        //     ], 200);
        // }
        $order->tracking()->update([
            'order_status' => $request->status,
            'modify_by' => $employee->id,
        ]);

        $data['order'] = $order;
        $statusValue = array_search($request->status, OrderTracking::$statusMap);
        $data['status'] = $statusValue;

        $status = null;
        if ($order->type === 'Delivery' || $order->type === 'Takeaway') {
            $status = $request->status;
            if ($status != null) {
                $statusValue = array_search($status, OrderTracking::$statusMap);

                $data = [
                    'orderId' => $order->id,
                    'status' => $statusValue,
                    'date' => now()->toDateString(), // Add this line

                ];
                if ($order->type == 'Delivery' && $request->status == 'delivered') {
                    $channel = $this->checkChannel($order);
                    if ($channel) {
                        $channel->status = 'closed';
                        $channel->save();
                    }
                }
                if ($order->type == 'Delivery' && $request->status == 'on_way') {
                    $clientId = $order->client_id;
                    if ($clientId == 11) {
                        $contact = [
                            'delivery_id' =>  $order->delivery_id ?? null,
                            'delivery_name' =>  $order->delivery->first_name . ' ' . $order->delivery->last_name ?? null,
                            'delivery_phone' => $order->delivery->phone_number ?? null,
                            'chat_channel_id' => null,
                        ];
                        $data['channel'] = $contact;
                    } else {
                        $channel = $this->checkChatChannel($order);
                        $data['channel'] = $channel;
                    }
                }
            }
            if ($order->type == 'Delivery' && $request->status == 'delivered') {
                $clientId = $order->client_id;
                $deliveryId = $order->delivery_id ?? null;
                if ($clientId == 11) {
                    $contact = [
                        'delivery_id' =>  $deliveryId ?? null,
                        'delivery_name' =>  $order->delivery->first_name . ' ' . $order->delivery->last_name ?? null,
                        'delivery_phone' => $order->delivery->phone_number ?? null,
                        'chat_channel_id' => null,
                    ];
                    $data['channel'] = $contact;
                } else {
                    $has_channel = ChatChannel::where(function ($query) use ($clientId, $deliveryId) {
                        $query->where('initiator_id', $clientId)
                            ->where('participant_id', $deliveryId);
                    })->orWhere(function ($query) use ($clientId, $deliveryId) {
                        $query->where('initiator_id', $deliveryId)
                            ->where('participant_id', $clientId);
                    })->first();
                    if ($has_channel) {
                        $channelName = 'order-channel-' . $order->id . '-client-' . $order->client_id;
                        ChatChannel::where('id', $has_channel->id)->update([
                            'status' => 'closed',
                            'closed_at' => SupportCarbon::now(),
                        ]);
                    }
                }
            }
 
            broadcast(new orderChangeStatus($data,$order));
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => $lang == 'en' ? 'Order status updated successfully' : 'تم تحديث حالة الطلب بنجاح',
            'data' => [
                'order_id' => $order->id,
                'new_status' => $request->status,
                'date' => now()->toDateString(), // Add this line

            ],
        ]);
    }
}
