<?php

namespace App\Http\Controllers\Api\WaiterAPIs;

use PDO;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Table;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\Country;
use App\Models\Invoice;
use App\Models\Employee;
use App\Events\EditOrder;
use App\Events\TotalPaid;
use App\Events\NotifySent;
use App\Models\BranchMenu;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Events\CashierNotify;
use App\Models\OrderTracking;
use App\Models\WaiterRequest;
use App\Models\InvoiceDetails;
use App\Models\BranchMenuAddon;
use App\Models\EmployeeMachine;
use Illuminate\Validation\Rule;
use App\Events\dishChangeStatus;
use App\Models\OrderTransaction;
use App\Events\dishChangeStatus2;
use App\Traits\DishCategoryTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Services\ClientServices\OrderService;
use App\Services\HR_Services\TimetableService;
use App\Services\ClientServices\InvoiceService;
use App\Services\SettingsServices\CouponService;
use App\Http\Controllers\Api\CashierAPIs\CashierBalanceController;

class WaiterOrderController extends Controller
{
    protected $orderService;
    protected $CouponService;
    protected $invoiceService;
    protected $timeTableService;
    use DishCategoryTrait;

    public function __construct(OrderService $orderService, TimetableService $timeTableService, CouponService $CouponService, InvoiceService $invoiceService)
    {
        $this->orderService = $orderService;
        $this->CouponService = $CouponService;
        $this->invoiceService = $invoiceService;
        $this->timeTableService = $timeTableService;
    }

    public function listOrders(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $today = Carbon::today();
        $twoDaysAgo = Carbon::now()->subDays(2);

        $shiftDetails = TimetableService::getTimetableForDate($employee->id, $today);
        $ordersQuery = Order::where('branch_id', $employee->branch_id)
            ->where('type', 'dine-in')
            ->where('created_at', '>=', $twoDaysAgo)
            ->whereHas('orderDetails')
            ->when($request->has('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->with(['branch', 'tracking', 'orderDetailsWithoutCancel.dish', 'orderDetailsWithoutCancel.dishAddons', 'table'])
            ->orderBy('updated_at', 'desc')->orderBy('created_at', 'desc');

        if ($shiftDetails['status']) {
            $onDutyTime = $shiftDetails['data']['on_duty_time'];
            $offDutyTime = $shiftDetails['data']['off_duty_time'];

            if ($shiftDetails['data']['cross_day']) {
                $ordersQuery->where(function ($query) use ($onDutyTime, $offDutyTime) {
                    $query->whereTime('created_at', '>=', $onDutyTime)
                        ->orWhereTime('created_at', '<=', $offDutyTime);
                });
            } else {
                $ordersQuery->whereTime('created_at', '>=', $onDutyTime)
                    ->whereTime('created_at', '<=', $offDutyTime);
            }
        }
        $orders = $ordersQuery->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.',
                'data' => null
            ], 200);
        }

        $responseData = $orders->map(function ($order) use ($lang) {
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

            // Calculate service and tax according to the formula
            // service = 12% * subtotal
            $serviceFees = $subtotal * 0.12;
            // tax = 14% * (subtotal + service)
            $taxValue = ($subtotal + $serviceFees) * 0.14;

            return [
                'order_details' => $orderData,
                'order_items' => $orderItems,
                'subtotal_price' => formatFloat($subtotal),
                'subtotal_price_before_coupon' => formatFloat($order->total_price_before_coupon),
                'tax_value' => formatFloat($taxValue),
                'service_fees' => formatFloat($serviceFees),
                'table_number' => $order->table->table_number ?? null,
                'total_price' => formatFloat($totalPrice),
                'payment_method' => $order->transaction?->payment_method,
                'payment_status' => $order->transaction?->payment_status,
                'currency_symbol' => $currencySymbol,
            ];
        });
        $response = [
            'orders' => $responseData,
        ];

