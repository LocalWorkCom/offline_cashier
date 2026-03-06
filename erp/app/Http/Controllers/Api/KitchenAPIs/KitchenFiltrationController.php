<?php

namespace App\Http\Controllers\Api\KitchenAPIs;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Country;
use App\Models\Employee;
use App\Models\Nationality;
use App\Models\ChifManagerLog;
use App\Models\Order;
use App\Services\HR_Services\TimetableService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

class KitchenFiltrationController extends Controller
{
    public function dishFiltration(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();
        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $branch_id = $employee->branch_id;
        $shift_start = $employee->shift_start;
        $shift_end = $employee->shift_end;
        $shift_type = $employee->shift_type;
        $cross_day = $employee->cross_day;

        if(checkDishBranches($branch_id) == 0){
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => __('validation.dataNotFound'),
                'data' => null,
                'errorData' => ['error' => __('validation.dataNotFound')]
            ], 200);
        }

        foreach($request->dish_filtration as $dish_filtration){
            foreach($dish_filtration['dish_categories'] as $dish_categories){
                if(checkDishBranchCategories($dish_categories['dish_category_id'], $branch_id) == 0){
                    return response()->json([
                        'code' => 400,
                        'status' => false,
                        'message' => __('validation.dataNotFound'),
                        'data' => null,
                        'errorData' => ['error' => __('validation.dataNotFound')]
                    ], 200);
                }
                foreach($dish_categories['dishes'] as $dish_id){
                    if(checkMenuDishes($dish_id, $branch_id) == 0){
                        return response()->json([
                            'code' => 400,
                            'status' => false,
                            'message' => __('validation.dataNotFound'),
                            'data' => null,
                            'errorData' => ['error' => __('validation.dataNotFound')]
                        ], 200);
                    }
                }
            }
        }

        //return $request->dish_filtration;

        $chifManagerLog = new ChifManagerLog();
        $chifManagerLog->employee_id = $employee->id;
        $chifManagerLog->branch_id = $branch_id;
        $chifManagerLog->dish_filtration = json_encode($request->dish_filtration);
        $chifManagerLog->date = date('Y-m-d');
        $chifManagerLog->shift_start = $shift_start;
        $chifManagerLog->shift_end = $shift_end;
        $chifManagerLog->shift_type = $shift_type;
        $chifManagerLog->cross_day = $cross_day;
        $chifManagerLog->save();

        $response = "طلب صحيح";
        return ResponseWithSuccessData($lang, $response, 1);
    }

    public function getDishFiltration(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();
        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $date = date('Y-m-d');
        $chifManagerLog = ChifManagerLog::where('employee_id', $employee->id)->where('date', $date)->latest()->first();
        if($chifManagerLog){
            $dish_filtration = json_decode($chifManagerLog->dish_filtration);
            return ResponseWithSuccessData($lang, $dish_filtration, 1);
        }

    }

    public function splitDishesOnOrders(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        if(checkOrders($request->orderId) == 0){
        //if(checkOrders($orderId) == 0){
                return response()->json([
                'code' => 400,
                'status' => false,
                'message' => __('validation.dataNotFound'),
                'data' => null,
                'errorData' => ['error' => __('validation.dataNotFound')]
            ], 200);
        }

        $order = Order::where('id', $request->orderId)->first();

        if($order->date != date('Y-m-d')){
                return response()->json([
                'code' => 400,
                'status' => false,
                'message' => __('validation.dataNotFound'),
                'data' => null,
                'errorData' => ['error' => __('validation.dataNotFound')]
            ], 200);
        }

        $twentyFourHoursAgo = Carbon::now()->subHours(24);
        if($order->created_at <= $twentyFourHoursAgo){
                return response()->json([
                'code' => 400,
                'status' => false,
                'message' => __('validation.dataNotFound'),
                'data' => null,
                'errorData' => ['error' => __('validation.dataNotFound')]
            ], 200);
        }

        return splitDishesOnOrder($request->orderId);
        //return splitDishesOnOrder($orderId);
    }

    public function splitDishesOnAllOrders(Request $request)
    {
        // $lang = $request->header('lang', 'ar');
        // App::setLocale($lang);

        $orderId = $request->orderId;
        $employeeId = $request->employeeId;
        return splitDishesOnAllOrders($orderId, $employeeId);

        //if(checkOrders($request->orderId) == 0){
        if(checkOrders($orderId) == 0){
                return response()->json([
                'code' => 400,
                'status' => false,
                'message' => __('validation.dataNotFound'),
                'data' => null,
                'errorData' => ['error' => __('validation.dataNotFound')]
            ], 200);
        }

        $order = Order::where('id', $orderId)->first();

        if($order->date != date('Y-m-d')){
                return response()->json([
                'code' => 400,
                'status' => false,
                'message' => __('validation.dataNotFound'),
                'data' => null,
                'errorData' => ['error' => __('validation.dataNotFound')]
            ], 200);
        }

        $twentyFourHoursAgo = Carbon::now()->subHours(24);
        if($order->created_at <= $twentyFourHoursAgo){
                return response()->json([
                'code' => 400,
                'status' => false,
                'message' => __('validation.dataNotFound'),
                'data' => null,
                'errorData' => ['error' => __('validation.dataNotFound')]
            ], 200);
        }
        //return splitDishesOnOrder($request->orderId);
    }

    public function profile(Request $request)
    {
        try{
            $lang = $request->header('lang', 'ar');
            App::setLocale($lang);
            $employee = auth('employee')->user();
            if (!$employee) {
                return RespondWithBadRequest($lang, 4);
            }

            $teamShift = getWorkingEmployeesByBranchAndTime($employee->branch_id, date('Y-m-d H:i:s'));
            $employee_flag = array('chef', 'kitchen manager' ,'kitchen staff' ,'Head Chef');
            $teamWork = Employee::whereIn('flag', $employee_flag)
                ->whereIn('id', $teamShift['working_employee_ids'])
                ->where('id', '!=', $employee->id)
                ->get();

            $employee['branch_name'] = $employee->branch->name;
            $chefData = $employee;

            // Get work days first
            $workDays = getEmployeeWorkDays($employee->id, $lang);

            // Set time indicators based on language
            $fromText = ($lang == 'ar') ? 'من' : 'from';
            $toText = ($lang == 'ar') ? 'الى' : 'to';

            // Convert time format for each work day entry
            if (is_array($workDays)) {
                $formattedWorkDays = [];
                foreach ($workDays as $dayEntry) {
                    // Extract times using regex
                    if (preg_match('/^(.*): من(.*) الى(.*)$/', $dayEntry, $matches)) {
                        $dayName = trim($matches[1]);
                        $startTime = trim($matches[2]);
                        $endTime = trim($matches[3]);

                        // Convert to 12-hour format
                        $formattedStart = date("g:i A", strtotime($startTime));
                        $formattedEnd = date("g:i A", strtotime($endTime));

                        // Translate AM/PM if Arabic
                        if ($lang == 'ar') {
                            $formattedStart = str_replace(['AM', 'PM'], ['صباحا', 'مساء'], $formattedStart);
                            $formattedEnd = str_replace(['AM', 'PM'], ['صباحا', 'مساء'], $formattedEnd);
                        }

                        // Use language-specific indicators
                        $formattedWorkDays[] = "$dayName: $fromText $formattedStart $toText $formattedEnd";
                    } else {
                        // If format doesn't match, keep original
                        $formattedWorkDays[] = $dayEntry;
                    }
                }
                $workDays = $formattedWorkDays;
            }

            $chefData['workDays'] = $workDays;
            $chefData['teamWork'] = $teamWork;

            $chefData->makeHidden('branch');

            return ResponseWithSuccessData($lang, $chefData, 1);
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Error head chef profile.'];
        }
    }
}
