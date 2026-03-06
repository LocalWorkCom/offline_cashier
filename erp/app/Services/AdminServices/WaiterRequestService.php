<?php

namespace App\Services\AdminServices;

use App\Events\CashierNotify;
use App\Events\dishChangeStatus;
use App\Events\dishChangeStatus2;
use App\Events\WaiterNotify;
use App\Models\Coupon;
use App\Models\Einvoice;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\InvoiceDetails;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderTracking;
use App\Models\OrderTransaction;
use App\Models\User;
use App\Models\WaiterRequest;
use App\Services\ClientServices\InvoiceService;
use App\Services\ClientServices\OrderService;
use App\Services\SettingsServices\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WaiterRequestService
{
    protected $orderService;
    protected $couponService;
    protected $invoiceService;

    public function __construct(OrderService $orderService, CouponService $couponService, InvoiceService $invoiceService)
    {
        $this->orderService = $orderService;
        $this->couponService = $couponService;
        $this->invoiceService = $invoiceService;
    }

    public function index($user)
    {
        $requests = WaiterRequest::with(['employee', 'tables'])
            ->where('employee_id', $user->id)->orWhere('branch_id', $user->branch_id)
            ->orderByDesc('created_at');

        return  $requests;
    }

    public function showInvoice($id)
    {
        // Fetch the WaiterRequest
        $waiterRequest = WaiterRequest::with(['employee', 'tables', 'coupon'])
            ->where('id', $id)
            ->first();

        if (!$waiterRequest) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Waiter Request not found");
        }
        // Mark related notification as read
        $notification = Notification::where('url', 'like', "%/invoice/$id")->first();
        if ($notification) {
            $notification->update(['status' => 1, 'updated_at' => now()]);
        }

        // Decode order IDs and order item IDs
        $originalOrderIds = is_array($waiterRequest->order_ids)
            ? $waiterRequest->order_ids
            : json_decode($waiterRequest->order_ids, true);

        $selectedOrderDetailIds = array_map('intval', is_array($waiterRequest->order_items_ids)
            ? $waiterRequest->order_items_ids
            : json_decode($waiterRequest->order_items_ids, true));

        // If split/merge request is accepted → include new order too
        $orderIds = $originalOrderIds;
        if (
            ($waiterRequest->type == 0 || $waiterRequest->type == 1)
            && $waiterRequest->status == 1
            && $waiterRequest->new_order_id
        ) {
            $orderIds[] = $waiterRequest->new_order_id;
        }

        // Fetch related orders
        $orders = Order::whereIn('id', $orderIds)
            ->with([
                'orderDetails' => fn($q) => $q->where('status', '!=', 'cancel'),
                'orderDetails.dish',
                'orderDetails.dishSize',
                'orderDetails.dishAddons.addon',
                'orderAddons',
                'table',
                'cashier',
                'coupon',
            ])
            ->get();

        // Annotate order details
        $orders->each(function ($order) use ($selectedOrderDetailIds) {
            $order->orderDetails->each(function ($detail) use ($selectedOrderDetailIds) {
                $detail->selected = in_array((int) $detail->id, $selectedOrderDetailIds, true);
                $detail->dish_name = optional($detail->dish)->name_site;
                $detail->size_name = optional($detail->dishSize)->name_site;
                $detail->addons_list = collect($detail->dishAddons)->map(fn($addon) => [
                    'id'    => $addon->addon->id ?? null,
                    'name'  => $addon->addon->addons->name_site ?? '',
                    'price' => $addon->price_after_tax ?? 0,
                ]);
            });
        });

        // Find client phone from an order at same table
        $orderWithTable = $orders->firstWhere('table_id', $waiterRequest->table_id);
        $clientPhone = optional($orderWithTable)->client_phone ?? 'N/A';
        $guard = getAuthenticatedGuard();

        if ($guard === 'admin') {
            $employee = Employee::find(getEmployeeID());
        } else {
            $employee = auth('employee')->user();
        }

        $symbole = $employee?->branch?->country?->currency_symbol ?? 'ج.م';
        return [
            'waiter_request' => $waiterRequest,
            'orders' => $orders,
            'client_phone' => $clientPhone,
            'coupon' => $waiterRequest->coupon,
            'symbole' => $symbole,
        ];
    }

    public function rejectRequest($request)
    {
        $waiterRequest = WaiterRequest::with('tables')->find($request->request_id);
        $waiterRequest->status = 2;
        $waiterRequest->reason = $request->reason;
        $waiterRequest->save();

        $data = [
            'status' =>  $waiterRequest->status,
            'reason' => $request->reason,
            'request_type' => $waiterRequest->type == 0 ? 'split' : 'merge',
            'order_id' => $waiterRequest->order_ids,
        ];

        broadcast(new WaiterNotify(Employee::find($waiterRequest->created_by), $waiterRequest->tables, $data));

        return $waiterRequest;
    }

    public function acceptRequest($id)
    {
        $lang = App::getLocale();

        $waiterRequest = WaiterRequest::find($id);

        if (!$waiterRequest) {
            return [
                'status' => false,
                'message' => __('order.RequestNotFound'),
            ];
        }

        $branchId = $waiterRequest->branch_id;
        $cashiers = getEmployeesForNotify($branchId, now(), 'cashier');

        if (!$cashiers) {
            return [
                'status' => false,
                'message' => __('order.nocashiersworknowinbranch'),
            ];
        }

        $newOrderId = null;
        $newInvoiceId = null;

        switch ($waiterRequest->type) {
            case 0: // Split
                $split = $this->divideInvoice($id);
                if (!$split) {
                    return [
                        'status' => false,
                        'message' => __('order.OrderMustHaveMultipleItems'),
                    ];
                }
                $newOrderId = $split['new_order_id'];
                $newInvoiceId = $split['new_invoice_id'];
                break;

            case 1: // Merge
                $merge = $this->mergeInvoices($id);
                if (!$merge) {
                    return [
                        'status' => false,
                        'message' => __('order.Somethingwronginmerge'),
                    ];
                }
                $newOrderId = $merge['new_order']->id;
                $newInvoiceId = $merge['new_invoice_id'];
                break;

            case 2: // Accept existing order
                $newOrderId = $waiterRequest->order_ids;
                $newInvoiceId = null;
                break;

            default:
                return [
                    'status' => false,
                    'message' => __('order.InvalidRequestType'),
                ];
        }

        // update waiter request
        $waiterRequest->status = 1;
        $waiterRequest->new_order_id = $newOrderId;
        $waiterRequest->save();

        $order = Order::with(
            'transaction',
            'orderDetails',
            'orderDetailsWithoutCancel.dish',
            'Table',
            'orderDetails.dish'
        )->find($newOrderId);

        if (!$order) {
            return [
                'status' => false,
                'message' => __('recipes.OrderNotFound'),
            ];
        }
        // $maxDishTime = $order->orderDetailsWithoutCancel->max(
        //     fn($detail) => $detail->dish->time ?? 0
        // );
        $maxDishTime = 0;

        // Check if the relationship exists and has items
        if (
            property_exists($order, 'orderDetailsWithoutCancel') &&
            $order->orderDetailsWithoutCancel instanceof \Illuminate\Database\Eloquent\Collection &&
            $order->orderDetailsWithoutCancel->isNotEmpty()
        ) {

            $validDetails = $order->orderDetailsWithoutCancel->filter(function ($detail) {
                return isset($detail->relations['dish']) || isset($detail->dish);
            });

            if ($validDetails->isNotEmpty()) {
                $maxDishTime = $validDetails->max(function ($detail) {
                    return $detail->dish->time ?? 0;
                });
            }
        }

        if ($newInvoiceId) {

            $invoice = Invoice::where('order_id', $order->id)->first();

            $data = [
                'invoice_id' => $invoice->id,
                'invoice_type' => $invoice->invoice_type,

                "invoice_number" => $order->invoice_number,
                'table_number' => ($order->type == 'dine-in') ? $order->table?->table_number : null,

                "invoice_print_status" => $order->print_status,
                "order_id" =>  $order->id,
                "order_type" => $order->type,
                "order_number" => $order->order_number,
                "order_items_count" =>  $order->orderDetails->count(),
                "order_time" => $maxDishTime,
                'print_count' => $order->print_count_cashier ?? 0,

                'payment_status' => $invoice->status ?? null,
            ];

            foreach ($cashiers as $cashier) {
                addNotification(
                    'invoice',
                    'cashier',
                    'يوجد فاتورة جديدة' . $data['invoice_number'],
                    'New invoice' . $data['invoice_number'],
                    'يوجد فاتورة جديدة',
                    'New invoice',
                    $cashier->id,
                    null,
                    $lang,
                    $newInvoiceId
                );

                broadcast(new CashierNotify($cashier->id, $data));
            }
        }

        return [
            'status' => true,
            'order' => $order,
            'new_order_id' => $newOrderId,
            'new_invoice_id' => $newInvoiceId,
        ];
    }

    // -----------------------------
    // divideInvoice & mergeInvoices
    // -----------------------------

    public function divideInvoice($id)
    {
        DB::beginTransaction();
        $lang = App::getLocale();

        try {
            $request_waiter = WaiterRequest::find($id);
            $parent_order = Order::withCount('orderDetailsWithoutCancel')
                ->whereIn('id', $request_waiter->order_ids)
                ->first();

            if (!$parent_order || $parent_order->orderDetailsWithoutCancel->count() <= 1) {
                return false;
            }
            if ($parent_order->orderTransactions->last()->payment_status == 'paid') {
                return respondError(($lang == 'en' ? 'Order cannot split it already paid' : 'تم دفع الاوردر لا يمكن اتمام عمليه التقسيم بعد الدفع'), 400);
            }
            $orderItems = json_decode($request_waiter->order_items_ids, true) ?? [];

            $remainingItemsCount = OrderDetail::where('order_id', $parent_order->id)
                ->whereNotIn('id', $orderItems)
                ->where('status', '!=', 'cancel')
                ->count();

            if ($remainingItemsCount < 1) {
                return false;
            }

            $waiter_id = $request_waiter->created_by;
            $client_id = User::where('flag', 'unknown')->value('id');
            $created_by = $client_id;

            // Split phone number
            $parts = explode(' ', $request_waiter->phone, 2);
            $countryCode = $parts[0] ?? null;
            $phoneNumber = $parts[1] ?? null;

            // Generate new order/invoice number
            $baseId = GetNextID('orders', $parent_order->make_type);
            do {
                $orderNumber = getNewOrderNumber(
                    $parent_order->make_type,
                    $baseId,
                    $parent_order->branch_id
                );
                $baseId++;
                $invoiceNumberExists = Order::where('invoice_number', "INV-{$orderNumber}")->exists();
            } while ($invoiceNumberExists);

            $orderNumber_parent = $parent_order->order_number;

            $newOrder = Order::create([
                'date' => now()->format('Y-m-d'),
                'time' => now()->addHour()->format('H:i:s'),
                'type' => $parent_order->type,
                'status' => $parent_order->status,
                'note' => $parent_order->note,
                'delivery_fees' => 0,
                'table_id' => $request_waiter->table_id,
                'client_id' => $parent_order->client_id,
                'discount_id' => null,
                'branch_id' => $parent_order->branch_id,
                'client_phone' => $phoneNumber,
                'client_country_code' => $countryCode,
                'coupon_id' => $request_waiter->coupon_id ?? null,
                'created_by' => $created_by,
                'make_type' => $parent_order->make_type,
                'waiter_id' => $parent_order->waiter_id ?? null,
                'cashier_id' => $parent_order->cashier_id ?? null,
                'customer_service_id' => $parent_order->customer_service_id ?? null,
                'cashier_machine_id' => $parent_order->cashier_machine_id ?? null,
                'parent_id' => $parent_order->id,
                'order_number' => $orderNumber_parent,
                'invoice_number' => "INV-{$orderNumber}",
                'tax_application' => $parent_order->tax_application,
                'invoice_type' => 'split',
                'tax_percentage' => $parent_order->tax_percentage,
                'service_percentage' => $parent_order->service_percentage
            ]);

            CountCouponUsage($request_waiter->coupon_id);

            $oldTransaction = OrderTransaction::where('order_id', $parent_order->id)->first();

            // Move order details
            foreach ($orderItems as $detail) {
                $orderDetail = OrderDetail::find($detail);

                if ($orderDetail) {
                    $orderDetail->update(['order_id' => $newOrder->id]);

                    $orderAddons = OrderAddon::where('order_details_id', $orderDetail->id)->get();
                    foreach ($orderAddons as $orderAddon) {
                        $orderAddon->update([
                            'order_id' => $newOrder->id,
                            'order_details_id' => $orderDetail->id
                        ]);
                    }
                }
            }

            // Recalculate totals
            $this->orderService->CalculateOrder($request_waiter->order_ids[0]);
            $this->orderService->CalculateOrder($newOrder->id);

            $newOrder->refresh();
            $parent_order->refresh();

            // Handle coupon validation
            if ($parent_order->coupon_id) {
                $coupon = new Request([
                    'code' => Coupon::find($parent_order->coupon_id)->code,
                    'amount' => $parent_order->total_price_befor_tax,
                    'branch_id' => $parent_order->branch_id
                ]);
                $couponValidationResponse = $this->couponService->isCouponValid($coupon, $parent_order->client_id);
                if (is_object($couponValidationResponse) && isset($couponValidationResponse->original)) {
                    $responseData = $couponValidationResponse->original;
                    if (isset($responseData['status']) && $responseData['status'] === false) {
                        decreaseCouponUsage($parent_order->coupon_id);
                        $parent_order->coupon_id = null;
                        $parent_order->save();
                        $this->orderService->CalculateOrder($parent_order->id);
                        $parent_order->refresh();
                        $oldTransaction->paid = $parent_order->total_price_after_tax;
                        $oldTransaction->original_price = $parent_order->total_price_after_tax;
                        $oldTransaction->save();
                    }
                }
            }

            // Track new order + invoice
            if ($newOrder) {
                $oldTransaction->update(['paid' => $parent_order->total_price_after_tax, 'original_price' => $parent_order->total_price_after_tax]);

                $new_invoice = $this->invoiceService->makeInvoice(
                    $newOrder->id,
                    "order",
                    'invoice',
                    0,
                    0,
                    null,
                    $newOrder->orderDetails()->pluck('id')->toArray(),
                    $newOrder->orderAddons()->pluck('id')->toArray()
                );
                $invoice_parent_id = Invoice::where('order_id', $parent_order->id)->first()->id;
                //update items with new invoice
                InvoiceDetails::whereIn('details_id', $newOrder->orderDetails()->pluck('id')->toArray())->where('invoice_id', $invoice_parent_id)->delete();
                $this->invoiceService->editInvoice($parent_order->id, 1);
                $order_details_with_prices = OrderDetail::with('dishAddons')
                    ->where('order_id', $parent_order->id)
                    ->get()
                    ->map(function ($detail) {
                        // Calculate total addon price
                        $addonsPrice = $detail->dishAddons->sum('price_before_tax');
                        // Final price for this detail
                        // $detail->price = $detail->price_before_tax + $addonsPrice;
                        return [
                            'id' => $detail->id,
                            'total_dish_price' =>  $detail->price_befor_tax + $addonsPrice,
                        ];
                    })
                    ->toArray();
                // Broadcast event for cashier
                $dish_data = [
                    'order_id'   => $parent_order->id,
                    'order_type' => $parent_order->type,
                    'dish_ids'   => array_column($order_details_with_prices, 'id'),
                    'details'    => $order_details_with_prices, // includes id + price
                    'total_price' =>  formatFloat($parent_order->total_price_after_tax),
                    'status'     => 'pending',
                    'date'       => now()->toDateString(),
                ];
                broadcast(new dishChangeStatus2($dish_data));

                // broadcast(new dishChangeStatus($dish_data));
                $this->orderService->storePaymentTransaction(
                    $newOrder->id,
                    $newOrder->type,
                    $oldTransaction->payment_method,
                    $created_by,
                    $request_waiter->coupon_id,
                    $newOrder->total_price_after_tax,
                    0,
                    $newOrder->make_type,
                    $oldTransaction->payment_status,
                    0,
                    null,
                    $new_invoice
                );
                $orderTransactions = OrderTransaction::where('order_id', $newOrder->id)->latest()->first();
                $orderTransactions->original_price = $newOrder->total_price_after_tax;
                $orderTransactions->save();
                Invoice::where('id', $new_invoice)->update(['status' => $oldTransaction->payment_status]);
                OrderTracking::create([
                    'order_id' => $newOrder->id,
                    'created_by' => $newOrder->created_by,
                    'order_status' => 'readyForPickup',
                ]);

                Einvoice::updateOrCreate([
                    "invoice_id" => $new_invoice,
                    "invoice_type" => 'i'
                ]);
            }

            DB::commit();
            $this->orderService->send_notification($newOrder->id, $newOrder->branch_id, 'cashier', $newOrder->type, $created_by, $lang);

            return [
                'original_order_id' => $parent_order->id,
                'new_order_id' => $newOrder->id,
                'new_invoice_id' => $new_invoice
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
    public function mergeInvoices($id)
    {
        $lang = App::getLocale();

        DB::beginTransaction();
        try {
            $request_waiter = WaiterRequest::findOrFail($id);
            $selectedOrders = Order::whereIn('id', $request_waiter->order_ids)->get();
            foreach ($selectedOrders as $order) {
                if ($order->orderTransactions->last()->payment_status == 'paid') {
                    return respondError(($lang == 'en' ? 'Order cannot merge it already paid' : 'تم دفع الاوردر لا يمكن اتمام عمليه الدمج بعد الدفع'), 400);
                }
            }
            $waiter_id = $request_waiter->created_by;
            $client_id = User::where('flag', 'unknown')->value('id');
            $created_by = $client_id;
            $makeType = $selectedOrders->first()->make_type;
            $branchId = $selectedOrders->first()->branch_id;

            $baseId = GetNextID('orders', $makeType);

            do {
                $order_number = getNewOrderNumber($makeType, $baseId, $branchId);
                $baseId++;
                $orderNumberExists = Order::where('order_number', "#{$order_number}")->exists();
                $invoiceNumberExists = Order::where('invoice_number', "INV-{$order_number}")->exists();
            } while ($orderNumberExists || $invoiceNumberExists);

            $oldTransaction = OrderTransaction::where('order_id', $selectedOrders->first()->id)->first();

            $parts = explode(' ', $request_waiter->phone, 2);
            $phoneNumber = $parts[1] ?? null;

            // Create New Parent Order
            $newOrder = new Order();
            $newOrder->date = now()->format('Y-m-d');
            $newOrder->time = now()->format('H:i:s');
            $newOrder->created_by = $created_by;
            $newOrder->cashier_machine_id = $selectedOrders->first()->cashier_machine_id;
            $newOrder->branch_id = $selectedOrders->first()->branch_id;
            $newOrder->status = $selectedOrders->first()->status;
            $newOrder->type = $selectedOrders->first()->type;
            $newOrder->order_number = "#" . $order_number;
            $newOrder->invoice_number = "INV-" . $order_number;
            $newOrder->tax_value = 0;
            $newOrder->delivery_fees = 0;
            $newOrder->total_price_befor_tax = 0;
            $newOrder->total_price_after_tax = 0;
            $newOrder->client_id = $selectedOrders->first()->client_id;
            $newOrder->client_phone = $phoneNumber;
            $newOrder->table_id = $request_waiter->table_id;
            $newOrder->make_type = $selectedOrders->first()->make_type;
            $newOrder->waiter_id = $waiter_id;
            $newOrder->cashier_id = $selectedOrders->first()->cashier_id ?? null;
            $newOrder->coupon_id = $request_waiter->coupon_id;
            $newOrder->customer_service_id = $selectedOrders->first()->customer_service_id ?? null;
            $newOrder->cashier_machine_id = $selectedOrders->first()->cashier_machine_id ?? null;
            $newOrder->service_fees = 0;
            $newOrder->tax_percentage = $selectedOrders->first()->tax_percentage ?? null;
            $newOrder->service_percentage = $selectedOrders->first()->service_percentage ?? null;
            $newOrder->invoice_type = 'merge';
            $newOrder->save();

            // Move Order Items
            $orderItems = json_decode($request_waiter->order_items_ids, true);
            foreach ($orderItems as $detail) {
                $orderDetail = OrderDetail::find($detail);
                if ($orderDetail) {
                    $orderDetail->update(['order_id' => $newOrder->id]);
                    OrderAddon::where('order_details_id', $orderDetail->id)->update(['order_id' => $newOrder->id]);
                }
            }

            Order::whereIn('id', $request_waiter->order_ids)->update(['parent_id' => $newOrder->id]);

            // Recalculate prices
            $this->orderService->CalculateOrder($newOrder->id);

            foreach ($request_waiter->order_ids as $orderId) {
                $order = Order::find($orderId);
                $this->orderService->CalculateOrder($orderId);

                if ($order->coupon_id) {
                    $coupon = new Request([
                        'code' => Coupon::find($order->coupon_id)->code,
                        'amount' => $order->total_price_befor_tax,
                        'branch_id' => $order->branch_id
                    ]);

                    $couponValidationResponse = $this->couponService->isCouponValid($coupon, $order->client_id);

                    if (is_object($couponValidationResponse) && isset($couponValidationResponse->original)) {
                        $responseData = $couponValidationResponse->original;
                        if (isset($responseData['status']) && $responseData['status'] === false) {
                            decreaseCouponUsage($order->coupon_id);
                            $order->coupon_id = null;
                            $order->save();
                            $this->orderService->CalculateOrder($order->id);
                        }
                    }
                }
                $order->refresh();

                // OrderTransaction::where('order_id', $order->id)->update(['paid' => $order->total_price_after_tax, 'original_price' => $order->total_price_after_tax]);
                // $this->invoiceService->editInvoice($order->id);

                $orderoldTransactions =  OrderTransaction::where('order_id', $order->id)->latest()->first();
                $orderoldTransactions->original_price = $order->total_price_after_tax;
                $orderoldTransactions->paid = $order->total_price_after_tax;

                $orderoldTransactions->save();
                $invoice_parent_id = Invoice::where('order_id', $order->id)->first()->id;
                // //update items with new invoice
                InvoiceDetails::whereIn('details_id', $newOrder->orderDetails()->pluck('id')->toArray())->where('invoice_id', $invoice_parent_id)->delete();
                $this->invoiceService->editInvoice($order->id, 1);
                $order_details_with_prices = OrderDetail::with('dishAddons')
                    ->where('order_id', $order->id)
                    ->get()
                    ->map(function ($detail) {
                        // Calculate total addon price
                        $activeAddons = $detail->dishAddons->filter(function ($addon) {
                            // Adjust the condition based on your actual status field/value
                            return !in_array($addon->status, [0, 'cancel']);
                        });

                        // Compute totalBeforeCoupon from active addons only
                        $addonsTotal = $activeAddons->sum(function ($addon) {
                            return $addon->price_before_coupon ?? 0;
                        });                        // Final price for this detail
                        // $detail->price = $detail->price_before_tax + $addonsPrice;
                        return [
                            'id' => $detail->id,
                            'total_dish_price' =>  $detail->price_befor_tax + $addonsTotal,
                        ];
                    })
                    ->toArray();
                // Broadcast event for cashier
                $dish_data = [
                    'order_id'   => $order->id,
                    'order_type' => $order->type,
                    'dish_ids'   => array_column($order_details_with_prices, 'id'),
                    'details'    => $order_details_with_prices, // includes id + price
                    'total_price' =>  formatFloat($order->total_price_after_tax),
                    'status'     => 'pending',
                    'date'       => now()->toDateString(),
                ];
                broadcast(new dishChangeStatus2($dish_data));

                // broadcast(new dishChangeStatus($dish_data));
            }

            // Handle coupon for new merged order
            if ($newOrder->coupon_id) {
                $coupon = new Request([
                    'code' => Coupon::find($newOrder->coupon_id)->value('code'),
                    'amount' => $newOrder->total_price_befor_tax,
                    'branch_id' => $newOrder->branch_id
                ]);
                $couponValidationResponse = $this->couponService->isCouponValid($coupon, $newOrder->client_id);
                if (is_object($couponValidationResponse) && isset($couponValidationResponse->original)) {
                    $responseData = $couponValidationResponse->original;
                    if (isset($responseData['status']) && $responseData['status'] === true) {
                        $this->orderService->CalculateOrder($newOrder->id);
                        CountCouponUsage($newOrder->coupon_id);
                    }
                }
            }

            $newOrder->refresh();
            // Store Payment Transaction
            $this->orderService->storePaymentTransaction(
                $newOrder->id,
                $newOrder->type,
                $oldTransaction->payment_method,
                $created_by,
                $request_waiter->coupon_id,
                $newOrder->total_price_after_tax,
                0,
                $newOrder->make_type,
                $oldTransaction->payment_status,
                0,
                null,
                null
            );
            $orderTransactions = OrderTransaction::where('order_id', $newOrder->id)->latest()->first();
            $orderTransactions->original_price = $newOrder->total_price_after_tax;
            $orderTransactions->save();
            // Create invoice

            $new_invoice = $this->invoiceService->makeInvoice(
                $newOrder->id,
                "order",
                'invoice',
                0,
                0,
                null,
                $newOrder->orderDetails()->pluck('id')->toArray(),
                $newOrder->orderAddons()->pluck('id')->toArray()
            );

            $Transaction = OrderTransaction::where('order_id', $newOrder->id)->update(['invoice_id' => $new_invoice]);

            // Add Order Tracking
            $orderTracking = new OrderTracking();
            $orderTracking->order_id = $newOrder->id;
            $orderTracking->created_by = $newOrder->created_by;
            $orderTracking->order_status = 'readyForPickup';
            $orderTracking->save();

            Einvoice::updateOrCreate([
                "invoice_id" =>  $new_invoice,
                "invoice_type" => 'i'
            ]);

            DB::commit();

            // Collect Data
            $originalOrdersData = [];
            foreach ($request_waiter->order_ids as $orderId) {
                $order = Order::with(['orderDetails.dishAddons'])->find($orderId);
                if ($order) {
                    $originalOrdersData[] = $order;
                }
            }

            $newOrderData = Order::with(['orderDetails.dishAddons'])->find($newOrder->id);
            $this->orderService->send_notification($newOrder->id, $branchId, 'cashier', $newOrder->type, $created_by, $lang);

            return [
                'new_order' => $newOrderData,
                'original_orders' => $originalOrdersData,
                'new_invoice_id' => $new_invoice
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function ajaxPrint($orderId, $response)
    {
        $order = $response->getData()->data;
        $order->details = collect($order->details);
        $order->is_split = $order->parent_id !== null;
        $order->is_merged = isset($order->invoice_type) && !empty($order->invoice_type);
        $order->split_orders = Order::where('parent_id', $order->id)->get();

        return $order;
    }
}
