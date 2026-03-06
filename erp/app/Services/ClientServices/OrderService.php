<?php


namespace App\Services\ClientServices;

use Carbon\Carbon;
use Pusher\Pusher;
use App\Models\User;
use App\Models\Order;
use App\Models\Table;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\Country;
use App\Models\Invoice;
use App\Events\NewOrder;
use App\Models\Einvoice;
use App\Models\Employee;
use App\Events\EditOrder;
use App\Events\NewOrder2;
use App\Events\TotalPaid;
use App\Models\BranchMenu;
use App\Models\OrderAddon;
use App\Events\TableStatus;
use App\Models\OrderDetail;
use Illuminate\Support\Str;
use App\Models\OrderRequest;
use Illuminate\Http\Request;
use App\Events\CashierNotify;
use App\Models\ClientAddress;
use App\Models\OrderTracking;
use App\Models\BranchMenuSize;
use App\Models\InvoiceDetails;
use App\Models\BranchMenuAddon;
use Illuminate\Validation\Rule;
use App\Events\dishChangeStatus;
use App\Models\OrderTransaction;
use App\Models\TableReservation;
use App\Events\dishChangeStatus2;
use App\Events\orderChangeStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Models\ReturnInvoiceRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use App\Models\OrderRequestSplitItem;
use App\Jobs\SendOrderNotificationJob;
use App\Models\BranchMenuAddonCategory;
use Illuminate\Support\Facades\Validator;
use App\Models\TableReservationTransaction;
use App\Services\ClientServices\InvoiceService;
use App\Services\SettingsServices\BranchService;
use App\Services\AddressServices\BranchSiteService;
use App\Http\Controllers\Api\CashierAPIs\CashierBalanceController;

class OrderService
{

    protected $reservationService;
    protected $branchService;
    // protected $invoiceService;

    public function __construct(ReservationService $reservationService, BranchSiteService $branchService)
    {
        $this->reservationService = $reservationService;
        $this->branchService = $branchService;
        // $this->invoiceService = $invoiceService;
    }
    public function index(Request $request, $checkToken)
    {
        $lang = $request->header('lang', 'ar');
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $query = Order::with([
            'branch:id,name_en,name_ar,country_id',
            'branch.country:id,currency_symbol',
            'orderTransactions:id,order_id,payment_status',
            'client:id,name',
            'waiter:id,first_name,last_name',
            'cashier:id,first_name,last_name',
            'customerService:id,first_name,last_name',
            'latestTracking'
        ])->limit(500);

        if (auth('admin')->user()->hasRole('Branch Manager')) {
            $branch_id = getBranchManagerID();
            if ($branch_id) {
                $query->where('branch_id', $branch_id);
            }
        }

        $orders = $query->select([
            'id',
            'invoice_number',
            'date',
            'type',
            'status',
            'total_price_after_tax',
            'total_price_befor_tax',
            'branch_id',
            'client_id',
            'make_type',
            'waiter_id',
            'cashier_id',
            'customer_service_id',
            'created_at'  // Ensure this is selected
        ])
            ->orderBy('created_at', 'desc')  // Primary sort
            // ->orderBy('id', 'desc')          // Secondary sort for ties
            ->get();


        foreach ($orders as $order) {
            $order['details'] = OrderDetail::where('order_id', $order->id)->get();
            $order['addons'] = OrderAddon::where('order_id', $order->id)->get();
            $order['transaction'] = OrderTransaction::where('order_id', $order->id)->first();
            $order_tracking = OrderTracking::where('order_id', $order->id)->orderby('id', 'desc')->first();
            $order['last_status'] = $order_tracking ? ($order_tracking->order_status ?? null) : null;
            $order['next_status'] = $this->getNextStatus($order_tracking->order_status ?? null, $order->type);
        }

        return ResponseWithSuccessData($lang, $orders, 1);
    }


    function validateOrderItem($items, $branchId, $type)
    {
        foreach ($items as $itemIndex => $item) {
            $dishId = $item['dish_id'] ?? null;
            $addonCategories = $item['addon_categories'] ?? [];
            if ($type == 'api') {
                $branchDish = BranchMenu::with('branchMenuAddons')
                    ->where('id', $dishId)
                    ->where('branch_id', $branchId)
                    ->where('is_active', 1)
                    ->first();
            } else {
                $branchDish = BranchMenu::with('branchMenuAddons')
                    ->where('dish_id', $dishId)
                    ->where('branch_id', $branchId)
                    ->where('is_active', 1)
                    ->first();
            }
            // Fetch dish from the branch
            if (!$branchDish) {
                return respondError2('error', 400, ['error' => __('order.dish_not_found')]);
            }

            // Check if the dish category is active
            if ($branchDish->dish->dishCategory->is_active != 1) {

                return respondError2('error', 400, ['error' => __('order.dish_category_not_active')]);
            }
            // Check if the dish category branch is active
            // if ($branchDish->branchMenuCategories->is_active != 1) {
            //     return respondError2('error', 400, ['error' =>  __('order.dish_category_branch_not_active')]);
            // }
            // Check if the dish is active
            if ($branchDish->is_active != 1) {
                return respondError2('error', 400,  ['error' => __('order.dish_not_active')]);
            }
            $has_size = $branchDish->dish->has_sizes;
            if ($has_size && (empty($item['sizeId']))) {
                return respondError2('errors', 400, __('order.dish_size_required'));
            } elseif (!$has_size && (!empty($item['sizeId']))) {
                return respondError2('errors', 400, __('order.dish_not_have_size'));
            } elseif ($has_size && $item['sizeId']) {
                $size_id = $item['sizeId'];
                $branch_dish_size = BranchMenuSize::where('id', $size_id)
                    ->where('branch_id', $branchId)
                    ->where('is_active', 1)
                    ->first();

                if (!$branch_dish_size || $branch_dish_size->is_active != 1) {
                    return respondError2('errors', 400, __('order.dish_size_not_active_or_deleted'));
                }
                if ($branchDish->dish->id != $branch_dish_size->dish_id) {
                    return respondError2('errors', 400, __('order.size_not_related_dish'));
                }
            }
            $has_addon = $branchDish->dish->has_addon;
            if ($has_addon && (empty($addonCategories))) {
                $minAddon = minAddons($branchDish->dish_id, 1);
                if ($minAddon > 0) {
                    return respondError2('error', 400, ['error' =>  __('order.addon_categories_required')]);
                }
            } elseif (!empty($addonCategories)) {
                // dd($addonCategories);
                foreach ($addonCategories as $catIndex => $category) {
                    if ($type == 'api') {

                        $BranchMenuAddonCategory = BranchMenuAddonCategory::where('id', $category['id'])->where('branch_id', $branchId)->first();
                    } else {
                        $BranchMenuAddonCategory = BranchMenuAddonCategory::where('addon_category_id', $category['id'])->where('branch_id', $branchId)->where('is_active', 1)
                            ->first();
                    }
                    if (!$BranchMenuAddonCategory || $BranchMenuAddonCategory->is_active != 1) {
                        return respondError2('error', 400, ['error' =>  __('order.addon_category_not_active')]);
                    }
                    $addonList = $category['addon'] ?? [];
                    if (!empty($addonList)) {
                        foreach ($addonList as $addon) {

                            $BranchMenuAddon = BranchMenuAddon::where('id', $addon)->where('branch_id', $branchId)->first();


                            if (!$BranchMenuAddon || $BranchMenuAddon->is_active != 1) {
                                return respondError2('error', 400,  ['error' => __('order.addon_not_active')]);
                            }
                            if ($branchDish->dish->id != $BranchMenuAddon->dish_id) {
                                return respondError2('error', 400,  ['error' => __('order.addon_not_related_dish')]);
                            }
                        }
                    }

                    $minAddon = minAddons($branchDish->dish_id, $BranchMenuAddonCategory->addon_category_id);
                    $maxAddon = maxAddons($branchDish->dish_id, $BranchMenuAddonCategory->addon_category_id);
                    $addonCount = count($addonList);

                    // dd($maxAddon);
                    if ($has_addon && ($addonCount < $minAddon || $addonCount > $maxAddon)) {
                        return respondError2('error', 400,  ['error' => __("order.addon_must_be_min_max") .  $minAddon . "-" . $maxAddon]);
                    }
                }
            }
        }

        return respondEmptyData('', 200); // All valid
    }

    public  function validate(array $data, string $lang = 'en', $checkToken, $dir, $function_name = 'store')
    {
        if ($function_name == 'store') {

            if ($checkToken) {
                // Determine authenticated client ID
                $client_id = match (getAuthenticatedGuard()) {
                    'api' => Auth::guard('api')->id(),
                    'client' => Auth::guard('client')->id(),
                    'employee' => Auth::guard('employee')->id(),
                    default => null,
                };
                // $client_id =getAuthenticatedGuard();
                if (!$client_id) {
                    return respondError(__(key: 'auth.unauthorized'),  401, ['auth' => __('auth.unauthorized')]);
                }
            } else {
                $client_id = Auth::guard('client')->user()->id;
            }
        }



        // Validate input structure
        $rules = [
            'client_country_code' => 'nullable|exists:countries,phone_code',
            'client_phone' => [
                'nullable',
                function ($attribute, $value, $fail) use ($data, $lang) {
                    $countryCode = $data['client_country_code'] ?? null;

                    if ($countryCode) {
                        $country = Country::where('phone_code', $countryCode)->first();
                        if ($country && $country->length) {
                            if (strlen($value) != $country->length) {
                                $message = $lang === 'ar'
                                    ? "رقم الهاتف يجب أن يحتوي على {$country->length} رقم حسب كود الدولة."
                                    : "Phone number must be {$country->length} digits long according to the country code.";
                                $fail($message);
                            }
                        }
                    }
                },
            ],
            'client_name' => 'nullable',
            'whatsapp_number_code' => 'nullable|exists:countries,phone_code',
            'whatsapp_number' => 'nullable',
            'cashier_machine_id' => 'nullable|exists:cashier_machines,id',
            // 'table_id' => 'required_if:type,dine-in|exists:tables,id',
            'lang' => 'required|string|in:en,ar',
            'type' => 'required|string|in:Takeaway,Online,dine-in,Delivery,CallCenter,reservation-table,talabat',
            'branch_id' => 'required|exists:branches,id',
            'address_id' => 'nullable|exists:client_addresses,id',
            'coupon_code' => 'nullable|exists:coupons,code',
            'make_type' => 'required|string',
            'payment_method' => 'nullable|string|in:cash,credit,online,credit_with_delivery,deposit,no_payment_required,deposit_required,full_payment_required,deferred',
            'payment_method2' => 'nullable|string|in:cash,credit,online,credit_with_delivery,deposit,no_payment_required,deposit_required,full_payment_required,deferred',
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.sizeId' => 'nullable|exists:branch_menu_sizes,id',
            // 'items.*.addon_categories' => 'nullable|array',
            // 'items.*.addon_categories.*.addon' => 'nullable|array',
            // 'items.*.addon_categories.*.addon.*' => 'exists:branch_menu_addons,id',
            'note' => 'nullable|string|max:255',
            'cash_amount' => 'nullable',
            'credit_amount' => 'nullable',
            // 'reference_number' => 'required_if:payment_method,credit',
            'reference_number' => [
                'nullable',
                function ($attribute, $value, $fail) use ($lang) {
                    $req = request(); // ✅ نحصل على الـ request الحالي

                    if (
                        ($req->payment_method === 'credit') &&
                        ($req->type !== 'talabat') &&
                        empty($value)
                    ) {
                        $message = $lang === 'ar'
                            ? 'رقم المرجع مطلوب عند الدفع بالكريدت عندما لا يكون الطلب من طلبات.'
                            : 'Reference number is required when payment method is credit and type is not talabat.';
                        $fail($message);
                    }
                },
            ],

        ];

        // Adjust rules conditionally based on authenticated guard
        if (getAuthenticatedGuard() === 'employee') {
            $employee = auth('employee')->user();

            if ($employee && $employee->flag === 'cashier') {
                $rules['cashier_machine_id'] = 'required|exists:cashier_machines,id';
            }
        }

        // Now run validator
        $validator = Validator::make($data, $rules);
        // $validator->after(function ($validator) use ($data) {
        //     if ($data['type'] === 'Takeaway') {
        //         $country = Country::where('code', $data['country_code'])->first();

        //         if (!$country) {
        //             $validator->errors()->add('country_code', __('validation.country_not_found'));
        //             return;
        //         }

        //         $expectedLength = $country->phone_length;
        //         if (strlen($data['phone']) != $expectedLength) {
        //             $validator->errors()->add('phone', __('validation.invalid_phone_length', [
        //                 'length' => $expectedLength
        //             ]));
        //         }
        //     }
        // });
        if ($validator->fails()) {
            return respondErrorData($validator->errors(), 400, $validator->errors());
        }

        // Branch active check
        $branch = Branch::find($data['branch_id']);
        if (!$branch || !$branch->is_active) {
            return respondError("branch not active", 400, ['branch_id' => __('order.branch_not_active')]);
        }

        // Coupon usage check
        if (!empty($data['coupon_code'])) {
            $coupon = GetCouponId($data['coupon_code'], $data['branch_id']);
            if (!$coupon || !CountCouponUsage($coupon->id)) {
                return respondError("errors", 400, ['coupon_code' => [$lang === 'ar' ? 'هذا الكوبون غير صالح' : 'Invalid coupon']]);
            }
            $dish_ids = array_column($data['items'], 'dish_id');
            $OriginalIds = BranchMenu::whereIn('id', $dish_ids)->pluck('dish_id')->toArray();
            if ($coupon->apply_type == 'dish') {
                if (!checkCouponApplyDish($coupon->id, $OriginalIds, $data['branch_id'])) {
                    return respondError("errors", 400, ['coupon_code' => [$lang === 'ar' ? 'هذا الكوبون غير صالح' : 'Invalid coupon']]);
                }
            }
        }

        // Address validation (for certain types only)
        if (!in_array($data['type'], ['Takeaway', 'reservation-table', 'dine-in', 'talabat'])) {
            $address = ClientAddress::find($data['address_id'] ?? null);
            if (!$address) {
                return respondError("errors", 400, ['address_id' => [__('order.address_not_found')]]);
            }
        }
        $result = $this->validateOrderItem($data['items'],  $branch->id, $dir);
        $responseData = $result->original;
        if (!$responseData['status']) {
            return $result; // Respond with validation error if any
        }

        if ($data['type'] == 'dine-in') {
            if (!isset($data['table_id'])) {
                return respondError("errors", 400, ['table_id' => [__('order.table_required')]]);
            }
            $table = Table::find($data['table_id']);
            if (!$table) {
                return respondError("errors", 400, ['table_id' => [__('order.table_not_found')]]);
            }
            if ($table->status == 2) {
                return respondError("errors", 400, ['table_id' => [__('order.tablealreadybusy')]]);
            }
        }
        // if (getAuthenticatedGuard() === 'employee') {
        //     $employee = auth('employee')->user();
        //     if ($employee->flag  == 'cashier' || $employee->flag  == 'customer_service') {
        //         if ($data['table_id'] && $data['type'] == 'dine-in' && !isset($data['order_id'])) {
        //             //check reservation time
        //             $table = Table::find($data['table_id']);
        //             if ($table->status == 2) {
        //                 return respondError("errors", 400, ['table_id' => [__('order.tablealreadybusy')]]);
        //             }
        //         }
        //     }
        // }
        return respondEmptyData('', 200);
    }

    public function store_v2(array $request, $checkToken, $type)
    {


        $lang = $request['lang'];
        App::setLocale($lang);
        DB::beginTransaction();

        // try {
        $validated = $this->validate($request, $lang, $checkToken, $type);
        $responseData = $validated->original;
        // return $responseData;

        if (!$responseData['status']) {
            return $validated; // Respond with validation error if any
        }
        if ($checkToken) {
            // Determine authenticated client ID
            $client_id = match (getAuthenticatedGuard()) {
                'api' => Auth::guard('api')->id(),
                'client' => Auth::guard('client')->id(),
                'employee' => Auth::guard('employee')->id(),
                default => null,
            };
            if (getAuthenticatedGuard() == 'employee') {
                $client_id = User::where('flag', 'unknown')->value('id');
            }
        } else {
            $client_id = Auth::guard('client')->user()->id;
        }

        $created_by = $client_id;
        $branchId = $request['branch_id'];
        $orderItems = $request['items'];
        $addressId = $request['address_id'] ?? null;
        $appiontment = $request['appiontment'] ?? null;


        // Branch settings
        $taxApplication = getBranchSettings($branchId, 'tax_application');
        $taxPercentage = getBranchSettings($branchId, 'tax_percentage');
        $couponApplication = getBranchSettings($branchId, 'coupon_application');
        $orderDeposit = getBranchSettings($branchId, 'order_reservation_deposit');
        // $deliveryFees = in_array($request['type'], ['Takeaway', 'dine-in', 'reservation-table']) ? 0 : getBranchSettings($branchId, 'delivery_fees');
        $deliveryFees = in_array($request['type'], ['Takeaway', 'dine-in', 'reservation-table']) ? 0 : getDeliveryFees($addressId, $branchId);

        $serviceFeesValue = in_array($request['type'], ['dine-in', 'reservation-table']) ? getBranchSettings($branchId, 'service_fees') : 0;
        $serviceFeesType = in_array($request['type'], ['dine-in', 'reservation-table']) ? getBranchSettings($branchId, 'service_fees_type') : "0";
        $cash_limit = getCashPaymentPolicy($branchId);

        $request['payment_method'] = $request['payment_method'] ?? $request['payment_method2'];


        // Coupon validation
        $coupon = null;
        if (!empty($request['coupon_code'])) {
            $coupon = GetCouponId($request['coupon_code'], $branchId);
        }
        $makeType = $request['make_type'];
        $pickupTime = null;
        $selected_date = $request['selected_date'] ?? date('Y-m-d');
        $selected_time = $request['selected_time'] ?? date('H:i:s');
        if ($request['type'] === 'Takeaway') {

            $data_takeaway = (object) [
                'branch_id' => $branchId,
                'date' => $selected_date,
                'pickup_time' => $selected_time
            ];

            $response = $this->branchService->checkOrderCapacity($data_takeaway, $lang);
            // If not available, return an error response
            if (!$response['available']) {
                return respondError('error', 400,  ['error' => __('order.TimeBooked')]);
            }
            $pickupTime = Carbon::parse(str_replace(['ص', 'م'], ['AM', 'PM'], "{$selected_date} {$selected_time}"));
        }
        if (getAuthenticatedGuard() === 'employee') {

            $employee = auth('employee')->user();
            if ($employee->flag  == 'cashier' || $employee->flag  == 'customer_service' || $employee->flag == 'waiter') {
                if ($request['table_id'] && $request['type'] == 'dine-in' && !isset($request['order_id'])) {
                    //check reservation time
                    $table = Table::find($request['table_id']);

                    $table->status = 2;
                    $table->last_busy_at = now();
                    $table->save();
                    // Notification for the new table
                    $notifyDataNew = [
                        'notification_type' => 'table',
                        'description_ar' => 'تم حجز الطاوله' . $table->table_number . '  بالفرع',
                        'description_en' => 'A table' . $table->table_number . '  was reserved in the branch',
                        'title_ar' => 'تم تعديل حاله طاولة',
                        'title_en' => 'Table changed',
                        'created_by' => null,
                        'order_id' => $table->id
                    ];
                    runNotificationToEmployees($table->branch_id, $notifyDataNew, null, $table->id, 'ar');

                    // Structure data for broadcasting
                    $data = [
                        'id' => $table->id,
                        'name' => $table->name,
                        'name_ar' => $table->name_ar,
                        'name_en' => $table->name_en,
                        'table_number' => $table->table_number,
                        'status' => $table->status,
                        'smoking' => $table->smoking,
                        'floors' => [
                            'id' => $table->floors->id,
                            'name' => $table->floors->name,
                            'name_ar' => $table->floors->name_ar,
                            'name_en' => $table->floors->name_en
                        ],
                        'floor_partitions' => [
                            'id' => $table->floorPartitions->id,
                            'name' => $table->floorPartitions->name,
                            'name_ar' => $table->floorPartitions->name_ar,
                            'name_en' => $table->floorPartitions->name_en
                        ]
                    ];
                    broadcast(new TableStatus($data, $table->branch_id, 'updated'));
                }
            }
        }
        // Create Order
        // Get Egyptian timezone date and time
        $egyptianTime = Carbon::now('Africa/Cairo');
        $order = Order::create([
            'date' => $egyptianTime->format('Y-m-d'),
            'time' => $egyptianTime->format('H:i:s'),
            'type' => $request['type'],
            'note' => $request['note'] ?? null,
            'delivery_fees' => $deliveryFees,
            'table_id' => in_array($request['type'], ['dine-in', 'reservation-table']) ? $request['table_id'] : null,
            'client_id' => $client_id,
            'discount_id' => null,
            'branch_id' => $branchId,
            'client_address_id' => in_array($request['type'], ['Takeaway', 'dine-in', 'reservation-table']) ? null : $addressId,
            'coupon_id' => ($coupon && $coupon->apply_type == 'order') ? $coupon?->id : null,
            'created_by' => $created_by,
            'make_type' => $makeType,
            'tax_application' => $taxApplication,
            'takeaway_pickup_time' => $pickupTime,
            'preparation_appointment' => $appiontment,
            'tax_percentage' => $taxPercentage,
            'service_percentage' => $serviceFeesValue,


        ]);

        if (getAuthenticatedGuard() === 'employee') {
            $employee = auth('employee')->user();

            if ($employee->flag  == 'cashier') {
                if (auth('employee')->user()->branch_id != $branchId) {
                    return respondError('Validation Error.', 400,  ['error' => __('order.not_allow_with_other_branch')]);
                }
                $order->delivery_id = ($request['type'] == 'Delivery' && isset($request['delivery_id'])) ? $request['delivery_id'] : null;
                $order->cashier_id = auth('employee')->user()->id;
                $order->cashier_machine_id = $request['cashier_machine_id'] ?? null;
                $makeType = 'cashier';
                $order->make_type = $makeType;

                $order->client_country_code = $request['client_country_code'];
                $order->client_phone = $request['client_phone'];
                $order->client_name = $request['client_name'];
            } elseif ($employee->flag   == 'customer_service') {
                if ($employee->branch_id === $branchId) {
                    $order->delivery_id = ($request['type'] == 'Delivery' && $request['delivery_id'] != null) ? $request['delivery_id'] : null;
                }
                $order->customer_service_id = auth('employee')->user()->id;
                $makeType = 'customer_service';
                $order->make_type = $makeType;
            } else if ($employee->flag  == 'waiter') {
                if (auth('employee')->user()->branch_id != $branchId) {
                    return respondError('Validation Error.', 400, ['error' => __('order.not_allow_with_other_branch')]);
                }
                $makeType = 'waiter';
                $order->make_type = $makeType;
                $order->waiter_id = $employee->id;
            }
            $order->save();
        }

        $baseId = GetNextID('orders', $makeType);

        do {
            $orderNumber = getNewOrderNumber($makeType, $baseId, $branchId);
            $baseId++;

            $orderNumberExists = Order::where('order_number', "#{$orderNumber}")->exists();
            $invoiceNumberExists = Order::where('invoice_number', "INV-{$orderNumber}")->exists();
        } while ($orderNumberExists || $invoiceNumberExists);

        $order->update([
            'order_number' => "#{$orderNumber}",
            'invoice_number' => "INV-{$orderNumber}",
        ]);


        $totals = $this->storeOrderItems(

            $order,
            $orderItems,
            $type,
            $branchId,
            $taxApplication,
            $taxPercentage,
            $serviceFeesValue,
            $serviceFeesType,
            $request['type'],
            $created_by,
            ($coupon?->apply_type == 'dish') ? $coupon?->id : null,
            0,
            $lang

        );

        if ($coupon && $coupon->apply_type == 'order'  && !CheckCouponValid($coupon->id, $totals['subTotal'])) {
            DB::rollBack();
            return respondError("coupon error", 400, ['coupon_code' => __('cart.CouponNotValid')]);
        }

        // if ($request['payment_method'] === 'cash') {
        OrderTracking::create([
            'order_id' => $order->id,
            'created_by' => $created_by,
        ]);
        // }
        $cash_amount = 0;
        $credit_amount = 0;
        $order->refresh();

        if (($request['type'] == 'dine-in' || $request['type'] == 'Takeaway' || $request['type'] == 'Delivery') &&  $makeType == 'cashier') {
            $cash_amount = $request['cash_amount'];
            $credit_amount = $request['credit_amount'];
            if (isset($coupon) && $coupon->value == 100 && $coupon->type == "percentage" && $coupon->apply_type == 'order') {
                $cash_amount = 0;
                $credit_amount = 0;
            } else {
                if ($request['type'] == 'Delivery' && !$cash_amount && !$credit_amount) {
                    $cash_amount = 0;
                    $credit_amount = 0;
                } else {
                    $cash_amount = $request['cash_amount'];
                    $credit_amount = $request['credit_amount'];
                }
            }
            // dd(var_dump($cash_amount), var_dump($credit_amount),var_dump(bcadd($cash_amount, $credit_amount, 2)), round($order->total_price_after_tax, 2));
            if (!$cash_amount && !$credit_amount) {
                $totalPaid = round($cash_amount + $credit_amount, 3);

                if ($totalPaid >= (float)$order->total_price_after_tax) {
                    // $reference_number = $request['reference_number'];
                    $this->storePaymentTransaction($order->id, $request['type'], 'cash', $created_by, $coupon?->id,  $order->total_price_after_tax, $orderDeposit, $makeType, 'paid', 0, null, $totals['order_invoice_id']);
                } else {
                    if ($request['type'] == 'Delivery' && $cash_amount == 0 && $credit_amount == 0) {
                        $this->storePaymentTransaction($order->id, $request['type'], 'cash', $created_by, $coupon?->id,  $order->total_price_after_tax, $orderDeposit, $makeType, $request['payment_status'], 0, null, $totals['order_invoice_id']);
                    } else {
                        $this->storePaymentTransaction($order->id, $request['type'], 'cash', $created_by, $coupon?->id,  $order->total_price_after_tax, $orderDeposit, $makeType, 'unpaid', 0, null, $totals['order_invoice_id']);
                    }
                }
            } else {

                $totalPaid = round($cash_amount + $credit_amount, 2);
                $taxedTotal = round((float)$order->total_price_after_tax, 2);
                // dd($totalPaid, $taxedTotal);

                if ($totalPaid < $taxedTotal) {
                    DB::rollBack();
                    return respondError('Validation Error.', 400, [
                        'error' => __('order.amount_wrong')
                    ]);
                }

                if ($cash_amount != 0) {
                    // dd($order);

                    $this->storePaymentTransaction($order->id, $request['type'], 'cash', $created_by, $coupon?->id,  $cash_amount, $orderDeposit, $makeType, 'paid', 0, null, $totals['order_invoice_id']);
                }
                if ($credit_amount != 0) {
                    $reference_number = $request['reference_number'];
                    $this->storePaymentTransaction($order->id, $request['type'], 'credit', $created_by, $coupon?->id,  $credit_amount, $orderDeposit, $makeType, 'paid', 0, $reference_number, $totals['order_invoice_id']);
                }
            }
        } else {
            // if (getAuthenticatedGuard() == 'client' || getAuthenticatedGuard() == 'api') {
            //     $cash_amount = $request['cash_amount'];
            //     $credit_amount = $request['credit_amount'];
            //     if ($cash_amount + $credit_amount != $order->total_price_after_tax) {
            //         DB::rollBack();
            //         return respondError('Validation Error.', 400, ['error' => __('order.amount_wrong')]);
            //     }
            //     if ($cash_amount != 0) {
            //         $this->storePaymentTransaction($order->id, $request['type'], 'cash', $created_by, $coupon?->id,  $cash_amount, $orderDeposit, $makeType, 'unpaid');
            //     }
            //     if ($credit_amount != 0) {
            //         $this->storePaymentTransaction($order->id, $request['type'], 'credit', $created_by, $coupon?->id,  $credit_amount, $orderDeposit, $makeType, 'unpaid');
            //     }
            // } else {
            $this->storePaymentTransaction($order->id, $request['type'], $request['payment_method'], $created_by, $coupon?->id,  $order->total_price_after_tax, $orderDeposit, $makeType, $request['payment_status'] ?? null, 0, null, $totals['order_invoice_id']);
            // }
        }
        if ((getAuthenticatedGuard() == 'client' || getAuthenticatedGuard() == 'api') && ($request['payment_method'] == 'cash' || $request['payment_method'] == 'no_payment_required')) {
            if ($order->total_price_after_tax > getCashPaymentPolicy($branchId)) {
                return respondError('error', 400,  ['error' => __('order.MaxCashLimit')]);
            }
        }

        //update Invoice Transaction
        if ($totals) {
            $invoiceService = app(invoiceService::class);
            $invoiceService->updateInvoiceTransaction($totals['order_invoice_id']);
        }

        // Set default address if needed
        if ($request['type'] == 'Delivery' && $order->client_address_id) {
            ClientAddress::where('user_id', $client_id)->update(['is_default' => 0]);
            ClientAddress::where('id', $order->client_address_id)->update(['is_default' => 1]);
        }

        if ($totals) {
            $invoice = Invoice::where('order_id', $order->id)->latest()->first();
            $orderTransactions = OrderTransaction::where('order_id', $order->id)->latest()->first();
            $orderTransactions->original_price = $invoice->original_price;
            $orderTransactions->save();
        }

        // Create Einvoice
        Einvoice::create([
            "invoice_id" => $totals['order_invoice_id'],
            "invoice_type" => 'i'
        ]);

        $userType = getAuthenticatedGuard();
        if ($userType == 'employee') {
            $userType = auth('employee')->user()->flag;
        }


        // Reservation data
        if ($request['type'] === 'reservation-table') {

            $order->table_id = $request['table_id'];
            $order->save();
        }

        // Send to kitchen

        DB::commit();
        //  SendOrderNotificationJob::dispatch(
        //     $order->id,
        //     $branchId,
        //     $userType,
        //     $request['type'],
        //     $created_by,
        //     $lang
        // );
        $this->send_notification($order->id, $branchId, $userType, $request['type'], $created_by, $lang);

        sendToKitchen($order->id, $lang);

        return ResponseWithSuccessData($lang, ['order_id' => $order->id, 'invoice_id' => (isset($totals['order_invoice_id'])) ? $totals['order_invoice_id'] : null], 1);
        // } catch (\Throwable $e) {
        //     DB::rollBack();
        //     // return RespondWithBadRequestWithData(['error' => $e->getMessage()]);
        //     return respondErrorData(__('error'), 400, $e->getMessage());
        // }
    }

