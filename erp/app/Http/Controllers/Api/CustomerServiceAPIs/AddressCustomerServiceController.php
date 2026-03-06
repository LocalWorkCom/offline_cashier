<?php

namespace App\Http\Controllers\Api\CustomerServiceAPIs;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Website\LocationController;
use App\Models\BranchRegion;
use App\Models\ClientAddress;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\User;
use App\Services\AddressServices\BranchSiteService;
use App\Traits\AddressTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;

class AddressCustomerServiceController extends Controller
{
    use AddressTrait;
     protected $googleMapsService;
    protected $locationService;

    protected $branchSiteService;

    public function __construct(BranchSiteService $branchSiteService)
    {
        $this->branchSiteService = $branchSiteService;
    }
    public function check(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $phone_length = Country::where('phone_code', $request->country_code)->value('length');
        $employee = auth('employee')->user();
        $employee_branch = auth('employee')->user()->branch_id;

        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $validator = Validator::make($request->all(), [
            'country_code' => 'required',
            'address_phone' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }

        if ($phone_length && strlen($request->address_phone) != $phone_length) {
            return respondError('Validation Error.', 400, [
                'address_phone' => [
                    __('validation.custom.phone.length', [
                        'attribute' => __('auth.phone'),
                        'length' => $phone_length,
                    ])
                ]
            ]);
        }

        $addresses = ClientAddress::where('country_code', $request->country_code)
            ->where('address_phone', $request->address_phone)
            ->get();

        if ($addresses->isEmpty()) {
            return respondError(__('auth.address_not_found'), 404);
        }

        // unique area IDs
        $areaIds = $addresses->pluck('area_id')->unique();

        // map region_id → delivery_fees for THIS branch
        $fees = BranchRegion::where('branch_id', $employee_branch)
            ->whereIn('region_id', $areaIds)
            ->pluck('delivery_fees', 'region_id');

        // append fee + hotels data to each address
        $addressesWithFee = $addresses->map(function ($addr) use ($fees, $lang) {
            $addr->delivery_fees = $fees[$addr->area_id] ?? null;

            if ($addr->hotel_id) {
                $hotel = Hotel::where('id', $addr->hotel_id)
                    ->select('id', 'name_ar', 'name_en')
                    ->first();

                // Create a custom hotel object with only id and name
                $customHotel = [
                    'id' => $hotel->id,
                    'name' => $lang == 'ar' ? $hotel->name_ar : $hotel->name_en
                ];

                $addr->hotels = [$customHotel]; // Wrap in array
            } else {
                $addr->hotels = []; // empty array if no hotel
            }

            return $addr;
        });

        return ResponseWithSuccessData($lang, $addressesWithFee, 1);
    }

    public function store(Request $request)
    {
        $employee = auth('employee')->user();

        $result = $this->branchSiteService->storeAddress($request, $employee);

        return  $result;
    }

    public function checkDelivery(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $phone_length = Country::where('phone_code', $request->country_code)->value('length');
        $employee = auth('employee')->user();
        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }
        $address = ClientAddress::find($id);
        if (!$address) {
            return respondError('Validation Error.', 400, ['error' => __('order.address_not_found')]);
        }
        $locationController = app(BranchSiteService::class);
        $request = new Request(['branchId' => auth('employee')->user()->branch_id, 'address' => $id]);
        $is_allow = $locationController->checkallowdelivery(auth('employee')->user()->branch_id, $id);
        if ($is_allow['status']) {
            return RespondWithSuccessMsg(__('order.allow_delivery'));
        } else {
            return respondError('Validation Error.', 400, ['error' => __('order.branch_not_allow')]);
        }
    }
}
