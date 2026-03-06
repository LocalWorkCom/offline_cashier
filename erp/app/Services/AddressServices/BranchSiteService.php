<?php

namespace App\Services\AddressServices;

use App\Events\Notification;
use App\Models\Branch;
use App\Models\BranchSetting;
use App\Models\BranchTime;
use App\Models\ClientAddress;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\Order;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use App\Services\GoogleMapsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class BranchSiteService
{
    // Current language for localization
    private $lang;

    /**
     * Constructor: Set the language for the service.
     */
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }
    public function getAreasByBranch($request, $branchId)
    {
        $branch = Branch::with('branchRegions.regions')->find($branchId);

        if (!$branch) {
            return [
                'status' => false,
                'message' => __('branch.branch_not_found'),
                'code' => 404
            ];
        }

        $areas = $branch->branchRegions->map(function ($branchRegion) {
            return [
                'id' => $branchRegion->region_id,
                'name' => $branchRegion->regions->name ?? null,
                'delivery_fees' => $branchRegion->delivery_fees ?? 0,
            ];
        })->filter(fn($area) => !empty($area['name']))->values();

        if ($areas->isEmpty()) {
            return [
                'status' => false,
                'message' => __('branch.no_areas_found'),
                'code' => 404
            ];
        }

        return  $areas;
    }


    /**
     * Check available time slots for a branch on a specific date.
     * Returns available slots or error messages if closed or unavailable.
     */
    public function checkAvailability($branchId, $dateString, $lang = 'ar')
    {
        App::setLocale($lang);

        $date = Carbon::parse($dateString);
        $now = Carbon::now();
        $isToday = $date->isToday();
        $day = $date->dayOfWeek;

        $branch = Branch::findOrFail($branchId);

        // Get working hours for the branch on the given day
        $branchTime = BranchTime::where('branch_id', $branchId)
            ->where('day', $day)
            ->first();

        if (!$branchTime) {
            return [
                'success' => false,
                'status' => 400,
                'message' => __('branch.closedbreanch')
            ];
        }

        $openingTime = Carbon::parse($branchTime->opening_hour);
        $closingTime = Carbon::parse($branchTime->closing_hour);

        // Handle cross-day closing (e.g., closes after midnight)
        if ($branchTime->cross_day) {
            $closingTime->addDay();
        }

        // Get session duration (minutes) for takeaway orders
        $sessionMinutes = BranchSetting::where('branch_id', $branchId)
            ->value('takeaway_session_minutes') ?? 30;

        if ($isToday) {
            $currentTime = $now->copy();

            if ($currentTime->lt($openingTime)) {
                $startTime = $openingTime->copy();
            } elseif ($currentTime->gte($closingTime)) {
                return [
                    'success' => false,
                    'status' => 400,
                    'message' => __('branch.closedbreanchtoday')
                ];
            } else {
                // Round up to the next available session slot
                $minutesToAdd = $sessionMinutes - ($currentTime->minute % $sessionMinutes);
                $startTime = $currentTime->copy()->addMinutes($minutesToAdd)->startOfMinute();

                if ($startTime->gte($closingTime)) {
                    return [
                        'success' => false,
                        'status' => 400,
                        'message' => __('branch.notimeavailabe')
                    ];
                }
            }
        } else {
            $startTime = $openingTime->copy();
        }

        $timeSlots = [];
        $currentSlot = $startTime->copy();

        Carbon::setLocale(app()->getLocale());

        // Generate all available time slots for the branch
        while ($currentSlot->lt($closingTime)) {
            $slotEnd = $currentSlot->copy()->addMinutes($sessionMinutes);

            if ($slotEnd->lte($closingTime)) {
                $timeSlots[] = [
                    'start' => $currentSlot->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'display' => $currentSlot->translatedFormat('h:i A') . ' - ' . $slotEnd->translatedFormat('h:i A')
                ];
            }

            $currentSlot->addMinutes($sessionMinutes);
        }

        // If no slots are available, return an error
        if (empty($timeSlots)) {
            return [
                'success' => false,
                'status' => 400,
                'message' => __('branch.notimeavailabetoday')
            ];
        }

        // Return available slots and branch info
        return [
            "status" => true,
            "message" => "Success Message",
            "code" => 200,
            'data' => [
                'is_today' => $isToday,
                'date' => $dateString,
                'session_minutes' => $sessionMinutes,
                'opening_time' => $openingTime->format('H:i'),
                'closing_time' => $closingTime->format('H:i'),
                'time_slots' => $timeSlots,
                'branch' => $branch
            ]
        ];
    }

    /**
     * Check if the branch can accept more takeaway orders at a specific time.
     * Returns availability and current order count.
     */
    public function checkOrderCapacity($request, $lang = 'ar')
    {
        App::setLocale($lang);
        $branchId =  $request->branch_id;
        $date =   $request->date;
        $pickupTime = $request->pickup_time;
        $branch = Branch::findOrFail($branchId);

        // Get current orders count for this time slot (excluding completed/cancelled)
        $ordersCount = Order::where('branch_id', $branchId)
            ->whereDate('takeaway_pickup_time', $date)
            ->whereTime('takeaway_pickup_time', $pickupTime)
            ->where('status', '!=', ['completed', 'cancelled'])
            ->count();

        // Get branch capacity for takeaway orders
        $capacity = BranchSetting::where('branch_id', $branchId)
            ->value('capacity_takeaway') ?? 10; // Default capacity

        return [
            'available' => $ordersCount < $capacity,
            'current_orders' => $ordersCount ?? null,
            'capacity' => $capacity ?? null,
            'branch' => $branch->only(['id', 'name', 'address', 'phone'])
        ];
    }

    /**
     * Search for branches based on location or region filters.
     * Returns the nearest or matching branch with available services.
     */
    public function searchBranches($params, $lang = 'ar')
    {
        App::setLocale($lang);

        $query = Branch::where('is_active', 1);
        // Search by latitude/longitude (nearest branch)
        if (!empty($params['latitude']) && !empty($params['longitude'])) {
            $lat = $params['latitude'];
            $lng = $params['longitude'];

            $query->select('*')
                ->selectRaw("(6371 * acos(cos(radians(?)) * cos(radians(latitute)) * cos(radians(longitute) - radians(?)) + sin(radians(?)) * sin(radians(latitute)))) AS distance", [$lat, $lng, $lat])
                ->whereNotNull('latitute')
                ->whereNotNull('longitute')
                ->orderBy('distance', 'asc');
        }
        // Search by country, city, and area
        elseif (!empty($params['country_id']) && !empty($params['city_id']) && !empty($params['area_id'])) {
            $query->where('country_id', $params['country_id'])
                ->where('city_id', $params['city_id'])
                ->where('area_id', $params['area_id']);

            // Optionally include branches by region
            if (!empty($params['branchesStatus'])) {
                $query->orWhereHas('branchRegions', function ($q) use ($params) {
                    $q->where('region_id', $params['area_id']);
                });
            }
        } else {
            // No valid search parameters
            return null;
        }

        // Get branches, prioritizing open branches
        $branches = !empty($params['branchesStatus'])
            ? $query->get()->sortByDesc(fn($b) => $b->is_open)
            : $query->where('is_takeaway', 1)->get()->sortByDesc(fn($b) => $b->is_open);

        if ($branches->isEmpty()) {
            return null;
        }

        $branch = $branches->first();

        // Build available services string
        $services = [];
        if ($branch->is_delivery) {
            $services[] = __("header.deliveryTo");
        }
        if ($branch->is_takeaway) {
            $services[] = __("header.pickup");
        }
        if ($branch->is_table_reservation) {
            $services[] = __("header.reservation");
        }

        $branch->services = implode(' - ', $services);
        $branch->takeaway = (bool) $branch->is_takeaway;
        $branch->working_times = getBranchWorkingHours($branch->id);

        return $branch;
    }

    /**
     * Check if a client address is within the delivery radius of a branch.
     * Returns status and message.
     */
    public function checkAllowDelivery($branchId, $addressId, $radius = 30)
    {
        $branch = Branch::find($branchId);
        $address = ClientAddress::find($addressId);

        if (!$branch || !$address) {
            return [
                'status' => false,
                'message' => 'Branch or address not found.',
                'code' => 404
            ];
        }
        if (!$branch->branchRegions->contains('region_id', $address->area_id)) {
            return [
                'status' => false,
                'message' => 'Address area does not match branch area.',
                'code' => 400
            ];
        } else {
            $isInRadius = true;
        }

        // $isInRadius = $this->isInRadius(
        //     $address->latitude,
        //     $address->longtitude,
        //     $branch->latitute,
        //     $branch->longitute,
        //     $radius
        // );

        return [
            'status' => $isInRadius,
            'message' => $isInRadius
                ? 'You are within the delivery radius.'
                : 'You are outside the delivery radius.',
            'code' => $isInRadius ? 200 : 400
        ];
    }

    /**
     * Helper: Calculate if two coordinates are within a given radius (km).
     */
    function isInRadius($lat1, $lng1, $lat2, $lng2, $radius)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance <= $radius;
    }
    public function getGeolocationDetails($address)
    {
        $apiKey = 'AIzaSyDgewbk6uYuyvCImG5r5wl0wRuDVaQrFg8';
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $apiKey;

        $response = Http::withOptions([
        'verify' => false, // تجاهل مشكلة ملف cacert.pem
    ])->get($url)->json();

        if (!empty($response['results'])) {
            $location = $response['results'][0]['geometry']['location'];
            $components = $response['results'][0]['address_components'];
            $result = [
                'latitude' => $location['lat'],
                'longitude' => $location['lng'],
                'country' => null,
                'city' => null,
                'area' => null,
            ];

            foreach ($components as $component) {
                if (in_array('country', $component['types'])) {
                    $result['country'] = $component['long_name'];
                }
                if (in_array('locality', $component['types'])) {
                    $result['city'] = $component['long_name'];
                }
                if (in_array('sublocality', $component['types']) || in_array('administrative_area_level_2', $component['types'])) {
                    $result['area'] = $component['long_name'];
                }
            }

            return $result;
        }
        return null;
    }

    public function storeAddress($request, $employee)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $user = User::find(3);

        $phone_length = Country::where('phone_code', $request->country_code)->value('length');
        $phone_length_whatsapp = Country::where('phone_code', $request->whatsapp_number_code)->value('length');

        if (!$employee) {
            return respondWithBadRequest($lang, 4);
        }

        $validator = Validator::make($request->all(), [
            'address' => 'required_if:address_type,apartment,villa,office|string',
            'address_type' => 'required|in:apartment,villa,office,hotel',
            'building' => 'nullable',
            'hotel_id' => ['required_if:address_type,hotel', 'exists:hotels,id'],
            'floor_number' => 'nullable',
            'apartment_number' => 'nullable',
            'notes' => 'nullable|string',
            'country_code' => 'required',
            'address_phone' => 'required|numeric',
            'whatsapp_number_code' => 'nullable',
            'whatsapp_number' => 'nullable|numeric',
            'client_name' => 'required|string',
            'area_id' => 'required|exists:areas,id',
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
        if ($phone_length_whatsapp && strlen($request->whatsapp_number) != $phone_length_whatsapp) {
            return respondError('Validation Error.', 400, [
                'whatsapp_number' => [
                    __('validation.custom.whatsapp_number.length', [
                        'attribute' => __('Validation.attributes.whatsapp_number'),
                        'length' => $phone_length_whatsapp,
                    ])
                ]
            ]);
        }


        $coordinates = $this->getGeolocationDetails($request->address);
        $unknown_user = User::where('flag', 'unknown')->value('id');
        ClientAddress::where('country_id', $employee->branch->country_id)->where('address_phone', $request->address_phone)->update(['is_default' => 0]);

        $address = new ClientAddress();
        if ($request->address_type === 'hotel') {
            $hotel = Hotel::find($request->hotel_id);
            if (!$hotel) {
                return respondWithBadRequest($lang, 22);
            }

            $address->hotel_id = $request->hotel_id;
             $address->address = $request->address;
            // $address->city_id = $hotel->city_id;
            // $address->area_id = $hotel->area_id;
            $address->country_id = $employee->branch->country_id ?? null;
            // $address->address = $hotel->address_ar;
            // $address->building = $hotel->building_number;
        } else {
            $address->city_id = $employee->branch->city_id ?? null;
            // $address->hotel_id = $request->hotel_id;

            $address->country_id = $employee->branch->country_id ?? null;
            $address->address = $request->address;
            $address->building = $request->building;
        }
        // $address->city = $coordinates['city'] ?? 'city';
        // $address->state = $coordinates['area'] ?? 'area';
        // $address->city_id = $employee->branch->city_id ?? null;
        // $address->area_id = $request->area_id;
        // // $address->hotel_id = $request->hotel_id;

        // $address->country_id = $employee->branch->country_id ?? null;
        // $address->postal_code = $coordinates['postal_code'] ?? null;
        $address->latitude = $coordinates['latitude'] ?? 30.0689;
        $address->longtitude = $coordinates['longitude'] ?? 31.2357;
        $address->address_type = $request->address_type;
        $address->floor_number = $request->floor_number;
        $address->apartment_number = $request->apartment_number;
        $address->notes = $request->notes;
        $address->country_code = $request->country_code;
        $address->address_phone = $request->address_phone;
        $address->whatsapp_number_code = $request->whatsapp_number_code;
        $address->whatsapp_number = $request->whatsapp_number;
        $address->user_id = $unknown_user;
        $address->area_id = $request->area_id;
        $address->user_name = $request->client_name;
        $address->created_by = $employee->id;
        $address->is_default = 1;
        $address->is_active = 1;
        $address->save();
        return response()->json([
            'code' => 200,
            'status' => true,
            'message' => __('messages.success'),
            'data' => [
                'address_id' => $address->id
            ],
        ]);
    }
}
