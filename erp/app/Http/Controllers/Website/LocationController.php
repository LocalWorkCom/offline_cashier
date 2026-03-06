<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Branch;
use App\Models\BranchRegion;
use App\Models\BranchSetting;
use App\Models\BranchTime;
use App\Models\ClientAddress;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\Order;
use App\Services\AddressServices\BranchSiteService;
use App\Services\AddressServices\LocationDataService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    protected $locationService;
    protected $branchService;


    public function __construct(LocationDataService $locationService, BranchSiteService $branchService)
    {
        $this->locationService = $locationService;
        $this->branchService = $branchService;
    }

    public function getByArea($area_id)
    {
        $hotels = Hotel::query()->where('area_id', $area_id)
            ->get()
            ->map(fn($hotel) => [
                'id'         => $hotel->id,
                'name'       => $hotel->name,
                'address'    => $hotel->address,
                'country_id' => $hotel->country_id,
                'city_id'    => $hotel->city_id,
                'area_id'    => $hotel->area_id,
            ]);

        return response()->json([
            'status' => true,
            'data' => $hotels
        ]);
    }
    public function setNewAddressSession(Request $request)
    {
        session(['new_address_id' => $request->addressId]);
        return response()->json(['status' => 'success']);
    }


    public function checkAreasBranch($Id)   // $areaId == region_id in branch_regions
    {
        $cookieBranchId = request()->cookie('branch_id');   // may be null

        /* 1️⃣  Area served by the branch in the cookie? */
        $namearea = Area::where('id', $Id)->first()->name_en;
        $inCurrentBranch = BranchRegion::where([
            ['region_id',  $Id],
            ['branch_id',  $cookieBranchId],
            ['is_active', 1],
        ])->first();

        if ($inCurrentBranch) {
            return response()->json([
                'status'   => 'served_by_current_branch',
                'is_current_branch' => true,
                'namearea' => $namearea,
                'branch'   => $inCurrentBranch->branch_id,
                'fees'     => $inCurrentBranch->delivery_fees,
            ]);
        }

        /* 2️⃣  Area served by some other active branch? */
        $inAnotherBranch = BranchRegion::where('region_id',  $Id)
            ->where('is_active', 1)
            ->first();

        if ($inAnotherBranch) {
            return response()->json([
                'status'   => 'served_by_another_branch',
                'is_current_branch' => false,
                'namearea' => $namearea,

                'branch'   => $inAnotherBranch->branch_id,
                'fees'     => $inAnotherBranch->delivery_fees,
            ]);
        }

        /* 3️⃣  Area not served at all */
        return response()->json([
            'status'  => 'not_served',
            'message' => 'No active branch delivers to this area.',
        ], 404);
    }

    public function getCities($countryId)
    {
        $language = app()->getLocale();
        $cities = $this->locationService->getCitiesByCountry($countryId, $language);

        return response()->json($cities);
    }

    public function getAreas($cityId)
    {
        $areas = $this->locationService->getAreasByCity($cityId);

        return response()->json($areas);
    }
    public function checkAvailability(Request $request)
    {
        $branchId = $request->branch_id;
        $dateString = $request->date;

        // Call the service method
        $result = $this->branchService->checkAvailability($branchId, $dateString);

        // Return JSON response accordingly
        return response()->json(
            $result['status']
                ? ['success' => true, 'data' => $result['data']]
                : ['success' => false, 'message' => $result['message']],
            $result['code'] // ✅ use actual HTTP code here
        );
    }


    public function checkOrderCapacity(Request $request)
    {
        $lang = App::getLocale();

        $response = $this->branchService->checkOrderCapacity($request, $lang);
        return response()->json($response);
    }
    public function searchBranches(Request $request)
    {
        $lang = App::getLocale();
        $branch = $this->branchService->searchBranches($request->all(), $lang);

        if (!$branch) {
            return response()->json(null);
        }

        return response()->json($branch);
    }


    public function showAddress()
    {
        $lang = app()->getLocale();


        $address = ClientAddress::with('hotel')->where('user_id', Auth::guard('client')->user()->id)->where('is_active', 1)->whereNull('deleted_at')
            ->withCount([
                'orders as has_inprogress_or_pending_orders' => function ($query) {
                    $query->whereIn('status', ['inprogress', 'pending']);
                }
            ])
            ->get();

        return view('website.auth.address', compact('address'));
    }

    public function createAddress($id = null)
    {
        $secondPhase = false;

        if (request()->query('open_second_phase', false)) {
            $secondPhase = true;
        }

        $lang = app()->getLocale();
        $isEdit = false;
        $isDelivery = request()->query('is_delivery', false);
        $issecondPhase = request()->query('open_second_phase', false);
        $hotels = $this->locationService->all($lang);
        $address = $id ? ClientAddress::find($id) : null;
        if ($id) {
            $isEdit = true;
        }
        return view('website.auth.create-address', compact('address', 'secondPhase', 'hotels', 'issecondPhase', 'isEdit', 'isDelivery'));;
    }



    public function createOrUpdateAddress(Request $request)
    {
        $userId = Auth::guard('client')->user()->id;
        // Update all other addresses for this user to is_default = 0
        // Fetch existing address or create a new one if no ID is provided
        $address = $request->id ? ClientAddress::findOrFail($request->id) : new ClientAddress();

        // Check if the latitude and longitude already exist for this user
        $existingAddress = ClientAddress::where('user_id', $userId)
            ->where('latitude', $request->latitude)
            ->where('longtitude', $request->longitude)
            ->first();
        $branchId = $request->cookie('branch_id');

        if ($existingAddress && !$request->id) {
            $area = $existingAddress->area_id;
            session(['nolat' => true]);
            if ($area && $branchId && BranchRegion::where('branch_id', $branchId)->where('region_id', $area)->exists()) {
                // If the address already exists and is in the same area as the current branch, set session variables
                session(['new_address_id' => $existingAddress->id]);
            }
             else {
                $address = ClientAddress::where('user_id', $userId)
                    ->where('area_id', BranchRegion::where('branch_id', $branchId)->first()->region_id)
                    ->first();
                session(['new_address_id' => $address->id]);
            }
            // If the address already exists, return the existing address ID with a message
            return redirect()->route('cart')->with([
                'message' => 'This location is already saved.',
                'existing_address_id' => $existingAddress->id,
            ]);
        }
        if (!$request->id && ClientAddress::where('user_id', $userId)->count() == 0) {
            $request->merge(['is_default' => 1]);
        } else {
            if (BranchRegion::where('branch_id', $branchId)->where('region_id', $request->area_id)->exists()) {
                ClientAddress::where('user_id', $userId)->update(['is_default' => 0]);
            }
        }
        // Initialize validation rules and messages
        $rules = $messages = [];
        // Set validation rules based on the selected delivery place.

        switch ($request->deliveryPlace) {
            case 'apartment':
                $phone_length = Country::where('phone_code', $request->country_code_apart)->value('length');

                $rules = [
                    'nameapart' => 'nullable|string|max:255',
                    'numapart' => 'nullable',
                    'floorapart' => 'nullable',
                    'phoneapart' => [
                        'required',
                        'numeric',
                        function ($attribute, $value, $fail) use ($phone_length) {
                            if ($phone_length && strlen($value) != $phone_length) {
                                $fail(__('validation.custom.phone.length', [
                                    'attribute' => __('auth.phone'),
                                    'length' => $phone_length,
                                ]));
                            }
                        },
                    ],
                    'country_code_apart' => 'required',
                    'addressdetailapart' => 'required',
                    'markapart' => 'nullable',
                ];

                $messages = [
                    'nameapart.required' => __('validation.required', ['attribute' => __('auth.nameapart')]),
                    'numapart.required' => __('validation.required', ['attribute' => __('auth.numapart')]),
                    // 'numapart.numeric' => __('validation.numeric', ['attribute' => __('auth.numapart')]),
                    // 'floorapart.numeric' => __('validation.numeric', ['attribute' => __('auth.floorapart')]),
                    'floorapart.required' => __('validation.required', ['attribute' => __('auth.floorapart')]),
                    'phoneapart.required' => __('validation.required', ['attribute' => __('auth.phoneapart')]),
                    'phoneapart.numeric' => __('validation.numeric', ['attribute' => __('auth.phoneapart')]),
                    'country_code_apart.required' => __('validation.required', ['attribute' => __('auth.country_code_apart')]),
                    'addressdetailapart.required' => __('validation.required', ['attribute' => __('auth.addressdetailapart')]),
                ];
                break;

            case 'villa':
                $phone_length = Country::where('phone_code', $request->country_code_villa)->value('length');

                $rules = [
                    'namevilla' => 'nullable|string|max:255',
                    'villanumber' => 'nullable',
                    'addressdetailvilla' => 'required',
                    'phonevilla' => [
                        'required',
                        'numeric',
                        function ($attribute, $value, $fail) use ($phone_length) {
                            if ($phone_length && strlen($value) != $phone_length) {
                                $fail(__('validation.custom.phone.length', [
                                    'attribute' => __('auth.phone'),
                                    'length' => $phone_length,
                                ]));
                            }
                        },
                    ],
                    'country_code_villa' => 'required',
                    'markvilla' => 'nullable',
                ];

                $messages = [
                    'namevilla.required' => __('validation.required', ['attribute' => __('auth.namevilla')]),
                    'villanumber.required' => __('validation.required', ['attribute' => __('auth.villanumber')]),
                    // 'villanumber.numeric' => __('validation.numeric', ['attribute' => __('auth.villanumber')]),
                    'addressdetailvilla.required' => __('validation.required', ['attribute' => __('auth.addressdetailvilla')]),
                    'phonevilla.required' => __('validation.required', ['attribute' => __('auth.phonevilla')]),
                    'phonevilla.numeric' => __('validation.numeric', ['attribute' => __('auth.phonevilla')]),
                    'country_code_villa.required' => __('validation.required', ['attribute' => __('auth.country_code_villa')]),
                ];
                break;

            case 'hotel':
                $phone_length = Country::where('phone_code', $request->country_code_hotel)->value('length');

                $rules = [
                    'hotel_id' => 'required|numeric',
                    'roomnumber' => 'required',
                    'phonehotel' => [
                        'required',
                        'numeric',
                        function ($attribute, $value, $fail) use ($phone_length) {
                            if ($phone_length && strlen($value) != $phone_length) {
                                $fail(__('validation.custom.phone.length', [
                                    'attribute' => __('auth.phone'),
                                    'length' => $phone_length,
                                ]));
                            }
                        },
                    ],
                    'country_code_hotel' => 'required',
                    'markhotel' => 'nullable',
                ];

                $messages = [
                    'hotelname.required' => __('validation.required', ['attribute' => __('auth.hotelname')]),
                    'roomnumber.required' => __('validation.required', ['attribute' => __('auth.roomnumber')]),
                    'phonehotel.required' => __('validation.required', ['attribute' => __('auth.phonehotel')]),
                    'phonehotel.numeric' => __('validation.numeric', ['attribute' => __('auth.phonehotel')]),
                    'country_code_hotel.required' => __('validation.required', ['attribute' => __('auth.country_code_hotel')]),
                ];
                break;

            case 'office':
                $phone_length = Country::where('phone_code', $request->country_code_office)->value('length');

                $rules = [
                    'nameoffice' => 'nullable|string|max:255',
                    'numaoffice' => 'nullable',
                    'flooroffice' => 'nullable',
                    'phoneoffice' => [
                        'required',
                        'numeric',
                        function ($attribute, $value, $fail) use ($phone_length) {
                            if ($phone_length && strlen($value) != $phone_length) {
                                $fail(__('validation.custom.phone.length', [
                                    'attribute' => __('auth.phone'),
                                    'length' => $phone_length,
                                ]));
                            }
                        },
                    ],
                    'country_code_office' => 'required',
                    'addressdetailoffice' => 'required',
                    'markoffice' => 'nullable',
                ];

                $messages = [
                    'nameoffice.required' => __('validation.required', ['attribute' => __('auth.nameoffice')]),
                    'numaoffice.required' => __('validation.required', ['attribute' => __('auth.numaoffice')]),
                    // 'numaoffice.numeric' => __('validation.required', ['attribute' => __('auth.numaoffice')]),
                    // 'flooroffice.numeric' => __('validation.numeric', ['attribute' => __('auth.flooroffice')]),
                    'flooroffice.required' => __('validation.required', ['attribute' => __('auth.flooroffice')]),
                    'phoneoffice.required' => __('validation.required', ['attribute' => __('auth.phoneoffice')]),
                    'phoneoffice.numeric' => __('validation.numeric', ['attribute' => __('auth.phoneoffice')]),
                    'country_code_office.required' => __('validation.required', ['attribute' => __('auth.country_code_office')]),
                    'addressdetailoffice.required' => __('validation.required', ['attribute' => __('auth.addressdetailoffice')]),
                ];
                break;
        }

        // Validate the request with the dynamic rules and messages
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }


        // ClientAddress::where('user_id', $userId)->update(['is_default' => 0]);
        $area = Area::with(['city', 'city.country'])
            ->where('id', $request->area_id)
            ->first();
        // Set common fields
        $address->user_id = $userId;
        $address->country_code = $area->city->country->phone_code;
        $address->latitude = $request->latitude;
        $address->longtitude = $request->longitude;
        $address->is_default = $request->id ? $address->is_default : 1;
        $address->country_id = $area->city->country->id;
        $address->city_id = $area->city->id;
        $address->area_id = $request->area_id;

        // Map address fields based on deliveryPlace type (apartment, villa, office)
        $this->mapAddressFields($address, $request);

        // Save the address
        $address->save();

        if ($address->area_id && $branchId && BranchRegion::where('branch_id', $branchId)->where('region_id', $address->area_id)->exists()) {
            $address_session = $address->id;
        } else {
            $address_new = ClientAddress::where('user_id',  $userId)
                ->where('area_id', BranchRegion::where('branch_id', $branchId)->first()->region_id)
                ->first();
            if ($address_new) {
                $address->is_default = 0;
                $address->save();
                return redirect()->route('showAddress')->with(['show_address_no' => true]);
            }

            $address_session = $address_new->id;
        }
        // Redirect accordingly
        if ($request->has('is_delivery') && $request->is_delivery === 'true') {
            session(['new_address_id' => $address_session]);
            return redirect()->route('menu')->with(['showModal' => true]);
        }
        if ($request->has('is_cart') && $request->is_cart === 'true') {
            session(['nolat' => true]);
            session(['new_address_id' => $address_session->id]);

            return redirect()->route('cart')->with(['showModal' => true]);
        }
        session(['new_address_id' => $address_session]);
        return redirect()->route('showAddress')->with(['showModal' => true]);
    }
    private function mapAddressFields(ClientAddress $address, Request $request)
    {
        switch ($request->deliveryPlace) {
            case 'apartment':
                $address->address_type = 'apartment';
                $address->building = $request->input('nameapart');
                $address->floor_number = $request->input(key: 'floorapart');
                $address->apartment_number = $request->input('numapart');
                $address->country_code = $request->input('country_code_apart');
                $address->address_phone = $request->input('phoneapart');
                $address->address = $request->input('addressdetailapart');
                $address->notes = $request->input('markapart');

                break;

            case 'villa':
                $address->address_type = 'villa';
                $address->building = $request->input('namevilla');
                $address->apartment_number = $request->input('villanumber');
                $address->country_code = $request->input('country_code_villa');
                $address->address_phone = $request->input('phonevilla');
                $address->address = $request->input('addressdetailvilla');
                $address->notes = $request->input('markvilla');
                break;

            case 'hotel':
                $address->address_type = 'hotel';
                $address->hotel_id = $request->input('hotel_id');
                $address->apartment_number = $request->input('roomnumber');
                $address->address_phone = $request->input('phonehotel');
                $address->country_code = $request->input('country_code_hotel');
                $address->notes = $request->input('markhotel');
                break;

            case 'office':
                $address->address_type = 'office';
                $address->building = $request->input('nameoffice');
                $address->floor_number = $request->input('flooroffice');
                $address->apartment_number = $request->input('numaoffice');
                $address->country_code = $request->input('country_code_office');
                $address->address_phone = $request->input('phoneoffice');
                $address->address = $request->input('addressdetailoffice');
                $address->notes = $request->input('markoffice');
                break;
        }
    }
    public function destroyAddress($id)
    {
        // Check if the address is associated with any in-progress or pending orders
        $check = Order::where('client_address_id', $id)
            ->where(function ($query) {
                $query->where('status', 'inprogress')
                    ->orWhere('status', 'pending');
            })
            ->exists();

        if ($check) {
            return redirect()->route('showAddress')->with('error', __('Address cannot be deleted because it is associated with an in-progress or pending order.'));
        }
        $address = ClientAddress::find($id);
        // Find the address to be deleted
        $hasAnotherDefaultAddress = ClientAddress::where('user_id', Auth::guard('client')->id())
            ->where('id', '!=', $id) // Exclude the address being deleted
            ->where('is_default', 1) // Check if it's a default address
            ->exists();
        // Check if the address being deleted is the default address
        if (!$hasAnotherDefaultAddress) {
            // Find another address for the same client to set as default
            $anotherAddress = ClientAddress::where('user_id', $address->user_id)->whereNull('deleted_at')
                ->where('id', '!=', $id)
                ->first();
            // dd($anotherAddress);
            if ($anotherAddress) {
                // Set the other address as default
                $anotherAddress->is_default = 1;
                $anotherAddress->save();
            } else {
                // If no other address exists, prevent deletion
                return redirect()->back()->with('error', __('Address cannot be deleted because it is the only address and is set as default.'));
            }
        }

        // Soft delete the address
        $address->deleted_at = now();
        $address->is_default = 0;

        $address->save();

        return redirect()->back()->with('success', __('Address deleted successfully.'));
    }
    public function defaultAddress($id)
    {
        // Find the address by ID
        $address = ClientAddress::findOrFail($id);

        if ($address) {
            // Set all other addresses for the same user to default 0
            ClientAddress::where('user_id', $address->user_id)
                ->where('id', '!=', $id)
                ->update(['is_default' => 0]);

            // Set the selected address as default
            $address->is_default = 1;
            $address->save();
        }

        return redirect()->route('showAddress')->with('success', __('Address default successfully.'));
    }
    public function getNearestBranchl(Request $request)
    {

        $userLat = $request->input('latitude');
        $userLon = $request->input('longitude');

        $nearestBranch = getNearestBranch($userLat, $userLon);
        return response()->json([
            'branch' => $nearestBranch
        ]);
    }
    public function checkAllowDelivery(Request $request)
    {
        $response = $this->branchService->checkAllowDelivery(
            $request->branchId,
            $request->address
        );

        if ($response['status']) {
            session()->forget('new_address_id');
            session(['new_address_id' => $request->address]);
        }

        return response()->json(['message' => $response['message']], $response['code']);
    }


    public function getAddressDetail(Request $request)
    {
        $address = ClientAddress::find($request->id);
        if ($address) {
            return response()->json(['data' => $address], 200);
        } else {
            return response()->json(['data' => null], 400);
        }
    }
    function getBranchDetails($branchId, $lat, $long)
    {
        $branch = Branch::select('latitude', 'longitude')->find($branchId);
        if ($branch) {
            $isInRadius = isInRadius($lat, $long, $branch->latitude, $branch->longitude, 10);
            return $isInRadius;
        }
        return false; // Return null if branch not found
    }
}