    public function store_v2_offline(array $request, $checkToken, $type)
    {


        $lang = $request['lang'];
        App::setLocale($lang);
        DB::beginTransaction();

        // try {
        // $validated = $this->validate($request, $lang, $checkToken, $type);
        // $responseData = $validated->original;
        // return $responseData;

        // if (!$responseData['status']) {
        //     return $validated; // Respond with validation error if any
        // }
        if ($checkToken) {
            // Determine authenticated client ID
            $client_id = match (getAuthenticatedGuard()) {
                'api' => Auth::guard('api')->id(),
                'client' => Auth::guard('client')->id(),
                'employee' => Auth::guard('employee')->id(),
                default => null,
            };
            if (getAuthenticatedGuard() == 'employee') {
                $client_id = User::where('flag', 'unknown')->value('id');
            }
        } else {
            $client_id = Auth::guard('client')->user()->id;
        }

        $created_by = $client_id;
        $branchId = $request['branch_id'];
        $orderItems = $request['items'];
        $addressId = $request['address_id'] ?? null;
        $appiontment = $request['appiontment'] ?? null;


        // Branch settings
        $taxApplication = getBranchSettings($branchId, 'tax_application');
        $taxPercentage = getBranchSettings($branchId, 'tax_percentage');
        $couponApplication = getBranchSettings($branchId, 'coupon_application');
        $orderDeposit = getBranchSettings($branchId, 'order_reservation_deposit');
        // $deliveryFees = in_array($request['type'], ['Takeaway', 'dine-in', 'reservation-table']) ? 0 : getBranchSettings($branchId, 'delivery_fees');
        $deliveryFees = in_array($request['type'], ['Takeaway', 'dine-in', 'reservation-table']) ? 0 : getDeliveryFees($addressId, $branchId);

        $serviceFeesValue = in_array($request['type'], ['dine-in', 'reservation-table']) ? getBranchSettings($branchId, 'service_fees') : 0;
        $serviceFeesType = in_array($request['type'], ['dine-in', 'reservation-table']) ? getBranchSettings($branchId, 'service_fees_type') : "0";
        $cash_limit = getCashPaymentPolicy($branchId);

        $request['payment_method'] = $request['payment_method'] ?? $request['payment_method2'];


        // Coupon validation
        $coupon = null;
        // if (!empty($request['coupon_code'])) {
        //     $coupon = GetCouponId($request['coupon_code'], $branchId);
        // }
        $makeType = $request['make_type'];
        $pickupTime = null;
        $selected_date = $request['selected_date'] ?? date('Y-m-d');
        $selected_time = $request['selected_time'] ?? date('H:i:s');
        if ($request['type'] === 'Takeaway') {

            $data_takeaway = (object) [
                'branch_id' => $branchId,
                'date' => $selected_date,
                'pickup_time' => $selected_time
            ];

            $response = $this->branchService->checkOrderCapacity($data_takeaway, $lang);
            // If not available, return an error response
            if (!$response['available']) {
                return respondError('error', 400,  ['error' => __('order.TimeBooked')]);
            }
            $pickupTime = Carbon::parse(str_replace(['ص', 'م'], ['AM', 'PM'], "{$selected_date} {$selected_time}"));
        }
        if (getAuthenticatedGuard() === 'employee') {

            $employee = auth('employee')->user();
            if ($employee->flag  == 'cashier' || $employee->flag  == 'customer_service' || $employee->flag == 'waiter') {
                if ($request['table_id'] && $request['type'] == 'dine-in' && !isset($request['order_id'])) {
                    //check reservation time
                    $table = Table::find($request['table_id']);

                    $table->status = 2;
                    $table->last_busy_at = now();
                    $table->save();
                    // Notification for the new table
                    $notifyDataNew = [
                        'notification_type' => 'table',
                        'description_ar' => 'تم حجز الطاوله' . $table->table_number . '  بالفرع',
                        'description_en' => 'A table' . $table->table_number . '  was reserved in the branch',
                        'title_ar' => 'تم تعديل حاله طاولة',
                        'title_en' => 'Table changed',
                        'created_by' => null,
                        'order_id' => $table->id
                    ];
                    runNotificationToEmployees($table->branch_id, $notifyDataNew, null, $table->id, 'ar');

                    // Structure data for broadcasting
                    $data = [
                        'id' => $table->id,
                        'name' => $table->name,
                        'name_ar' => $table->name_ar,
                        'name_en' => $table->name_en,
                        'table_number' => $table->table_number,
                        'status' => $table->status,
                        'smoking' => $table->smoking,
                        'floors' => [
                            'id' => $table->floors->id,
                            'name' => $table->floors->name,
                            'name_ar' => $table->floors->name_ar,
                            'name_en' => $table->floors->name_en
                        ],
                        'floor_partitions' => [
                            'id' => $table->floorPartitions->id,
                            'name' => $table->floorPartitions->name,
                            'name_ar' => $table->floorPartitions->name_ar,
                            'name_en' => $table->floorPartitions->name_en
                        ]
                    ];
                    broadcast(new TableStatus($data, $table->branch_id, 'updated'));
                }
            }
        }
        // Create Order
        // Get Egyptian timezone date and time
        $egyptianTime = Carbon::now('Africa/Cairo');
        $order = Order::create([
            'date' => $egyptianTime->format('Y-m-d'),
            'time' => $egyptianTime->format('H:i:s'),
            'type' => $request['type'],
            'note' => $request['note'] ?? null,
            'delivery_fees' => $deliveryFees,
            'table_id' => in_array($request['type'], ['dine-in', 'reservation-table']) ? $request['table_id'] : null,
            'client_id' => $client_id,
            'discount_id' => null,
            'branch_id' => $branchId,
            'client_address_id' => in_array($request['type'], ['Takeaway', 'dine-in', 'reservation-table']) ? null : $addressId,
            'coupon_id' => ($coupon && $coupon->apply_type == 'order') ? $coupon?->id : null,
            'created_by' => $created_by,
            'make_type' => $makeType,
            'tax_application' => $taxApplication,
            'takeaway_pickup_time' => $pickupTime,
            'preparation_appointment' => $appiontment,
            'tax_percentage' => $taxPercentage,
            'service_percentage' => $serviceFeesValue,


        ]);

        if (getAuthenticatedGuard() === 'employee') {
            $employee = auth('employee')->user();

            if ($employee->flag  == 'cashier') {
                if (auth('employee')->user()->branch_id != $branchId) {
                    return respondError('Validation Error.', 400,  ['error' => __('order.not_allow_with_other_branch')]);
                }
                $order->delivery_id = ($request['type'] == 'Delivery' && isset($request['delivery_id'])) ? $request['delivery_id'] : null;
                $order->cashier_id = auth('employee')->user()->id;
                $order->cashier_machine_id = $request['cashier_machine_id'] ?? null;
                $makeType = 'cashier';
                $order->make_type = $makeType;

                $order->client_country_code = $request['client_country_code'];
                $order->client_phone = $request['client_phone'];
                $order->client_name = $request['client_name'];
            } elseif ($employee->flag   == 'customer_service') {
                if ($employee->branch_id === $branchId) {
                    $order->delivery_id = ($request['type'] == 'Delivery' && $request['delivery_id'] != null) ? $request['delivery_id'] : null;
                }
                $order->customer_service_id = auth('employee')->user()->id;
                $makeType = 'customer_service';
                $order->make_type = $makeType;
            } else if ($employee->flag  == 'waiter') {
                if (auth('employee')->user()->branch_id != $branchId) {
                    return respondError('Validation Error.', 400, ['error' => __('order.not_allow_with_other_branch')]);
                }
                $makeType = 'waiter';
                $order->make_type = $makeType;
                $order->waiter_id = $employee->id;
            }
            $order->save();
        }

        $baseId = GetNextID('orders', $makeType);

        do {
            $orderNumber = getNewOrderNumber($makeType, $baseId, $branchId);
            $baseId++;

            $orderNumberExists = Order::where('order_number', "#{$orderNumber}")->exists();
            $invoiceNumberExists = Order::where('invoice_number', "INV-{$orderNumber}")->exists();
        } while ($orderNumberExists || $invoiceNumberExists);

        $order->update([
            'order_number' => "#{$orderNumber}",
            'invoice_number' => "INV-{$orderNumber}",
        ]);


        $totals = $this->storeOrderItems(

            $order,
            $orderItems,
            $type,
            $branchId,
            $taxApplication,
            $taxPercentage,
            $serviceFeesValue,
            $serviceFeesType,
            $request['type'],
            $created_by,
            ($coupon?->apply_type == 'dish') ? $coupon?->id : null,
            0,
            $lang

        );

        if ($coupon && $coupon->apply_type == 'order'  && !CheckCouponValid($coupon->id, $totals['subTotal'])) {
            DB::rollBack();
            return respondError("coupon error", 400, ['coupon_code' => __('cart.CouponNotValid')]);
        }

        // if ($request['payment_method'] === 'cash') {
        OrderTracking::create([
            'order_id' => $order->id,
            'created_by' => $created_by,
        ]);
        // }
        $cash_amount = 0;
        $credit_amount = 0;
        $order->refresh();

        if (($request['type'] == 'dine-in' || $request['type'] == 'Takeaway' || $request['type'] == 'Delivery') &&  $makeType == 'cashier') {
            $cash_amount = $request['cash_amount'];
            $credit_amount = $request['credit_amount'];
            if (isset($coupon) && $coupon->value == 100 && $coupon->type == "percentage" && $coupon->apply_type == 'order') {
                $cash_amount = 0;
                $credit_amount = 0;
            } else {
                if ($request['type'] == 'Delivery' && !$cash_amount && !$credit_amount) {
                    $cash_amount = 0;
                    $credit_amount = 0;
                } else {
                    $cash_amount = $request['cash_amount'];
                    $credit_amount = $request['credit_amount'];
                }
            }
            // dd(var_dump($cash_amount), var_dump($credit_amount),var_dump(bcadd($cash_amount, $credit_amount, 2)), round($order->total_price_after_tax, 2));
            if (!$cash_amount && !$credit_amount) {
                $totalPaid = round($cash_amount + $credit_amount, 3);

                if ($totalPaid >= (float)$order->total_price_after_tax) {
                    // $reference_number = $request['reference_number'];
                    $this->storePaymentTransaction($order->id, $request['type'], 'cash', $created_by, $coupon?->id,  $order->total_price_after_tax, $orderDeposit, $makeType, 'paid', 0, null, $totals['order_invoice_id']);
                } else {
                    if ($request['type'] == 'Delivery' && $cash_amount == 0 && $credit_amount == 0) {
                        $this->storePaymentTransaction($order->id, $request['type'], 'cash', $created_by, $coupon?->id,  $order->total_price_after_tax, $orderDeposit, $makeType, $request['payment_status'], 0, null, $totals['order_invoice_id']);
                    } else {
                        $this->storePaymentTransaction($order->id, $request['type'], 'cash', $created_by, $coupon?->id,  $order->total_price_after_tax, $orderDeposit, $makeType, 'unpaid', 0, null, $totals['order_invoice_id']);
                    }
                }
            } else {

                $totalPaid = round($cash_amount + $credit_amount, 3);

                if ($totalPaid < (float)$order->total_price_after_tax) {
                    DB::rollBack();
                    return respondError('Validation Error.', 400, [
                        'error' => __('order.amount_wrong')
                    ]);
                }

                if ($cash_amount != 0) {
                    // dd($order);

                    $this->storePaymentTransaction($order->id, $request['type'], 'cash', $created_by, $coupon?->id,  $cash_amount, $orderDeposit, $makeType, 'paid', 0, null, $totals['order_invoice_id']);
                }
                if ($credit_amount != 0) {
                    $reference_number = $request['reference_number'];
                    $this->storePaymentTransaction($order->id, $request['type'], 'credit', $created_by, $coupon?->id,  $credit_amount, $orderDeposit, $makeType, 'paid', 0, $reference_number, $totals['order_invoice_id']);
                }
            }
        } else {
            // if (getAuthenticatedGuard() == 'client' || getAuthenticatedGuard() == 'api') {
            //     $cash_amount = $request['cash_amount'];
            //     $credit_amount = $request['credit_amount'];
            //     if ($cash_amount + $credit_amount != $order->total_price_after_tax) {
            //         DB::rollBack();
            //         return respondError('Validation Error.', 400, ['error' => __('order.amount_wrong')]);
            //     }
            //     if ($cash_amount != 0) {
            //         $this->storePaymentTransaction($order->id, $request['type'], 'cash', $created_by, $coupon?->id,  $cash_amount, $orderDeposit, $makeType, 'unpaid');
            //     }
            //     if ($credit_amount != 0) {
            //         $this->storePaymentTransaction($order->id, $request['type'], 'credit', $created_by, $coupon?->id,  $credit_amount, $orderDeposit, $makeType, 'unpaid');
            //     }
            // } else {
            $this->storePaymentTransaction($order->id, $request['type'], $request['payment_method'], $created_by, $coupon?->id,  $order->total_price_after_tax, $orderDeposit, $makeType, $request['payment_status'] ?? null, 0, null, $totals['order_invoice_id']);
            // }
        }
        if ((getAuthenticatedGuard() == 'client' || getAuthenticatedGuard() == 'api') && ($request['payment_method'] == 'cash' || $request['payment_method'] == 'no_payment_required')) {
            if ($order->total_price_after_tax > getCashPaymentPolicy($branchId)) {
                return respondError('error', 400,  ['error' => __('order.MaxCashLimit')]);
            }
        }

        //update Invoice Transaction
        if ($totals) {
            $invoiceService = app(invoiceService::class);
            $invoiceService->updateInvoiceTransaction($totals['order_invoice_id']);
        }

        // Set default address if needed
        if ($request['type'] == 'Delivery' && $order->client_address_id) {
            ClientAddress::where('user_id', $client_id)->update(['is_default' => 0]);
            ClientAddress::where('id', $order->client_address_id)->update(['is_default' => 1]);
        }

        if ($totals) {
            $invoice = Invoice::where('order_id', $order->id)->latest()->first();
            $orderTransactions = OrderTransaction::where('order_id', $order->id)->latest()->first();
            $orderTransactions->original_price = $invoice->original_price;
            $orderTransactions->save();
        }

        // Create Einvoice
        Einvoice::create([
            "invoice_id" => $totals['order_invoice_id'],
            "invoice_type" => 'i'
        ]);

        $userType = getAuthenticatedGuard();
        if ($userType == 'employee') {
            $userType = auth('employee')->user()->flag;
        }


        // Reservation data
        if ($request['type'] === 'reservation-table') {

            $order->table_id = $request['table_id'];
            $order->save();
        }

        // Send to kitchen

        DB::commit();
        //  SendOrderNotificationJob::dispatch(
        //     $order->id,
        //     $branchId,
        //     $userType,
        //     $request['type'],
        //     $created_by,
        //     $lang
        // );
        $this->send_notification($order->id, $branchId, $userType, $request['type'], $created_by, $lang);

        sendToKitchen($order->id, $lang);

        return ResponseWithSuccessData($lang, ['order_id' => $order->id, 'invoice_id' => (isset($totals['order_invoice_id'])) ? $totals['order_invoice_id'] : null], 1);
        // } catch (\Throwable $e) {
        //     DB::rollBack();
        //     // return RespondWithBadRequestWithData(['error' => $e->getMessage()]);
        //     return respondErrorData(__('error'), 400, $e->getMessage());
        // }
    }
    public function send_notification($order_id, $branch_id, $UserType, $OrderType, $created_by, $lang)
    {
        $order = Order::with([
            'orderDetails',
            'orderDetailsWithoutCancel.dish',
            'Table',
            'cashier.employeeSchedules'
        ])->find($order_id);

        if (!$order) {
            Log::warning("Order not found for notification: {$order_id}");
            return;
        }

        $maxDishTime = optional($order->orderDetailsWithoutCancel)->max(fn($detail) => $detail->dish->time ?? 0);

        // Handle cashier balance update broadcast
        if ($order->make_type === 'cashier' && $order->type !== 'talabat') {
            $this->handleCashierBalanceBroadcast($order);
        }

        // Determine recipients per user type
        $roles = [
            'cashier' => getEmployeesForNotify($branch_id, now(), 'cashier'),
            'waiter' => getEmployeesForNotify($branch_id, now(), 'waiter'),
            'customer_service' => getEmployeesForNotify($branch_id, now(), 'customer_service'),
        ];

        // Determine notification title and description
        $titles = [
            'ar' => "تم إضافة طلب جديد",
            'en' => "A new order added"
        ];

        $description = [
            'ar' => "تم استلام طلب جديد. الطلب [{$order->order_number}] بحاجة إلى معالجة.",
            'en' => "New order received. Order [{$order->order_number}] requires processing."
        ];

        // Send to specific groups depending on $UserType
        match ($UserType) {
            'client' => $this->notifyCashiers($roles['cashier'], $order, $created_by, $lang, $OrderType, $titles, $description),
            'customer_service' => $this->notifyCustomerServiceAndCashiers($roles, $order, $branch_id, $created_by, $lang, $OrderType, $titles, $description),
            'waiter' => $this->notifyWaitersAndCashiers($roles, $order, $created_by, $lang, $OrderType, $titles, $description),
            'cashier' => $this->notifyCashiersAndWaiters($roles, $order, $created_by, $lang, $OrderType, $titles, $description),
            default => Log::info("Unhandled user type for order {$order_id}: {$UserType}")
        };
    }

    private function handleCashierBalanceBroadcast($order)
    {
        $hasPaidTransaction = OrderTransaction::where('order_id', $order->id)
            ->where('payment_status', 'paid')
            ->where('is_refund', 0)
            ->first();

        if ($order->cashier_id && $order->cashier_machine_id && $hasPaidTransaction) {
            $cashierBalanceController = app(CashierBalanceController::class);

            $request = new Request([
                'order_id' => $order->id,
                'cashier_machine_id' => $order->cashier_machine_id,
                'employee_schedule_id' => optional($order->cashier->employeeSchedules->first())->id,
                'payment_method' => $hasPaidTransaction->payment_method,
            ]);

            $response = $cashierBalanceController->getCurrentBalance($request);

            if ($response->original['status']) {
                broadcast(new TotalPaid($order->cashier_id, $response->original['data']));
            }
        }
    }

    private function notifyCashiers($cashiers, $order, $created_by, $lang, $OrderType, $titles, $description)
    {
        if (empty($cashiers)) return;

        foreach ($cashiers as $cashier) {
            $this->sendNotificationAndBroadcast($cashier, 'cashier', $order, $created_by, $lang, $titles, $description, $OrderType);
        }
    }

    private function notifyWaitersAndCashiers($roles, $order, $created_by, $lang, $OrderType, $titles, $description)
    {
        if ($order->type === 'dine-in' && !empty($roles['waiter'])) {
            foreach ($roles['waiter'] as $waiter) {
                $this->sendNotificationAndBroadcast($waiter, 'waiter', $order, $created_by, $lang, $titles, $description, $OrderType);
            }
        }

        $this->notifyCashiers($roles['cashier'], $order, $created_by, $lang, $OrderType, $titles, $description);
    }

    private function notifyCashiersAndWaiters($roles, $order, $created_by, $lang, $OrderType, $titles, $description)
    {
        $this->notifyCashiers($roles['cashier'], $order, $created_by, $lang, $OrderType, $titles, $description);

        if ($order->type === 'dine-in') {
            foreach ($roles['waiter'] as $waiter) {
                $this->sendNotificationAndBroadcast($waiter, 'waiter', $order, $created_by, $lang, $titles, $description, $OrderType);
            }
        }
    }

    private function notifyCustomerServiceAndCashiers($roles, $order, $branch_id, $created_by, $lang, $OrderType, $titles, $description)
    {
        if (!empty($roles['customer_service'])) {
            foreach ($roles['customer_service'] as $service) {
                $this->sendNotificationAndBroadcast($service, 'customer_service', $order, $created_by, $lang, $titles, $description, $OrderType);
            }
        }

        $this->notifyCashiers($roles['cashier'], $order, $created_by, $lang, $OrderType, $titles, $description);
    }

    private function sendNotificationAndBroadcast($employee, $role, $order, $created_by, $lang, $titles, $description, $OrderType)
    {
        addNotification(
            'order',
            $role,
            $description['ar'],
            $description['en'],
            $titles['ar'],
            $titles['en'],
            $employee->id,
            $created_by,
            $lang,
            $order->id
        );

        $orderData = ['order_id' => $order->id, 'order_type' => $order->type];
        broadcast(new NewOrder2($employee->id, $employee->branch_id, $orderData));
    }


