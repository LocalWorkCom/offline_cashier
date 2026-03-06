<?php

namespace App\Http\Controllers\Dashboard;

use App\Events\orderChangeStatus;
use Carbon\Carbon;
use Pusher\Pusher;
use App\Models\Dish;
use App\Models\User;
use App\Models\Order;
use App\Models\Table;
use App\Models\Branch;
use App\Models\Country;
use App\Traits\ChatTrait;
use App\Models\BranchMenu;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\OrderTracking;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\OrderTransaction;
use App\Models\CancellationReason;
use App\Events\OrderReadyForPickup;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderCancellationReason;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Services\ClientServices\OrderService;
use App\Services\SettingsServices\BranchService;
use Illuminate\Support\Facades\Validator;


class OrderController extends Controller
{
    use ChatTrait;
    protected $orderService;
    protected $lang;
    protected $checkToken;  // Set to true or false based on your need
    protected $branchService;


    public function __construct(OrderService $orderService, BranchService $branchService)
    {
        $this->orderService = $orderService;
        $this->lang =  app()->getLocale();
        $this->checkToken = false;
        $this->branchService = $branchService;
    }

    public function index(Request $request)
    {
        $response = $this->orderService->index($request, $this->checkToken);
        $responseData = $response->original;

        // Ensure it's a collection and sort it
        $orders = collect($responseData['data'])->sortByDesc('date')->values();

        $reasons = OrderCancellationReason::select('id', 'reason_ar', 'reason_en')->get();

        return view('dashboard.order.list', compact('orders', 'reasons'));
    }

