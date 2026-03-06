<?php

namespace App\Http\Controllers\Api\CashierAPIs;

use Pusher\Pusher;
use App\Models\Dish;
use App\Models\Order;
use Knp\Snappy\Image;
// use App\Events\dishChangeStatus;
use App\Models\Client;
use App\Models\Coupon;
use ArPHP\I18N\Arabic;
use App\Models\Invoice;
use App\Models\Employee;
use App\Events\TotalPaid;
use App\Traits\ChatTrait;
use App\Models\BranchMenu;
use App\Models\OrderAddon;
use Mike42\Escpos\Printer;

use App\Events\TableStatus;
use App\Models\OrderDetail;
use App\Models\DishCategory;

use Illuminate\Http\Request;
use App\Models\OrderTracking;
use App\Models\InvoiceDetails;
use Illuminate\Support\Carbon;
use Mike42\Escpos\EscposImage;
use App\Models\BranchMenuAddon;
use App\Models\EmployeeMachine;
use Illuminate\Validation\Rule;
use App\Events\dishChangeStatus;
// use App\Http\Controllers\Controller;
use App\Models\OrderTransaction;
use App\Events\dishChangeStatus2;
use App\Events\orderChangeStatus;
use App\Traits\DishCategoryTrait;
use App\Models\BranchMenuCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\MenusIntegrationDishSize;
use Illuminate\Support\Facades\Validator;
use App\Services\ClientServices\TipService;
use App\Services\ClientServices\OrderService;
use App\Services\HR_Services\TimetableService;
use App\Services\ClientServices\InvoiceService;
use App\Services\AddressServices\BranchSiteService;
use Intervention\Image\ImageManagerStatic as ImageManager;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use App\Http\Controllers\Api\CashierAPIs\CashierInvoiceController;


class CashierOrderController extends Controller
{
    use ChatTrait, DishCategoryTrait;

    protected $orderService;
    protected $employee;
    protected $tipService;
    protected $invoiceService;
    protected $branchSiteService;

    public function __construct(OrderService $orderService, InvoiceService $invoiceService, TipService $tipService, BranchSiteService $branchSiteService)
    {
        $this->tipService = $tipService;
        $this->orderService = $orderService;
        $this->invoiceService = $invoiceService;
        $this->branchSiteService = $branchSiteService;
        $this->employee = auth('employee')->user();
    }
    public function listOrders(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $today = Carbon::today();
        $twentyFourHoursAgo = Carbon::now()->subHours(24);

        $shiftDetails = TimetableService::getTimetableForDate($this->employee->id, $today);

        // Build a single optimized query instead of multiple queries
        $ordersQuery = Order::where('branch_id', $this->employee->branch_id)
            ->whereHas('orderDetails')
            ->with(['tracking:id,order_id,order_status', 'table:id,table_number'])
            ->withSum('orderDetailsWithoutCancel', 'quantity')
            ->withSum('orderDetails', 'quantity')
            ->where(function ($query) use ($twentyFourHoursAgo) {
                $query->where(function ($subQuery) use ($twentyFourHoursAgo) {
                    // Include non-completed/cancelled orders regardless of time
                    $subQuery->whereNotIn('status', ['completed', 'cancelled']);
                })->orWhere(function ($subQuery) use ($twentyFourHoursAgo) {
                    // Include completed/cancelled orders only if within 24 hours
                    $subQuery->whereIn('status', ['completed', 'cancelled'])
                        ->where('created_at', '>=', $twentyFourHoursAgo);
                })->orWhere(function ($subQuery) use ($twentyFourHoursAgo) {
                    // Include delivery orders with unpaid transactions
                    $subQuery->where('type', 'Delivery')
                        ->whereHas('orderTransactions', function ($transQuery) {
                            $transQuery->where('payment_status', 'unpaid');
                        })
                        ->where('created_at', '>=', $twentyFourHoursAgo);
                });
            });

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

        $orders = $ordersQuery->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($orders->isEmpty()) {
            return RespondWithBadRequest($lang, 22);
        }

        $now = Carbon::now();
        $responseData = $orders->map(function ($order) use ($now) {
            // Calculate minutes passed since order creation
            $minutesPassed = $order->created_at->diffInMinutes($now);

            $orderItemsCount = $order->status === 'cancelled'
                ? ($order->orderDetails->sum('quantity') ?? 0)
                : ($order->orderDetailsWithoutCancel->sum('quantity') ?? 0);

            $orderData = [
                'order_type' => $order->type,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->tracking->last()->order_status ?? null,
                'order_items_count' => $orderItemsCount,
                'order_time' => $minutesPassed,
            ];

            if ($order->type == 'dine-in') {
                $orderData['table_number'] = $order->table->table_number ?? null;
                $orderData['table_type'] = $order->table->type ?? null; // 1 = internal (داخلي), 2 = external (خارجي)
            }

            return $orderData;
        });

        $orderTypeCounts = $orders->groupBy('type')->map(function ($group) {
            return $group->count();
        });

        $response = [
            'order_type_counts' => $orderTypeCounts,
            'orders' => $responseData,
        ];

        return ResponseWithSuccessData($lang, $response, 1);
    }