    public function storeOrderItems(
        Order $order,
        array $items,
        string $type,
        int $branchId,
        int $taxApplication,
        float $taxPercentage,
        float $serviceFeesValue,
        string $serviceFeesType,
        string $orderType,
        int $createdBy,
        $coupon_id = null,
        $order_edit = 0,
        $lang
    ): array {

        $subTotal = 0;
        $itemData = []; // to track totals for later discount distribution

        $invoiceService = app(invoiceService::class);
        foreach ($items as $item) {

            if ($type == "web") {
                $menuItem = BranchMenu::where('dish_id', $item['dish_id'])->where('branch_id', $branchId)->first();
            } else {
                $menuItem = BranchMenu::where('id', $item['dish_id'])->where('branch_id', $branchId)->first();
            }
            $price = $menuItem->price;
            $sizeId = $item['sizeId'] ?? null;
            if ($sizeId) {
                $size = BranchMenuSize::findOrFail($sizeId);
                $price = $size->price;
                $sizeId = $size->dish_size_id;
            }

            $quantity = $item['quantity'];
            $baseTotal = $price * $quantity;
            $addonsTotal = 0;

            // Store base item info
            $orderItem = OrderDetail::create([
                'order_id' => $order->id,
                'dish_id' => $menuItem->dish_id,
                'dish_size_id' => $sizeId,
                'quantity' => $quantity,
                'price_befor_tax' => $price,
                'tax_value' => 0,
                'price_after_tax' => 0,
                'service_fees' => 0,
                'created_by' => $createdBy,
                'note' => $item['note'] ?? null,
                'dish_order' => $item['dish_order'] ?? "-1",
                "coupon_id" => checkCouponApplyDish($coupon_id, [$menuItem->dish_id], $branchId) ? $coupon_id : null
            ]);


            $addonDetails = [];
            if (!empty($item['addon_categories'])) {
                foreach ($item['addon_categories'] as $addonCategory) {

                    if (!empty($addonCategory['addon'])) {
                        foreach ($addonCategory['addon'] as $addonId) {
                            $addon = BranchMenuAddon::findOrFail($addonId);
                            $addonsTotal += ($addon->price * $quantity);
                            $addonDetails[] = OrderAddon::create([
                                'order_details_id' => $orderItem->id,
                                'branch_menu_addon_id' => $addonId,
                                'price_before_tax' => 0, // updated later
                                'tax_value' => 0,
                                'price_after_tax' => 0,
                                'service_fees' => 0,
                                'created_by' => $createdBy,
                                'order_id' => $order->id,
                                'quantity' => $quantity,
                                'dish_addon_id' => $addon->dish_addon_id,
                                'in_request_return' => 0,
                            ]);
                        }
                    }
                }
            }

            $itemTotal = $baseTotal + $addonsTotal;
            $subTotal += $itemTotal;

            $itemData[] = [
                'order_item' => $orderItem,
                'addons' => $addonDetails,
                'baseTotal' => $baseTotal,
                'addonsTotal' => $addonsTotal,
                'fullTotal' => $itemTotal,
                'quantity' => $quantity,
            ];
        }

        $order_invoice_id = 0;

        $transformedDishes = [];
        foreach ($items as $item) {
            $transformedDishes[] = [
                'dish_id' => $item['dish_id'],
                'quantity' => $item['quantity'],
                'sizeId' => $item['sizeId'] ?? null,
                'addons' => collect($item['addons'])->pluck('id')->toArray(),
                'status' => 0,

            ];
        }
        //call invoice function
        if ($order_edit) {
            $transformedDishes = [];
            foreach ($order->orderDetails as $item) {
                $transformedDishes[] = [
                    'dish_id' => $item['dish_id'],
                    'quantity' => $item['quantity'],
                    'addons' => collect($item['addons'])->pluck('id')->toArray(),
                    'status' => 0,

                ];
            }
            $hasPaidTransaction = OrderTransaction::where('order_id', $order->id)
                ->where('payment_status', 'paid')
                ->where('is_refund', 0)
                ->exists();
            if (!$hasPaidTransaction) {

                $responseInv = $invoiceService->editInvoice($order->id, $order_edit);
                $responseData = $responseInv->original;
                // Handle service response
                if (!$responseData['status']) {

                    // return $responseInv;
                } else {

                    $order_invoice_id = $responseData['data'];
                }
            }
        } else {

            $order_details_ids = collect($itemData)->pluck('order_item')->flatten(1)->pluck('id')->all();
            $order_addon_ids = collect($itemData)->pluck('addons')->flatten(1)->pluck('id')->all();
            $order_invoice_id = $invoiceService->makeInvoice(
                $order->id,
                "order",
                "invoice",
                $quantity = 0,
                $dish_size_id = 0,
                $invoice_id = null,
                $order_details_ids,
                $order_addon_ids
            );
        }
        // Calculate coupon discount
        $couponValue = 0;
        $couponApplication = $order->coupon_id ? getBranchSettings($branchId, 'coupon_application') : null;
        // if ($order->coupon_id && $couponApplication === 0) {
        //     $coupon = Coupon::find($order->coupon_id);
        //     $couponValue = calcCoupon($subTotal, $coupon);
        //     $subTotalAfterCoupon = applyCoupon($subTotal, $coupon);
        // } else {
        //     $subTotalAfterCoupon = $subTotal;
        // }
        // Recalculate tax/service based on discounted total
        $totalTax = 0;
        $totalService = 0;
        $allSubTotalAfterCoupon = 0;
        $totalCouponValue = 0;

        // dd($transformedDishes);
        // $addonsCount = collect($itemData)->sum(fn($item) => count($item['addons']));
        $collectedItems = [];

        foreach ($itemData as $index => $data) {
            $orderItem = $data['order_item'];
            $addons = $data['addons'];
            $itemTotal = $data['fullTotal'];
            $baseTotal = $data['baseTotal'];
            $dish_id = $data['order_item']->dish_id;

            $addonsTotal = $data['addonsTotal'];
            // $discountRatio = $itemTotal / $subTotal;
            // $discountAmount = $couponValue * $discountRatio;
            // $discountedTotal = $itemTotal - $discountAmount;
            if ($order->coupon_id && $couponApplication == false) {
                $coupon = Coupon::find($order->coupon_id);
                if ($coupon->type == 'fixed') {
                    $result =   $this->calculateCouponValue($transformedDishes, $branchId,  $coupon->value, $type);
                    if ($order_edit) {
                        $subTotalAfterCoupon = $result
                            ->last(fn($itemCoupon) => $itemCoupon['dish_id'] == $dish_id && $itemCoupon['type'] == 'dish' && $itemCoupon['has_addon'])['priceAfter'] ?? 0;
                        $couponValue = $result
                            ->last(fn($itemCoupon) => $itemCoupon['dish_id'] == $dish_id && $itemCoupon['type'] == 'dish' && $itemCoupon['has_addon'])['couponValue'] ?? 0;
                    } else {

                        // Build full condition in a single place
                        $branch_dish_size = BranchMenuSize::where('dish_size_id',  $orderItem->dish_size_id)->where('branch_id', $branchId)->where('is_active', 1)->first();

                        $hasAddon = count($addons) > 0;
                        $hasSize = $branch_dish_size ? $branch_dish_size->id : false;


                        $matchedItems = $result->filter(
                            fn($row) =>
                            $row['dish_id'] == $dish_id &&
                                $row['type'] == 'dish' &&
                                (int)$row['has_addon'] === (int)$hasAddon &&
                                (int)$row['has_size'] === ((int)$hasSize > 0 ? 1 : 0)
                                && ($hasSize ? (int)$row['sizeId'] === (int)$branch_dish_size->dish_size_id : true)
                        );

                        $item = $matchedItems->first(); // or last(), or whichever logic you want

                        // Safe extraction
                        $subTotalAfterCoupon = $item['priceAfter'] ?? 0;
                        $couponValue         = $item['couponValue'] ?? 0;

                        // if (count($addons) > 0) {

                        //     $subTotalAfterCoupon = $result
                        //         ->first(fn($itemCoupon) => $itemCoupon['dish_id'] == $dish_id && $itemCoupon['type'] == 'dish' && $itemCoupon['has_addon'])['priceAfter'] ?? 0;
                        //     $couponValue = $result
                        //         ->first(fn($itemCoupon) => $itemCoupon['dish_id'] == $dish_id && $itemCoupon['type'] == 'dish' && $itemCoupon['has_addon'])['couponValue'] ?? 0;
                        // } else {
                        //     $subTotalAfterCoupon = $result
                        //         ->first(fn($itemCoupon) => $itemCoupon['dish_id'] == $dish_id && $itemCoupon['type'] == 'dish' && !$itemCoupon['has_addon'])['priceAfter'] ?? 0;
                        //     $couponValue = $result
                        //         ->first(fn($itemCoupon) => $itemCoupon['dish_id'] == $dish_id && $itemCoupon['type'] == 'dish' && !$itemCoupon['has_addon'])['couponValue'] ?? 0;
                        // }
                    }
                } else {
                    $couponValue = calcCoupon($baseTotal, $coupon);
                    $subTotalAfterCoupon = applyCoupon($baseTotal, $coupon);
                }
            } else if ($orderItem->coupon_id) {
                $coupon = Coupon::find($orderItem->coupon_id);
                if ($coupon->type == 'fixed') {
                    $couponValue = $coupon->value;
                    $subTotalAfterCoupon = $baseTotal - $couponValue;
                } else {
                    $couponValue = calcCoupon($baseTotal, $coupon);
                    $subTotalAfterCoupon = applyCoupon($baseTotal, $coupon);
                }
            } else {


                $subTotalAfterCoupon = $baseTotal;
            }


            // Service fee
            $serviceFee = 0;
            if (in_array($orderType, ['dine-in', 'reservation-table'])) {
                $serviceFee = ($serviceFeesType === "percentage") ? ($subTotalAfterCoupon * $serviceFeesValue / 100) : 0;
            }
            // Tax
            $taxValue = ($taxApplication == 1 && $baseTotal != 0)
                ? CalculateTax($taxPercentage, $subTotalAfterCoupon + $serviceFee)
                : ($subTotalAfterCoupon + $serviceFee) * $taxPercentage / 100;
            $priceAfterTax = $subTotalAfterCoupon + $serviceFee + $taxValue;
            $orderItem->update([
                'price_befor_tax' => $subTotalAfterCoupon,
                'price_before_coupon' => $baseTotal,

                'service_fees' => $serviceFee,
                'tax_value' => $taxValue,
                'price_after_tax' => $subTotalAfterCoupon + $serviceFee + $taxValue,
                'coupon_value' => $orderItem->coupon_id ? $couponValue : 0,
            ]);
            // Add to totals
            $totalTax += $taxValue;
            $totalService += $serviceFee;
            $totalCouponValue += $couponValue;
            $allSubTotalAfterCoupon += $subTotalAfterCoupon;
            // Optionally update addons evenly (or keep as-is if minor)

            foreach ($addons as $addon) {
                $BranchMenuAddon = BranchMenuAddon::where('dish_addon_id', $addon->dish_addon_id)->where('branch_id', $branchId)->where('is_active', 1)->first();

                $addonPrice = $BranchMenuAddon->price * $data['quantity'];
                // $addonRatio = $addonPrice / $itemTotal;
                // $addonDiscount = $discountAmount * $addonRatio;
                // $addonDiscounted = $addonPrice - $addonDiscount;

                if ($order->coupon_id && $couponApplication == false) {
                    $coupon = Coupon::find($order->coupon_id);

                    if ($coupon->type == 'fixed') {


                        $result =   $this->calculateCouponValue($transformedDishes, $branchId,  $coupon->value, $type);
                        $branch_dish_size = BranchMenuSize::where('dish_size_id',  $orderItem->dish_size_id)->where('branch_id', $branchId)->where('is_active', 1)->first();

                        $hasAddon = count($addons) > 0;
                        $hasSize = $branch_dish_size ? $branch_dish_size->id : false;

                        $subTotalAfterCoupon = $result
                            ->first(fn($itemAddon) => $itemAddon['dish_id'] == $BranchMenuAddon->id && $itemAddon['type'] == 'addon')['priceAfter'] ?? 0;

                        $couponValue = $result
                            ->first(fn($itemCoupon) => $itemCoupon['dish_id'] == $BranchMenuAddon->id && $itemCoupon['type'] == 'addon')['couponValue'] ?? 0;

                        // $subTotalAfterCoupon = $couponValue > 0 ? $addonPrice - $couponValue : $addonPrice;
                    } else {

                        $couponValue = calcCoupon($addonPrice, $coupon);
                        $subTotalAfterCoupon = applyCoupon($addonPrice, $coupon);
                    }
                } else {
                    $subTotalAfterCoupon = $addonPrice;
                }

                $addonService = in_array($orderType, ['dine-in', 'reservation-table']) && $serviceFeesType == "percentage"
                    ? ($subTotalAfterCoupon * $serviceFeesValue / 100)
                    : 0;
                $addonTax = ($taxApplication === 1)
                    ? CalculateTax($taxPercentage, $subTotalAfterCoupon + $addonService)
                    : ($subTotalAfterCoupon + $addonService) * $taxPercentage / 100;


                $addon->update([
                    'price_before_tax' => $subTotalAfterCoupon,
                    'price_before_coupon' => $addonPrice,
                    'service_fees' => $addonService,
                    'tax_value' => $addonTax,
                    'price_after_tax' => $subTotalAfterCoupon + $addonService + $addonTax,
                ]);

                $totalTax += $addonTax;
                $totalService += $addonService;
                $totalCouponValue += $couponValue;
                $allSubTotalAfterCoupon += $subTotalAfterCoupon;
                // dd($totalCouponValue, $allSubTotalAfterCoupon);
            }
            $orderItem->refresh();
            $totalAddonsPrice = collect($addons)->sum(function ($addon) {
                return $addon->price_before_tax ?? 0;
            });

            $dishPriceBeforeCoupon = $orderItem->price_before_coupon ?? $orderItem->price_befor_tax ?? 0;

            $totalBeforeCoupon = $dishPriceBeforeCoupon + $addonsTotal;
            $orderItem->load('dish');
            $collectedItems[] = [
                'order_detail_id' => $orderItem->id,
                'quantity' => $orderItem->quantity,
                'status' => 'pending',
                'total_dish_price' => formatFloat($totalBeforeCoupon),
                'dish_name_ar' => $orderItem->dish->name_ar ?? null,
                'dish_name_en' => $orderItem->dish->name_en ?? null,
            ];
        }

        // Add fixed service if applicable
        if (in_array($orderType, ['dine-in', 'reservation-table']) && $serviceFeesType === 'fixed') {
            $totalService = $serviceFeesValue;
        }
        if ($orderType == 'Delivery' && $order->coupon_id) {

            if ($order->coupon->apply_type == 'order' && $order->coupon->type == 'percentage' && $order->coupon->value == 100) {
                $order->update([
                    'delivery_fees' => 0,
                ]);
                $order->refresh();
            }
        }
        $total = $allSubTotalAfterCoupon + $totalService + $totalTax + $order->delivery_fees;
        $order->update([
            'total_price_befor_tax' => $allSubTotalAfterCoupon,
            'total_price_before_coupon' => $subTotal,
            'coupon_value' => ($order->coupon_id) ? $totalCouponValue : null,
            'tax_value' => $totalTax,
            'service_fees' => $totalService,
            'total_price_after_tax' => $total,
        ]);

        return compact('subTotal', 'totalTax', 'totalService', 'total', 'order_invoice_id', 'collectedItems');
    }
    public function getCountItemsToCoupon($orderId)
    {
        $order = Order::with(['orderAddons', 'orderDetails'])->find($orderId);

        // أطباق: نجهز مصفوفة فيها السعر + id
        $dishData = collect($order->orderDetails)->map(function ($detail) {
            return [
                'type'  => 'dish',
                'id'    => $detail->dish_id,
                'price' => (float)$detail->price_befor_tax * (float)$detail->quantity,
            ];
        });

        $addonData = collect($order->orderAddons)->map(function ($addon) {
            return [
                'id'       => $addon->dish_addon_id,
                'quantity' => $addon->quantity,
            ];
        });

        $addonPrices = BranchMenuAddon::whereIn('dish_addon_id', $addonData->pluck('id'))
            ->where('branch_id', $order->branch_id)
            ->get(['dish_addon_id', 'price'])
            ->map(function ($addon) use ($addonData) {
                $quantity = $addonData->firstWhere('id', $addon->dish_addon_id)['quantity'] ?? 1;

                return [
                    'type'     => 'addon',
                    'id'       => $addon->dish_addon_id,
                    'price'    => (float)$addon->price * (float)$quantity,
                    'quantity' => $quantity,
                ];
            });

        $allItems = $dishData->merge($addonPrices)->filter(fn($item) => $item['price'] > 0)->values();
        if ($allItems->isEmpty()) {
            return [];
        }

        $couponValue = Coupon::find($order->coupon_id)->value;

        $initialDiscount = $couponValue / $allItems->count();

        $validItems = $allItems->filter(fn($item) => $initialDiscount <= $item['price']);

        if ($validItems->isEmpty()) {
            return [];
        }

        $discountPerItem = $couponValue / $validItems->count();

        $itemsWithDiscount = $validItems->map(function ($item) use ($discountPerItem) {
            $item['discount'] = $discountPerItem;
            return $item;
        });

        $itemsWithDiscount['discount'] = $discountPerItem;

        return $itemsWithDiscount;
    }




    public function CalculateOrder($order_id)
    {

        $subTotal = 0;
        $itemData = []; // to track totals for later discount distribution
        $Order = Order::find($order_id);

        $IDBranch = $Order->branch_id;
        $coupon_application = getBranchSettings($IDBranch, 'coupon_application');

        $tax_application = getBranchSettings($IDBranch, 'tax_application');
        $tax_percentage = getBranchSettings($IDBranch, 'tax_percentage');
        $service_fees_value =  ($Order->type  != 'dine-in' && $Order->type != "reservation-table") ? 0 : getBranchSettings($IDBranch, 'service_fees');
        $service_fees_type =  ($Order->type  != 'dine-in' && $Order->type != "reservation-table") ? 0 : getBranchSettings($IDBranch, 'service_fees_type');
        $delivery_fees = ($Order->type == 'Delivery') ?  getBranchSettings($IDBranch, 'delivery_fees') : 0;

        $DataOrderDetails = OrderDetail::where('order_id', $order_id)->where('status', '!=', 'cancel')->get();
        foreach ($DataOrderDetails as  $DataOrderDetail) {

            $Branch_Dish = BranchMenu::where('dish_id', $DataOrderDetail->dish_id)->where('branch_id', $IDBranch)->where('is_active', 1)->first();
            if ($Branch_Dish) {
                $has_size = $Branch_Dish->dish->has_sizes;
                if ($has_size && $DataOrderDetail->dish_size_id) {
                    $size_id = $DataOrderDetail->dish_size_id;
                    $branch_dish_size = BranchMenuSize::where('dish_size_id', $size_id)->where('branch_id', $IDBranch)->where('is_active', 1)->first();
                    $price =  $branch_dish_size->price;
                } else {

                    $price =  $Branch_Dish->price;
                }
            }


            $baseTotal = $price * $DataOrderDetail['quantity'];
            $addonsTotal = 0;

            $OrderAddonsArray = OrderAddon::where('order_details_id', $DataOrderDetail->id)->where('status', '!=', 'cancel')->get();

            $addonDetails = [];
            if (!empty($OrderAddonsArray)) {
                foreach ($OrderAddonsArray as $OrderAddonsArr) {
                    $addon_id = $OrderAddonsArr->Addon->id;
                    $addon = BranchMenuAddon::where('dish_addon_id', $addon_id)->where('branch_id', $IDBranch)->where('is_active', 1)->first();

                    $addonsTotal += ($addon->price * $OrderAddonsArr['quantity']);
                }
            }

            $itemTotal = $baseTotal + $addonsTotal;
            $subTotal += $itemTotal;

            $itemData[] = [
                'order_item' => $DataOrderDetail,
                'addons' => $OrderAddonsArray,
                'baseTotal' => $baseTotal,
                'addonsTotal' => $addonsTotal,
                'fullTotal' => $itemTotal,
                'quantity' => $DataOrderDetail['quantity'],
            ];
        }

        // Calculate coupon discount
        $couponValue = 0;
        $couponApplication = $Order->coupon_id ? getBranchSettings($IDBranch, 'coupon_application') : null;

        $totalTax = 0;
        $totalService = 0;
        $allSubTotalAfterCoupon = 0;
        $totalCouponValue = 0;
        $transformedDishes = [];
        $fullOrderData = Order::with([
            'orderDetails',
            'orderAddons'
        ])->find($order_id);

        $transformedDishes = $fullOrderData->orderDetails->map(function ($item) {
            return [
                'dish_id' => $item->dish_id,
                'quantity' => $item->quantity,
                'addons' => $item->dishAddons->pluck('dish_addon_id')->toArray(),
                'status' => $item->in_request_return,
                'sizeId' => $item->sizeId ?? null,


            ];
        });
        foreach ($itemData as $data) {
            $orderItem = $data['order_item'];
            $addons = $data['addons'];
            $itemTotal = $data['fullTotal'];
            $baseTotal = $data['baseTotal'];

            $addonsTotal = $data['addonsTotal'];

            if ($orderItem->coupon_id) {
                $coupon = Coupon::find($orderItem->coupon_id);
                // if ($coupon->type == 'fixed') {
                //     $couponValue = $coupon->value;
                //     $subTotalAfterCoupon = $baseTotal - $couponValue;
                // } else {
                $couponValue = calcCoupon($baseTotal, $coupon);
                $subTotalAfterCoupon = applyCoupon($baseTotal, $coupon);
                // }
            } else if ($Order->coupon_id) {
                $coupon = Coupon::find($Order->coupon_id);
                if ($coupon->type == 'fixed') {
                    $result =   $this->calculateCouponValue($transformedDishes, $IDBranch,  $coupon->value, 'web');
                    // $subTotalAfterCoupon = $result
                    //     ->first(fn($itemAddon) => $itemAddon['dish_id'] == $orderItem->dish_id && $itemAddon['type'] == 'dish' && $itemAddon['has_addon'])['priceAfter'] ?? 0;
                    //     $couponValue = $result
                    //     ->first(fn($itemCoupon) => $itemCoupon['dish_id'] ==  $orderItem->dish_id && $itemCoupon['type'] == 'dish' && $itemCoupon['has_addon'])['couponValue'] ?? 0;
                    $branch_dish_size = BranchMenuSize::where('dish_size_id',  $orderItem->dish_size_id)->where('branch_id', $IDBranch)->where('is_active', 1)->first();

                    $hasAddon = count($addons) > 0;
                    $hasSize = $branch_dish_size ? $branch_dish_size->id : false;


                    $matchedItems = $result->filter(
                        fn($row) =>
                        $row['dish_id'] == $orderItem->dish_id &&
                            $row['type'] == 'dish' &&
                            (int)$row['has_addon'] === (int)$hasAddon &&
                            (int)$row['has_size'] === ((int)$hasSize > 0 ? 1 : 0)
                            && ($hasSize ? (int)$row['sizeId'] === (int)$branch_dish_size->dish_size_id : true)
                    );

                    $item = $matchedItems->first(); // or last(), or whichever logic you want

                    // Safe extraction
                    $subTotalAfterCoupon = $item['priceAfter'] ?? 0;
                    $couponValue         = $item['couponValue'] ?? 0;

                    // if (count($addons) > 0) {
                    //     $subTotalAfterCoupon = $result
                    //         ->first(fn($itemCoupon) => $itemCoupon['dish_id'] == $orderItem->dish_id && $itemCoupon['type'] == 'dish' && $itemCoupon['has_addon'])['priceAfter'] ?? 0;
                    //     $couponValue = $result
                    //         ->first(fn($itemCoupon) => $itemCoupon['dish_id'] == $orderItem->dish_id && $itemCoupon['type'] == 'dish' && $itemCoupon['has_addon'])['couponValue'] ?? 0;
                    // } else {
                    //     $subTotalAfterCoupon = $result
                    //         ->first(fn($itemCoupon) => $itemCoupon['dish_id'] == $orderItem->dish_id && $itemCoupon['type'] == 'dish' && !$itemCoupon['has_addon'])['priceAfter'] ?? 0;
                    //     $couponValue = $result
                    //         ->first(fn($itemCoupon) => $itemCoupon['dish_id'] == $orderItem->dish_id && $itemCoupon['type'] == 'dish' && !$itemCoupon['has_addon'])['couponValue'] ?? 0;
                    // }
                } else {
                    $couponValue = calcCoupon($baseTotal, $coupon);
                    $subTotalAfterCoupon = applyCoupon($baseTotal, $coupon);
                }
            } else {
                $subTotalAfterCoupon = $baseTotal;
            }
            // Service fee
            $serviceFee = 0;
            if (in_array($Order->type, ['dine-in', 'reservation-table'])) {
                $serviceFee = ($service_fees_type === "percentage") ? ($subTotalAfterCoupon * $service_fees_value / 100) : 0;
            }

            // Tax
            $taxValue = ($tax_application == 1)
                ? CalculateTax($tax_percentage, $subTotalAfterCoupon + $serviceFee)
                : ($subTotalAfterCoupon + $serviceFee) * $tax_percentage / 100;


            $orderItem->price_befor_tax = $subTotalAfterCoupon;
            $orderItem->price_before_coupon = $baseTotal;

            $orderItem->service_fees = $serviceFee;
            $orderItem->tax_value = $taxValue;
            $orderItem->coupon_value = $couponValue;
            $orderItem->price_after_tax = $subTotalAfterCoupon + $serviceFee + $taxValue;
            $orderItem->save();

            // Add to totals
            $totalTax += $taxValue;
            $totalService += $serviceFee;
            $totalCouponValue += $couponValue;
            $allSubTotalAfterCoupon += $subTotalAfterCoupon;


            // Optionally update addons evenly (or keep as-is if minor)
            foreach ($addons as $addon) {
                $BranchMenuAddon = BranchMenuAddon::where('dish_addon_id', $addon->dish_addon_id)->where('branch_id', $IDBranch)->where('is_active', 1)->first();

                $addonPrice = $BranchMenuAddon->price * $data['quantity'];
                if ($orderItem->coupon_id) {
                    $coupon = Coupon::find($DataOrderDetail->coupon_id);
                    // if ($coupon->type == 'fixed') {
                    //     $couponValue = $coupon->value;
                    //     $subTotalAfterCoupon =   $addonPrice - $couponValue;
                    // } else {
                    $couponValue = calcCoupon($addonPrice, $coupon);
                    $subTotalAfterCoupon = applyCoupon($addonPrice, $coupon);
                    // }
                } else if ($Order->coupon_id) {
                    $coupon = Coupon::find($Order->coupon_id);
                    if ($coupon->type == 'fixed') {
                        // $couponValue = $coupon->value;
                        $result =   $this->calculateCouponValue($transformedDishes, $IDBranch,  $coupon->value, 'web');

                        $subTotalAfterCoupon = $result
                            ->first(fn($itemAddon) => $itemAddon['dish_id'] == $addon->dish_addon_id && $itemAddon['type'] == 'addon')['priceAfter'] ?? 0;
                        $couponValue = $result
                            ->first(fn($itemCoupon) => $itemCoupon['dish_id'] == $addon->dish_addon_id && $itemCoupon['type'] == 'addon')['couponValue'] ?? 0;
                    } else {
                        $couponValue = calcCoupon($addonPrice, $coupon);
                        $subTotalAfterCoupon = applyCoupon($addonPrice, $coupon);
                    }
                } else {
                    $subTotalAfterCoupon = $addonPrice;
                }


                $addonService = in_array($Order->type, ['dine-in', 'reservation-table']) && $service_fees_type == "percentage"
                    ? ($subTotalAfterCoupon * $service_fees_value / 100)
                    : 0;
                $addonTax = ($tax_application == 1)
                    ? CalculateTax($tax_percentage, $subTotalAfterCoupon + $addonService)
                    : ($subTotalAfterCoupon + $addonService) * $tax_percentage / 100;
                $addon->price_before_tax = $subTotalAfterCoupon;
                $addon->price_before_coupon = $addonPrice;
                $addon->service_fees = $addonService;
                $addon->tax_value = $addonTax;
                $addon->price_after_tax = $subTotalAfterCoupon + $addonService + $addonTax;
                $addon->save();



                $totalTax += $addonTax;
                $totalService += $addonService;
                $totalCouponValue += $couponValue;

                $allSubTotalAfterCoupon += $subTotalAfterCoupon;
            }
        }


        // Add fixed service if applicable
        if (in_array($Order->type, ['dine-in', 'reservation-table']) && $service_fees_type === 'fixed') {
            $totalService = $service_fees_value;
        }
        if ($Order->type == 'Delivery' && $Order->coupon_id) {

            if ($Order->coupon->apply_type == 'order' && $Order->coupon->type == 'percentage' && $Order->coupon->value == 100) {
                $Order->update([
                    'delivery_fees' => 0,
                ]);
                $Order->refresh();
            }
        }
        $total = $allSubTotalAfterCoupon + $totalService + $totalTax + $Order->delivery_fees;
        $Order->total_price_befor_tax = $allSubTotalAfterCoupon;
        $Order->total_price_before_coupon = $subTotal;
        $Order->tax_value = $totalTax;
        $Order->coupon_value =  ($Order->coupon_id) ? $totalCouponValue : null;
        $Order->service_fees = $totalService;
        $Order->total_price_after_tax = $total;
        $Order->save();

        return true;
    }

