<?php

namespace App\Http\Controllers\Api\CashierAPIs;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Order;
use App\Models\SettingDelivery;
use App\Models\Vehicle;
use App\Models\VehicleSetting;
use App\Services\HR_Services\TimetableService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    protected $timeTableService;

    // Inject the service via constructor
    public function __construct(TimetableService $timeTableService)
    {
        $this->timeTableService = $timeTableService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $today = Carbon::now()->format('Y-m-d');
        $user = auth('employee')->user();

        if (!(CheckTokenEmployee() && ($user->flag == 'cashier' || $user->flag == 'customer_service'))) {
            return RespondWithBadRequest($lang, 4);
        }


        if (CheckTokenEmployee() && ($user->flag == 'cashier' || $user->flag == 'customer_service')) {
            $deliveries = Employee::whereNull('deleted_at')
                ->where('flag', 'driver')
                ->where('branch_id', $user->branch_id)
                ->get();

            if ($deliveries->isEmpty()) {
                return ResponseWithSuccessData($lang, null, 1);
            }

            $driverIds = $deliveries->pluck('id')->toArray();
            $cashierShift = $this->timeTableService->getTimetableForDate($user->id, $today);
            $shiftId = $cashierShift['data']['timetable']->id ?? null;
            $filteredDriverIds = [];

            foreach ($driverIds as $driverId) {
                $driverShift = $this->timeTableService->getTimetableForDate($driverId, $today);

                if ($driverShift['status'] && isset($driverShift['data']['timetable']) && isset($driverShift['data']['timetable']->id)) {
                    $filteredDriverIds[] = $driverId;
                }
            }

            if (!empty($filteredDriverIds)) {
                // Retrieve matching drivers
                $matchingDrivers = Employee::whereIn('id', $filteredDriverIds)
                    ->select([
                        'id',
                        'employee_code',
                        'first_name',
                        'last_name',
                        'email',
                        'country_code',
                        'phone_number',
                        'branch_id',
                        'vehicle_id',
                        'image'
                    ])
                    ->get();

                // Retrieve orders count for each driver
                $ordersPerDriver = Order::whereIn('delivery_id', $filteredDriverIds)
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->selectRaw('delivery_id, COUNT(*) as order_count')
                    ->groupBy('delivery_id')
                    ->pluck('order_count', 'delivery_id') // <-- key = delivery_id, value = order_count
                    ->toArray();
                // Retrieve vehicle max orders
                $vehicleSettings = Vehicle::with('type')
                    ->whereIn('id', $matchingDrivers->pluck('vehicle_id')->toArray())
                    ->get()
                    ->mapWithKeys(function ($vehicle) {
                        return [
                            $vehicle->id => [
                                'name' => $vehicle->type->vehicle_type ?? null,
                                'vehicle_max' => $vehicle->type->vehicle_max ?? null,
                            ]
                        ];
                    })
                    ->toArray();


                // dd($vehicleSettings);
                $cashierShift = $this->timeTableService->getTimetableForDate($user->id, $today);
                if (!($cashierShift['status'] == false)) {
                    $cashierShiftType = $lang == 'en' ?  $cashierShift['data']['timetable']['name_en'] : $cashierShift['data']['timetable']['name_ar'];
                    $cashierShiftStart = $cashierShift['data']['on_duty_time'];
                    $endStart = $cashierShift['data']['off_duty_time'];
                    $user->shift_start = $cashierShiftStart;
                    $user->shift_end = $endStart;
                    $user->shift_type = $cashierShiftType;
                } else {
                    $user->shift_start = null;
                    $user->shift_end = null;
                    $user->shift_type = null;
                }
                // Filter & update availability for each driver
                $filteredDrivers = $matchingDrivers->filter(function ($driver) use ($ordersPerDriver, $vehicleSettings, $user, $lang) {
                    $vehicleId = $driver->vehicle_id;
                    $orderCount = $ordersPerDriver[$driver->id] ?? 0;
                    $driver->order_count = $orderCount ?? 0;
                    $vehicleSetting = $vehicleSettings[$vehicleId] ?? null;
                    $vehicleType = $vehicleSetting['name'] ?? null;
                    $maxOrders   = $vehicleSetting['vehicle_max'] ?? null;

                    $driver->branch = Branch::find($driver->branch_id)?->name ?? null;
                    $driver->latitude = Branch::find($driver->branch_id)?->latitute ?? null;
                    $driver->longitude = Branch::find($driver->branch_id)?->longitute ?? null;

                    $today = Carbon::now()->format('Y-m-d');

                    $shift = $this->timeTableService->getTimetableForDate($driver->id, $today);

                    if (!($shift['status'] == false)) {
                        $shiftType = $lang == 'en' ?  $shift['data']['timetable']['name_en'] : $shift['data']['timetable']['name_ar'];
                        $shiftStart = $shift['data']['on_duty_time'];
                        $endStart = $shift['data']['off_duty_time'];
                        $driver->shift_start = $shiftStart;
                        $driver->shift_end = $endStart;
                        $driver->shift_type = $shiftType;
                    } else {
                        $driver->shift_start = null;
                        $driver->shift_end = null;
                        $driver->shift_type = null;
                    }

                    if ($user->shift_start && $user->shift_end && $driver->shift_start && $driver->shift_end) {
                        $userShiftStart = Carbon::parse($user->shift_start);
                        $userShiftEnd = Carbon::parse($user->shift_end);
                        $driverShiftStart = Carbon::parse($driver->shift_start);
                        $driverShiftEnd = Carbon::parse($driver->shift_end);
                    }

                    if (!($driverShiftStart <= $userShiftEnd && $driverShiftEnd >= $userShiftStart)) {
                        return false; // Unassign driver
                    }

                    if ($vehicleType) {
                        if ($lang == 'ar') {
                            $driver->vehicle_type = ($vehicleType == 'motorcycle') ? 'موتوسيكل' : 'سيارة';
                        } else {
                            $driver->vehicle_type = $vehicleType;
                        }
                    } else {
                        $driver->vehicle_type = null;
                    }
                    if ($maxOrders !== null && $orderCount >= $maxOrders) {
                        if ($user->flag !== 'cashier') {
                            $driver->availability = false; // Mark unavailable but keep driver
                            return true;
                        }
                        return false; // Remove driver for cashier
                    }

                    $driver->availability = true; // Driver is available
                    return true;
                });
                if ($filteredDrivers->isEmpty()) {
                    $data = null;
                } else {
                    $data = $filteredDrivers->values();
                }

                return ResponseWithSuccessData($lang, $data, 1);
            } else {
                return ResponseWithSuccessData($lang, null, 1);
            }
        }

        return RespondWithBadRequest($lang, 4);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