        return ResponseWithSuccessData($lang, $response, 1);
    }
    public function orderDetails(Request $request, $orderId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $orders = Order::where('branch_id', $employee->branch_id)
            ->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons', 'orderDetailsWithoutCancel.dish', 'orderDetailsWithoutCancel.dishAddons'])->where('id', $orderId)
            ->where('type', 'dine-in')
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

            // Count all items (including canceled) if order is canceled, otherwise count non-canceled items
            $orderItemsCount = $order->status === 'cancelled'
                ? $order->orderDetails->sum('quantity')
                : $order->orderDetailsWithoutCancel->sum('quantity');

            $orderDetails = $order->orderDetails->map(function ($detail) use ($lang, $order) {
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
                    $addonsTotal = $addons->sum('price_before_coupon');
                    if ($detail->status === 'cancel') {
                        $canceledAddonsTotal = $detail->dishAddons
                            ->where('status', 'cancel')
                            ->sum('price_before_coupon');
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
                $totalBeforeCoupon = $orderDetailTotal + $addonsTotal;

                // Check if dish has coupon_id - only apply coupon if it exists
                $dishCouponId = $detail->coupon_id ?? null;
                $dishCouponValue = $dishCouponId ? ($detail->coupon_value ?? 0) : 0;

                return [
                    'item_id' => $detail->id,
                    'dish_order' => $detail->dish_order,
                    'dish_id' => $detail->dish_id,
                    'dish_name' => $detail->dish->name ?? null,
                    'dish_image' => $detail->dish->image ?? null,
                    'size_id' => $detail->dish_size_id ?? null,
                    'size' => $detail->dish_size_id ?
                        (($lang === 'ar') ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
                        : null,
                    'quantity' => $detail->quantity,
                    'total_dish_price' => formatFloat($totalBeforeCoupon),
                    'total_dish_price_coupon_applied' => formatFloat($total),
                    'note' => $detail->note,
                    'addons' => $detail->dishAddons->map(function ($addon) use ($lang) {
                        return [
                            'addon_category_id' => $addon->Addon->addon_category_id,
                            'addon_id' => $addon->Addon->addon_id,
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
            $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';

            $subtotal = $order->total_price_befor_tax;
            if ($order->tax_application == 1) {
                $subtotal += $order->tax_value;
            }

            return [
                'order_id' => $order->id,
                'order_status' => $order->status,
                'table_id' => $order->table->id ?? null,
                'table_number' => $order->table->table_number ?? null,
                'order_number' => $order->order_number,
                'created_at' => $order->created_at,
                'order_items_count' => $orderItemsCount,
                'order_details' => $orderDetails,
                'subtotal_price' => formatFloat($subtotal),
                'subtotal_price_before_coupon' => formatFloat($order->total_price_before_coupon),
                'tax_value' => formatFloat($order->tax_value ?? null),
                'service_fees' => formatFloat($order->service_fees ?? null),
                'total_price' => formatFloat($order->total_price_after_tax),
                'payment_method' => $order->transaction?->payment_method,
                'payment_status' => $order->transaction?->payment_status,
                'currency_symbol' => $currencySymbol,
                'order_notes' => $order->note,
            ];
        });
        $response = [
            'orderDetails' => $responseData,
        ];
        return ResponseWithSuccessData($lang, $response, 1);
    }
    // public function orderInvoice(Request $request, $orderId)
    // {
    //     $lang = $request->header('lang', 'ar');
    //     App::setLocale($lang);

    //     $employee = auth('employee')->user();

    //     if (!$employee) {
    //         return RespondWithBadRequest($lang, 4);
    //     }

    //     $invoices = Order::where('branch_id', $employee->branch_id)
    //         ->where('type', 'dine-in')
    //         ->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons', 'table'])
    //         ->where('id', $orderId)->get();

    //     if ($invoices->isEmpty()) {
    //         return response()->json([
    //             'status' => false,
    //             'code' => 400,
    //             'message' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.',
    //             'errorData' => ['error' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.'],
    //             'data' => null
    //         ], 200);
    //     }

    //     $responseData = $invoices->map(function ($order) use ($lang) {

    //         $branchDetails = [
    //             'branch_id' => $order->branch->id ?? null,
    //             'branch_name' => $order->branch->name ?? null,
    //             'branch_address' => ($lang === 'ar') ? $order->branch->address_ar ?? null : $order->branch->address_en ?? null,
    //             'created_at' => $order->created_at,
    //             'invoice_number' => $order->invoice_number,
    //             'order_number' => $order->order_number,
    //             'branch_phone' => $order->branch->phone ?? null,
    //             'table_id' => $order->table->id ?? null,
    //             'table_number' => $order->table->table_number ?? null,
    //             'client_phone' => $order->client_country_code . $order->client_phone ?? null,
    //         ];

    //         $orderDetails = $order->orderDetails
    //             ->filter(function ($detail) {
    //                 return $detail->status !== 'cancel';
    //             })
    //             ->map(function ($detail) use ($lang, $order) {
    //                 $addons = $detail->dishAddons->where('status', '!=', 'cancel');
    //                 if ($order->tax_application == 0) {
    //                     $orderDetailTotal = $detail->price_befor_tax;
    //                     $addonsTotal = $addons->sum('price_before_tax');
    //                 } else {
    //                     $orderDetailTotal = $detail->price_after_tax;
    //                     $addonsTotal = $addons->sum('price_after_tax');
    //                 }
    //                 $total = $orderDetailTotal + $addonsTotal;
    //                 return [
    //                     'item_id' => $detail->id,
    //                     'dish_id' => $detail->dish_id,
    //                     'dish_name' => $detail->dish->name ?? null,
    //                     'size' => $detail->dish_size_id ?
    //                         (($lang === 'ar') ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
    //                         : null,
    //                     'quantity' => $detail->quantity,
    //                     'total_dish_price' => formatFloat($total),
    //                     'note' => $detail->note,
    //                     'addons' => $addons->map(function ($addon) use ($lang) {
    //                         return ($lang === 'ar') ? $addon->Addon->addons->name_ar ?? null : $addon->Addon->addons->name_en ?? null;
    //                     }),
    //                     'coupon_id' => $detail->coupon_id ?? null,
    //                     'coupon_value' => formatFloat($detail->coupon_value ?? null),
    //                 ];
    //             });
    //         $subtotal = $order->total_price_befor_tax;
    //         if ($order->tax_application == 1) {
    //             $subtotal += $order->tax_value;
    //         }
    //         $invoiceSummary = [
    //             'subtotal_price' => formatFloat($subtotal),
    //             'delivery_fees' => formatFloat($order->delivery_fees ?? null),
    //             'service_fees' => formatFloat($order->service_fees ?? null),
    //             'tax_value' => formatFloat($order->tax_value ?? null),
    //             'tax_apply' => !empty($order->tax_value) || $order->tax_value != 0,
    //             'tax_application' => $order->tax_application == 1 ? true : false,
    //             'tax_percentage' => formatFloat($order->tax_percentage ?? null),
    //             'coupon_id' => $order->coupon_id ?? null,
    //             'coupon_code' => $order->coupon?->code ?? null,
    //             'coupon_type' => $order->coupon?->type ?? null,
    //             'coupon_value' => formatFloat($order->coupon_value ?? null),
    //             'total_price' => formatFloat($order->total_price_after_tax),
    //         ];
    //         $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';

    //         $invoiceData = [
    //             'order_type' => $order->type,
    //             'branch_details' => $branchDetails,
    //             'orderDetails' => $orderDetails,
    //             'payment_method' => $order->transaction->payment_method,
    //             'payment_status' => $order->transaction->payment_status,
    //             'invoice_summary' => $invoiceSummary,
    //             'currency_symbol' => $currencySymbol,
    //             'order_notes' => $order->note,
    //         ];

    //         return $invoiceData;
    //     });
    //     $response = [
    //         'invoice' => $responseData,
    //     ];

    //     return ResponseWithSuccessData($lang, $response, 1);
    // }
    public function requestSplit(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $messages = [];

        $phone_length = Country::where('phone_code', $request->country_code)->value('length');

        $validator = Validator::make($request->all(), [
            "order_id" => "required|array|exists:orders,id",
            "phone" => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) use ($phone_length) {
                    if ($phone_length && strlen($value) != $phone_length) {
                        $fail(__('validation.custom.phone.length', [
                            'attribute' => __('auth.phone'),
                            'length' => $phone_length
                        ]));
                    }
                },
            ],
            "country_code" => "nullable",
            "order_detail_ids" => "required|array|exists:order_details,id",
            "selected_table_id_split" => ['required', Rule::exists('tables', 'id')->whereNull('deleted_at')],
        ], $messages);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $branch_id = $employee->branch_id;
        $manager = Branch::where('id', $branch_id)->value('employee_id');
        $user_manager = Employee::where('id', $manager)->value('user_id');
        $parent_order = Order::whereIn('id', $request->order_id)->first();
        if ($request->coupon_code) {
            $coupon_id = Coupon::where('code', $request->coupon_code)->value('id');
            $coupon = GetCouponId($request['coupon_code'], $employee->branch_id);
            if ($coupon) {
                if (!CountCouponUsage($coupon->id)) {
                    DB::rollBack();
                    return respondError(($lang == 'en' ? 'coupon not found.' : 'كوبون غير صالح'), 400);
                }
                if ($coupon->minimum_spend > $parent_order->total_price_after_tax) {
                    DB::rollBack();
                    return respondError(($lang == 'en' ? 'coupon minimum spend not met.' : ' بناء علىالسعر كوبون غير صالح'), 400);
                }
                $date = ($coupon->end_date) <= (Carbon::now());
                $startdate = ($coupon->start_date) > (Carbon::now());
                if ($date) {
                    return response()->json([
                        'status' => false,
                        'code' => 400,
                        'message' => $lang == 'en' ? 'Coupon is expired.' : 'انتهت صلاحية الكوبون.',
                        'errorData' => ['error' => $lang == 'en' ? 'Coupon is expired.' : 'انتهت صلاحية الكوبون.'],
                        'data' => null
                    ], 200);
                }
                if ($startdate) {
                    return response()->json([
                        'status' => false,
                        'code' => 400,
                        'message' => $lang == 'en' ? 'Coupon doesn`t start yet' : 'الكوبون لم يبدأ بعد.',
                        'errorData' => ['error' => $lang == 'en' ? 'Coupon doesn`t start yet' : 'الكوبون لم يبدأ بعد.'],
                        'data' => null
                    ], 200);
                }
            } else {
                return respondError(($lang == 'en' ? 'coupon not found.' : 'كوبون غير موجود'), 400);
            }
        }
        // Check if order status is 'dine-in'
        if ($parent_order->type != 'dine-in') {
            return respondError(($lang == 'en' ? 'Order must be in packing status to be split.' : 'يجب أن يكون الطلب داخل المطعم.'), 400);
        }
        // Check if order status is 'packing'
        if ($parent_order->status !== 'packing') {
            return respondError(($lang == 'en' ? 'Order must be in packing status to be split.' : 'يجب أن يكون الطلب في حالة التعبئة ليتم تقسيمه.'), 400);
        }
        if ($parent_order->orderTransactions->last()->payment_status == 'paid') {
            return respondError(($lang == 'en' ? 'Order cannot split it already paid' : 'لا يمكن تقسيم الطلب لأنه تم دفعه بالفعل'), 400);
        }
        // Get all order details for this order
        $allOrderDetails = OrderDetail::where('order_id', $parent_order->id)->get();
        $allOrderDetailIds = $allOrderDetails->pluck('id')->toArray();

        $orderIds = is_array($request->order_id) ? $request->order_id : [$request->order_id];
        $validOrderDetails = OrderDetail::whereIn('order_id', $orderIds)->pluck('id')->toArray();
        $validOrderDetail = OrderDetail::whereIn('order_id', $orderIds)->get();

        $invalidDetails = array_diff($request->order_detail_ids, $validOrderDetails);

        // Check if selected details belong to the parent order
        $splitFromParent = array_intersect($request->order_detail_ids, $allOrderDetailIds);

        // Check how many will remain after split
        $remainingDetailsCount = count($allOrderDetailIds) - count($splitFromParent);

        if ($remainingDetailsCount < 1) {
            return respondError(
                $lang === 'en'
                    ? 'Cannot split all items. At least one item must remain in the original order.'
                    : 'لا يمكن تقسيم جميع الأصناف. يجب أن يبقى عنصر واحد على الأقل في الطلب الأصلي.',
                400
            );
        }

        if (!empty($invalidDetails)) {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => ($lang == 'en'
                    ? 'Validation failed'
                    : 'فشل التحقق'),
                'errorData' => [
                    'details' => ($lang == 'en'
                        ? 'Some order details do not belong to the provided orders.'
                        : 'بعض تفاصيل الطلب لا تنتمي إلى الطلبات المحددة.')
                ]
            ], 400);
        }
        // Ensure all order details have status 'completed'
        if ($allOrderDetails->whereNotIn('status', ['completed', 'cancel'])->isNotEmpty()) {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => ($lang == 'en'
                    ? 'Validation failed'
                    : 'فشل التحقق'),
                'errorData' => [
                    'details' => ($lang == 'en'
                        ? 'All order details must have a completed status to split the order.'
                        : 'يجب أن تكون جميع تفاصيل الطلب في حالة مكتملة لتقسيم الطلب.')
                ]
            ], 400);
        }

        // Save Request
        $request_user = new WaiterRequest();
        $request_user->type = 0;
        $request_user->created_by = $employee->id;
        $request_user->user_id = $user_manager;
        $request_user->branch_id = $employee->branch_id;
        $request_user->employee_id =  $manager;
        $request_user->order_ids = $request->order_id;
        $request_user->coupon_id = $request->coupon_code ? $coupon_id : null;
        $request_user->table_id = $request->selected_table_id_split;
        if ($request->country_code) {
            $request_user->phone = $request->country_code . ' ' . $request->phone;
        }
        $request_user->order_items_ids = json_encode($request->order_detail_ids);
        $request_user->status = 0;
        $request_user->save();

        $url = str_replace('127.0.0.1:8000', 'erp.test', route('waiter.request.show', ['id' => $request_user->id]));

        $data = addNotification(
            'invoice',
            'admin',
            'يوجد طلب تقسيم جديد',
            'New split request',
            'طلب جديد',
            'New request',
            $user_manager,
            $employee->id,
            $lang,
            $parent_order->order_id,
            $url,
        );
        // Broadcast event
        broadcast(new NotifySent(User::find($user_manager), $data));

        return ResponseWithSuccessData($lang, $data, 1);
    }

    public function requestMerge(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $messages = [
            'order_ids.required' => __('validation.order_ids.required'),
            'order_detail_ids.required' => __('validation.order_detail_ids.required'),
            'selected_table_id.required' => __('validation.selected_table_id.required'),
            'phone.numeric' => __('validation.phone.numeric'),

        ];
        $phone_length = 0;
        if ($request->country_code) {
            $phone_length = Country::where('phone_code', $request->country_code)->value('length');
        }
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $validator = Validator::make($request->all(), [
            "order_ids" => [
                "required",
                "array",
                "min:2",
                Rule::exists('orders', 'id'),
            ],
            'phone' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) use ($phone_length) {
                    if ($phone_length && strlen($value) != $phone_length) {
                        $fail(__('validation.custom.phone.length', [
                            'attribute' => __('auth.phone'),
                            'length' => $phone_length
                        ]));
                    }
                },
            ],
            "reason" => "nullable",
            "country_code" => "nullable",
            "order_detail_ids" => [
                "required",
                "array",
                Rule::exists('order_details', 'id'),
            ],
            "selected_table_id" => "required|exists:tables,id",
        ], $messages);

        // 🔍 Custom validation rules
        $validator->after(function ($validator) use ($request) {
            $orderIds = $request->input('order_ids', []);
            $orderDetailIds = $request->input('order_detail_ids', []);

            if (!empty($orderIds) && !empty($orderDetailIds)) {
                // Fetch order_details with their order_id
                $details = OrderDetail::whereIn('id', $orderDetailIds)
                    ->pluck('order_id', 'id');

                // 1️⃣ Ensure all order_details belong to given order_ids
                foreach ($details as $detailId => $orderId) {
                    if (!in_array($orderId, $orderIds)) {
                        $validator->errors()->add(
                            'order_detail_ids',
                            __('validation.order_detail_not_in_orders', ['detailId' => $detailId])
                        );
                    }
                }

                // 2️⃣ Ensure order_details are not all from the same order
                if ($details->unique()->count() < 2) {
                    $validator->errors()->add(
                        'order_detail_ids',
                        __('validation.order_details_same_order')
                    );
                }
            }
        });


        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }
        if ($request->coupon_code) {
            $coupon_id = Coupon::where('code', $request->coupon_code)->value('id');
            $coupon = GetCouponId($request['coupon_code'], $employee->branch_id);
            if ($coupon) {
                if (!CountCouponUsage($coupon->id)) {
                    DB::rollBack();
                    return respondError(($lang == 'en' ? 'coupon not found.' : 'كوبون غير صالح'), 400);
                }
                $date = ($coupon->end_date) <= (Carbon::now());
                $startdate = ($coupon->start_date) > (Carbon::now());
                if ($date) {
                    return response()->json([
                        'status' => false,
                        'code' => 400,
                        'message' => $lang == 'en' ? 'Coupon is expired.' : 'انتهت صلاحية الكوبون.',
                        'errorData' => ['error' => $lang == 'en' ? 'Coupon is expired.' : 'انتهت صلاحية الكوبون.'],
                        'data' => null
                    ], 200);
                }
                if ($startdate) {
                    return response()->json([
                        'status' => false,
                        'code' => 400,
                        'message' => $lang == 'en' ? 'Coupon validity error.' : 'خطأ في صلاحية الكوبون.',
                        'errorData' => ['error' => $lang == 'en' ? 'Coupon validity error.' : 'خطأ في صلاحية الكوبون.'],
                        'data' => null
                    ], 200);
                }
            } else {
                return respondError(($lang == 'en' ? 'coupon not found.' : 'كوبون غير موجود'), 400);
            }
        }
        // Check if the selected table exists
        $table = Table::find($request->selected_table_id);
        if (!$table) {
            return respondError(($lang == 'en' ? 'The selected table does not exist.' : 'الطاولة المحددة غير موجودة.'), 400);
        }
        $employee = auth('employee')->user();
        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }
        // Check if all orders belong to the same branch
        $orders = Order::with('orderDetails')->whereIn('id', $request->order_ids)->get();
        $branch_id = $employee->branch_id;
        $manager = Branch::find($branch_id)->value('employee_id');
        $user_manager = Employee::find($manager)->user_id;
        foreach ($orders as $order) {
            if ($order->branch_id != $branch_id) {
                return respondError(($lang == 'en' ? 'All orders must belong to the same branch.' : 'يجب أن تنتمي جميع الطلبات إلى نفس الفرع.'), 400);
            }
        }

        // Check if all orders are of type 'dine-in'
        foreach ($orders as $order) {
            if ($order->type != 'dine-in') {
                return respondError(($lang == 'en' ? 'All orders must be of type dine-in to be merged.' : 'يجب أن تكون جميع الطلبات من نوع داخل المطعم ليتم دمجها.'), 400);
            }
        }

        // Check if all orders are in 'packing' status
        foreach ($orders as $order) {
            if ($order->status !== 'packing') {
                return respondError(($lang == 'en' ? 'All orders must be in packing status to be merged.' : 'يجب أن تكون جميع الطلبات في حالة التعبئة ليتم دمجها.'), 400);
            }
            if ($order->orderTransactions->last()->payment_status == 'paid') {
                return respondError(($lang == 'en' ? 'Order cannot merge it already paid' : 'لا يمكن دمج الطلب لأنه تم دفعه بالفعل'), 400);
            }
        }

        // Check if all order details belong to the provided orders
        $validOrderDetails = OrderDetail::whereIn('order_id', $request->order_ids)->pluck('id')->toArray();
        $invalidDetails = array_diff($request->order_detail_ids, $validOrderDetails);
        if (!empty($invalidDetails)) {
            return respondError(($lang == 'en' ? 'Some order details do not belong to the provided orders.' : 'بعض تفاصيل الطلب لا تنتمي إلى الطلبات المحددة.'), 400);
        }

        // Check if all order details have status 'completed'
        $orderDetails = OrderDetail::whereIn('id', $request->order_detail_ids)->get();
        // foreach ($orderDetails as $detail) {
        $orderDetailsFromOrder = OrderDetail::whereIn('order_id', $request->order_ids)->get();

        // Check if all order details have status 'completed' or 'cancel'
        if (
            $orderDetailsFromOrder->isNotEmpty() &&
            $orderDetailsFromOrder->contains(fn($detail) => ! in_array($detail->status, ['completed', 'cancel']))
        ) {

            return respondError(
                $lang == 'en'
                    ? 'All order details must have a completed status to merge the orders.'
                    : 'يجب أن تكون جميع تفاصيل الطلب في حالة مكتملة لدمج الطلبات.',
                400
            );
        }
        $request_user = new WaiterRequest();
        $request_user->type = 1;
        $request_user->created_by = $employee->id;
        $request_user->user_id = $user_manager;
        $request_user->branch_id = $employee->branch_id;
        $request_user->employee_id = $manager;
        $request_user->order_ids = $request->order_ids;
        $request_user->order_items_ids = json_encode($request->order_detail_ids);
        $request_user->table_id = $request->selected_table_id;
        $request_user->coupon_id = $request->coupon_code ? $coupon_id : null;
        if ($request->country_code) {
            $request_user->phone = $request->country_code . ' ' . $request->phone;
        }
        $request_user->status = 0;
        $request_user->save();
        $url = str_replace('127.0.0.1:8000', 'erp.test', route('waiter.request.show', ['id' => $request_user->id]));
        // dd($url);
        // Data to send
        $data = addNotification(
            'invoice',
            'admin',
            'يوجد طلب دمج جديد',
            'New merge request',
            'طلب جديد',
            'New request',
            $user_manager,
            $employee->id,
            $lang,
            null,
            $url,
        );

        // Broadcast event
        broadcast(new NotifySent(User::find($user_manager), $data));


        return ResponseWithSuccessData($lang, $data, 1);
    }

    public function orderPlace_v2(Request $request, $type)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $request['lang'] = $lang;
        $request['make_type'] = 'app';
        $request['type'] = 'dine-in';

        if ($request->order_id) {
            // if ($request['type'] == 'dine-in') {
            //     $table = Table::find($request['table_id']);
            //     if (!$table) {
            //         return respondError(($lang == 'en' ? 'Table not found.' : 'الطاولة غير موجودة.'), 400);
            //     }
            //     if ($table->status != 1 && !$request->order_id) {

            //         return respondError(($lang == 'en' ? 'Table is not available.' : 'الطاولة غير متاحة.'), 400);
            //     }
            //     $table->status = 2; // Mark table as occupied
            //     $table->save();
            // }
            $employee = auth('employee')->user();
            $created_by = $employee->id;
            // $cond_array = ['pending', 'inprogress'];
            $order = Order::where('id', $request->order_id)->first();
            if ($order) {
                // $order = Order::where('id', $request->order_id)->whereIn('status', $cond_array)->first();
                // if ($order->status == "cancelled" || $order->status == 'completed') {
                //     $message = "You can't edit in this order";
                //     return respondErrorData('errors', 400, [$lang === 'ar' ? 'لا يمكنك التعديل على الطلب' : $message]);
                // }
                if ($order->status == "cancelled" || $order->status == 'completed' || $order->orderTransactions->contains('payment_status', 'paid')) {
                    $message = "You can't edit in this order";
                    return respondErrorData('errors', 400, [$lang === 'ar' ? 'لا يمكنك التعديل على الطلب' : $message]);
                }
            } else {
                $message = "this order is not found";
                return respondErrorData('errors', 400, [$lang === 'ar' ? 'هذا الطلب غير موجود' : $message]);
            }



            // if ($order->orderTransactions->contains("payment_status", "paid")) {
            //     $message = "You can't edit in this order";
            //     return respondErrorData('errors', 400, [$lang === 'ar' ? 'لا يمكنك التعديل على الطلب' : $message]);
            // }

            $data = $this->orderService->transformOrderRequest($request->all());
            $taxApplication = getBranchSettings($data['branch_id'], 'tax_application');
            $taxPercentage = getBranchSettings($data['branch_id'], 'tax_percentage');
            $serviceFeesValue = in_array($request['type'], ['dine-in', 'reservation-table']) ? getBranchSettings($data['branch_id'], 'service_fees') : 0;
            $serviceFeesType = in_array($request['type'], ['dine-in', 'reservation-table']) ? getBranchSettings($data['branch_id'], 'service_fees_type') : "0";
            $response = $this->orderService->storeOrderItems(
                $order,
                $data['items'],
                $type,
                $data['branch_id'],
                $taxApplication,
                $taxPercentage,
                $serviceFeesValue,
                $serviceFeesType,
                $request['type'],
                $created_by,
                null,
                1,
                $lang
            );
            $item_calculate = $this->orderService->CalculateOrder($order->id);
            $order->refresh();
            $order_transaction = OrderTransaction::where('order_id', $order->id)->first();
            $order_transaction->paid = $order->total_price_after_tax;
            // $order_transaction->coupon_id = $coupon ? $coupon->id : null;
            $order_transaction->save();
            $notifyDataNew = [
                'notification_type' => 'order',
                'description_ar' => 'تم أضافه طبق جديد للأوردر رقم ' . $order->order_number,
                'description_en' => 'New item has been added to order ' . $order->order_number,
                'title_ar' => ' أضافه طبق جديد للأوردر',
                'title_en' => 'new item added to order',
                'created_by' => null,
                'order_id' => $order->id
            ];
            runNotificationToEmployees($order->branch_id, $notifyDataNew, null, $order->id, $lang);
            $orderItemIds = (array) $response['collectedItems'];
            $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';

            $orderItemIds = collect($response['collectedItems'])->map(function ($item) use ($order, $currencySymbol, $lang) {
                $orderItem = OrderDetail::with([
                    'dish',
                    'dishSize',
                    'dishAddons.Addon.addons'
                ])->find($item['order_detail_id']);

                if (!$orderItem) return null;

                $branchMenu = BranchMenu::where('branch_id', $order->branch_id)
                    ->where('dish_id', $orderItem->dish_id)
                    ->with(['branchMenuAddons', 'branchMenuSizes'])
                    ->first();

                $sizeId = $orderItem->dish_size_id;
                $sizeMenuId = $branchMenu?->branchMenuSizes()
                    ->where('branch_id', $order->branch_id)
                    ->where('dish_size_id', $sizeId)
                    ->value('id');

                // Filter out canceled addons before anything else
                $activeAddons = $orderItem->dishAddons->filter(function ($addon) {
                    return !in_array($addon->status, ['cancel']);
                });

                $addons = $activeAddons->map(function ($addon) use ($lang, $branchMenu, $order) {
                    $menuAddonId = $branchMenu?->branchMenuAddons()
                        ->where('branch_id', $order->branch_id)
                        ->where('dish_addon_id', $addon->Addon?->id)
                        ->value('id');

                    $price = $addon->price_before_coupon ?? $addon->price_befor_tax ?? 0;

                    return array_filter([
                        'addon_category_id' => $addon->Addon?->addon_category_id,
                        'addon_id'          => $addon->Addon?->addon_id,
                        'addon_menu_id'     => $menuAddonId,
                        'addon_name'        => $addon->Addon?->addons?->name_ar ?? null,
                        'addon_status'      => $addon->status,
                        'price'             => formatFloat($price),
                    ], fn($v) => !is_null($v));
                })->values();

                // ✅ Sum only active addons
                $addonsTotal = $activeAddons->sum(fn($addon) => $addon->price_before_coupon ?? $addon->price_befor_tax ?? 0);
                $dishBase = $orderItem->price_before_coupon ?? $orderItem->price_befor_tax ?? 0;
                $totalBeforeCoupon = $dishBase + $addonsTotal;

                // ✅ Remove all null keys from final response
                return array_filter([
                    'order_detail_id'   => $orderItem->id,
                    'quantity'          => $orderItem->quantity,
                    'dish_status'            => $orderItem->status,
                    'total_dish_price'  => formatFloat($totalBeforeCoupon),
                    'dish_menu_id'      => $branchMenu->id ?? null,
                    'dish_id'           => $orderItem->dish_id,
                    'dish_name'         => $orderItem->dish->name_ar ?? null,
                    'dish_image'        => $orderItem->dish->image_url ?? null,
                    'currency_symbol'   => $currencySymbol,
                    'size_id'           => $sizeId,
                    'size_menu_id'      => $sizeMenuId,
                    'addons'            => $addons->isNotEmpty() ? $addons : null,
                    'note'              => $orderItem->note,
                    'dish_order'        => $orderItem->dish_order,
                ], fn($v) => !is_null($v));
            })->filter()->values()->toArray();

            $orderItemsCount = $order->status === 'cancelled'
                ? $order->orderDetails->sum('quantity')
                : $order->orderDetailsWithoutCancel->sum('quantity');
            // Create data for broadcasting
            $dish_data = [
                'order_id' => $order->id,
                'order_type' => $order->type,
                'order_items_count' =>  $orderItemsCount,
                'status' => $order->status,
                'added_items' =>  $orderItemIds ?? [],
                'currency_symbol' => $currencySymbol,
                'total_price' =>  formatFloat($order->total_price_after_tax),

                'date' => now()->toDateString(),
            ];
            broadcast(new dishChangeStatus2($dish_data));

            // broadcast(new dishChangeStatus($dish_data));
            return ResponseWithSuccessData($lang, ['order_id' => $order->id], 1);
        } else {
            $data = $this->orderService->transformOrderRequest($request->all());
            $response = $this->orderService->store_v2($data, 'true', 'api');
            $responseData = $response->original;
            if (!$responseData['status']) {
                if ($responseData['validation_type']) {
                    return respondError($responseData['message'], 400, $responseData['errorData']);
                } else {
                    return respondErrorData('errors', 400, $responseData['errorData']['error']);
                }
            }

            $data = $responseData['data'];
            return ResponseWithSuccessData($lang, $data, 1);
        }
    }


    public function requestPrint(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $messages = [
            'order_id.required' => __('validation.order_id.required'),
        ];

        $validator = Validator::make($request->all(), [
            "order_id" => "required|exists:orders,id",
            "waiter_id" => "required",
        ], $messages);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $employee = auth('employee')->user();
        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $branch_id = $employee->branch_id;
        $manager = Branch::where('id', $branch_id)->value('employee_id');
        $user_manager = Employee::where('id', $manager)->value('user_id');
        $order = Order::whereIn('id', $request->order_id)->first();
        // Check if order status is 'dine-in'
        if ($order->type != 'dine-in') {
            return respondError(($lang == 'en' ? 'Order must be in packing status to be split.' : 'يجب أن يكون الطلب داخل المطعم.'), 400);
        }
        // Check if order status is 'packing'
        if ($order->status !== 'packing' && $order->status !== 'completed') {
            return respondError(($lang == 'en' ? 'Order must be in packing status or completed status.' : 'يجب ان يكون الطلب فى حاله التعبئه او الاكتمال لطباعه'), 400);
        }
        if ($request->coupon_code) {
            $coupon_id = Coupon::where('code', $request->coupon_code)->value('id');
            $coupon = GetCouponId($request['coupon_code'], $employee->branch_id);
            if ($coupon) {
                if (!CountCouponUsage($coupon->id)) {
                    DB::rollBack();
                    return respondError(($lang == 'en' ? 'coupon not found.' : 'كوبون غير صالح'), 400);
                }
                if ($coupon->minimum_spend > $order->total_price_after_tax) {
                    DB::rollBack();
                    return respondError(($lang == 'en' ? 'coupon minimum spend not met.' : ' بناء علىالسعر كوبون غير صالح'), 400);
                }
                $date = ($coupon->end_date) <= (Carbon::now());
                $startdate = ($coupon->start_date) > (Carbon::now());
                if ($date) {
                    return response()->json([
                        'status' => false,
                        'code' => 400,
                        'message' => $lang == 'en' ? 'Coupon is expired.' : 'انتهت صلاحية الكوبون.',
                        'errorData' => ['error' => $lang == 'en' ? 'Coupon is expired.' : 'انتهت صلاحية الكوبون.'],
                        'data' => null
                    ], 200);
                }
                if ($startdate) {
                    return response()->json([
                        'status' => false,
                        'code' => 400,
                        'message' => $lang == 'en' ? 'Coupon doesn`t start yet' : 'الكوبون لم يبدأ بعد.',
                        'errorData' => ['error' => $lang == 'en' ? 'Coupon doesn`t start yet' : 'الكوبون لم يبدأ بعد.'],
                        'data' => null
                    ], 200);
                }
            } else {
                return respondError(($lang == 'en' ? 'coupon not found.' : 'كوبون غير موجود'), 400);
            }
        }
        $order = Order::where('branch_id', $employee->branch_id)
            ->where('id', $request->order_id)
            ->first();
        $order_details = OrderDetail::where('order_id', $request->order_id)->pluck('id')->toArray();
        // Save Request
        $request_user = new WaiterRequest();
        $request_user->type = 2;
        $request_user->created_by = $employee->id;
        $request_user->user_id = $user_manager;
        $request_user->branch_id = $employee->branch_id;
        $request_user->employee_id =  $manager;
        $request_user->order_ids = array_map('intval', $request->order_id);
        $request_user->order_items_ids = json_encode($order_details);
        $request_user->coupon_id = $order->coupon_id ?? null;
        $request_user->phone = $request->phone;
        $request_user->table_id = $order->table_id;
        $request_user->status = 0;
        $request_user->save();

        $url = str_replace('127.0.0.1:8000', 'erp.test', route('waiter.request.show', ['id' => $request_user->id]));

        $data = addNotification(
            'invoice',
            'admin',
            'طلب طباعه جديد',
            'New print request',
            'طلب جديد',
            'New request',
            $user_manager,
            $employee->id,
            $lang,
            $order->id,
            $url,
        );
        $usertoken = User::where('id', $user_manager)->value('fcm_token');
        send_push_notification($usertoken, 'طلب جديد', 'طلب جديد', 'طلب جديد', 'يوجد طلب طباعه جديد', 'admin', $user_manager, $employee->id, $request->order_id, $lang, 'invoice');
        // $notifyData =
        //             [
        //                 'notification_type' => 'table',
        //                 'description_ar' => 'تم إضافة طاولة جديدة للفرع',
        //                 'description_en' => 'new table added to your branch',
        //                 'title_ar' => 'تمت إضافة طاولة جديدة',
        //                 'title_en' => 'New table added',
        //                 'created_by' =>$user_manager,
        //                 'order_id' => $order->id
        //             ];
        //         runNotificationToEmployees($branch_id, $notifyData, $user_manager, $order->id, 'ar');
        // Broadcast event
        broadcast(new NotifySent(User::find($user_manager), $data));

        return ResponseWithSuccessData($lang, $data, 1);
    }
    public function sendOrderToCashier(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }
        $phone_length = Country::where('phone_code', $request->country_code)->value('length');


        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            "phone" => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) use ($phone_length) {
                    if ($phone_length && strlen($value) != $phone_length) {
                        $fail(__('validation.custom.phone.length', [
                            'attribute' => __('auth.phone'),
                            'length' => $phone_length
                        ]));
                    }
                },
            ],
            "country_code" => "nullable",
            "coupon_code" => "nullable|exists:coupons,code",
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $order = Order::where('branch_id', $employee->branch_id)
            ->where('id', $request->order_id)
            ->first();
        if (!$order) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Order not found or not eligible for sending to cashier' : 'الطلب غير موجود أو غير مؤهل للارسال للكاشير',
                'errorData' => ['error' => $lang == 'en' ? 'Order not found or not eligible for sending to cashier' : 'الطلب غير موجود أو غير مؤهل للارسال للكاشير'],
                'data' => null
            ], 200);
        }
        // Check if order status is 'dine-in'
        if ($order->type != 'dine-in') {
            return respondError(($lang == 'en' ? 'Order must be in packing status to be split.' : 'يجب أن يكون الطلب داخل المطعم.'), 400);
        }

        // Check if order status is 'packing'
        if ($order->status !== 'packing' && $order->status !== 'completed') {
            return respondError(($lang == 'en' ? 'Order must be in packing status or completed status.' : 'يجب ان يكون الطلب فى حاله التعبئه او الاكتمال لطباعه'), 400);
        }

        $orderDetails = OrderDetail::where('order_id', $order->id)->get();

        foreach ($orderDetails as $detail) {
            if ($detail->status != 'completed' && $detail->status != 'cancel') {
                return respondError(
                    $lang == 'en'
                        ? 'All order details must have a completed or cancel status to print order'
                        : 'يجب أن تكون جميع تفاصيل الطلب في حالة مكتملة أو ملغاة لطباعه الطلب.',
                    400
                );
            }
        }
        // Order already paid + request has coupon → reject
        if ($request->coupon_code && $order->orderTransactions->last()?->payment_status === 'paid') {
            return respondError(
                $lang === 'en'
                    ? 'Order is already paid, cannot apply coupon.'
                    : 'عفوا، الاوردر مدفوع ولا يمكن تطبيق كوبون عليه',
                400
            );
        }

        //Order not paid but already has a coupon + request has coupon → reject
        if ($request->coupon_code && $order->coupon_id !== null) {
            return respondError(
                $lang === 'en'
                    ? 'Cannot apply coupon to order as another coupon is already applied.'
                    : 'لا يمكن تطبيق كوبون على الاوردر لوجود كوبون آخر مطبق بالفعل',
                400
            );
        }

        $coupon = null;
        // $coupon_value = 0;
        if ($order->print_status != 'hold') {
            $data = new Request(['order_id' => [$request->order_id], 'waiter_id' => $employee->id, 'phone' => $request->country_code . $request->phone]);

            $status = $this->requestPrint($data);

            $response = json_decode($status->getContent(), true);
            $branch_id = $employee->branch_id;
            $manager = Branch::where('id', $branch_id)->value('employee_id');
            $user_manager = Employee::where('id', $manager)->value('user_id');
            $usertoken = User::where('id', $user_manager)->value('fcm_token');

            if (isset($response['status']) && $response['status'] === true) {
                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'message' => $lang == 'en' ? 'Request sent to branch manager first.' : 'تم ارسال طلب لمدير الفرع اولا',
                    'data' => null
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'code' => 400,
                    'message' => $response['message'],
                    'errorData' => [
                        'error' => $response['message']
                    ],
                ], 400);
            }
        } else {
            $coupon = GetCouponId($request['coupon_code'], $employee->branch_id);
            if ($request->coupon_code && $coupon) {
                $dataRequest = new Request([
                    'code' => $request->coupon_code,
                    'amount' => $order->total_price_after_tax,
                    'branch_id' => $employee->branch_id,
                    "order_type" => $order->type,
                    "dishes" => $order->orderDetails->map(function ($detail) {
                        return [
                            "dish_id" => $detail->dish_id,
                            "size_id" => $detail->size_id,
                            "quantity" => $detail->quantity
                        ];
                    }),
                ]);

                $dataRequest->headers->set('lang', $lang); // Set lang header

                $response     = $this->CouponService->isCouponValid($dataRequest, $order->client_id);
                $responseData = $response->getData(true);

                if (!$responseData['status']) {
                    return respondError($responseData['message'], 400, $responseData['errorData'] ?? []);
                }

                // ✅ Coupon is valid
                $type = $coupon->apply_type;

                if ($type === 'order') {
                    $order->coupon_id = $coupon->id;
                    $order->save();
                } elseif ($type === 'dish') {
                    // Extract valid dish_ids returned from isCouponValid
                    $validDishIds = collect($responseData['data']['discount_details'])
                        ->pluck('dish_id')
                        ->unique()
                        ->toArray();

                    if (!empty($validDishIds)) {
                        OrderDetail::where('order_id', $order->id)
                            ->whereIn('dish_id', $validDishIds)
                            ->update(['coupon_id' => $coupon->id]);
                    }
                }
            }


            if ($request->phone) {
                $order->client_country_code = $request->country_code;
                $order->client_phone = $request->phone;
            }
            $order->save();
            $data =  $this->orderService->CalculateOrder($order->id);
            $order->refresh();

            // if ($order->coupon_id && $order->total_price_after_tax == 0) {
            //     $order->cashier_machine_id = EmployeeMachine::where('employee_id', $cashiers->first()->id)
            //         ->orderby('id', 'desc')
            //         ->first()->cashier_machine_id;
            //     $order->save();
            //     $order->refresh();
            // }
            $order_transaction = OrderTransaction::where('order_id', $order->id)->first();
            $order_transaction->payment_status = $order->coupon_id && $order->total_price_after_tax == 0 ? 'paid' : $order_transaction->payment_status;
            $order_transaction->paid = $order->total_price_after_tax;
            $order_transaction->coupon_id =  $request->coupon_code && $coupon  ? $coupon->id :  $order->coupon_id;
            $order_transaction->save();
            $order->print_status = 'urgent';
            $order->save();
            $invoice_id = $this->invoiceService->editInvoice($order->id, 1);


            $Order = Order::with('transaction', 'orderDetails', 'orderDetailsWithoutCancel.dish', 'Table', 'orderDetails.dish')->find($order->id);
            $maxDishTime = $order->orderDetailsWithoutCancel->max(fn($detail) => $detail->dish->time ?? 0);
            $invoice = Invoice::where('order_id', $order->id)->first();

            $data = [
                'invoice_id' => $invoice->id,
                'invoice_type' => $invoice->invoice_type,

                "invoice_number" => $order->invoice_number,
                'table_number' => ($Order->type == 'dine-in') ? $Order->table?->table_number : null,

                "invoice_print_status" => $order->print_status,
                "order_id" =>  $order->id,
                "order_type" => $order->type,
                "order_number" => $order->order_number,
                "order_items_count" =>  $order->orderDetails->count(),
                "order_time" => $maxDishTime,
                'print_count' => $order->print_count_cashier ?? 0,

                'payment_status' => $invoice->status ?? null,
            ];
            $notifyData =
                [
                    'notification_type' => 'invoice',
                    'description_ar' => 'تم أضافه فاتوره جديده لأوردر ' . $Order->order_number,
                    'description_en' => 'new invoice added to order ' . $Order->order_number,
                    'title_ar' => 'تمت إضافة فاتورة جديدة',
                    'title_en' => 'New invoice added',
                    'created_by' => null,
                    'order_id' => $Order->id
                ];
            runNotificationToEmployees($Order->branch_id, $notifyData, null, $Order->id, $lang);
            $cashiers = getEmployeesForNotify($Order->branch_id, now(), 'cashier');
            if (!$cashiers) {
                return response()->json([
                    'status' => false,
                    'code' => 400,
                    'message' =>  __('recipes.nocashiersworknowinbranch'),
                    'errorData' => ['error' => __('recipes.nocashiersworknowinbranch')],
                    'data' => null
                ], 400);
            }

            foreach ($cashiers as $cashier) {

                // Broadcast event
                broadcast(new CashierNotify($cashier->id, $data));
            }
            $orderDetails = OrderDetail::with('dishAddons')
                ->where('order_id', $order->id)
                ->get()
                ->map(function ($detail) {
                    // Compute totalBeforeCoupon properly
                    $activeAddons = $detail->dishAddons->filter(function ($addon) {
                        // Adjust the condition based on your actual status field/value
                        return !in_array($addon->status, [0, 'cancel']);
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
            $dish_data = [
                'order_id' => $order->id,
                'order_type' => $order->type,
                'order_items_count' =>  $orderItemsCount,
                'status' => $order->status,
                'items_updated' => $orderDetails,
                'total_price' =>  formatFloat($order->total_price_after_tax),

                'date' => now()->toDateString(),
            ];
            broadcast(new dishChangeStatus2($dish_data));

            // broadcast(new dishChangeStatus($dish_data));
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => $lang == 'en' ? 'Order sent to cashier successfully' : 'تم إرسال الطلب إلى الكاشير بنجاح',
                'data' => [
                    'order_id' => $order->id,
                    'print_status' => $order->print_status,
                ],
            ]);
        }
    }

    // public function orderCancel(Request $request)
    // {
    //     $lang = $request->header('lang', 'ar');
    //     App::setLocale($lang);

    //     $employee = auth('employee')->user();

    //     if (!$employee) {
    //         $message = "plesae login first";
    //         return response()->json([
    //             'code' => 400,
    //             'status' => false,
    //             'message' => $message,
    //             'data' => null,
    //             'errorData' => ['error' => $message]
    //         ], 200);
    //     }

    //     if ($employee->flag != "waiter") {
    //         $message = "this is not watiter";
    //         return response()->json([
    //             'code' => 400,
    //             'status' => false,
    //             'message' => $message,
    //             'data' => null,
    //             'errorData' => ['error' => $message]
    //         ], 200);
    //     }
    //     $created_by = $employee->id;

    //     $validator = Validator::make($request->all(), [
    //         'order_id' => 'required|exists:orders,id',
    //         'item_id' => 'required_if:type,2|exists:order_details,id',
    //         'reason_id' => 'nullable|exists:order_cancellation_reasons,id',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'code' => 400,
    //             'status' => false,
    //             'message' => __('validation.dataNotFound'),
    //             'data' => null,
    //             'errorData' => ['error' => __('validation.dataNotFound')]
    //         ], 200);
    //     }

    //     $order = Order::where('id', $request->order_id)->first();
    //     if ($order->status == "cancelled") {
    //         return response()->json([
    //             'code' => 400,
    //             'status' => false,
    //             'message' => __('validation.AlreadyDeleted'),
    //             'data' => null,
    //             'errorData' => ['error' => __('validation.AlreadyDeleted')]
    //         ], 200);
    //     } else if ($order->status != "pending" && !($request->item_id)) {
    //         return response()->json([
    //             'code' => 400,
    //             'status' => false,
    //             'message' => __('validation.OrderCanNotDeleteAnyMore'),
    //             'data' => null,
    //             'errorData' => ['error' => __('validation.OrderCanNotDeleteAnyMore')]
    //         ], 200);
    //     }

    //     if ($request->item_id) {
    //         //$dish_menu_id = BranchMenu::where('id', $request->item_id)->where('branch_id', $order->branch_id)->first();
    //         $order_details = OrderDetail::where('id', $request->item_id)->first();
    //         if ($order_details->status != "pending") {
    //             $message = "sorry you can't cancel this dish now, this is not pending";
    //             return response()->json([
    //                 'code' => 400,
    //                 'status' => false,
    //                 'message' => $message,
    //                 'data' => null,
    //                 'errorData' => ['error' => $message]
    //             ], 200);
    //         }

    //         if ($order_details->status == "cancel") {
    //             return response()->json([
    //                 'code' => 400,
    //                 'status' => false,
    //                 'message' => __('validation.AlreadyDeleted'),
    //                 'data' => null,
    //                 'errorData' => ['error' => __('validation.AlreadyDeleted')]
    //             ], 200);
    //         }
    //     }

    //     $cancel_time = getBranchSettings($order->branch_id, 'time_cancellation');
    //     $minutesDifference = $order->created_at->diffInMinutes(Carbon::now());
    //     // if ($minutesDifference > $cancel_time) {
    //     //     return respondErrorData(__('validation.OrderCanNotDeleteAnyMore'), 400, __('validation.OrderCanNotDeleteAnyMore'));
    //     // }
    //     if (CheckOrderPaidStatus($order->id)) {
    //         return respondErrorData(__('validation.OrderCanNotDeleteAnyMore'), 400, __('validation.OrderCanNotDeleteAnyMore'));
    //     }
    //     // if ($created_by != $order->waiter_id) {
    //     //     return respondErrorData(__('validation.OrderCanNotDeleteAnyMore'), 400, __('validation.OrderCanNotDeleteAnyMore'));
    //     // }

    //     if ($request->type == 2) {
    //         if ($order_details) {
    //             $order_details->status = "cancel";
    //             $order->modify_by = $created_by;
    //             $order_details->save();
    //             if ($order_details->dishAddons) {
    //                 OrderAddon::where('order_details_id', $order_details->id)->update(['status' => 'cancel', 'modify_by' => $created_by]);
    //             }
    //             $remainingOrderDetails = $order->orderDetails()->where('status', '!=', 'cancel')->get();
    //             if ($remainingOrderDetails->count() == 0) {
    //                 $order->status = "cancelled";
    //                 $order->print_status = 'cancelled';
    //                 $order->modify_by = $created_by;
    //                 $order->save();

    //                 $order_tracking = new OrderTracking();
    //                 $order_tracking->order_id = $request->order_id;
    //                 $order_tracking->order_status = 'cancelled';
    //                 $order_tracking->created_by = $created_by;
    //                 $order_tracking->time = date('H:i:s');
    //                 $order_tracking->save();
    //             } elseif ($remainingOrderDetails->every(fn($detail) => $detail->status === 'completed')) {
    //                 // All remaining details are completed
    //                 $order->status = "packing";
    //                 $order->print_status = 'hold';
    //                 $order->modify_by = $created_by;
    //                 $order->save();
    //                 $order_tracking = new OrderTracking();
    //                 $order_tracking->order_id = $request->order_id;
    //                 $order_tracking->order_status = 'readyForPickup';
    //                 $order_tracking->created_by = $created_by;
    //                 $order_tracking->time = date('H:i:s');
    //                 $order_tracking->save();
    //             }
    //             $item_calculate = $this->orderService->CalculateItem($order_details->id);
    //             $update_item_calculate = $this->orderService->UpdateCalculateItem($item_calculate);

    //             $item_calculate = $this->orderService->CalculateOrder($request->order_id);
    //             // $update_item_calculate = $this->orderService->UpdateCalculateItem($item_calculate);
    //         }
    //     } else {
    //         foreach ($order->orderDetails as $detail) {
    //             $detail->status = "cancel";
    //             $detail->modify_by = $created_by;
    //             $detail->save();

    //             if ($detail->dishAddons) {
    //                 OrderAddon::where('order_details_id', $detail->id)->update(['status' => 'cancel', 'modify_by' => $created_by]);
    //             }
    //         }

    //         $order->status = "cancelled";
    //         $order->print_status = 'cancelled';
    //         $order->modify_by = $created_by;
    //         $order->save();

    //         $order_tracking = new OrderTracking();
    //         $order_tracking->order_id = $request->order_id;
    //         $order_tracking->order_status = 'cancelled';
    //         $order_tracking->created_by = $created_by;
    //         $order_tracking->time = date('H:i:s');
    //         $order_tracking->save();

    //         // $item_calculate = $this->orderService->CalculateOrder($request->order_id);
    //         // $update_item_calculate = $this->orderService->UpdateCalculateOrder($item_calculate);
    //     }

    //     if ($request->reason_id) {
    //         cancelOrderReason($request->order_id, $request->reason, $request->reason_id, $request->item_id);
    //     }

    //     return response()->json([
    //         'status' => true,
    //         'code' => 200,
    //         'message' => $lang == 'en' ? 'Order deleted successfully' : 'تم حذف الطلب بنجاح',
    //         'data' => []
    //     ]);
    // }
    public function orderCancel(Request $request)
    {
        return $this->orderService->orderCancel($request);
    }
    public function orderEditItem(Request $request, $type)
    {
        return $this->orderService->orderEditItem($request, $type);
    }
    // public function orderEditItem(Request $request, $type)
    // {
    //     $lang =  $request->header('lang', 'en');
    //     App::setLocale($lang);

    //     $employee = auth('employee')->user();
    //     if (!$employee) {
    //         return RespondWithBadRequest($lang, 4);
    //     }
    //     $validator = Validator::make($request->all(), [
    //         "branch_id" => "required|exists:branches,id",
    //         "order_id" => "required|exists:orders,id",
    //         "item_id" => "required|exists:order_details,id",
    //         "quantity" => "required|integer|min:1",
    //         "note" => "nullable|string",
    //     ]);


    //     $created_by = $employee->id;
    //     $done = false;
    //     $IDBranch = $request['branch_id']; //from auth employee branch id
    //     $status = 'pending';
    //     $make_type = 'waiter';

    //     if ($validator->fails()) {
    //         return respondError('Validation Error.', 400, $validator->errors());
    //     }

    //     if ($request->item_id) {
    //         $checkOrderDetails = OrderDetail::where('id', $request->item_id)->where('status', "pending")->first();
    //         if (!$checkOrderDetails) {
    //             $message = "you can't allow to edit in this item, the dish is not in pending any more";
    //             return respondErrorData($message, 400, $lang == 'ar' ? 'غير مسموح بتعديل هذا الطبق لانه اصبح غير معلق' : $message);
    //         }
    //     }

    //     $Branch_Dish = getBranchMenuDetails($IDBranch, $checkOrderDetails->dish_id, 'web', 'first');
    //     if (!$Branch_Dish) {
    //         return respondError('error', 400,  __('order.dish_not_found'));
    //     }

    //     if ($Branch_Dish->is_active == 0) {
    //         return respondError('error', 400, __('order.dish_not_active'));
    //     }
    //     $addon_categories = [];
    //     // return $Branch_Dish->id;
    //     if (!empty($request['addon_categories']) && is_array($request['addon_categories'])) {
    //         $addon_categories[0] = [
    //             "id" =>  $request['addon_categories'][0]['id'] ?? null,
    //             "addon" => $request['addon_categories'][0]['addon'] ?? null
    //         ];
    //     }


    //     $items[0] =  [
    //         "dish_id" => $Branch_Dish->id,
    //         "dish_order" => $request->dish_order,
    //         "sizeId" => $request->size_id,
    //         "quantity" => $request->quantity,
    //         "note" => $request->note,
    //         "addon_categories" => $addon_categories
    //     ];

    //     $result = $this->orderService->validateOrderItem($items,  $request->branch_id, 'api');
    //     $responseData = $result->original;
    //     if (!$responseData['status']) {
    //         return $result; // Respond with validation error if any
    //     }

    //     // $client_id = User::where('flag', 'unknown')->value('id');

    //     // $Branch_Dish = BranchMenu::where('dish_id', $checkOrderDetails->dish_id)->where('branch_id', $IDBranch)->first();


    //     $has_size = $Branch_Dish->dish->has_sizes;
    //     if ($has_size) {
    //         if ($request->size_id) {
    //             $branch_dish_size = getBranchSizeDetails($IDBranch, $request->size_id, 'api', 'first');
    //             if (!$branch_dish_size) {
    //                 $message = "sorry, the size not found";
    //                 return respondErrorData($message, 400, $message);
    //             }
    //             $size_id = $branch_dish_size->dish_size_id;
    //         } else {
    //             $size_id = $checkOrderDetails->dish_size_id;
    //         }
    //     } else {
    //         $size_id = null;
    //     }

    //     // $orderDetail = OrderDetail::where('id', $checkOrderDetails->id)
    //     //     ->where('order_id', $request->order_id)
    //     //     ->where('status', 'pending')
    //     //     ->first();

    //     if ($checkOrderDetails) {
    //         $checkOrderDetails->note = $request->note;
    //         $checkOrderDetails->quantity = $request->quantity;
    //         $checkOrderDetails->dish_size_id = $size_id;
    //         $checkOrderDetails->dish_order = $request->dish_order ?? $checkOrderDetails->dish_order;
    //         $checkOrderDetails->modify_by = $created_by;
    //         $checkOrderDetails->save();
    //     }
    //     $checkOrderDetails->refresh();
    //     $min_addons = 0;
    //     $max_addons = 0;
    //     $check_addons_count = BranchMenuAddon::where('dish_id', $Branch_Dish->dish_id)->where('branch_id', $IDBranch)->where('is_active', 1)->first();
    //     if ($check_addons_count) {

    //         $min_addons = $check_addons_count->dishAddons ? $check_addons_count->dishAddons->min_addons : 0;
    //         $max_addons = $check_addons_count->dishAddons ? $check_addons_count->dishAddons->max_addons : 0;
    //     }
    //     if ($request->has('addon_categories')) {
    //         if ($request->addon_categories != []) {
    //             foreach ($request->addon_categories as $addon_category) {
    //                 $addons = BranchMenuAddon::whereIn('id', $addon_category['addon'])->where('branch_id', $IDBranch)->pluck('dish_addon_id');
    //                 $orderAddon = OrderAddon::where(['order_details_id' => $checkOrderDetails->id, 'status' => 'pending'])->whereNotIn('dish_addon_id', $addons)->update(['status' => 'cancel']);
    //                 foreach ($addon_category['addon'] as $addon_id) {
    //                     if (count($addon_category['addon']) < $min_addons) {
    //                         return respondError('error', 400,  __("order.addon_must_be_min_max") .  $min_addons . "-" . $max_addons);
    //                     }
    //                     // $addon = BranchMenuAddon::find($addon_id);
    //                     $addon = getBranchAddonDetails($IDBranch, $addon_id, 'api', 'first');
    //                     if ($addon) {
    //                         $price = $addon->price;
    //                         $orderAddon = OrderAddon::updateOrCreate(
    //                             [
    //                                 'dish_addon_id' => $addon->dish_addon_id,
    //                                 'order_details_id' => $checkOrderDetails->id,
    //                                 'order_id' => $checkOrderDetails->order_id,
    //                                 'status' => 'pending',
    //                             ],
    //                             [
    //                                 'quantity' => $request->quantity,
    //                                 'created_by' => $created_by
    //                             ]
    //                         );
    //                         $orderAddon->save();
    //                     } else {
    //                         $message = "sorry, the addon not found";
    //                         return respondErrorData($message, 400, $message);
    //                     }
    //                 }
    //             }
    //         } else {
    //             if ($min_addons > 0) {
    //                 return respondError('error', 400,  __("order.addon_must_be_min_max") .  $min_addons . "-" . $max_addons);
    //             }
    //             $orderAddon = OrderAddon::where(['order_details_id' => $checkOrderDetails->id, 'status' => 'pending'])->update(['status' => 'cancel']);
    //         }
    //     }

    //     $item_calculate = $this->orderService->CalculateItem($request->item_id);
    //     $update_item_calculate = $this->orderService->UpdateCalculateItem($item_calculate);

    //     $item_calculate = $this->orderService->CalculateOrder($request->order_id);
    //     // $update_item_calculate = $this->orderService->UpdateCalculateOrder($item_calculate);

    //     $employees_ids =  getWorkingEmployeesByBranchAndTime($employee->branch_id, now());

    //     if ($employees_ids['status'] === false) {
    //         return respondError('Validation Error.', 400, ['error' => __('recipes.nochaiersworknowinbranch')]);
    //     }
    //     $employees = Employee::whereIn('id', $employees_ids['working_employee_ids'])->whereIn('flag', ['cashier', 'waiter'])->get();
    //     if (!$employees) {
    //         return respondError('Validation Error.', 400, ['error' => __('recipes.nochaiersworknowinbranch')]);
    //     }
    //     $checkOrderDetails->refresh();

    //     foreach ($employees as $employee) {
    //         $usertoken = User::where('id', $employee->user_id)->value('fcm_token');
    //         $data = addNotification(
    //             'cashier',
    //             'تم تعديل الطبق ',
    //             'The dish is edited',
    //             'تعديل طبق',
    //             'Dish edit',
    //             $employee->id,
    //             $employee->id,
    //             $lang,
    //             $request->order_id,
    //         );
    //         if ($checkOrderDetails->order->tax_application == 0) {
    //             $orderDetailTotal = $checkOrderDetails->price_befor_tax;
    //             $addonsTotal = $checkOrderDetails->dishAddons->where('status', '!=', 'cancel')->sum('price_before_tax');
    //         } else {
    //             $orderDetailTotal = $checkOrderDetails->price_after_tax;
    //             $addonsTotal = $checkOrderDetails->dishAddons->where('status', '!=', 'cancel')->sum('price_after_tax');
    //         }
    //         $total = $orderDetailTotal + $addonsTotal;
    //         $data = [
    //             'dish_id' => $Branch_Dish->dish_id,
    //             'order_id' => $request->order_id,
    //             'status' => 'pending',
    //             'note' => $request->note ?? $checkOrderDetails->note,
    //             'quantity' => $request->quantity,
    //             'dish_size_id' => $size_id,
    //             'total_dish_price' =>  $total,
    //             'dish_order' => $request->dish_order ?? $checkOrderDetails->dish_order,
    //         ];
    //         // Broadcast event for cashier
    //         broadcast(new EditOrder($employee->id, $employee->branch_id, $data));
    //     }


    //     $chef = Employee::whereIn('id', $employees_ids['working_employee_ids'])->where('flag', 'Head Chef')->get();
    //     if (!$chef) {
    //         return respondError('Validation Error.', 400, ['error' => __('recipes.nochefssworknowinbranch')]);
    //     }

    //     //call function gives chefs and order to get each chef has witch order detail and call event for each one
    //     sendToKitchen($request->order_id, $lang);

    //     return ResponseWithSuccessData($lang, $data, 1);
    //     // } catch (Exception $e) {
    //     //     DB::rollBack();

    //     //     return RespondWithBadRequestWithData(['error' => $e->getMessage()]);
    //     // }
    // }
    public function orderViewItem(Request $request, $type, $itemId)
    {
        $lang =  $request->header('lang', 'en');
        App::setLocale($lang);

        $employee = auth('employee')->user();
        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $orderDetails = OrderDetail::where('id', $itemId)->with('dishAddons')->first();
        if (!$orderDetails) {
            $message = "item is not found";
            return respondErrorData($message, 400, $message);
        }

        // if ($orderDetails->status != "pending") {
        //     $message = "you can't allow to edit in this item, the dish is not in pending any more";
        //     return respondErrorData($message, 400, $message);
        // }

        $branchId = $orderDetails->order->branch_id;

        $dish_menu = BranchMenu::where(['dish_id' => $orderDetails->dish_id, 'branch_id' => $branchId])->first();

        $request['dishId'] = $dish_menu->id;
        $request['orderDetails'] = $orderDetails;
        return $this->menuDishesDetailsInEdit($request);
    }


    public function changeOrderTable(Request $request)
    {

        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();


        $created_by = $employee->id;

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'table_id' => 'required|exists:tables,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => __('validation.dataNotFound'),
                'data' => null,
                'errorData' => $validator->errors()
            ], 200);
        }

        $order = Order::where('id', $request->order_id)->first();
        if ($order->type != 'dine-in') {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => __('validation.ordernotdine-in'),
                'data' => null,
                'errorData' => ['error' => __('validation.ordernotdine-in')]
            ], 200);
        }
        $result = $this->orderService->changeOrderTable($request->table_id, $request->order_id);

        if ($result === true) {

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => $lang == 'en' ? 'Order table changed successfully' : 'تم تغير طاوله الطلب بنجاح',
                'data' => null
            ]);
        }

        return $result;
    }

    public function orderInvoice(Request $request, $invoiceId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $invoice = Invoice::where('id', $invoiceId)
            ->where('invoice_type', 'invoice')
            ->whereHas('orders', function ($q) use ($employee) {
                $q->where('branch_id', $employee->branch_id);
            })
            ->with([
                'orders.branch',
                'orders.table',
                'orders.coupon',
                'orderTransactions'
            ])->first();

        if (!$invoice) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'No invoice found for this ID' : 'لا توجد فاتورة لهذا المعرف.',
                'errorData' => ['error' => $lang == 'en' ? 'No invoice found for this ID' : 'لا توجد فاتورة لهذا المعرف.'],
                'data' => null
            ], 200);
        }

        $order = $invoice->order;

        if (!$order) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Order not found for this invoice' : 'الطلب غير موجود لهذه الفاتورة.',
                'errorData' => ['error' => $lang == 'en' ? 'Order not found for this invoice' : 'الطلب غير موجود لهذه الفاتورة.'],
                'data' => null
            ], 200);
        }

        $branchDetails = [
            'branch_id' => $order->branch->id ?? null,
            'branch_name' => $order->branch->name ?? null,
            'branch_address' => ($lang === 'ar') ? $order->branch->address_ar ?? null : $order->branch->address_en ?? null,
            'created_at' => $order->created_at,
            'invoice_number' => $invoice->invoice_num,
            'order_number' => $order->order_number ?? null,
            'branch_phone' => $order->branch->phone ?? null,
            'table_id' => $order->table->id ?? null,
            'table_number' => $order->table->table_number ?? null,
            'client_phone' => $order->client_country_code . $order->client_phone ?? null,
        ];

        // Get merged invoice details
        $mergedData = $this->invoiceService->merge_invoice_details($invoiceId);
        $mergedDishes = $mergedData['dishes'];

        $orderDetails = collect($mergedDishes)->map(function ($dish) use ($lang, $order) {
            $dishAddons = collect($dish['addons'])->map(function ($addon) use ($lang) {
                return [
                    'addon_id' => $addon['addon_id'],
                    'addon_name' => $addon['name'],
                    'quantity' => $addon['quantity'],
                    'price_before_tax' => formatFloat($addon['price_before_tax']),
                    'price_after_tax' => formatFloat($addon['price_after_tax']),
                    'note' => $addon['note'],
                ];
            })->values();

            // Calculate total dish price based on tax application
            $totalPrice = ($order->tax_application == 0) ? $dish['price_before_tax'] : $dish['price_after_tax'];

            $dishCouponId = $dish['coupon_id'] ?? null;
            $dishCouponValue = $dishCouponId ? ($dish['coupon_value'] ?? 0) : 0;

            return [
                'dish_id' => $dish['dish_id'],
                'dish_name' => $dish['name'],
                'size' => $dish['size'],
                'quantity' => $dish['quantity'],
                'total_dish_price' => formatFloat($dish['total_before_coupon']),
                'total_dish_price_coupon_applied' => formatFloat($totalPrice),
                'note' => $dish['note'],
                'addons' => $dishAddons,
                'coupon_id' => $dishCouponId,
                'coupon_value' => formatFloat($dishCouponValue),
                'coupon_title' => $dishCouponId ? $dish['coupon_title'] : null
            ];
        })->values();

        $subtotal = $invoice->total_before_tax;
        if ($order->tax_application == 1) {
            $subtotal += $invoice->tax;
        }
        $couponId = $order->coupon_id;
        $couponType = $order->coupon?->type;
        $couponValue = $order->coupon_value;
        $couponCode = $order->coupon?->code;
        $couponTitle = $order->coupon?->title;

        // If order doesn't have a coupon, check order details for coupon
        if (!$couponId) {
            $orderDetailsWithCoupons = $order->orderDetails->whereNotNull('coupon_id');
            if ($orderDetailsWithCoupons->isNotEmpty()) {
                $firstOrderDetailWithCoupon = $orderDetailsWithCoupons->first();
                $couponId = $firstOrderDetailWithCoupon->coupon_id;
                $couponType = $firstOrderDetailWithCoupon->coupon?->type;
                $couponTitle = $firstOrderDetailWithCoupon->coupon?->title;
                $couponCode = $firstOrderDetailWithCoupon->coupon?->code;
                $couponValue = $orderDetailsWithCoupons->sum('coupon_value');
            }
        }
        $invoiceSummary = [
            'subtotal_price' => formatFloat($subtotal),
            'subtotal_price_before_coupon' => formatFloat($invoice->total_before_coupon),
            'delivery_fees' => formatFloat($order->delivery_fees ?? null),
            'service_fees' => formatFloat($invoice->service_fees ?? null),
            'tax_value' => formatFloat($invoice->tax ?? null),
            'tax_apply' => !empty($invoice->tax) || $invoice->tax != 0,
            'tax_application' => $order->tax_application == 1 ? true : false,
            'tax_percentage' => formatFloat($order->tax_percentage ?? null),
            'coupon_id' => $couponId,
            'coupon_code' => $couponCode,
            'coupon_type' => $couponType,
            'coupon_value' => formatFloat($couponValue),
            'coupon_title' => $couponTitle,
            'total_price' => formatFloat($invoice->total_after_tax),
        ];
        $currencySymbol = $order->branch?->country?->currency_symbol ?? 'ج.م';

        // Get transaction for this specific invoice
        $invoiceTransaction = $invoice->orderTransactions->first();

        $invoiceData = [
            'order_type' => $order->type,
            'branch_details' => $branchDetails,
            'orderDetails' => $orderDetails,
            'payment_method' => $invoiceTransaction->payment_method ?? null,
            'payment_status' => $invoiceTransaction->payment_status ?? null,
            'invoice_summary' => $invoiceSummary,
            'currency_symbol' => $currencySymbol,
            'order_notes' => $order->note,
        ];

        $response = [
            'order_id' => $order->id,
            'invoice' => [$invoiceData],
        ];
        return ResponseWithSuccessData($lang, $response, 1);
    }

     public function printInvoice(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $user = auth('employee')->user();

            if ((!$user) || ($user->flag != 'cashier')) {
                return RespondWithBadRequest($lang, 4);
            }

            // cashier's info
            $today = Carbon::now()->format('Y-m-d');
            $shift = $this->timeTableService->getTimetableForDate($user->id, $today);
            if (!($shift['status'] == false)) {
                $shiftType = $lang == 'en' ? $shift['data']['timetable']['name_en'] : $shift['data']['timetable']['name_ar'];
                $user->shift_start = $shift['data']['on_duty_time'];
                $user->shift_end = $shift['data']['off_duty_time'];
                $user->shift_type = $shiftType;
                $user->employee_schedule_id = $shift['data']['employee_schedule_id'];
                $user->cross_day = $shift['data']['cross_day'];
            } else {
                $user->shift_start = null;
                $user->shift_end = null;
                $user->shift_type = null;
                $user->employee_schedule_id = null;
                $user->cross_day = null;
            }
            //            dd($user);

            $request->merge([
                'employee_schedule_id' => $user->employee_schedule_id,
                'shift_start' => $user->shift_start,
                'shift_end' => $user->shift_end,
            ]);
            //            dd($request->all());

            App::setLocale($lang);
            $validator = Validator::make($request->all(), [
                "order_id" => "required|exists:orders,id,deleted_at,NULL",
                "cashier_machine_id" => "required|exists:cashier_machines,id,deleted_at,NULL",
                // "payment_method" => "required|in:cash,credit",
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $order = Order::find($request->order_id);
            $order_id = $order->id;
            $not_delivery = $order->type != 'Delivery';
            //            $takeaway = $order->type == 'Takeaway';
            //            completed = $order->status == 'completed';
            $order_tracking = OrderTracking::where('order_id', $order->id)->orderby('created_at', 'desc')->orderby('id', 'desc')->first();
            $order_transaction = OrderTransaction::where('order_id', $order->id)->orderby('created_at', 'desc')->orderby('id', 'desc')->first();
            $order_details = OrderDetail::where('order_id', $order->id)->get();
            $order_table = Table::find($order->table_id) ?? false;
            $waiter_request = WaiterRequest::where('type', 2)
                ->whereJsonContains('order_ids', $order_id)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            DB::beginTransaction();
            if ($order_tracking && $order_transaction) {
                $hasPaidTransaction = OrderTransaction::where('order_id', $request->order_id)
                    ->where('payment_status', 'paid')
                    ->where('is_refund', 0)
                    ->exists();
                // if ($order_transaction->payment_status == 'paid' && $order_tracking->order_status == 'readyForPickup') {
                if ($hasPaidTransaction && $order->status != 'cancelled') {
                    $order->status = 'completed';
                    $order->print_status = 'done';
                    $order->save();
                    foreach ($order_details as $order_detail) {
                        if ($order_detail->status != 'cancel') {
                            $order_detail->status = 'completed';
                            $order_detail->save();
                        }
                    }

                    $order_tracking->order_status = 'completed';
                    $order_tracking->save();

                    $order_transaction->save();

                    if ($order_table) {
                        $order_table->status = 1;
                        $order_table->save();
                    }

                    $notifyData = [
                        'notification_type' => 'invoice',
                        'title_ar' => 'تمت طباعة الفاتورة' . $order->order_number,
                        'title_en' => 'Invoice Printed' . $order->order_number,
                        'description_ar' => 'تم طباعة فاتورة جديدة للطلب رقم ' . $order->order_number . ' بواسطة الكاشير ' . $user->first_name . ' ' . $user->last_name . '.',
                        'description_en' => 'A new invoice for order #' . $order->order_number . ' has been printed by cashier ' . $user->first_name . ' ' . $user->last_name . '.',
                        'created_by' => $user->id,
                        'order_id' => $order_id
                    ];
                    runNotificationToEmployees($order->branch_id, $notifyData, $user->id, $order_id, $lang);
                } else {
                    if ($order->status == 'cancelled') {

                        $order->print_status = 'cancelled';
                        $order->save();
                    }

                    $notifyData = [
                        'notification_type' => 'invoice',
                        'title_ar' => 'تمت طباعة الفاتورة' . $order->order_number,
                        'title_en' => 'Invoice Printed' . $order->order_number,
                        'description_ar' => 'تم طباعة فاتورة جديدة للطلب رقم ' . $order->order_number . ' بواسطة الكاشير ' . $user->first_name . ' ' . $user->last_name . '.',
                        'description_en' => 'A new invoice for order #' . $order->order_number . ' has been printed by cashier ' . $user->first_name . ' ' . $user->last_name . '.',
                        'created_by' => $user->id,
                        'order_id' => $order_id
                    ];
                    runNotificationToEmployees($order->branch_id, $notifyData, $user->id, $order_id, $lang);
                }

                $order->cashier_id = $user->id;
                $order->cashier_machine_id = $request->cashier_machine_id;
                $order->print_count_cashier += 1;
                $order->save();

                $cashierBalanceController = app(CashierBalanceController::class);
                $response = $cashierBalanceController->getCurrentBalance($request);

                if ($response->original['status']) {
                    broadcast(new TotalPaid($user->id, $response->original['data']));
                }

                DB::commit();

                $order->printed_by = $user->first_name . ' ' . $user->last_name;

                return ResponseWithSuccessData($lang, $order, 1);
            }
            else
            {
                DB::rollBack();
                if ($lang == 'en') {
                    $message = "Order not found.";
                } else {
                    $message = "الطلب غير موجود.";
                }
                return CustomRespondWithBadRequest($message);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return respondError($e->getMessage(), 400);
        }
    }
}