    public function checkPaymentStatus($orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            $paymentStatus = $order->transaction ? $order->transaction->payment_status : 'unpaid';

            return response()->json([
                'payment_status' => $paymentStatus
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error checking payment status'
            ], 500);
        }
    }
    public function create()
    {
        $branches =  Branch::with(['country', 'creator', 'deleter', 'floors'])->get();
        $tables = Table::where('branch_id', getDefaultBranch())->all();
        $menu = BranchMenu::where('branch_id', getDefaultBranch())->all();

        return view('dashboard.order.add', compact('branches'));
    }
    public function store(Request $request)
    {
        //        dd($request->all());
        $response = $this->orderService->store_v2($request->all(), $this->checkToken, 'web');
        $responseData = $response->original;
        //        dd($responseData);
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect('dashboard/orders')->with('message', $message);
    }

    public function show($id)
    {
        $lang = App::getLocale(); // Get the current locale

        $response = $this->orderService->show($lang, $id, $this->checkToken);

        $responseData = $response->original;

        $order = $responseData['data'];
        $reasons = OrderCancellationReason::select('id', 'reason_ar', 'reason_en')->get();

        return view('dashboard.order.show', compact('order', 'reasons'));
    }
    public function changeStatus(Request $request)
    {
        $lang = App::getLocale();
        $order = Order::find($request->order_id);
        if (!$order) {
            return response()->json(['message' => __('order.order_not_found')], 404);
        }

        $allowedStatuses = ['cancelled', 'completed', 'in_progress', 'on_way', 'delivered', 'readyForPickup'];
        if (!in_array($request->status, $allowedStatuses)) {
            return response()->json(['message' => __('order.invalid_status')], 400);
        }

        $cancel_time = getBranchSettings($order->branch_id, 'time_cancellation');
        $minutesDifference = $order->created_at->diffInMinutes(Carbon::now());
        if ($request->status == 'cancelled') {
            if (auth('admin')->user()->hasRole('Branch Manager') || auth('admin')->user()->hasRole('superAdmin') || auth('admin')->user()->hasRole('LocalWork Admin')) {

                CancellationReason::create([
                    'type' => 'admin',
                    'order_id' => $request->order_id,
                    'reason_id' => $request->reason_id,
                    'reason' => $request->reason, // This can be optional if reason_id is selected
                    'user_id' => auth('admin')->id(),
                    'created_by' => auth('admin')->id(),
                ]);

                $this->trackOrderStatus($order, $request->status);
                $this->orderDetailsStatus($order, 'cancel');
                $request['type'] = 1;
                $this->orderService->orderCancel($request);

                // Update the print_status to 'cancelled'
                $order->print_status = 'cancelled';

                $order->status = $request->status;
                $order->save();
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
        } elseif ($request->status == 'delivered') {
            if ($order->type == 'Delivery' && !$order->delivery_id) {
                return response()->json(['message' => __('order.there is no delivery assigned for this step')], 400);
            }
            $this->trackOrderStatus($order, $request->status);
            $this->orderTransactionStatus($order, $request->status);
            $order->status = 'packing';
            $order->save();
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
                'order'
            );
        } elseif ($request->status == 'readyForPickup') {
            $this->trackOrderStatus($order, $request->status);
            $this->orderDetailsStatus($order, 'completed');
            $order->status = 'packing';
            $order->save();
            OrderReadyForPickup::dispatch($order, $lang);
        } else {
            $order->status = $request->status === 'in_progress' ? 'inprogress' : $request->status;
            $order->save();
            if ($order) {
                $this->orderDetailsStatus($order, $request->status);
                $this->trackOrderStatus($order, $request->status);
            }
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

        return response()->json(['message' => __('order.order_status_updated')]);
    }
    private function CheckOrderPaid($order)
    {
        return  OrderTransaction::where('order_id', $order)->value('payment_status');
    }
    private function orderDetailsStatus($order, $status)
    {
        $order_details = OrderDetail::where('order_id', $order->id)->whereIn('status', ['pending', 'inprogress'])->get();
        foreach ($order_details as $item) {
            $addons = OrderAddon::where('order_details_id', $item->id)->get();
            foreach ($addons as $addon) {
                $addon = OrderAddon::find($addon->id);
                $addon->status = $status === 'in_progress' ? 'inprogress' : $status;
                $addon->modify_by = Auth::guard('admin')->user()->id;
                $addon->updated_at = now();
                $addon->save();
            }
            $item = OrderDetail::find($item->id);
            $item->status = $status === 'in_progress' ? 'inprogress' : $status;
            $item->modify_by = Auth::guard('admin')->user()->id;
            $item->updated_at = now();
            $item->save();
        }
    }
    private function trackOrderStatus($order, $status)
    {
        $orderTracking = new OrderTracking;
        $orderTracking->order_id = $order->id;
        $orderTracking->order_status = $status;
        $orderTracking->created_by = Auth::guard('admin')->user()->id;
        $orderTracking->time = now()->format('H:i:s');
        $orderTracking->save();
    }
    private function orderTransactionStatus($order, $status)
    {
        $OrderTransaction =  OrderTransaction::where('order_id', $order->id)->first();
        $OrderTransaction->payment_status = 'paid';
        $OrderTransaction->paid_at = date('Y-m-d H:i:s');
        $OrderTransaction->modify_by = Auth::guard('admin')->user()->id;
        $OrderTransaction->save();
    }
    function changeStatusQr($id)
    {
        $order_tracking = new  OrderTracking;
        $order_tracking->order_id = $id;
        $order_tracking->order_status = 'delivered';
        $order_tracking->created_by = Auth::guard('admin')->user()->id;
        $order_tracking->time = date('H:i a');
        $order_tracking->save();

        $order_tracking = new  OrderTracking;
        $order_tracking->order_id = $id;
        $order_tracking->order_status = 'completed';
        $order_tracking->created_by = Auth::guard('admin')->user()->id;
        $order_tracking->time = date('H:i a');
        $order_tracking->save();

        $order = Order::find($id);
        $order->status = 'completed';
        $order->save();

        // return true;

    }
    public function showInvoice($id)
    {
        $lang = App::getLocale(); // Get the current locale

        $response = $this->orderService->show($lang, $id, $this->checkToken);

        $responseData = $response->original;

        $order = $responseData['data'];

        return view('dashboard.order.invoice', compact('order'));
    }

    public function printReceipt($id, $type)
    {
        $order = Order::with(['orderDetails.dish', 'orderAddons.Addon.addons', 'client', 'address'])->findOrFail($id);

        switch ($type) {
            case 'customer_delivery_print':
                return view('dashboard.order.customer_delivery_print', compact('order'));
            case 'customer_dinein_print':
                return view('dashboard.order.customer_dinein_print', compact('order'));
            case 'customer_takeaway_print':
                return view('dashboard.order.customer_takeaway_print', compact('order'));
            case 'delivery_print':
                return view('dashboard.order.delivery_print', compact('order'));
            default:
                abort(404, 'Invalid print type');
        }
    }

    // public function changeItemStatus(Request $request)
    // {
    //     $detail = OrderDetail::findOrFail($request->order_detail_id);
    //     $detail->status = $request->input('status');
    //     $detail->modify_by = Auth::guard('admin')->user()->id;
    //     $detail->save();

    //     // Update all addons associated with the detail
    //     $detailaddons = OrderAddon::where('order_details_id', $request->order_detail_id)->get();
    //     foreach ($detailaddons as $detailaddon) {
    //         $addon = OrderAddon::find($detailaddon->id);
    //         $addon->status = $request->input('status');
    //         $addon->modify_by = Auth::guard('admin')->user()->id;
    //         $addon->updated_at = now();
    //         $addon->save();
    //     }

    //     $item_calculate = $this->orderService->CalculateItem($request->order_detail_id);
    //     $update_item_calculate = $this->orderService->UpdateCalculateItem($item_calculate);
    //     Log::info('UpdateCalculateItem result', ['result' => $update_item_calculate]);

    //     // // Get the order and all its dishes
    //     // $order = $detail->order;
    //     // $allDishes = $order->orderDetails;

    //     // // Check status conditions
    //     // $allCancelled = true;
    //     // $allCompleted = true;
    //     // $anyInProgress = false;
    //     // $anyCancelled = false;
    //     // $anyPending = false;

    //     // foreach ($allDishes as $dish) {
    //     //     if ($dish->status != 'cancel') {
    //     //         $allCancelled = false;
    //     //     }
    //     //     if ($dish->status != 'completed') {
    //     //         $allCompleted = false;
    //     //     }
    //     //     if ($dish->status == 'inprogress') {
    //     //         $anyInProgress = true;
    //     //     }
    //     //     if ($dish->status == 'cancel') {
    //     //         $anyCancelled = true;
    //     //     }
    //     //     if ($dish->status == 'pending') {
    //     //         $anyPending = true;
    //     //     }
    //     // }

    //     // // Update order status based on dish statuses
    //     // if ($allCancelled) {
    //     //     // All dishes are cancelled - cancel the order
    //     //     CancellationReason::create([
    //     //         'type' => 'admin',
    //     //         'order_id' => $order->id,
    //     //         'reason_id' => $request->reason_id,
    //     //         'reason' => $request->reason,
    //     //         'user_id' => auth('admin')->id(),
    //     //         'created_by' => auth('admin')->id(),
    //     //     ]);

    //     //     $this->trackOrderStatus($order, 'cancelled');
    //     //     $order->status = 'cancelled';
    //     //     $order->print_status = 'cancelled';
    //     // } elseif ($anyInProgress) {
    //     //     // At least one dish is in progress - set order to inprogress
    //     //     $this->trackOrderStatus($order, 'in_progress');
    //     //     $order->status = 'inprogress';
    //     // } elseif ($allCompleted || ($anyCancelled && !$anyPending && !$anyInProgress)) {
    //     //     // All items completed or some cancelled with no pending/in-progress items
    //     //     $this->trackOrderStatus($order, 'readyForPickup');
    //     //     $order->status = 'packing';

    //     //     // Dispatch ready for pickup event if order is Takeaway
    //     //     if ($order->type === 'Takeaway') {
    //     //         OrderReadyForPickup::dispatch($order, app()->getLocale());
    //     //     }
    //     // }

    //     // $order->save();
    //     // $order->refresh();

    //     // Log::info('Order after refresh:', [
    //     //     'total_price_after_tax' => $order->total_price_after_tax,
    //     //     'original_total' => $order->getOriginal('total_price_after_tax')
    //     // ]); // Add this line

    //     // return response()->json([
    //     //     'success' => true,
    //     //     'message' => __('order.status_updated'),
    //     //     'total_price_after_tax' => $order->total_price_after_tax,
    //     //     'previous_total' => $order->getOriginal('total_price_after_tax')
    //     // ]);
    // }
    public function changeItemStatus(Request $request)
    {
        $detail = OrderDetail::findOrFail($request->order_detail_id);
        $detail->status = $request->input('status');
        $detail->modify_by = Auth::guard('admin')->user()->id;
        $detail->save();

        // Update all addons associated with the detail
        $detailaddons = OrderAddon::where('order_details_id', $request->order_detail_id)->get();
        foreach ($detailaddons as $detailaddon) {
            $addon = OrderAddon::find($detailaddon->id);
            $addon->status = $request->input('status');
            $addon->modify_by = Auth::guard('admin')->user()->id;
            $addon->updated_at = now();
            $addon->save();
        }

        // Recalculate the order totals after status changes
        $item_calculate = $this->orderService->CalculateItem($request->order_detail_id, $detail->quantity);
        $update_item_calculate = $this->orderService->UpdateCalculateItem($item_calculate);
        Log::info('UpdateCalculateItem result', ['result' => $update_item_calculate]);

        // Get the order and all its dishes
        $order = $detail->order;

        // Recalculate the ENTIRE order totals
        $calculate_order_details = $this->orderService->CalculateOrderWithStatus($order->id, ['pending', 'inprogress', 'completed']);
        $this->orderService->UpdateCalculateOrder($calculate_order_details);

        // Refresh the order data from database
        $order->refresh();

        // Initialize refund variables
        $totalRefund = 0;
        $refundData = null;
        $hasUnpaidTransaction = $order->orderTransactions->contains(function ($transaction) {
            return $transaction->payment_status === 'paid';
        });

        // Handle payment transaction if needed
        if ($hasUnpaidTransaction) {
            try {
                $refundData = $this->orderService->CalculateRefundOrder($order->id);
                $totalRefund = $refundData['refund_summary']['total_refund'];

                // dd($totalRefund);
                $this->orderService->storePaymentTransaction(
                    $order->id,
                    $order->type,
                    $request->payment_method ?? 'cash',
                    Auth::guard('admin')->user()->id,
                    null,
                    $totalRefund,
                    null,
                    $order->make_type,
                    'paid',
                    1
                );
            } catch (\Exception $e) {
                Log::error('Payment transaction update failed: ' . $e->getMessage());
            }
        }

        // Check status conditions
        $statusChecks = [
            'allCancelled' => true,
            'allCompleted' => true,
            'anyInProgress' => false,
            'anyCancelled' => false,
            'anyPending' => false
        ];

        foreach ($order->orderDetails as $dish) {
            $statusChecks['allCancelled'] = $statusChecks['allCancelled'] && ($dish->status == 'cancel');
            $statusChecks['allCompleted'] = $statusChecks['allCompleted'] && ($dish->status == 'completed');
            $statusChecks['anyInProgress'] = $statusChecks['anyInProgress'] || ($dish->status == 'inprogress');
            $statusChecks['anyCancelled'] = $statusChecks['anyCancelled'] || ($dish->status == 'cancel');
            $statusChecks['anyPending'] = $statusChecks['anyPending'] || ($dish->status == 'pending');
        }

        // Update order status
        if ($statusChecks['allCancelled']) {
            try {
                CancellationReason::firstOrCreate(
                    ['order_id' => $order->id],
                    [
                        'type' => 'admin',
                        'reason_id' => $request->reason_id,
                        'reason' => $request->reason,
                        'user_id' => auth('admin')->id(),
                        'created_by' => auth('admin')->id()
                    ]
                );

                $this->trackOrderStatus($order, 'cancelled');
                $order->status = 'cancelled';
                $order->print_status = 'cancelled';
            } catch (\Exception $e) {
                Log::error('Cancellation failed: ' . $e->getMessage());
            }
        } elseif ($statusChecks['anyInProgress']) {
            $this->trackOrderStatus($order, 'in_progress');
            $order->status = 'inprogress';
        } elseif ($statusChecks['allCompleted'] || ($statusChecks['anyCancelled'] && !$statusChecks['anyPending'] && !$statusChecks['anyInProgress'])) {
            $this->trackOrderStatus($order, 'readyForPickup');
            $order->status = 'packing';
            if ($order->type === 'Takeaway') {
                OrderReadyForPickup::dispatch($order, app()->getLocale());
            }
        }

        $order->save();

        return response()->json([
            'success' => true,
            'message' => __('order.status_updated'),
            'total_price_after_tax' => $order->total_price_after_tax,
            'previous_total' => $order->getOriginal('total_price_after_tax'),
            'total_refund' => $totalRefund,
            'refund_data' => $refundData // Optional: include full refund details if needed
        ]);
    }
    // public function changeItemStatus(Request $request)
    // {

    //     $detail = OrderDetail::findOrFail($request->order_detail_id);
    //     $detail->status = $request->input('status');
    //     $detail->modify_by = Auth::guard('admin')->user()->id;
    //     $detail->save();

    //     // Update all addons associated with the detail
    //     $detailaddons = OrderAddon::where('order_details_id', $request->order_detail_id)->get();
    //     foreach ($detailaddons as $detailaddon) {
    //         $addon = OrderAddon::find($detailaddon->id);
    //         $addon->status = $request->input('status');
    //         $addon->modify_by = Auth::guard('admin')->user()->id;
    //         $addon->updated_at = now();
    //         $addon->save();
    //     }
    //     // ⬇️ Recalculate the order totals after status changes
    //     $item_calculate = $this->orderService->CalculateItem($request->order_detail_id);
    //     $update_item_calculate = $this->orderService->UpdateCalculateItem($item_calculate);
    //     Log::info('UpdateCalculateItem result', ['result' => $update_item_calculate]);

    //     // Get the order and all its dishes
    //     $order = $detail->order;

    //     // Recalculate the ENTIRE order totals
    //     $calculate_order_details = $this->orderService->CalculateOrderWithStatus($order->id, ['pending', 'inprogress', 'completed']);
    //     $this->orderService->UpdateCalculateOrder($calculate_order_details);

    //     // Refresh the order data from database
    //     $order->refresh();

    //     // Check status conditions
    //     $statusChecks = [
    //         'allCancelled' => true,
    //         'allCompleted' => true,
    //         'anyInProgress' => false,
    //         'anyCancelled' => false,
    //         'anyPending' => false
    //     ];

    //     foreach ($order->orderDetails as $dish) {
    //         $statusChecks['allCancelled'] = $statusChecks['allCancelled'] && ($dish->status == 'cancel');
    //         $statusChecks['allCompleted'] = $statusChecks['allCompleted'] && ($dish->status == 'completed');
    //         $statusChecks['anyInProgress'] = $statusChecks['anyInProgress'] || ($dish->status == 'inprogress');
    //         $statusChecks['anyCancelled'] = $statusChecks['anyCancelled'] || ($dish->status == 'cancel');
    //         $statusChecks['anyPending'] = $statusChecks['anyPending'] || ($dish->status == 'pending');
    //     }

    //     // Update order status
    //     if ($statusChecks['allCancelled']) {
    //         try {
    //             CancellationReason::firstOrCreate(
    //                 ['order_id' => $order->id],
    //                 [
    //                     'type' => 'admin',
    //                     'reason_id' => $request->reason_id,
    //                     'reason' => $request->reason,
    //                     'user_id' => auth('admin')->id(),
    //                     'created_by' => auth('admin')->id()
    //                 ]
    //             );

    //             $this->trackOrderStatus($order, 'cancelled');
    //             $order->status = 'cancelled';
    //             $order->print_status = 'cancelled';
    //         } catch (\Exception $e) {
    //             Log::error('Cancellation failed: ' . $e->getMessage());
    //         }
    //     } elseif ($statusChecks['anyInProgress']) {
    //         $this->trackOrderStatus($order, 'in_progress');
    //         $order->status = 'inprogress';
    //     } elseif ($statusChecks['allCompleted'] || ($statusChecks['anyCancelled'] && !$statusChecks['anyPending'] && !$statusChecks['anyInProgress'])) {
    //         $this->trackOrderStatus($order, 'readyForPickup');
    //         $order->status = 'packing';
    //         if ($order->type === 'Takeaway') {
    //             OrderReadyForPickup::dispatch($order, app()->getLocale());
    //         }
    //     }

    //     $order->save();

    //     // Handle payment transaction if needed
    //     if ($order->relationLoaded('transaction') && $order->transaction && $order->transaction->paid) {
    //         try {
    //             $totals = $this->orderService->CalculateRefundOrder($order->id);

    //             $this->orderService->storePaymentTransaction(
    //                 $order->id, // Use order->id instead of request->order_id
    //                 $order->type,
    //                 $request->payment_method ?? 'cash', // Default payment method
    //                 Auth::guard('admin')->user()->id,
    //                 null, // transaction_id (will generate if null)
    //                 $totals['refund_summary']['total_refund'],
    //                 null, // coupon_id
    //                 $order->make_type,
    //                 'paid',
    //                 1 // is_refund
    //             );
    //         } catch (\Exception $e) {
    //             Log::error('Payment transaction update failed: ' . $e->getMessage());
    //         }
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => __('order.status_updated'),
    //         'total_price_after_tax' => $order->total_price_after_tax,
    //         'previous_total' => $order->getOriginal('total_price_after_tax')
    //     ]);
    // }
    public function changeAddonStatus(Request $request)
    {
        // $request->validate([
        //     'status' => 'required|in:pending,processing,completed,canceled',
        // ]);

        $addon = OrderAddon::findOrFail($request->order_addon_id);
        $addon->status = $request->input('status');
        $addon->save();

        return true;
    }
    public function downloadOrder($id)
    {
        $lang = App::getLocale(); // Get the current locale
        $response = $this->orderService->show($lang, $id, $this->checkToken);
        $responseData = $response->original;
        $order = $responseData['data'];
        $URL = URL::to('/');
        $order['qr'] = QrCode::format('png')->size(80)->errorCorrection('H')->generate(route('order.change.status', $order->id));
        $order['qr'] = base64_encode($order['qr']);  // base64 encoding the PNG image

        // Generate QR code as a string (SVG format)
        // $order['site_logo'] = $URL . '/build/assets/images/brand-logos/desktop.png';
        $order['site_logo'] = asset('build/assets/images/brand-logos/desktop.png');

        // Load the PDF view with the order and QR code
        $pdf = Pdf::loadView('dashboard.order.pdf', compact('order'));
        return $pdf->download($order->order_number . '.pdf');

        // Stream the generated PDF (you can also download it using download() method)
        // return $pdf->stream();
    }


    // public function downloadOrder($id)
    // {
    //     $lang = App::getLocale(); // Get the current locale

    //     $response = $this->orderService->show($lang, $id, $this->checkToken);
    //     $responseData = $response->original;
    //     $order = $responseData['data'];
    //     $URL = URL::to('/');

    //     // Generate QR code as a string (image source) and attach it to the order array

    //     $order['qr'] = base64_encode(QrCode::format('svg')->size(80)->errorCorrection('H')->generate(route('order.change.status', $order->id)));
    //     $order['site_logo'] = $URL . '/build/assets/images/brand-logos/desktop.png';
    //     // Load the PDF view with the order and QR code
    //     $pdf = Pdf::loadView('dashboard.order.pdf', compact('order'));

    //     // Stream the generated PDF (you can also download it using download() method)
    //     // return $pdf->download($order->order_number. '.pdf');
    //     return $pdf->stream();
    // }

    // public function delete(Request $request, $id)
    // {
    //     $response = $this->orderService->destroy($request, $id);
    //     $responseData = $response->original;
    //     $message= $responseData['message'];
    //     return redirect('branches')->with('message',$message);
    // }

    public function changeOrderTable(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('admin')->user();
        $created_by = $employee->id;

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'table_id' => 'required|exists:tables,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', __('validation.dataNotFound'));
        }

        $result = $this->orderService->changeOrderTable($request->table_id, $request->order_id);

        if ($result === true) {
            return redirect()->back()->with('success', $lang == 'en' ? 'Order table changed successfully' : 'تم تغير طاوله الطلب بنجاح');
        }

        if ($result instanceof \Illuminate\Http\JsonResponse) {
            $data = $result->getData(true);
            return redirect()->back()->with('error', $data['message'] ?? __('validation.cannotchange'));
        }


        return redirect()->back()->with('error', __('validation.cannotchange'));
    }
}