    public function listOrdersDetails(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $today = Carbon::today();
        $twentyFourHoursAgo = Carbon::now()->subHours(24);

        $shiftDetails = TimetableService::getTimetableForDate($this->employee->id, $today);

        // Single optimized query with proper eager loading
        // $ordersQuery = Order::where('branch_id', $this->employee->branch_id)
        //     ->whereHas('orderDetails')
        //     ->with([
        //         'tips',
        //         'tracking',
        //         'table',
        //         'orderTransactions',
        //         'Client',
        //         'address',
        //         'delivery',
        //         'Branch.country',
        //         'orderDetails',
        //         'orderDetails.dish',
        //         'orderDetails.dishSize',
        //         'orderDetails.coupon',
        //         'orderDetails.dishAddons',
        //         'orderDetails.dishAddons.Addon.addons',
        //         'orderDetails.dish.dishAddonsDetails',
        //         'orderDetails.dish.dishAddonsDetails.addons'
        //     ])
        //     ->withSum('orderDetailsWithoutCancel', 'quantity')
        //     ->withSum('orderDetails', 'quantity')
        //     ->where(function ($query) use ($twentyFourHoursAgo) {
        //         $query->where(function ($subQuery) use ($twentyFourHoursAgo) {
        //             // Include non-completed/cancelled orders regardless of time
        //             $subQuery->whereNotIn('status', ['completed', 'cancelled']);
        //         })->orWhere(function ($subQuery) use ($twentyFourHoursAgo) {
        //             // Include completed/cancelled orders only if within 24 hours
        //             $subQuery->whereIn('status', ['completed', 'cancelled'])
        //                 ->where('created_at', '>=', $twentyFourHoursAgo);
        //         })->orWhere(function ($subQuery) use ($twentyFourHoursAgo) {
        //             // Include delivery orders with unpaid transactions
        //             $subQuery->where('type', 'Delivery')
        //                 ->whereHas('orderTransactions', function ($transQuery) {
        //                     $transQuery->where('payment_status', 'unpaid');
        //                 })
        //                 ->where('created_at', '>=', $twentyFourHoursAgo);
        //         });
        //     })
        //     ->where(function ($query) use ($twentyFourHoursAgo) {
        //         // ✅ Handle Talabat logic
        //         $query->where('type', '!=', 'talabat')
        //             ->orWhere(function ($q) use ($twentyFourHoursAgo) {
        //                 $q->where('type', 'talabat')
        //                     ->where(function ($subQ) use ($twentyFourHoursAgo) {
        //                         // Show if paid OR unpaid but within 10 hours
        //                         $subQ->whereHas('orderTransactions', function ($trans) {
        //                             $trans->where('payment_status', 'paid');
        //                         })
        //                             ->orWhere(function ($transQ) use ($twentyFourHoursAgo) {
        //                                 $transQ->whereHas('orderTransactions', function ($trans) {
        //                                     $trans->where('payment_status', 'unpaid');
        //                                 })
        //                                     ->where('created_at', '>=', $twentyFourHoursAgo);
        //                             });
        //                     });
        //             });
        //     });

        $ordersQuery = Order::where('branch_id', $this->employee->branch_id)
        ->whereHas('orderDetails')
        ->with([
            'tips',
            'tracking',
            'table',
            'orderTransactions',
            'Client',
            'address',
            'delivery',
            'Branch.country',
            'orderDetails',
            'orderDetails.dish',
            'orderDetails.dishSize',
            'orderDetails.coupon',
            'orderDetails.dishAddons',
            'orderDetails.dishAddons.Addon.addons',
            'orderDetails.dish.dishAddonsDetails',
            'orderDetails.dish.dishAddonsDetails.addons'
        ])
        ->withSum('orderDetailsWithoutCancel', 'quantity')
        ->withSum('orderDetails', 'quantity')
        ->where(function ($query) use ($twentyFourHoursAgo) {

            // 🟡 كل الأوردرات غير المدفوعة (أي وقت)
            $query->whereHas('orderTransactions', function ($q) {
                $q->where('payment_status', 'unpaid');
            })

            // 🟢 الأوردرات المدفوعة خلال 24 ساعة فقط
            ->orWhere(function ($q) use ($twentyFourHoursAgo) {
                $q->whereHas('orderTransactions', function ($t) {
                    $t->where('payment_status', 'paid');
                })
                ->where('created_at', '>=', $twentyFourHoursAgo);
            });

        });




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

        $orders = $ordersQuery->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'List Order is empty' : 'الطلبات فارغة',
                'errorData' => ['error' => $lang == 'en' ? 'List Order is empty' : 'الطلبات فارغة'],
                'data' => null
            ], 200);
        }

        $responseData = $orders->map(function ($order) use ($lang) {
            // Use pre-calculated counts
            $orderItemsCount = $order->status === 'cancelled'
                ? ($order->orderDetails->sum('quantity') ?? 0)
                : ($order->orderDetailsWithoutCancel->sum('quantity') ?? 0);

            $orderData = [
                'order_type' => $order->type,
                'hasCoupon' => $order->coupon_id ? true : false,
                'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : ($order->address ? $order->address->user_name : $order->client_name),
                'client_phone' => $order->Client->flag != 'unknown' ? $order->Client->phone : ($order->address ? $order->address->address_phone : $order->client_phone),
                'status' => $order->tracking->last()->order_status ?? null,
                'payment_status' => $order->orderTransactions->last()->payment_status ?? null,
                'order_id' => $order->id,
                'created_at' => $order->created_at,
                'time' => $order->time,
                'date' => $order->date,
                'order_number' => $order->order_number,
                'order_items_count' => $orderItemsCount,
            ];

            $orderItems = $order->orderDetails->map(function ($detail) use ($lang, $order) {
                // Include cancelled addons if order is cancelled or if the dish itself is cancelled
                $includeCancelledAddons = $order->status === 'cancelled' || $detail->status === 'cancel';

                $addons = $detail->dishAddons
                    ->filter(function ($addon) use ($lang, $includeCancelledAddons) {
                        $hasValidName = $addon->Addon?->addons?->{($lang === 'ar' ? 'name_ar' : 'name_en')} ?? false;

                        if ($includeCancelledAddons) {
                            return $hasValidName;
                        } else {
                            return $addon->status !== 'cancel' && $hasValidName;
                        }
                    });

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

                //  dd($order->tips->menus_integration_id);

                // if (optional($order->tips?->first())->menus_integration_id === null)
                //  {
                $totalBeforeCoupon = $orderDetailTotal + $addonsTotal;



                // Check if dish has coupon_id - only apply coupon if it exists
                $dishCouponId = $detail->coupon_id ?? null;
                $dishCouponValue = $dishCouponId ? ($detail->coupon_value ?? 0) : 0;

                return [
                    'order_detail_id' => $detail->id,
                    'dish_name' => $detail->dish->name ?? null,
                    'dish_status' => $detail->status ?? null,
                    'size' => $detail->dish_size_id ?
                        (($lang === 'ar') ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
                        : null,
                    'addons' => $detail->dishAddons->map(function ($addon) use ($lang) {
                        return [
                            'addon_category_id' => $addon->Addon?->addon_category_id,
                            'addon_id' => $addon->Addon?->addon_id,
                            'addon_name' => ($lang === 'ar') ? ($addon->Addon?->addons?->name_ar ?? null) : ($addon->Addon?->addons?->name_en ?? null),
                            'addon_status' => $addon->status,
                        ];
                    }),
                    'dish_addons' => $detail->dish->dishAddonsDetails->map(function ($addon) use ($lang) {
                        return [
                            'addon_category_id' => $addon->addon_category_id,
                            'addon_id' => $addon->addon_id,
                            'addon_name' => $addon->addons?->name ?? null,
                        ];
                    }),
                    'quantity' => $detail->quantity,
                    // 'total_dish_price1' => formatFloat($detail->price_after_tax),
                    'total_dish_price' => ($order->type == 'talabat') ? formatFloat($detail->price_after_tax) : formatFloat($totalBeforeCoupon),
                    'total_dish_price_coupon_applied' => ($order->type == 'talabat') ? formatFloat($detail->price_after_tax) : formatFloat($total),
                    'coupon_value' => formatFloat($dishCouponValue),
                    'coupon_title' => $dishCouponId ? $detail->coupon?->title : null
                ];
            });

            if ($order->type == 'dine-in') {
                $orderData['table_number'] = $order->table->table_number ?? null;
                $orderData['table_type'] = $order->table->type ?? null; // 1 = internal (داخلي), 2 = external (خارجي)
            }
            if ($order->type == 'Delivery') {
                $orderData['delivery_id'] = $order->delivery?->id ?? null;
                $orderData['delivery_name'] = $order->delivery?->first_name . ' ' . $order->delivery?->last_name ?? null;
            }
            $totalPrice = $order->total_price_after_tax;
            $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';
            $detailsOrder = $this->getOrderDetailsData($order, $lang);

            return [
                'order_details' => $orderData,
                'details_order' => $detailsOrder,
                'order_items' => $orderItems,
                'total_price' => formatFloat($totalPrice),
                'currency_symbol' => $currencySymbol,
            ];
        });

        $response = [
            // counts by order\
            'order_counts' => $orders->count(),
            'orders' => $responseData,
        ];
        return ResponseWithSuccessData($lang, $response, 1);
    }
    public function orderDetailsById(Request $request, $orderId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $order = Order::where('id', $orderId)
            ->where('branch_id', $this->employee->branch_id)
            ->with([
                'tips',
                'tracking',
                'table',
                'orderTransactions',
                'Client',
                'address',
                'delivery',
                'Branch.country',
                'orderDetails',
                'orderDetails.dish',
                'orderDetails.dishSize',
                'orderDetails.coupon',
                'orderDetails.dishAddons',
                'orderDetails.dishAddons.Addon.addons',
                'orderDetails.dish.dishAddonsDetails',
                'orderDetails.dish.dishAddonsDetails.addons'
            ])
            ->withSum('orderDetailsWithoutCancel', 'quantity')
            ->withSum('orderDetails', 'quantity')
            ->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => $lang == 'en' ? 'Order not found' : 'الطلب غير موجود',
                'errorData' => ['error' => $lang == 'en' ? 'Order not found' : 'الطلب غير موجود'],
                'data' => null
            ], 200);
        }

        // Build response in the same structure
        $orderItemsCount = $order->status === 'cancelled'
            ? ($order->orderDetails->sum('quantity') ?? 0)
            : ($order->orderDetailsWithoutCancel->sum('quantity') ?? 0);

        $orderData = [
            'order_type' => $order->type,
            'hasCoupon' => $order->coupon_id ? true : false,
            'client_name' => $order->Client->flag != 'unknown'
                ? $order->Client->name
                : ($order->address ? $order->address->user_name : $order->client_name),
            'client_phone' => $order->Client->flag != 'unknown'
                ? $order->Client->phone
                : ($order->address ? $order->address->address_phone : $order->client_phone),
            'status' => $order->tracking->last()->order_status ?? null,
            'payment_status' => $order->orderTransactions->last()->payment_status ?? null,
            'order_id' => $order->id,
            'created_at' => $order->created_at,
            'time' => $order->time,
            'date' => $order->date,
            'order_number' => $order->order_number,
            'order_items_count' => $orderItemsCount,
        ];

        $orderItems = $order->orderDetails->map(function ($detail) use ($lang, $order) {
            $includeCancelledAddons = $order->status === 'cancelled' || $detail->status === 'cancel';

            $addons = $detail->dishAddons
                ->filter(function ($addon) use ($lang, $includeCancelledAddons) {
                    $hasValidName = $addon->Addon?->addons?->{($lang === 'ar' ? 'name_ar' : 'name_en')} ?? false;

                    if ($includeCancelledAddons) {
                        return $hasValidName;
                    } else {
                        return $addon->status !== 'cancel' && $hasValidName;
                    }
                });

            if ($order->tax_application == 0) {
                $orderDetailTotal = $detail->price_befor_tax;
                $addonsTotal = $addons->sum(fn($addon) => $addon->price_before_tax);
            } else {
                $orderDetailTotal = $detail->price_after_tax;
                $addonsTotal = $addons->sum(fn($addon) => $addon->price_after_tax);
            }

            $total = $orderDetailTotal + $addonsTotal;

            if ($order->tax_application == 0) {
                $orderDetailTotal = $detail->price_before_coupon;
                $addonsTotal = $addons->sum(fn($addon) => $addon->price_before_coupon);
            } else {
                $orderDetailTotal = $detail->price_after_tax;
                $addonsTotal = $addons->sum(fn($addon) => $addon->price_after_tax);
            }

            $totalBeforeCoupon = $orderDetailTotal + $addonsTotal;

            $dishCouponId = $detail->coupon_id ?? null;
            $dishCouponValue = $dishCouponId ? ($detail->coupon_value ?? 0) : 0;

            return [
                'order_detail_id' => $detail->id,
                'dish_name' => $detail->dish->name ?? null,
                'dish_status' => $detail->status ?? null,
                'size' => $detail->dish_size_id
                    ? (($lang === 'ar') ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
                    : null,
                'addons' => $detail->dishAddons->map(function ($addon) use ($lang) {
                    return [
                        'addon_category_id' => $addon->Addon?->addon_category_id,
                        'addon_id' => $addon->Addon?->addon_id,
                        'addon_name' => ($lang === 'ar')
                            ? ($addon->Addon?->addons?->name_ar ?? null)
                            : ($addon->Addon?->addons?->name_en ?? null),
                        'addon_status' => $addon->status,
                    ];
                }),
                'dish_addons' => $detail->dish->dishAddonsDetails->map(function ($addon) use ($lang) {
                    return [
                        'addon_category_id' => $addon->addon_category_id,
                        'addon_id' => $addon->addon_id,
                        'addon_name' => $addon->addons?->name ?? null,
                    ];
                }),
                'quantity' => $detail->quantity,
                'total_dish_price' => ($order->type == 'talabat')
                    ? formatFloat($detail->price_after_tax)
                    : formatFloat($totalBeforeCoupon),
                'total_dish_price_coupon_applied' => ($order->type == 'talabat')
                    ? formatFloat($detail->price_after_tax)
                    : formatFloat($total),
                'coupon_value' => formatFloat($dishCouponValue),
                'coupon_title' => $dishCouponId ? $detail->coupon?->title : null
            ];
        });

        if ($order->type == 'dine-in') {
            $orderData['table_number'] = $order->table->table_number ?? null;
            $orderData['table_type'] = $order->table->type ?? null; // 1 = internal (داخلي), 2 = external (خارجي)
        }

        if ($order->type == 'Delivery') {
            $orderData['delivery_id'] = $order->delivery?->id ?? null;
            $orderData['delivery_name'] = trim(($order->delivery?->first_name ?? '') . ' ' . ($order->delivery?->last_name ?? ''));
        }

        $totalPrice = $order->total_price_after_tax;
        $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';
        $detailsOrder = $this->getOrderDetailsData($order, $lang);
        $maxDishTime = $order->orderDetailsWithoutCancel->max(fn($detail) => $detail->dish->time ?? 0);

        $orderData['invoice'] = [
            'invoice_id' => $order->invoice?->id ?? null,
            'invoice_number' => $order->invoice_number,
            'invoice_print_status' => $order->print_status,
            'order_time' => $maxDishTime,
        ];
        $responseData = [
            'order_details' => $orderData,
            'details_order' => $detailsOrder,
            'order_items' => $orderItems,
            'total_price' => formatFloat($totalPrice),
            'currency_symbol' => $currencySymbol,
        ];

        return ResponseWithSuccessData($lang, ['order' => $responseData], 1);
    }

    // dalia inifinite scroll

    public function listOrdersDetailsEnchance(Request $request)
    {

        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $today = Carbon::today();
        $twentyFourHoursAgo = Carbon::now()->subHours(24);

        $shiftDetails = TimetableService::getTimetableForDate($this->employee->id, $today);

        $perPage = $request->get('per_page', 50); // default 50 per page

        $ordersQuery = Order::where('branch_id', $this->employee->branch_id)
            ->whereHas('orderDetails')
            ->with([
                'tips',
                'tracking',
                'table',
                'orderTransactions',
                'Client',
                'address',
                'delivery',
                'Branch.country',
                'orderDetails',
                'orderDetails.dish',
                'orderDetails.dishSize',
                'orderDetails.coupon',
                'orderDetails.dishAddons',
                'orderDetails.dishAddons.Addon.addons',
                'orderDetails.dish.dishAddonsDetails',
                'orderDetails.dish.dishAddonsDetails.addons'
            ])
            ->withSum('orderDetailsWithoutCancel', 'quantity')
            ->withSum('orderDetails', 'quantity')
            ->where(function ($query) use ($twentyFourHoursAgo) {
                $query->where(function ($subQuery) {
                    $subQuery->whereNotIn('status', ['completed', 'cancelled']);
                })->orWhere(function ($subQuery) use ($twentyFourHoursAgo) {
                    $subQuery->whereIn('status', ['completed', 'cancelled'])
                        ->where('created_at', '>=', $twentyFourHoursAgo);
                })->orWhere(function ($subQuery) use ($twentyFourHoursAgo) {
                    $subQuery->where('type', 'Delivery')
                        ->whereHas('orderTransactions', function ($transQuery) {
                            $transQuery->where('payment_status', 'unpaid');
                        })
                        ->where('created_at', '>=', $twentyFourHoursAgo);
                });
            });

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

        // ✅ PAGINATION HERE
        $orders = $ordersQuery
            ->where('created_at', '>=', Carbon::now()->subWeek())
            // ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'List Order is empty' : 'الطلبات فارغة',
                'errorData' => ['error' => $lang == 'en' ? 'List Order is empty' : 'الطلبات فارغة'],
                'data' => null
            ], 200);
        }

        $responseData = $orders->map(function ($order) use ($lang) {
            $orderItemsCount = $order->status === 'cancelled'
                ? ($order->orderDetails->sum('quantity') ?? 0)
                : ($order->orderDetailsWithoutCancel->sum('quantity') ?? 0);

            $orderData = [
                'order_type' => $order->type,
                'hasCoupon' => $order->coupon_id ? true : false,
                'client_name' => $order->Client->flag != 'unknown'
                    ? $order->Client->name
                    : ($order->address ? $order->address->user_name : $order->client_name),
                'client_phone' => $order->Client->flag != 'unknown'
                    ? $order->Client->phone
                    : ($order->address ? $order->address->address_phone : $order->client_phone),
                'status' => $order->tracking->last()->order_status ?? null,
                'payment_status' => $order->orderTransactions->last()->payment_status ?? null,
                'order_id' => $order->id,
                'created_at' => $order->created_at,
                'time' => $order->time,
                'date' => $order->date,
                'order_number' => $order->order_number,
                'order_items_count' => $orderItemsCount,
            ];

            $orderItems = $order->orderDetails->map(function ($detail) use ($lang, $order) {
                $includeCancelledAddons = $order->status === 'cancelled' || $detail->status === 'cancel';

                $addons = $detail->dishAddons->filter(function ($addon) use ($lang, $includeCancelledAddons) {
                    $hasValidName = $addon->Addon?->addons?->{($lang === 'ar' ? 'name_ar' : 'name_en')} ?? false;
                    return $includeCancelledAddons ? $hasValidName : ($addon->status !== 'cancel' && $hasValidName);
                });

                if ($order->tax_application == 0) {
                    $orderDetailTotal = $detail->price_befor_tax;
                    $addonsTotal = $addons->sum(fn($addon) => $addon->price_before_tax);
                } else {
                    $orderDetailTotal = $detail->price_after_tax;
                    $addonsTotal = $addons->sum(fn($addon) => $addon->price_after_tax);
                }

                $total = $orderDetailTotal + $addonsTotal;
                $totalBeforeCoupon = $total;

                $dishCouponId = $detail->coupon_id ?? null;
                $dishCouponValue = $dishCouponId ? ($detail->coupon_value ?? 0) : 0;

                return [
                    'order_detail_id' => $detail->id,
                    'dish_name' => $detail->dish->name ?? null,
                    'dish_status' => $detail->status ?? null,
                    'size' => $detail->dish_size_id ? (($lang === 'ar') ? optional($detail->dishSize)->size_name_ar : optional($detail->dishSize)->size_name_en) : null,
                    'addons' => $detail->dishAddons->map(fn($addon) => [
                        'addon_category_id' => $addon->Addon?->addon_category_id,
                        'addon_id' => $addon->Addon?->addon_id,
                        'addon_name' => ($lang === 'ar')
                            ? ($addon->Addon?->addons?->name_ar ?? null)
                            : ($addon->Addon?->addons?->name_en ?? null),
                        'addon_status' => $addon->status,
                    ]),
                    'quantity' => $detail->quantity,
                    // 'total_dish_price' => formatFloat($totalBeforeCoupon),
                    // 'total_dish_price1' => formatFloat($detail->price_after_tax),
                    'total_dish_price' => ($order->type == 'talabat') ? formatFloat($detail->price_after_tax) : formatFloat($totalBeforeCoupon),
                    'total_dish_price_coupon_applied' => ($order->type == 'talabat') ? formatFloat($detail->price_after_tax) : formatFloat($total),
                    'coupon_value' => formatFloat($dishCouponValue),
                    'coupon_title' => $dishCouponId ? $detail->coupon?->title : null,
                ];
            });

            if ($order->type == 'dine-in') {
                $orderData['table_number'] = $order->table->table_number ?? null;
                $orderData['table_type'] = $order->table->type ?? null; // 1 = internal (داخلي), 2 = external (خارجي)
            }
            if ($order->type == 'Delivery') {
                $orderData['delivery_id'] = $order->delivery?->id ?? null;
                $orderData['delivery_name'] = $order->delivery?->first_name . ' ' . $order->delivery?->last_name ?? null;
            }

            $totalPrice = $order->total_price_after_tax;
            $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';

            $detailsOrder = $this->getOrderDetailsData($order, $lang);

            return [
                'order_details' => $orderData,
                'details_order' => $detailsOrder,
                'order_items' => $orderItems,
                'total_price' => formatFloat($totalPrice),
                'currency_symbol' => $currencySymbol,
            ];
        });

        // ✅ Add pagination metadata
        $pagination = [
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
        ];

        $response = [
            'orders' => $responseData,
            'pagination' => $pagination,
        ];

        return ResponseWithSuccessData($lang, $response, 1);
    }

    private function getOrderDetailsData($order, $lang)
    {
        $orderDetails = $order->orderDetails->map(function ($detail) use ($lang, $order) {
            $includeCancelledAddons = $order->status === 'cancelled' || $detail->status === 'cancel';

            $addons = $detail->dishAddons
                ->filter(function ($addon) use ($lang, $includeCancelledAddons) {
                    $hasValidName = $addon->Addon?->addons?->{($lang === 'ar' ? 'name_ar' : 'name_en')};

                    if ($includeCancelledAddons) {
                        return $hasValidName;
                    } else {
                        return $addon->status !== 'cancel' && $hasValidName;
                    }
                });

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

            return [
                'order_detail_id' => $detail->id,
                'dish_id' => $detail->dish_id,
                'dish_name' => $detail->dish->name ?? null,
                'size' => $detail->dish_size_id ?
                    (($lang === 'ar') ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
                    : null,
                'quantity' => $detail->quantity,
                // 'total_dish_price' => formatFloat($totalBeforeCoupon),
                'total_dish_price' => ($order->order_details?->order_type === "talabat") ? formatFloat($detail->price_after_tax) : formatFloat($totalBeforeCoupon),
                'total_dish_price_coupon_applied' => ($order->type == 'talabat') ? formatFloat($detail->price_after_tax) : formatFloat($totalBeforeCoupon),
                'note' => $detail->note ?? null,
                'addons' => $detail->dishAddons->map(function ($addon) use ($lang) {
                    return ($lang === 'ar') ? $addon->Addon->addons->name_ar ?? null : $addon->Addon->addons->name_en ?? null;
                }),
                'coupon_id' => $dishCouponId,
                'coupon_value' => formatFloat($dishCouponValue),
                'coupon_title' => $dishCouponId ? $detail->coupon?->title : null
            ];
        });

        $subtotal = $order->total_price_befor_tax;
        if ($order->tax_application == 1) {
            $subtotal += $order->tax_value;
        }

        $couponId = $order->coupon_id;
        $couponType = $order->coupon?->type;
        $couponValue = $order->coupon_value;
        $couponTitle = $order->coupon?->title;

        if (!$couponId) {
            $orderDetailsWithCoupons = $order->orderDetails->whereNotNull('coupon_id');
            if ($orderDetailsWithCoupons->isNotEmpty()) {
                $firstOrderDetailWithCoupon = $orderDetailsWithCoupons->first();
                $couponId = $firstOrderDetailWithCoupon->coupon_id;
                $couponType = $firstOrderDetailWithCoupon->coupon?->type;
                $couponTitle = $firstOrderDetailWithCoupon->coupon?->title;
                $couponValue = $orderDetailsWithCoupons->sum('coupon_value');
            }
        }

        $orderSummary = [
            'order_number' => $order->order_number,
            'subtotal_price' => formatPrice($subtotal),
            'subtotal_price_before_coupon' => formatFloat($order->total_price_before_coupon),
            'total_price' => formatFloat($order->total_price_after_tax),
            'delivery_fees' => formatFloat($order->delivery_fees ?? null),
            'coupon_id' => $couponId ?? null,
            'coupon_type' => $couponType ?? null,
            'coupon_value' => formatFloat($couponValue ?? null),
            'coupon_title' => $couponTitle ?? null,
            'service_fees' => formatFloat($order->service_fees ?? null),
            'service_percentage' => formatFloat($order->service_percentage ?? null),
            'tax_value' => formatFloat($order->tax_value ?? null),
            'tax_apply' => !empty($order->tax_value) || $order->tax_value != 0,
            'tax_application' => $order->tax_application == 1 ? true : false,
            'tax_percentage' => formatFloat($order->tax_percentage ?? null),
            'order_notes' => $order->note ?? null,
        ];

        if ($order->type == 'Delivery') {
            $deliveryData = [
                'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : ($order->address ? $order->address->user_name : $order->client_name),
                'client_phone' => $order->Client->flag != 'unknown' ? $order->Client->phone : ($order->address ? $order->address->address_phone : $order->client_phone),
                'client_address_phone' => $order->address ? $order->address->address_phone : null,
                // 'client_address' => $order->address->address,
                'client_address' => $order->address?->address ?? null,
                'delivery_id' => $order->delivery?->id ?? null,
                'delivery_name' => $order->delivery?->first_name . ' ' . $order->delivery?->last_name ?? null,
            ];
        } else {
            $deliveryData = null;
        }

        $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';

        // Process transactions based on is_refund status
        $refundTransactions = $order->orderTransactions->where('is_refund', 1);
        $normalTransactions = $order->orderTransactions->where('is_refund', 0);

        $processedTransactions = [];

        // Handle normal transactions (is_refund = 0) - merge cash together and credit together separately
        if ($normalTransactions->isNotEmpty()) {
            $cashTransactions = $normalTransactions->where('payment_method', 'cash');
            $creditTransactions = $normalTransactions->where('payment_method', 'credit');

            // Merge all cash transactions into one
            if ($cashTransactions->isNotEmpty()) {
                $firstCashTransaction = $cashTransactions->first();
                $totalCashPaid = $cashTransactions->sum('paid');
                $totalCashRefund = $cashTransactions->sum('refund');

                $processedTransactions[] = [
                    'payment_status' => $firstCashTransaction->payment_status,
                    'payment_method' => 'cash',
                    'paid' => round($totalCashPaid, 2),
                    'refund' => round($totalCashRefund, 2),
                    'date' => $firstCashTransaction->date,
                    'is_refund' => 0,
                ];
            }

            // Merge all credit transactions into one
            if ($creditTransactions->isNotEmpty()) {
                $firstCreditTransaction = $creditTransactions->first();
                $totalCreditPaid = $creditTransactions->sum('paid');
                $totalCreditRefund = $creditTransactions->sum('refund');

                $processedTransactions[] = [
                    'payment_status' => $firstCreditTransaction->payment_status,
                    'payment_method' => 'credit',
                    'paid' => round($totalCreditPaid, 2),
                    'refund' => round($totalCreditRefund, 2),
                    'date' => $firstCreditTransaction->date,
                    'is_refund' => 0,
                ];
            }

            // Handle any other payment methods
            $otherTransactions = $normalTransactions->whereNotIn('payment_method', ['cash', 'credit']);
            foreach ($otherTransactions as $transaction) {
                $processedTransactions[] = [
                    'payment_status' => $transaction->payment_status,
                    'payment_method' => $transaction->payment_method,
                    'paid' => round($transaction->paid, 2),
                    'refund' => round($transaction->refund, 2),
                    'date' => $transaction->date,
                    'is_refund' => $transaction->is_refund,
                ];
            }
        }

        // Handle refund transactions (is_refund = 1)
        if ($refundTransactions->isNotEmpty()) {
            $firstRefundTransaction = $refundTransactions->first();
            $totalRefund = $refundTransactions->sum('refund');

            $processedTransactions[] = [
                'payment_status' => $firstRefundTransaction->payment_status,
                'payment_method' => $firstRefundTransaction->payment_method,
                'paid' => round($firstRefundTransaction->paid, 2),
                'refund' => round($totalRefund, 2),
                'date' => $firstRefundTransaction->date,
                'is_refund' => 1,
            ];
        }

        $transactions = $processedTransactions;

        return [
            'order_type' => $order->type,
            'status' => $order->tracking->last()->order_status ?? null,
            'transactions' => $transactions,
            'order_details' => $orderDetails,
            'order_summary' => $orderSummary,
            'delivery_data' => $deliveryData,
            'currency_symbol' => $currencySymbol,
        ];
    }
    public function orderDetails(Request $request, $orderId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $today = Carbon::today();

        $shiftDetails = TimetableService::getTimetableForDate($this->employee->id, $today);

        $ordersQuery = Order::where('branch_id', $this->employee->branch_id)->where('id', $orderId)
            ->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons', 'table', 'orderTransactions']);

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
        // if($orders->isEmpty()){
        //     return respondError($this->lang == 'en' ? 'Order does not exist.' : 'الطلب غير موجود', 404);

        // }
        $twentyFourHoursAgo = Carbon::now()->subHours(24);

        $deliveryOrders = Order::where('branch_id', $this->employee->branch_id)
            ->where('type', 'Delivery')
            ->where('id', $orderId)
            ->whereHas('orderTransactions', function ($query) {
                $query->where('payment_status', 'unpaid');
            })
            ->where('created_at', '>=', $twentyFourHoursAgo)
            ->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons', 'table', 'orderTransactions'])
            ->get();

        $orders = $orders->merge($deliveryOrders);

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.',
                'errorData' => ['error' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.'],
                'data' => null
            ], status: 404);
        }

        $responseData = $orders->map(function ($order) use ($lang) {
            $orderDetails = $order->orderDetails->map(function ($detail) use ($lang, $order) {
                // Include cancelled addons if order is cancelled or if the dish itself is cancelled
                $includeCancelledAddons = $order->status === 'cancelled' || $detail->status === 'cancel';

                $addons = $detail->dishAddons
                    ->filter(function ($addon) use ($lang, $includeCancelledAddons) {
                        $hasValidName = $addon->Addon?->addons?->{($lang === 'ar' ? 'name_ar' : 'name_en')} ?? false;

                        if ($includeCancelledAddons) {
                            return $hasValidName;
                        } else {
                            return $addon->status !== 'cancel' && $hasValidName;
                        }
                    });

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

                // Check if dish has coupon_id - only apply coupon if it exists
                $dishCouponId = $detail->coupon_id ?? null;
                $dishCouponValue = $dishCouponId ? ($detail->coupon_value ?? 0) : 0;

                return [
                    'order_detail_id' => $detail->id,
                    'dish_id' => $detail->dish_id,
                    'dish_name' => $detail->dish->name ?? null,
                    'size' => $detail->dish_size_id ?
                        (($lang === 'ar') ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
                        : null,
                    'quantity' => $detail->quantity,
                    // 'total_dish_price' => formatFloat($totalBeforeCoupon),

                    'total_dish_price' => ($order->type == 'talabat') ? formatFloat($detail->price_after_tax) : formatFloat($totalBeforeCoupon),
                    'total_dish_price_coupon_applied' => ($order->type == 'talabat') ? formatFloat($detail->price_after_tax) : formatFloat($total),
                    'note' => $detail->note ?? null,
                    'addons' => $detail->dishAddons->map(function ($addon) use ($lang) {
                        return ($lang === 'ar') ? ($addon->Addon?->addons?->name_ar ?? null) : ($addon->Addon?->addons?->name_en ?? null);
                    }),
                    'coupon_id' => $dishCouponId,
                    'coupon_value' => formatFloat($dishCouponValue),
                    'coupon_title' => $dishCouponId ? $detail->coupon?->title : null
                ];
            });

            $subtotal = $order->total_price_befor_tax;
            if ($order->tax_application == 1) {
                $subtotal += $order->tax_value;
            }

            $couponId = $order->coupon_id;
            $couponType = $order->coupon?->type;
            $couponValue = $order->coupon_value;
            $couponTitle = $order->coupon?->title;

            // If order doesn't have a coupon, check order details for coupon
            if (!$couponId) {
                $orderDetailsWithCoupons = $order->orderDetails->whereNotNull('coupon_id')->where('status', '!=', 'cancel');
                if ($orderDetailsWithCoupons->isNotEmpty()) {
                    $firstOrderDetailWithCoupon = $orderDetailsWithCoupons->first();
                    $couponId = $firstOrderDetailWithCoupon->coupon_id;
                    $couponType = $firstOrderDetailWithCoupon->coupon?->type;
                    $couponTitle = $firstOrderDetailWithCoupon->coupon?->title;
                    $couponValue = $orderDetailsWithCoupons->sum('coupon_value');
                }
            }

            $orderSummary = [
                'order_number' => $order->order_number,
                'subtotal_price' => formatPrice($subtotal),
                'subtotal_price_before_coupon' => formatFloat($order->total_price_before_coupon),
                'total_price' => formatFloat($order->total_price_after_tax),
                'delivery_fees' => formatFloat($order->delivery_fees ?? null),
                'coupon_id' => $couponId ?? null,
                'coupon_type' => $couponType ?? null,
                'coupon_value' => formatFloat($couponValue ?? null),
                'coupon_title' => $couponTitle ?? null,
                'service_fees' => formatFloat($order->service_fees ?? null),
                'service_percentage' => formatFloat($order->service_percentage ?? null),
                'tax_value' => formatFloat($order->tax_value ?? null),
                'tax_apply' => !empty($order->tax_value) || $order->tax_value != 0,
                'tax_application' => $order->tax_application == 1 ? true : false,
                'tax_percentage' => formatFloat($order->tax_percentage ?? null),
                'order_notes' => $order->note ?? null,
            ];

            if ($order->type == 'Delivery') {
                $deliveryData = [
                    'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : ($order->address ? $order->address->user_name : $order->client_name),
                    'client_phone' => $order->Client->flag != 'unknown' ? $order->Client->phone : ($order->address ? $order->address->address_phone : $order->client_phone),
                    'client_address_phone' => $order->address ? $order->address->address_phone : null,
                    'client_address' => $order->address->address,
                    'delivery_id' => $order->delivery?->id ?? null,
                    'delivery_name' => $order->delivery?->first_name . ' ' . $order->delivery?->last_name ?? null,
                ];
            } else {
                $deliveryData = null;
            }

            $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';

            // Process transactions based on is_refund status
            $refundTransactions = $order->orderTransactions->where('is_refund', 1);
            $normalTransactions = $order->orderTransactions->where('is_refund', 0);

            $processedTransactions = [];

            // Handle normal transactions (is_refund = 0) - merge cash together and credit together separately
            if ($normalTransactions->isNotEmpty()) {
                $cashTransactions = $normalTransactions->where('payment_method', 'cash');
                $creditTransactions = $normalTransactions->where('payment_method', 'credit');

                // Merge all cash transactions into one
                if ($cashTransactions->isNotEmpty()) {
                    $firstCashTransaction = $cashTransactions->first();
                    $totalCashPaid = $cashTransactions->sum('paid');
                    $totalCashRefund = $cashTransactions->sum('refund');

                    $processedTransactions[] = [
                        'payment_status' => $firstCashTransaction->payment_status,
                        'payment_method' => 'cash',
                        'paid' => round($totalCashPaid, 2),
                        'refund' => round($totalCashRefund, 2),
                        'date' => $firstCashTransaction->date,
                        'is_refund' => 0,
                    ];
                }

                // Merge all credit transactions into one
                if ($creditTransactions->isNotEmpty()) {
                    $firstCreditTransaction = $creditTransactions->first();
                    $totalCreditPaid = $creditTransactions->sum('paid');
                    $totalCreditRefund = $creditTransactions->sum('refund');

                    $processedTransactions[] = [
                        'payment_status' => $firstCreditTransaction->payment_status,
                        'payment_method' => 'credit',
                        'paid' => round($totalCreditPaid, 2),
                        'refund' => round($totalCreditRefund, 2),
                        'date' => $firstCreditTransaction->date,
                        'is_refund' => 0,
                    ];
                }

                // Handle any other payment methods
                $otherTransactions = $normalTransactions->whereNotIn('payment_method', ['cash', 'credit']);
                foreach ($otherTransactions as $transaction) {
                    $processedTransactions[] = [
                        'payment_status' => $transaction->payment_status,
                        'payment_method' => $transaction->payment_method,
                        'paid' => round($transaction->paid, 2),
                        'refund' => round($transaction->refund, 2),
                        'date' => $transaction->date,
                        'is_refund' => $transaction->is_refund,
                    ];
                }
            }

            // Handle refund transactions (is_refund = 1)
            if ($refundTransactions->isNotEmpty()) {
                $firstRefundTransaction = $refundTransactions->first();
                $totalRefund = $refundTransactions->sum('refund');

                $processedTransactions[] = [
                    'payment_status' => $firstRefundTransaction->payment_status,
                    'payment_method' => $firstRefundTransaction->payment_method,
                    'paid' => round($firstRefundTransaction->paid, 2),
                    'refund' => round($totalRefund, 2),
                    'date' => $firstRefundTransaction->date,
                    'is_refund' => 1,
                ];
            }

            $transactions = $processedTransactions;

            return [
                'order_type' => $order->type,
                'status' => $order->tracking->last()->order_status ?? null,
                'transactions' => $transactions,
                'order_details' => $orderDetails,
                'order_summary' => $orderSummary,
                'delivery_data' => $deliveryData,
                'currency_symbol' => $currencySymbol,
            ];
        });

        $response = [
            'orderDetails' => $responseData,
        ];

        return ResponseWithSuccessData($lang, $response, 1);
    }
    public function updateOrder(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);


        $validator = Validator::make($request->all(), [
            'delivery_id' => [
                'required',
                Rule::exists('employees', 'id')->where('flag', 'driver')
            ],
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Fetch the delivery employee
        $deliveryBranch = Employee::where('id', $request->delivery_id)
            ->where('flag', 'driver')
            ->value('branch_id');

        // Check if both employees belong to the same branch
        if ($this->employee->branch_id !== $deliveryBranch) {
            return respondError('Validation Error.', 400, ['error' => __('api.delivery_branch_mismatch')]);
        }
        $Order = Order::find($request->order_id);
        // Check if both employees belong to the same branch
        if ($this->employee->branch_id !== $Order->branch_id) {
            return respondError('Validation Error.', 400, ['error' => __('api.order_branch_mismatch')]);
        }
        if ($Order->status != 'packing') {
            return respondError('Validation Error.', 400, ['error' => __('api.order_not_packing')]);
        }
        $Order->delivery_id = $request->delivery_id;
        $Order->save();
        $data = [
            'order_id' => $Order->id,
            'title_ar' => "يوجد طلب جديد",
            'type' => 'delivery',
            'title_en' => "New order",
            'description_ar' => "تم تعيين تسليم جديد. الطلب [$Order->id] إلى [$Order->address->address]",
            'description_en' => "New delivery assigned. Order [$Order->id] to [$Order->address->address]",
        ];

        send_push_notification(
            $Order->delivery->device_token,
            $data['description_ar'],
            $data['description_en'],
            $data['title_ar'],
            $data['title_en'],
            "cashier",
            $request->delivery_id,
            $this->employee->id,
            $request->order_id, // or request ID
            $lang,
            'order'
        );
        $data = addNotification(
            'order',
            'delivery',
            $data['description_ar'],
            $data['description_en'],
            $data['title_ar'],
            $data['title_en'],
            $request->delivery_id,
            $this->employee->id,
            $lang,
            $request->order_id,
        );


        return ResponseWithSuccessData($lang, $Order, 1);
    }


    public function placeOrder_v2(Request $request, $type)
    {
        // return $request->all();
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|exists:branches,id',
            'type' => ['required'],
            'payment_method' => ['required'],
            'items' => 'required|array|min:1',
            'address_id' => 'nullable|exists:client_addresses,id',
            // 'table_id' => 'nullable|exists:tables,id',
            'coupon_code' => 'nullable|string',
            'cashier_machine_id' => 'nullable|exists:cashier_machines,id',
            'note' => 'nullable|string',

            // 'make_type' => ['nullable', Rule::in(['site', 'app', 'pos'])],
        ]);


        if ($validator->fails()) {
            return respondErrorData('Validation Errors', 400, $validator->errors());
        }
        Client::create([
            'name' => $request->client_name,
            'phone_number' => $request->client_phone,
            'country_code' => $request->client_country_code
        ]);
        if ($request->order_id) {
            $employee = auth('employee')->user();
            $created_by = $employee->id;
            // $cond_array = ['pending', 'inprogress'];
            $order = Order::where('id', $request->order_id)->first();
            if (!$order) {
                return respondError(__('order.order_not_found'), 400, __('order.order_not_found'));
            }
            // $order = Order::where('id', $request->order_id)->whereIn('status', $cond_array)->first();
            if ($order->status == "cancelled" || $order->latestTracking->order_status == "delivered" || $order->orderTransactions->contains('payment_status', 'paid')) {
                $message = "You can't add items in this order";
                return respondErrorData('errors', 400, [$lang === 'ar' ? 'لا يمكنك اضافة اطباق على الطلب' : $message]);
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

            // $hasUnpaidTransaction = $order->orderTransactions->contains(function ($transaction) {
            //     return $transaction->payment_status == 'unpaid';
            // });
            // if ($hasUnpaidTransaction) {

            $item_calculate = $this->orderService->CalculateOrder($order->id);
            $order->refresh();
            $order_transaction = OrderTransaction::where('order_id', $order->id)->where('payment_status', 'unpaid')->where('is_refund', 0)->first();
            $order_transaction->paid = $order->total_price_after_tax;
            // $order_transaction->coupon_id = $coupon ? $coupon->id : null;
            $order_transaction->save();
            // Notification for the new table
            $notifyDataNew = [
                'notification_type' => 'order',
                'description_ar' => 'تم أضافه طبق جديد للأوردر رقم ' . $order->order_number,
                'description_en' => 'New item has been added to order ' . $order->order_number,
                'title_ar' => ' أضافه طبق جديد للأوردر',
                'title_en' => 'new item added to order',
                'created_by' => null,
                'order_id' => $order->id
            ];
            $orderItemIds = (array) $response['collectedItems'];
            $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';

            $orderItemIds = collect($response['collectedItems'])->map(function ($item) use ($order, $currencySymbol, $lang) {
                $orderItem = OrderDetail::with([
                    'dish',
                    'dishSize',
                    'dishAddons.Addon.addons'
                ])->find($item['order_detail_id']);

                if (!$orderItem)
                    return null;

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
                        'addon_id' => $addon->Addon?->addon_id,
                        'addon_menu_id' => $menuAddonId,
                        'addon_name' => $addon->Addon?->addons?->name_ar ?? null,
                        'addon_status' => $addon->status,
                        'price' => formatFloat($price),
                    ], fn($v) => !is_null($v));
                })->values();

                // Sum only active addons
                $addonsTotal = $activeAddons->sum(fn($addon) => $addon->price_before_coupon ?? $addon->price_befor_tax ?? 0);
                $dishBase = $orderItem->price_before_coupon ?? $orderItem->price_befor_tax ?? 0;
                $totalBeforeCoupon = $dishBase + $addonsTotal;

                // Remove all null keys from final response
                return array_filter([
                    'order_detail_id' => $orderItem->id,
                    'quantity' => $orderItem->quantity,
                    'dish_status' => $orderItem->status,
                    'total_dish_price' => formatFloat($totalBeforeCoupon),
                    'dish_menu_id' => $branchMenu->id ?? null,
                    'dish_id' => $orderItem->dish_id,
                    'dish_name' => $orderItem->dish->name_ar ?? null,
                    'dish_image' => $orderItem->dish->image_url ?? null,
                    'currency_symbol' => $currencySymbol,
                    'size_id' => $sizeId,
                    'size_menu_id' => $sizeMenuId,
                    'addons' => $addons->isNotEmpty() ? $addons : null,
                    'note' => $orderItem->note,
                    'dish_order' => $orderItem->dish_order,
                ], fn($v) => !is_null($v));
            })->filter()->values()->toArray();

            $orderItemsCount = $order->status === 'cancelled'
                ? $order->orderDetails->sum('quantity')
                : $order->orderDetailsWithoutCancel->sum('quantity');
            // Create data for broadcasting
            $dish_data = [
                'order_id' => $order->id,
                'order_type' => $order->type,
                'order_items_count' => $orderItemsCount,
                'status' => $order->status,
                'added_items' => $orderItemIds ?? [],
                'total_price' => formatFloat($order->total_price_after_tax),

                'currency_symbol' => $currencySymbol,
                'date' => now()->toDateString(),
            ];

            // if($request->order_id)
            // {
            //     $order = Order::with('table')->find($request->order_id);
            //     if($order)
            //     {
            //         $this->print($request->items , $order);
            //     }
            // }
            runNotificationToEmployees($order->branch_id, $notifyDataNew, null, $order->id, $lang);
            broadcast(new dishChangeStatus2($dish_data));
            // broadcast(new dishChangeStatus($dish_data));

            return ResponseWithSuccessData($lang, ['order_id' => $order->id, 'invoice_id' => $response['order_invoice_id']], 1);
        } else {

            $data = $this->orderService->transformOrderRequest($request->all()); // items stre
            $response = $this->orderService->store_v2($data, 'true', 'api');

            $tiparray = [
                'data' => $data,
                'response' => $response->getData(true),
                'request' => $request->all()
            ];
            $tips = $this->tipService->create($tiparray);
            $responseData = $response->original;



            // dd($responseData);
            // return $responseData;

            //   if($request->edit_invoice == 'true')
            //     {
            //         $inv= [
            //             'tip'=>$tips || 0,
            //             'order_number'=>$responseData['data']['order_id'],
            //             'payment_status'=>'upaid',
            //             'cash_amount'=> $request->cash_amount || 0,
            //             'credit_amount'=>$request->credit_amount || 0,,
            //         ];
            //         $cashierInvoiceController = new CashierInvoiceController();
            //         $cashierInvoiceController->updateOrderInvoice($inv,$responseData['data']['order_id']);
            //     }
            // \Log::info('Response data:', $responseData);
            if ($request->type == 'talabat' && $responseData['data']) {
                // return $responseData['data'];
                // $data = (array) $responseData['data'];
                $data = json_decode(json_encode($responseData['data']), true);

                $this->talabat($data, $request);
                $orderAfterTalabatUpdate = Order::find($data['order_id']);
                $hasPaidTransaction = OrderTransaction::where('order_id', $data['order_id'])
                    ->where('payment_status', 'paid')
                    ->where('is_refund', 0)
                    ->first();
                if ($orderAfterTalabatUpdate->cashier_id != null && $orderAfterTalabatUpdate->cashier_machine_id != null && $hasPaidTransaction) {

                    $cashierBalanceController = app(CashierBalanceController::class);
                    $request = new Request([
                        'order_id' => $orderAfterTalabatUpdate->id,
                        "cashier_machine_id" => $orderAfterTalabatUpdate->cashier_machine_id,
                        'employee_schedule_id' => $orderAfterTalabatUpdate->cashier->employeeSchedules->first()->id ?? null,
                        "payment_method" => $hasPaidTransaction->payment_method ?? null
                    ]);

                    $response = $cashierBalanceController->getCurrentBalance($request);
                    if ($response->original['status']) {
                        broadcast(new TotalPaid($orderAfterTalabatUpdate->cashier_id, $response->original['data']));
                    }
                }
            }

            // if($responseData['data'])
            // {
            //     $order = Order::with('table')->find($responseData['data']['order_id']);
            //     if($order)
            //     {
            //         $this->print($request->items , $order);
            //     }
            // }


            if (!$responseData['status']) {
                if ($responseData['validation_type']) {
                    // dd($responseData);
                    return respondError($responseData['message'], 400, $responseData['errorData']);
                } else {
                    return respondErrorData('errors', 400, $responseData['errorData']['error']);
                }
            }


            $data = $responseData['data'];





            return ResponseWithSuccessData($lang, $data, 1);
        }
    }

    public function orderoffline(Request $request)
    {

        $lang = $request->header('lang', 'ar');
        $employee = auth('employee')->user();
        $cashier_machine_id = EmployeeMachine::where('employee_id', $employee->id)
            ->orderby('id', 'desc')
            ->first()->cashier_machine_id ?? null;
        App::setLocale($lang);
        if ($request->has('client_name') && $request->has('client_phone') && $request->has('client_country_code')) {
            Client::create([
                'name' => $request->client_name,
                'phone_number' => $request->client_phone,
                'country_code' => $request->client_country_code
            ]);
        }
        $orderId = null;

        if ($request->has('delivery_info')) {
            $mergedData = array_merge(
                $request->delivery_info,
                ['headers' => $request->headers->all()]
            );

            // إنشاء Request جديد من البيانات المدموجة
            $fakeRequest = new Request($mergedData);

            // إضافة الهيدرز للـ Fake Request
            foreach ($request->headers->all() as $key => $value) {
                $fakeRequest->headers->set($key, $value);
            }

            // استدعاء الخدمة
            // $this->branchSiteService->storeAddress($fakeRequest, $employee);
            $this->branchSiteService->storeAddress($fakeRequest, $employee);
        }

        if ($request->order_id != null) {
            $orderId = $request->order_id;
            $employee = auth('employee')->user();
            $created_by = $employee->id;
            $order = Order::where('id', $request->order_id)->first();
            if ($order) {
                if ($order->status == "cancelled" || $order->latestTracking->order_status == "delivered" || $order->orderTransactions->contains('payment_status', 'paid')) {
                    $message = "You can't add items in this order";
                    return respondErrorData('errors', 400, [$lang === 'ar' ? 'لا يمكنك اضافة اطباق على الطلب' : $message]);
                }
                $data = $this->orderService->transformOrderRequest($request->all());
                $taxApplication = getBranchSettings($data['branch_id'], 'tax_application');
                $taxPercentage = getBranchSettings($data['branch_id'], 'tax_percentage');
                $serviceFeesValue = in_array($request['type'], ['dine-in', 'reservation-table']) ? getBranchSettings($data['branch_id'], 'service_fees') : 0;
                $serviceFeesType = in_array($request['type'], ['dine-in', 'reservation-table']) ? getBranchSettings($data['branch_id'], 'service_fees_type') : "0";
                $response = $this->orderService->storeOrderItems(
                    $order,
                    $data['items'],
                    $request['type'],
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
                $order_transaction = OrderTransaction::where('order_id', $order->id)->where('payment_status', 'unpaid')->where('is_refund', 0)->first();
                $order_transaction->paid = $order->total_price_after_tax;
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
                $orderItemIds = (array) $response['collectedItems'];
                $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';

                $orderItemIds = collect($response['collectedItems'])->map(function ($item) use ($order, $currencySymbol, $lang) {
                    $orderItem = OrderDetail::with([
                        'dish',
                        'dishSize',
                        'dishAddons.Addon.addons'
                    ])->find($item['order_detail_id']);

                    if (!$orderItem)
                        return null;

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
                            'addon_id' => $addon->Addon?->addon_id,
                            'addon_menu_id' => $menuAddonId,
                            'addon_name' => $addon->Addon?->addons?->name_ar ?? null,
                            'addon_status' => $addon->status,
                            'price' => formatFloat($price),
                        ], fn($v) => !is_null($v));
                    })->values();

                    // Sum only active addons
                    $addonsTotal = $activeAddons->sum(fn($addon) => $addon->price_before_coupon ?? $addon->price_befor_tax ?? 0);
                    $dishBase = $orderItem->price_before_coupon ?? $orderItem->price_befor_tax ?? 0;
                    $totalBeforeCoupon = $dishBase + $addonsTotal;

                    // Remove all null keys from final response
                    return array_filter([
                        'order_detail_id' => $orderItem->id,
                        'quantity' => $orderItem->quantity,
                        'dish_status' => $orderItem->status,
                        'total_dish_price' => formatFloat($totalBeforeCoupon),
                        'dish_menu_id' => $branchMenu->id ?? null,
                        'dish_id' => $orderItem->dish_id,
                        'dish_name' => $orderItem->dish->name_ar ?? null,
                        'dish_image' => $orderItem->dish->image_url ?? null,
                        'currency_symbol' => $currencySymbol,
                        'size_id' => $sizeId,
                        'size_menu_id' => $sizeMenuId,
                        'addons' => $addons->isNotEmpty() ? $addons : null,
                        'note' => $orderItem->note,
                        'dish_order' => $orderItem->dish_order,
                    ], fn($v) => !is_null($v));
                })->filter()->values()->toArray();

                $orderItemsCount = $order->status === 'cancelled'
                    ? $order->orderDetails->sum('quantity')
                    : $order->orderDetailsWithoutCancel->sum('quantity');
                // Create data for broadcasting
                $dish_data = [
                    'order_id' => $order->id,
                    'order_type' => $order->type,
                    'order_items_count' => $orderItemsCount,
                    'status' => $order->status,
                    'added_items' => $orderItemIds ?? [],
                    'total_price' => formatFloat($order->total_price_after_tax),

                    'currency_symbol' => $currencySymbol,
                    'date' => now()->toDateString(),
                ];
                runNotificationToEmployees($order->branch_id, $notifyDataNew, null, $order->id, $lang);
                broadcast(new dishChangeStatus2($dish_data));

                // broadcast(new dishChangeStatus($dish_data));

                return ResponseWithSuccessData($lang, ['order_id' => $order->id, 'invoice_id' => $response['order_invoice_id']], 1);
            }
        } else {
            $data = $this->orderService->transformOrderRequest($request->all());
            $response = $this->orderService->store_v2_offline($data, 'true', 'api');
            $tiparray = [
                'data' => $data,
                'response' => $response->getData(true),
                'request' => $request->all()
            ];
            $tips = $this->tipService->create($tiparray);
            $responseData = $response->original;
            $orderId = $responseData['data']['order_id'] ?? null;
            if ($request->type == 'talabat') {
                // return $responseData['data'];
                // $data = (array) $responseData['data'];
                $data = json_decode(json_encode($responseData['data']), true);

                $this->talabat($data, $request);
                $orderAfterTalabatUpdate = Order::find($data['order_id']);
                $hasPaidTransaction = OrderTransaction::where('order_id', $data['order_id'])
                    ->where('payment_status', 'paid')
                    ->where('is_refund', 0)
                    ->first();
                if ($orderAfterTalabatUpdate->cashier_id != null && $orderAfterTalabatUpdate->cashier_machine_id != null && $hasPaidTransaction) {

                    $cashierBalanceController = app(CashierBalanceController::class);
                    $request = new Request([
                        'order_id' => $orderAfterTalabatUpdate->id,
                        "cashier_machine_id" => $orderAfterTalabatUpdate->cashier_machine_id,
                        'employee_schedule_id' => $orderAfterTalabatUpdate->cashier->employeeSchedules->first()->id ?? null,
                        "payment_method" => $hasPaidTransaction->payment_method ?? null
                    ]);

                    $response = $cashierBalanceController->getCurrentBalance($request);
                    if ($response->original['status']) {
                        broadcast(new TotalPaid($orderAfterTalabatUpdate->cashier_id, $response->original['data']));
                    }
                }
            }

            //edit invoice
            if ($request->edit_invoice === true) {
                $order = Order::where('branch_id', $employee->branch_id)
                    ->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons', 'table', 'orderTransactions'])
                    ->find($orderId);
                if ($order) {
                    DB::beginTransaction();
                    try {
                        // if ($request->has('cashier_machine_id')) {
                        $order->update([
                            'cashier_machine_id' => $request->cashier_machine_id ?? $cashier_machine_id,
                        ]);
                        // }

                        if ($request->has('order_status') && $order->type !== 'Delivery') {
                            DB::rollBack();
                            return response()->json([
                                'status' => false,
                                'code' => 400,
                                'message' => $lang == 'en' ? 'Order status can only be updated for Delivery orders.' : 'يمكن تحديث حالة الطلب فقط للطلبات التوصيل.',
                                'errorData' => ['error' => $lang == 'en' ? 'Order status can only be updated for Delivery orders.' : 'يمكن تحديث حالة الطلب فقط للطلبات التوصيل.'],
                                'data' => null
                            ], 200);
                        }
                        if ($request->has('order_status')) {
                            $order->tracking()->create([
                                'order_id' => $order->id,
                                'order_status' => $request->order_status,
                                'created_by' => $employee->id,
                            ]);
                            // Notify: Order status updated
                            $notifyData = [
                                'notification_type' => 'order',
                                'title_ar' => 'تم تحديث حالة الطلب',
                                'title_en' => 'Order status updated',
                                'description_ar' => 'تم تحديث حالة الطلب رقم ' . $orderId . ' إلى ' . $request->order_status . ' بواسطة الكاشير ' . $employee->first_name . ' ' . $employee->last_name . '.',
                                'description_en' => 'Order #' . $orderId . ' status updated to ' . $request->order_status . ' by cashier ' . $employee->first_name . ' ' . $employee->last_name . '.',
                                'created_by' => $employee->id,
                                'order_id' => $orderId
                            ];
                            runNotificationToEmployees($order->branch_id, $notifyData, $employee->id, $orderId, $lang);
                        }

                        $branchId = $employee->branch_id;
                        $created_by = $employee->id;
                        $makeType = 'cashier';
                        $orderDeposit = getBranchSettings($branchId, 'order_reservation_deposit');
                        $coupon = $order->coupon_id;

                        if ($request->has('payment_status') && $request->payment_status === 'paid') {
                            // $hasUnpaidTransaction = $order->orderTransactions->contains(function ($transaction) {
                            //     return $transaction->payment_status == 'unpaid';
                            // });
                            // dd($order->orderTransactions);
                            // if (!$hasUnpaidTransaction) {
                            //     DB::rollBack();
                            //     return RespondWithErrorMsg($lang == 'en' ? 'No unpaid transactions exist for this order.' : 'لا توجد معاملات غير مدفوعة لهذا الطلب.');
                            // }

                            // Update invoice status to paid
                            $invoice = Invoice::where('order_id', $orderId)->first();
                            $invoiceId = null;
                            if ($invoice) {
                                $this->invoiceService->updateInvoice($invoice->id);
                                $invoiceId = $invoice->id;
                            }

                            $cash_amount = $request->cash_amount ?? 0;
                            $credit_amount = $request->credit_amount ?? 0;

                            if ($order->type == 'dine-in' || $order->type == 'Takeaway') {
                                $totalPaid = round($cash_amount + $credit_amount, 3);
                                if ($totalPaid < (float) $order->total_price_after_tax) {
                                    DB::rollBack();
                                    return RespondWithErrorMsg(__('order.amount_wrong'));
                                }
                            }

                            // Only update statuses if the amount is correct
                            $order->update([
                                'status' => 'completed',
                                'print_status' => 'done',
                                'cashier_id' => $employee->id,
                                'modify_by' => $employee->id,
                            ]);

                            $order->tracking()->create([
                                'order_id' => $order->id,
                                'order_status' => 'completed',
                                'created_by' => $employee->id,
                            ]);

                            // Notify: Order status completed
                            $notifyData = [
                                'notification_type' => 'order',
                                'title_ar' => 'تم تحديث حالة الطلب',
                                'title_en' => 'Order status updated',
                                'description_ar' => 'تم تحديث حالة الطلب رقم ' . $orderId . ' إلى مكتمل بواسطة الكاشير ' . $employee->first_name . ' ' . $employee->last_name . '.',
                                'description_en' => 'Order #' . $orderId . ' status updated to completed by cashier ' . $employee->first_name . ' ' . $employee->last_name . '.',
                                'created_by' => $employee->id,
                                'order_id' => $orderId
                            ];
                            runNotificationToEmployees($order->branch_id, $notifyData, $employee->id, $orderId, $lang);

                            if ($order->type == 'dine-in' && $order->table) {
                                $order->Table()->update([
                                    'status' => 1,
                                    'modified_by' => $employee->id,
                                ]);

                                // Prepare table data for notification and broadcast
                                $table = $order->table->load(['floors', 'floorPartitions']);
                                $data = [
                                    'id' => $table->id,
                                    'name' => $table->name,
                                    'name_ar' => $table->name_ar,
                                    'name_en' => $table->name_en,
                                    'table_number' => $table->table_number,
                                    'status' => $table->status,
                                    'smoking' => $table->smoking,
                                    'floors' => [
                                        'id' => $table->floors->id ?? null,
                                        'name' => $table->floors->name ?? null,
                                        'name_ar' => $table->floors->name_ar ?? null,
                                        'name_en' => $table->floors->name_en ?? null
                                    ],
                                    'floor_partitions' => [
                                        'id' => $table->floorPartitions->id ?? null,
                                        'name' => $table->floorPartitions->name ?? null,
                                        'name_ar' => $table->floorPartitions->name_ar ?? null,
                                        'name_en' => $table->floorPartitions->name_en ?? null
                                    ]
                                ];
                                // Notify: Table status updated
                                $notifyData = [
                                    'notification_type' => 'table',
                                    'description_ar' => 'تم تحديث حالة طاولة بالفرع',
                                    'description_en' => 'Table status updated in your branch',
                                    'title_ar' => 'تم تحديث حالة طاولة',
                                    'title_en' => 'Table status updated',
                                    'created_by' => $employee->id,
                                    'order_id' => $order->id
                                ];
                                runNotificationToEmployees($table->branch_id, $notifyData, $employee->id, $table->id, $lang);
                                broadcast(new TableStatus($data, $table->branch_id, 'update'));
                            }

                            // Delete unpaid transactions
                            $order->orderTransactions()->where('payment_status', 'unpaid')->delete();

                            if ($order->type == 'dine-in' || $order->type == 'Takeaway') {
                                if ($cash_amount != 0) {
                                    $this->orderService->storePaymentTransaction(
                                        $order->id,
                                        $order->type,
                                        'cash',
                                        $created_by,
                                        $coupon,
                                        $cash_amount,
                                        $orderDeposit,
                                        $makeType,
                                        'paid',
                                        0,
                                        null,
                                        $invoiceId
                                    );
                                }
                                if ($credit_amount != 0) {
                                    $reference_number = $request->reference_number;
                                    $this->orderService->storePaymentTransaction(
                                        $order->id,
                                        $order->type,
                                        'credit',
                                        $created_by,
                                        $coupon,
                                        $credit_amount,
                                        $orderDeposit,
                                        $makeType,
                                        'paid',
                                        0,
                                        $reference_number,
                                        $invoiceId
                                    );
                                }
                            } else {
                                $this->orderService->storePaymentTransaction(
                                    $order->id,
                                    $order->type,
                                    'cash',
                                    $created_by,
                                    $coupon,
                                    $order->total_price_after_tax,
                                    $orderDeposit,
                                    $makeType,
                                    'paid',
                                    0,
                                    null,
                                    $invoice->id
                                );
                            }

                            // Notify: Invoice paid
                            $notifyData = [
                                'notification_type' => 'invoice',
                                'title_ar' => 'تم دفع الفاتورة' . $order->order_number,
                                'title_en' => 'Invoice paid' . $order->order_number,
                                'description_ar' => 'تم دفع فاتورة الطلب رقم ' . $order->order_numbr . ' بواسطة الكاشير ' . $employee->first_name . ' ' . $employee->last_name . '.',
                                'description_en' => 'Order invoice #' . $order->order_numbr . ' has been paid by cashier ' . $employee->first_name . ' ' . $employee->last_name . '.',
                                'created_by' => $employee->id,
                                'order_id' => $orderId
                            ];
                            runNotificationToEmployees($order->branch_id, $notifyData, $employee->id, $orderId, $lang);
                        }

                        DB::commit();

                        $updatedOrder = Order::where('branch_id', $employee->branch_id)
                            ->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons', 'table', 'orderTransactions'])
                            ->find($orderId);


                        $tiparray = [
                            'order_id' => $orderId,
                            'invoice_id' => $invoiceId,
                            'request' => $request,
                        ];
                        $tips = $this->tipService->update($tiparray);
                        //    return $tips;




                        // $responseData = $this->prepareInvoiceDetails($updatedOrder, $lang);
                        return ResponseWithSuccessData($lang, $updatedOrder, 1);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        return respondError($e->getMessage(), 500);
                    }
                }
            }

            if (!$responseData['status']) {
                if ($responseData['validation_type']) {
                    // dd($responseData);
                    return respondError($responseData['message'], 400, $responseData['errorData']);
                } else {
                    return respondErrorData('errors', 400, $responseData['errorData']['error']);
                }
            }
            $data = $responseData['data'];


            return ResponseWithSuccessData($lang, $data, 1);
        }
    }

    public function talabat($data, Request $request)
    {
        // dd($data , $request->all());
        $order = Order::find($data['order_id']);
        $order->tax_value = 0;
        $order->tax_percentage = 0;
        $order->tax_application = 0;
        $order->delivery_fees = 0;
        $order->total_price_befor_tax = $request->bill_amount;
        $order->total_price_before_coupon = $request->bill_amount;
        $order->total_price_after_tax = $request->bill_amount;
        $order->coupon_id = null;
        $order->coupon_value = 0;
        $order->service_fees = 0;
        $order->save();

        $orderDetails = OrderDetail::where('order_id', $data['order_id'])->get();
        foreach ($orderDetails as $detail) {
            foreach ($request->items as $item) {
                $branchMenuDetails = BranchMenu::where('id', $item['dish_id'])->where('branch_id', $request->branch_id)->first();

                if ($detail->dish_id == $branchMenuDetails->dish_id) {

                    $detail->tax_value = 0;
                    $detail->service_fees = 0;
                    $detail->price_befor_tax = $item['final_price'] ?? $item['finalPrice'];
                    $detail->price_after_tax = $item['final_price'] ?? $item['finalPrice'];
                    $detail->price_before_coupon = $item['final_price'] ?? $item['finalPrice'];
                    // $detail->price_after_coupon = $item['dish_price'] * $item['quantity'];
                    $detail->coupon_id = null;
                    $detail->coupon_value = 0;
                    $detail->save();

                    if ($item['sizeId'] != null) {
                        $dish_menu_integration = MenusIntegrationDishSize::where(['branch_menu_id' => $branchMenuDetails->id, 'branch_menu_size_id' => $item['sizeId'], 'menus_integration_id' => 1])->first();
                    } else {
                        $dish_menu_integration = $branchMenuDetails->menusIntegrationDishs()->where('menus_integration_id', 1)->first();
                    }
                    $invoiceDetails = InvoiceDetails::where(['details_id' => $detail->id, 'type' => 'dish'])->first();
                    if ($invoiceDetails) {
                        $invoiceDetails->tax = 0;
                        $invoiceDetails->service_fees = 0;
                        $invoiceDetails->total_before_tax = $dish_menu_integration->price * $invoiceDetails->quantity;
                        $invoiceDetails->total_before_coupon = $dish_menu_integration->price * $invoiceDetails->quantity;
                        $invoiceDetails->total_after_tax = $dish_menu_integration->price * $invoiceDetails->quantity;
                        // $invoiceDetails->price_after_coupon = $item['dish_price'] * $item['quantity'];
                        $invoiceDetails->coupon_id = null;
                        $invoiceDetails->coupon_value = 0;
                        $invoiceDetails->save();
                    }

                    //addon
                    if (isset($item['selectedAddons'])) {
                        foreach ($item['selectedAddons'] as $addon) {
                            $addonDetails = BranchMenuAddon::where('id', $addon['id'])->first();
                            $addon_talabat = $addonDetails->menusIntegrationDishAddons()->where('menus_integration_id', 1)->first();
                            $orderAddon = OrderAddon::where(['dish_addon_id' => $addonDetails->dish_addon_id, 'order_details_id' => $detail->id, 'order_id' => $detail->order_id])->first();
                            $orderAddon->tax_value = 0;
                            $orderAddon->service_fees = 0;
                            $orderAddon->price_before_tax = $addon_talabat->price * $detail->quantity;
                            $orderAddon->price_before_coupon = $addon_talabat->price * $detail->quantity;
                            $orderAddon->price_after_tax = $addon_talabat->price * $detail->quantity;
                            // $orderAddon->price_after_coupon = $item['dish_price'] * $item['quantity'];
                            // $orderAddon->coupon_id = null;
                            // $orderAddon->coupon_value = 0;
                            $orderAddon->save();
                            $orderAddon->refresh();
                            $invoiceDetails = InvoiceDetails::where(['details_id' => $orderAddon->id, 'type' => 'addon'])->first();
                            if ($invoiceDetails) {
                                $invoiceDetails->tax = 0;
                                $invoiceDetails->service_fees = 0;
                                $invoiceDetails->total_before_tax = $orderAddon->price_before_tax;
                                $invoiceDetails->total_before_coupon = $orderAddon->price_before_tax;
                                $invoiceDetails->total_after_tax = $orderAddon->price_before_tax;
                                // $invoiceDetails->price_after_coupon = $item['dish_price'] * $item['quantity'];
                                $invoiceDetails->coupon_id = null;
                                $invoiceDetails->coupon_value = 0;
                                $invoiceDetails->save();
                            }
                        }
                    }
                }
            }
        }

        $invoice = Invoice::find($data['invoice_id']);
        $invoice->tax = 0;
        $invoice->service_fees = 0;
        $invoice->delivery_fees = 0;
        $invoice->tax_percentage = 0;
        $invoice->service_percentage = 0;
        $invoice->coupon_value = 0;
        $invoice->total_before_tax = $request->bill_amount;
        $invoice->total_before_coupon = $request->bill_amount;
        $invoice->total_after_tax = $request->bill_amount;
        $invoice->coupon_id = null;
        $invoice->original_price = $request->bill_amount;
        $invoice->save();

        $transaction = OrderTransaction::where('order_id', $data['order_id'])->where('invoice_id', $data['invoice_id'])->first();
        $transaction->paid = $request->bill_amount;
        $transaction->original_price = $request->bill_amount;
        $transaction->save();
    }
    public function updateOrderStatus(Request $request, $orderId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,inprogress,packing,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $order = Order::where('branch_id', $this->employee->branch_id)
            ->where('id', $orderId)
            ->first();
        if (!$order) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Order not found or not eligible for status update' : 'الطلب غير موجود أو غير مؤهل لتحديث الحالة',
                'errorData' => ['error' => $lang == 'en' ? 'Order not found or not eligible for status update' : 'الطلب غير موجود أو غير مؤهل لتحديث الحالة'],
                'data' => null
            ], 200);
        }
        $order->update([
            'status' => $request->status,
            'modify_by' => $this->employee->id,
        ]);
        $data['order'] = $order;
        $data['status'] = $request->status;
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

                // $channelName = 'order-channel-' . $order->id . '-client-' . $order->client_id;
                // $channelPublic = 'order-channel-' . $order->id;

                if ($order->type == 'Delivery' && $request->status == 'on_way') {
                    if (!$order->delivery_id) {
                        return response()->json([
                            'status' => false,
                            'code' => 400,
                            'message' => $lang == 'en' ? 'Sorry, this order does not have a delivery man assigned' : 'عفوا يجب تحديد الدليفرى مسبقا',
                            'errorData' => ['error' => $lang == 'en' ? 'Sorry, this order does not have a delivery man assigned' : 'عفوا يجب تحديد الدليفرى مسبقا'],
                        ], 200);
                    }
                    $channel = $this->checkChatChannel($order);
                    $data['channel'] = $channel;
                }

                broadcast(new orderChangeStatus($data, $order));
            }
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => $lang == 'en' ? 'Order status updated successfully' : 'تم تحديث حالة الطلب بنجاح',
            'data' => [
                'order_id' => $order->id,
                'new_status' => $request->status,
                'date' => now()->toDateString(),

            ],
        ]);
    }
    public function cancel(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $employee = auth('employee')->user();

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => __('validation.dataNotFound'),
                'data' => null,
                'errorData' => ['error' => __('validation.dataNotFound')]
            ], 200);
        }

        $order = Order::find($request->order_id);

        if ($order->status == "cancelled") {
            return respondErrorData(__('validation.AlreadyDeleted'), 400, __('validation.AlreadyDeleted'));
        }

        $cancel_time = getBranchSettings($order->branch_id, 'time_cancellation');
        $minutesDifference = $order->created_at->diffInMinutes(Carbon::now());

        if (CheckOrderPaidStatus($order->id) && $order->status == "pending") {
            $order->status = 'cancelled';
            $order->print_status = 'cancelled';
            $order->modify_by = $employee->id;
            $order->save();

            $order_details = OrderDetail::where('order_id', $request->order_id)->update(['status' => 'cancel']);
            $order_tracking = new OrderTracking();
            $order_tracking->order_id = $request->order_id;
            $order_tracking->order_status = 'cancelled';
            $order_tracking->created_by = Auth::guard('api')->user()->id;
            $order_tracking->time = date('H:i:s');
            $order_tracking->save();
        } else {
            return respondErrorData(__('validation.OrderCanNotDeleteAnyMore'), 400, __('validation.OrderCanNotDeleteAnyMore'));
        }
        $data = null;
        return ResponseWithSuccessData($lang, $data, 1);
    }
    public function orderCancel(Request $request)
    {
        return $this->orderService->orderCancel($request);
    }
    public function orderEditItem(Request $request, $type)
    {
        return $this->orderService->orderEditItem($request, $type);
    }
    public function requestCancellation(Request $request)
    {
        return $this->orderService->requestCancellation($request);
    }
    public function orderViewItem(Request $request, $type, $itemId)
    {
        $lang = $request->header('lang', 'en');
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


    public function printKitchen(Request $request)
    {
        $items = $request->order_data['items'];
        $order = Order::with('table')->find($request->order_id);
        // $cashier = "192.168.100.102";
        // $printerIp = $cashier;

        $all = [];
        $contentDrinks = [];
        $contentFish = [];
        $contentGrills = [];



        // $items = $request;

        foreach ($items as $item) {

            $name_en = BranchMenu::find($item['dish_id'])->dish->name_en;

            // return $item["selectedAddons"];
            // $category = DishCategory::find($item['category']);
            $category = BranchMenuCategory::find($item['category'])->dish_categories;
            // dd($category);

            if ($category && $category->is_active == 1) {
                // مقبلات
                // if ($category->name == "مقبلات") {
                //     $flagAppetizers = true;
                $all[] = [
                    "name" => $item['dish_name'],
                    "name_en" => $name_en,
                    "note" => $item['note'],
                    "quantity" => $item['quantity'],
                    "price" => $item['dish_price'],
                    "size" => $item['sizeName'] == "" ? null : $item['sizeName'],
                    "addons" => $item['selectedAddons'],
                ];
                //     // مشروبات
                // } else{
                if (str_contains($category->name_ar, 'مشروبات')||str_contains($category->name_ar, 'عصائر فريش')||str_contains($category->name_ar, 'مشربات غازية')||str_contains($category->name_ar, 'مشروبات ساخنه')) {
                    // dd($item['dish_name']);
                    $flagDrinks = true;
                    $contentDrinks[] = [
                        "name" => $item['dish_name'],
                        "note" => $item['note'],
                        "name_en" => $name_en,
                        "quantity" => $item['quantity'],
                        "price" => $item['dish_price'],
                        "size" => $item['sizeName'] == "" ? null : $item['sizeName'],
                        "addons" => $item['selectedAddons'],
                    ];
                    // اسماك
                } elseif (str_contains($category->name_ar, 'اسماك')) {
                    // dd($item['dish_name']);
                    $flagFish = true;
                    $contentFish[] = [
                        "name" => $item['dish_name'],
                        "name_en" => $name_en,
                        "note" => $item['note'],
                        "quantity" => $item['quantity'],
                        "price" => $item['dish_price'],
                        "size" => $item['sizeName'] == "" ? null : $item['sizeName'],
                        "addons" => $item['selectedAddons'],
                    ];
                    // مشويات
                } elseif (
                    str_contains($category->name_ar, "السلطات") ||
                    str_contains($category->name_ar, "مشويات")
                ) {

                    $flagGrills = true;
                    $contentGrills[] = [
                        "name" => $item['dish_name'],
                        "name_en" => $name_en,
                        "note" => $item['note'],
                        "quantity" => $item['quantity'],
                        "price" => $item['dish_price'],
                        "size" => $item['sizeName'] == "" ? null : $item['sizeName'],
                        "addons" => $item['selectedAddons'],
                    ];
                }
            }
        }

        return response()->json([
            "status" => true,
            "message" => "Test mode - data grouped successfully",
            // "appetizers" => $this->formatTable($contentAppetizers),
            "IPdrinks" => "192.168.100.102",
            "IPfish" => "192.168.100.102",
            "IPgrills" => "192.168.100.102",
            "Ipall" => "192.168.100.102",
            "portdrinks" => "9100",
            "portfish" => "9100",
            "portgrills" => "9100",
            "portall" => "9100",
            "drinks" => $contentDrinks,
            "fish" => $contentFish,
            "grills" => $contentGrills,
            "allDish" => $all,
            "order" => $order,
            "type" => null,

        ]);
    }


    // print kitchen from waiter

    public function printwaiter(Request $request)
    {
        $order = Order::with([
            'orderDetails.dish',
            'orderDetails.dishAddons',
            'orderDetails.dishSize',
            'orderDetails.dishAddons.Addon.addons',
            'orderDetails.dish.dishAddonsDetails',
            'orderDetails.dish.dishAddonsDetails.addons',
            'table'
        ])->findOrFail($request->order_id);
        // return $order;
        $items = [];
        foreach ($order->orderDetails as $detail) {


            $items[] = [
                'name_ar'    => $detail->dish->name_ar,
                'name_en'    => $detail->dish->name_en,
                'quantity'   => $detail->quantity,
                'note'       => $detail->note,
                'category'   => $detail->dish->category_id,
                'dish_id'    => $detail->dish_id,
                'dish_price' => $detail->price_after_tax,
                'sizeName'       => $detail->dishSize->name ?? '',
                'selectedAddons'     => $detail->dishAddons->map(function ($addon) {
                    return [
                        'id' => $addon->dish_addon_id,
                        'name' => $addon->Addon->addons->name_ar ?? '',
                        // 'currency_symbol' => $addon->Addon->addons->currency_symbol ?? '',
                        // 'price' => $addon->price,
                    ];
                })
            ];
        }
        // return $items;



        $all = [];
        $contentDrinks = [];
        $contentFish = [];
        $contentGrills = [];

        // $items = $request;

        foreach ($items as $item) {

            // $name_en = BranchMenu::find($item['dish_id'])->dish->name_en;
            // $name_ar = BranchMenu::find($item['dish_id'])->dish->name_ar;
            $name_en = $item['name_en'];
            $name_ar = $item['name_ar'];

            // return $item["selectedAddons"];
            $category = DishCategory::find($item['category']);
            // $category = BranchMenuCategory::find($item['category'])->dish_categories;
            // dd($category);

            if ($category && $category->is_active == 1) {
                // مقبلات
                // if ($category->name == "مقبلات") {
                //     $flagAppetizers = true;
                $all[] = [
                    "name" => $name_ar,
                    "name_en" => $name_en,
                    "note" => $item['note'],
                    "quantity" => $item['quantity'],
                    "price" => $item['dish_price'],
                    "size" => $item['sizeName'] == "" ? null : $item['sizeName'],
                    "addons" => $item['selectedAddons'],
                ];
                //     // مشروبات
                // } else{
                if (str_contains($category->name_ar, 'مشروبات')||str_contains($category->name_ar, 'عصائر فريش')||str_contains($category->name_ar, 'مشربات غازية')||str_contains($category->name_ar, 'مشروبات ساخنه')) {
                    // dd($item['dish_name']);
                    $flagDrinks = true;
                    $contentDrinks[] = [
                        "name" => $name_ar,
                        "note" => $item['note'],
                        "name_en" => $name_en,
                        "quantity" => $item['quantity'],
                        "price" => $item['dish_price'],
                        "size" => $item['sizeName'] == "" ? null : $item['sizeName'],
                        "addons" => $item['selectedAddons'],
                    ];
                    // اسماك
                } elseif (str_contains($category->name_ar, 'اسماك')) {
                    // dd($item['dish_name']);
                    $flagFish = true;
                    $contentFish[] = [
                        "name" => $name_ar,
                        "name_en" => $name_en,
                        "note" => $item['note'],
                        "quantity" => $item['quantity'],
                        "price" => $item['dish_price'],
                        "size" => $item['sizeName'] == "" ? null : $item['sizeName'],
                        "addons" => $item['selectedAddons'],
                    ];
                    // مشويات
                } elseif (
                    str_contains($category->name_ar, "السلطات") ||
                    str_contains($category->name_ar, "مشويات")
                ) {

                    $flagGrills = true;
                    $contentGrills[] = [
                        "name" => $name_ar,
                        "name_en" => $name_en,
                        "note" => $item['note'],
                        "quantity" => $item['quantity'],
                        "price" => $item['dish_price'],
                        "size" => $item['sizeName'] == "" ? null : $item['sizeName'],
                        "addons" => $item['selectedAddons'],
                    ];
                }
            }
        }
        $order = Order::with('table')->find($request->order_id);

        return response()->json([
            "status" => true,
            "message" => "Test mode - waiter data grouped successfully",
            // "appetizers" => $this->formatTable($contentAppetizers),
            "IPdrinks" => "192.168.100.102",
            "IPfish" => "192.168.100.102",
            "IPgrills" => "192.168.100.102",
            "Ipall" => "192.168.100.102",
            "portdrinks" => "9100",
            "portfish" => "9100",
            "portgrills" => "9100",
            "portall" => "9100",
            "allDish" => $all,
            "drinks" => $contentDrinks,
            "fish" => $contentFish,
            "grills" => $contentGrills,
            "order" => $order,
            "type" => null,
        ]);
    }

    public function requestSplit(Request $request)
    {
        // split order (split dishes and quantities)
        /* this will work auto without approval of branch manager
           it will accept in all status of order
        */

        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'order_id' => [
                'required',
                'integer',
                Rule::exists('orders', 'id')->whereNull('deleted_at'),
            ],
            'new_table_id' => [
                'required',
                'integer',
                Rule::exists('tables', 'id')->whereNull('deleted_at'),
            ],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_detail_id' => [
                'required',
                'integer',
                Rule::exists('order_details', 'id')->whereNull('deleted_at'),
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ], [
            'items.required' => 'يجب اختيار عناصر للطلب.',
            'items.*.order_detail_id.exists' => 'أحد الأصناف المختارة غير موجود.',
            'items.*.quantity.min' => 'الكمية يجب أن تكون أكبر من صفر.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $orderDetailIds = collect($request->items)->pluck('order_detail_id')->toArray();

            // Fetch all order details in one query
            $orderDetails = OrderDetail::whereIn('id', $orderDetailIds)
                ->where('order_id', $request->order_id)
                ->get()
                ->keyBy('id');

            foreach ($request->items as $index => $item) {
                $orderDetail = $orderDetails[$item['order_detail_id']] ?? null;

                if (!$orderDetail) {
                    $validator->errors()->add(
                        "items.$index.order_detail_id",
                        "أحد الأصناف المختارة لا ينتمي لهذا الطلب."
                    );
                    continue;
                }

                // Check quantity
                if ($item['quantity'] > $orderDetail->quantity) {
                    $validator->errors()->add(
                        "items.$index.quantity",
                        "الكمية المطلوبة أكبر من الكمية المتاحة."
                    );
                }
            }

            // Check if order will have at least one item left after split
            $order = Order::with('orderDetails')->find($request->order_id);

            $remainingQty = 0;
            foreach ($order->orderDetails as $detail) {
                $splitQty = collect($request->items)
                    ->where('order_detail_id', $detail->id)
                    ->sum('quantity');
                $remainingQty += max(0, $detail->quantity - $splitQty);
            }

            if ($remainingQty < 1) {
                $validator->errors()->add(
                    'items',
                    'يجب أن يبقى على الأقل عنصر واحد في الطلب الأصلي بعد التقسيم.'
                );
            }
        });

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $result = $this->orderService->splitOrder($request);

        // If result is an error response, return it directly
        if ($result instanceof \Illuminate\Http\JsonResponse) {
            return $result;
        }

        // Get new order details for response
        $newOrder = Order::find($result->target_order_id);
        $sourceOrder = Order::find($result->source_order_id);

        // Return the order request with new order ID and details for frontend
        return ResponseWithSuccessData($lang, [
            'new_order_id' => $result->target_order_id,
            'new_order_number' => $newOrder ? $newOrder->invoice_number : null,
            'source_order_id' => $result->source_order_id,
            'source_order_number' => $sourceOrder ? $sourceOrder->invoice_number : null,
            'order_request' => $result
        ], 1);
    }
