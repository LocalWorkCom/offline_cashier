<?php

namespace App\Http\Controllers\Dashboard;

use App\Events\CashierNotify;
use App\Events\WaiterNotify;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Einvoice;
use App\Models\Employee;
use App\Models\Gift;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderTracking;
use App\Models\OrderTransaction;
use App\Models\User;
use App\Models\WaiterRequest;
use App\Services\ClientServices\InvoiceService;
use App\Services\SettingsServices\GiftService;
use App\Services\ClientServices\OrderService;
use App\Services\SettingsServices\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class waiterRequestCotroller extends Controller
{
    protected $orderService;
    protected $couponService;

    protected $InvoiceService;
    protected $lang;
    protected $checkToken;  // Set to true or false based on your need
    public function __construct(OrderService $orderService, CouponService $couponService, InvoiceService $invoiceService)
    {
        $this->orderService = $orderService;
        $this->couponService = $couponService;
        $this->InvoiceService = $invoiceService;
        $this->lang =  app()->getLocale();
        $this->checkToken = false;
    }

    public function index(Request $request)
    {
        $user_id = Auth::guard('admin')->user()->id;

        // Retrieve waiter requests
        $requests = WaiterRequest::with(['employee', 'tables'])
            ->where('user_id', $user_id)
            ->orderByDesc('created_at')
            ->get();
        return view('dashboard.requests.index', compact('requests'));
    }
    public function ajaxPrint(Request $request)
    {
        $orderId = $request->order_id;
        $lang = App::getLocale();

        $response = $this->orderService->show($lang, $orderId, $this->checkToken);

        // Get the order data from the response
        $order = $response->getData()->data;
        $order->details = collect($order->details);

        // Add logic for split/merge if not already in service
        $order->is_split = $order->parent_id !== null;
        $order->is_merged = isset($order->invoice_type) && !empty($order->invoice_type);
        $order->split_orders = \App\Models\Order::where('parent_id', $order->id)->get(); // optional
        $order->original_order_ids = isset($order->invoice_type)
            ? explode(',', $order->invoice_type)
            : [];

        $html = view('dashboard.requests.print', compact('order'))->render();

        return response()->json(['html' => $html]);
    }

    public function showInvoice($id)
    {
        // Fetch the WaiterRequest
        $waiterRequest = WaiterRequest::with(['employee', 'tables', 'coupon'])
            ->where('id', $id)
            ->first(); // Use first() instead of get() to work with a single object
        $notification = Notification::where('url', 'like', "%/invoice/$id")
            ->first();

        if ($notification) {
            // Update status to read (assuming 1 means read)
            Notification::where('id', $notification->id)
                ->update(['status' => 1, 'updated_at' => now()]);
        }
        if (!$waiterRequest) {
            return response()->json(['message' => 'Request not found'], 404);
        }

        // Decode order_ids and order_items_ids
        $originalOrderIds = is_array($waiterRequest->order_ids) ? $waiterRequest->order_ids : json_decode($waiterRequest->order_ids, true);
        $selectedOrderDetailIds = array_map('intval', is_array($waiterRequest->order_items_ids) ? $waiterRequest->order_items_ids : json_decode($waiterRequest->order_items_ids, true));

        // If split and accepted, include both original and new order
        $orderIds = $originalOrderIds;
        if (($waiterRequest->type == 0 || $waiterRequest->type == 1) && $waiterRequest->status == 1 && $waiterRequest->new_order_id) {
            $orderIds[] = $waiterRequest->new_order_id;
        }
        // dd($orderIds);
        // Fetch orders
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


        // Annotate details
        $orders->each(function ($order) use ($selectedOrderDetailIds) {
            $order->orderDetails->each(function ($detail) use ($selectedOrderDetailIds) {
                $detail->selected    = in_array((int)$detail->id, $selectedOrderDetailIds, true); // Ensure integer comparison
                $detail->dish_name   = optional($detail->dish)->name_site;
                $detail->size_name   = optional($detail->dishSize)->name_site;
                $detail->addons_list = collect($detail->dishAddons)->map(fn($addon) => [
                    'id'    => $addon->addon->id ?? null,
                    'name'  => $addon->addon->addons->name_site ?? '',
                    'price' => $addon->price_after_tax ?? 0,
                ]);
            });
        });
        // Find the order with the same table_id for client phone
        $orderWithTable = $orders->firstWhere('table_id', $waiterRequest->table_id);
        $clientPhone = optional($orderWithTable)->client_phone ?? 'N/A';
        $symbole = Employee::find(getEmployeeID())->branch->country->currency_symbol;

        // Send data to view
        $data = [
            'waiter_request' => $waiterRequest,
            'orders' => $orders,
            'client_phone' => $clientPhone,
            'coupon' => $waiterRequest->coupon,
            'symbole' => $symbole
        ];


        return view('dashboard.requests.detail', compact('data'));
    }

    public function rejectRequest(Request $request)
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

        return redirect()->route('waiterrequest.list');
    }

    public function acceptRequest($id)
    {
        $waiterRequest = WaiterRequest::find($id);

        if (!$waiterRequest) {
            return redirect()->back()->with('error', __('order.RequestNotFound'));
        }

        $branchId = $waiterRequest->employee->branch_id;
        $cashiers = getEmployeesForNotify($branchId, now(),  'cashier');


        if ($cashiers->isEmpty()) {
            return redirect()->back()->with('error', __('order.nocashiersworknowinbranch'));
        }

        $newOrderId = null;
        $newInvoiceId = null;

        switch ($waiterRequest->type) {
            case 0: // Split
                $split = $this->divideInvoice($id);
                if (!$split) {
                    return redirect()->back()->with('error', __('order.OrderMustHaveMultipleItems'));
                }
                $newOrderId = $split['new_order_id'];
                $newInvoiceId = $split['new_invoice_id'];

                break;

            case 1: // Merge
                $merge = $this->mergeInvoices($id);
                if (!$merge) {
                    return redirect()->back()->with('error', __('order.Somethingwronginmerge'));
                }
                $newOrderId = $merge['new_order']->id;
                $newInvoiceId = $merge['new_invoice_id'];
                break;

            case 2: // Accept existing order
                $newOrderId = $waiterRequest->order_ids;
                $newInvoiceId = null;
                break;

            default:
                return redirect()->back()->with('error', __('order.InvalidRequestType'));
        }

        $waiterRequest->status = 1;
        $waiterRequest->new_order_id = $newOrderId;
        $waiterRequest->save();

        $Order = Order::with(
            'orderDetails',
            'orderDetailsWithoutCancel.dish',
            'Table',
            'orderDetails.dish'
        )->where('id', $newOrderId)->first();

        if (!$Order) {
            return redirect()->back()->with('error', __('recipes.OrderNotFound'));
        }

        $maxDishTime = $Order->orderDetailsWithoutCancel->max(
            fn($detail) => $detail->dish->time ?? 0
        );
        if ($newInvoiceId) {

              $Order = Order::with('transaction', 'orderDetails', 'orderDetailsWithoutCancel.dish', 'table', 'orderDetails.dish')->find($Order->id);
            $maxDishTime = $Order->orderDetailsWithoutCancel->max(fn($detail) => $detail->dish->time ?? 0);
            $invoice = Invoice::where('order_id', $Order->id)->first();

             $data = [
                'invoice_id' => $invoice->id,
                'invoice_type' => $invoice->invoice_type,

                "invoice_number" => $Order->invoice_number,
                                'table_number' => ($Order->type == 'dine-in') ? $Order->table?->table_number : null,

                "invoice_print_status" => $Order->print_status,
                "order_id" =>  $Order->id,
                "order_type" => $Order->type,
                "order_number" => $Order->order_number,
                "order_items_count" =>  $Order->orderDetails->count(),
                "order_time" => $maxDishTime,
                'print_count' => $Order->print_count_cashier ?? 0,

                'payment_status' => $invoice->status ?? null,
            ];

            foreach ($cashiers as $cashier) {
                addNotification(
                    'invoice',
                    'cashier',
                    'يوجد فاتورة جديدة',
                    'New invoice',
                    'يوجد فاتورة جديدة',
                    'New invoice',
                    $cashier->id,
                    null,
                    $this->lang,
                    $newInvoiceId
                );
                broadcast(new CashierNotify($cashier->id, $data));
            }
        }

        return redirect()->route('waiterrequest.list');
    }


    public function divideInvoice($id)
    {
        DB::beginTransaction();

        // try {
        $request_waiter = WaiterRequest::find($id);
        $parent_order = Order::withCount('orderDetailsWithoutCancel')
            ->whereIn('id', $request_waiter->order_ids)
            ->first();

        if (!$parent_order) {
            return false;
        }

        // Check if the order has more than one item
        if ($parent_order->orderDetailsWithoutCancel->count() <= 1) {
            return false;
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

        // Split phone number into country code and actual number
        $parts = explode(' ', $request_waiter->phone, 2);
        $countryCode = $parts[0] ?? null;
        $phoneNumber = $parts[1] ?? null;
        $baseId = GetNextID('orders', $parent_order->make_type);
        do {
            $orderNumber = getNewOrderNumber(
                $parent_order->make_type,
                $baseId,
                $parent_order->branch_id
            );
            $baseId++;

            // $orderNumberExists = Order::where('order_number', "#{$orderNumber}")->exists();
            $invoiceNumberExists = Order::where('invoice_number', "INV-{$orderNumber}")->exists();
        } while ($invoiceNumberExists);
        $branchId = $parent_order->branch_id;
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
        // Process Order Details (No Quantity Update)
        foreach ($orderItems as $detail) {
            $orderDetail = OrderDetail::find($detail);

            if ($orderDetail) {
                // Move entire order detail to the new order
                $orderDetail->update(['order_id' => $newOrder->id]);

                // Move add-ons to the new order
                $orderAddons = OrderAddon::where('order_details_id', $orderDetail->id)->get();
                foreach ($orderAddons as $orderAddon) {
                    $orderAddon->update(['order_id' => $newOrder->id, 'order_details_id' => $orderDetail->id]);
                }
            }
        }
        // Recalculate Order Totals
        $this->orderService->CalculateOrder($request_waiter->order_ids[0]);
        $this->orderService->CalculateOrder($newOrder->id);
        $newOrder->refresh();
        $parent_order->refresh();
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
                    $oldTransaction->save();
                }
            }
        }
        // Track new order
        if ($newOrder) {
            $oldTransaction->update(['paid' => $parent_order->total_price_after_tax]);
            $this->InvoiceService->editInvoice($parent_order->id);

            $new_invoice = $this->InvoiceService->makeInvoice(
                $newOrder->id,
                "order",
                'invoice',
                0,
                0,
                null,
                $newOrder->orderDetails()->pluck('id')->toArray(),
                $newOrder->orderAddons()->pluck('id')->toArray()
            );
            $this->orderService->storePaymentTransaction($newOrder->id, $newOrder->type, $oldTransaction->payment_method, $created_by, $request_waiter->coupon_id, $newOrder->total_price_after_tax, 0, $newOrder->make_type,  $oldTransaction->payment_status, 0, null, $new_invoice);

            OrderTracking::create([
                'order_id' => $newOrder->id,
                'created_by' => $newOrder->created_by,
                'order_status' => 'readyForPickup',
            ]);
            // Create Einvoice
            Einvoice::updateOrCreate([
                "invoice_id" => $new_invoice,
                "invoice_type" => 'i'
            ]);
        }

        DB::commit();

        // Return Response
        $data = [
            'original_order_id' => $parent_order->id,
            'new_order_id' => $newOrder->id,
            'new_invoice_id' => $new_invoice
        ];

        return $data;
    }

    public function mergeInvoices($id)
    {
        DB::beginTransaction();

        // try {
        $request_waiter = WaiterRequest::find($id);
        $selectedOrders = Order::whereIn('id', $request_waiter->order_ids)->get();
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

        // Now you can safely use:
        $oldTransaction = OrderTransaction::where('order_id', $selectedOrders->first()->id)->first();
        $parts = explode(' ', $request_waiter->phone, 2);
        $countryCode = $parts[0] ?? null;
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
        $newOrder->waiter_id =  $waiter_id;
        $newOrder->cashier_id =  $selectedOrders->first()->cashier_id ?? null;
        $newOrder->coupon_id =  $request_waiter->coupon_id;
        $newOrder->customer_service_id =  $selectedOrders->first()->customer_service_id ?? null;
        $newOrder->cashier_machine_id =  $selectedOrders->first()->cashier_machine_id ?? null;
        $newOrder->service_fees = 0;
        $newOrder->tax_percentage =  $selectedOrders->first()->tax_percentage ?? null;
        $newOrder->service_percentage =  $selectedOrders->first()->service_percentage ?? null;

        $newOrder->invoice_type = 'merge';
        $newOrder->save();
        $orderItems = json_decode($request_waiter->order_items_ids, true); // true returns associative array

        // Process Order Details
        foreach ($orderItems as $detail) {
            $orderDetail = OrderDetail::find($detail);

            if ($orderDetail) {
                $orderDetail->update(['order_id' => $newOrder->id]);

                // Move add-ons to the new order
                $orderAddons = OrderAddon::where('order_details_id', $orderDetail->id)->get();
                foreach ($orderAddons as $orderAddon) {
                    $orderAddon->update(['order_id' => $newOrder->id]);
                }
            }
        }

        Order::whereIn('id', $request_waiter->order_ids)->update(['parent_id' => $newOrder->id]);

        // Recalculate prices for the new order
        $this->orderService->CalculateOrder($newOrder->id);

        // Recalculate prices for the original orders
        foreach ($request_waiter->order_ids as $orderId) {
            $dd = $this->orderService->CalculateOrder($orderId);
            $order = Order::find($orderId);
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
                        $order->refresh();
                    }
                }
            }
            OrderTransaction::where('order_id', $orderId)->update(['paid' => $order->total_price_after_tax]);
            $this->InvoiceService->editInvoice($order->id);
        }
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

        if ($newOrder) {
            $new_invoice = $this->InvoiceService->makeInvoice(
                $newOrder->id,
                "order",
                'invoice',
                0,
                0,
                null,
                $newOrder->orderDetails()->pluck('id')->toArray(),
                $newOrder->orderAddons()->pluck('id')->toArray()
            );
            $this->orderService->storePaymentTransaction($newOrder->id, $newOrder->type, $oldTransaction->payment_method, $created_by, $request_waiter->coupon_id, $newOrder->total_price_after_tax, 0, $newOrder->make_type,  $oldTransaction->payment_status, 0,null, $new_invoice);

            $orderTracking = new OrderTracking();
            $orderTracking->order_id = $newOrder->id;
            $orderTracking->created_by = $newOrder->created_by;
            $orderTracking->order_status = 'readyForPickup';
            $orderTracking->save();
            // Create Einvoice
            Einvoice::updateOrCreate([
                "invoice_id" =>  $new_invoice,
                "invoice_type" => 'i'
            ]);
        }

        DB::commit();

        $originalOrdersData = [];
        foreach ($request_waiter->order_ids as $orderId) {
            $order = Order::with(['orderDetails.dishAddons'])->find($orderId);
            if ($order) {
                $originalOrdersData[] = $order;
            }
        }

        $newOrderData = Order::with(['orderDetails.dishAddons'])->find($newOrder->id);

        $data = [
            'new_order' => $newOrderData,
            'original_orders' => $originalOrdersData,
            'new_invoice_id' => $new_invoice
        ];

        return $data;
    }
}