    function storePaymentTransaction($order_id, $order_type, $payment_method, $created_by, $coupon_id, $total_price_after_tax, $orderDeposit, $makeType,  $payment_status, $is_refund = 0, $reference_number = null, $invoice_id = null, $paid_at = null)
    {
        $order_transaction = new OrderTransaction();
        $order_transaction->order_id = $order_id;
        $order_transaction->invoice_id = $invoice_id;
        $order_transaction->is_refund = $is_refund;
        $order_transaction->payment_status = match (convertPolicyToMethod($payment_method)) {
            'credit' => 'paid',
            'deposit' => 'part',
            'cash' => ($makeType == 'cashier') ? $payment_status : 'unpaid',
        };
        $order_transaction->payment_method = (convertPolicyToMethod($payment_method) == 'deposit') ? 'credit' : convertPolicyToMethod($payment_method);
        $order_transaction->transaction_id = Str::uuid()->toString();
        if ($is_refund == 1) {
            $order_transaction->refund = $total_price_after_tax;
            $order_transaction->original_price = $total_price_after_tax;
        } else {
            $order_transaction->paid = (convertPolicyToMethod($payment_method) == 'deposit') ? $total_price_after_tax  * ($orderDeposit / 100) : $total_price_after_tax;
        }
        $order_transaction->date = date('Y-m-d');
        $order_transaction->created_by = $created_by;
        $order_transaction->paid_at = (convertPolicyToMethod($payment_method) == 'credit' || $payment_status == 'paid' || convertPolicyToMethod($payment_method) == 'deposit') ? date('Y-m-d H:i:s') : $paid_at;
        $order_transaction->coupon_id = $coupon_id;
        $order_transaction->reference_number = $reference_number;
        $order_transaction->save();
    }
    public function show($lang, $id, $api = 0)
    {

        $order = Order::find($id);
        if ($api === 1) {
            if (auth('employee')->check() && auth('employee')->user()->hasRole('Branch_Manager')) {
                $user = auth('employee')->user();
                $managerBranchId = $user->branch_id;

                if ($order) {
                    if ($order->branch_id != $managerBranchId) {
                        abort(403, __('messages.forbidden'));
                    }
                } else {
                    abort(403, __('messages.notfound'));
                }
            }
        } else {
            if (auth('admin')->user()->hasRole('Branch Manager')) {
                $managerBranchId = getBranchManagerID();
                if ($order) {
                    if ($order->branch_id != $managerBranchId) {
                        abort(403, __('messages.forbidden'));
                    }
                } else {
                    abort(403, __('messages.notfound'));
                }
            }
        }

        $order['details'] = OrderDetail::with(['dishSize', 'dish'])->where('order_id', $id)->get();
        $order['addons'] = OrderAddon::with('orderDetail.dish')->where('order_id', $id)->get();

        // Controller should have:
        $order['transactions'] = OrderTransaction::where('order_id', $id)->get(); // Plural 'transactions'
        $order['address'] = ClientAddress::where('id', $order->client_address_id)->first();
        $order['tracking'] = OrderTracking::where('order_id', $id)->get();
        $order_tracking = OrderTracking::where('order_id', $order->id)->orderby('id', 'desc')->first();

        $order['next_status'] = $this->getNextStatus($order_tracking->order_status, $order->type);
        return ResponseWithSuccessData($lang, $order, 1);
    }

    public function getNextStatus($status, $orderType): array
    {
        $next_status = array();

        if ($orderType === 'Takeaway') {
            if ($status == 'pending') {
                array_push($next_status, 'in_progress');
                array_push($next_status, 'cancelled');
            }            // For Takeaway orders, if status is 'in_progress', next status is only 'completed'
            else if ($status == 'cancelled' || $status == 'completed') {
                $next_status = array();
            } else if ($status == 'in_progress') {
                array_push($next_status, 'readyForPickup');
                array_push($next_status, 'cancelled');
            } else if ($status == 'readyForPickup') {
                array_push($next_status, 'completed');
                array_push($next_status, 'cancelled');
            }
        } else if ($orderType === 'Delivery') {
            // For other order types (Delivery), maintain current logic
            if ($status == 'pending') {
                array_push($next_status, 'in_progress');
                array_push($next_status, 'cancelled');
            } else if ($status == 'cancelled' || $status == 'completed') {
                $next_status = array(); // No next status
            } else if ($status == 'in_progress') {
                array_push($next_status, 'readyForPickup');
                array_push($next_status, 'cancelled');
            } else if ($status == 'readyForPickup') {
                array_push($next_status, 'on_way');
                array_push($next_status, 'cancelled');

                // array_push($next_status, values: 'delivered');
            } else if ($status == 'on_way') {
                array_push($next_status, 'delivered');
                array_push($next_status, 'cancelled');
            } else if ($status == 'delivered') {
                array_push($next_status, 'completed');
            }
        } else if ($orderType == 'dine-in') {
            if ($status == 'pending') {
                array_push($next_status, 'in_progress');
                array_push($next_status, 'cancelled');
            } else if ($status == 'cancelled' || $status == 'completed') {
                $next_status = array(); // No next status
            } else if ($status == 'in_progress') {
                array_push($next_status, 'readyForPickup');
                array_push($next_status, 'cancelled');
            } else if ($status == 'readyForPickup') {
                array_push($next_status, 'completed');
            }
        }

        return $next_status;
    }


    public function reOrder(Request $request, $checkToken)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $validateData = Validator::make($request->all(), [
            'orderId' => 'required|exists:orders,id'
        ]);


