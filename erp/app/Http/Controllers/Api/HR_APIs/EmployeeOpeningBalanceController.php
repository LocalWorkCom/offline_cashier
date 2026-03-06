<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\EmployeeOpeningBalance;
use App\Models\Setting;
use App\Models\CashierMachineLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Services\HR_Services\TimetableService;

class EmployeeOpeningBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function open_day_balance(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $validateData = Validator::make($request->all(), [
                'employee_id' => 'required|integer|exists:employees,id',
                'employee_schedule_id' => 'required|integer|exists:employee_schedules,id',
                'cashier_machine_id' => 'required|integer|exists:cashier_machines,id',
                'open_cash' => 'required|numeric|min:0',
                'open_visa' => 'required|numeric|min:0',
                'date' => 'required|date|after:yesterday'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            $user_id = Auth::guard('api')->user()->id;
            $employee_opening_balance = new EmployeeOpeningBalance();
            $employee_opening_balance->employee_id = $request->employee_id;
            $employee_opening_balance->employee_schedule_id = $request->employee_schedule_id;
            $employee_opening_balance->cashier_machine_id = $request->cashier_machine_id;
            $employee_opening_balance->open_cash = $request->open_cash;
            $employee_opening_balance->open_visa = $request->open_visa;
            $employee_opening_balance->date = $request->date;
            $employee_opening_balance->type = 1;
            $employee_opening_balance->created_by = $user_id;
            $employee_opening_balance->save();

            return ResponseWithSuccessData($lang, $employee_opening_balance, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function close_day_balance(Request $request)
    {
        //try {
            $lang =  $request->header('lang', 'en');
            $validateData = Validator::make($request->all(), [
                'cashier_machine_id' => 'required|integer|exists:cashier_machines,id',
                'close_cash' => 'required|numeric|min:0',
                'close_visa' => 'required|numeric|min:0',
                'date' => 'required|date|after:yesterday'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            $user_id = Auth::guard('api')->user()->id;

            $employee_opening_balance = EmployeeOpeningBalance::where('cashier_machine_id', $request->cashier_machine_id)->where('date', $request->date)->where('type', 1)->first();
            if(!$employee_opening_balance){
                return RespondWithBadRequestData($lang, 2);
            }
            
            $deficit_cash = 0;
            $deficit_visa = 0;            
            $order_real_total_cash = CalculateTotalOrders($employee_opening_balance->cashier_machine_id, $employee_opening_balance->employee_id, $employee_opening_balance->date, 'cash');
            $order_real_total_visa = CalculateTotalOrders($employee_opening_balance->cashier_machine_id, $employee_opening_balance->employee_id, $employee_opening_balance->date, 'credit_card');

            $deficit_cash = CalculateDeficitOrder($employee_opening_balance->open_cash, $request->close_cash, $order_real_total_cash);
            $deficit_visa = CalculateDeficitOrder($employee_opening_balance->open_visa, $request->close_visa, $order_real_total_visa);
            $total_deficit = $deficit_cash + $deficit_visa;

            $setting_closing_cashier = Setting::where('id', 1)->first()->closing_cashier;
            if($total_deficit != 0){
                $cashier_machine_log = new CashierMachineLog();
                $cashier_machine_log->employee_id = $employee_opening_balance->employee_id;
                $cashier_machine_log->cashier_machine_id = $employee_opening_balance->cashier_machine_id;
                $cashier_machine_log->employee_opening_balance_id = $employee_opening_balance->id;
                $cashier_machine_log->deficit_cash = $deficit_cash;
                $cashier_machine_log->deficit_visa = $deficit_visa;
                $cashier_machine_log->date = $request->date;
                $cashier_machine_log->time = date('H:s:i');
                $cashier_machine_log->created_by = $user_id;
                $cashier_machine_log->save();
            }   

            if($setting_closing_cashier == 0 && $total_deficit != 0){
                return RespondWithBadRequestNotClosing($lang, 2);
            }
           
            $employee_opening_balance->close_cash = $request->close_cash;
            $employee_opening_balance->close_visa = $request->close_visa;
            $employee_opening_balance->real_cash = $order_real_total_cash;
            $employee_opening_balance->real_visa = $order_real_total_visa;
            $employee_opening_balance->deficit_cash = $deficit_cash;
            $employee_opening_balance->deficit_visa = $deficit_visa;
            $employee_opening_balance->type = 2;
            $employee_opening_balance->modified_by = $user_id;
            $employee_opening_balance->save();

            return ResponseWithSuccessData($lang, $employee_opening_balance, 1);
        // } catch (\Exception $e) {
        //     return RespondWithBadRequestData($lang, 2);
        // }
    }


}
