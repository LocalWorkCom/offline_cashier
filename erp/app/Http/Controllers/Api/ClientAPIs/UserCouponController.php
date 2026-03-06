<?php

namespace App\Http\Controllers\Api\ClientAPIs;

use App\Models\Dish;
use App\Models\Offer;
use App\Models\UserCoupon;
use App\Services\KitchenServices\DishCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\BranchMenu;
use Illuminate\Support\Facades\Auth;

class UserCouponController extends Controller
{

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        if (CheckToken()) {
            $status = $request->query('type');

            $couponsQuery = UserCoupon::whereHas('coupon', function ($query) use ($status) {
                if (!is_null($status)) {
                    $query->where('status', $status);
                }
            })->where('user_id', auth('api')->user()->id);

            $coupons = $couponsQuery->get();

            if (!$coupons) {
                return RespondWithBadRequestData($lang, 2);
            }

            foreach ($coupons as $coupon) {
                $code = $coupon->coupon->code;
                $type = $coupon->coupon->type;
                $value = $coupon->coupon->value;
                $min_spend = $coupon->coupon->minimum_spend;
                $end_date = $coupon->coupon->end_date;

                $coupon->code = $code;
                $coupon->type = $type;
                $coupon->value = $value;
                $coupon->minimum_spend = $min_spend;
                $coupon->end_date = $end_date;
                $coupon->makeHidden('coupon');
            }

            if ($coupons->isEmpty()) {
                $coupons = null;
            }

            return ResponseWithSuccessData($lang, $coupons, 1);
        }

        return RespondWithBadRequest($lang, 4);
    }


    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        if(CheckToken())
        {
            $coupon = UserCoupon::where('user_id', auth('api')->user()->id)->whereHas('coupon')->find($id) ?? null;
            if(!$coupon)
            {
                return RespondWithBadRequestData($lang, 2);
            }
            $code = $coupon->coupon->code;
            $type = $coupon->coupon->type;
            $value = $coupon->coupon->value;
            $min_spend = $coupon->coupon->minimum_spend;
            $end_date = $coupon->coupon->end_date;
            $coupon->code = $code;
            $coupon->type = $type;
            $coupon->value = $value;
            $coupon->minimum_spend = $min_spend;
            $coupon->end_date = $end_date;
            $coupon->makeHidden('coupon');
            if ($coupon->isEmpty()) {
                $coupon = null;
            }

            return ResponseWithSuccessData($lang, $coupon, 1);
        }
        return RespondWithBadRequest($lang, 4);
    }

    public function apply(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        if(CheckToken())
        {
            $coupon = UserCoupon::where('user_id', auth('api')->user()->id)->whereHas('coupon')->find($id) ?? null;
            if(!$coupon)
            {
                return RespondWithBadRequestData($lang, 2);
            }
            if ($coupon->status == 'active')
            {
                $coupon->status = 'used';
                $coupon->save();
            }else if ($coupon->status == 'used' || $coupon->status == 'inactive')
            {
                return RespondWithBadRequestData($lang, 11);
            }
            $code = $coupon->coupon->code;
            $type = $coupon->coupon->type;
            $value = $coupon->coupon->value;
            $min_spend = $coupon->coupon->minimum_spend;
            $coupon->code = $code;
            $coupon->type = $type;
            $coupon->value = $value;
            $coupon->minimum_spend = $min_spend;
            $coupon->makeHidden('coupon');

            return ResponseWithSuccessData($lang, $coupon, 1);
        }
        return RespondWithBadRequest($lang, 4);

    }
}
