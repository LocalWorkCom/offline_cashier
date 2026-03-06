<?php

namespace App\Http\Controllers\Api\KitchenAPIs;

use App\Events\dishChangeStatus;
use App\Events\dishChangeStatus2;
use App\Events\orderChangeStatus;
use App\Events\OrderReadyForPickup;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderTracking;
use App\Services\HR_Services\TimetableService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Pusher\Pusher;

class KitchenOrderController extends Controller
{
    public function listOrders(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }
        $filters = $this->parseFilters($request);
        $orders = $this->getFilteredOrders($employee, $filters);
        // At line 262 or before the error, add:

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => $lang == 'en' ? 'No orders found' : 'لا توجد طلبات.',
                'data' => null
            ], 200);
        }

        $responseData = $this->buildOrdersResponse($orders, $employee, $filters, $lang);

        if (empty($responseData)) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => $lang == 'en' ? 'No dishes found for your selections' : 'لا توجد أطباق مطابقة لاختياراتك.',
                'data' => null
            ], 200);
        }

        return ResponseWithSuccessData($lang, ['orders' => $responseData], 1);
    }

    private function parseFilters(Request $request): array
    {
        $status = $request->query('status');
        $type = $request->query('type');
        $dishStatus = $request->query('dish_status');

        $allowedStatuses = ['pending', 'inprogress', 'packing'];
        $allowedDishStatuses = ['pending', 'inprogress', 'completed', 'cancel'];

        return [
            'status' => $this->normalizeArray($status, $allowedStatuses),
            'type' => $this->normalizeArray($type),
            'dish_status' => $this->normalizeArray($dishStatus, $allowedDishStatuses),
        ];
    }

    private function normalizeArray($value, array $allowedValues = []): array
    {
        if (is_array($value)) {
            $array = $value;
        } elseif (is_string($value) && !empty($value)) {
            $array = array_map('trim', explode(',', $value));
        } else {
            $array = [];
        }

        return !empty($allowedValues) ? array_intersect($array, $allowedValues) : $array;
    }

    private function getFilteredOrders($employee, array $filters)
    {
        $today = Carbon::today();
        $shiftDetails = TimetableService::getTimetableForDate($employee->id, $today);
        $allowedStatuses = ['pending', 'inprogress', 'packing'];

        $ordersQuery = Order::with(['branch', 'tracking', 'orderDetailsWithoutCancel.dish', 'orderDetailsWithoutCancel.dishAddons', 'orderDetailsWithoutCancel.dishSize', 'table', 'table.floorPartitions'])
            ->where('branch_id', $employee->branch_id)
            ->where('in_request_return', 0);

        if (!empty($filters['status'])) {
            $ordersQuery->whereIn('status', $filters['status']);
        } else {
            $ordersQuery->whereIn('status', $allowedStatuses);
        }

        if (!empty($filters['type'])) {
            $ordersQuery->whereIn('type', $filters['type']);
        }

        if (!empty($filters['dish_status'])) {
            $ordersQuery->whereHas('orderDetailsWithoutCancel', function ($query) use ($filters) {
                $query->whereIn('status', $filters['dish_status']);
            });
        }

        $this->applyShiftFilter($ordersQuery, $shiftDetails);

        $orders = $ordersQuery->get();
        $deliveryOrders = $this->getDeliveryOrders($employee, $filters, $allowedStatuses);

        return $orders->merge($deliveryOrders);
    }

    private function applyShiftFilter($query, $shiftDetails)
    {
        if (!$shiftDetails['status']) {
            return;
        }

        $onDutyTime = $shiftDetails['data']['on_duty_time'];
        $offDutyTime = $shiftDetails['data']['off_duty_time'];

        if ($shiftDetails['data']['cross_day']) {
            $query->where(function ($q) use ($onDutyTime, $offDutyTime) {
                $q->whereTime('created_at', '>=', $onDutyTime)
                    ->orWhereTime('created_at', '<=', $offDutyTime);
            });
        } else {
            $query->whereTime('created_at', '>=', $onDutyTime)
                ->whereTime('created_at', '<=', $offDutyTime);
        }
    }

    private function getDeliveryOrders($employee, array $filters, array $allowedStatuses)
    {
        $twentyFourHoursAgo = Carbon::now()->subHours(24);

        $deliveryOrdersQuery = Order::where('branch_id', $employee->branch_id)
            ->where('type', 'Delivery')
            ->where('in_request_return', 0)
            ->whereHas('orderTransactions', function ($query) {
                $query->where('payment_status', 'unpaid');
            })
            ->where('created_at', '>=', $twentyFourHoursAgo)
            ->with(['branch', 'tracking', 'orderDetailsWithoutCancel.dish', 'orderDetailsWithoutCancel.dishAddons', 'table', 'orderTransactions'])
            ->orderBy('updated_at', 'desc')->orderBy('created_at', 'desc');

        if (!empty($filters['status'])) {
            $deliveryOrdersQuery->whereIn('status', $filters['status']);
        } else {
            $deliveryOrdersQuery->whereIn('status', $allowedStatuses);
        }

        if (!empty($filters['type']) && !in_array('Delivery', $filters['type'])) {
            return collect();
        }

        return $deliveryOrdersQuery->get();
    }

    private function buildOrdersResponse($orders, $employee, array $filters, string $lang): array
    {
        $orderIds = $orders->pluck('id')->toArray();
        $filteredDishes = splitDishesOnAllOrders($orderIds, $employee->id);

        if (empty($filteredDishes)) {
            return [];
        }

        $groupedDishes = $this->groupDishesByOrder($filteredDishes);
        $responseData = [];

        foreach ($groupedDishes as $orderId => $dishes) {
            $dishes = $this->filterDishesByStatus($dishes, $filters['dish_status']);

            if (empty($dishes)) {
                continue;
            }

            $order = $orders->firstWhere('id', $orderId);
            $responseData[] = $this->buildOrderResponse($order, $dishes, $lang);
        }

        return $responseData;
    }

    private function groupDishesByOrder($filteredDishes): array
    {
        $groupedDishes = [];
        foreach ($filteredDishes as $employeeDishes) {
            foreach ($employeeDishes['dishes'] as $dish) {
                $orderId = $dish->order_id;
                if (!isset($groupedDishes[$orderId])) {
                    $groupedDishes[$orderId] = [];
                }
                $groupedDishes[$orderId][] = $dish;
            }
        }
        return $groupedDishes;
    }

    private function filterDishesByStatus(array $dishes, array $dishStatuses): array
    {
        $uniqueDishes = [];
        foreach ($dishes as $dish) {
            $uniqueDishes[$dish->id] = $dish;
        }
        $dishes = array_values($uniqueDishes);

        if (!empty($dishStatuses)) {
            $dishes = array_filter($dishes, function ($dish) use ($dishStatuses) {
                return in_array($dish->status, $dishStatuses);
            });
            $dishes = array_values($dishes);
        }

        return $dishes;
    }

    private function buildOrderResponse($order, array $dishes, string $lang): array
    {
        $orderItemsCount = $order->orderDetailsWithoutCancel->sum('quantity');
        $maxDishTime = $order->orderDetailsWithoutCancel->max(function ($detail) {
            return $detail->dish->time ?? 0;
        });

        $orderData = [
            'order_type' => $order->type,
            'status' => $order->status,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'order_items_count' => $orderItemsCount,
            'table_number' => $order->table->table_number ?? null,
            'floor_partition' => $order->table->floorPartitions->name ?? null,
            'order_time' => $maxDishTime,
            'created_at' => $order->created_at,
            'date' => $order->date,
            'time' => $order->time,
        ];

        $orderItems = [];
        foreach ($dishes as $dish) {
            $orderItems[] = [
                'item_id' => $dish->id,
                'dish_id' => $dish->dish_id,
                'dish_name' => $dish->dish->name ?? null,
                'dish_name_ar' => $dish->dish->name_ar ?? null,
                'dish_name_en' => $dish->dish->name_en ?? null,
                'size_id' => $dish->dish_size_id ?? null,
                'size' => $dish->dish_size_id ?
                    (($lang === 'ar') ? $dish->dishSize->size_name_ar ?? null : $dish->dishSize->size_name_en ?? null)
                    : null,
                'size_name_ar' => $dish->dish_size_id ? $dish->dishSize->size_name_ar  :  null,
                'size_name_en' => $dish->dish_size_id ? $dish->dishSize->size_name_en  :  null,

                'quantity' => $dish->quantity,
                'dish_time' => $dish->dish->time ?? 0,
                'note' => $dish->note,
                'addons' => $dish->dishAddons->map(function ($addon) use ($lang) {
                    return [
                        'addon_category_id' => $addon->addon?->addon_category_id,
                        'addon_id' => $addon->Addon?->addon_id,
                        'addon_name' => $addon->Addon->addons->name ?? null,
                        'addon_name_ar' => $addon->Addon->addons->name_ar ?? null,
                        'addon_name_en' =>  $addon->Addon->addons->name_en ?? null,
                        'addon_status' => $addon->status,
                    ];
                }),
                'dish_status' => $dish->status,
            ];
        }

        return [
            'order_details' => $orderData,
            'order_items' => $orderItems,
            'order_notes' => $order->note,
        ];
    }
   
    public function orderDetails(Request $request, $orderId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $order = Order::with([
            'branch',
            'tracking',
            'orderDetailsWithoutCancel.dish',
            'orderDetailsWithoutCancel.dishAddons',
            'orderDetailsWithoutCancel.dishSize',
            'table',
            'table.floorPartitions'
        ])
            ->where('id', $orderId)
            ->where('in_request_return', 0)
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

        $filteredDishes = splitDishesOnOrder($orderId);

        if (empty($filteredDishes)) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => $lang == 'en' ? 'No dishes found for your selections' : 'لا توجد أطباق مطابقة لاختياراتك.',
                'data' => null
            ], 200);
        }
        $filteredDishes = collect($filteredDishes)
            ->firstWhere('id', $employee->id);
        if (!$filteredDishes) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => $lang == 'en' ? 'No dishes found for your selections' : 'لا توجد أطباق مطابقة لاختياراتك.',
                'data' => null
            ], 200);
        }
        // Group dishes like in listOrders()
        // $dishes = [];
        // foreach ($filteredDishes as $employeeDishes) {
        //     foreach ($employeeDishes['dishes'] as $dish) {
        //         $dishes[] = $dish;
        //     }
        // }
        $dishes = $filteredDishes['dishes'] ?? [];

        if (empty($dishes)) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => $lang == 'en'
                    ? 'No dishes found for your selections'
                    : 'لا توجد أطباق مطابقة لاختياراتك.',
                'data' => null
            ], 200);
        }
        $orderItemsCount = $order->orderDetailsWithoutCancel->sum('quantity');
        $maxDishTime = $order->orderDetailsWithoutCancel->max(function ($detail) {
            return $detail->dish->time ?? 0;
        });

        // Match the same response keys as listOrders()
        $orderData = [
            'order_type' => $order->type,
            'status' => $order->status,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'order_items_count' => $orderItemsCount,
            'table_number' => $order->table->table_number ?? null,
            'floor_partition' => $order->table->floorPartitions->name ?? null,
            'order_time' => $maxDishTime,
            'created_at' => $order->created_at,
            'date' => $order->date,
            'time' => $order->time,
        ];

        $orderItems = [];
        foreach ($dishes as $dish) {
            $orderItems[] = [
                'item_id' => $dish->id,
                'dish_id' => $dish->dish_id,
                'dish_name' => $dish->dish->name ?? null,
                'dish_name_ar' => $dish->dish->name_ar ?? null,
                'dish_name_en' => $dish->dish->name_en ?? null,
                'dish_image' => $dish->dish->image ??  'https://alkoot-restaurant.com/public/images/dishes/51745846293.jpeg',
                'size_id' => $dish->dish_size_id ?? null,
                'size' => $dish->dish_size_id ?
                    (($lang === 'ar') ? $dish->dishSize->size_name_ar ?? null : $dish->dishSize->size_name_en ?? null)
                    : null,
                'size_name_ar' => $dish->dish_size_id ? $dish->dishSize->size_name_ar : null,
                'size_name_en' => $dish->dish_size_id ? $dish->dishSize->size_name_en : null,
                'quantity' => $dish->quantity,
                'dish_time' => $dish->dish->time ?? 0,
                'note' => $dish->note,
                'addons' => $dish->dishAddons->map(function ($addon) use ($lang) {
                    return [
                        'addon_category_id' => $addon->Addon->addon_category_id,
                        'addon_id' => $addon->Addon->addon_id,
                        'addon_name' => $addon->Addon->addons->name ?? null,
                        'addon_name_ar' => $addon->Addon->addons->name_ar ?? null,
                        'addon_name_en' => $addon->Addon->addons->name_en ?? null,
                        'addon_status' => $addon->status,

                    ];
                }),
                'dish_status' => $dish->status,
            ];
        }

        $response = [
            'order_details' => $orderData,
            'order_items' => $orderItems,
            'order_notes' => $order->note,
        ];

        return ResponseWithSuccessData($lang, ['orders' => [$response]], 1);
    }
   

    // public function orderDetails(Request $request, $orderId)
    // {
    //     $lang = $request->header('lang', 'ar');
    //     App::setLocale($lang);

    //     $employee = auth('employee')->user();

    //     if (!$employee) {
    //         return RespondWithBadRequest($lang, 4);
    //     }

    //     $order = Order::with(['branch', 'tracking', 'orderDetailsWithoutCancel.dish', 'orderDetailsWithoutCancel.dishAddons', 'orderDetailsWithoutCancel.dishSize', 'table'])
    //         ->where('id', $orderId)
    //         ->where('in_request_return', 0)
    //         ->first();

    //     if (!$order) {
    //         return response()->json([
    //             'status' => false,
    //             'code' => 400,
    //             'message' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.',
    //             'errorData' => ['error' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.'],
    //             'data' => null
    //         ], 200);
    //     }

    //     $filteredDishes = splitDishesOnOrder($orderId);

    //     if (empty($filteredDishes)) {
    //         return response()->json([
    //             'status' => true,
    //             'code' => 200,
    //             'message' => $lang == 'en' ? 'No dishes found for your selections' : 'لا توجد أطباق مطابقة لاختياراتك.',
    //             'data' => null
    //         ], 200);
    //     }

    //     $orderDetails = [];
    //     foreach ($filteredDishes as $employeeDishes) {
    //         foreach ($employeeDishes['dishes'] as $dish) {
    //             $orderDetails[] = [
    //                 'item_id' => $dish->id,
    //                 'dish_id' => $dish->dish_id,
    //                 'dish_image' => $dish->dish->image,
    //                 'dish_name' => $dish->dish->name ?? null,
    //                 'size_id' => $dish->dish_size_id ?? null,
    //                 'size' => $dish->dish_size_id ?
    //                     (($lang === 'ar') ? $dish->dishSize->size_name_ar ?? null : $dish->dishSize->size_name_en ?? null)
    //                     : null,
    //                 'quantity' => $dish->quantity,
    //                 'note' => $dish->note,
    //                 'addons' => $dish->dishAddons->map(function ($addon) use ($lang) {
    //                     return [
    //                         'addon_category_id' => $addon->Addon->addon_category_id,
    //                         'addon_id' => $addon->Addon->addon_id,
    //                         'addon_name' => ($lang === 'ar') ? $addon->Addon->addons->name_ar ?? null : $addon->Addon->addons->name_en ?? null,
    //                     ];
    //                 }),
    //             ];
    //         }
    //     }

    //     $orderItemsCount = $order->orderDetailsWithoutCancel->sum('quantity');

    //     if (!empty($orderDetails)) {
    //         foreach ($orderDetails as &$orderDetail) {
    //             if ($orderDetail['dish_image'] === null) {
    //                 $orderDetail['dish_image'] = 'https://alkoot-restaurant.com/public/images/dishes/51745846293.jpeg';
    //             }
    //         }
    //         unset($orderDetail);
    //     }

    //     $responseData = [
    //         'order_id' => $order->id,
    //         'order_number' => $order->order_number,
    //         'created_at' => $order->created_at,
    //         'order_items_count' => $orderItemsCount,
    //         'order_details' => $orderDetails,
    //         'order_notes' => $order->note,
    //     ];

    //     $response = [
    //         'orderDetails' => $responseData,
    //     ];

    //     return ResponseWithSuccessData($lang, $response, 1);
    // }
    public function updateDishStatus(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'order_items_ids' => 'required|array',
            'order_items_ids.*' => [
                'required',
                Rule::exists('order_details', 'id')->where(function ($query) {
                    $query->where('status', '!=', 'cancel');
                }),
            ],
            'status' => 'required|in:inprogress,completed',
        ], [
            'order_items_ids.*.exists' => $lang == 'ar' ? 'عنصر الطلب المحدد غير صالح.' : 'The selected order item is invalid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Validation error' : 'خطأ في التحقق',
                'errorData' => $validator->errors(),
                'data' => null
            ], 200);
        }

        // Additional validation to ensure order items have in_request_return = 0
        $validOrderItems = OrderDetail::whereIn('id', $request->order_items_ids)
            ->where('in_request_return', 0)
            ->pluck('id');

        $invalidItems = array_diff($request->order_items_ids, $validOrderItems->toArray());
        if (!empty($invalidItems)) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Some order items are not valid for updating' : 'بعض عناصر الطلب غير صالحة لتغيير الحالة',
                'errorData' => ['invalid_items' => $invalidItems],
                'data' => null
            ], 200);
        }

        $order = Order::where('id', $request->order_id)->where('in_request_return', 0)->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.',
                'errorData' => ['error' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.'],
                'data' => null
            ], 200);
        }

        $updatedDishes = [];
        foreach ($request->order_items_ids as $dishId) {
            $dish = $order->orderDetails()->with('dishAddons')
                ->where('in_request_return', 0)
                ->find($dishId);

            if ($dish) {
                $dish->status = $request->status;
                $dish->save();
                $dish->dishAddons()->update(['status' => $request->status]);
                $updatedDishes[] = $dish;
            }
        }

        if (empty($updatedDishes)) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'No dishes updated' : 'لم يتم تحديث أي أطباق.',
                'errorData' => ['error' => $lang == 'en' ? 'No dishes updated' : 'لم يتم تحديث أي أطباق.'],
                'data' => null
            ], 200);
        }

        $order->load('orderDetailsWithoutCancel');


        if ($request->status == 'inprogress') {
            $order->status = 'inprogress';
            $order->tracking()->create([
                'order_status' => 'in_progress',
                'created_at' => now(),
            ]);
            $order->save();
        } elseif ($request->status == 'completed') {
            $allCompleted = true;
            foreach ($order->orderDetailsWithoutCancel as $dish) {
                if (!in_array($dish->status, ['completed', 'cancel'])) {
                    $allCompleted = false;
                    break;
                }
            }
            if ($allCompleted) {
                $order->status = 'packing';
                $order->tracking()->create([
                    'order_status' => 'readyForPickup',
                    'created_at' => now(),
                ]);
                $order->save();
                OrderReadyForPickup::dispatch($order, $lang);
            }
        }
        $statusValue = array_search($order->status, OrderTracking::$statusMap);

        $data_order = [
            'orderId' => $order->id,
            'status' => $statusValue,
            'date' => now()->toDateString(), // Add this line

        ];;
        broadcast(new orderChangeStatus($data_order, $order));


        // Get waiter & cashier
        $waiterId = $order->waiter_id;
        $cashierId = $order->cashier_id ?? null; // If you store it in the order
        $customerserviceId = $order->customer_service_id ?? null; // If you store it in the order

        // Prepare broadcast recipients
        $recipients = [
            ['role' => 'Head chef', 'id' => $employee->id],
        ];

        if ($waiterId) {
            $recipients[] = ['role' => 'waiter', 'id' => $waiterId];
        }

        if ($cashierId) {
            $recipients[] = ['role' => 'cashier', 'id' => $cashierId];
        }
        if ($customerserviceId) {
            $recipients[] = ['role' => 'customer_service', 'id' => $customerserviceId];
        }
        $orderItemIds = (array) $request->order_items_ids;

        $orderDetails = OrderDetail::with('dishAddons')
            ->whereIn('id', $orderItemIds)
            ->get()
            ->map(function ($detail) {
                // Compute totalBeforeCoupon properly
                 $activeAddons = $detail->dishAddons->filter(function ($addon) {
                    // Adjust the condition based on your actual status field/value
                    return !in_array($addon->status, [ 'cancel']);
                });

                // Compute totalBeforeCoupon from active addons only
                $addonsTotal = $activeAddons->sum(function ($addon) {
                    return $addon->price_before_coupon ?? 0;
                });

                // Determine correct base price (before coupon, tax may or may not apply)
                $dishPriceBeforeCoupon = $detail->price_before_coupon ?? $detail->price_befor_tax ?? 0;

                $totalBeforeCoupon = $dishPriceBeforeCoupon + $addonsTotal;

                return [
                    'order_detail_id' => $detail->id,
                    'quantity' => $detail->quantity,
                    'dish_status' => $detail->status,
                    'total_dish_price' => formatFloat($totalBeforeCoupon),
                ];
            })
            ->values()
            ->toArray();
        $orderItemsCount = $order->status === 'cancelled'
            ? $order->orderDetails->sum('quantity')
            : $order->orderDetailsWithoutCancel->sum('quantity');
        // Create data for broadcasting
        $data = [
            'order_id' => $order->id,
            'order_type' => $order->type,
            'order_items_count' =>  $orderItemsCount,
            'status' => $order->status,
            'items_updated' => $orderDetails,
                                'total_price' =>  formatFloat($order->total_price_after_tax),

            'date' => now()->toDateString(),
        ];

        broadcast(new dishChangeStatus2($data));

        foreach ($recipients as $recipient) {
            $data['recipients'][] = [
                'role' => $recipient['role'],
                'id' => $recipient['id'],
            ];

            //add notification
            foreach ($request->order_items_ids as $dishId) {
                $dish_details = OrderDetail::find($dishId);
                if ($request->status == "inprogress") {
                    $status_ar = "قيد التنفيذ";
                    $status_en = "In Progress";
                } elseif ($request->status == "completed") {
                    $status_ar = "مكتمل";
                    $status_en = "Completed";
                }

                if ($dish_details) {
                    $description_ar =  'تم تحديث حالة الطبق إلى ' . $status_ar;
                    $description_en = 'Dish status updated to ' . $status_en;
                    $title_ar = 'تحديث حالة الطبق: ' . $dish_details->dish->name_ar . ' - ' . $order->order_number;
                    $title_en = 'Dish Status Update: ' . $dish_details->dish->name_en . ' - ' . $order->order_number;
                    // Assuming you have a function to add
                    addNotification('order', $recipient['role'], $description_ar, $description_en, $title_ar, $title_en, $recipient['id'], 7, $lang, $order->id, null);
                }
            }
        }
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => $lang == 'en' ? 'Dish status updated successfully' : 'تم تحديث حالة الطبق بنجاح',
        ]);
    }
}
