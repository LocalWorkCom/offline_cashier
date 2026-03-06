<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Events\orderChangeStatus;
use Pusher\Pusher;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Models\BranchSetting;
use App\Models\OrderTracking;
use Illuminate\Support\Carbon;
use App\Models\OrderTransaction;
use App\Models\CancellationReason;
use App\Events\OrderReadyForPickup;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Models\BranchMenu;
use Illuminate\Support\Facades\Validator;
use App\Services\ClientServices\OrderService;

class OrderChangeController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function changeStatus(Request $request, $id)
    {
        // dd(0);
        $lang =  $request->header('lang', 'en');
        $employee = auth('employee')->user();
        if (!$employee) {
            return RespondWithBadRequestData($lang, 2); // Assuming code 2 is for unauthorized
        }
        $order = Order::find($request->id);
        // dd($order);
        if (!$order) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => __('order.order_not_found'),
                'data' => null,
                'errorData' => [
                    'order' => [__('order.order_not_found')]
                ],
                'validation_type' => true
            ], 400);
        }
        if ($order->status === 'cancelled') {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => __('order.order_already_cancelled'),
                'data' => null,
                'errorData' => [
                    'order' => [__('order.order_already_cancelled')]
                ],
                'validation_type' => true
            ], 400);
        }
        $allowedStatuses = ['cancelled', 'completed', 'in_progress', 'on_way', 'delivered', 'readyForPickup'];
        if (!in_array($request->status, $allowedStatuses)) {
            return response()->json(['message' => __('order.invalid_status')], 400);
        }
        $cancel_time = getBranchSettings($order->branch_id, 'time_cancellation');
        $minutesDifference = $order->created_at->diffInMinutes(Carbon::now());
        if ($request->status == 'cancelled') {

            // validate cancellation reason
            $validator = Validator::make($request->all(), [
                'reason_id' => 'required|exists:cancellation_reasons,id', // adjust table name if needed
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => 'Validation Error.',
                    'data' => null,
                    'errorData' => $validator->errors(),
                    'validation_type' => true
                ], 400);
            }

            if (auth('employee')->user()->hasRole('Branch_Manager') || auth('employee')->user()->hasRole('superAdmin') || auth('employee')->user()->hasRole('LocalWork_Admin')) {

                $reasonModel = CancellationReason::create([
                    'type' => 'branch manager',
                    'order_id' => $request->id,
                    'reason_id' => $request->reason_id,
                    'reason' => $request->reason, // This can be optional if reason_id is selected
                    'user_id' => $employee->id,
                    'created_by' => authActionSave()['by'],
                    'created_by_type' => authActionSave()['type'],
                ]);
                $reasonModel->load(['reasonModel:id,reason_ar,reason_en']);

                $this->trackOrderStatus($order, $request->status);
                $this->orderDetailsStatus($order, 'cancel');
                $request['type'] = 1;
                $this->orderService->orderCancel($request);

                // Update the print_status to 'cancelled'
                $order->print_status = 'cancelled';

                $order->status = $request->status;
                $order->save();
                $responseData = [
                    'order' => $order,
                    'cancellation_reason' => $reasonModel,
                    'status_name_en' => $request->status
                ];
                return ResponseWithSuccessData($lang, $responseData, 1);
            } else {
                return response()->json(['message' => __('order.cancellation_not_allowed')], 403);
            }
        } elseif ($request->status == 'on_way') {
            if ($order->type == 'Delivery' && !$order->delivery_id) {
                return response()->json(['message' => __('order.there is no delivery assigned for this step')], 400);
            }
            $this->trackOrderStatus($order, $request->status);
            $this->orderDetailsStatus($order, 'completed');
            $order->status = 'packing';
            $order->save();
            $datares = [
                'order' => $order,
                'status_name_en' => $request->status,
            ];
            return ResponseWithSuccessData($lang, $datares, 1);
        } elseif ($request->status == 'delivered') {
            if ($order->type == 'Delivery' && !$order->delivery_id) {
                return response()->json(['message' => __('order.there is no delivery assigned for this step')], 400);
            }
            $this->trackOrderStatus($order, $request->status);
            $this->orderTransactionStatus($order, $request->status);
            $order->status = 'packing';
            $order->save();
            $datares = [
                'order' => $order,
                'status_name_en' => $request->status,
            ];
            return ResponseWithSuccessData($lang, $ordataresder, 1);

            if ($order->type == 'Delivery') {
                $channel = $this->checkChannel($order);
                $channel->status = 'closed';
                $channel->save();
            }
            $user = User::find($order->client_id);
            $user_fcm = $user->fcm_token;
            $user_id = $user->id;

            send_push_notification(
                $user_fcm,
                'تم توصيل طلبك رقم ' . $order->order_number . ' من الفرع ' . $order->branch->name_en,
                'Your order ' . $order->order_number . ' has been delivered from branch ' . $order->branch->name_en,
                'تم توصيل طلبك',
                'Your order has been delivered',
                'client',
                $user_id,
                auth('admin')->id() ?? $user_id,
                $order->id,
                $lang,
                null,
            );
        } elseif ($request->status == 'readyForPickup') {
            $this->trackOrderStatus($order, $request->status);
            $this->orderDetailsStatus($order, 'completed');
            $order->status = 'packing';
            $order->save();

            OrderReadyForPickup::dispatch($order, $lang);
            $datares = [
                'order' => $order,
                'status_name_en' => $request->status,
            ];
            return ResponseWithSuccessData($lang, $datares, 1);
        } else {
            $order->status = $request->status === 'in_progress' ? 'inprogress' : $request->status;
            $order->save();
            if ($order) {
                $this->orderDetailsStatus($order, $request->status);
                $this->trackOrderStatus($order, $request->status);
            }
            $datares = [
                'order' => $order,
                'status_name_en' => $request->status,
            ];
            return ResponseWithSuccessData($lang, $datares, 1);
        }
        $status = null;
        if ($order->type === 'Delivery' || $order->type === 'Takeaway') {
            $status = ($order->type === 'Delivery' && $request->status === 'readyForPickup') ? 'in_progress' : $request->status;
            if ($status != null) {
                $statusValue = array_search($status, OrderTracking::$statusMap);

                $data = [
                    'orderId' => $order->id,
                    'status' => $statusValue,
                    'status_name' => $request->status,
                    'date' => now()->toDateString(), // Add this line
                ];


                if ($order->type == 'Delivery' && $request->status == 'on_way') {
                    if ($order->client_id == 11) {
                        $contact = [
                            'delivery_id' =>  $order->delivery_id ?? null,
                            'delivery_name' =>  $order->delivery->first_name . ' ' . $order->delivery->last_name ?? null,
                            'delivery_phone' => $order->delivery->phone_number ?? null,
                            'delivery_image' =>  $order->delivery->image ?? '/front/AlKout-Resturant/SiteAssets/images/delivery-man.png',
                            'chat_channel_id' => null,
                        ];
                        $data['channel'] = $contact;
                    } else {
                        $channel = $this->checkChatChannel($order);
                        $data['channel'] = $channel;
                    }
                }
            
                broadcast(new orderChangeStatus($data, $order));
            }
        } else {
            $statusValue = array_search($request->status, OrderTracking::$statusMap);

            $invoice = Invoice::where('order_id', $order->id)->where('invoice_type', 'invoice')->first();
            // Count all items (including canceled) if order is canceled, otherwise count non-canceled items
            $orderItemsCount = $order->status === 'cancelled'
                ? $order->orderDetails->sum('quantity')
                : $order->orderDetailsWithoutCancel->sum('quantity');
            $orderData = [
                'status' => $order->status,
                'order_id' => $order->id,
                'invoice_id' => ($invoice) ? $invoice->id : null,
                'created_at' => $order->created_at,
                'order_number' => $order->order_number,
                'order_items_count' => $orderItemsCount,
                'table_id' => $order->table->id ?? null,
                'table_number' => $order->table->table_number ?? null,
                'note' => $order->note
            ];
            $orderItems = $order->orderDetails->map(function ($detail) use ($lang, $order) {
                $addons = $detail->dishAddons->where('status', '!=', 'cancel');
                if ($order->tax_application == 0) {
                    $orderDetailTotal = $detail->price_befor_tax;
                    $addonsTotal = $addons->sum('price_before_tax');
                    if ($detail->status === 'cancel') {
                        $canceledAddonsTotal = $detail->dishAddons
                            ->where('status', 'cancel')
                            ->sum('price_before_tax');
                        $addonsTotal += $canceledAddonsTotal;
                    }
                } else {
                    $orderDetailTotal = $detail->price_after_tax;
                    $addonsTotal = $addons->sum('price_after_tax');
                    if ($detail->status === 'cancel') {
                        $canceledAddonsTotal = $detail->dishAddons
                            ->where('status', 'cancel')
                            ->sum('price_after_tax');
                        $addonsTotal += $canceledAddonsTotal;
                    }
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

                $dishCouponId = $detail->coupon_id ?? null;
                $dishCouponValue = $dishCouponId ? ($detail->coupon_value ?? 0) : 0;

                $branch_menu = BranchMenu::where('branch_id', $order->branch_id)->where('dish_id', $detail->dish_id)->with('branchMenuAddons', 'branchMenuSizes')->first();

                return [
                    'item_id' => $detail->id,
                    'dish_id' => $detail->dish_id,
                    'dish_menu_id' => $branch_menu->id,
                    'dish_order' => $detail->dish_order,
                    'dish_name' => $detail->dish->name ?? null,
                    'dish_image' => $detail->dish->image ?? null,
                    'size_id' => $detail->dish_size_id ?? null,
                    'size_menu_id' => $branch_menu->branchMenuSizes()->where('branch_id', $order->branch_id)
                        ->where('dish_size_id', $detail->dish_size_id)
                        ->value('id') ?? null,

                    'size' => $detail->dish_size_id ?
                        (($lang === 'ar') ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
                        : null,
                    'quantity' => $detail->quantity,
                    'total_dish_price' => formatFloat($totalBeforeCoupon),
                    'total_dish_price_coupon_applied' => formatFloat($total),
                    'note' => $detail->note,
                    'addons' => $detail->dishAddons->map(function ($addon) use ($lang, $branch_menu, $order) {
                        return [
                            'addon_category_id' => $addon->Addon?->addon_category_id,
                            'addon_id' => $addon->Addon?->addon_id,
                            'addon_menu_id' => $branch_menu->branchMenuAddons()->where('branch_id', $order->branch_id)
                                ->where('dish_addon_id', $addon->Addon?->id)
                                ->value('id'),

                            'addon_name' => ($lang === 'ar') ? $addon->Addon->addons->name_ar ?? null : $addon->Addon->addons->name_en ?? null,
                            'addon_status' => $addon->status,
                        ];
                    }),
                    'dish_status' => $detail->status,
                    'coupon_id' => $dishCouponId,
                    'coupon_value' => formatFloat($dishCouponValue),
                    'coupon_title' => $dishCouponId ? $detail->coupon?->title : null
                ];
            });
            $totalPrice = $order->total_price_after_tax;
            $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';

            $subtotal = $order->total_price_befor_tax;
            if ($order->tax_application == 1) {
                $subtotal += $order->tax_value;
            }
            $data = [
                'orderId' => $order->id,
                'status' => $statusValue,
                'status_name' => $request->status,
                'date' => now()->toDateString(),
                'order' => [
                    'order_details' => $orderData,
                    'order_items' => $orderItems,
                    'subtotal_price' => formatFloat($subtotal),
                    'subtotal_price_before_coupon' => formatFloat($order->total_price_before_coupon),
                    'tax_value' => formatFloat($order->tax_value ?? null),
                    'total_price' => formatFloat($totalPrice),
                    'payment_method' => $order->transaction?->payment_method,
                    'payment_status' => $order->transaction?->payment_status,
                    'currency_symbol' => $currencySymbol
                ]
            ];
        }
        broadcast(new orderChangeStatus($data, $order));

        $datares = [
            'order' => $order,
            'status_name_en' => $request->status,
        ];
        return ResponseWithSuccessData($lang, $datares, 1);

        // return response()->json(['message' => __('order.order_status_updated')]);
    }
    private function orderDetailsStatus($order, $status)
    {
        $order_details = OrderDetail::where('order_id', $order->id)->whereIn('status', ['pending', 'inprogress'])->get();
        foreach ($order_details as $item) {
            $addons = OrderAddon::where('order_details_id', $item->id)->get();
            foreach ($addons as $addon) {
                $addon = OrderAddon::find($addon->id);
                $addon->status = $status === 'in_progress' ? 'inprogress' : $status;
                $addon->modified_by = authActionSave()['by'];
                $addon->modified_by_type = authActionSave()['type'];
                $addon->updated_at = now();
                $addon->save();
            }
            $item = OrderDetail::find($item->id);
            $item->status = $status === 'in_progress' ? 'inprogress' : $status;
            $item->modified_by = authActionSave()['by'];
            $item->modified_by_type = authActionSave()['type'];
            $item->updated_at = now();
            $item->save();
        }
    }
    private function trackOrderStatus($order, $status)
    {
        $orderTracking = new OrderTracking;
        $orderTracking->order_id = $order->id;
        $orderTracking->order_status = $status;
        $orderTracking->created_by = authActionSave()['by'];
        $orderTracking->created_by_type = authActionSave()['type'];
        $orderTracking->time = now()->format('H:i:s');
        $orderTracking->save();
    }
    private function orderTransactionStatus($order, $status)
    {
        $OrderTransaction =  OrderTransaction::where('order_id', $order->id)->first();
        $OrderTransaction->payment_status = 'paid';
        $OrderTransaction->paid_at = date('Y-m-d H:i:s');
        $OrderTransaction->modified_by = authActionSave()['by'];
        $OrderTransaction->modified_by_type = authActionSave()['type'];
        $OrderTransaction->save();
    }

    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');

            // Check authentication first
            $employee = auth('employee')->user();
            if (!$employee) {
                return RespondWithBadRequestData($lang, 2); // Assuming code 2 is for unauthorized
            }
            $query = CancellationReason::with('floorPartitions.tables');

            $result = paginateOrGetAll($query, $request, null);
            return ResponseWithSuccessDataPaginated($lang, $result, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2); // Assuming code 2 is for server error
        }
    }
}