public function mergeRequest(Request $request)
    {
        // merge order (merge items)
        /* this will work auto without approval of branch manager
           it will accept in all status of order
        */

        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        // Support both naming conventions: primary_order_id/secondary_order_id (from frontend) 
        // and main_order_id/merged_order_id (legacy)
        $mainOrderId = $request->input('main_order_id') ?? $request->input('primary_order_id');
        $mergedOrderId = $request->input('merged_order_id') ?? $request->input('secondary_order_id');

        // Normalize request data for validation
        $request->merge([
            'main_order_id' => $mainOrderId,
            'merged_order_id' => $mergedOrderId,
        ]);

        $validator = Validator::make($request->all(), [
            'main_order_id' => [
                'required',
                'integer',
                Rule::exists('orders', 'id')->whereNull('deleted_at'),
            ],
            'merged_order_id' => [
                'required',
                'integer',
                Rule::exists('orders', 'id')->whereNull('deleted_at'),
            ],

        ]);

        $validator->after(function ($validator) use ($request, $lang) {

            $orders = Order::with('orderTransactions')
                ->whereIn('id', [
                    $request->main_order_id,
                    $request->merged_order_id
                ])
                ->get()
                ->keyBy('id');

            foreach (
                [
                    'main_order_id'   => $request->main_order_id,
                    'merged_order_id' => $request->merged_order_id,
                ] as $field => $orderId
            ) {

                $order = $orders[$orderId] ?? null;

                if (!$order) {
                    $validator->errors()->add(
                        $field,
                        $lang === 'en' ? 'Order not found.' : 'الطلب غير موجود.'
                    );
                    continue;
                }

                if ($order->type !== 'dine-in') {
                    $validator->errors()->add(
                        $field,
                        $lang === 'en'
                            ? 'Order must be dine-in.'
                            : 'يجب أن يكون الطلب داخل المطعم.'
                    );
                }

                if ($order->orderTransactions->last()?->payment_status === 'paid') {
                    $validator->errors()->add(
                        $field,
                        $lang === 'en'
                            ? 'Order is already paid.'
                            : 'لا يمكن دمج الطلب لأنه مدفوع.'
                    );
                }
            }
        });

        if ($validator->fails()) {
            return respondError(
                $lang === 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }
        $result = $this->orderService->mergeOrder($request);

        return ResponseWithSuccessData($lang, $result, 1);
    }

    // edit or cancel return printKitchen cashier
   public function EditorCancelPrintKitchen(Request $request)
    {
        // new order exist
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $orderId = $request->order['order_id'];
        $orderNew = Order::where('id', $orderId)
            // ->where('branch_id', $this->employee->branch_id)
            ->with([
                'table',
                'orderDetails',
                'orderDetails.dish',
                'orderDetails.dishSize',
                'orderDetails.coupon',
                'orderDetails.dishAddons',
                'orderDetails.dishAddons.Addon.addons',
                'orderDetails.dish.dishAddonsDetails',
                'orderDetails.dish.dishAddonsDetails.addons'
            ])
            ->withSum('orderDetailsWithoutCancel', 'quantity')
            ->withSum('orderDetails', 'quantity')
            ->first();

        if (!$orderNew) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => $lang == 'en' ? 'Order not found' : 'الطلب غير موجود',
                'errorData' => ['error' => $lang == 'en' ? 'Order not found' : 'الطلب غير موجود'],
                'data' => null
            ], 200);
        }

        // Build response in the same structure
        $orderItemsCount = $orderNew->status === 'cancelled'
            ? ($orderNew->orderDetails->sum('quantity') ?? 0)
            : ($orderNew->orderDetailsWithoutCancel->sum('quantity') ?? 0);

        $orderDataNew = [
            'order_type' => $orderNew->type,
            'hasCoupon' => $orderNew->coupon_id ? true : false,
            'client_name' => $orderNew->Client->flag != 'unknown'
                ? $orderNew->Client->name
                : ($orderNew->address ? $orderNew->address->user_name : $orderNew->client_name),
            'client_phone' => $orderNew->Client->flag != 'unknown'
                ? $orderNew->Client->phone
                : ($orderNew->address ? $orderNew->address->address_phone : $orderNew->client_phone),
            'status' => $orderNew->tracking->last()->order_status ?? null,
            'payment_status' => $orderNew->orderTransactions->last()->payment_status ?? null,
            'order_id' => $orderNew->id,
            'created_at' => $orderNew->created_at,
            'time' => $orderNew->time,
            'date' => $orderNew->date,
            'order_number' => $orderNew->order_number,
            'order_items_count' => $orderItemsCount,
        ];

        $orderItemsNew = $orderNew->orderDetails->map(function ($detail) use ($lang, $orderNew) {
            $includeCancelledAddons = $orderNew->status === 'cancelled' || $detail->status === 'cancel';

            $addons = $detail->dishAddons
                ->filter(function ($addon) use ($lang, $includeCancelledAddons) {
                    $hasValidName = $addon->Addon?->addons?->{($lang === 'ar' ? 'name_ar' : 'name_en')} ?? false;

                    if ($includeCancelledAddons) {
                        return $hasValidName;
                    } else {
                        return $addon->status !== 'cancel' && $hasValidName;
                    }
                });

            if ($orderNew->tax_application == 0) {
                $orderDetailTotal = $detail->price_befor_tax;
                $addonsTotal = $addons->sum(fn($addon) => $addon->price_before_tax);
            } else {
                $orderDetailTotal = $detail->price_after_tax;
                $addonsTotal = $addons->sum(fn($addon) => $addon->price_after_tax);
            }

            $total = $orderDetailTotal + $addonsTotal;

            if ($orderNew->tax_application == 0) {
                $orderDetailTotal = $detail->price_before_coupon;
                $addonsTotal = $addons->sum(fn($addon) => $addon->price_before_coupon);
            } else {
                $orderDetailTotal = $detail->price_after_tax;
                $addonsTotal = $addons->sum(fn($addon) => $addon->price_after_tax);
            }

            $totalBeforeCoupon = $orderDetailTotal + $addonsTotal;

            $dishCouponId = $detail->coupon_id ?? null;
            $dishCouponValue = $dishCouponId ? ($detail->coupon_value ?? 0) : 0;

            return [
                'order_detail_id' => $detail->id,
                'dish_id' => $detail->dish_id,
                'dish_name' => $detail->dish->name ?? null,
                'dish_status' => $detail->status ?? null,
                'size' => $detail->dish_size_id
                    ? (($lang === 'ar') ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
                    : null,
                'addons' => $detail->dishAddons->map(function ($addon) use ($lang) {
                    return [
                        'addon_category_id' => $addon->Addon?->addon_category_id,
                        'addon_id' => $addon->Addon?->addon_id,
                        'addon_name' => ($lang === 'ar')
                            ? ($addon->Addon?->addons?->name_ar ?? null)
                            : ($addon->Addon?->addons?->name_en ?? null),
                        'addon_status' => $addon->status,
                    ];
                }),
                'dish_addons' => $detail->dish->dishAddonsDetails->map(function ($addon) use ($lang) {
                    return [
                        'addon_category_id' => $addon->addon_category_id,
                        'addon_id' => $addon->addon_id,
                        'addon_name' => $addon->addons?->name ?? null,
                    ];
                }),
                'quantity' => $detail->quantity,
                'total_dish_price' => ($orderNew->type == 'talabat')
                    ? formatFloat($detail->price_after_tax)
                    : formatFloat($totalBeforeCoupon),
                'total_dish_price_coupon_applied' => ($orderNew->type == 'talabat')
                    ? formatFloat($detail->price_after_tax)
                    : formatFloat($total),
                'coupon_value' => formatFloat($dishCouponValue),
                'coupon_title' => $dishCouponId ? $detail->coupon?->title : null
            ];
        });
        // return $orderItemsNew;

        // old order in request
        // compare between orderitemNew and orderitem Request

        $orderItemsRequest = collect($request->order['order_data']['order_items'])
            ->keyBy('order_detail_id');

        $result = [];
        if($request->order['type'] == 'edit'){
              foreach ($orderItemsNew as $dbItem) {

            $requestItem = $orderItemsRequest[$dbItem['order_detail_id']] ?? null;

            // لو مش موجود في الريكوست
            if (!$requestItem) {
                $dbItem['x'] = false;
                $result[] = $dbItem;
                continue;
            }

            $isDifferent = false;

            // quantity
            if ($requestItem['quantity'] != $dbItem['quantity'] ) {
                $isDifferent = true;
            }

            // price
            if (($requestItem['total_dish_price'] ?? null) != ($dbItem['total_dish_price'] ?? null)) {
                $isDifferent = true;
            }

            // size
            if (($requestItem['size'] ?? null) != ($dbItem['size'] ?? null)) {
                $isDifferent = true;
            }

            // addons
            $reqAddons = collect($requestItem['dish_addons'] ?? [])
                ->pluck('addon_id')->sort()->values()->toArray();

            $dbAddons = collect($dbItem['dish_addons'] ?? [])
                ->pluck('addon_id')->sort()->values()->toArray();

            if ($reqAddons != $dbAddons) {
                $isDifferent = true;
            }
            $requestItem['dish_id'] = $dbItem['dish_id'] ?? null;

            // 👈 حسب طلبك

            $requestItem['x'] = $isDifferent; // true لو مختلف
            $dbItem['x'] = false;  // دايمًا false


            if($isDifferent == true)
            {
                    $result[] = $requestItem;
                    $result[] = $dbItem;
            }
            else
            {
                $result[] = $dbItem;
            }
        }

        }
        else
        {
            // cancel
            foreach ($orderItemsNew as $dbItem) {
                $requestItem = $orderItemsRequest[$dbItem['order_detail_id']] ?? null;
                if ($requestItem && $dbItem['dish_status'] == 'cancel') {
                $dbItem['x'] = true;  // دايمًا true
                }
                else
                {
                 $dbItem['x'] = false;  // دايمًا true
                }
                $result[] = $dbItem;
            }


        }




        // return $result;

        $all = [];
        $contentDrinks = [];
        $contentFish = [];
        $contentGrills = [];

        $items = $result;

        foreach ($items as $item) {
                // return $item;

             $branchMenu = BranchMenu::find($item['dish_id']);

            $dish = $branchMenu?->dish;

            if (!$dish) {
                $dish = Dish::find($item['dish_id']);
            }
            $name_en = $dish->name_en;
            $category = DishCategory::find($dish->category_id);



            // return $item["selectedAddons"];
            $category = DishCategory::find($dish->category_id);
            // $category = BranchMenuCategory::find($dish->category_id)->dish_categories;
            // dd($category);

            if ($category && $category->is_active == 1) {
                // مقبلات
                // if ($category->name == "مقبلات") {
                //     $flagAppetizers = true;
                $all[] = [
                    "name" => $item['dish_name'],
                    "name_en" => $name_en,
                    "note" => $item['note'] ?? null,
                    "quantity" => $item['quantity'],
                    "x" => $item['x'],
                    // "price" => $item['dish_price'],
                    "size" => $item['size'] == "" ? null : $item['size'],
                    "addons" => collect($item['dish_addons'])->map(function ($addon) {
                        return [
                            'addon_category_id' => $addon['addon_category_id'] ?? null,
                            'addon_id' => $addon['addon_id'] ?? null,
                            'addon_name' => $addon['addon_name'] ?? '',
                        ];
                    }),
                ];
                //     // مشروبات
                // } else{
                if (str_contains($category->name_ar, 'مشروبات')||str_contains($category->name_ar, 'عصائر فريش')||str_contains($category->name_ar, 'مشربات غازية')||str_contains($category->name_ar, 'مشروبات ساخنه')) {
                    // dd($item['dish_name']);
                    $flagDrinks = true;
                    $contentDrinks[] = [
                        "name" => $item['dish_name'],
                        "note" => $item['note'] ?? null,
                        "name_en" => $name_en,
                        "quantity" => $item['quantity'],
                        "x" => $item['x'],
                        // "price" => $item['dish_price'],
                        "size" => $item['size'] == "" ? null : $item['size'],
                        "addons" => collect($item['dish_addons'])->map(function ($addon) {
                            return [
                                'addon_category_id' => $addon['addon_category_id'] ?? null,
                                'addon_id' => $addon['addon_id'] ?? null,
                                'addon_name' => $addon['addon_name'] ?? '',
                            ];
                        }),
                    ];
                    // اسماك
                } elseif (str_contains($category->name_ar, 'اسماك')) {
                    // dd($item['dish_name']);
                    $flagFish = true;
                    $contentFish[] = [
                        "name" => $item['dish_name'],
                        "name_en" => $name_en,
                        "note" => $item['note'] ?? null,
                        "quantity" => $item['quantity'],
                        "x" => $item['x'],
                        // "price" => $item['dish_price'],
                        "size" => $item['size'] == "" ? null : $item['size'],
                        "addons" => collect($item['dish_addons'])->map(function ($addon) {
                            return [
                                'addon_category_id' => $addon['addon_category_id'] ?? null,
                                'addon_id' => $addon['addon_id'] ?? null,
                                'addon_name' => $addon['addon_name'] ?? '',
                            ];
                        }),
                    ];
                    // مشويات
                } elseif (
                    str_contains($category->name_ar, "السلطات") ||
                    str_contains($category->name_ar, "مشويات")
                ) {

                    $flagGrills = true;
                    $contentGrills[] = [
                        "name" => $item['dish_name'],
                        "name_en" => $name_en,
                        "note" => $item['note'] ?? null,
                        "quantity" => $item['quantity'],
                        "x" => $item['x'],
                        // "price" => $item['dish_price'],
                        "size" => $item['size'] == "" ? null : $item['size'],
                        "addons" => collect($item['dish_addons'])->map(function ($addon) {
                            return [
                                'addon_category_id' => $addon['addon_category_id'] ?? null,
                                'addon_id' => $addon['addon_id'] ?? null,
                                'addon_name' => $addon['addon_name'] ?? '',
                            ];
                        }),
                    ];
                }
            }
        }
           $order = Order::with('table')->find($orderId);

        return response()->json([
            "status" => true,
            "message" => "Test mode - data grouped successfully",
            // "appetizers" => $this->formatTable($contentAppetizers),
            "IPdrinks" => "192.168.100.102",
            "IPfish" => "192.168.100.102",
            "IPgrills" => "192.168.100.102",
            "Ipall" => "192.168.100.102",
            "portdrinks" => "9100",
            "portfish" => "9100",
            "portgrills" => "9100",
            "portall" => "9100",
            "drinks" => $contentDrinks,
            "fish" => $contentFish,
            "grills" => $contentGrills,
            "allDish" => $all,
            "order" => $order,
            "type" => "edit_cancel",
        ]);
    }

}