        if ($validateData->fails()) {
            //return respondError('Validation Error.', 404, $validateData->errors());
            //return respondErrorData( __('validation.dataNotFound'), 404, __('validation.dataNotFound'));
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => __('validation.dataNotFound'),
                'data' => null,
                'errorData' => ['error' => __('validation.dataNotFound')]
            ], 200);
        }

        $get_order = Order::with(['coupon'])->find($request->orderId);

        if (!$get_order) {
            return response()->json(['error' =>  __('order.address_not_found')], 404);
        }
        $branch = Branch::where('id', $get_order->branch_id)->first();
        // For 12-hour format with AM/PM
        $selected_date = null;
        $selected_time = null;
        if ($get_order->type == 'Takeaway') {

            $pickup = Carbon::parse($get_order->takeaway_pickup_time);

            $selected_date = $pickup->format('Y-m-d');  // "2025-06-22"
            $selected_time = str_replace(['AM', 'PM'], ['ص', 'م'], $pickup->format('h:i A'));  // "11:34 ص"
        }
        $new_order = [
            'coupon_code' => $get_order->coupon ? $get_order->coupon->code : "",
            'type' => $get_order->type,
            'address_id' => $get_order->client_address_id,
            'note' => $get_order->note,
            //'branch' => null,
            'branch_name' => $branch->name,
            'branch_address' => $branch->address,
            'branch_phone' => $branch->phone,
            'branch_id' => $get_order->branch_id,
            'selected_date' => $selected_date,
            'selected_time' => $selected_time,
            'items' => null,
        ];

        if ($branch) {
            //$new_order['branch'] = ['id' => $branch->id,'name' => $branch->name,'latitute' => $branch->latitute,'longitute' => $branch->longitute];
        }

        $order_details = OrderDetail::where('order_id', $get_order->id)
            ->with(['dishAddons'])
            ->get();

        $check_dish = 0;

        foreach ($order_details as $order_detail) {
            $dish = BranchMenu::Active()
                ->where('dish_id', $order_detail->dish_id)
                ->where('branch_id', $get_order->branch_id)
                ->first();

            if ($dish) {
                $category_dish_addon = BranchMenuAddonCategory::where('addon_category_id', 1)->where('branch_id', $get_order->branch_id)->first();
                $addon_categories = ['id' => $category_dish_addon->id, 'name' => $category_dish_addon ? ($category_dish_addon->addonCategories ? $category_dish_addon->addonCategories->name : null) : null, 'min_addons' => minAddons($order_detail->dish_id, 1), 'max_addons' => maxAddons($order_detail->dish_id, 1), 'addon' => null];

                foreach ($order_detail->dishAddons as $order_addon) {
                    $dish_addon = BranchMenuAddon::Active()
                        ->where('dish_addon_id', $order_addon->dish_addon_id)
                        ->where('branch_id', $get_order->branch_id)
                        ->first();

                    if ($dish_addon) {
                        $addon_categories['addon'][] = ['id' => $dish_addon->id, 'name' => $dish_addon->dishAddons ? ($dish_addon->dishAddons->addons ? $dish_addon->dishAddons->addons->name : null) : null, 'price' => doubleval($dish_addon->dishAddons ? $dish_addon->dishAddons->price : 0), 'currency_symbol' => $branch->country ? $branch->country->currency_symbol : null];
                    }
                }

                $dish_size = BranchMenuSize::Active()
                    ->where('dish_id', $order_detail->dish_id)
                    ->where('branch_id', $get_order->branch_id)
                    ->where('dish_size_id', $order_detail->dish_size_id)
                    ->first();

                if ($dish_size) {
                    $size_selected = [
                        "id" => $dish_size->id,
                        "name" => $dish_size->dishSizes ? $dish_size->dishSizes->name : null,
                        "price" => doubleval($dish_size->price),
                        "currency_symbol" => $branch->country ? $branch->country->currency_symbol : null,
                        "default_size" => $dish_size->dishSizes ? $dish_size->dishSizes->name : false
                    ];
                } else {
                    $size_selected = null;
                }

                $new_order['items'][] = [
                    'dish_id' => ($checkToken) ? $dish->id : $order_detail->dish_id,
                    'quantity' => $order_detail->quantity,
                    "name" => $dish->dish ? $dish->dish->name : null,
                    "price" => doubleval($dish_size ? $dish_size->price : $dish->price),
                    "currency_symbol" => $branch->country ? $branch->country->currency_symbol : null,
                    "has_size" => $dish->dish->has_sizes ? true : false,
                    "has_addon" => $dish->dish->has_addon ? true : false,
                    "image" => "",
                    "note" => $order_detail->note,
                    "size_selected" => $size_selected,
                    'addon_categories' => [$addon_categories],
                ];
            } else {
                $check_dish++;
            }
        }

        if ($check_dish > 0) {
            $new_order['losing_item'] = true;
        } else {
            $new_order['losing_item'] = false;
        }

        //return $check_dish;

        return ResponseWithSuccessData($lang, $new_order, 1);
    }

    public function cancel(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        if (Auth::guard('api')->user()->flag == 0) {
            return RespondWithBadRequest($lang, 5);
        } else {
            $UserType =  CheckUserType();
            $client_id = Auth::guard('api')->user()->id;
            if ($UserType != '') {
                $unknown_user = User::where('flag', $UserType)->first()->id;
                $client_id = ($UserType == 'admin') ? $unknown_user : Auth::guard('api')->user()->id;
            }
            $created_by = Auth::guard('api')->user()->id;
        }

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id', // Optional but must exist in the 'coupons' table
        ]);

        if ($validator->fails()) {
            //return respondErrorData( __('validation.dataNotFound'), 404, __('validation.dataNotFound'));
            //return respondError('Validation Error.', 400, $validator->errors());

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
            //return RespondWithBadRequestAlreadyDeleted();
            return respondErrorData(__('validation.AlreadyDeleted'), 400, __('validation.AlreadyDeleted'));
        }

        $cancel_time = getBranchSettings($order->branch_id, 'time_cancellation');
        $minutesDifference = $order->created_at->diffInMinutes(Carbon::now());
        //return CheckOrderPaid($order->id);
        if (!CheckOrderPaidStatus($order->id)  && $order->status == "pending") {
            //if ($cancel_time < $minutesDifference && !CheckOrderPaid($order->id) && $created_by == $order->created_by) {
            $order->status = 'cancelled';
            $order->print_status = 'cancelled';
            $order->modify_by = $created_by;
            $order->save();
            if ($order->type == 'dine-in') {
                $table = Table::where('id', $order->table_id)->first();
                if ($table) {
                    $table->status = 1;
                    $table->save();
                }
                $notifyData = [
                    'notification_type' => 'table',
                    'description_ar' => 'تم إفراغ طاولة' . $table->table_number . ' بالفرع',
                    'description_en' => 'A table' . $table->table_number . ' was freed in the branch',
                    'title_ar' => 'تم إفراغ طاولة',
                    'title_en' => 'Table Freed',
                    'created_by' => null,
                    'order_id' => $table->id
                ];

                runNotificationToEmployees($table->branch_id, $notifyData, null, $table->id, 'ar');
                // Structure data for broadcasting
                $data = [
                    'id' => $table->id,
                    'name' => $table->name,
                    'name_ar' => $table->name_ar,
                    'name_en' => $table->name_en,
                    'table_number' => $table->table_number,
                    'status' => $table->status,
                    'smoking' => $table->smoking,
                    'floors' => [
                        'id' => $table->floors->id,
                        'name' => $table->floors->name,
                        'name_ar' => $table->floors->name_ar,
                        'name_en' => $table->floors->name_en
                    ],
                    'floor_partitions' => [
                        'id' => $table->floorPartitions->id,
                        'name' => $table->floorPartitions->name,
                        'name_ar' => $table->floorPartitions->name_ar,
                        'name_en' => $table->floorPartitions->name_en
                    ]
                ];

                // Broadcast table update
                broadcast(new TableStatus($data, $table->branch_id, 'update'));
            }

            $order_details = OrderDetail::where('order_id', $request->order_id)->update(['status' => 'cancel']);
            $order_tracking = new OrderTracking();
            $order_tracking->order_id = $request->order_id;
            $order_tracking->order_status = 'cancelled';
            $order_tracking->created_by = Auth::guard('api')->user()->id;
            $order_tracking->time = date('H:i:s');
            $order_tracking->save();
            // $item_calculate = $this->CalculateOrder($order->id);
            // $update_item_calculate = $this->UpdateCalculateOrder($item_calculate);
        } else {
            //return RespondWithBadRequestData($lang, 34);
            return respondErrorData(__('validation.OrderCanNotDeleteAnyMore'), 400, __('validation.OrderCanNotDeleteAnyMore'));
        }
        $data = null;
        return ResponseWithSuccessData($lang, $data, 1);
    }
    public function CalculateInvoiceOrder($order_id, $lang = 'ar')
    {
        $subTotal = 0;
        $itemData = [];
        $Order = Order::find($order_id);
        $IDBranch = $Order->branch_id;

        $coupon_application = getBranchSettings($IDBranch, 'coupon_application');
        $tax_application = getBranchSettings($IDBranch, 'tax_application');
        $tax_percentage = getBranchSettings($IDBranch, 'tax_percentage');
        $service_fees_value = in_array($Order->type, ['dine-in', 'reservation-table']) ? getBranchSettings($IDBranch, 'service_fees') : 0;
        $service_fees_type = in_array($Order->type, ['dine-in', 'reservation-table']) ? getBranchSettings($IDBranch, 'service_fees_type') : 0;
        $delivery_fees = $Order->type == 'Delivery' ? getBranchSettings($IDBranch, 'delivery_fees') : 0;

        $DataOrderDetails = OrderDetail::where('order_id', $order_id)->with(['dishSize'])->get();
        $totalTax = 0;
        $totalService = 0;
        $totalCouponValue = 0;
        $allSubTotalAfterCoupon = 0;

        foreach ($DataOrderDetails as $DataOrderDetail) {
            $Branch_Dish = BranchMenu::where('dish_id', $DataOrderDetail->dish_id)
                ->where('branch_id', $IDBranch)->where('is_active', 1)->first();

            if (!$Branch_Dish) continue;

            $has_size = $Branch_Dish->dish->has_sizes;
            $price = $has_size && $DataOrderDetail->dish_size_id
                ? BranchMenuSize::where('dish_size_id', $DataOrderDetail->dish_size_id)
                ->where('branch_id', $IDBranch)->where('is_active', 1)->value('price')
                : $Branch_Dish->price;

            $baseTotal = $price * $DataOrderDetail['quantity'];
            $addonsTotal = 0;
            $addonDetails = [];

            $OrderAddonsArray = OrderAddon::where('order_details_id', $DataOrderDetail->id)->get();

            foreach ($OrderAddonsArray as $OrderAddonsArr) {
                $addonBranch = BranchMenuAddon::where('dish_addon_id', $OrderAddonsArr->dish_addon_id)
                    ->where('branch_id', $IDBranch)->where('is_active', 1)->first();

                if (!$addonBranch) continue;

                $addonPrice = $addonBranch->price * $OrderAddonsArr->quantity;
                $addonsTotal += $addonPrice;

                $addonSubTotalAfterCoupon = $addonPrice;
                $addonService = $service_fees_type == 'percentage'
                    ? ($addonSubTotalAfterCoupon * $service_fees_value / 100) : 0;
                $addonTax = $tax_application
                    ? CalculateTax($tax_percentage, $addonSubTotalAfterCoupon + $addonService)
                    : ($addonSubTotalAfterCoupon + $addonService) * $tax_percentage / 100;

                $addonDetails[] = [
                    'addon_id' => $OrderAddonsArr->dish_addon_id,
                    'addon_name' => ($lang === 'ar') ? $OrderAddonsArr->Addon->addons->name_ar ?? null : $OrderAddonsArr->Addon->addons->name_en ?? null,
                    'quantity' => $OrderAddonsArr->quantity,
                    'price_before_tax' => $addonPrice,
                    'service_fee' => $addonService,
                    'tax_value' => $addonTax,
                    'total_price' => $addonSubTotalAfterCoupon + $addonService + $addonTax
                ];

                $totalTax += $addonTax;
                $totalService += $addonService;
                $allSubTotalAfterCoupon += $addonSubTotalAfterCoupon;
            }

            $itemTotal = $baseTotal + $addonsTotal;
            $subTotal += $itemTotal;

            // Handle coupons (for base only)
            $couponValue = 0;
            $subTotalAfterCoupon = $baseTotal;
            if ($Order->coupon_id && $coupon_application == 0) {
                $coupon = Coupon::find($Order->coupon_id);
                if ($coupon->type == 'fixed') {
                    $couponValue = $coupon->value / (count($DataOrderDetails) + count($addonDetails));
                    $subTotalAfterCoupon = $baseTotal - $couponValue;
                } else {
                    $couponValue = calcCoupon($baseTotal, $coupon);
                    $subTotalAfterCoupon = applyCoupon($baseTotal, $coupon);
                }
            }

            $serviceFee = ($service_fees_type === "percentage") ? ($subTotalAfterCoupon * $service_fees_value / 100) : 0;
            $taxValue = $tax_application
                ? CalculateTax($tax_percentage, $subTotalAfterCoupon + $serviceFee)
                : ($subTotalAfterCoupon + $serviceFee) * $tax_percentage / 100;

            $totalTax += $taxValue;
            $totalService += $serviceFee;
            $totalCouponValue += $couponValue;
            $allSubTotalAfterCoupon += $subTotalAfterCoupon;

            $itemData[] = [
                'order_detail_id' => $DataOrderDetail->id,
                'dish_id' => $DataOrderDetail->dish_id,
                'dish_name' => $Branch_Dish->dish->name ?? '',
                'quantity' => $DataOrderDetail['quantity'],
                'size' => $DataOrderDetail->dish_size_id ? [
                    'id' => $DataOrderDetail->dish_size_id,
                    'name_ar' => $DataOrderDetail->dishSize->size_name_ar ?? null,
                    'name_en' => $DataOrderDetail->dishSize->size_name_en ?? null
                ] : null,
                'base_total' => $baseTotal,
                'addons_total' => $addonsTotal,
                'sub_total_after_coupon' => $subTotalAfterCoupon,
                'service_fee' => $serviceFee,
                'tax_value' => $taxValue,
                'total_price' => $subTotalAfterCoupon + $serviceFee + $taxValue,
                'addons' => $addonDetails,
                'note' => $DataOrderDetail->note
            ];
        }

        if (in_array($Order->type, ['dine-in', 'reservation-table']) && $service_fees_type === 'fixed') {
            $totalService = $service_fees_value;
        }

        $total = $allSubTotalAfterCoupon + $totalService + $totalTax + $delivery_fees;
        $transactions = OrderTransaction::where('order_id', $order_id)->where('is_refund', 0)->get();
        $transactionData = $transactions->map(function ($transaction) {
            return [
                'payment_status' => $transaction->payment_status,
                'payment_method' => $transaction->payment_method,
                'transaction_id' => $transaction->transaction_id,
                'paid' => $transaction->paid,
                'refund' => $transaction->refund,
                'date' => $transaction->date,
                'is_refund' => $transaction->is_refund,
                'reason' => $transaction->reason,
                'payment_gateway_reference' => $transaction->payment_gateway_reference,
                'payment_gateway_date' => $transaction->payment_gateway_date,
                'payment_gateway_currency' => $transaction->payment_gateway_currency,
                'payment_gateway_status' => $transaction->payment_gateway_status,
                'payment_gateway_method' => $transaction->payment_gateway_method,
            ];
        })->toArray();

        return [
            'invoice_items' => $itemData,
            'invoice_summary' => [
                'subtotal_before_tax' => $subTotal,
                'coupon_value' => $totalCouponValue,
                'service_fees' => $totalService,
                'tax_value' => $totalTax,
                'tax_percentage' => $Order->tax_percentage ?? null,
                'delivery_fees' => $Order->delivery_fees,
                'total_invoice' => $total,
                'service_percentage' => $Order->service_percentage ?? null,
            ],
            'transaction_info' => $transactionData
        ];
    }
    public function CalculateRefundOrder($order_id, $lang = 'ar')
    {
        $subTotal = 0;
        $refundedItems = [];
        $refundedStandaloneAddons = [];

        $addonOnlySubtotal = 0;
        $addonOnlyService = 0;
        $addonOnlyTax = 0;

        $Order = Order::find($order_id);
        $IDBranch = $Order->branch_id;

        $coupon_application = getBranchSettings($IDBranch, 'coupon_application');
        $tax_application = getBranchSettings($IDBranch, 'tax_application');
        $tax_percentage = getBranchSettings($IDBranch, 'tax_percentage');
        $service_fees_value = in_array($Order->type, ['dine-in', 'reservation-table']) ? getBranchSettings($IDBranch, 'service_fees') : 0;
        $service_fees_type = in_array($Order->type, ['dine-in', 'reservation-table']) ? getBranchSettings($IDBranch, 'service_fees_type') : 0;
        $delivery_fees = $Order->type == 'Delivery' ? getBranchSettings($IDBranch, 'delivery_fees') : 0;

        $DataOrderDetails = OrderDetail::where('order_id', $order_id)
            ->where('status', 'cancel')
            ->with(['dishSize'])
            ->get();

        $totalTax = 0;
        $totalService = 0;
        $totalCouponValue = 0;
        $allSubTotalAfterCoupon = 0;

        foreach ($DataOrderDetails as $DataOrderDetail) {
            $Branch_Dish = BranchMenu::where('dish_id', $DataOrderDetail->dish_id)
                ->where('branch_id', $IDBranch)->where('is_active', 1)->first();

            if (!$Branch_Dish) continue;

            $has_size = $Branch_Dish->dish->has_sizes;
            $price = $has_size && $DataOrderDetail->dish_size_id
                ? BranchMenuSize::where('dish_size_id', $DataOrderDetail->dish_size_id)
                ->where('branch_id', $IDBranch)->where('is_active', 1)->value('price')
                : $Branch_Dish->price;

            $baseTotal = $price * $DataOrderDetail['quantity'];
            $addonsTotal = 0;
            $addonDetails = [];

            $OrderAddonsArray = OrderAddon::where('order_details_id', $DataOrderDetail->id)
                ->where('status', 'cancel')->get();

            foreach ($OrderAddonsArray as $addonItem) {
                $addon = BranchMenuAddon::where('dish_addon_id', $addonItem->dish_addon_id)
                    ->where('branch_id', $IDBranch)->where('is_active', 1)->first();

                if (!$addon) continue;

                $addonPrice = $addon->price * $addonItem->quantity;
                $addonsTotal += $addonPrice;

                $addonSubTotalAfterCoupon = $addonPrice;
                $addonService = $service_fees_type == 'percentage'
                    ? ($addonSubTotalAfterCoupon * $service_fees_value / 100) : 0;
                $addonTax = $tax_application
                    ? CalculateTax($tax_percentage, $addonSubTotalAfterCoupon + $addonService)
                    : ($addonSubTotalAfterCoupon + $addonService) * $tax_percentage / 100;

                $addonDetails[] = [
                    'addon_id' => $addonItem->dish_addon_id,
                    'addon_name' => ($lang === 'ar') ? $addonItem->Addon->addons->name_ar ?? null : $addonItem->Addon->addons->name_en ?? null,
                    'quantity' => $addonItem->quantity,
                    'price_before_tax' => $addonPrice,
                    'service_fee' => $addonService,
                    'tax_value' => $addonTax,
                    'total_refund' => $addonSubTotalAfterCoupon + $addonService + $addonTax
                ];

                $totalTax += $addonTax;
                $totalService += $addonService;
                $allSubTotalAfterCoupon += $addonSubTotalAfterCoupon;
            }

            $itemTotal = $baseTotal + $addonsTotal;
            $subTotal += $itemTotal;

            $couponValue = 0;
            // $subTotalAfterCoupon = $baseTotal;




            if ($Order->coupon_id && $coupon_application == 0) {
                $coupon = Coupon::find($Order->coupon_id);
                if ($coupon->type == 'fixed') {
                    $couponValue = $coupon->value / (count($DataOrderDetails) + count($OrderAddonsArray));
                    $subTotalAfterCoupon = $baseTotal - $couponValue;
                } else {
                    $couponValue = calcCoupon($baseTotal, $coupon);
                    $subTotalAfterCoupon = applyCoupon($baseTotal, $coupon);
                }
            } else if ($DataOrderDetail->coupon_id) {
                $coupon = Coupon::find($DataOrderDetail->coupon_id);
                if ($coupon->type == 'fixed') {
                    $couponValue = $coupon->value;
                    $subTotalAfterCoupon = $baseTotal - $couponValue;
                } else {
                    $couponValue = calcCoupon($baseTotal, $coupon);
                    $subTotalAfterCoupon = applyCoupon($baseTotal, $coupon);
                }
            } else {


                $subTotalAfterCoupon = $baseTotal;
            }





            $serviceFee = ($service_fees_type === "percentage") ? ($subTotalAfterCoupon * $service_fees_value / 100) : 0;
            $taxValue = $tax_application
                ? CalculateTax($tax_percentage, $subTotalAfterCoupon + $serviceFee)
                : ($subTotalAfterCoupon + $serviceFee) * $tax_percentage / 100;

            $totalTax += $taxValue;
            $totalService += $serviceFee;
            $totalCouponValue += $couponValue;
            $allSubTotalAfterCoupon += $subTotalAfterCoupon;

            $refundedItems[] = [
                'order_detail_id' => $DataOrderDetail->id,
                'dish_id' => $DataOrderDetail->dish_id,
                'coupon_value' => $DataOrderDetail->coupon_value,
                'dish_name' => $Branch_Dish->dish->name ?? '',
                'quantity' => $DataOrderDetail['quantity'],
                'size' => $DataOrderDetail->dish_size_id ? [
                    'id' => $DataOrderDetail->dish_size_id,
                    'name_ar' => $DataOrderDetail->dishSize->size_name_ar ?? null,
                    'name_en' => $DataOrderDetail->dishSize->size_name_en ?? null
                ] : null,
                'base_total' => $baseTotal,
                'addons_total' => $addonsTotal,
                'sub_total_after_coupon' => $subTotalAfterCoupon,
                'service_fee' => $serviceFee,
                'tax_value' => $taxValue,
                'total_refund' => $subTotalAfterCoupon + $serviceFee + $taxValue,
                'addons' => $addonDetails,
                'note' => $DataOrderDetail->note
            ];
        }

        // Refunded Addons with non-canceled items
        $refundedAddons = OrderAddon::whereHas('orderDetail', function ($query) use ($order_id) {
            $query->where('order_id', $order_id)->where('status', '!=', 'cancel');
        })
            ->where('status', 'cancel')
            ->get();

        foreach ($refundedAddons as $addonItem) {
            $orderDetail = $addonItem->orderDetail;

            $Branch_Dish = BranchMenu::where('dish_id', $orderDetail->dish_id)
                ->where('branch_id', $IDBranch)->where('is_active', 1)->first();

            if (!$Branch_Dish) continue;

            $addon = BranchMenuAddon::where('dish_addon_id', $addonItem->dish_addon_id)
                ->where('branch_id', $IDBranch)->where('is_active', 1)->first();

            if (!$addon) continue;

            $addonPrice = $addon->price * $addonItem->quantity;
            $addonSubTotalAfterCoupon = $addonPrice;

            $addonService = $service_fees_type == 'percentage'
                ? ($addonSubTotalAfterCoupon * $service_fees_value / 100)
                : 0;

            $addonTax = $tax_application
                ? CalculateTax($tax_percentage, $addonSubTotalAfterCoupon + $addonService)
                : ($addonSubTotalAfterCoupon + $addonService) * $tax_percentage / 100;

            // Totals for summary of refunded_addons_only
            $addonOnlySubtotal += $addonSubTotalAfterCoupon;
            $addonOnlyService += $addonService;
            $addonOnlyTax += $addonTax;

            // Totals for global summary
            $totalTax += $addonTax;
            $totalService += $addonService;
            $allSubTotalAfterCoupon += $addonSubTotalAfterCoupon;

            $refundedStandaloneAddons[] = [
                'addon_id' => $addonItem->dish_addon_id,
                'addon_name' => ($lang === 'ar') ? $addonItem->Addon->addons->name_ar ?? null : $addonItem->Addon->addons->name_en ?? null,
                'quantity' => $addonItem->quantity,
                'price_before_tax' => $addonPrice,
                'service_fee' => $addonService,
                'tax_value' => $addonTax,
                'total_refund' => $addonSubTotalAfterCoupon + $addonService + $addonTax,
                'linked_dish_id' => $orderDetail->dish_id,
                'linked_dish_name' => $Branch_Dish->dish->name ?? '',
                'note' => $orderDetail->note
            ];
        }

        if (in_array($Order->type, ['dine-in', 'reservation-table']) && $service_fees_type === 'fixed') {
            $totalService = $service_fees_value;
        }

        $totalRefund = $allSubTotalAfterCoupon + $totalService + $totalTax + $delivery_fees;

        $transactions = OrderTransaction::where('order_id', $order_id)->where('is_refund', 1)->get();
        $transactionData = $transactions->map(function ($transaction) {
            return [
                'payment_status' => $transaction->payment_status,
                'payment_method' => $transaction->payment_method,
                'transaction_id' => $transaction->transaction_id,
                'paid' => $transaction->paid,
                'refund' => $transaction->refund,
                'date' => $transaction->date,
                'is_refund' => $transaction->is_refund,
                'reason' => $transaction->reason,
                'payment_gateway_reference' => $transaction->payment_gateway_reference,
                'payment_gateway_date' => $transaction->payment_gateway_date,
                'payment_gateway_currency' => $transaction->payment_gateway_currency,
                'payment_gateway_status' => $transaction->payment_gateway_status,
                'payment_gateway_method' => $transaction->payment_gateway_method,
            ];
        })->toArray();

        return [
            'refunded_items' => $refundedItems,
            'refunded_addons_only' => [
                'addons' => $refundedStandaloneAddons,
                'summary' => [
                    'subtotal_before_tax' => $addonOnlySubtotal,
                    'service_fees' => $addonOnlyService,
                    'tax_value' => $addonOnlyTax,
                    'total_refund' => $addonOnlySubtotal + $addonOnlyService + $addonOnlyTax
                ]
            ],
            'refund_summary' => [
                'subtotal_before_tax' => $subTotal,
                'coupon_value' => $totalCouponValue,
                'service_fees' => $totalService,
                'tax_value' => $totalTax,
                'tax_percentage' => $Order->tax_percentage ?? null,
                'delivery_fees' => $delivery_fees,
                'total_refund' => $totalRefund,
                'service_percentage' => $Order->service_percentage ?? null,
            ],
            'transaction_info' => $transactionData
        ];
    }




    public function CalculateOrderWithStatus($order_id, $item_status)
    {
        $Order = Order::find($order_id);
        $IDBranch = $Order->branch_id;
        $coupon_application = getBranchSettings($IDBranch, 'coupon_application');

        $tax_application = getBranchSettings($IDBranch, 'tax_application');
        $tax_percentage = getBranchSettings($IDBranch, 'tax_percentage');
        $service_fees_value =  ($Order->type  != 'dine-in' && $Order->type != "reservation-table") ? 0 : getBranchSettings($IDBranch, 'service_fees');
        $service_fees_type =  ($Order->type  != 'dine-in' && $Order->type != "reservation-table") ? 0 : getBranchSettings($IDBranch, 'service_fees_type');
        $delivery_fees = ($Order->type == 'Delivery') ?  getBranchSettings($IDBranch, 'delivery_fees') : 0;

        $DataOrderDetails = OrderDetail::where('order_id', $order_id)->whereIn('status', $item_status)->get();
        $total_price_after_tax = 0;
        $total_price_before_tax = 0;
        $tax_value_total = 0;
        $service_value_total = 0;
        if (sizeof($DataOrderDetails)) {
            // $branch_dish_size = 0;
            foreach ($DataOrderDetails as $x => $DataOrderDetail) {
                $total = 0;
                $Branch_Dish = BranchMenu::where('dish_id', $DataOrderDetail->dish_id)->where('branch_id', $IDBranch)->first();

                if ($Branch_Dish) {
                    $has_size = $Branch_Dish->dish->has_sizes;
                    if ($has_size && $DataOrderDetail->dish_size_id) {
                        $size_id = $DataOrderDetail->dish_size_id;
                        $branch_dish_size = BranchMenuSize::where('dish_size_id', $size_id)->where('branch_id', $IDBranch)->first();
                        $total =  $branch_dish_size->price;
                    } else {

                        $total =  $Branch_Dish->price;
                    }
                }

                if ($Order->type  == 'dine-in' || $Order->type == "reservation-table") {
                    $price_before_tax = $tax_application == 1 ? applyTax($total * $DataOrderDetail['quantity'], $tax_percentage, $tax_application) : $total * $DataOrderDetail['quantity'];
                    $service_value =  $service_fees_type != 'fixed' ? ($price_before_tax * ($service_fees_value / 100)) : ($service_fees_value / count($DataOrderDetails));
                    $priceAfterService =  $price_before_tax + $service_value;
                    $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage,  $priceAfterService) : $priceAfterService * ($tax_percentage / 100);
                    $price_after_tax = $priceAfterService + $tax_value;
                } else {
                    $service_value =  0;
                    $price_before_tax = $tax_application == 1 ? applyTax($total * $DataOrderDetail['quantity'], $tax_percentage, $tax_application) : $total * $DataOrderDetail['quantity'];
                    $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage, $total * $DataOrderDetail['quantity']) : $price_before_tax * ($tax_percentage / 100);
                    $price_after_tax = $price_before_tax + $tax_value;
                }
                // $price_before_tax = $tax_application == 1 ? applyTax($total * $DataOrderDetail['quantity'], $tax_percentage, $tax_application) : $total * $DataOrderDetail['quantity'];
                // $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage, $total * $DataOrderDetail['quantity']) : $price_before_tax * ($tax_percentage / 100);
                // $price_after_tax = $price_before_tax + $tax_value;

                $total_price_after_tax += $price_after_tax;
                $total_price_before_tax += $price_before_tax;
                $tax_value_total += $tax_value;
                if ($Order->type  == 'dine-in' || $Order->type == "reservation-table") {
                    $service_value_total += $service_value;
                }
                $OrderAddonsArray = OrderAddon::where('order_details_id', $DataOrderDetail->id)->where('status', $item_status)->get();
                if (!empty($OrderAddonsArray)) {
                    foreach ($OrderAddonsArray as $OrderAddonsArr) {
                        if ($OrderAddonsArr) {
                            $addon_id = $OrderAddonsArr->Addon->id;
                            $addon = BranchMenuAddon::where('dish_addon_id', $addon_id)->where('branch_id', $IDBranch)->first();

                            $price = $addon->price;
                            if ($Order->type == 'dine-in' || $Order->type == "reservation-table") {
                                $price_before_tax = $tax_application == 1 ? applyTax($price * $DataOrderDetail['quantity'], $tax_percentage, $tax_application) : $price * $DataOrderDetail['quantity'];
                                $service_value =  $service_fees_type != 'fixed' ? ($price_before_tax * ($service_fees_value / 100)) : 0;
                                $priceAfterService =  $price_before_tax +  $service_value;
                                $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage,  $priceAfterService) : $priceAfterService * ($tax_percentage / 100);
                                $price_after_tax = $priceAfterService + $tax_value;
                            } else {
                                $service_value =  0;
                                $price_before_tax = $tax_application == 1 ? applyTax($price * $DataOrderDetail['quantity'], $tax_percentage, $tax_application) : $price * $DataOrderDetail['quantity'];
                                $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage, $price * $DataOrderDetail['quantity']) : $price_before_tax * ($tax_percentage / 100);
                                $price_after_tax = $price_before_tax + $tax_value;
                            }
                            // $price_before_tax = $tax_application == 1 ? applyTax($price * $DataOrderDetail->quantity, $tax_percentage, $tax_application) : $price * $DataOrderDetail->quantity;
                            // $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage, $price * $DataOrderDetail->quantity) : $price_before_tax * ($tax_percentage / 100);
                            // $price_after_tax = $price_before_tax + $tax_value;


                            $total_price_after_tax += $price_after_tax;
                            $total_price_before_tax += $price_before_tax;
                            $tax_value_total += $tax_value;
                            if ($Order->type == 'dine-in' || $Order->type == "reservation-table") {
                                $service_value_total += $service_value;
                            }
                        } else {
                            return CustomRespondWithBadRequest(__('order.address_not_found'));
                        }
                    }
                }
            }
        }


        // $service_fees = 0;
        // if ($service_fees_type) {
        //     if ($service_fees_type == 'fixed') {
        //         $service_fees = $service_fees_value;
        //     } else {
        //         // if ($tax_application == 1) {

        //         //     $service_fees = ($tax_value + $total_price_before_tax) * ($service_fees_value / 100);
        //         // } else {
        //         $service_fees =  $total_price_before_tax * ($service_fees_value / 100);
        //         // }
        //     }
        // }

        $tax_value = $tax_value_total;
        $total_items = $total_price_before_tax;
        $coupon_value = 0;

        //total here is included tax as setting applied so we need get total before tax
        // if ($tax_application == 1) { // because total_price_before_tax is without tax value

        //     $total_items = $total_price_before_tax - $tax_value_total;  // applyTax($total_items, $tax_percentage, $tax_application);  not used
        //     // $tax_value = CalculateTax($tax_percentage, $total_items); not used
        // }

        $coupon = Coupon::find($Order->coupon_id);
        if ($Order->coupon_id && $coupon_application == 0) {
            $coupon_value = calcCoupon($total_items, $coupon);
            $total_items = applyCoupon($total_items, $coupon);
        } else if ($Order->coupon_id && $coupon_application == 1) { // apply coupon after tax
            // $coupon_value = calcCoupon($total_price_after_tax, $coupon);
            // $total_items = applyCoupon($total_price_after_tax, $coupon);
        }



        // $tax_value = $tax_value_total;
        // $total_items = $total_price_before_tax;
        $response = [
            'order_id' => $Order->id,
            'totalBeforeTax' => $total_price_before_tax,
            'totalAfterTax' =>  $total_price_before_tax - $coupon_value + $service_value_total + $delivery_fees + $tax_value,
            'tax_value' => $tax_value,
            'coupon_value' => $coupon_value,
            'tax_percentage' => "{$tax_percentage}%",
            'service_fees' => $service_value_total,
            'delivery_fees' => $delivery_fees
        ];
        return $response;
    }

    public function UpdateCalculateOrder($items)
    {
        $DataOrder = Order::find($items['order_id']);
        $DataOrder->total_price_befor_tax = $items['totalBeforeTax'];
        $DataOrder->total_price_after_tax = $items['totalAfterTax'];
        $DataOrder->tax_value = $items['tax_value'];
        $DataOrder->coupon_value = $items['coupon_value'];
        $DataOrder->service_fees = $items['service_fees'];
        $DataOrder->delivery_fees = $items['delivery_fees'];
        $DataOrder->save();
        return true;
    }

    public function CalculateItem($item_id, $quantity)
    {
        $DataOrderDetail = OrderDetail::find($item_id);
        $Order = $DataOrderDetail->order;
        $order_id = $DataOrderDetail->order_id;
        $IDBranch = $Order->branch_id;
        $count_order_items = $Order->orderDetails->count();
        $coupon_application = getBranchSettings($IDBranch, 'coupon_application');
        $tax_application = getBranchSettings($IDBranch, 'tax_application');
        $tax_percentage = getBranchSettings($IDBranch, 'tax_percentage');
        $service_fees_value =  ($Order->type  != 'dine-in' && $Order->type != "reservation-table") ? 0 : getBranchSettings($IDBranch, 'service_fees');
        $service_fees_type =  ($Order->type  != 'dine-in' && $Order->type != "reservation-table") ? 0 : getBranchSettings($IDBranch, 'service_fees_type');
        $delivery_fees = ($Order->type == 'Delivery') ?  getBranchSettings($IDBranch, 'delivery_fees') : 0;
        // $DataOrderDetails = OrderDetail::where('order_id', $order_id)->get();
        $total_price_after_tax = 0;
        $total_price_before_tax = 0;
        $tax_value_total = 0;
        $service_value_total = 0;
        // $branch_dish_size = 0;
        $total = 0;
        $couponValue = 0;
        $Branch_Dish = BranchMenu::where('dish_id', $DataOrderDetail->dish_id)->where('branch_id', $IDBranch)->first();
        if ($Branch_Dish) {
            $has_size = $Branch_Dish->dish->has_sizes;
            if ($has_size && $DataOrderDetail->dish_size_id) {
                $size_id = $DataOrderDetail->dish_size_id;
                $branch_dish_size = BranchMenuSize::where('dish_size_id', $size_id)->where('branch_id', $IDBranch)->first();
                $total =  $branch_dish_size->price;
            } else {
                $total =  $Branch_Dish->price;
            }
        }

        $fullOrderData = Order::with([
            'orderDetails',
            'orderAddons'
        ])->find($order_id);

        $transformedDishes = [];
        $transformedDishes = $fullOrderData->orderDetails->map(function ($item) {
            return [
                'dish_id' => $item->dish_id,
                'quantity' => $item->quantity,
                'addons' => $item->dishAddons->pluck('dish_addon_id')->toArray(),
                'status' => $item->in_request_return,
                'sizeId' => $item->sizeId ?? null,

            ];
        });
        $totalDishPrice = $total * $quantity;
        if ($DataOrderDetail->coupon_id) {
            $coupon = Coupon::find($DataOrderDetail->coupon_id);
            // if ($coupon->type == 'fixed') {
            //     $couponValue = $coupon->value;
            //     $subTotalAfterCoupon = $totalDishPrice - $couponValue;
            // } else {
            $couponValue = calcCoupon($totalDishPrice, $coupon);
            $subTotalAfterCoupon = applyCoupon($totalDishPrice, $coupon);
            // }
        } else if ($Order->coupon_id) {
            $coupon = Coupon::find($Order->coupon_id);
            if ($coupon->type == 'fixed') {
                // $couponValue = $coupon->value;
                $result =   $this->calculateCouponValue($transformedDishes, $IDBranch,  $coupon->value, 'web');
                $subTotalAfterCoupon = $result
                    ->first(fn($itemAddon) => $itemAddon['dish_id'] == $DataOrderDetail->dish_id && $itemAddon['type'] == 'dish' && $itemAddon['status'] && $itemAddon['has_addon'])['priceAfter'] ?? 0;
                $couponValue = $result
                    ->first(fn($itemCoupon) => $itemCoupon['dish_id'] ==  $DataOrderDetail->dish_id && $itemCoupon['type'] == 'dish' && $itemCoupon['status'] && $itemCoupon['has_addon'])['couponValue'] ?? 0;

                // dd(0);
            } else {
                $couponValue = calcCoupon($totalDishPrice, $coupon);

                $subTotalAfterCoupon = applyCoupon($totalDishPrice, $coupon);
            }
        } else {
            $subTotalAfterCoupon = $totalDishPrice;
        }


        if ($Order->type  == 'dine-in' || $Order->type == "reservation-table") {
            $price_before_tax = $tax_application == 1 ? applyTax($subTotalAfterCoupon, $tax_percentage, $tax_application) : $subTotalAfterCoupon;
            $service_value =  $service_fees_type != 'fixed' ? ($price_before_tax * ($service_fees_value / 100)) : ($service_fees_value / $count_order_items);
            $priceAfterService =  $price_before_tax + $service_value;
            $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage,  $priceAfterService) : $priceAfterService * ($tax_percentage / 100);
            $price_after_tax = $priceAfterService + $tax_value;
        } else {
            $service_value =  0;
            $price_before_tax = $tax_application == 1 ? applyTax($subTotalAfterCoupon, $tax_percentage, $tax_application) : $subTotalAfterCoupon;
            $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage, $subTotalAfterCoupon) : $price_before_tax * ($tax_percentage / 100);
            $price_after_tax = $price_before_tax + $tax_value;
        }
        $dish_service_value = $service_value;
        $dish_price_before_tax = $price_before_tax;
        $dish_tax_value = $tax_value;
        $dish_price_after_tax = $price_after_tax;
        $dish_coupon_value = $couponValue;
        $OrderAddonsArray = OrderAddon::where('order_details_id', $DataOrderDetail->id)->where('status', '!=', 'cancel')->get();
        $addons = [];
        $couponValueTotal = 0;
        $couponValueAddon = 0;
        $addon_price_before_tax = 0;
        $addon_tax_value = 0;
        $addon_price_after_tax = 0;
        if (!empty($OrderAddonsArray)) {
            foreach ($OrderAddonsArray as $OrderAddonsArr) {
                if ($OrderAddonsArr) {
                    $addon_id = $OrderAddonsArr->Addon->id;
                    $addon = BranchMenuAddon::where('dish_addon_id', $addon_id)->where('branch_id', $IDBranch)->first();
                    $price = $addon->price;
                    $priceFullQuantity = $price * $quantity;
                    if ($DataOrderDetail->coupon_id) {
                        $coupon = Coupon::find($DataOrderDetail->coupon_id);
                        // if ($coupon->type == 'fixed') {
                        //     $couponValue = $coupon->value;
                        //     $subTotalAfterCoupon =   $priceFullQuantity - $couponValue;
                        // } else {
                        $couponValueAddon = calcCoupon($priceFullQuantity, $coupon);
                        $subTotalAfterCoupon = applyCoupon($priceFullQuantity, $coupon);
                        // }
                    } else if ($Order->coupon_id) {
                        $coupon = Coupon::find($Order->coupon_id);
                        if ($coupon->type == 'fixed') {
                            // $couponValue = $coupon->value;
                            $result =   $this->calculateCouponValue($transformedDishes, $IDBranch,  $coupon->value, 'web');

                            $subTotalAfterCoupon = $result
                                ->first(fn($itemAddon) => $itemAddon['dish_id'] == $addon_id && $itemAddon['type'] == 'addon' && $itemAddon['status'])['priceAfter'] ?? 0;

                            $couponValueAddon = $result
                                ->first(fn($itemCoupon) => $itemCoupon['dish_id'] == $addon_id && $itemCoupon['type'] == 'addon' && $itemCoupon['status'])['couponValue'] ?? 0;
                        } else {
                            $couponValueAddon = calcCoupon($priceFullQuantity, $coupon);
                            $subTotalAfterCoupon = applyCoupon($priceFullQuantity, $coupon);
                        }
                    } else {
                        $subTotalAfterCoupon = $priceFullQuantity;
                    }
                    $couponValueTotal += $couponValueAddon;

                    if ($Order->type == 'dine-in' || $Order->type == "reservation-table") {
                        $price_before_tax = $tax_application == 1 ? applyTax($subTotalAfterCoupon, $tax_percentage, $tax_application) : $subTotalAfterCoupon;
                        $service_value =  $service_fees_type != 'fixed' ? ($price_before_tax * ($service_fees_value / 100)) : 0;
                        $priceAfterService =  $price_before_tax +  $service_value;
                        $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage,  $priceAfterService) : $priceAfterService * ($tax_percentage / 100);
                        $price_after_tax = $priceAfterService + $tax_value;
                    } else {
                        $service_value = 0;
                        $price_before_tax = $tax_application == 1 ? applyTax($subTotalAfterCoupon, $tax_percentage, $tax_application) : $subTotalAfterCoupon;
                        $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage, $subTotalAfterCoupon) : $price_before_tax * ($tax_percentage / 100);
                        $price_after_tax = $price_before_tax + $tax_value;
                    }

                    $addons[] = [
                        'addon_order_id' => $OrderAddonsArr->id,
                        'addon_price_before_tax' => $price_before_tax,
                        'addon_price_before_coupon' => $priceFullQuantity,

                        'addon_tax_value' => $tax_value,
                        'addon_price_after_tax' => $price_after_tax,
                        'addon_service_value' => $service_value,
                        'addon_quantity' => $quantity,
                        'branch_addon_id' => $addon->id
                    ];
                } else {
                    return CustomRespondWithBadRequest(__('order.address_not_found'));
                }
            }
        }

        $total_addons_price = 0;

        // Sum the addon prices
        if (count($addons) > 0) {

            foreach ($addons as $addon) {
                $total_addons_price += $addon['addon_price_after_tax'];
            }
        }

        // Calculate the total price
        $total_price = $total_addons_price + $dish_price_after_tax;


        // Prepare the final response
        $response = [
            'item_id' => $item_id,
            'dish_price_before_tax' => $dish_price_before_tax,
            'dish_price_before_coupon' => $totalDishPrice,
            'dish_quantity' => $quantity,
            'dish_tax_value' => $dish_tax_value,
            'dish_price_after_tax' => $dish_price_after_tax,
            'dish_service_value' => $dish_service_value,
            'dish_coupon_value' => $dish_coupon_value + $couponValueTotal,
            'addons' => $addons,
            'total_price' => $total_price
        ];

        return $response;
    }
    public function CalculateItemForRefund($item_id, $quantity)
    {
        $DataOrderDetail = OrderDetail::find($item_id);
        $Order = $DataOrderDetail->order;
        $order_id = $DataOrderDetail->order_id;
        $IDBranch = $Order->branch_id;
        $count_order_items = $Order->orderDetails->count();
        $coupon_application = getBranchSettings($IDBranch, 'coupon_application');
        $tax_application = getBranchSettings($IDBranch, 'tax_application');
        $tax_percentage = getBranchSettings($IDBranch, 'tax_percentage');
        $service_fees_value =  ($Order->type  != 'dine-in' && $Order->type != "reservation-table") ? 0 : getBranchSettings($IDBranch, 'service_fees');
        $service_fees_type =  ($Order->type  != 'dine-in' && $Order->type != "reservation-table") ? 0 : getBranchSettings($IDBranch, 'service_fees_type');
        $delivery_fees = ($Order->type == 'Delivery') ?  getBranchSettings($IDBranch, 'delivery_fees') : 0;
        // $DataOrderDetails = OrderDetail::where('order_id', $order_id)->get();
        $total_price_after_tax = 0;
        $total_price_before_tax = 0;
        $tax_value_total = 0;
        $service_value_total = 0;
        // $branch_dish_size = 0;
        $total = 0;
        $couponValue = 0;
        $Branch_Dish = BranchMenu::where('dish_id', $DataOrderDetail->dish_id)->where('branch_id', $IDBranch)->first();
        if ($Branch_Dish) {
            $has_size = $Branch_Dish->dish->has_sizes;
            if ($has_size && $DataOrderDetail->dish_size_id) {
                $size_id = $DataOrderDetail->dish_size_id;
                $branch_dish_size = BranchMenuSize::where('dish_size_id', $size_id)->where('branch_id', $IDBranch)->first();
                $total =  $branch_dish_size->price;
            } else {
                $total =  $Branch_Dish->price;
            }
        }

        $fullOrderData = Order::with([
            'orderDetails',
            'orderAddons'
        ])->find($order_id);

        $transformedDishes = [];
        $transformedDishes = $fullOrderData->orderDetails->map(function ($item) {
            return [
                'dish_id' => $item->dish_id,
                'quantity' => $item->quantity,
                'addons' => $item->dishAddons->pluck('dish_addon_id')->toArray(),
                'status' => $item->in_request_return,
                'sizeId' => $item->sizeId ?? null,


            ];
        });
        // dd($transformedDishes);
        $totalDishPrice = $total * $quantity;
        if ($DataOrderDetail->coupon_id) {
            $coupon = Coupon::find($DataOrderDetail->coupon_id);
            // if ($coupon->type == 'fixed') {
            //     $couponValue = $coupon->value;
            //     $subTotalAfterCoupon = $totalDishPrice - $couponValue;
            // } else {
            $couponValue = calcCoupon($totalDishPrice, $coupon);
            $subTotalAfterCoupon = applyCoupon($totalDishPrice, $coupon);
            // }
        } else if ($Order->coupon_id) {
            $coupon = Coupon::find($Order->coupon_id);
            if ($coupon->type == 'fixed') { //need to change also
                // $couponValue = $coupon->value;
                $result =   $this->calculateCouponValue($transformedDishes, $IDBranch,  $coupon->value, 'web');

                $subTotalAfterCoupon = $result
                    ->first(fn($itemAddon) => $itemAddon['dish_id'] == $DataOrderDetail->dish_id && $itemAddon['type'] == 'dish' && $itemAddon['status'] && $itemAddon['has_addon'])['priceAfter'] ?? 0;
                $couponValue = $result
                    ->first(fn($itemCoupon) => $itemCoupon['dish_id'] ==  $DataOrderDetail->dish_id && $itemCoupon['type'] == 'dish' && $itemCoupon['status'] && $itemCoupon['has_addon'])['couponValue'] ?? 0;
            } else {
                $couponValue = calcCoupon($totalDishPrice, $coupon);

                $subTotalAfterCoupon = applyCoupon($totalDishPrice, $coupon);
            }
        } else {
            $subTotalAfterCoupon = $totalDishPrice;
        }


        if ($Order->type  == 'dine-in' || $Order->type == "reservation-table") {
            $price_before_tax = $tax_application == 1 ? applyTax($subTotalAfterCoupon, $tax_percentage, $tax_application) : $subTotalAfterCoupon;
            $service_value =  $service_fees_type != 'fixed' ? ($price_before_tax * ($service_fees_value / 100)) : ($service_fees_value / $count_order_items);
            $priceAfterService =  $price_before_tax + $service_value;
            $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage,  $priceAfterService) : $priceAfterService * ($tax_percentage / 100);
            $price_after_tax = $priceAfterService + $tax_value;
        } else {
            $service_value =  0;
            $price_before_tax = $tax_application == 1 ? applyTax($subTotalAfterCoupon, $tax_percentage, $tax_application) : $subTotalAfterCoupon;
            $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage, $subTotalAfterCoupon) : $price_before_tax * ($tax_percentage / 100);
            $price_after_tax = $price_before_tax + $tax_value;
        }
        $dish_service_value = $service_value;
        $dish_price_before_tax = $price_before_tax;
        $dish_tax_value = $tax_value;
        $dish_price_after_tax = $price_after_tax;
        $dish_coupon_value = $couponValue;
        $OrderAddonsArray = OrderAddon::where('order_details_id', $DataOrderDetail->id)->get();
        $addons = [];
        $couponValueTotal = 0;
        $couponValueAddon = 0;
        $addon_price_before_tax = 0;
        $addon_tax_value = 0;
        $addon_price_after_tax = 0;
        if (!empty($OrderAddonsArray)) {
            foreach ($OrderAddonsArray as $OrderAddonsArr) {
                if ($OrderAddonsArr) {
                    $addon_id = $OrderAddonsArr->Addon->id;
                    $addon = BranchMenuAddon::where('dish_addon_id', $addon_id)->where('branch_id', $IDBranch)->first();
                    $price = $addon->price;
                    $priceFullQuantity = $price * $quantity;
                    if ($DataOrderDetail->coupon_id) {
                        $coupon = Coupon::find($DataOrderDetail->coupon_id);
                        // if ($coupon->type == 'fixed') {
                        //     $couponValue = $coupon->value;
                        //     $subTotalAfterCoupon =   $priceFullQuantity - $couponValue;
                        // } else {
                        $couponValueAddon = calcCoupon($priceFullQuantity, $coupon);
                        $subTotalAfterCoupon = applyCoupon($priceFullQuantity, $coupon);
                        // }
                    } else if ($Order->coupon_id) {
                        $coupon = Coupon::find($Order->coupon_id);
                        if ($coupon->type == 'fixed') {
                            // $couponValue = $coupon->value;
                            $result =   $this->calculateCouponValue($transformedDishes, $IDBranch,  $coupon->value, 'web');

                            $subTotalAfterCoupon = $result
                                ->first(fn($itemAddon) => $itemAddon['dish_id'] == $addon_id && $itemAddon['type'] == 'addon' && $itemAddon['status'])['priceAfter'] ?? 0;
                            $couponValueAddon = $result
                                ->first(fn($itemCoupon) => $itemCoupon['dish_id'] == $addon_id && $itemCoupon['type'] == 'addon' && $itemCoupon['status'])['couponValue'] ?? 0;
                        } else {
                            $couponValueAddon = calcCoupon($priceFullQuantity, $coupon);
                            $subTotalAfterCoupon = applyCoupon($priceFullQuantity, $coupon);
                        }
                    } else {
                        $subTotalAfterCoupon = $priceFullQuantity;
                    }
                    $couponValueTotal += $couponValueAddon;

                    if ($Order->type == 'dine-in' || $Order->type == "reservation-table") {
                        $price_before_tax = $tax_application == 1 ? applyTax($subTotalAfterCoupon, $tax_percentage, $tax_application) : $subTotalAfterCoupon;
                        $service_value =  $service_fees_type != 'fixed' ? ($price_before_tax * ($service_fees_value / 100)) : 0;
                        $priceAfterService =  $price_before_tax +  $service_value;
                        $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage,  $priceAfterService) : $priceAfterService * ($tax_percentage / 100);
                        $price_after_tax = $priceAfterService + $tax_value;
                    } else {
                        $service_value = 0;
                        $price_before_tax = $tax_application == 1 ? applyTax($subTotalAfterCoupon, $tax_percentage, $tax_application) : $subTotalAfterCoupon;
                        $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage, $subTotalAfterCoupon) : $price_before_tax * ($tax_percentage / 100);
                        $price_after_tax = $price_before_tax + $tax_value;
                    }

                    $addons[] = [
                        'addon_order_id' => $OrderAddonsArr->id,
                        'addon_price_before_tax' => $price_before_tax,
                        'addon_price_before_coupon' => $priceFullQuantity,

                        'addon_tax_value' => $tax_value,
                        'addon_price_after_tax' => $price_after_tax,
                        'addon_service_value' => $service_value,
                        'addon_quantity' => $quantity,
                        'branch_addon_id' => $addon->id
                    ];
                } else {
                    return CustomRespondWithBadRequest(__('order.address_not_found'));
                }
            }
        }

        $total_addons_price = 0;

        // Sum the addon prices
        if (count($addons) > 0) {

            foreach ($addons as $addon) {
                $total_addons_price += $addon['addon_price_after_tax'];
            }
        }

        // Calculate the total price
        $total_price = $total_addons_price + $dish_price_after_tax;


        // Prepare the final response
        $response = [
            'item_id' => $item_id,
            'dish_price_before_tax' => $dish_price_before_tax,
            'dish_price_before_coupon' => $totalDishPrice,
            'dish_quantity' => $quantity,
            'dish_tax_value' => $dish_tax_value,
            'dish_price_after_tax' => $dish_price_after_tax,
            'dish_service_value' => $dish_service_value,
            'dish_coupon_value' => $dish_coupon_value + $couponValueTotal,
            'addons' => $addons,
            'total_price' => $total_price
        ];

        return $response;
    }

    public function UpdateCalculateItem($items)
    {
        // dd($items);
        $DataOrderDetail = OrderDetail::find($items['item_id']);
        $DataOrderDetail->total = $items['dish_price_before_tax'];
        $DataOrderDetail->price_befor_tax = $items['dish_price_before_tax'];
        $DataOrderDetail->price_before_coupon = $items['dish_price_before_coupon'];
        $DataOrderDetail->price_after_tax = $items['dish_price_after_tax'];
        $DataOrderDetail->tax_value = $items['dish_tax_value'];
        $DataOrderDetail->service_fees = $items['dish_service_value'];
        $DataOrderDetail->quantity = $items['dish_quantity'];
        $DataOrderDetail->coupon_value = $items['dish_coupon_value'];
        $DataOrderDetail->save();

        foreach ($items['addons'] as $addon) {

            $DataOrderAddon = OrderAddon::where('id', $addon['addon_order_id'])->whereNot('status', 'cancel')->first();
            if ($DataOrderAddon) {

                $DataOrderAddon->price_before_tax = $addon['addon_price_before_tax'] ?? 0;
                $DataOrderAddon->price_after_tax = $addon['addon_price_after_tax'] ?? 0;
                $DataOrderAddon->price_before_coupon = $addon['addon_price_before_coupon'] ?? 0;
                $DataOrderAddon->tax_value = $addon['addon_tax_value'] ?? 0;
                $DataOrderAddon->service_fees = $addon['addon_service_value'] ?? 0;
                $DataOrderAddon->quantity = $addon['addon_quantity'] ?? 0;
                $DataOrderAddon->save();
            }
        }

        // $calculate_order_details = $this->CalculateOrderWithStatus($DataOrderDetail->order_id, ['pending', 'inprogress', 'completed']);
        // $this->UpdateCalculateOrder($calculate_order_details);
        return true;
    }

    public function transformOrderRequest(array $rawRequest)
    {
        $items = [];
        foreach ($rawRequest['items'] as $item) {
            $dish = BranchMenu::find($item['dish_id']);
            $size = isset($item['sizeId']) ? BranchMenuSize::find($item['sizeId']) : null;
            $addons = [];
            if ($dish) {


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
            }
            $itemPrice = $size ? floatval($size->price) : floatval($dish->price ?? 0);
            $totalPrice = $itemPrice * $item['quantity'];

            $items[] = [

                'dish_id' => (string) $dish  ? $dish->id : null,
                'name' => $dish  ? $dish->name : null,
                'image' => $dish  ? $dish->image_url : null, // assuming you have an accessor
                'price' => $itemPrice,
                'size' => $size ? [
                    'id' => $size->id,
                    'price' => $size->price,
                    'label' => $size->label,
                ] : ['label' => ''],
                'addons' => $addons,
                'quantity' => $item['quantity'],
                'note' => $item['note'] ?? '',
                'totalPrice' => $totalPrice,
                'dish_order' => $item['dish_order'] ?? "-1",
                'sizeId' => $size?->id,
                'addon_categories' => $item['addon_categories'] ?? [],
            ];
        }

        return [
            '_token' => csrf_token(),
            'client_address_id' => $rawRequest['address_id'] ?? null,
            'address_id' => $rawRequest['address_id'] ?? null,
            'payment_method' => $rawRequest['payment_method'],
            'payment_status' => $rawRequest['payment_status'] ?? null,
            "cashier_machine_id" => $rawRequest['cashier_machine_id'] ?? null,
            'type' => $rawRequest['type'],
            'note' => $rawRequest['note'] ?? '',
            'table_id' => $rawRequest['table_id'] ?? null,
            'branch_id' => (string) $rawRequest['branch_id'],
            'coupon_code' => $rawRequest['coupon_code'] ?? null,
            'appiontment' => $rawRequest['appiontment'] ?? null,
            'items' => $items,
            'lang' => 'ar',
            'make_type' => $rawRequest['make_type'] ?? 'site',
            'credit_amount' => $rawRequest['credit_amount'] ?? null,
            'cash_amount' => $rawRequest['cash_amount'] ?? null,
            'reference_number' => $rawRequest['reference_number'] ?? null,
            'client_country_code' => $rawRequest['client_country_code'] ?? null,
            'client_phone' => $rawRequest['client_phone'] ?? null,
            'client_name' => $rawRequest['client_name'] ?? null,
            'whatsapp_number_code' => $rawRequest['whatsapp_number_code'] ?? null,
            'whatsapp_number' => $rawRequest['whatsapp_number'] ?? null,
        ];
    }


    public function changeOrderTable($table_id, $order_id)
    {
        $order = Order::find($order_id);
        $table = Table::find($table_id);

        if ($order && ($order->status != 'completed' && $order->status != 'cancelled') && $table->status == 1) {
            $old_table = Table::find($order->table_id);

            // Set old table to available
            $old_table->status = 1;
            $old_table->save();

            // Update order to new table
            $order->table_id = $table_id;
            $order->save();

            // Set new table to occupied
            $table->status = 2;
            $table->save();

            // Structure data for broadcasting
            $data = [
                'id' => $table->id,
                'name' => $table->name,
                'name_ar' => $table->name_ar,
                'name_en' => $table->name_en,
                'table_number' => $table->table_number,
                'status' => $table->status,
                'smoking' => $table->smoking,
                'floors' => [
                    'id' => $table->floors->id,
                    'name' => $table->floors->name,
                    'name_ar' => $table->floors->name_ar,
                    'name_en' => $table->floors->name_en
                ],
                'floor_partitions' => [
                    'id' => $table->floorPartitions->id,
                    'name' => $table->floorPartitions->name,
                    'name_ar' => $table->floorPartitions->name_ar,
                    'name_en' => $table->floorPartitions->name_en
                ]
            ];

            // Notification for the new table
            $notifyDataNew = [
                'notification_type' => 'table',

                'description_ar' => 'تم تغير حاله الطاوله  ' . $table->table_number,
                'description_en' => 'Table status changed ' . $table->table_number,
                'title_ar' => 'تم حجز طاولة',
                'title_en' => 'Table Reserved',
                'created_by' => null,
                'order_id' => $table->id
            ];
            runNotificationToEmployees($table->branch_id, $notifyDataNew, null, $table->id, 'ar');

            // Notification for the old table
            $notifyDataOld = [
                'notification_type' => 'table',
                'description_ar' => 'تم إفراغ طاولة' . $old_table->table_number . ' بالفرع',
                'description_en' => 'A table' . $old_table->table_number . ' was freed in the branch',
                'title_ar' => 'تم إفراغ طاولة',
                'title_en' => 'Table Freed',
                'created_by' => null,
                'order_id' => $old_table->id
            ];
            runNotificationToEmployees($old_table->branch_id, $notifyDataOld, null, $old_table->id, 'ar');

            // Broadcast table update
            broadcast(new TableStatus($data, $table->branch_id, 'update'));

            return true;
        }

        return response()->json([
            'code' => 400,
            'status' => false,
            'message' => __('validation.cannotchange'),
            'data' => null,
            'errorData' => ['error' => __('validation.cannotchange')]
        ], 200);
    }
    public function requestCancellation(Request $request)
    {

        // try {
        DB::beginTransaction(); // Start the transaction

        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $admin = auth('admin')->user();
        $employee = auth('employee')->user();
        // Determine who is logged in
        $user = $admin ?? $employee;
        if (!$user) {
            $message = __('auth.unauthenticated'); // Or "Please login first"
            return response()->json([
                'code' => 401,
                'status' => false,
                'message' => $message,
                'data' => null,
                'errorData' => ['error' => $message]
            ], 200);
        }
        // Optional: restrict by role if employee
        if ($user && !in_array($user->flag, ['waiter', 'cashier'])) {
            $message = __('auth.invalid_employee'); // Or "This is not a valid employee"
            return response()->json([
                'code' => 403,
                'status' => false,
                'message' => $message,
                'data' => null,
                'errorData' => ['error' => $message]
            ], 200);
        }
        // Validate request
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'type' => 'required|in:full,partial',
            'reason' => 'required|string|min:3',
            'payment_method' => 'nullable|string|in:cash,credit,online,credit_with_delivery',
        ]);

        $validator->sometimes('items', ['array', 'min:1'], function ($input) {
            return $input->type === 'partial';
        });

        $validator->sometimes('items.*.item_id', ['integer', 'exists:order_details,id'], function ($input) {
            return $input->type === 'partial';
        });

        $validator->sometimes('items.*.quantity', ['integer', 'min:1'], function ($input) {
            return $input->type === 'partial';
        });


        if ($validator->fails()) {
            return respondError('validation_error', 400, $validator->errors());
        }

        $order_id = $request->order_id;
        $type = $request->type;
        $items = $request->items ?? [];
        $reason = $request->reason;

        $order_details_ids = collect($items)->pluck('item_id')->toArray();
        $quantities = collect($items)->pluck('quantity', 'item_id')->toArray();

        $order = Order::find($order_id);
        /////////Log::info('Order fetched for cancellation request', ['order_id' => $order_id, 'status' => $request->type]);
        $hasPaidTransaction = OrderTransaction::where('order_id', $order_id)
            ->where('payment_status', 'paid')
            ->where('is_refund', 0)
            ->exists();
        if ($user->flag == "waiter" && ($order->status != 'pending' || $hasPaidTransaction)) {
            if ($lang == "ar") {
                $message = "لا يمكنك تقديم طلب إلغاء لهذا الطلب.";
            } else {
                $message = "You cannot submit a cancellation request for this order.";
            }
            return respondError("error", 400, ['error' => $message]);
        }
        // if (!$hasPaidTransaction && $order->status == 'pending') {
        //     if ($lang == "ar") {
        //         $message = "You do not need to make a request. The order is neither paid nor in progress";
        //     } else {
        //         $message = "لا حاجة لتقديم طلب. الطلب ليس مدفوعًا ولا قيد التنفيذ.";
        //     }
        //     return respondError("error", 400, ['error' => $message]);
        // }

        // Validate quantities
        $invalidQuantities = [];




        if ($type == 'partial') {

            $details = OrderDetail::whereIn('id', $order_details_ids)->where('order_id', $order->id)->get();
            if (count($details) > 0) {
                foreach ($details as $detail) {
                    $requestedQty = $quantities[$detail->id] ?? 0;
                    if ($requestedQty <= 0 || $requestedQty > $detail->quantity) {
                        $invalidQuantities[] = [
                            'id' => $detail->id,
                            'available' => $detail->quantity,
                            'requested' => $requestedQty,
                        ];
                    }
                }
            } else {
                if ($lang == "ar") {
                    $message = "معرف تفاصيل الطلب غير مرتبط بمعرف الطلب";
                } else {
                    $message = "order details id not related to order id";
                }
                return respondError("error", 400, ['error' => $message]);
            }
        }



        if (!empty($invalidQuantities)) {
            return respondError("error", 422, [
                'error' => __('order.invalid_quantities'),
                'details' => $invalidQuantities,
            ]);
        }

        // Process item splitting
        $processedOrderDetails = [];
        $quantitiesForConversion = [];
        if ($type == 'partial') {

            foreach ($order_details_ids as $orderDetailId) {
                $orderDetail = OrderDetail::where('id', $orderDetailId)->where('order_id', $order->id)->first();
                if (!$orderDetail) {
                    if ($lang == "ar") {
                        $message = "معرف تفاصيل الطلب غير مرتبط بمعرف الطلب";
                    } else {
                        $message = "order details id not related to order id";
                    }
                    return respondError("error", 400, ['error' => $message]);
                }
                $invoiceDetail = InvoiceDetails::where('details_id', $orderDetail->id)->where('type', 'dish')->first();
                $requestedQty = $quantities[$orderDetailId] ?? 0;
                if (!$orderDetail || $requestedQty <= 0) continue;
                if ($requestedQty == $orderDetail->quantity) {
                    $orderDetail->in_request_return = 1;
                    $orderDetail->save();

                    $processedOrderDetails[] = $orderDetail->id;
                    $quantitiesForConversion[$invoiceDetail->id] = $orderDetail->quantity;
                } else if ($requestedQty > $orderDetail->quantity) {
                    if ($lang == "ar") {
                        $message = "الكمية المطلوبة أكبر من الكمية الأصلية";
                    } else {
                        $message = "requested quantity greater than original quantity";
                    }
                    return respondError("error", 400, ['error' => $message]);
                } else {
                    $remainingQty = $orderDetail->quantity - $requestedQty;
                    // $remainingQtyInv = $invoiceDetail->quantity - $requestedQty;

                    $orderDetail->quantity = $remainingQty;
                    $orderDetail->save();

                    $invoiceDetail->quantity  = $remainingQty;
                    $invoiceDetail->save();

                    $newOrderDetail = new OrderDetail();
                    $newOrderDetail->order_id = $orderDetail->order_id;
                    $newOrderDetail->dish_id = $orderDetail->dish_id;
                    $newOrderDetail->dish_size_id = $orderDetail->dish_size_id;
                    $newOrderDetail->price_befor_tax = 0;
                    $newOrderDetail->tax_value = 0;
                    $newOrderDetail->price_after_tax = 0;
                    $newOrderDetail->service_fees = 0;
                    $newOrderDetail->created_by = $orderDetail->created_by;
                    $newOrderDetail->in_request_return = 1;
                    $newOrderDetail->quantity = $requestedQty;
                    $newOrderDetail->note = $orderDetail->note;
                    $newOrderDetail->dish_order = $orderDetail->dish_order;
                    $newOrderDetail->coupon_id = $orderDetail->coupon_id;
                    $newOrderDetail->save();


                    $invoiceService = app(InvoiceService::class);

                    // dd($order_id, [$newOrderDetail->id], [], "dish", $requestedQty);
                    // dd($newOrderDetail->id);
                    $caluclate_dish = $invoiceService->CalculateItemOrder($order_id, [$newOrderDetail->id], [], "dish", [$requestedQty], "credit_note"); //type = dish or addon or order

                    $add_invoice_details = new InvoiceDetails();
                    $add_invoice_details->invoice_id = $invoiceDetail->invoice_id;
                    $add_invoice_details->details_id = $newOrderDetail->id;
                    $add_invoice_details->dish_size_id = $newOrderDetail->dish_size_id;
                    $add_invoice_details->coupon_id = $newOrderDetail->coupon_id;
                    $add_invoice_details->type = "dish";
                    $add_invoice_details->in_request_return = 1;
                    $add_invoice_details->note = $newOrderDetail->note;
                    $add_invoice_details->quantity =  $newOrderDetail->quantity;
                    $add_invoice_details->tax = $caluclate_dish['tax'];;
                    $add_invoice_details->service_fees = $caluclate_dish['service_fees'];;
                    $add_invoice_details->coupon_value = $caluclate_dish['coupon_value'];
                    $add_invoice_details->total_before_tax = $caluclate_dish['total_before_tax'];
                    $add_invoice_details->total_before_coupon = $caluclate_dish['total_before_coupon'];
                    $add_invoice_details->total_after_tax = $caluclate_dish['total_after_tax'];
                    $add_invoice_details->save();
                    $quantitiesForConversion[$add_invoice_details->id] = $newOrderDetail->quantity;

                    // Copy related addons
                    $addons = OrderAddon::where('order_details_id', $orderDetailId)
                        ->where('order_id', $order_id)->get();
                    if (count($addons) > 0) {

                        foreach ($addons as $addon) {
                            $remainingAddonQty = $addon->quantity - $requestedQty;

                            $invoiceDetailAddon = InvoiceDetails::where('details_id', $addon->id)->where('order_detail_id', $orderDetailId)->where('type', 'addon')->first();

                            $addon->quantity = $remainingAddonQty;
                            $addon->save();

                            $invoiceDetailAddon->quantity = $remainingAddonQty;
                            $invoiceDetailAddon->save();

                            $item_calculate = $this->CalculateItem($orderDetail->id, $remainingQty);
                            $this->UpdateCalculateItem($item_calculate);

                            $newAddon = new OrderAddon();
                            $newAddon->order_id = $addon->order_id;
                            $newAddon->order_details_id = $newOrderDetail->id;
                            $newAddon->dish_addon_id = $addon->dish_addon_id;
                            $newAddon->price_before_tax = 0;
                            $newAddon->tax_value = 0;
                            $newAddon->in_request_return = 1;
                            $newAddon->price_after_tax = 0;
                            $newAddon->service_fees = 0;
                            $newAddon->created_by = $addon->created_by;
                            $newAddon->quantity = $requestedQty;
                            $newAddon->save();


                            $caluclate_addon = $invoiceService->CalculateItemOrder($order_id, [], [$newAddon->id], "addon", [$requestedQty], "credit_note"); //type = dish or addon or order
                            $add_invoice_details = new InvoiceDetails();
                            $add_invoice_details->invoice_id = $invoiceDetail->invoice_id;
                            $add_invoice_details->order_detail_id = $newAddon->order_details_id;
                            $add_invoice_details->details_id = $newAddon->id;
                            $add_invoice_details->type = "addon";
                            $add_invoice_details->in_request_return = 1;

                            $add_invoice_details->quantity = $newAddon->quantity;
                            $add_invoice_details->tax = $caluclate_addon['tax'];;
                            $add_invoice_details->service_fees = $caluclate_addon['service_fees'];;
                            $add_invoice_details->coupon_value = $caluclate_addon['coupon_value'];
                            $add_invoice_details->total_before_tax = $caluclate_addon['total_before_tax'];
                            $add_invoice_details->total_before_coupon = $caluclate_addon['total_before_coupon'];
                            $add_invoice_details->total_after_tax = $caluclate_addon['total_after_tax'];
                            $add_invoice_details->save();
                            // dd($add_invoice_details);
                        }
                    } else {
                        $item_calculate = $this->CalculateItem($orderDetail->id, $remainingQty);
                        $this->UpdateCalculateItem($item_calculate);
                    }

                    // Dish calculation
                    $item_calculate = $this->CalculateItem($newOrderDetail->id, $requestedQty);
                    $this->UpdateCalculateItem($item_calculate);

                    $caluclate_dish = $invoiceService->CalculateItemOrder($order_id, [$orderDetail->id], [], "dish", [$remainingQty], "invoice"); //type = dish or addon or order
                    $invoiceDetail->tax = $caluclate_dish['tax'];;
                    $invoiceDetail->service_fees = $caluclate_dish['service_fees'];;
                    $invoiceDetail->coupon_value = $caluclate_dish['coupon_value'];
                    $invoiceDetail->total_before_tax = $caluclate_dish['total_before_tax'];
                    $invoiceDetail->total_before_coupon = $caluclate_dish['total_before_coupon'];
                    $invoiceDetail->total_after_tax = $caluclate_dish['total_after_tax'];
                    $invoiceDetail->save();

                    // dd($invoiceDetail);
                    $processedOrderDetails[] = $newOrderDetail->id;
                }
            }
        }

        // Convert to invoice
        $conversionResult = $this->convertOrderToInvoice($order_id, $processedOrderDetails, $type);
        if (isset($conversionResult['error'])) {
            return respondError("error", 400, ['error' => $conversionResult['error']]);
        }
        $invoice_id = $conversionResult['invoice_id'];
        $invoice_details_ids = $conversionResult['invoice_detail_ids'];
        // Check for existing cancellation requests



        $cancelItems = [];
        if ($type == 'full') {

            // foreach ($invoice_details_ids as $invoiceDetailId) {
            foreach ($quantitiesForConversion as $itemId => $quantity) {
                // $qty = $quantitiesForConversion[$invoiceDetailId];

                $cancelItems[] = [
                    // 'item_id' => $invoiceDetailId,
                    // 'quantity' => $qty,

                    'invoice_detail_id' => $itemId,
                    'quantity' => $quantity,
                ];
            }
        } else {

            $temp = 0;
            foreach ($invoice_details_ids as $index => $invoiceDetailId) {

                // foreach ($quantitiesForConversion as $itemId => $quantity) {
                $invType = InvoiceDetails::where('id', $invoiceDetailId)
                    ->where('invoice_id', $invoice_id)
                    ->first()->type;

                if ($invType == 'dish') {

                    $qty = $quantitiesForConversion[$invoiceDetailId];
                    $temp = $qty;
                } else {
                    $qty = $temp;
                }

                $cancelItems[] = [
                    'invoice_detail_id' => $invoiceDetailId,
                    'quantity' => $qty,

                    // 'invoice_detail_id' => $itemId,
                    // 'quantity' => $quantity,
                ];
            }
        }
        $invoiceService = app(invoiceService::class);
        $cancelRequest = $invoiceService->makeCancelRequest(
            $invoice_id,
            $cancelItems,
            $type,
            $reason
        );

        $responseData = $cancelRequest->original;
        if (!$responseData['status']) {
            return $responseData; // Respond with validation error if any
        }

        foreach ($cancelItems as &$item) {
            $item['type'] = 'waste';
        }
        // dd($cancelItems);
        $data = new Request([
            "request_id" => $responseData['data']->id,
            "status" => "accept", //or accept
            "reason" => $reason,
            "items" => $cancelItems,
            'payment_method' => $request->payment_method ?? null,
        ]);

        $responseData =   $invoiceService->changeRequestStatus($data);

        $responseData = $responseData->original;
        if (!$responseData['status']) {
            return $responseData; // Respond with validation error if any
        }

        DB::commit(); // ✅ Commit transaction
        // if ($order->make_type == 'cashier') {
        $hasPaidTransaction = OrderTransaction::where('order_id', $order_id)
            ->where('payment_status', 'paid')
            ->where('is_refund', 0)
            ->first();
        $employeeData = Employee::with('machine', 'employeeSchedules')
            ->find(auth('employee')->id());

        if (
            $employeeData &&
            ($employeeData->machine->first()->id ?? false) &&
            $employeeData->employeeSchedules->isNotEmpty() &&
            $hasPaidTransaction
        ) {
            Log::info("Broadcast TotalPaid Event for Order ID: {$order_id}, employee_schedule_id: {$employeeData->employeeSchedules->first()->id}, cashier_machine_id: {$employeeData->machine->first()->id}");

            $cashierBalanceController = app(CashierBalanceController::class);
            $request = new Request([
                'order_id'            => $order_id,
                'cashier_machine_id'  => $employeeData->machine->first()->id,
                'employee_schedule_id' => $employeeData->employeeSchedules->first()->id ?? null,
                'payment_method'      => $hasPaidTransaction->payment_method ?? null,
            ]);

            $responseData = $cashierBalanceController->getCurrentBalance($request)->getData(true);

            if ($responseData['status']) {
                Log::info("Broadcast TotalPaid Event: employee_id={$employeeData->id}, cashier_machine_id={$employeeData->machine->first()->id}");
                broadcast(new TotalPaid($employeeData->id, $responseData['data']));
            }
            // }
        }

        // }


        return RespondWithSuccessRequest($lang, 1);
        // } catch (\Exception $e) {
        //     DB::rollBack(); // ❌ Roll back on error
        //     if ($lang == "ar") {
        //         $message = "فشل طلب الإلغاء:";
        //     } else {
        //         $message = "Cancellation request failed: ";
        //     }
        //     return respondErrorData('error', 500, $message . $e->getMessage());
        // }
    }

    public function orderCancel(Request $request)
    {

        $invoiceService = app(invoiceService::class);
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        // Try both guards
        $admin = auth('admin')->user();
        $employee = auth('employee')->user();
        // Determine who is logged in
        $user = $admin ?? $employee;
        if (!$user) {
            $message = __('auth.unauthenticated'); // Or "Please login first"
            return response()->json([
                'code' => 401,
                'status' => false,
                'message' => $message,
                'data' => null,
                'errorData' => ['error' => $message]
            ], 200);
        }

        // Optional: restrict by role if employee
        if ($user && !in_array($user->flag, ['waiter', 'cashier', 'admin'])) {
            $message = __('auth.invalid_employee'); // Or "This is not a valid employee"
            return response()->json([
                'code' => 403,
                'status' => false,
                'message' => $message,
                'data' => null,
                'errorData' => ['error' => $message]
            ], 200);
        }
        $created_by = $user->id;
        $validator = Validator::make($request->all(), [
            'order_id' => ['required', 'exists:orders,id'],
            'type' => ['required', 'in:1,2'],
            // 'quantity' => ['required', 'int', 'min:1'],
            'reason_id' => ['nullable', 'exists:order_cancellation_reasons,id'],
        ]);
        $validator->sometimes('item_id', ['required', 'exists:order_details,id'], function ($input) {
            return $input->type == 2;
        });
        $validator->sometimes('quantity', ['required'], function ($input) {
            return $input->type == 2;
        });
        if ($user->flag === 'waiter') {
            $rules['order_id'][] = Rule::exists('orders', 'id')->where(function ($query) {
                $query->where('type', 'dine-in');
            });
        } else {
            $rules['order_id'][] = Rule::exists('orders', 'id');
        }
        $validator->after(function ($validator) use ($request) {
            if ($request->type == 2 && $request->filled('item_id') && $request->filled('order_id')) {
                $exists = OrderDetail::where('id', $request->item_id)
                    ->where('order_id', $request->order_id)
                    ->exists();
                if (!$exists) {
                    $validator->errors()->add('item_id', __('validation.invalid_item_for_order'));
                }
            }
        });
        if ($validator->fails()) {
            return respondError(__('validation.error'), 200, $validator->errors());
        }
        $order = Order::where('id', $request->order_id)->first();
        if ($order->status == "cancelled") {
            $message = "You can't delete this order";
            return respondErrorData('errors', 400, [$lang === 'ar' ? 'لا يمكنك حذف الطلب' : $message]);
        }

        // if ($user->flag != 'admin' && ($order->status != "pending" || $order->orderTransactions->contains('payment_status', 'paid'))) {
        //     $message = "You can't delete this order";
        //     return respondErrorData('errors', 400, [$lang === 'ar' ? 'لا يمكنك حذف الطلب' : $message]);
        // }
        $cancel_time = getBranchSettings($order->branch_id, 'time_cancellation');
        $minutesDifference = $order->created_at->diffInMinutes(Carbon::now());

        if ($request->type == 2) {
            if ($request->item_id) {
                //$dish_menu_id = BranchMenu::where('id', $request->item_id)->where('branch_id', $order->branch_id)->first();
                $order_details = OrderDetail::where('id', $request->item_id)->first();
                if ($order_details->status == "cancel") {
                    return response()->json([
                        'code' => 400,
                        'status' => false,
                        'message' => __('validation.AlreadyDeleted'),
                        'data' => null,
                        'errorData' => ['error' => __('validation.AlreadyDeleted')]
                    ], 200);
                }
            }
            $old_qty = 0;

            $new_qty = 0;
            $hasPaidTransaction = OrderTransaction::where('order_id', $request->order_id)
                ->where('payment_status', 'paid')
                ->where('is_refund', 0)
                ->exists();

            if ($order_details) {
                $old_order_detail = OrderDetail::where('dish_id', $order_details->dish_id)->where('order_id', $order_details->order_id)->where('dish_size_id', $order_details->dish_size_id)->first();
                if ($request->quantity == $old_order_detail->quantity) {
                    $old_qty = $old_order_detail->quantity;
                    $new_qty = $request->quantity;
                    $order_details->status = "cancel";
                    $order_details->modify_by = $created_by;
                    $order_details->save();
                    if (!$hasPaidTransaction) {
                        InvoiceDetails::where('details_id', $order_details->id)->where('type', 'dish')->update(['status' => 'cancel', 'modified_by' => $created_by]);
                    }
                    if ($order_details->dishAddons) {
                        OrderAddon::where('order_details_id', $order_details->id)->update(['status' => 'cancel', 'modify_by' => $created_by]);
                        if (!$hasPaidTransaction) {
                            InvoiceDetails::where('order_detail_id', $order_details->id)->where('type', 'addon')->update(['status' => 'cancel', 'modified_by' => $created_by]);
                        }
                    }
                } else if ($old_order_detail->quantity > $old_order_detail->quantity + $request->quantity) { //1>1+2
                    return response()->json([
                        'code' => 400,
                        'status' => false,
                        'message' => "Quantity Error",
                        'data' => null,
                        'errorData' => ['error' => "Request quantity greater than the original quantity"]
                    ], 200);
                } else {

                    $old_qty = $old_order_detail->quantity; //1
                    $new_qty =  $request->quantity; //2
                    $order_details->quantity = $request->quantity;
                    $order_details->modify_by = $created_by;
                    $order_details->save();

                    $order_details->status = "cancel";
                    $order_details->modify_by = $created_by;
                    $order_details->save();
                    //update addon quantity related to this dish
                    if (!$hasPaidTransaction) {
                        InvoiceDetails::where('details_id', $order_details->id)->where('type', 'dish')->update(['status' => 'cancel', 'modified_by' => $created_by]);
                    }
                    // OrderAddon::where('order_details_id', $order_details->id)->update(['quantity' => $request->quantity , 'modify_by' => $created_by]);
                    if ($order_details->dishAddons) {
                        OrderAddon::where('order_details_id', $order_details->id)->update(['status' => 'cancel', 'modify_by' => $created_by]);
                        if (!$hasPaidTransaction) {
                            InvoiceDetails::where('order_detail_id', $order_details->id)->where('type', 'addon')->update(['status' => 'cancel', 'modified_by' => $created_by]);
                        }
                    }
                }

                $remainingOrderDetails = $order->orderDetails()->where('status', '!=', 'cancel')->get();
                if ($remainingOrderDetails->count() == 0) {
                    $order->status = "cancelled";
                    $order->print_status = 'cancelled';
                    $order->modify_by = $created_by;
                    $order->save();
                    $order_tracking = new OrderTracking();
                    $order_tracking->order_id = $request->order_id;
                    $order_tracking->order_status = 'cancelled';
                    $order_tracking->created_by = $created_by;
                    $order_tracking->time = date('H:i:s');
                    $order_tracking->save();
                    $data = [
                        'orderId' => $order->id,
                        'status' => 'cancelled',
                        'date' => now()->toDateString(),
                    ];
                    broadcast(new orderChangeStatus($data, $order));

                    if ($order->type == 'dine-in' && $order->table_id) {
                        $table = Table::find($order->table_id);
                        $table->status = 1;
                        $table->save();

                        $notifyData = [
                            'notification_type' => 'table',
                            'description_ar' => 'تم إفراغ طاولة' . $table->table_number . ' بالفرع',
                            'description_en' => 'A table' . $table->table_number . ' was freed in the branch',
                            'title_ar' => 'تم إفراغ طاولة',
                            'title_en' => 'Table Freed',
                            'created_by' => null,
                            'order_id' => $table->id
                        ];
                        $notification_response =  runNotificationToEmployees($table->branch_id, $notifyData, null, $table->id, 'ar');

                        $data = [
                            'id' => $table->id,
                            'name' => $table->name,
                            'name_ar' => $table->name_ar,
                            'name_en' => $table->name_en,
                            'table_number' => $table->table_number,
                            'status' => $table->status,
                            'smoking' => $table->smoking,
                            'floors' => [
                                'id' => $table->floors->id,
                                'name' => $table->floors->name,
                                'name_ar' => $table->floors->name_ar,
                                'name_en' => $table->floors->name_en
                            ],
                            'floor_partitions' => [
                                'id' => $table->floorPartitions->id,
                                'name' => $table->floorPartitions->name,
                                'name_ar' => $table->floorPartitions->name_ar,
                                'name_en' => $table->floorPartitions->name_en
                            ]
                        ];

                        // Broadcast table update
                        broadcast(new TableStatus($data, $table->branch_id, 'update'));
                    }
                } elseif ($remainingOrderDetails->every(fn($detail) => $detail->status == 'completed') && $order->status != 'completed') {
                    // All remaining details are completed
                    $order->status = "packing";
                    $order->print_status = 'hold';
                    $order->modify_by = $created_by;
                    $order->save();
                    $order_tracking = new OrderTracking();
                    $order_tracking->order_id = $request->order_id;
                    $order_tracking->order_status = 'readyForPickup';
                    $order_tracking->created_by = $created_by;
                    $order_tracking->time = date('H:i:s');
                    $order_tracking->save();

                    $data = [
                        'orderId' => $order->id,
                        'status' => 'packing',
                        'date' => now()->toDateString(),
                    ];
                    broadcast(new orderChangeStatus($data, $order));
                }


                // if ($new_qty <= $old_qty) {
                // if ($new_qty < $old_qty) {

                $item_calculate = $this->CalculateItem($order_details->id,  $new_qty);
                // } else {
                //     dd($old_qty, $new_qty);
                //     $item_calculate = $this->CalculateItem($order_details->id, $old_qty);
                // }

                $update_item_calculate = $this->UpdateCalculateItem($item_calculate);
                // }
                $item_calculate = $this->CalculateOrder($request->order_id);
                $order->refresh();
                $hasPaidTransaction = OrderTransaction::where('order_id', $request->order_id)
                    ->where('payment_status', 'paid')
                    ->where('is_refund', 0)
                    ->exists();

                // dd($old_qty >= $new_qty,$old_qty , $new_qty);
                if ($hasPaidTransaction) {
                    $new_item_calculate = $this->CalculateItemForRefund($request->item_id, $new_qty);
                    // dd($new_item_calculate);
                    // if ($old_qty >= $new_qty) {


                    // $new_qty = $request->quantity - $old_qty;
                    $refund_price_qty = $new_item_calculate['total_price'];
                    $this->storePaymentTransaction($request->order_id, $order->type, $request->payment_method, $created_by, null, $refund_price_qty, null, 'cashier', 'paid', 1, null);
                    // }
                } else {
                    $order_transaction = OrderTransaction::where('order_id', $request->order_id)->where('is_refund', 0)->where('payment_status', 'unpaid')->first();
                    $order_transaction->paid = $order['total_price_after_tax'];
                    $order_transaction->save();
                }

                //call invoice function
                $order_details_ids = [$order_details->id];
                $order_addon_ids = $order_details->dishAddons->pluck('id')->toArray();
            }
        } else {

            foreach ($order->orderDetails as $detail) {
                $detail->status = "cancel";
                $detail->modify_by = $created_by;
                $detail->save();
                if ($detail->dishAddons) {
                    OrderAddon::where('order_details_id', $detail->id)->update(['status' => 'cancel', 'modify_by' => $created_by]);
                }
            }
            $order->status = "cancelled";
            $order->print_status = 'cancelled';
            $order->modify_by = $created_by;
            $order->save();
            if ($order->type == 'dine-in' && $order->table_id) {
                $table = Table::find($order->table_id);
                $table->status = 1;
                $table->save();
                $notifyData = [
                    'notification_type' => 'table',
                    'description_ar' => 'تم إفراغ طاولة' . $table->table_number . ' بالفرع',
                    'description_en' => 'A table' . $table->table_number . ' was freed in the branch',
                    'title_ar' => 'تم إفراغ طاولة',
                    'title_en' => 'Table Freed',
                    'created_by' => null,
                    'order_id' => $table->id
                ];
                runNotificationToEmployees($table->branch_id, $notifyData, null, $table->id, 'ar');

                $data = [
                    'id' => $table->id,
                    'name' => $table->name,
                    'name_ar' => $table->name_ar,
                    'name_en' => $table->name_en,
                    'table_number' => $table->table_number,
                    'status' => $table->status,
                    'smoking' => $table->smoking,
                    'floors' => [
                        'id' => $table->floors->id,
                        'name' => $table->floors->name,
                        'name_ar' => $table->floors->name_ar,
                        'name_en' => $table->floors->name_en
                    ],
                    'floor_partitions' => [
                        'id' => $table->floorPartitions->id,
                        'name' => $table->floorPartitions->name,
                        'name_ar' => $table->floorPartitions->name_ar,
                        'name_en' => $table->floorPartitions->name_en
                    ]
                ];

                // Broadcast table update
                broadcast(new TableStatus($data, $table->branch_id, 'update'));
            }

            $order_tracking = new OrderTracking();
            $order_tracking->order_id = $request->order_id;
            $order_tracking->order_status = 'cancelled';
            $order_tracking->created_by = $created_by;
            $order_tracking->time = date('H:i:s');
            $order_tracking->save();
            $hasPaidTransaction = OrderTransaction::where('order_id', $request->order_id)
                ->where('payment_status', 'paid')
                ->where('is_refund', 0)
                ->exists();
            $lastPaidTransaction = OrderTransaction::where('order_id', $request->order_id)
                ->where('payment_status', 'paid')
                ->where('is_refund', 0)
                ->latest('created_at')
                ->first();
            if ($hasPaidTransaction) {

                $this->storePaymentTransaction($request->order_id, $order->type, $request->payment_method, $created_by, null, $order->total_price_after_tax, null, 'cashier', 'paid', 1, null);
            }
            //call invoice function
            // $orderDetails = OrderDetail::with('dishAddons')
            //     ->where('order_id',  $order->id)
            //     ->get()
            //     ->map(function ($detail) {
            //         // Compute totalBeforeCoupon properly
            //         $addonsTotal = $detail->dishAddons->sum(function ($addon) {
            //             return $addon->price_before_coupon ?? 0;
            //         });

            //         // Determine correct base price (before coupon, tax may or may not apply)
            //         $dishPriceBeforeCoupon = $detail->price_before_coupon ?? $detail->price_befor_tax ?? 0;

            //         $totalBeforeCoupon = $dishPriceBeforeCoupon + $addonsTotal;

            //         return [
            //             'order_detail_id' => $detail->id,
            //             'quantity' => $detail->quantity,
            //             'status' => $detail->status,
            //             'dish_status' => $detail->status,
            //             'total_dish_price' => formatFloat($totalBeforeCoupon),
            //         ];
            //     })
            //     ->values()
            //     ->toArray();
            // $orderItemsCount = $order->status === 'cancelled'
            //     ? $order->orderDetails->sum('quantity')
            //     : $order->orderDetailsWithoutCancel->sum('quantity');
            // // Create data for broadcasting
            // $datachanged = [
            //     'order_id' => $order->id,
            //     'order_type' => $order->type,
            //     'order_items_count' =>  $orderItemsCount,
            //     'status' => $order->status,
            //     'items_updated' => $orderDetails??[],
            //     'date' => now()->toDateString(),
            // ];


            $orderDetails = OrderDetail::with('dishAddons')
                ->where('order_id',  $order->id)
                ->get()
                ->map(function ($detail) {
                    // Compute totalBeforeCoupon properly
                    $addonsTotal = $detail->dishAddons->sum(function ($addon) {
                        return $addon->price_before_coupon ?? 0;
                    });

                    // Determine correct base price (before coupon, tax may or may not apply)
                    $dishPriceBeforeCoupon = $detail->price_before_coupon ?? $detail->price_befor_tax ?? 0;

                    $totalBeforeCoupon = $dishPriceBeforeCoupon + $addonsTotal;

                    return [
                        'order_detail_id' => $detail->id,
                        'quantity' => $detail->quantity,
                        'status' => $detail->status,
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
            $datachanged = [
                'order_id' => $order->id,
                'order_type' => $order->type,
                'order_items_count' =>  $orderItemsCount,
                'status' => $order->status,
                'items_updated' => $orderDetails ?? [],
                'date' => now()->toDateString(),
            ];


            $data = [
                'orderId' => $order->id,
                'order_id' => $order->id,
                'order_type' => $order->type,
                'status' => 'cancelled',
                'date' => now()->toDateString(),
            ];
            broadcast(new orderChangeStatus($data, $order));
            broadcast(new dishChangeStatus2($datachanged));
        }
        if ($request->reason_id) {
            cancelOrderReason($request->order_id, $request->reason, $request->reason_id, $request->item_id);
        }
        $hasPaidTransaction = OrderTransaction::where('order_id', $request->order_id)
            ->where('payment_status', 'paid')
            ->where('is_refund', 0)
            ->exists();

        // If the order is paid, create a credit note invoice
        if (!$hasPaidTransaction && $request->type != 1) {
            $responseInv = $invoiceService->editInvoice($order->id);
            $responseData = $responseInv->original;
            // Handle service response
            if (!$responseData['status']) {
                return $responseInv;
            }
        }



        // broadcast(new dishChangeStatus($data));
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => $lang == 'en' ? 'Order deleted successfully' : 'تم حذف الطلب بنجاح',
            'data' => []
        ]);
    }

    public function orderEditItem(Request $request, $type)
    {
        $lang =  $request->header('lang', 'en');
        App::setLocale($lang);

        $employee = auth('employee')->user();
        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }
        $validator = Validator::make($request->all(), [
            "branch_id" => "required|exists:branches,id",
            "order_id" => "required|exists:orders,id",
            "item_id" => "required|exists:order_details,id",
            "quantity" => "required|integer|min:1",
            "note" => "nullable|string",
        ]);


        $created_by = $employee->id;
        $done = false;
        $IDBranch = $request['branch_id']; //from auth employee branch id
        $status = 'pending';
        $make_type = $employee->flag ?? 'waiter';
        $order = Order::find($request->order_id);
        if (!$order) {
            $validator->errors()->add('order_id', __('order.order_not_found'));
            return respondError('Validation Error.', 400, $validator->errors());
        }

        if ($order->status == "cancelled" || $order->latestTracking->order_status == "delivered" || $order->orderTransactions->contains('payment_status', 'paid')) {
            $message = "You can't edit in this order";
            return respondErrorData('errors', 400, [$lang === 'ar' ? 'لا يمكنك التعديل على الطلب' : $message]);
        }

        $validator->after(function ($validator) use ($request) {
            if ($request->filled('item_id') && $request->filled('order_id')) {
                $exists = OrderDetail::where('id', $request->item_id)
                    ->where('order_id', $request->order_id)
                    ->exists();

                if (!$exists) {
                    $validator->errors()->add('item_id', __('validation.invalid_item_for_order'));
                }
            }
        });
        if ($validator->fails()) {

            return respondErrorData(
                'errors',
                400,
                $validator->errors()->all() // <-- convert to array of messages
            );
        }

        if ($request->item_id) {
            $checkOrderDetails = OrderDetail::where('id', $request->item_id)->where('status', "pending")->first();
            if (!$checkOrderDetails) {
                $message = "you can't allow to edit in this item, the dish is not in pending any more";
                return respondErrorData($message, 400, $lang == 'ar' ? 'غير مسموح بتعديل هذا الطبق لانه اصبح غير معلق' : $message);
            }
        }

        $Branch_Dish = getBranchMenuDetails($IDBranch, $checkOrderDetails->dish_id, 'web', 'first');
        if (!$Branch_Dish) {

            return respondErrorData('error', 400,  __('order.dish_not_found'));
        }

        if ($Branch_Dish->is_active == 0) {
            return respondErrorData('error', 400, __('order.dish_not_active'));
        }
        $addon_categories = [];
        // return $Branch_Dish->id;
        if (!empty($request['addon_categories']) && is_array($request['addon_categories'])) {
            $addon_categories[0] = [
                "id" =>  $request['addon_categories'][0]['id'] ?? null,
                "addon" => $request['addon_categories'][0]['addon'] ?? null
            ];
        }


        $items[0] =  [
            "dish_id" => $Branch_Dish->id,
            "dish_order" => $request->dish_order,
            "sizeId" => $request->size_id,
            "quantity" => $request->quantity,
            "note" => $request->note,
            "addon_categories" => $addon_categories
        ];

        $result = $this->validateOrderItem($items,  $request->branch_id, 'api');
        $responseData = $result->original;
        if (!$responseData['status']) {
            return $result; // Respond with validation error if any
        }
        $has_size = $Branch_Dish->dish->has_sizes;
        if ($has_size) {
            if ($request->size_id) {
                $branch_dish_size = getBranchSizeDetails($IDBranch, $request->size_id, 'api', 'first');
                if (!$branch_dish_size) {
                    $message = "sorry, the size not found";
                    return respondErrorData('error', 400, $message);
                }
                $size_id = $branch_dish_size->dish_size_id;
            } else {
                $size_id = $checkOrderDetails->dish_size_id;
            }
        } else {
            $size_id = null;
        }


        $old_qty = 0;
        $old_size = 0;
        if ($checkOrderDetails) {
            $old_qty =  $checkOrderDetails->quantity;
            $old_size = $checkOrderDetails->dish_size_id;
            $checkOrderDetails->note = $request->note;
            $checkOrderDetails->quantity = $request->quantity;
            $checkOrderDetails->dish_size_id = $size_id;
            $checkOrderDetails->dish_order = $request->dish_order ?? $checkOrderDetails->dish_order;
            $checkOrderDetails->modify_by = $created_by;
            $checkOrderDetails->save();
        }
        $checkOrderDetails->refresh();
        $min_addons = 0;
        $max_addons = 0;
        $check_addons_count = BranchMenuAddon::where('dish_id', $Branch_Dish->dish_id)->where('branch_id', $IDBranch)->where('is_active', 1)->first();
        if ($check_addons_count) {

            $min_addons = $check_addons_count->dishAddons ? $check_addons_count->dishAddons->min_addons : 0;
            $max_addons = $check_addons_count->dishAddons ? $check_addons_count->dishAddons->max_addons : 0;
        }
        $hasPaidTransaction = OrderTransaction::where('order_id', $checkOrderDetails->order_id)
            ->where('payment_status', 'paid')
            ->where('is_refund', 0)
            ->exists();
        if ($request->has('addon_categories')) {
            if ($request->addon_categories != []) {
                $allSelectedAddons = collect();
                $createdAddons = [];
                $updatedAddons = [];

                foreach ($request->addon_categories as $addon_category) {
                    $addons = BranchMenuAddon::whereIn('id', $addon_category['addon'])
                        ->where('branch_id', $IDBranch)
                        ->pluck('dish_addon_id');

                    $allSelectedAddons = $allSelectedAddons->merge($addons);

                    foreach ($addons as $addon_id) {
                        $addon = getBranchAddonDetails($IDBranch, $addon_id, 'web', 'first');
                        $existingAddon = OrderAddon::where([
                            'dish_addon_id' => $addon->dish_addon_id,
                            'order_details_id' => $checkOrderDetails->id,
                            'order_id' => $checkOrderDetails->order_id,
                            'status' => 'pending',
                        ])->first();

                        if ($existingAddon) {
                            $existingAddon->update(['quantity' => $request->quantity]);
                            $updatedAddons[] = $existingAddon->id;
                        } else {
                            $orderAddon = OrderAddon::create([
                                'dish_addon_id' => $addon->dish_addon_id,
                                'order_details_id' => $checkOrderDetails->id,
                                'order_id' => $checkOrderDetails->order_id,
                                'status' => 'pending',
                                'quantity' => $request->quantity,
                                'created_by' => $created_by
                            ]);
                            $createdAddons[] = $orderAddon->id;
                        }
                    }
                }

                // cancel removed addons (do this once)
                OrderAddon::where([
                    'order_details_id' => $checkOrderDetails->id,
                    'status' => 'pending'
                ])->whereNotIn('dish_addon_id', $allSelectedAddons)
                    ->update(['status' => 'cancel']);
            } else {
                $orderAddon = OrderAddon::where(['order_details_id' => $checkOrderDetails->id, 'status' => 'pending'])->update(['status' => 'cancel']);
            }
        }
        $item_calculate = $this->CalculateItem($request->item_id, $checkOrderDetails->quantity);
        $this->UpdateCalculateItem($item_calculate);

        $this->CalculateOrder($request->order_id);
        $order->refresh();
        $order_transaction = OrderTransaction::where('order_id', $request->order_id)->where('is_refund', 0)->where('payment_status', 'unpaid')->first();
        $order_transaction->paid = $order['total_price_after_tax'];
        $order_transaction->save();
        $invoiceService = app(invoiceService::class);

        $responseInv = $invoiceService->editInvoice($order->id);

        $responseData = $responseInv->original;
        if (!$responseData['status']) {
            return $responseData;
        }

        $employees_ids =  getWorkingEmployeesByBranchAndTime($employee->branch_id, now());

        if ($employees_ids['status'] === false) {
            return respondError('Validation Error.', 400, ['error' => __('recipes.nochaiersworknowinbranch')]);
        }
        $employees = Employee::whereIn('id', $employees_ids['working_employee_ids'])->whereIn('flag', ['cashier', 'waiter'])->get();
        if (!$employees) {
            return respondError('Validation Error.', 400, ['error' => __('recipes.nochaiersworknowinbranch')]);
        }
        $checkOrderDetails->refresh();

        foreach ($employees as $employee) {
            $usertoken = User::where('id', $employee->user_id)->value('fcm_token');
            $data = addNotification(
                'order',
                $employee->flag,
                'تم تعديل الطبق ' . $Branch_Dish->dish->name_ar . ' to order ' . $order->order_number,
                'The dish is edited ' . $Branch_Dish->dish->name_en . ' to order ' . $order->order_number,
                'تعديل طبق',
                'Dish edit',
                $employee->id,
                $employee->id,
                $lang,
                $request->order_id,
            );
            if ($checkOrderDetails->order->tax_application == 0) {
                $orderDetailTotal = $checkOrderDetails->price_befor_tax;
                $addonsTotal = $checkOrderDetails->dishAddons->where('status', '!=', 'cancel')->sum('price_before_tax');
            } else {
                $orderDetailTotal = $checkOrderDetails->price_after_tax;
                $addonsTotal = $checkOrderDetails->dishAddons->where('status', '!=', 'cancel')->sum('price_after_tax');
            }
            $total = $orderDetailTotal + $addonsTotal;
        }
        $orderDetails = OrderDetail::with('dishAddons')
            ->where('id',  $request->item_id)
            ->get()
            ->map(function ($detail) {
                // Compute totalBeforeCoupon properly
                $addonsTotal = $detail->dishAddons->sum(function ($addon) {
                    return $addon->price_before_coupon ?? 0;
                });

                // Determine correct base price (before coupon, tax may or may not apply)
                $dishPriceBeforeCoupon = $detail->price_before_coupon ?? $detail->price_befor_tax ?? 0;

                $totalBeforeCoupon = $dishPriceBeforeCoupon + $addonsTotal;

                return [
                    'order_detail_id' => $detail->id,
                    'quantity' => $detail->quantity,
                    'status' => $detail->status,
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
            'date' => now()->toDateString(),
        ];
        broadcast(new dishChangeStatus2($data));
        return ResponseWithSuccessData($lang, $data, 1);
        // } catch (Exception $e) {
        //     DB::rollBack();

        //     return RespondWithBadRequestWithData(['error' => $e->getMessage()]);
        // }
    }

    public function convertOrderToInvoice($order_id, $order_details_ids, $type)
    {
        // Fetch the invoice object based on order_id and invoice_type
        $invoice = Invoice::where('order_id', $order_id)->where('invoice_type', 'invoice')->first();

        // Check if invoice exists
        if (!$invoice) {
            // Return an error if no invoice is found
            return ['error' => 'Invoice not found.'];
        }

        // Get the invoice ID
        $invoice_id = $invoice->id;

        // Initialize an array to hold the invoice_detail_ids
        $invoice_detail_ids = [];

        // Fetch invoice details based on type (partial or not)
        if ($type == 'partial') {
            // Fetch only the selected order details (partial selection)
            $invoice_details = InvoiceDetails::whereIn('details_id', $order_details_ids)
                ->where('invoice_id', $invoice_id)
                ->where('type', 'dish')
                ->get();
        } else {
            // Fetch all invoice details for the full invoice
            $invoice_details = InvoiceDetails::where('invoice_id', $invoice_id)
                ->where('type', 'dish')
                ->get();
        }

        // Loop through each invoice detail and process addons
        foreach ($invoice_details as $invoice_detail) {
            // Add the primary key (details_id) of the 'dish' to the invoice_detail_ids array
            $invoice_detail_ids[] = $invoice_detail->id;

            // Fetch the corresponding addons related to this dish
            $invoice_detail_addons = InvoiceDetails::where('order_detail_id', $invoice_detail->details_id)
                ->where('type', 'addon')
                ->get();

            // Loop through each addon and add its details_id to the invoice_detail_ids array
            foreach ($invoice_detail_addons as $addon) {
                $invoice_detail_ids[] = $addon->id;
            }

            // Optionally, attach the addons to the invoice detail (this can be useful for debugging)
            $invoice_detail['addons'] = $invoice_detail_addons;
        }

        // Return the invoice_id and the list of invoice_detail_ids
        return [
            'invoice_id' => $invoice_id,
            'invoice_detail_ids' => $invoice_detail_ids
        ];
    }

    public function convertInvoiceToOrder($invoice_id, $items)
    {
        // Extract item IDs and build a map of quantities
        $invoice_detail_ids = [];
        $quantity_map = [];

        foreach ($items as $item) {

            $invoice_detail_ids[] = $item->invoice_detail_id;
            $quantity_map[$item->invoice_detail_id] = $item->quantity;
        }

        // Fetch the invoice
        $invoice = Invoice::where('id', $invoice_id)
            ->where('invoice_type', 'invoice')
            ->first();

        if (!$invoice) {
            return ['error' => 'Invoice not found.'];
        }

        $order_id = $invoice->order_id;
        $order_detail_data = [];
        $order_addon_data = [];

        // Fetch the invoice details for the given IDs
        $invoice_details = InvoiceDetails::whereIn('id', $invoice_detail_ids)->where('invoice_id', $invoice_id)->get();

        if ($invoice_details->isEmpty()) {
            return ['error' => 'No invoice details found.'];
        }

        foreach ($invoice_details as $detail) {
            $quantity = $quantity_map[$detail->id] ?? 1;

            if ($detail->type === 'dish') {
                $order_detail_data[] = [
                    'order_detail_id' => $detail->details_id,
                    'quantity' => $quantity
                ];
            } elseif ($detail->type === 'addon') {
                $order_addon_data[] = [
                    'order_addon_id' => $detail->details_id,
                    'quantity' => $quantity
                ];
            }
        }

        return [
            'order_id' => $order_id,
            'order_details' => $order_detail_data,
            'order_addons' => $order_addon_data
        ];
    }
    public function calculateCouponValue($dishIds, $branchId, $coupon, $type)
    {
        $dishes = [];
        foreach ($dishIds as $item) {
            if ($type == "web") {
                $menuItem = BranchMenu::where('dish_id', $item['dish_id'])
                    ->where('branch_id', $branchId)
                    ->where('is_active', 1)
                    ->first();
            } else {
                $menuItem = BranchMenu::where('id', $item['dish_id'])
                    ->where('branch_id', $branchId)
                    ->where('is_active', 1)
                    ->first();
            }

            $dish_id = $menuItem->dish_id;
            $price   = $menuItem->price;
            $sizeId  = $item['sizeId'] ?? null;

            if ($sizeId) {
                $size   = BranchMenuSize::find($sizeId);
                $price  = $size->price;
                $sizeId = $size->dish_size_id;
            }

            $finalPriceBeforeDiscount = $price * ($item['quantity'] ?? 1);

            foreach ($item['addons'] as $addonId) {
                if ($type == "web") {
                    $addon = BranchMenuAddon::where('dish_addon_id', $addonId)
                        ->where('branch_id', $branchId)
                        ->where('is_active', 1)
                        ->first();
                } else {
                    $addon = BranchMenuAddon::findOrFail($addonId);
                }

                $addonsTotal = ($addon->price * $item['quantity']);
                $dishes[] = [
                    'dish_id' => $addonId,
                    'price'   => $addonsTotal,
                    'type'    => 'addon',
                    'has_addon' => 0,
                    'has_size' => 0,
                    'sizeId' => 0,
                    'status'  => $item['status']
                ];
            }

            $dishes[] = [
                'dish_id' => $type == 'web' ? $item['dish_id'] : $dish_id,
                'price'   => $finalPriceBeforeDiscount,
                'type'    => 'dish',
                'has_addon' => count($item['addons']) > 0 ? 1 : 0,
                'has_size' => $item['sizeId'] ? 1 : 0,
                'sizeId' => $sizeId,
                'status'  => $item['status']
            ];
        }

        $totalPrice = collect($dishes)->sum('price');

        $couponAllocated = 0;
        $dishesCount = count($dishes);

        $eachDishPercentage = collect($dishes)->map(function ($itemMap, $index) use ($totalPrice, $coupon, &$couponAllocated, $dishesCount) {
            if ($totalPrice <= 0) {
                $couponValue = 0;
            } elseif ($index == $dishesCount - 1) {
                // give the remainder to last item
                $couponValue = round($coupon - $couponAllocated, 2);
            } else {
                $couponValue = round($coupon * ($itemMap['price'] / $totalPrice), 2);
                $couponAllocated += $couponValue;
            }

            return [
                'dish_id'     => $itemMap['dish_id'],
                'percentage'  => $totalPrice > 0 ? ($itemMap['price'] / $totalPrice) : 0,
                'couponValue' => $couponValue,
                'type'        => $itemMap['type'],
                'priceAfter'  => round($itemMap['price'] - $couponValue, 2),
                'status'      => $itemMap['status'],
                'has_addon'   => $itemMap['has_addon'],
                'has_size'    => $itemMap['has_size'],
                'sizeId'      => $itemMap['sizeId']
            ];
        });
        return $eachDishPercentage;
    }

    public function splitOrder($request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $employee = auth('employee')->user();
        $branch_id = $employee->branch_id;

        $parent_order = Order::with('orderDetails', 'orderTransactions')->findOrFail($request->order_id);

        // Validation: order type and payment status
        if ($parent_order->type != 'dine-in') {
            return respondError(
                $lang == 'en'
                    ? 'Order must be dine-in to be split.'
                    : 'يجب أن يكون الطلب داخل المطعم.',
                400
            );
        }

        if ($parent_order->orderTransactions->last()?->payment_status == 'paid') {
            return respondError(
                $lang == 'en'
                    ? 'Order cannot be split as it is already paid.'
                    : 'لا يمكن تقسيم الطلب لأنه تم دفعه بالفعل',
                400
            );
        }

        DB::beginTransaction();

        try {
            //Create the new split order
            $newOrder = $this->createSplitOrder($parent_order, $request, $employee);

            //Update original order details & create new order details
            $this->splitOrderDetails($parent_order, $newOrder, $request, $employee);

            //Recalculate totals and invoices
            $this->CalculateOrder($parent_order->id);
            $this->CalculateOrder($newOrder->id);

            //Create OrderRequest
            $order_request = OrderRequest::create([
                'source_order_id' => $parent_order->id,
                'target_order_id' => $newOrder->id,
                'request_type' => 'split',
                'status' => 'pending',
                'requested_by_id' => $employee->id,
                'requested_by_role' => $employee->flag,
                'branch_id' => $branch_id,
            ]);

            //Add split items to OrderRequestSplitItem
            foreach ($request->items as $item) {
                OrderRequestSplitItem::create([
                    'order_request_id' => $order_request->id,
                    'order_item_id' => $item['order_detail_id'],
                    'from_order_id' => $parent_order->id,
                    'to_order_id' => $newOrder->id,
                    'quantity' => $item['quantity'],
                ]);
            }

            // Refresh new order to get latest calculated totals
            $newOrder->refresh();

            // Create OrderTransaction for the new order
            $oldTransaction = OrderTransaction::where('order_id', $parent_order->id)->first();
            $new_invoice_id = Invoice::where('order_id', $newOrder->id)->first()?->id;
            
            $this->storePaymentTransaction(
                $newOrder->id,
                $newOrder->type,
                'cash',
                $employee->id,
                null,
                $newOrder->total_price_after_tax,
                0,
                $newOrder->make_type,
                $oldTransaction?->payment_status ?? 'unpaid',
                0,
                null,
                $new_invoice_id
            );

            // Create OrderTracking for the new order
            OrderTracking::create([
                'order_id' => $newOrder->id,
                'order_status' => $newOrder->status,
                'created_by' => $employee->id,
            ]);

            // Update new table status to occupied
            if ($newOrder->table_id) {
                $newTable = Table::find($newOrder->table_id);
                if ($newTable && $newTable->status != 2) {
                    $newTable->status = 2; // Occupied
                    $newTable->last_busy_at = now();
                    $newTable->save();

                    // Notification for the new table
                    $notifyDataNew = [
                        'notification_type' => 'table',
                        'description_ar' => 'تم حجز الطاوله ' . $newTable->table_number . '  بالفرع',
                        'description_en' => 'A table ' . $newTable->table_number . '  was reserved in the branch',
                        'title_ar' => 'تم تعديل حاله طاولة',
                        'title_en' => 'Table changed',
                        'created_by' => null,
                        'order_id' => $newTable->id
                    ];
                    runNotificationToEmployees($newTable->branch_id, $notifyDataNew, null, $newTable->id, 'ar');

                    // Structure data for broadcasting
                    $data = [
                        'id' => $newTable->id,
                        'name' => $newTable->name,
                        'name_ar' => $newTable->name_ar,
                        'name_en' => $newTable->name_en,
                        'table_number' => $newTable->table_number,
                        'status' => $newTable->status,
                        'smoking' => $newTable->smoking,
                        'floors' => [
                            'id' => $newTable->floors->id,
                            'name' => $newTable->floors->name,
                            'name_ar' => $newTable->floors->name_ar,
                            'name_en' => $newTable->floors->name_en
                        ],
                        'floor_partitions' => [
                            'id' => $newTable->floorPartitions->id,
                            'name' => $newTable->floorPartitions->name,
                            'name_ar' => $newTable->floorPartitions->name_ar,
                            'name_en' => $newTable->floorPartitions->name_en
                        ]
                    ];
                    broadcast(new TableStatus($data, $newTable->branch_id, 'updated'));
                }
            }

            DB::commit();

            return $order_request;
        } catch (\Exception $e) {
            DB::rollBack();
            return respondError($e->getMessage(), 500);
        }
    }

    protected function createSplitOrder(Order $parentOrder, $request, $employee)
    {
        $baseId = GetNextID('orders', $parentOrder->make_type);
        do {
            $orderNumber = getNewOrderNumber($parentOrder->make_type, $baseId, $parentOrder->branch_id);
            $baseId++;
            $invoiceNumberExists = Order::where('invoice_number', "INV-{$orderNumber}")->exists();
        } while ($invoiceNumberExists);

        // Get Egyptian timezone date and time
        $egyptianTime = Carbon::now('Africa/Cairo');
        return Order::create([
            'date' => $egyptianTime->format('Y-m-d'),
            'time' => $egyptianTime->format('H:i:s'),
            'type' => $parentOrder->type,
            'status' => $parentOrder->status,
            'note' => $parentOrder->note,
            'delivery_fees' => 0,
            'table_id' => $request->new_table_id,
            'client_id' => $parentOrder->client_id,
            'branch_id' => $parentOrder->branch_id,
            'created_by' => $employee->id,
            'parent_id' => $parentOrder->id,
            'make_type' => $parentOrder->make_type,
            'cashier_id' => $parentOrder->cashier_id,
            'waiter_id' => $parentOrder->waiter_id,
            'order_number' => $parentOrder->order_number,
            'invoice_number' => "INV-{$orderNumber}",
            'invoice_type' => 'split',
            'tax_application' => $parentOrder->tax_application,
            'tax_percentage' => $parentOrder->tax_percentage,
            'service_percentage' => $parentOrder->service_percentage,
        ]);
    }

    /**
     * Handle splitting order details between old and new order
     */
    protected function splitOrderDetails(Order $parentOrder, Order $newOrder, $request, $employee)
    {
        $invoiceService = app(invoiceService::class);

        foreach ($request->items as $item) {
            $originalDetail = OrderDetail::where('id', $item['order_detail_id'])
                ->where('order_id', $parentOrder->id)
                ->first();
            if (!$originalDetail) continue;

            $splitQty = $item['quantity'];
            $originalQtyBeforeSplit = $originalDetail->quantity; // Save original quantity before modification

            // Create new order detail for the split quantity FIRST (before modifying original)
            $newDetail = $originalDetail->replicate(['id']);
            $newDetail->order_id = $newOrder->id;
            $newDetail->quantity = $splitQty;
            $newDetail->save();

            // Copy addons proportionally to the new detail
            $addons = OrderAddon::where('order_details_id', $originalDetail->id)->get();
            foreach ($addons as $addon) {
                // Calculate proportional quantity for addons
                $addonSplitQty = $originalQtyBeforeSplit > 0 ? round(($addon->quantity / $originalQtyBeforeSplit) * $splitQty) : $addon->quantity;
                // Ensure at least 1 if original addon quantity was > 0
                if ($addon->quantity > 0 && $addonSplitQty < 1) {
                    $addonSplitQty = 1;
                }

                if ($addonSplitQty > 0) {
                    $newAddon = $addon->replicate(['id']);
                    $newAddon->order_id = $newOrder->id;
                    $newAddon->order_details_id = $newDetail->id;
                    $newAddon->quantity = $addonSplitQty;
                    $newAddon->save();
                    
                    // تقليل كمية الإضافات في الطلب الأصلي
                    $addon->quantity -= $addonSplitQty;
                    if ($addon->quantity <= 0) {
                        $addon->delete();
                    } else {
                        $addon->save();
                    }
                }
            }

            // Decrease quantity in original order
            $originalDetail->quantity -= $splitQty;

            // If quantity becomes 0 or less, delete the detail and its addons
            if ($originalDetail->quantity <= 0) {
                // Delete related addons first
                OrderAddon::where('order_details_id', $originalDetail->id)->delete();
                // Delete the detail
                $originalDetail->delete();
            } else {
                $originalDetail->save();
            }
        }

        // Recalculate totals for both orders
        $this->CalculateOrder($parentOrder->id);
        $this->CalculateOrder($newOrder->id);

        // Update/create invoices
        $oldTransaction = OrderTransaction::where('order_id', $parentOrder->id)->first();

        //New invoice for split order
        $new_invoice_id = $invoiceService->makeInvoice(
            $newOrder->id,
            "order",
            'invoice',
            0,
            0,
            null,
            $newOrder->orderDetails()->pluck('id')->toArray(),
            $newOrder->orderAddons()->pluck('id')->toArray()
        );

        //Update original order invoice
        $invoice_parent_id = Invoice::where('order_id', $parentOrder->id)->first()->id;

        // Remove old invoice details for moved items
        InvoiceDetails::whereIn('details_id', $newOrder->orderDetails()->pluck('id')->toArray())
            ->where('invoice_id', $invoice_parent_id)
            ->delete();

        // Edit original invoice totals
        $invoiceService->editInvoice($parentOrder->id, 1);

        // Update transactions
        if ($oldTransaction) {
            $oldTransaction->paid = $parentOrder->total_price_after_tax;
            $oldTransaction->original_price = $parentOrder->total_price_after_tax;
            $oldTransaction->save();
        }

        // Update invoice status for new invoice
        Invoice::where('id', $new_invoice_id)->update([
            'status' => $oldTransaction?->payment_status ?? 'pending'
        ]);
        $data = [
            'order_id' => $parentOrder->id,
            'order_type' => $parentOrder->type,
            'order_items_count' =>  $parentOrder->orderDetailsWithoutCancel->count('id'),
            'status' => $parentOrder->status,
            'items_updated' => $parentOrder->orderDetails,
            'date' => now()->toDateString(),
        ];
        broadcast(new dishChangeStatus2($data));
        $orderData = ['order_id' => $newOrder->id, 'order_type' => $newOrder->type];
        broadcast(new NewOrder2($employee->id, $employee->branch_id, $orderData));
        return $new_invoice_id;
    }

    protected function updateMergedOrder($request)
    {
        $mainOrder = Order::with('orderDetails')->findOrFail($request->main_order_id);
        $mergedOrder = Order::with('orderDetails')->findOrFail($request->merged_order_id);
        
        // Move all order details from merged order to main order
        foreach ($mergedOrder->orderDetails as $detail) {
            $detail->order_id = $mainOrder->id;
            $detail->save();
            
            // Move addons
            OrderAddon::where('order_details_id', $detail->id)
                ->update(['order_id' => $mainOrder->id]);
        }
        
        // Recalculate totals
        $this->CalculateOrder($mainOrder->id);
        
        return $mainOrder;
    }

    public function mergeOrder($request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $employee = auth('employee')->user();
        $branch_id = $employee->branch_id;

        DB::beginTransaction();

        try {
            // Get merged order with orderDetails before moving them
            $mergedOrder = Order::with('orderDetails')->findOrFail($request->merged_order_id);
            
            // Save orderDetails before they are moved
            $mergedOrderDetails = $mergedOrder->orderDetails;
            
            //Update the main order with merged order items
            $newOrder = $this->updateMergedOrder($request);

            // Update table status to available (status = 1)
            if ($mergedOrder->table_id) {
                $table = Table::find($mergedOrder->table_id);
                if ($table) {
                    $table->status = 1;
                    $table->save();
                }
            }
            
            //Create OrderRequest
            $order_request = OrderRequest::create([
                'source_order_id' => $request->main_order_id,
                'target_order_id' => $request->merged_order_id,
                'request_type' => 'merge',
                'status' => 'approved',
                'requested_by_id' => $employee->id,
                'requested_by_role' => $employee->flag,
                'branch_id' => $branch_id,
            ]);

            //Add merge items to OrderRequestSplitItem
            foreach ($mergedOrderDetails as $item) {
                OrderRequestSplitItem::create([
                    'order_request_id' => $order_request->id,
                    'order_item_id' => $item->id,
                    'from_order_id' => $request->merged_order_id,
                    'to_order_id' => $request->main_order_id,
                    'quantity' => $item->quantity,
                ]);
            }

            // Close the merged order (mark as cancelled)
            $mergedOrder->status = 'cancelled';
            $mergedOrder->print_status = 'cancelled';
            $mergedOrder->modify_by = $employee->id;
            $mergedOrder->save();

            // Create tracking record for merged order cancellation
            OrderTracking::create([
                'order_id' => $mergedOrder->id,
                'order_status' => 'cancelled',
                'created_by' => $employee->id,
            ]);

            DB::commit();

            // Create OrderTransaction for new order
            $newOrder->refresh();
            $new_invoice_id = Invoice::where('order_id', $newOrder->id)->first()?->id;
            
            $this->storePaymentTransaction(
                $newOrder->id,
                $newOrder->type,
                'cash',
                $newOrder->created_by,
                null,
                $newOrder->total_price_after_tax,
                0,
                $newOrder->make_type,
                'unpaid',
                0,
                null,
                $new_invoice_id
            );

            // Create OrderTracking for new order
            OrderTracking::create([
                'order_id' => $newOrder->id,
                'created_by' => $newOrder->created_by,
            ]);

            // Update new table status to busy (status = 2)
            if ($newOrder->table_id) {
                $newTable = Table::with(['floors', 'floorPartitions'])->find($newOrder->table_id);
                if ($newTable && $newTable->status != 2) {
                    $newTable->status = 2;
                    $newTable->last_busy_at = now();
                    $newTable->modified_by = $newOrder->created_by;
                    $newTable->save();

                    // Notification for the new table
                    $notifyDataNew = [
                        'notification_type' => 'table',
                        'description_ar' => 'تم حجز الطاوله ' . $newTable->table_number . ' بالفرع',
                        'description_en' => 'A table ' . $newTable->table_number . ' was reserved in the branch',
                        'title_ar' => 'تم تعديل حاله طاولة',
                        'title_en' => 'Table changed',
                        'created_by' => $newOrder->created_by,
                        'order_id' => $newOrder->id
                    ];
                    runNotificationToEmployees($newTable->branch_id, $notifyDataNew, $newOrder->created_by, $newTable->id, 'ar');

                    // Structure data for broadcasting
                    $data = [
                        'id' => $newTable->id,
                        'name' => $newTable->name,
                        'name_ar' => $newTable->name_ar,
                        'name_en' => $newTable->name_en,
                        'table_number' => $newTable->table_number,
                        'status' => $newTable->status,
                        'smoking' => $newTable->smoking,
                        'floors' => [
                            'id' => $newTable->floors->id ?? null,
                            'name' => $newTable->floors->name ?? null,
                            'name_ar' => $newTable->floors->name_ar ?? null,
                            'name_en' => $newTable->floors->name_en ?? null
                        ],
                        'floor_partitions' => [
                            'id' => $newTable->floorPartitions->id ?? null,
                            'name' => $newTable->floorPartitions->name ?? null,
                            'name_ar' => $newTable->floorPartitions->name_ar ?? null,
                            'name_en' => $newTable->floorPartitions->name_en ?? null
                        ]
                    ];
                    broadcast(new TableStatus($data, $newTable->branch_id, 'update'));
                }
            }

            return $new_invoice_id;
        } catch (\Exception $e) {
            DB::rollBack();
            return respondError($e->getMessage(), 500);
        }
    }
}
