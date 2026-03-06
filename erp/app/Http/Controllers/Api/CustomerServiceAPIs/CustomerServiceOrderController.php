<?php

namespace App\Http\Controllers\Api\CustomerServiceAPIs;

use App\Events\CashierNotify;
use App\Events\EditOrder;
use App\Events\NotifySent;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchMenu;
use App\Models\BranchMenuAddon;
use App\Models\BranchMenuSize;
use App\Models\BranchTime;
use App\Models\DeliveryComplaints;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderTransaction;
use App\Models\User;
use App\Services\ClientServices\OrderService;
use Carbon\Carbon;
use Google\Rpc\Context\AttributeContext\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CustomerServiceOrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    public function listBranches(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        App::setLocale($lang);

        $branches = Branch::where('is_active', 1)->get();

        if ($branches->isEmpty()) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => $lang == 'en' ? 'There are no branches exist' : 'لا يوجد فروع.',
                'errorData' => ['error' => $lang == 'en' ? 'There are no branches exist' : 'لا يوجد فروع.'],
                'data' => null
            ], 200);
        }

        $currentDay = Carbon::now()->dayOfWeek;
        $currentTime = Carbon::now()->format('H:i:s');

        $responseData = $branches->map(function ($branch) use ($currentDay, $currentTime, $lang) {
            $branchTime = BranchTime::where('branch_id', $branch->id)
                ->where('day', $currentDay)
                ->where('is_active', 1)
                ->first();

            $isOpen = false;
            if ($branchTime) {
                $openingHour = Carbon::parse($branchTime->opening_hour);
                $closingHour = Carbon::parse($branchTime->closing_hour);

                if ($branchTime->cross_day) {
                    $closingHour->addDay();
                }

                $isOpen = $currentTime >= $openingHour->format('H:i:s') && $currentTime <= $closingHour->format('H:i:s');
            }

            return [
                'branch_id' => $branch->id,
                'branch_name' => $lang === 'ar' ? $branch->name_ar : $branch->name_en,
                'branch_phone' => $branch->phone,
                'is_delivery' => $branch->is_delivery,
                'status' => $isOpen ? 'open' : 'closed',
            ];
        });

        return ResponseWithSuccessData($lang, $responseData, 1);
    }
    public function listOrders(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $orders = Order::where('branch_id', $employee->branch_id)
            ->whereIn('type', ['Delivery', 'Takeaway'])
            ->with(['branch', 'tracking', 'orderDetailsWithoutCancel.dish', 'orderDetailsWithoutCancel.dishAddons', 'table'])->get();

        if ($orders->isEmpty()) {
            return RespondWithBadRequest($lang, 22);
        }

        $responseData = $orders->map(function ($order) use ($lang) {

            $orderItemsCount = $order->orderDetailsWithoutCancel->sum('quantity');

            $orderData = [
                'order_status' => $order->tracking->last()->order_status,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'client_phone' => $order->Client->flag != 'unknown' ? $order->Client->phone : ($order->address ? $order->address->address_phone : $order->client_phone),
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'order_type' => $order->type,
                'order_creation_type' => $order->cashier_id ? 'cashier' : ($order->Client->flag != 'unknown' ? 'online' : ($order->customer_service_id ? 'customer_service' : 'unknown')),
                'order_items_count' => $orderItemsCount,
                'client_address' => $order->address?->address ?? null,
                'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : ($order->address ? $order->address->user_name : $order->client_name),
            ];

            if ($order->tracking->last()->order_status == 'on_way') {
                $orderData['delivery_phone'] = $order->delivery?->phone_number ?? null;
                $orderData['delivery_name'] = $order->delivery?->first_name . ' ' . $order->delivery?->last_name ?? null;
            }

            if ($order->tracking->last()->order_status == 'delivered') {
                $orderData['delivery_name'] = $order->delivery?->first_name . ' ' . $order->delivery?->last_name ?? null;
            }

            return $orderData;
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

        $orders = Order::where('branch_id', $employee->branch_id)->where('id', $orderId)
            ->with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons', 'table', 'customerService'])->get();

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

            $orderData = [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'order_status' => $order->tracking->last()->order_status,
                'order_type' => $order->type,
                'order_creation_type' => $order->cashier_id ? 'cashier' : ($order->Client->flag != 'unknown' ? 'online' : ($order->customer_service_id ? 'customer_service' : 'unknown')),
                'employee_name' => $order->customer_service_id ?
                    ($order->customerService->first_name . ' ' . $order->customerService->last_name) : ($order->cashier_id ? ($order->cashier->first_name . ' ' . $order->cashier->last_name) : null),
                'payment_status' => $order->transaction->payment_status,
                'branch_name' => $lang === 'ar' ? $order->branch->name_ar : $order->branch->name_en,
            ];
            $deliveryData = [
                'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : ($order->address ? $order->address->user_name : $order->client_name),
                'client_phone' => $order->Client->flag != 'unknown' ? $order->Client->phone : ($order->address ? $order->address->address_phone : $order->client_phone),
                'client_address_phone' => $order->address ? $order->address->address_phone : null,
                'client_address' => $order->address?->address,
            ];

            if ($order->tracking->last()->order_status == 'on_way') {
                $deliveryData['delivery_phone'] = $order->delivery->phone_number ?? null;
                $deliveryData['delivery_name'] = $order->delivery->first_name . ' ' . $order->delivery->last_name ?? null;
            }

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
                    'dish_id' => $detail->dish_id,
                    'dish_name' => $detail->dish->name ?? null,
                    'size' => $detail->dish_size_id ?
                        (($lang === 'ar') ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
                        : null,
                    'quantity' => $detail->quantity,
                    'total_dish_price' => formatFloat($totalBeforeCoupon),
                    'total_dish_price_coupon_applied' => formatFloat($total),
                    'addons' => $detail->dishAddons->map(function ($addon) use ($lang) {
                        return [
                            'addon_name' => ($lang === 'ar') ? $addon->Addon->addons->name_ar ?? null : $addon->Addon->addons->name_en ?? null,
                            'addon_price' => formatFloat($addon->price_before_tax),
                        ];
                    }),
                    'coupon_id' => $dishCouponId,
                    'coupon_value' => formatFloat($dishCouponValue)
                ];
            });
            $orderSummary = [
                'total_price' => formatFloat($order->total_price_after_tax),
                'payment_method' => $order->transaction->payment_method,
            ];
            $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';

            return [
                'order_data' => $orderData,
                'delivery_data' => $deliveryData,
                'order_details' => $orderDetails,
                'order_summary' => $orderSummary,
                'currency_symbol' => $currencySymbol,
            ];
        });
        $response = [
            'orderDetails' => $responseData,
        ];
        return ResponseWithSuccessData($lang, $response, 1);
    }
    public function sendToCashier(Request $request, $order)
    {
        $lang =  $request->header('lang', 'en');
        App::setLocale($lang);

        $employee = auth('employee')->user();
        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }
        $order = Order::where('id', $order)->whereNot('status', 'cancelled')
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
        $employees_ids =  getEmployeesWithSameShift($employee->id, now());
        if ($employees_ids['status'] === false) {
            return respondError('Validation Error.', 400, ['error' => __('recipes.nocashiersworknowinbranch')]);
        }
        $cashier = Employee::whereIn('id', $employees_ids['data']['working_employees'])->where('flag', 'cashier')->first();
        if (!$cashier) {
            return respondError('Validation Error.', 400, ['error' => __('recipes.nocashiersworknowinbranch')]);
        }
        $order->print_status = 'urgent';
        $order->save();

        $data = addNotification(
            'order',
            'cashier',
            'طلب طباعه',
            'A New print request',
            'طلب جديد',
            'New order',
            $cashier->id,
            $employee->id,
            $lang,
            $request->order_id,
        );
        $Order = Order::with('transaction', 'orderDetails', 'orderDetailsWithoutCancel.dish', 'Table', 'orderDetails.dish')->find($order->id);
        $maxDishTime = $order->orderDetailsWithoutCancel->max(fn($detail) => $detail->dish->time ?? 0);
        $invoice = Invoice::where('order_id', $request->order_id)->first();

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
        // Broadcast event
        broadcast(new CashierNotify($cashier->id, $data));
        return ResponseWithSuccessData($lang, $data, 1);
    }

    public function hangingOrders(Request $request)
    {
        $lang = $request->header('lang', 'en');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if ((!$employee) || ($employee->flag != 'customer_service')) {
            return RespondWithBadRequest($lang, 4);
        }
        $customer_service_branch = $employee->branch_id;

        $orders = Order::where('type', 'Delivery')
            ->where('branch_id', $customer_service_branch)
            ->whereHas('tracking', function ($query) {
                $query->where('order_status', '!=', 'delivered');
            })
            ->whereHas('deliveryComplaints', function ($query) {
                $query->where('status', 'hold');
            })
            ->with(['address', 'tracking', 'deliveryComplaints'])
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => $lang == 'en' ? 'No hanging orders found' : 'لا توجد طلبات معلقة',
                'data' => []
            ], 200);
        }

        $responseData = $orders->map(function ($order) use ($lang) {
            return [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'address' => $order->address?->address,
                'created_at' => $order->deliveryComplaints->created_at?->format('Y-m-d'),
            ];
        });

        return ResponseWithSuccessData($lang, $responseData, 1);
    }

    public function hangingOrdersDetails(Request $request)
    {
        $lang = $request->header('lang', 'en');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if ((!$employee) || ($employee->flag != 'customer_service')) {
            return RespondWithBadRequest($lang, 4);
        }
        $customer_service_branch = $employee->branch_id;

        $orders = Order::where('type', 'Delivery')
            ->where('branch_id', $customer_service_branch)
            ->whereHas('tracking', function ($query) {
                $query->where('order_status', '!=', 'delivered');
            })
            ->whereHas('deliveryComplaints', function ($query) {
                $query->where('status', 'hold');
            })
            ->with(['address', 'tracking', 'deliveryComplaints', 'Client', 'delivery', 'transaction'])
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => $lang == 'en' ? 'No hanging orders found' : 'لا توجد طلبات معلقة',
                'data' => []
            ], 200);
        }

        $responseData = $orders->map(function ($order) use ($lang) {
            return [
                'order_id' => $order->id,
                'complaint_id' => $order->deliveryComplaints->id,
                'order_number' => $order->order_number,
                'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : ($order->address ? $order->address->user_name : $order->client_name),
                'client_phone' => $order->Client->flag != 'unknown' ? $order->Client->phone : ($order->address ? $order->address->address_phone : $order->client_phone),
                'client_address_phone' => $order->address ? $order->address->address_phone : null,
                'address_latitude' => $order->address?->latitude ?? null,
                'address_longitude' => $order->address?->longtitude ?? null,
                'delivery_name' => $order->delivery?->first_name . ' ' . $order->delivery?->last_name ?? null,
                'delivery_phone' => $order->delivery?->phone_number ?? null,
                'address' => $order->address?->address ?? null,
                'payment_status' => $order->transaction?->payment_status,
                'order_complaint' => $order->deliveryComplaints->message,
                'order_complaint_status' => $order->deliveryComplaints->status,
                'created_at' => $order->deliveryComplaints->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return ResponseWithSuccessData($lang, $responseData, 1);
    }
    public function changeStatus(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $user = auth('employee')->user();

            if ((!$user) || ($user->flag != 'customer_service')) {
                return RespondWithBadRequest($lang, 4);
            }

            App::setLocale($lang);
            $validator = Validator::make($request->all(), [
                "id" => "required|exists:delivery_complaints,id,deleted_at,NULL",
                "status" => "required|in:hold,done",
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $complaint = DeliveryComplaints::find($request->id);

            $complaint->status = $request->status;
            $complaint->save();

            return ResponseWithSuccessData($lang, $complaint, 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }
    public function sendRequest(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            "id" => "required|numeric|exists:delivery_complaints,id,deleted_at,NULL"
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $employee = auth('employee')->user();
        $isEmployee = auth('employee')->user() && (auth('employee')->user()->flag == 'customer_service');

        if (!$isEmployee) {
            return RespondWithBadRequest($lang, 4);
        }

        $branch_id = $employee->branch_id;
        $manager = Branch::where('id', $branch_id)?->value('employee_id');
        if ($manager == null) {
            return respondError(($lang == 'en' ? 'No Manager found.' : 'لم يتم العثور على مدير.'), 400, ($lang == 'en' ? 'No Manager found.' : 'لم يتم العثور على مدير.'));
        }
        $user_manager = Employee::where('id', $manager)?->value('user_id');
        if ($user_manager == null) {
            return respondError(($lang == 'en' ? 'No Manager found.' : 'لم يتم العثور على مدير.'), 400, ($lang == 'en' ? 'No Manager found.' : 'لم يتم العثور على مدير.'));
        }
        $user_fcm = User::find($user_manager)?->fcm_token;
        if ($user_fcm == null) {
            return respondError(($lang == 'en' ? "Manager can't receive notification." : 'المدير لا يمكنه استقبال اشعار.'), 400, ($lang == 'en' ? "Manager can't receive notification." : 'المدير لا يمكنه استقبال اشعار.'));
        }

        $complaint = DeliveryComplaints::find($request->id);

        $complaint->manage = 'admin';
        $complaint->save();

        $fullUrl = url()->current();
        $apiBaseUrl = Str::before($fullUrl, '/api');

        $url = $apiBaseUrl . '/dashboard/hanging-order/show/' . $request->id;

        $data = send_push_notification(
            $user_fcm,
            'يوجد طلب معلق مرسل',
            'New hanging order received',
            'طلب جديد',
            'New request',
            'admin',
            $user_manager,
            $user_manager,
            $request->id,
            $lang,
            null,
            $url,
        );

        //        sendManagerNotification(
        //            'admin',
        //            'يوجد طلب معلق مرسل',
        //            'New hanging order received',
        //            'طلب جديد',
        //            'New request',
        //            $user_manager,
        //            $employee->id,
        //            $lang,
        //            $request->id,
        //            $url,
        //        );

        // Broadcast event
        if (!$data) {
            return RespondWithBadRequestData($lang, 2);
        }
        broadcast(new NotifySent(User::find($user_manager), $data));

        return ResponseWithSuccessData($lang, $data, 1);
    }
    public function transformOrderRequest(array $rawRequest)
    {
        $items = [];
        foreach ($rawRequest['items'] as $item) {
            $dish = BranchMenu::find($item['dish_id']);
            $size = isset($item['sizeId']) ? BranchMenuSize::find($item['sizeId']) : null;

            $addons = [];
            foreach ($item['addon_categories'] ?? [] as $cat) {
                foreach ($cat['addon'] as $addonId) {
                    $addon = BranchMenuAddon::find($addonId);
                    if ($addon) {
                        $addons[] = [
                            'id' => (string) $addon->id,
                            'name' => $addon->name,
                            'price' => floatval($addon->price)
                        ];
                    }
                }
            }

            $itemPrice = $size ? floatval($size->price) : floatval($dish->price ?? 0);
            $totalPrice = $itemPrice * $item['quantity'];

            $items[] = [

                'dish_id' => (string) $dish->id,
                'name' => $dish->name,
                'image' => $dish->image_url, // assuming you have an accessor
                'price' => $itemPrice,
                'size' => $size ? [
                    'id' => $size->id,
                    'price' => $size->price,
                    'label' => $size->label,
                ] : ['label' => ''],
                'addons' => $addons,
                'quantity' => $item['quantity'],
                'notes' => $item['note'] ?? '',
                'totalPrice' => $totalPrice,
                'sizeId' => $size?->id,
                'addon_categories' => $item['addon_categories'] ?? [],
            ];
        }

        return [
            '_token' => csrf_token(),
            'client_address_id' => $rawRequest['address_id'] ?? null,
            'address_id' => $rawRequest['address_id'] ?? null,
            'payment_method' => $rawRequest['payment_method'],
            "cashier_machine_id" => $rawRequest['cashier_machine_id'] ?? null,
            'type' => $rawRequest['type'],
            'note' => $rawRequest['note'] ?? '',
            'table_id' => $rawRequest['table_id'] ?? null,
            'branch_id' => (string) $rawRequest['branch_id'],
            'coupon_code' => $rawRequest['coupon_code'] ?? null,
            'appiontment' => $rawRequest['appiontment'] ?? null,
            'items' => $items,
            'lang' => $rawRequest['lang'],
            'make_type' => $rawRequest['make_type'] ?? 'site',
        ];
    }
    public function orderEditItem(Request $request, $type)
    {
        return $this->orderService->orderEditItem($request, $type);
    }
    function placeOrder(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $type = "api";
        $request['lang'] = $lang;
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
            $cond_array = ['pending', 'inprogress'];
            $order = Order::where('id', $request->order_id)->whereIn('status', $cond_array)->first();
            if (!$order) {
                $message = "order id is wrong";
                return respondErrorData('errors', 400, [$lang === 'ar' ? 'الطلب غير موجود' : $message]);
            }
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
            $order_transaction->paid_at = date('Y-m-d H:i:s');
            // $order_transaction->coupon_id = $coupon ? $coupon->id : null;
            $order_transaction->save();
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
    public function mostCustomers(Request $request)
    {
        $lang = app()->getLocale();
        $from = $request->input('from');
        $to = $request->input('to');
        $order = $request->input('order_by');
        $query  = Order::with([
            'Client' => function ($query) {
                $query->withTrashed();
            },
            'Branch',
            'address',
            'orderDetails',
            'orderTransactions'
        ])
            ->select(
                'client_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_price_after_tax) as total_price'),
                DB::raw('GROUP_CONCAT(id) as order_ids')
            )
            ->whereHas('orderTransactions', function ($subQuery) {
                $subQuery->where('payment_status', 'paid');
            })
            ->groupBy('client_id');

        if ($from && $to) {
            $query->whereBetween('date', [$from, $to]);
        } elseif ($from) {
            $query->whereDate('date', '>=', $from);
        } elseif ($to) {
            $query->whereDate('date', '<=', $to);
        }

        if ($order == 1) {
            $query->orderByDesc('total_orders');
        } elseif ($order == 2) {
            $query->orderByDesc('total_price');
        }

        $topClients = paginateOrGetAll($query, $request, null, null);

        return ResponseWithSuccessDataPaginated($lang, $topClients, 1);
    }
}
