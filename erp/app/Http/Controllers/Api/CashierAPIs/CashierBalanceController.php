<?php

namespace App\Http\Controllers\Api\CashierAPIs;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CashierMachineLog;
use App\Models\EmployeeOpeningBalance;
use App\Models\Order;
use App\Models\OrderTransaction;
use App\Models\BranchSafe;
use App\Models\CashierMachine;
use App\Services\HR_Services\TimetableService;
use App\Services\ReportServices\CashierBranchSafeService;
use App\Services\ReturnInvoiceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Absolute;

class CashierBalanceController extends Controller
{
    protected $CashierBranchSafeService;

    protected $timeTableService;
    protected $returnInvoiceService;

    // Inject the service via constructor
    public function __construct(TimetableService $timeTableService, CashierBranchSafeService $CashierBranchSafeService, ReturnInvoiceService $returnInvoiceService)
    {
        $this->timeTableService = $timeTableService;
        $this->CashierBranchSafeService = $CashierBranchSafeService;
        $this->returnInvoiceService = $returnInvoiceService;
    }

    /**
     * Store a newly created open balance in storage.
     */
    public function openBalance(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            App::setLocale($lang);

            // authenticate employee
            $user = auth('employee')->user();

            // authorize cashier flag if not send 401 code
            if ((!$user) || ($user->flag != 'cashier')) {
                return RespondWithBadRequest($lang, 4);
            }

            // get date for today to use it in shift calculation function
            $today = Carbon::now()->format('Y-m-d');

            // service function for calculating shift
            $shift = $this->timeTableService->getTimetableForDate($user->id, $today);

            // respond from the service function if the employee has recorded shift in the system
            if (!($shift['status'] == false)) {
                $shiftType = $lang == 'en' ?  $shift['data']['timetable']['name_en'] : $shift['data']['timetable']['name_ar'];
                $shiftStart = $shift['data']['on_duty_time'];
                $endStart = $shift['data']['off_duty_time'];
                $user->shift_start = $shiftStart;
                $user->shift_end = $endStart;
                $user->shift_type = $shiftType;
                $user->employee_schedule_id = $shift['data']['employee_schedule_id'];
            }
            // if the employee doesn't have recorded shift in the system
            else {
                $user->shift_start = null;
                $user->shift_end = null;
                $user->shift_type = null;
                $user->employee_schedule_id = null;
            }

            $validator = Validator::make($request->all(), [
                'cashier_machine_id' => 'required|numeric|min:1|exists:cashier_machines,id',
                'employee_schedule_id' => 'required|numeric|min:1|exists:employee_schedules,id',
                'open_cash' => 'required|numeric|min:0',
                'open_visa' => 'nullable|numeric|min:0',
                // 'shift_start' => 'required|date_format:H:i:s',
            ]);

            // validator on required fields
            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            // validator to ensure that employee_schedule_id sent in request is right for authenticated employee
            if ($request->employee_schedule_id != $user->employee_schedule_id) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, ['employee_schedule_id' => $lang == 'en' ? 'employee schedule is not correct.' : 'جدول الموظف غير صحيح.']);
            }

            // if exist get opening balance at same day for same employee with same machine
            $existingOpeningBalance = EmployeeOpeningBalance::where('cashier_machine_id', $request->cashier_machine_id)
                ->where('employee_schedule_id', $request->employee_schedule_id)
                ->where('date', $today)
                ->where('type', 1) // 1 for open, 2 for close
                ->first();

            // return validation error that shift opened before
            //            if ($existingOpeningBalance) {
            //                return respondErrorData($lang == 'en' ? 'Shift already opened.' : 'تم فتح الوردية.', 400, $lang == 'en' ? ['The shift has already been opened.'] : ['تم فتح الوردية مسبقاً.']);
            //            }

            // if exist get previous closing balance for same machine to use it in validating open amount sent in the request
            $previousClosingBalance = EmployeeOpeningBalance::latest()->where('cashier_machine_id', $request->cashier_machine_id) //Eman
                ->where('type', 2)
                ->orderBy('date', 'desc')
                // ->where('date', '<', $today) //Eman remove
                // ->orWhere('date', $today) //Eman remove
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->orderBy('updated_at', 'desc')
                ->first();
            // if there is no previous closing (first record in system) or the amount sent in the request is equals to closing amounts of the previous shift
            //            if (!$previousClosingBalance ||
            //                ($request->open_cash == $previousClosingBalance->close_cash &&
            //                    $request->open_visa == $previousClosingBalance->close_visa))

            // if there is a deficit from previous shift (to another employee) we don't make the current employee responsible for it also


            // no condition (temporary)
            if (true) {
                // if there is previous closing
                if ($previousClosingBalance) {
                    // compare between amount sent in the request and close amounts in database to calculate deficits
                    // only if sent amounts is smaller than the close amounts of previous shift
                    // deficit of shortage or deficit of overage
                    if (($request->open_cash < $previousClosingBalance->close_cash ||
                        $request->open_visa < $previousClosingBalance->close_visa) || ($request->open_cash > $previousClosingBalance->close_cash ||
                        $request->open_visa > $previousClosingBalance->close_visa)) {
                        $deficit_cash = (float)($request->open_cash - $previousClosingBalance->close_cash);
                        $deficit_visa = (float)($request->open_visa - $previousClosingBalance->close_visa);
                    }
                }

                //group a set of database operations so that they either all succeed or all fail together (ensures data integrity)
                DB::beginTransaction();

                // save data in employee_opening_balances table
                $openningBalance = new EmployeeOpeningBalance();
                $openningBalance->employee_id = $user->id;
                $openningBalance->cashier_machine_id = (int) $request->cashier_machine_id;
                $openningBalance->employee_schedule_id = (int) $request->employee_schedule_id;
                $openningBalance->open_cash = round((float) $request->open_cash, 2);
                $openningBalance->open_visa = round((float) $request->open_visa ?? 0, 2);
                $openningBalance->deficit_cash = round($deficit_cash ?? 0, 2);
                $openningBalance->deficit_visa = round($deficit_visa ?? 0, 2);
                $openningBalance->time = $user->shift_start;
                $openningBalance->date = $today;
                $openningBalance->type = 1; // open
                $openningBalance->save();
                $openningBalance->currency_symbol = Branch::find($user->branch_id)->country->currency_symbol;

                $balance_id = $openningBalance->id;

                // save same data in cashier_machine_logs table
                $log = new CashierMachineLog();
                $log->employee_id = $user->id;
                $log->cashier_machine_id = (int) $request->cashier_machine_id;
                $log->employee_opening_balance_id = (int) $balance_id;
                $log->open_cash = (float) $request->open_cash;
                $log->open_visa = (float) $request->open_visa ?? 0;
                $log->deficit_cash = $deficit_cash ?? 0;
                $log->deficit_visa = $deficit_visa ?? 0;
                $log->time = $user->shift_start;
                $log->date = $today;
                $log->type = 1;
                $log->save();

                // every thing is ok save all changes
                DB::commit();
                $openningBalance->total_deficiting = round($openningBalance->total_deficiting, 2);
                $openningBalance->total_opening = round($openningBalance->total_opening, 2);
                return ResponseWithSuccessData($lang, $openningBalance, 1);
            }

            // unreachable statement (temporary) else if the amounts sent in request not equals real amounts
            else {
                // something wrong happened roll back all changes
                DB::rollBack();
                return respondErrorData(($lang = 'en' ? 'Matching balance Error.' : 'خطأ في مطابقة الرصيد.'), 400, $lang == 'en' ? ['The amounts entered do not match the system records or expected totals.'] : ['المبالغ المدخلة لا تتوافق مع سجلات النظام او الإجماليات المتوقعة.']);
            }
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    /**
     * Display the specified open balance.
     */
    public function getOpenBalance(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $user = auth('employee')->user();
            $today = Carbon::now()->format('Y-m-d');

            // if ((!$user) || ($user->flag != 'cashier')) {
            //     return RespondWithBadRequest($lang, 4);
            // }

            $shift = $this->timeTableService->getTimetableForDate($user->id, $today);

            if (!($shift['status'] == false)) {
                $shiftType = $lang == 'en' ?  $shift['data']['timetable']['name_en'] : $shift['data']['timetable']['name_ar'];
                $shiftStart = $shift['data']['on_duty_time'];
                $endStart = $shift['data']['off_duty_time'];
                $user->shift_start = $shiftStart;
                $user->shift_end = $endStart;
                $user->shift_type = $shiftType;
                $user->employee_schedule_id = $shift['data']['employee_schedule_id'];
            } else {
                $user->shift_start = null;
                $user->shift_end = null;
                $user->shift_type = null;
                $user->employee_schedule_id = null;
            }

            App::setLocale($lang);

            $validator = Validator::make($request->all(), [
                'cashier_machine_id' => 'required|numeric|min:1|exists:cashier_machines,id',
                'employee_schedule_id' => 'required|numeric|min:1|exists:employee_schedules,id',
                // 'shift_start' => 'required|date_format:H:i:s',
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            if ($request->employee_schedule_id != $user->employee_schedule_id) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, ['employee_schedule_id' => $lang == 'en' ? 'employee schedule is not correct.' : 'جدول الموظف غير صحيح.']);
            }

            $existingOpeningBalance = EmployeeOpeningBalance::where('cashier_machine_id', $request->cashier_machine_id)
                ->where('employee_schedule_id', $request->employee_schedule_id)
                ->where('date', $today)
                ->where('type', 1)
                ->first();

            if ($existingOpeningBalance) {
                return respondErrorData($lang == 'en' ? 'Shift already opened.' : 'تم فتح الوردية.', 400, $lang == 'en' ? ['The shift has already been opened.'] : ['تم فتح الوردية مسبقاً.']);
            }

            $previousClosingBalance = EmployeeOpeningBalance::where('cashier_machine_id', $request->cashier_machine_id)
                ->where('type', 2)
                ->orderBy('date', 'desc')
                ->where('date', '<', $today)
                ->orWhere('date', $today)
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->first();
            if ($previousClosingBalance) {
                $openCash = $previousClosingBalance->close_cash;
                $openVisa = $previousClosingBalance->close_visa;
                $total = [
                    'open_cash' => round($openCash, 2),
                    'open_visa' => round($openVisa, 2)
                ];
            } else {
                $total = [
                    'open_cash' => 0,
                    'close_visa' => 0,
                ];
            }
            return ResponseWithSuccessData($lang, $total, 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    /**
     * Store a newly created close balance in storage.
     */
    public function closeBalance(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            App::setLocale($lang);

            // authenticate employee
            $user = auth('employee')->user();

            // get date for today to use it in queries
            $today = Carbon::now()->format('Y-m-d');

            // authorize cashier flag if not send 401 code
            if ((!$user) || ($user->flag != 'cashier')) {
                return RespondWithBadRequest($lang, 4);
            }

            $validator = Validator::make($request->all(), [
                'cashier_machine_id' => 'required|numeric|min:1|exists:cashier_machines,id',
                'employee_schedule_id' => 'required|numeric|min:1|exists:employee_schedules,id',
                'balance_id' => 'required|numeric|min:1|exists:employee_opening_balances,id',
                // make balance_id nullable (temporary)
                // 'balance_id' => 'nullable|numeric|min:1',
                'close_cash' => 'required|numeric|min:0',
                'close_visa' => 'nullable|numeric|min:0',
                // 'shift_start' => 'required|date_format:H:i:s',
                // 'shift_end' => 'required|date_format:H:i:s',
            ]);
            $test = $this->returnInvoiceService->getCurrentBalance($request);
            $test = $test->original['data'];
            // dd($test[0]['value']);
            // get date for today to use it in shift calculation function
            $today = Carbon::now()->format('Y-m-d');

            // service function for calculating shift
            $shift = $this->timeTableService->getTimetableForDate($user->id, $today);

            // respond from the service function if the employee has recorded shift in the system
            if (!($shift['status'] == false)) {
                $shiftType = $lang == 'en' ?  $shift['data']['timetable']['name_en'] : $shift['data']['timetable']['name_ar'];
                $shiftStart = $shift['data']['on_duty_time'];
                $endStart = $shift['data']['off_duty_time'];
                $user->shift_start = $shiftStart;
                $user->shift_end = $endStart;
                $user->shift_type = $shiftType;
                $user->employee_schedule_id = $shift['data']['employee_schedule_id'];
            }
            // if the employee doesn't have recorded shift in the system
            else {
                $user->shift_start = null;
                $user->shift_end = null;
                $user->shift_type = null;
                $user->employee_schedule_id = null;
            }

            // validator on required fields
            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            // If closing balance exists, retrieve current branch safe balances
            $branchSafe = BranchSafe::latest()->where('branch_id', $user->branch_id)
                ->where('cashier_machine_id', $request->cashier_machine_id)
                ->first();
            // If branch safe exists, get its balances (if present)
            $branchSafeCash = $branchSafe ? $branchSafe->cash_amount : 0;
            $branchSafeVisa = $branchSafe ? $branchSafe->visa_amount : 0;

            // if exist get closing balance at same day for same employee with same machine
            $existingClosingBalance = EmployeeOpeningBalance::where('cashier_machine_id', $request->cashier_machine_id)
                ->where('employee_schedule_id', $request->employee_schedule_id)
                ->where('date', $today)
                ->where('type', 2) // 1 for open, 2 for close
                ->first();

            // find balance with sent id to validate on if it is not closed already
            $closingBalance = EmployeeOpeningBalance::find($request->balance_id) ?? null;
            $sameClosingBalance = $closingBalance?->type ?? null; //issue solved here

            $currentOpeningBalance = $closingBalance;

            if ($currentOpeningBalance) {

                // validator to ensure that cashier_machine_id sent in request is the same saved in current opened balance record
                if ($currentOpeningBalance->cashier_machine_id != $request->cashier_machine_id) {
                    return respondErrorData(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $lang == 'en' ? ['Cashier machine is not correct.'] : ['رقم الماكينة غير صحيح.']);
                }

                // validator to ensure that employee_schedule_id sent in request is right for authenticated employee and the same saved in current opened balance record
                if ($currentOpeningBalance->employee_schedule_id != $request->employee_schedule_id) {
                    return respondErrorData(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $lang == 'en' ? ['Employee schedule is not correct.'] : ['جدول الموظف غير صحيح.']);
                }

                // get shift start and end in the format of updated_at date + time
                $start = $today . ' ' . $user->shift_start;
                $end = $today . ' ' . $user->shift_end;

                // get amounts entered in open balance
                $openCash = $currentOpeningBalance->open_cash;
                $openVisa = $currentOpeningBalance->open_visa;

                if (isset($closingBalance)) {
                    $closingBalance_created_at = $closingBalance->created_at;
                }
                $cashTotal = OrderTransaction::whereHas('order', function ($query) use ($user, $start, $end, $closingBalance_created_at) {
                    $query->where('cashier_id', $user->id)
                        ->where('updated_at', '>', $closingBalance_created_at); //Eman
                    $query->where('status', '!=', 'cancelled');

                    //->whereBetween('updated_at', [$start, $end]);
                    //                        ->where('status', 'completed')
                    //                        ->where('print_status', 'done');
                })
                    ->where('payment_method', 'cash')
                    ->where('payment_status', 'paid')
                    //                    ->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')
                    ->selectRaw('SUM(paid)  as total')
                    ->value('total');

                $visaTotal = OrderTransaction::whereHas('order', function ($query) use ($user, $start, $end, $closingBalance_created_at) {
                    $query->where('cashier_id', $user->id)
                        ->where('updated_at', '>', $closingBalance_created_at); //Eman
                    $query->where('status', '!=', 'cancelled');

                    //                        ->where('print_status', 'done');
                })
                    ->where('payment_method', 'credit')
                    ->where('payment_status', 'paid')
                    //                    ->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')
                    ->selectRaw('SUM(paid)  as total')
                    ->value('total');

                // calculate total cash and visa amounts (no consideration to deficit if exists in open balance)
                // $totalClosingCash = ($openCash + $cashTotal) - ($branchSafeCash ?? 0);
                // $totalClosingVisa = ($openVisa + $visaTotal) - ($branchSafeVisa ?? 0);

                //$totalClosingCash = ($cashTotal) - ($branchSafeCash ?? 0);
                //$totalClosingVisa = ($visaTotal) - ($branchSafeVisa ?? 0);
                $totalClosingCash =  $closingBalance->open_cash + $cashTotal - $closingBalance->balance_after_sent_to_safe;
                $totalClosingVisa =  $closingBalance->open_visa + $visaTotal - $closingBalance->balance_after_sent_to_safe_visa;



                //                // check if entered values matches real amounts  of all orders (hashed temporary)
                //                if (($request->close_cash == $totalClosingCash) && ($request->close_visa == $totalClosingVisa))

                // get existing deficits from opening balance
                $existDeficitCash = (float)$currentOpeningBalance->deficit_cash;
                $existDeficitVisa = (float)$currentOpeningBalance->deficit_visa;

                // no condition (temporary)
                if (true) {
                    // if entered values doesn't match real amounts calculate deficits
                    // only if sent amounts is smaller than real
                    if (($request->close_cash < $totalClosingCash) || ($request->close_visa < $totalClosingVisa) || ($request->close_cash > $totalClosingCash) || ($request->close_visa > $totalClosingVisa)) {

                        // calculate closing deficit the difference between real amounts and request sent amounts
                        $closeDeficitCash = (float)($request->close_cash - $totalClosingCash);
                        $closeDeficitVisa = (float)($request->close_visa - $totalClosingVisa);

                        //                        // calculate final deficit in the whole shift (the difference between real amounts and request sent amounts + the deficit exists from open balance)
                        //                        $deficit_cash = $closeDeficitCash + $existDeficitCash;
                        //                        $deficit_visa = $closeDeficitVisa + $existDeficitVisa;

                        // calculate real closing amounts
                        //                        $real_cash = ($totalClosingCash + $existDeficitCash) - $deficit_cash;
                        //                        $real_visa = ($totalClosingVisa + $existDeficitVisa) - $deficit_visa;
                    }

                    DB::beginTransaction();

                    // update values of current opening balance and convert it to closing balance
                    $currentOpeningBalance->close_cash = round((float) $request->close_cash, 2); // real amount after deducting the deficit
                    // $currentOpeningBalance->real_cash = $totalClosingCash + $existDeficitCash; // amount that should be without any deficits
                    // $currentOpeningBalance->deficit_cash_close = round($closeDeficitCash ?? 0, 2);
                    $currentOpeningBalance->deficit_cash_close = round((float)($request->close_cash - $test[0]['value']), 2);

                    $currentOpeningBalance->close_visa = round((float) $request->close_visa, 2); // real amount after deducting the deficit
                    // $currentOpeningBalance->real_visa = $totalClosingVisa + $existDeficitVisa; // amount that should be without any deficits
                    $currentOpeningBalance->deficit_visa_close = round($closeDeficitVisa ?? 0, 2);

                    $currentOpeningBalance->date = $today;
                    $currentOpeningBalance->time = $user->shift_end;
                    $currentOpeningBalance->type = 2;
                    $currentOpeningBalance->save();

                    // to return amounts in double in the response
                    $currentOpeningBalance->open_cash = round((float) $currentOpeningBalance->open_cash, 2);
                    $currentOpeningBalance->open_visa = round((float) $currentOpeningBalance->open_visa, 2);
                    $currentOpeningBalance->deficit_cash = round((float) $currentOpeningBalance->deficit_cash, 2);
                    $currentOpeningBalance->deficit_visa = round((float) $currentOpeningBalance->deficit_visa, 2);

                    $currentOpeningBalance->currency_symbol = Branch::find($user->branch_id)->country->currency_symbol;


                    // save same data in cashier_machine_logs table
                    $log = new CashierMachineLog();
                    $log->employee_id = $user->id;
                    $log->cashier_machine_id = (int) $request->cashier_machine_id;
                    $log->employee_opening_balance_id = (int) $currentOpeningBalance->id;
                    $log->close_cash = (float) $request->close_cash;
                    $log->deficit_cash_close = $closeDeficitCash ?? 0;
                    //                    $log->real_cash = $totalClosingCash + $existDeficitCash;
                    $log->close_visa = $real_visa ?? (float) $request->close_visa;
                    $log->deficit_visa_close = $closeDeficitVisa ?? 0;
                    //                    $log->real_visa = $totalClosingVisa + $existDeficitVisa;
                    $log->time = $user->shift_end;
                    $log->date = $today;
                    $log->type = 2;
                    $log->save();

                    $currentOpeningBalance->total_deficiting = round($currentOpeningBalance->total_deficiting, 2);
                    $currentOpeningBalance->total_opening = round($currentOpeningBalance->total_opening, 2);

                    DB::commit();

                    return ResponseWithSuccessData($lang, $currentOpeningBalance, 1);
                }
                // unreachable statement (temporary) else if the amounts sent in request not equals real amounts
                else {
                    DB::rollBack();
                    return respondErrorData(($lang == 'en' ? 'Matching balance Error.' : 'خطأ في مطابقة الرصيد.'), 400, $lang == 'en' ? ['The amounts entered do not match the system records or expected totals.'] : ['المبالغ المدخلة لا تتوافق مع سجلات النظام او الإجماليات المتوقعة.']);
                }
            }
            // if there is no current opening balance
            // or the balance_id not sent in the request (temporary)
            // unreachable statement (balance_id is required now)
            else {
                //                // return validation error (hashed temporary)
                //                return respondErrorData(($lang == 'en' ? ['Shift is not opened.'] : ['لم يتم فتح الوردية.']), 400, ($lang == 'en' ? ['Shift is not opened.'] : ['لم يتم فتح الوردية.']));

                DB::beginTransaction();

                // save the data sent in the request in new record (just like log)
                $newOpening = EmployeeOpeningBalance::create([
                    'employee_id' => $user->id,
                    'cashier_machine_id' => $request->cashier_machine_id,
                    'employee_schedule_id' => $request->employee_schedule_id,
                    'close_cash' => (float) $request->close_cash ?? 0,
                    'close_visa' => (float) $request->close_visa ?? 0,
                    'date' => $today,
                    'time' => $user->shift_end,
                    'type' => 2, // 1 is for open, 2 for close
                ]);

                // save same data in cashier_machine_logs table
                $log = new CashierMachineLog();
                $log->employee_id = $user->id;
                $log->cashier_machine_id = (int) $request->cashier_machine_id;
                $log->employee_opening_balance_id = (int) $newOpening->id;
                $log->close_cash = (float) $request->close_cash ?? 0;
                $log->close_visa = (float) $request->close_visa ?? 0;
                $log->time = $user->shift_end;
                $log->date = $today;
                $log->type = 2; // 1 is for open, 2 for close
                $log->save();

                DB::commit();

                return ResponseWithSuccessData($lang, $newOpening, 1);
            }
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    /**
     * Display the specified close balance.
     */
    public function getCloseBalance(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $user = auth('employee')->user();

            if ((!$user) || ($user->flag != 'cashier')) {
                return RespondWithBadRequest($lang, 4);
            }

            App::setLocale($lang);

            $validator = Validator::make($request->all(), [
                'cashier_machine_id' => 'required|numeric|min:1|exists:cashier_machines,id',
                'employee_schedule_id' => 'required|numeric|min:1|exists:employee_schedules,id',
                'balance_id' => 'required|numeric|min:1|exists:employee_opening_balances,id',
                // 'shift_start' => 'required|date_format:H:i:s',
                // 'shift_end' => 'required|date_format:H:i:s',
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            // If closing balance exists, retrieve current branch safe balances
            $branchSafe = BranchSafe::where('branch_id', $user->branch_id)
                ->where('cashier_machine_id', $request->cashier_machine_id)
                ->first();

            // If branch safe exists, get its balances (if present)
            $branchSafeCash = $branchSafe ? $branchSafe->cash_amount : 0;
            $branchSafeVisa = $branchSafe ? $branchSafe->visa_amount : 0;

            // get date for today to use it in shift calculation function
            $today = Carbon::now()->format('Y-m-d');

            // service function for calculating shift
            $shift = $this->timeTableService->getTimetableForDate($user->id, $today);

            // respond from the service function if the employee has recorded shift in the system
            if (!($shift['status'] == false)) {
                $shiftType = $lang == 'en' ?  $shift['data']['timetable']['name_en'] : $shift['data']['timetable']['name_ar'];
                $shiftStart = $shift['data']['on_duty_time'];
                $endStart = $shift['data']['off_duty_time'];
                $user->shift_start = $shiftStart;
                $user->shift_end = $endStart;
                $user->shift_type = $shiftType;
                $user->employee_schedule_id = $shift['data']['employee_schedule_id'];
            }
            // if the employee doesn't have recorded shift in the system
            else {
                $user->shift_start = null;
                $user->shift_end = null;
                $user->shift_type = null;
                $user->employee_schedule_id = null;
            }

            $existingClosingBalance = EmployeeOpeningBalance::where('cashier_machine_id', $request->cashier_machine_id)
                ->where('employee_schedule_id', $request->employee_schedule_id)
                ->where('date', $today)
                ->where('type', 2)
                ->first();
            $closingBalance = EmployeeOpeningBalance::find($request->balance_id) ?? null;
            $sameClosingBalance = $closingBalance?->type ?? null; //issue solved here
            $existingOpenedBalance = EmployeeOpeningBalance::where('cashier_machine_id', $request->cashier_machine_id)
                ->where('employee_schedule_id', $request->employee_schedule_id)
                ->where('date', $today)
                ->orderBy('created_at', 'desc')
                ->where('type', 1)
                ->first();

            //            if ($existingClosingBalance) {
            if (($existingClosingBalance && !$existingOpenedBalance) || ($sameClosingBalance == 2)) {
                return respondErrorData(($lang == 'en' ? ['Shift already closed.'] : ['تم غلق الوردية.']), 400, $lang == 'en' ? ['The shift has already been closed.'] : ['تم غلق الوردية مسبقاً.']);
            }

            $currentOpeningBalance = EmployeeOpeningBalance::find($request->balance_id) ?? $existingOpenedBalance;

            if ($currentOpeningBalance) {
                if ($currentOpeningBalance->cashier_machine_id != $request->cashier_machine_id) {
                    return respondErrorData(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $lang == 'en' ? ['Cashier machine is not correct.'] : ['رقم الماكينة غير صحيح.']);
                }
                if ($currentOpeningBalance->employee_schedule_id != $request->employee_schedule_id) {
                    return respondErrorData(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $lang == 'en' ? ['Employee schedule is not correct.'] : ['جدول الموظف غير صحيح.']);
                }
                $start = $today . ' ' . $user->shift_start;
                $end = $today . ' ' . $user->shift_end;
                $openCash = $currentOpeningBalance->open_cash;
                $openVisa = $currentOpeningBalance->open_visa;
                //                $cashTotal = Order::where('cashier_id', $user->id)
                //                    ->whereBetween('updated_at', [$start, $end])
                //                    ->where('status', 'completed')
                //                    ->where('print_status', 'done')
                //                    ->whereHas('orderTransactions', function ($query) {
                //                        $query->where('payment_method', 'cash')->where('payment_status', 'paid');
                //                    })
                //                    ->sum('total_price_after_tax');
                //
                //                $visaTotal = Order::where('cashier_id', $user->id)
                //                    ->whereBetween('updated_at', [$start, $end])
                //                    ->where('status', 'completed')
                //                    ->where('print_status', 'done')
                //                    ->whereHas('orderTransactions', function ($query) {
                //                        $query->where('payment_method', 'credit')->where('payment_status', 'paid');
                //                    })
                //                    ->sum('total_price_after_tax');

                $cashTotal = OrderTransaction::whereHas('order', function ($query) use ($user, $start, $end) {
                    $query->where('cashier_id', $user->id)
                        ->whereBetween('updated_at', [$start, $end]);
                    //                        ->where('status', 'completed')
                    //                        ->where('print_status', 'done');
                })
                    ->where('payment_method', 'cash')
                    ->where('payment_status', 'paid')
                    //                    ->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')
                    ->selectRaw('SUM(paid) as total')
                    ->value('total');

                $visaTotal = OrderTransaction::whereHas('order', function ($query) use ($user, $start, $end) {
                    $query->where('cashier_id', $user->id)
                        ->whereBetween('updated_at', [$start, $end]);
                    //                        ->where('status', 'completed')
                    //                        ->where('print_status', 'done');
                })
                    ->where('payment_method', 'credit')
                    ->where('payment_status', 'paid')
                    //                    ->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')
                    ->selectRaw('SUM(paid) as total')
                    ->value('total');

                $totalClosingCash = ($openCash + $cashTotal) - ($branchSafeCash ?? 0);
                $totalClosingVisa = ($openVisa + $visaTotal) - ($branchSafeVisa ?? 0);
                $total = [
                    'close_cash' => round($totalClosingCash, 2),
                    'close_visa' => round($totalClosingVisa, 2)
                ];
                return ResponseWithSuccessData($lang, $total, 1);
            } else {
                return respondErrorData(($lang == 'en' ? ['Shift is not opened.'] : ['لم يتم فتح الوردية.']), 400, ($lang == 'en' ? ['Shift is not opened.'] : ['لم يتم فتح الوردية.']));
            }
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    public function getCurrentBalance(Request $request)
    {
        $test = $this->returnInvoiceService->getCurrentBalance($request);
        return $test;
        // try {
        //     $lang = $request->header('lang', 'ar');
        //     $user = auth('employee')->user();

        //     if ((!$user) || ($user->flag != 'cashier')) {
        //         return RespondWithBadRequest($lang, 4);
        //     }

        //     App::setLocale($lang);

        //     $validator = Validator::make($request->all(), [
        //         'cashier_machine_id' => 'required|numeric|min:1|exists:cashier_machines,id',
        //         'employee_schedule_id' => 'required|numeric|min:1|exists:employee_schedules,id',
        //         //                'balance_id' => 'nullable|numeric|min:1|exists:employee_opening_balances,id',
        //         'shift_start' => 'required|date_format:H:i:s',
        //         'shift_end' => 'required|date_format:H:i:s',
        //     ]);

        //     if ($validator->fails()) {
        //         return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        //     }

        //     $today = Carbon::now()->format('Y-m-d');

        //     $start = $today . ' ' . $request->shift_start;
        //     $end = $today . ' ' . $request->shift_end;

        //     $lastBalance = EmployeeOpeningBalance::latest()->where('cashier_machine_id', $request->cashier_machine_id)->first(); //Eman added ->where('cashier_machine_id', $request->cashier_machine_id)


        //     if (isset($lastBalance)) {
        //         $lastBalance_created_at = $lastBalance->created_at;
        //         $lastBalance_open_cash = $lastBalance->open_cash;
        //         $lastBalance_open_visa = $lastBalance->open_visa;
        //         $lastBalance_close_cash = $lastBalance->close_cash;
        //         $lastBalance_close_visa = $lastBalance->close_visa;
        //     } else {
        //         $lastBalance_created_at = Carbon::now();
        //         $lastBalance_open_cash = 0;
        //         $lastBalance_open_visa = 0;
        //         $lastBalance_close_cash = 0;
        //         $lastBalance_close_visa = 0;
        //     }
        //     $cashTotal = OrderTransaction::whereHas('order', function ($query) use ($user, $start, $end, $lastBalance_created_at) {
        //         $query->where('cashier_id', $user->id)
        //             //->whereBetween('updated_at', [$start, $end]);
        //             ->where('updated_at', '>', $lastBalance_created_at); //Eman


        //         //                        ->where('status', 'completed')
        //         //                        ->where('print_status', 'done');
        //     })
        //         ->where('payment_method', 'cash')
        //         ->where('payment_status', 'paid')
        //         //                    ->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')
        //         ->selectRaw('SUM(paid) as total')
        //         ->value('total');

        //     $visaTotal = OrderTransaction::whereHas('order', function ($query) use ($user, $start, $end, $lastBalance_created_at) {
        //         $query->where('cashier_id', $user->id)
        //             ->where('updated_at', '>', $lastBalance_created_at); //Eman
        //         //    ->whereBetween('updated_at', [$start, $end]);
        //         //                        ->where('status', 'completed')
        //         //                        ->where('print_status', 'done');
        //     })
        //         ->where('payment_method', 'credit')
        //         ->where('payment_status', 'paid')
        //         //                    ->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')
        //         ->selectRaw('SUM(paid) as total')
        //         ->value('total');
        //     if (isset($lastBalance) && $lastBalance->type == 1) {
        //         $totalClosingCash = $cashTotal + $lastBalance_open_cash; //Eman
        //         $totalClosingVisa =  $visaTotal + $lastBalance_open_visa; //Eman
        //     } else {
        //         $totalClosingCash =  $lastBalance_close_cash; //Eman
        //         $totalClosingVisa =   $lastBalance_close_visa; //Eman
        //     }
        //     if (isset($lastBalance) && $lastBalance->balance_after_sent_to_safe != 0 && $lastBalance->type == 1) {
        //         $totalClosingCash = abs($lastBalance->balance_after_sent_to_safe - $totalClosingCash); //Eman
        //         $totalClosingVisa = abs($lastBalance->balance_after_sent_to_safe_visa - $totalClosingVisa); //Eman
        //         // $currentLastBalanceCash = $lastBalance->balance_after_sent_to_safe;
        //         // $currentLastBalanceCash = abs($currentLastBalanceCash - ($cashTotal + $lastBalance->open_cash));

        //         // dd($cashTotal);
        //     }

        //     // $totalClosingCash =  $cashTotal;
        //     $total = [
        //         [
        //             'name' => 'cash',
        //             'value' => $totalClosingCash ?? 0,
        //         ],
        //         [
        //             'name' => 'visa',
        //             'value' => $totalClosingVisa ?? 0,
        //         ],
        //         [
        //             'name' => 'total',
        //             'value' => $totalClosingCash + $totalClosingVisa,
        //         ],
        //     ];
        //     return ResponseWithSuccessData($lang, $total, 1);
        // } catch (\Exception $e) {
        //     return respondError($e->getMessage(), 2);
        // }
    }

    public function sendToBranchSafe(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $user = auth('employee')->user();

            if ((!$user) || ($user->flag != 'cashier')) {
                return RespondWithBadRequest($lang, 4);
            }

            App::setLocale($lang);

            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'branch_id' => 'required|exists:branches,id',
                // 'balances_ids' => 'required|array',
                // 'balances_ids.*' => 'integer|exists:employee_opening_balances,id',
                'cashier_machine_id' => 'integer|required|exists:cashier_machines,id',
                'cash_amount' => 'required|numeric|min:0',
                'visa_amount' => 'nullable|numeric|min:0',
                'reason' => 'required|string',
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }



            $lastBalance = EmployeeOpeningBalance::latest()->where('cashier_machine_id', $request->cashier_machine_id)->first(); // Eman added->where('cashier_machine_id', $request->cashier_machine_id)
            // dd($lastBalance);

            $visaTotal = 0;
            $cashTotal = 0;
            $currentLastBalanceCash = ($lastBalance ? $lastBalance->balance_after_sent_to_safe : 0);
            $currentLastBalanceVisa = ($lastBalance ? $lastBalance->balance_after_sent_to_safe_visa : 0);

            if ($lastBalance) {
                if ($lastBalance->type === 1) {
                    $lastBalance_created_at = $lastBalance->created_at; //Eman

                    $today = Carbon::now()->format('Y-m-d');

                    // service function for calculating shift
                    $shift = $this->timeTableService->getTimetableForDate($user->id, $today);

                    // respond from the service function if the employee has recorded shift in the system
                    if (!($shift['status'] == false)) {
                        $shiftType = $lang == 'en' ?  $shift['data']['timetable']['name_en'] : $shift['data']['timetable']['name_ar'];
                        $shiftStart = $shift['data']['on_duty_time'];
                        $endStart = $shift['data']['off_duty_time'];
                        $user->shift_start = $shiftStart;
                        $user->shift_end = $endStart;
                        $user->shift_type = $shiftType;
                        $user->employee_schedule_id = $shift['data']['employee_schedule_id'];
                    }
                    // if the employee doesn't have recorded shift in the system
                    else {
                        $user->shift_start = null;
                        $user->shift_end = null;
                        $user->shift_type = null;
                        $user->employee_schedule_id = null;
                    }
                    $start = $today . ' ' . $user->shift_start;
                    $end = $today . ' ' . $user->shift_end;
                    $lastBalance_created_at = $lastBalance->created_at;
                    $cashierMachineId = $request->cashier_machine_id;

                    $cashTotalall = OrderTransaction::whereHas('invoice', function ($query) use ($user, $start, $end, $lastBalance_created_at) {
                        // $query->where('created_by', $user->id)
                        $query->where('invoice_type', 'invoice'); //Eman
                        // $query->where('status', '!=', 'cancelled');

                        //                        ->where('status', 'completed')
                        //                        ->where('print_status', 'done');
                    })->whereHas('order', function ($q) use ($cashierMachineId) {
                        $q->where('cashier_machine_id', $cashierMachineId);
                    })
                        ->where('paid_at', '>', $lastBalance_created_at)
                        ->where('payment_method', 'cash')
                        ->where('payment_status', 'paid')
                        //                    ->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')
                        ->selectRaw('SUM(paid) as total')
                        ->value('total');

                    $cashTotalRefund = OrderTransaction::whereHas('invoice', function ($query) use ($user, $start, $end, $lastBalance_created_at) {
                        // ->where('created_by', $user->id)
                        $query->where('invoice_type', 'credit_note'); //Eman
                        // $query->where('status', '!=', 'cancelled');

                        //                        ->where('status', 'completed')
                        //                        ->where('print_status', 'done');
                    })->whereHas('order', function ($q) use ($cashierMachineId) {
                        $q->where('cashier_machine_id', $cashierMachineId);
                    })
                        ->where('paid_at', '>', $lastBalance_created_at)
                        ->where('payment_method', 'cash')
                        ->where('payment_status', 'paid')
                        ->where('is_refund', 1)
                        //                    ->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')
                        ->selectRaw('SUM(refund) as total')
                        ->value('total');
                    // dd($cashTotalRefund);
                    $cashTotal = $cashTotalall - ($cashTotalRefund);

                    $visaTotalall = OrderTransaction::whereHas('invoice', function ($query) use ($user, $start, $end, $lastBalance_created_at) {
                        // ->where('created_by', $user->id)
                        $query->where('invoice_type', 'invoice');
                        //    ->whereBetween('updated_at', [$start, $end]);
                        //                        ->where('status', 'completed')
                        //                        ->where('print_status', 'done');
                    })->whereHas('order', function ($q) use ($cashierMachineId) {
                        $q->where('cashier_machine_id', $cashierMachineId);
                    })->where('paid_at', '>', $lastBalance_created_at) //Eman

                        ->where('payment_method', 'credit')
                        ->where('payment_status', 'paid')
                        //                    ->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')
                        ->selectRaw('SUM(paid) as total')
                        ->value('total');
                    $visaTotalRefund = OrderTransaction::whereHas('invoice', function ($query) use ($user, $start, $end, $lastBalance_created_at) {
                        // ->where('created_by', $user->id)
                        $query->where('invoice_type', 'credit_note'); //Eman
                        // $query->where('status', '!=', 'cancelled');

                        //    ->whereBetween('updated_at', [$start, $end]);
                        //                        ->where('status', 'completed')
                        //                        ->where('print_status', 'done');
                    })->whereHas('order', function ($q) use ($cashierMachineId) {
                        $q->where('cashier_machine_id', $cashierMachineId);
                    })
                        ->where('paid_at', '>', $lastBalance_created_at)
                        ->where('payment_method', 'credit')
                        ->where('payment_status', 'paid')
                        ->where('is_refund', 1)
                        //                    ->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')
                        ->selectRaw('SUM(refund) as total')
                        ->value('total');

                    $visaTotal = $visaTotalall - $visaTotalRefund;

                    // dd($currentLastBalanceCash);
                    // $currentLastBalanceCash = abs($currentLastBalanceCash - ($cashTotal + $lastBalance->open_cash)); // 300 - (300 + 200) = 200
                    // $currentLastBalanceVisa = abs($currentLastBalanceVisa - ($visaTotal + $lastBalance->open_visa));

                    //  $currentLastBalanceCash = abs($currentLastBalanceCash - ($cashTotal)); // 300 - (300 + 200) = 200
                    $currentLastBalanceCash = abs($lastBalance->open_cash + ($cashTotal) - $lastBalance->balance_after_sent_to_safe); // 300 - (300 + 200) = 200//Eman
                    //  $currentLastBalanceVisa = abs($currentLastBalanceVisa - ($visaTotal));
                    $currentLastBalanceVisa = abs($currentLastBalanceVisa - ($visaTotal) - $lastBalance->balance_after_sent_to_safe_visa);
                }
            }



            // Get the balances from the database using the provided balance IDs
            $query = EmployeeOpeningBalance::where('sent_to_safe', false)->where('cashier_machine_id', $request->cashier_machine_id);
            $balances_ids = $query->pluck('id')->toArray();
            if (!$balances_ids) {
                return respondError(($lang == 'en' ? 'All amounts have been sent to the safe.' : 'تم ارسال كل المبالغ الي الخزنه.'), 400);
            }
            $balances = $query->get();

            // Calculate the total of the close_cash and close_visa of these balances
            // $totalCloseCash = $balances->sum('close_cash');
            $totalCloseCash = $lastBalance->open_cash;
            $totalCloseVisa = $lastBalance->open_visa;
            // $totalCloseVisa = $balances->sum('close_visa');

            // $deficit_cash = $request->cash_amount - ($totalCloseCash + $currentLastBalanceCash); // 1000 + (200 - 200)
            // $deficit_visa = $request->visa_amount - ($totalCloseVisa + $currentLastBalanceVisa);

            $deficit_cash = $request->cash_amount - ($currentLastBalanceCash); // 1000 + (200 - 200)
            $deficit_visa = $request->visa_amount - ($currentLastBalanceVisa);


            // Check if the provided amounts don't exceed the available balances


            // Create a new BranchSafe record with the provided data
            $newBranchSafe = new BranchSafe();
            $newBranchSafe->branch_id = $request->branch_id;
            $newBranchSafe->cashier_id = $user->id;
            $newBranchSafe->balances_ids = json_encode($balances_ids); // Save balances as JSON

            $newBranchSafe->cash_amount = round((float) $request->cash_amount, 2);
            $newBranchSafe->reason = $request->reason;

            // $newBranchSafe->visa_amount = $request->has('visa_amount') ? $request->visa_amount : 0;
            $newBranchSafe->visa_amount = round(
                ($lastBalance->open_visa + $visaTotal - $lastBalance->balance_after_sent_to_safe_visa),
                2
            );

            $newBranchSafe->deficit_cash = round((float) $deficit_cash, 2);
            $newBranchSafe->deficit_visa = $request->has('visa_amount') ? round((float) $deficit_visa, 2) : 0;

            $newBranchSafe->cashier_machine_id = $request->cashier_machine_id;
            $newBranchSafe->created_by = $user->id;

            $newBranchSafe->save();


            EmployeeOpeningBalance::whereIn('id', $balances_ids)->update(['sent_to_safe' => true]);

            if ($lastBalance && $lastBalance->type === 1) {
                $lastBalance->update([
                    'balance_after_sent_to_safe' => $lastBalance->open_cash + $cashTotal, // 300 - (300+200)//Eman +
                    'balance_after_sent_to_safe_visa' => $lastBalance->open_visa + $visaTotal,
                    'sent_to_safe' => false,
                ]);
            }

            $message = [];

            if ($deficit_cash > 0) {
                $message[] = ($lang == 'en' ? 'Cash amount exceeds available balance.' : 'المبلغ النقدي يتجاوز الرصيد المتاح.');
            }

            if ($request->has('visa_amount') && $deficit_visa > 0) {
                $message[] = ($lang == 'en' ? 'Visa amount exceeds available balance.' : 'مبلغ الفيزا يتجاوز الرصيد المتاح.');
            }

            if ($deficit_cash < 0) {
                $message[] = ($lang == 'en' ? 'Cash amount is less than available balance.' : 'المبلغ النقدي أقل من الرصيد المتاح.');
            }

            if ($request->has('visa_amount') && $deficit_visa < 0) {
                $message[] = ($lang == 'en' ? 'Visa amount is less than available balance.' : 'مبلغ الفيزا أقل من الرصيد المتاح.');
            }

            $newBranchSafe->deficit_cash = round($newBranchSafe->deficit_cash, 2);
            $newBranchSafe->deficit_visa = round($newBranchSafe->deficit_visa, 2);
            $newBranchSafe->save();
            $data = [
                "alert" => $message,
                "newBranchSafe" => $newBranchSafe,
            ];
            // Return success response with the created BranchSafe record
            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            // Handle any exception and return a response
            return respondError($e->getMessage(), 2);
        }
    }

    public function showCashierData(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $balance = $this->CashierBranchSafeService->show($id, $lang);

        // 👇 Check if branch safe not found and return directly
        if (isset($balance['code']) && $balance['code'] == 401) {
            return response()->json($balance, 200); // use 200 to avoid exception handling
        }

        // ✅ Only wrap when real data exists
        return ResponseWithSuccessData($lang, $balance, 1);
    }
    public function getOrderCountsForShift(Request $request, $cashierMachineId)
    {
        $lang = $request->header('lang', 'ar');
        $user = auth('employee')->user();

        // Get opening balance for this employee and machine
        $openingBalance = EmployeeOpeningBalance::where('employee_id', $user->id)
            ->where('cashier_machine_id', $cashierMachineId)
            ->where('type', 1)
            ->orderBy('date', 'desc')
            ->orderBy('time', 'asc')
            ->first();

        if (!$openingBalance) {
            return respondError(($lang == 'en' ? 'No valid opening balance found.' : 'لم يتم العثور على رصيد فتح .'), 400);
        }

        $start = $openingBalance->date . ' ' . $openingBalance->time;

        $ordersQuery = Order::where('branch_id', $user->branch_id)
            ->where('cashier_id', $user->id)
            ->where('cashier_machine_id', $cashierMachineId)
            ->where('created_at', '>=', $start);

        $orders = $ordersQuery->get();

        $orderTypes = [
            'Delivery',
            'Takeaway',
            'dine-in',
        ];

        $orderTypeCounts = [];
        foreach ($orderTypes as $type) {
            $orderTypeCounts[$type] = $orders->where('type', $type)->count();
        }

        $data =  [
            'orders_number'   => $orders->count(),
            'delivery_count'  => $orderTypeCounts['Delivery'],
            'takeaway_count'  => $orderTypeCounts['Takeaway'],
            'dinein_count'    => $orderTypeCounts['dine-in'],
        ];

        return ResponseWithSuccessData($lang, $data, 1);
    }

    // public function getBalancesForBranchSafe(Request $request)
    // {
    //     try {
    //         $lang = $request->header('lang', 'ar');
    //         $user = auth('employee')->user();

    //         // Check if user exists and if the user is a cashier
    //         if ((!$user) || ($user->flag != 'cashier')) {
    //             return RespondWithBadRequest($lang, 4);
    //         }

    //         App::setLocale($lang);

    //         // Validate the incoming request for cashier_machine_id and branch_id
    //         $validator = Validator::make($request->all(), [
    //             'cashier_machine_id' => 'required|exists:cashier_machines,id', // Validate cashier_machine_id
    //             'branch_id' => 'required|exists:branches,id',  // Ensure the branch exists
    //         ]);

    //         if ($validator->fails()) {
    //             return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
    //         }

    //         // Get the cashier machine using the provided cashier_machine_id
    //         $cashierMachine = CashierMachine::find($request->cashier_machine_id);

    //         // Check if the cashier machine is associated with the correct branch
    //         if ($cashierMachine->branch_id != $request->branch_id) {
    //             return respondError(($lang == 'en' ? 'Cashier machine does not belong to the specified branch.' : 'جهاز الصراف الآلي لا ينتمي إلى الفرع المحدد.'), 403);
    //         }

    //         // Retrieve the balances based on the provided cashier_machine_id
    //         $balances = EmployeeOpeningBalance::where('cashier_machine_id', $request->cashier_machine_id)
    //             ->where('type', 1) // Assuming '1' indicates an open balance
    //             ->get();

    //         // Return the balances
    //         return ResponseWithSuccessData($lang, $balances, 1);

    //     } catch (\Exception $e) {
    //         // Handle any exception and return a response
    //         return respondError($e->getMessage(), 2);
    //     }
    // }
}
