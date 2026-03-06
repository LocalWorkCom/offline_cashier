<?php

namespace App\Services\ClientServices;

use App\Models\Branch;
use App\Models\BranchTime;
use App\Models\FloorPartition;
use App\Models\Table;
use App\Models\TableReservation;
use App\Models\TableReservationTransaction;
use App\Models\User;
use App\Services\SettingsServices\BranchService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReservationService
{
    protected $branchService;

    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }

    function get_table_today_time($branch_id, $date)
    {
        $branch_time_count = date('w', strtotime($date));
        $branch_time = BranchTime::where('branch_id', $branch_id)->where('day', $branch_time_count)->first();
        if ($branch_time) {
            return $branch_time;
        } else {
            return null;
        }
    }

    function get_table_session($count, $branch_id, $reservation_date, $floor_partition, $type)
    {
        // Get branch data
        $branch = Branch::find($branch_id);
        if ($branch) {
            $branch_time = $this->get_table_today_time($branch_id, $reservation_date);

            if ($branch_time) {

                $table_session = getBranchSettings($branch_id, 'table_session_minutes');

                if ($table_session) {
                    $reservation_steps = (int) $table_session * 60;

                    $today = date('Y-m-d');
                    $today_time = new \DateTime();

                    $reservation_date = trim($reservation_date);

                    // $work_form = ($reservation_date == $today)
                    //     ? ($today_time->format('H:00:00') < $branch_time->opening_hour ? $branch_time->opening_hour : $today_time->format('H:00:00'))
                    //     : $branch_time->opening_hour;

                    $new_time = (strtotime($today_time->format('H:00:00')) + $reservation_steps);
                    $new_time = (strtotime($today_time->format('H:i:00')) > $new_time) ? date('H:i:00', ($new_time + $reservation_steps)) : date('H:i:00', $new_time);
                    $diff_minutes = strtotime($today_time->format('H:i:00')) - strtotime($branch_time->opening_hour);
                    $minutes = fmod($diff_minutes, $reservation_steps) / 60;
                    $work_form = ($reservation_date == $today)
                        ? ($today_time->format('H:i:00') < $branch_time->opening_hour ? $branch_time->opening_hour
                            : ($minutes >= 0 ? $new_time : $today_time->format('H:00:00')))
                        : $branch_time->opening_hour;

                    if ($branch_time->closing_hour > $work_form) {
                        $closing_hour = strtotime($branch_time->closing_hour) - $reservation_steps;
                        $range = range(strtotime($work_form), $closing_hour, $reservation_steps);
                    } else {
                        $range = [];
                    }

                    if ($floor_partition == 0) {
                        $tables = $branch->getAllTables();
                    } else {
                        $tables = $branch->getAllTables()->filter(fn($table) => $table->floor_partition_id == $floor_partition);
                    }

                    $tableAvailability = [];

                    foreach ($tables as $table) {
                        if ($table->status != 1 && $reservation_date == $today) {
                            continue;
                        }

                        if ($table->capacity < $count) {
                            continue;
                        }

                        $workHours = array_map(fn($time) => date("H:i:s", $time), $range);
                        $allTime = [];

                        $reservations = TableReservation::where('branch_id', $branch_id)
                            ->where('table_id', $table->id)
                            ->where('date', $reservation_date)
                            ->get();

                        foreach ($reservations as $reservation) {
                            if ($reservation) {
                                $time_from = strtotime($reservation->time_from);
                                $time_end = $time_from + $reservation_steps;

                                if ($time_from < $time_end) {
                                    foreach (range($time_from, $time_end - $reservation_steps, $reservation_steps) as $time) {
                                        $allTime[] = date("H:i:s", $time);
                                    }
                                    // return $allTime;
                                }
                            } else {
                                $allTime = [];
                            }
                        }

                        // return $allTime;

                        $availableTimes = array_values(array_diff($workHours, $allTime));

                        $tableAvailability[] = [
                            'table_name' => $table->name,
                            'table_number' => $table->table_number,
                            'table_id' => $table->id,
                            'table_image' => $table->image,
                            'available_times' => $availableTimes,
                        ];
                        $availableTimes = [];

                        // if ($type == "encode") {
                        //     return $tableAvailability;
                        // }
                        // return $tableAvailability;
                    }

                    return $tableAvailability;
                } else {
                    return [];
                }
            } else {
                return [];
            }
        }

        return false;
    }

    function updateTableReservation($order_id, $table_reservation_id)
    {
        if (TableReservation::where('id', $table_reservation_id)->exists()) {
            $reservations = TableReservation::find($table_reservation_id);
            $reservations->order_id = $order_id;
            $reservations->save();
        }
    }

    function checkTransactionTableReservation($table_reservation_id)
    {
        return TableReservationTransaction::where('table_reservation_id', $table_reservation_id)->exists() ? true : false;
    }

    function store(array $request, $checkToken, $order_id = null)
    {

        $lang = $request['lang'];
        App::setLocale($lang);
        $gaurd_name = getAuthenticatedGuard();
        if ($gaurd_name == 'api') {
            $client_id = Auth::guard('api')->user()->id;
        } else if ($gaurd_name == 'client') {
            $client_id = Auth::guard('client')->user()->id;
        } else if ($gaurd_name == 'admin') {
            $client_id = Auth::user()->id;
        } else {
            return RespondWithBadRequest($lang, 5);
        }

        $reservation = new TableReservation();
        $reservation->table_id = $request['tableId'];
        $reservation->client_id = $client_id;
        $reservation->date = trim($request['date']);
        $reservation->time_from = $request['time_from'];
        $reservation->time_to = $request['time_to'];
        $reservation->status = 1; // pending
        $reservation->confirmed = 2; // pending
        $reservation->confirmed_date = trim($request['date']);
        $reservation->confirmed_time = $request['time_from'];
        $reservation->confirmed_by = $client_id;
        $reservation->modified_by = null;
        $reservation->deleted_by = null;
        $reservation->created_by = $client_id;
        $reservation->branch_id = $request['branch_id'];
        $reservation->floor_partition_id = $request['floor_partition_id'];
        $reservation->reservation_type = $request['reservation_type'];
        $reservation->adult = $request['adult'] ?? 0;
        $reservation->kids = $request['kids'] ?? 0;
        $reservation->men = $request['men'] ?? 0;
        $reservation->women = $request['women'] ?? 0;

        $reservation->notes = $request['note'] ?? 0;
        $reservation->personal_type = $request['personal_type'] ?? 'person';
        if ($reservation->reservation_type == 'with') {
            $reservation->order_id = $order_id;
        } else {

            $reservation->order_id = null;
        }
        $reservation->reservation_number = strtoupper(uniqid('RSV'));
        $reservation->save();

        //table_reservation_log
        $table_data = [
            'table_reservation_id' => $reservation->id,
            'client_id' => $reservation->client_id,
            // 'cashier_id' => 3,
            // 'waiter_id' => 4,
            // 'canceled' => '1',
            // 'canceled_reason' => "test",
            'date' => $reservation->date,
            'from' => $reservation->time_from,
            'to' => $reservation->time_to,
            //'canceled_by' => $reservation->client_id,
            'created_by' => $reservation->created_by
        ];
        $addTableReservationLog = AddTableReservationLog($table_data);
        //end of table_reservation_log
        $branch = Branch::find($request['branch_id']);
        if ($request['reservation_type'] == 'without') {

            $ReservationTransactions = new TableReservationTransaction();
            $ReservationTransactions->table_reservation_id = $reservation->id; // Example reservation ID
            $ReservationTransactions->payment_status = 'unpaid';
            $ReservationTransactions->payment_method = (convertPolicyToMethod($request['payment_method']) == 'deposit') ? 'credit_card' : convertPolicyToMethod($request['payment_method']); // or 'credit_card', 'online'
            $ReservationTransactions->paid = $request['deposit'] ?? 0; // The paid amount
            $ReservationTransactions->date = now()->toDateString(); // Current date
            $ReservationTransactions->refund = 0; // Refund if any
            $ReservationTransactions->is_refund = 0; // Set to 1 if it's a refund
            $ReservationTransactions->created_by = auth()->id(); // Assuming the user is logged in
            $ReservationTransactions->modified_by = null; // Optional
            $ReservationTransactions->deleted_by = null; // Optional
            $ReservationTransactions->payment_gateway_reference = null; // If any reference
            $ReservationTransactions->payment_gateway_date = null; // If any date related to gateway
            $ReservationTransactions->payment_gateway_currency = null; // If any currency related to the payment gateway
            $ReservationTransactions->payment_gateway_status = null; // If any status from the gateway
            $ReservationTransactions->payment_gateway_method = null; // If any method from the gateway
            $ReservationTransactions->reason = null; // Optional reason for payment

            // Save the object to the database
            $ReservationTransactions->save();
        }
        send_push_notification(
            User::find($client_id)->fcm_token,
            "حجزك ف فرع $branch->name سوف يكون من الساعة ($reservation->time_from, $reservation->time_to)",
            " Your reservation at $branch->name is scheduled for ($reservation->time_from, $reservation->time_to) .",
            "حجز طاولة",
            "Reservation Table",
            "client",
            $client_id,
            $client_id,
            $reservation->id, // or request ID
            $lang,
            'table'
        );
        $data = addNotification(
            'table',
            'client',
            "حجزك ف فرع $branch->name سوف يكون من الساعة ($reservation->time_from, $reservation->time_to)",
            "Your reservation at $branch->name is scheduled for ($reservation->time_from, $reservation->time_to) .",
            "حجز طاولة",
            "Reservation Table",
            $client_id,
            $client_id,
            $lang,
            $reservation->id,
        );

        $reservation_data = [
            "table_id" => $reservation->table_id,
            "client_id" => $client_id,
            "date" => Carbon::parse($reservation->date)->format('Y-m-d'),
            "time_from" => Carbon::parse($reservation->time_from)->format('H:i:s'),
            "time_to" => $reservation->time_to ? Carbon::parse($reservation->time_to)->format('H:i:s') : null,
            "status" => $reservation->status,
            "confirmed" => $reservation->confirmed,
            "confirmed_date" => Carbon::parse($reservation->confirmed_date)->format('Y-m-d'),
            "confirmed_time" => Carbon::parse($reservation->confirmed_time)->format('H:i:s'),
            "confirmed_by" => $reservation->confirmed_by,
            "branch_id" => $reservation->branch_id,
            "floor_partition_id" => $reservation->floor_partition_id,
            "reservation_type" => $reservation->reservation_type,
            "adult" => $reservation->adult,
            "kids" => $reservation->kids,
            "men" => $reservation->men,
            "notes" => $reservation->notes,
            "personal_type" => $reservation->personal_type,
            "order_id" => $reservation->order_id,
            "reservation_number" => $reservation->reservation_number,
            "id" => $reservation->id
        ];

        return ResponseWithSuccessData($lang, $reservation_data, 1);
    }

    function validateReservationRequest(Request $request, $func_name)
    {
        $lang = $request->header('lang', 'ar');

        $validateData = Validator::make($request->all(), [
            'table_id' => 'required|exists:tables,id',
            'branch_id' => 'required|exists:branches,id',
            'floor_partition_id' => 'required|exists:floor_partitions,id',
            'time_from' => 'required|date_format:H:i:s',
            'time_to' => 'nullable|date_format:H:i:s',
            'date' => 'required|date|date_format:Y-m-d|after:yesterday',
            'adult' => 'nullable|required_if:personal_type,family|integer',
            'kids' => 'nullable|integer',
            'personal_type' => 'required|in:family,person',
            'men' => 'nullable|integer',
            'women' => 'nullable|integer'
        ]);

        $validateData->after(function ($validator) use ($request) {
            if ($request->input('personal_type') === 'person') {
                $men = (int) $request->input('men', 0);
                $women = (int) $request->input('women', 0);
                if ($men < 1 && !$request->filled('women')) {
                    $validator->errors()->add('men', 'The men field is required when personal type is person and women is less than 1.');
                }
                if ($women < 1 && !$request->filled('men')) {
                    $validator->errors()->add('women', 'The women field is required when personal type is person and men is less than 1.');
                }
            }
        });

        if ($validateData->fails()) {
            return respondError('Validation Error.', 400, $validateData->errors());
        }


        if ($func_name == "store") {
            if ($request->reservation_type == "with") {
                $col_cond = "reservation_with_order";
            } else {
                if ($request->payment_method == "full_payment_required") {
                    $message = "you can not use this payment policy in this branch";
                    return respondErrorData($message, 400, $message);
                }
                $col_cond = "reservation_without_order";
            }
            if (!in_array($request->payment_method, getBranchPolicyPayment($request->branch_id, $col_cond))) {
                $message = "this payment policy has not in this branch policy";
                return respondErrorData($message, 400, $message);
            }
        }

        // Custom checks
        $branch = Branch::find($request->branch_id);
        if (!$branch) {
            $message = "this branch is not available";
            return respondErrorData($message, 400, $message);
        }

        if (!$branch->is_branch_open) {
            $message = "this branch is closed now";
            return respondErrorData($message, 400, $message);
        }

        if (!$branch->is_table_reservation) {
            $message = "this branch has no table reservation";
            return respondErrorData($message, 400, $message);
        }

        if (!$branch->is_active) {
            $message = "no branch available";
            return respondErrorData($message, 400, $message);
        }

        $floor_partition = FloorPartition::where('id', $request->floor_partition_id)
            ->whereHas('floors', function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            })->first();

        if (!$floor_partition) {
            $message = "No floor partition found";
            return respondErrorData($message, 400, $message);
        }

        $count = (int) $request->adult + (int) $request->kids + (int) $request->men + (int) $request->women;

        $table_details = Table::where('id', $request->table_id)
            ->whereHas('floorPartitions', function ($q) use ($request) {
                $q->where('floor_partition_id', $request->floor_partition_id);
            })->first();
        if (!$table_details) {
            $message = "this table is not available in this floor partition";
            return respondErrorData($message, 400, $message);
        }

        if ($table_details->capacity < $count) {
            $message = "the pepole capacity is not equal table capacity";
            return respondErrorData($message, 400, $message);
        }

        if ($table_details->online == 0) {
            $message = "this table is not available to reservation online";
            return respondErrorData($message, 400, $message);
        }

        $tables = $this->get_table_session($count, $request->branch_id, $request->date, $request->floor_partition_id, "encode");
        $available_table_ids = array_column($tables, 'table_id');
        if (!in_array($request->table_id, $available_table_ids)) {
            $message = "This table is not available";
            return respondErrorData($message, 400, $message);
        }

        $table_info = array_filter($tables, fn($t) => $t['table_id'] == $request->table_id);
        $available_times = $table_info ? ($table_info[array_key_first($table_info)]['available_times'] ?? []) : [];
        if (!in_array($request->time_from, $available_times)) {
            $message = "This time is not available";
            return respondErrorData($message, 400, $message);
        }

        $payment_policy = $this->branchService->payment_policy($request->branch_id);
        if (!$payment_policy) {
            $message = "no branch policy found";
            return respondErrorData($message, 400, $message);
        }

        return respondEmptyData([], 200, []);
    }

    function validationTableSession(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $validateData = Validator::make($request->all(), [
            'branch_id' => 'required|exists:branches,id',
            'floor_partition_id' => 'required|exists:floor_partitions,id',
            'date' => 'required|date|date_format:Y-m-d|after:yesterday',
            'count' => 'required|integer'
        ]);

        if ($validateData->fails()) {
            return respondError('Validation Error.', 400, $validateData->errors());
        }

        $barnch_details = Branch::where('id', $request->branch_id)->first();

        //return $today = \Carbon\Carbon::parse(date('Y-m-d'));
        if (!$barnch_details) {
            $message = "this branch is not available";
            return respondErrorData($message, 400, $message);
        }

        if (!$barnch_details->is_branch_open) {
            $message = "this branch is closed now";
            return respondErrorData($message, 400, $message);
        }


        if ($barnch_details->is_table_reservation == 0) {
            $message = "this branch has no table reservation";
            return respondErrorData($message, 400, $message);
        }

        if ($barnch_details->is_active == 0) {
            $message = "no branch available";
            return respondErrorData($message, 400, $message);
        }

        $branch_id = $request->branch_id;
        $floor_partition_details = FloorPartition::where('id', $request->floor_partition_id)
            ->whereHas('floors', function ($q) use ($branch_id) {
                $q->where('branch_id', $branch_id);
            })
            ->first();
        if (!$floor_partition_details) {
            $message = "No floor partition found";
            return respondErrorData($message, 400, $message);
        }

        return true;
    }
}
