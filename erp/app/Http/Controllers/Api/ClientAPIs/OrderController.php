<?php

namespace App\Http\Controllers\Api\ClientAPIs;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchMenu;
use App\Models\BranchMenuAddon;
use App\Models\BranchMenuAddonCategory;
use App\Models\BranchMenuSize;
use App\Models\ClientAddress;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderTracking;
use App\Models\OrderTransaction;
use App\Models\RecipeAddon;
use App\Models\DishAddon;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Models\OrderProduct;
use App\Models\ProductUnit;
use App\Models\Store;
use App\Services\AddressServices\BranchSiteService;
use App\Services\ClientServices\OrderService;
use App\Services\MyFatoorahService;
use App\Services\SettingsServices\BranchService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php
    protected $orderService;
    protected $lang;
    protected $checkToken;  // Set to true or false based on your need
    protected $myFatoorahService;

    protected $branchService;
    protected $branchSiteService;


    public function __construct(OrderService $orderService, MyFatoorahService $myFatoorahService, BranchService $branchService, BranchSiteService $branchSiteService)
    {
        $this->branchService = $branchService;
        $this->orderService = $orderService;
        $this->myFatoorahService = $myFatoorahService;
        $this->branchSiteService = $branchSiteService;
        $this->lang =  app()->getLocale();
        $this->checkToken = true;
    }
    public function index(Request $request)
    {

        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided
        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }
        $orders = Order::all();

        foreach ($orders as $order) {
            $order['details'] = OrderDetail::where('order_id', $order->id)->get();
            $order['addons'] = OrderAddon::where('order_id', $order->id)->get();
            $order['transaction'] = OrderTransaction::where('order_id', $order->id)->get();
        }

        return ResponseWithSuccessData($lang, $orders, 1);
    }

    public function store(Request $request)
    {
        // Extract request data into an array
        $requestData = $request->all();

        // Add or override the 'lang' key
        $requestData['lang'] = $request->header('lang', 'ar');

        // Call the service
        $requestData['make_type'] = 'app';
        $response = $this->orderService->store_v2($requestData, true, 'api');
        $responseData = $response->original;
        // Handle service response
        if (!$responseData['status']) {
            return $responseData;
        } else {
            $data = $responseData['data'];
        }

        // Return the response
        return ResponseWithSuccessData($this->lang, $data, 1);
    }

    public function Checkout(Request $request)
    {

        $lang = $request->header('lang', 'ar');
        // Token validation log
        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }
        $validated = Validator::make($request->all(), [
            'type' => 'required|in:Delivery,Takeaway,dine-in'
        ]);

        if ($validated->fails()) {
            return CustomRespondWithBadRequest($validated->errors()->first());
        }
        $branch_id = $request->branch_id;
        if (!$branch_id) {
            return CustomRespondWithBadRequest(trans('order.branch_required'));
        }
        $branch = Branch::find($branch_id);

        if (!$branch) {
            return CustomRespondWithBadRequest(trans('order.branch_not_exist'));
        }
        if (!$branch->is_active) {
            return CustomRespondWithBadRequest(trans('order.branch_not_active'));
        }


        $tax_application = getBranchSettings($branch_id, 'tax_application');
        $tax_percentage = getBranchSettings($branch_id, 'tax_percentage');
        $coupon_application = getBranchSettings($branch_id, 'coupon_application');
        $service_fees_value =  ($request['type'] != 'dine-in') ? 0 : getBranchSettings($branch_id, 'service_fees');
        $service_fees_type =  ($request['type'] != 'dine-in') ? 0 : getBranchSettings($branch_id, 'service_fees_type');
        $delivery_time = getBranchSettings($branch_id, 'delivery_time');
        $tax_apply = getBranchSettings($branch_id, 'tax_apply');
        $note = $request->note;
        $DataOrderDetails = $request->items;
        $coupon_code = $request->coupon_code;
        $type = $request->type;
        $coupon = null;
        $address_id = $request->address_id ?? null;
        $delivery = getDeliveryFees($address_id, $branch_id);


        // Attempt to find the client address
        $client_address = ClientAddress::find($address_id);
        if (!$client_address && $type != 'Takeaway' && $type != 'dine-in' && $type != 'reservation-table') {

            return CustomRespondWithBadRequest(trans('order.address_not_found'));
        }

        // Coupon validation log
        if ($coupon_code) {

            $coupon = GetCouponId($coupon_code, $branch_id);
            if ($coupon) {
                if (!CountCouponUsage($coupon->id)) {
                    DB::rollBack();

                    return RespondWithBadRequest($lang, 11);
                }
            } else {
                return RespondWithBadRequest($lang, 11);
            }
        }

        $lat_branch = $branch->latitute;
        $long_branch = $branch->longitute;
        if ($client_address &&  $type != 'Takeaway' && $type != 'dine-in' && $type != 'reservation-table') {

            $lat_address = $client_address->latitude;
            $long_address = $client_address->longtitude;
        }
        if ($type === 'Delivery' && (!$branch || !$branch->is_delivery)) {
            return CustomRespondWithBadRequest(trans('order.Branch_not_delivery', [], $lang));
        }

        $total_price_before_tax = 0;
        $total_price_after_tax = 0;
        $tax_value_total = 0;
        // Check if the address is within the delivery radius
        if ($type != 'Takeaway' && $type != 'dine-in' && $type != 'reservation-table') {

            $isInRadius = isInRadius($lat_address, $long_address, $lat_branch, $long_branch, 30);
            if (!$isInRadius) {

                return CustomRespondWithBadRequest(trans('order.address_delivery_redius'));
            }
        }
        $pickupTime = null;
        $selected_date = $request['selected_date'] ?? date('Y-m-d');
        $selected_time = $request['selected_time'] ?? date('H:i:s');
        if ($request['type'] === 'Takeaway') {

            $data_takeaway = (object) [
                'branch_id' => $branch_id,
                'date' => $selected_date,
                'pickup_time' => $selected_time
            ];

            $response = $this->branchSiteService->checkOrderCapacity($data_takeaway, $lang);
            // If not available, return an error response
            if (!$response['available']) {
                return respondError('error', 400,  ['error' => __('order.TimeBooked')]);
            }
            // $pickupTime = Carbon::parse(str_replace(['ص', 'م'], ['AM', 'PM'], "{$selected_date} {$selected_time}"));
        }


        $total_price_before_discount = 0;

        foreach ($DataOrderDetails as $index => $DataOrderDetail) {
            $Dish = BranchMenu::where('id', $DataOrderDetail['dish_id'])
                ->where('branch_id', $branch_id)
                ->where('is_active', 1)
                ->first();

            if ($Dish) {
                $items[$index]['dish_name'] = $Dish->dish->name;
                $price = $Dish->dish->has_sizes
                    ? optional(BranchMenuSize::find($DataOrderDetail['sizeId']))->price ?? 0
                    : $Dish->price;

                if (isset($DataOrderDetail['sizeId'])) {
                    $size = BranchMenuSize::find($DataOrderDetail['sizeId']);
                    $items[$index]['size_name'] = $size->dishSizes->name;
                }

                $dish_price = $price * $DataOrderDetail['quantity'];
                $addon_price = 0;

                if (!empty($DataOrderDetail['addon_categories'])) {
                    foreach ($DataOrderDetail['addon_categories'] as $addon_category) {
                        foreach ($addon_category['addon'] as $i => $addon_id) {
                            $addon = BranchMenuAddon::find($addon_id);
                            $items[$index]['addons'][$i]['addon_name'] = $addon->dishAddons->addons->name;
                            if ($addon) {
                                $addon_price += $addon->price * $DataOrderDetail['quantity'];
                            }
                        }
                    }
                }

                $total_price_before_discount += ($dish_price + $addon_price);

                $items[$index]['dish_price'] = $dish_price + $addon_price;
                $items[$index]['quantity'] = $DataOrderDetail['quantity'];
                $items[$index]['note'] = $DataOrderDetail['note'] ?? null;
            }
        }

        // Step 2: Apply coupon
        $coupon_value = 0;
        $total_after_coupon = $total_price_before_discount;

        if ($coupon) {
            $coupon_value = calcCoupon($total_price_before_discount, $coupon);
            $total_after_coupon = applyCoupon($total_price_before_discount, $coupon);
        }

        // Step 3: Service fee
        $total_service = $service_fees_type != 'fixed'
            ? ($total_after_coupon * ($service_fees_value / 100))
            : $service_fees_value;

        // Step 4: Tax
        $tax_value_total = $tax_application == 1
            ? CalculateTax($tax_percentage, $total_after_coupon + $total_service)
            : ($total_after_coupon + $total_service) * ($tax_percentage / 100);

        // Step 5: Final total
        $final_total = $total_after_coupon + $total_service + $tax_value_total;



        $preprationTime = collect($DataOrderDetails)
            ->map(function ($detail) {
                $BranchMenu = BranchMenu::find($detail['dish_id']);
                return $BranchMenu ? $BranchMenu->dish->time : 0; // Return only the time
            })
            ->max(); // Get the maximum time
        $payment_policy = getBranchPolicyPayment($branch_id, $type);

        $currency_symbol = $branch->country->currency_symbol;
        $response = [];
        if ($type == 'Takeaway') {
            $response['payment_policy'] = $payment_policy;
            $response['deposit_value'] = (float)  round($final_total * (getBranchSettings($branch_id, 'takeaway_deposit_value_if_1') / 100), 2);
            $response['view_meal_appiontment'] = [
                'full_payment_preparation_setting' => getBranchSettings($branch_id, 'full_payment_preparation_setting'),
                'deposit_preparation_setting' => getBranchSettings($branch_id, 'full_payment_preparation_setting'),
                'no_payment_preparation_setting' => getBranchSettings($branch_id, 'no_payment_preparation_setting')
            ];
            $reservation_policy = $this->branchService->reservation_policy();
            $response['policy'] = $reservation_policy;
        }
        // Prepare response
        $response = array_merge($response, [
            'currency_symbol' => $currency_symbol,
            'note' => $note,
            'cash_limit' => getCashPaymentPolicy($branch_id),
            'coupon_code' => $coupon ? $coupon_code : null,
            'total' => $final_total,
            'sub_total' => $total_price_before_discount,
            'total_after_coupon' => $total_after_coupon,
            'tax_value' => $tax_value_total,
            'tax_percentage' => "{$tax_percentage}%",
            'fees' => $service_fees_value,
            'delivery' => ($type == 'Takeaway' || $type == 'dine-in' || $type == 'reservation-table') ? 0 : $delivery,
            'coupon_value' => $coupon ? (float)$coupon_value : 0.0,
            'coupon_type' => $coupon ? $coupon->type : null,
            'delivery_time' => ($type == 'Takeaway' || $type == 'dine-in' || $type == 'reservation-table') ? $preprationTime : $preprationTime + $delivery_time,
            'service_fees_value' => $service_fees_value,
            'service_fees_type' => $service_fees_type
        ]);
        return ResponseWithSuccessData($lang, $response, 1);
    }
    public function cancel(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        return $order = $this->orderService->cancel($request);
    }

    public function reOrder(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        return $order = $this->orderService->reOrder($request, true);
    }

    public function paymentTransaction(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }
        return $transaction = $this->myFatoorahService->callbackApi($request);
    }
    public  function orderInvoice(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        return $order = $this->orderService->CalculateInvoiceOrder($id, true);
    }
    public  function orderRefund(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        return $order = $this->orderService->CalculateRefundOrder($id, true);
    }
}
