<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\AddonCategory;
use App\Models\Branch;
use App\Models\BranchMenu;
use App\Models\BranchMenuAddon;
use App\Models\BranchMenuAddonCategory;
use App\Models\BranchMenuSize;
use App\Models\ClientAddress;
use App\Models\DishAddon;
use App\Models\DishDiscount;
use App\Models\Offer;
use App\Models\OrderTransaction;
use App\Models\TableReservationTransaction;
use App\Models\PolicyPaymentReservation;
use App\Services\ClientServices\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{

    protected $orderService;
    protected $lang;
    protected $checkToken;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
        $this->lang =  app()->getLocale();
        $this->checkToken = false;
    }
    /**
     * Display a listing of the resource.
     */
    public function getDishDetail(Request $request)
    {

        $branchId = $request->cookie('branch_id') ?? ($_COOKIE['branch_id'] ?? null);

        $Branch = Branch::find($branchId);

        if (!$Branch) {
            return response()->json(['status' => 'error', 'message' => 'Branch not found'], 404);
        }

        switch ($request->type) {
            case 'offer':
                return $this->handleOfferType($request, $branchId, $Branch);

            case 'discount':
                return $this->handleDiscountType($request, $branchId, $Branch);

            default:
                return $this->handleDishType($request, $branchId, $Branch);
        }
    }

    private function handleOfferType($request, $branchId, $Branch)
    {
        $offer = Offer::find($request->id);

        if (!$offer) {
            return response()->json(['status' => 'error', 'message' => 'Offer not found'], 404);
        }

        $BranchMenu = BranchMenu::where('dish_id', $request->id)->where('branch_id', $branchId)->first();
        if (!$BranchMenu) {
            return response()->json(['status' => 'error', 'message' => 'Branch menu not found'], 404);
        }

        $response = [
            'status' => 'success',
            'name' => $offer->name,
            'branch' => [
                'currency_symbol' => $Branch->country->currency_symbol ?? '',
            ],
            'details' => $offer->details->map(function ($detail) use ($BranchMenu) {
                $price = $detail->discount_type === 'fixed'
                    ? $detail->discount_value
                    : $BranchMenu->price - ($detail->discount_value / 100) * $BranchMenu->price;

                return [
                    'price' => $price,
                    'dish' => [
                        'id' => $detail->type_id,
                        'quantity' => $detail->count,
                        'name' => $detail->dish->name_ar ?? '',
                        'description' => $detail->dish->description ?? '',
                        'price' => $price,
                        'image' => $detail->dish->image_ar ?? null,
                        'has_size' => 0,
                        'has_addon' => 0,
                        'mostOrdered' => 0,
                    ],
                ];
            }),
        ];

        return response()->json($response, 200);
    }

    private function handleDiscountType($request, $branchId, $Branch)
    {
        $dishDiscount = DishDiscount::find($request->id);

        if (!$dishDiscount || !$dishDiscount->discount) {
            return response()->json(['status' => 'error', 'message' => 'Discount not found'], 404);
        }
        // dd($dishDiscount->dish, $branchId);

        $BranchMenu = BranchMenu::where('dish_id', $dishDiscount->dish->id)
            ->where('branch_id', $branchId)
            ->first();

        if (!$BranchMenu) {
            return response()->json(['status' => 'error', 'message' => 'Branch menu not found'], 404);
        }
        $defaultPrice = $BranchMenu->dish->has_sizes
            ? BranchMenuSize::where('branch_menu_sizes.dish_id', $dishDiscount->dish->id)
            ->where('branch_id', $branchId)->leftJoin('dish_sizes', 'dish_sizes.id', 'branch_menu_sizes.dish_size_id')->where('dish_sizes.default_size', 1)->first()->price ?? 0
            : $BranchMenu->price;
        $response = [
            'status' => 'success',
            'name' => $dishDiscount->discount->name,
            'branch' => [
                'currency_symbol' => $Branch->country->currency_symbol ?? '',
            ],
            'original' => $defaultPrice,
            'details' => [
                'price' => applyDiscount($defaultPrice, $dishDiscount->discount),
                'id' => $request->id,
                'dish_id' => $dishDiscount->dish_id,
                'name' => $dishDiscount->dish->name_ar ?? '',
                'description' => $dishDiscount->dish->description ?? '',
                'image' => $dishDiscount->dish->image ?? null,
                'has_size' => 0,
                'has_addon' => 0,
                'mostOrdered' => 0,
            ],
        ];

        return response()->json($response, 200);
    }

    private function handleDishType($request, $branchId, $Branch)
    {
        $min = 0;
        $max = 0;
        $BranchMenu = BranchMenu::where('dish_id', $request->id)
            ->where('branch_id', $branchId)
            ->where('is_active', 1)

            ->first();

        if (!$BranchMenu) {
            return response()->json(['status' => 'error', 'message' => 'Dish not found in this branch.'], 404);
        }

        $BranchMenuSize = BranchMenuSize::where('dish_id', $request->id)
            ->where('branch_id', $branchId)
            ->where('is_active', 1)
            ->get();
        $AddonCategory = AddonCategory::first();
        $branchMenuAddonCategory = BranchMenuAddonCategory::where('addon_category_id', $AddonCategory->id)->where('branch_id', $branchId);
        $branchMenuAddonCategory_ids = $branchMenuAddonCategory->clone()->pluck('id')->toArray();
        // $branchMenuAddonCategoryArray = $branchMenuAddonCategory->clone()->get();
        $BranchMenuAddon = BranchMenuAddon::whereIn('branch_menu_addon_category_id', $branchMenuAddonCategory_ids)->where('dish_id', $request->id)
            ->where('branch_id', $branchId)
            ->where('is_active', 1)
            ->get();

        $defaultPrice = $BranchMenu->dish->has_sizes
            ? BranchMenuSize::where('branch_menu_sizes.dish_id', $request->id)
            ->select('branch_menu_sizes.*')
            ->where('is_active', 1)
            ->where('branch_id', $branchId)->leftJoin('dish_sizes', 'dish_sizes.id', 'branch_menu_sizes.dish_size_id')->where('dish_sizes.default_size', 1)->first()->price ?? 0
            : $BranchMenu->price;
        if ($BranchMenuAddon->count() >  0) {

            $DishAddons = DishAddon::where('dish_id', $request->id)->where('addon_category_id', $BranchMenuAddon[0]->branchMenuAddonCategories->addonCategories->id)->first();
            if ($DishAddons) {

                $min = $DishAddons->min_addons;
                $max = $DishAddons->max_addons;
            }
        }
        $response = [
            'status' => 'success',
            'branch' => [
                'currency_symbol' => $Branch->country->currency_symbol ?? '',
            ],
            'dish' => [
                'id' => $BranchMenu->dish_id,
                // 'name' => $BranchMenu->dish->name_ar ?? '',
                // 'description' => $BranchMenu->dish->description ?? '',
                'name' => app()->getLocale() == 'en' ? $BranchMenu->dish->name_en ?? '' : $BranchMenu->dish->name_ar ?? '',
                'description' => app()->getLocale() == 'en' ? $BranchMenu->dish->description_en ?? '' : $BranchMenu->dish->description_ar ?? '',


                'price' => $defaultPrice,
                'image' => $BranchMenu->dish->image ?? null,
                'has_size' => $BranchMenu->dish->has_sizes,
                'has_addon' =>  $BranchMenu->dish->has_addon,
                'mostOrdered' => checkDishExistMostOrderd($branchId, $BranchMenu->dish_id),
            ],
            'sizes' => $BranchMenuSize->map(fn($size) => [
                'id' => $size->id,
                'name' => $size->dishSizes->name_site ?? '',
                'price' => $size->price,
                'default_size' => $size->dishSizes->default_size ?? false,
            ]),
            'min' => $min,
            'max' => $max,
            'addons' => $BranchMenuAddon->map(fn($addon) => [
                'id' => $addon->id,
                'name' => $addon->dishAddons->addons->name_site ?? '',
                'price' => $addon->price,
            ]),
        ];

        return response()->json($response, 200);
    }

    public function Cart(Request $request)
    {
        $branchId = $request->cookie('branch_id') ?? ($_COOKIE['branch_id'] ?? null);
        $currency = $branchId ?? Branch::find($branchId)->country->currency_symbol;

        $address = '';
        if (auth('client')->check()) {

            $address = ClientAddress::where('is_active', 1)->where('is_default', 1)->where('user_id', auth('client')->user()->id)->first();
        }
        return view('website.cart.view', compact('address', 'branchId', 'currency'));
    }
    public function Checkout(Request $request)
    {
        $branchId = $request->cookie('branch_id') ?? ($_COOKIE['branch_id'] ?? null);

        $address = '';
        if (auth('client')->check()) {

            $address = ClientAddress::where('is_active', 1)->where('is_default', 1)->where('user_id', auth('client')->user()->id)->first();
        }
        $reservation_policy = PolicyPaymentReservation::first();
        $cash_limit = getCashPaymentPolicy($branchId);

        return view('website.checkout.view', compact('address', 'branchId', 'reservation_policy', 'cash_limit'));
    }
    public function store(Request $request)
    {
        $data = $request->all();

        $lang = $request->header('lang') ?? app()->getLocale() ?? 'ar';
        $branchId = $request->cookie('branch_id');

        $cart = json_decode($request->cart_data);
        $takeaway_data = json_decode($request->takeaway_data);
        $reservation_data = json_decode($request->reservation_data);

        if ($cart && !empty((array) $reservation_data)) {
            $data['type'] = 'reservation-table';
        } elseif ($cart && !empty((array) $takeaway_data)) {
            $data['type'] = 'Takeaway';
        } elseif ($cart) {
            $data['type'] = 'Delivery';
        }

        if ($data['type'] == 'Takeaway' && $takeaway_data) {
            $data['selected_date'] = $takeaway_data->selected_date;
            $data['selected_time'] = $takeaway_data->selected_time;
        }

        $data['note'] = $cart->notes ?? null;
        $data['table_id'] = null;
        $data['branch_id'] = $branchId;
        $data['coupon_code'] = $cart->coupon ?? null;
        $data['appiontment'] = $request->appiontment ?? null;


        $item_array = json_decode(json_encode($cart->items), true);

        foreach ($item_array as $k => $item) {
            $item_array[$k]['sizeId'] = $item['size']['id'] ?? null;
            $item_array[$k]['note'] = $item_array[$k]['notes'] ?? null;
            unset($item_array[$k]['notes']); // Remove 'notes'

            $addon_categories = ['id' => 1, 'addon' => []];
            foreach ($item['addons'] as $addon) {
                if ($addon) {
                    $addon_categories['addon'][] = $addon['id'];
                }
            }
            $item_array[$k]['addon_categories'][] = $addon_categories;
        }

        $data['items'] = $item_array;
        $data['address_id'] = $request->client_address_id;
        $data['lang'] = $lang;

        if (!empty((array) $reservation_data)) {
            $data = array_merge($data, [
                'table_id' => $reservation_data->tableId ?? null,
                'branch_id' => $branchId ?? null,
                'floor_partition_id' => $reservation_data->partitionId ?? null,
                'client_id' => $reservation_data->client_id ?? null,
                'date' => $reservation_data->date ?? null,
                'time_from' => $reservation_data->tableSession ?? null,
                'time_to' => $reservation_data->leaveTime ?? null,
                'reservation_type' => $reservation_data->reservType ?? null,
                'adult' => $reservation_data->personal->adult ?? null,
                'kids' => $reservation_data->personal->kids ?? null,
                'men' => $reservation_data->personal->men ?? null,
                'women' => $reservation_data->personal->women ?? null,
                'personal_type' => $reservation_data->personal->type ?? null,
            ]);
        }
        $data['make_type'] = 'site';
        $response = $this->orderService->store_v2($data, $this->checkToken, 'web');
        $responseData = $response->original;

        if (!$responseData['status']) {
            // Prefer `errorData['error']` if it exists
            $errors = $responseData['errorData']['error'] ?? $responseData['data'] ?? 'حدث خطأ ما';

            return redirect()->back()
                ->withErrors(['error' => $errors])
                ->withInput()
                ->withCookie(cookie('backButton', true, 60)); // 60 minutes
        }


        $request['payment_method'] = $request['payment_method'] ?? $request['payment_method2'];

        if (in_array($request['payment_method'], ['full_payment_required', 'deposit_required'])) {
            $reserve = "with";
            return redirect()->route('myfatoorah-payment', [$responseData['data']['order_id'], $reserve]);
        }

        return redirect()->route('orders.tracking')->with('message', $responseData['message']);
    }

    public function validateCoupon(Request $request)
    {
        $couponCode = $request->code;
        $branchId = $request->cookie('branch_id');
        $user = Auth::guard('client')->user();


        $coupon = GetCouponId($couponCode, $branchId);
        if ($coupon) {
            $check = CheckUserCouponUsage($coupon->id, $user->id);
            // dd($request->amount);
            $valid = CheckCouponValid($coupon->id, $request->amount);

            return response()->json([
                'valid' => (!$check && $valid) ? true : false,
            ]);
        }
        return response()->json([
            'valid' =>  false,
        ]);
    }

    public function isCouponValid(Request $request)
    {
        $code = $request->code;
        $amount = $request->amount;
        $branchId = $request->cookie('branch_id');
        $user = Auth::guard('client')->user();
        $lang = $request->header('lang') ?? app()->getLocale() ?? 'ar';
        $coupon_application = getBranchSettings($branchId, 'coupon_application');
        $tax_application = getBranchSettings($branchId, 'tax_application');
        $tax_percentage = getBranchSettings($branchId, 'tax_percentage');
        try {
            // $branchId = $request->input('branch_id');

            $coupon = GetCouponId($code, $branchId);
            if ($coupon) {
                $date = ($coupon->end_date) <= (Carbon::now());
                $startdate = ($coupon->start_date) > (Carbon::now());
                $minimum_spend = $coupon->minimum_spend;
                $usage = $coupon->count_usage >= $coupon->usage_limit;
                $valid = CheckCouponValid($coupon->id, $amount);
                if (!$user) {
                    return RespondWithBadRequest($lang, 4);
                }
                if ($date) {
                    return response()->json([
                        'success' => false,
                        'message' => $lang == 'en' ? 'Coupon is expired.' : 'انتهت صلاحية الكوبون.',
                    ], 200);
                }
                if ($startdate) {
                    return response()->json([
                        'success' => false,
                        'message' => $lang == 'en' ? 'Coupon validity error.' : 'خطأ في صلاحية الكوبون.',
                    ], 200);
                }
                if ($usage) {
                    return response()->json([
                        'success' => false,
                        'message' => $lang == 'en' ? 'Coupon reached usage limit.' : 'الكوبون وصل الحد الاقصى للاستخدام.',
                    ], 200);
                }
                if ($amount && ($amount < $minimum_spend)) {
                    return response()->json([
                        'success' => false,
                        'message' => $lang == 'en' ? "Can't apply coupon minimum spend not reached." : 'لا يمكن تطبيق الكوبون, لم تتجاوز الحد الادني للشراء.',
                    ], 200);
                }
                if ($valid) {
                    $check = CheckUserCouponUsage($coupon->id, $user->id);
                    if ($check) {
                        return response()->json([
                            'success' => false,
                            'message' => trans('cart.CouponUsedBefore'),
                        ], 200);
                    }
                    if ($coupon_application == 0) {
                        if ($tax_application == 0) {
                            $amount_after_coupon =  applyCoupon($amount, $coupon);
                            $coupon_value = $amount - $amount_after_coupon;
                        } else {
                            $value_before = $amount - CalculateTax($tax_percentage, $amount);
                            $amount_after_coupon =  applyCoupon($value_before, $coupon);
                            $coupon_value =  $value_before - $amount_after_coupon;
                        }
                    } else {
                        if ($tax_application == 0) {

                            $valueAfterTax =  applyTax($amount, $tax_percentage, $tax_application);
                            $amount_after_coupon =  applyCoupon($valueAfterTax, $coupon) - CalculateTax($tax_percentage, $valueAfterTax);
                            $coupon_value = $valueAfterTax - $amount_after_coupon - CalculateTax($tax_percentage, $valueAfterTax);
                        } else {

                            $amount_after_coupon =  applyCoupon($amount, $coupon);
                            $coupon_value = $amount - $amount_after_coupon;
                        }
                    }
                    return response()->json([
                        'success' => true,
                        'message' => trans('cart.CouponValid'),
                        'data' => $amount_after_coupon,
                        'coupon_value' => $coupon_value

                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => trans('cart.CouponNoLongerValid'),
                    ], 200);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => trans('cart.CouponNotValid'),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('cart.ErrorChecking'),
            ], 200);
        }
    }

    public function payment_transaction($payment_gateway_reference, $reserve)
    {
        $payment_method_value = array('credit', 'online', 'credit_card');
        $transaction_payment_status = "payment_failed";
        if ($reserve == "without") {
            $payment_status_details = TableReservationTransaction::where('payment_gateway_reference', $payment_gateway_reference)->whereIn('payment_method', $payment_method_value)->first();
        } else {
            $payment_status_details = OrderTransaction::where('payment_gateway_reference', $payment_gateway_reference)->whereIn('payment_method', $payment_method_value)->first();
        }
        if ($payment_status_details) {
            $transaction_payment_status = $payment_status_details->payment_status;
        }
        return view('website.payment_transaction_details', compact('transaction_payment_status'));
    }

    public function checkDishBranch($branchId)
    {
        return checkDishBranches($branchId);
    }

    public function checkDish($dishId, $branchId)
    {
        return checkDishes($dishId, $branchId);
    }

    public function checkDishSize($dishId, $dishSizeId, $branchId)
    {
        return checkDishSizes($dishId, $dishSizeId, $branchId);
    }

    public function checkDishAddon($dishId, $dishAddonId, $branchId)
    {
        return checkDishAddons($dishId, $dishAddonId, $branchId);
    }


    public function reOrder(Request $request)
    {
        $response = $this->orderService->reOrder($request, false);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        return $responseData['data'];
    }
    public function getDeliveryFeesAjax(Request $request)
    {
        $addressId = $request->input('address_id');
        $branchId = $request->input('branch_id');

        $fees = getDeliveryFees($addressId, $branchId); // your existing helper

        return response()->json(['delivery_fees' => $fees]);
    }
}
