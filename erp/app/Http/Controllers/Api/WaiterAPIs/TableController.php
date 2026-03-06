<?php

namespace App\Http\Controllers\Api\WaiterAPIs;

use App\Events\TableStatus;
use App\Events\WaiterNotify;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use App\Services\HR_Services\TimetableService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class TableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }
        // Find the reservation
        $reservation = Table::find($id);
        if (!$reservation) {
            return respondError('Validation Error.', 400, ['error' => __('validation.table_not_found')]);
        }

        // Validation rules
        // 1 for available, 2 occupied, 3 reserved
        $validator = Validator::make($request->all(), [
            'table_status' => 'required|in:1,2,3',
        ]);

        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }

        $newStatus = $request->table_status;
        $today = Carbon::today();

        $shiftDetails = TimetableService::getTimetableForDate($employee->id, $today);

        $ordersQuery = Order::where('branch_id', $employee->branch_id)
            ->where('type', 'dine-in')->where('table_id', $id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('updated_at', 'desc')->orderBy('created_at', 'desc');

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
        $order = $ordersQuery->first();
        if ($order) {
            return respondError('Validation Error.', 400, ['error' =>  __('validation.there_is_order_open_in_this_table')]);
        }       // Check if the new status is the same as the current one
        if ($reservation->status === $newStatus) {
            // Check if the reservation is already "busy" and the new status is also "busy"
            if ($reservation->status === '2' && $newStatus === '2') {
                return respondError('Validation Error.', 400, ['error' =>  __('validation.already_busy')]);
            }
            return respondError('Validation Error.', 400, ['error' => __('validation.status_same')]);
        }

        // Update status
        $reservation->status = $newStatus;
        $reservation->save();
        $notifyData =
            [
                'notification_type' => 'table',
                'description_ar' => 'تم تغير حاله الطاوله  ' . $reservation->table_number,
                'description_en' => 'Table status changed ' . $reservation->table_number,
                'title_ar' => 'تم تغير حاله الطاوله',
                'title_en' => 'Table status changed',
                'created_by' => null,
                'order_id' => $reservation->id
            ];
        runNotificationToEmployees($reservation->branch_id, $notifyData, null, $reservation->id, 'ar');
        $data = [
            'id' => $reservation->id,
            'status' => $reservation->status,
            'table_number' => $reservation->table_number,
            'table_id' => $reservation->id,
            'employee_id' => $employee->id,
            'employee_name' => $employee->first_name . ' ' . $employee->last_name,
        ];
        if (!$reservation || !$employee) {
            Log::error('Reservation or Employee missing', [
                'reservation' => $reservation,
                'employee' => $employee,
                'user_id' => auth()->id(),
            ]);
        } else {

            broadcast(new WaiterNotify($employee, $reservation, $data));
            broadcast(new TableStatus($reservation,  $employee->branch_id, 'updated'));
        }
        // Broadcast event
        // broadcast(new WaiterNotify($employee, $reservation,$data));
        return ResponseWithSuccessData($lang, $data, 1);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
