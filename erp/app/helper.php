<?php

use Carbon\Carbon;
use App\Models\Area;
use App\Models\Dish;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Order;
use App\Models\Shift;
use App\Models\Table;
use App\Models\Branch;
use App\Models\Coupon;
use GuzzleHttp\Client;
use App\Models\APICode as ApICode;

use App\Models\Country;
use App\Models\Cuisine;
use App\Models\Setting;
use App\Models\Discount;
use App\Models\DishSize;
use App\Models\Employee;
use App\Models\ItemCode;
use App\Models\DishAddon;
use App\Events\ChefNotify;
use App\Events\DishStatus;
use App\Models\Attendance;
use App\Models\BranchMenu;
use App\Models\BranchTime;
use App\Models\DishDetail;
use App\Models\OrderAddon;
use App\Events\ChefNotify2;
use App\Events\DishChanges;
use App\Models\ChatChannel;
use App\Models\OrderDetail;
use App\Models\pointSystem;
use App\Models\ShiftDetail;
use Illuminate\Support\Str;
use App\Models\BranchCoupon;
use App\Models\BranchRegion;
use App\Models\DishCategory;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\ProductBrand;
use App\Models\SystemModule;
use Illuminate\Http\Request;
use App\Models\ActionBackLog;
use App\Models\AddonCategory;
use App\Models\BranchSetting;
use App\Models\ClientAddress;
use App\Models\BranchMenuSize;
use App\Models\CashierMachine;
use App\Models\ChifManagerLog;
use App\Models\OpeningBalance;
use App\Models\VehicleSetting;
use App\Models\BranchMenuAddon;
use App\Models\CuisineCategory;
use App\Models\DeliverySetting;
use App\Models\PaymentPolicies;
use App\Models\EmployeeSchedule;
use App\Models\InventorySetting;
use App\Models\MenusIntegration;
use App\Models\OrderTransaction;
use App\Models\TableReservation;
use App\Events\NotificationEvent;
use App\Models\InventoryEmployee;
use App\Models\RolePermissionLog;
use App\Models\BranchMenuCategory;
use App\Models\CancellationReason;
use App\Models\CashPaymentSetting;
use App\Models\ProductTransaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\ChefCuisineCategory;
use App\Models\TableReservationLog;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Models\BiometricTransaction;
use App\Models\MenusIntegrationDish;
use App\Models\NotificationCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use App\Models\ProductTransactionsLog;
use App\Models\BranchMenuAddonCategory;
use App\Models\PurchaseInvoicesDetails;
use App\Models\MenusIntegrationDishSize;
use Illuminate\Support\Facades\Response;
use App\Models\MenusIntegrationDishAddon;
use App\Services\HR_Services\TimetableService;
use App\Events\Notification as EventsNotification;
use App\Http\Controllers\Api\InventoryAPIs\PurchaseRequestController;
use App\Models\Currency;
use App\Models\CurrencyExchange;
use App\Models\Facility;
use App\Models\Journal;
use GuzzleHttp\Promise\Create;

//Respond functions to be removed(updated) in API responses
function RespondWithSuccessRequest($lang, $code)
{
    //bad or invalid request missing some params
    $response = new stdClass();
    $APICode = ApICode::where('code', $code)->first();
    $response_array = array(
        'status' => true,
        // 'apiTitle' => $lang == 'ar' ? $APICode->api_code_title_ar : $APICode->api_code_title_en,
        'message' => $lang == 'ar' ? $APICode->api_code_message_ar : $APICode->api_code_message_en,
        'code' => 200
    );
    $response_code = 200;
    $response = Response::json($response_array, $response_code);
    return $response;
}
function RespondWithBadRequest($lang, $code)
{
    $APICode = ApICode::where('code', $code)->first();
    $response_array = array(
        'status' => false,
        // 'apiTitle' => $lang == 'ar' ? $APICode->api_code_title_ar : $APICode->api_code_title_en,
        'message' => $lang == 'ar' ? $APICode->api_code_message_ar : $APICode->api_code_message_en,
        'code' => 401
    );
    $response_code = 401;
    $response = Response::json($response_array, $response_code);
    return $response;
}
function RespondWithUnauthorizedRequest($lang, $code)
{
    $APICode = ApICode::where('code', $code)->first();
    $response_array = array(
        'status' => false,
        // 'apiTitle' => $lang == 'ar' ? $APICode->api_code_title_ar : $APICode->api_code_title_en,
        'message' => $lang == 'ar' ? $APICode->api_code_message_ar : $APICode->api_code_message_en,
        'code' => 401
    );
    $response_code = 401;
    $response = Response::json($response_array, $response_code);
    return $response;
}
function CustomRespondWithBadRequest($message)
{
    $response_array = array(
        'status' => false,
        // 'apiTitle' => $lang == 'ar' ? $APICode->api_code_title_ar : $APICode->api_code_title_en,
        'message' => $message,
        'code' => 400
    );
    $response_code = 400;
    $response = Response::json($response_array, $response_code);
    return $response;
}
function RespondWithBadRequestWithData($data)
{
    $response_array = array(
        'status' => false,
        // 'apiTitle' => trans('validation.validator_title'),
        'message' => trans('validation.validator_msg'),
        'code' => 400,
        'data' => $data
    );
    $response_code = 400;
    $response = Response::json($response_array, $response_code);
    return $response;
}
function ResponseWithSuccessData($lang, $data, $code)
{
    $APICode = ApiCode($code);
    // dd($APICode);
    $response_array = array(
        'status' => true,
        // 'apiTitle' => $lang == 'ar' ? $APICode->api_code_title_ar : $APICode->api_code_title_en,
        'message' => $lang == 'ar' ? $APICode->api_code_message_ar : $APICode->api_code_message_en,
        'code' => 200,
        'data'   => $data
    );
    $response_code = 200;
    $response = Response::json($response_array, $response_code);

    return $response;
}
function ResponseWithSuccessDataPaginated($lang, $data, $code)
{
    $APICode = ApiCode($code);
    $response_array = [
        'status' => true,
        'message' => $lang == 'ar' ? $APICode->api_code_message_ar : $APICode->api_code_message_en,
        'code' => 200,
    ];

    // Ensure 'data' exists, set to null if missing or empty
    if (!isset($data['data']) || empty($data['data'])) {
        $data['data'] = null;
    }

    $response_code = 200;
    $response_array = array_merge($response_array, $data);

    return Response::json($response_array, $response_code);
}
function RespondWithBadRequestData($lang, $code)
{
    $APICode = ApiCode($code);
    $response_array = array(
        'status' => true,
        // 'apiTitle' => $lang == 'ar' ? $APICode->api_code_title_ar : $APICode->api_code_title_en,
        'message' => $lang == 'ar' ? $APICode->api_code_message_ar : $APICode->api_code_message_en,
        'code' => 400,
        'data'   => null
    );
    $response_code = 200;
    $response = Response::json($response_array, $response_code);
    return $response;
}
function RespondWithBadRequestNoChange()
{
    $response_array = array(
        'status' => true,
        // 'apiTitle' => trans('validation.NoChange'),
        'message' => trans('validation.NoChangeMessage'),
        'code' => 401,
        'data'   => []
    );
    $response_code = 200;
    $response = Response::json($response_array, $response_code);
    return $response;
}
function RespondWithBadRequestAlreadyDeleted()
{
    $response_array = array(
        'status' => true,
        'message' => trans('validation.AlreadyDeleted'),
        'code' => 401,
        'data'   => []
    );
    $response_code = 200;
    $response = Response::json($response_array, $response_code);
    return $response;
}
function RespondWithBadRequestNoEmpty()
{
    $response_array = array(
        'status' => true,
        // 'apiTitle' => trans('validation.NoChange'),
        'message' => trans('validation.NotAllowMessage'),
        'code' => 401,
        'data'   => []
    );
    $response_code = 200;
    $response = Response::json($response_array, $response_code);
    return $response;
}
function RespondWithBadRequestNotExist()
{

    $response_array = array(
        'status' => false,  // Set success to false to indicate an error
        // 'apiTitle' => trans('validation.NotExist'),
        'message' => trans('validation.NotExistMessage'),
        'code' => 401,
        'data'   => []
    );

    // Change the response code to 404 for "Not Found"
    $response_code = 200;

    return Response::json($response_array, $response_code);
}
function RespondWithBadRequestNotHavePermeation()
{
    $response_array = array(
        'status' => false,
        // 'apiTitle' => trans('validation.NotHavePermeation'),
        'message' => trans('validation.NotHavePermeationMessage'),
        'code' => 403,
        'data'   => []
    );
    $response_code = 200;
    return Response::json($response_array, $response_code);
}
function RespondWithBadRequestIsDefault()
{
    $response_array = array(
        'status' => false,
        // 'apiTitle' => trans('validation.NotAvailable'),
        'message' => trans('validation.IsDefaultMessageForBranch'),
        'code' => 400,
        'data'   => []
    );
    $response_code = 400;
    return Response::json($response_array, $response_code);
}
function RespondWithBadRequestIsDefaultNoChange()
{
    $response_array = array(
        'status' => false,
        // 'apiTitle' => trans('validation.NotAvailable'),
        'message' => trans('validation.IsDefaultMessageForBranchNoChange'),
        'code' => 400,
        'data'   => []
    );
    $response_code = 400;
    return Response::json($response_array, $response_code);
}
function RespondWithBadRequestNotDate()
{
    $response_array = array(
        'status' => false,
        // 'apiTitle' => trans('validation.NotDate'),
        'message' => trans('validation.NotDateMessage'),
        'code' => 400,
        'data'   => []
    );
    $response_code = 200;
    return Response::json($response_array, $response_code);
}
function RespondWithBadRequestNotAdd()
{
    $response_array = array(
        'status' => false,
        // 'apiTitle' => trans('validation.NotAddMore'),
        'message' => trans('validation.NotAddMoreMessage'),
        'code' => 400,
        'data'   => []
    );
    $response_code = 200;
    return Response::json($response_array, $response_code);
}
function RespondWithBadRequestNotAvailable()
{
    $response_array = array(
        'status' => false,
        // 'apiTitle' => trans('validation.NotAvailable'),
        'message' => trans('validation.NotAvailableMessage'),
        'code' => 404,
        'data'   => []
    );
    $response_code = 200;
    return Response::json($response_array, $response_code);
}
function RespondWithBadRequestNotClosing()
{
    $response_array = array(
        'status' => false,  // Set success to false to indicate an error
        // 'apiTitle' => trans('validation.NotAvailable'),
        'message' => trans('validation.NotClosingMessage'),
        'code' => 403,
        'data'   => []
    );
    $response_code = 200;
    return Response::json($response_array, $response_code);
}
function RespondWithBadRequestNoLeave()
{
    $response_array = array(
        'status' => false,
        // 'apiTitle' => trans('validation.NoLeave'),
        'message' => trans('validation.NoLeaveMessage'),
        'code' => 400,
        'data'   => []
    );
    $response_code = 200;
    return Response::json($response_array, $response_code);
}
function RespondWithBadRequestDataExist()
{
    $response_array = array(
        'status' => false,  // Set success to false to indicate an error
        // 'apiTitle' => trans('validation.DataExist'),
        'message' => trans('validation.DataExistMessage'),
        'code' => 401,
        'data'   => []
    );
    $response_code = 200;
    return Response::json($response_array, $response_code);
}
////////////////////////////////////////////////

/////Respond functions to be used in API responses/////

// Returns a JSON error response with a custom message, status code 401, and empty data.

function RespondWithErrorMsg($message)
{
    $response_array = array(
        'status' => false,
        // 'apiTitle' => trans('validation.DataExist'),
        'message' => $message, //trans('validation.DataExistMessage')
        'code' => 401,
        'data'   => []
    );
    $response_code = 200;
    return Response::json($response_array, $response_code);
}
// Returns a JSON success response with a custom message, status code 200, and empty data.
function RespondWithSuccessMsg($message)
{
    $response_array = array(
        'status' => true,
        // 'apiTitle' => trans('validation.DataExist'),
        'message' => $message, //trans('validation.DataExistMessage')
        'code' => 200,
        'data'   => []
    );
    $response_code = 200;
    return Response::json($response_array, $response_code);
}

// Returns a JSON success response with a custom message, specified data, and status code 200.
function respondSuccess($message, $data = [])
{
    $response_array = array(
        'status' => true,
        'message' => $message,
        'code' => 200,
        'data' => $data
    );
    $response_code = 200;
    return Response::json($response_array, $response_code);
}
// Returns a JSON error response with a custom message, specified error code, optional error details, and appropriate HTTP status code.
function respondError2($message, $code, $errors = [])
{
    return response()->json([
        'code' => $code,
        'status' => false,
        'message' => $message,
        'data' => null,
        'errorData' => [
            'error' => is_array($errors) ? $errors : [$errors] // 🔥 always array
        ],
        'validation_type' => false
    ], $code);
}
function respondError($error, $code = 400, $errorMessages = [])
{
    $response = [
        'code' => $code,
        'status' => false,
        'message' => $error,
        'data' => null,
        'errorData' => !empty($errorMessages) ? $errorMessages : null,
        'validation_type' => true,
    ];

    // Force the actual HTTP response code
    return response()->json($response, $code)
        ->setStatusCode($code);
}
function respondErrorData($error, $code, $errorMessages = [])
{
    if ($code == 404) {
        $responseCode = 404;
    } elseif ($code == 500) {
        $responseCode = 500;
    } else {
        $responseCode = 200;
    }
    $response = [
        'code' => $code,
        'status' => false,
        'message' => $error,
        'data' => null,
        'errorData' => !empty($errorMessages) ? ['error' => $errorMessages] : null,
        'validation_type' => false,
    ];

    // return response()->json($response, $responseCode);
    return response()->json($response, $code);
}
// Returns a JSON error response with a custom message, specified error code, and error details, with validation_type set to false.
function respondEmptyData($error, $code, $errorMessages = [])
{
    if ($code == 404) {
        $responseCode = 404;
    } elseif ($code == 500) {
        $responseCode = 500;
    } else {
        $responseCode = 200;
    }
    $response = [
        'code' => $code,
        'status' => true,
        'message' => $error,
        'data' => null,
        'errorData' => null
    ];

    return response()->json($response, $responseCode);
}
////////////////////////////////////////////////
// Retrieves the next sequential ID for a given table and type by counting existing records and adding 1.
function GetNextID($table, $type)
{
    $nextId  = DB::table($table)->where('make_type', $type)->count() + 1;
    return $nextId;
}
// Retrieves the highest ID value from the specified table.
function GetLastID($table)
{
    $nextId  = DB::table($table)->max('id');
    return $nextId;
}
// Logs user action details, including user ID, function, controller, action name, action ID, and timestamp, to the ActionBackLog model.
function ActionBackLog($IDUser, $function_name, $controller_name, $action_name, $action_id)
{
    $ActionBackLog = new ActionBackLog();
    $ActionBackLog->IDUser = $IDUser;
    $ActionBackLog->function_name = $function_name;
    $ActionBackLog->controller_name = $controller_name;
    $ActionBackLog->action_name = $action_name;
    $ActionBackLog->action_id = $action_id;
    $ActionBackLog->date_time = date('Y-m-d H:i:s');
    $ActionBackLog->save();
}
// Constructs and returns the base URL of the application, using HTTPS if available, or HTTP otherwise, with a special case for 'erp.test/'.
function BaseUrl()
{
    $myUrl = "";
    if (isset($_SERVER['HTTPS'])) $myUrl .= "https://";
    else $myUrl .= "http://";
    if ($_SERVER['SERVER_NAME'] == "erp.test/") return "http://erp.test/";
    return $myUrl . $_SERVER['SERVER_NAME'];
}
// Adds a specified number of days to a given date using Carbon and returns the resulting date in 'Y-m-d' format.
function AddDays($date, $days_number)
{
    $startDate = Carbon::parse($date);
    $daysNumber = $days_number;

    $next_date = $startDate->copy()->addDays($daysNumber);
    return $next_date->toDateString();
}
// Formats a time string from 'H:i:s' to 'h:i A' (12-hour format with AM/PM) and replaces AM/PM with Arabic characters 'ص' and 'م'.
function formatTime($time)
{
    $to = Carbon::createFromFormat('H:i:s', $time)->format('h:i A');
    $toDay = str_replace(['AM', 'PM'], ['ص', 'م'], $to);
    return $toDay;
}
// Retrieves the first API code record from the ApICode model matching the provided code.
function ApiCode($code)
{
    $APICode = ApICode::where('code', $code)->first();
    return $APICode;
}
// Translates specified columns in the data array based on the provided language ('ar' or 'en'), appending '_ar' or '_en' to column names.
function translateDataColumns($data, $lang, $translateColumns)
{
    $translatedData = $data;
    foreach ($translateColumns as $column) {
        $translatedColumn = $column . ($lang === 'ar' ? '_ar' : '_en');
        if (array_key_exists($translatedColumn, $data)) {
            $translatedData[$column] = $data[$translatedColumn];
        } else {
            $translatedData[$column] = null;
        }
    }
    return $translatedData;
}
// Removes specified columns from the data array by filtering out keys listed in columnsToRemove.
function removeColumns($data, $columnsToRemove)
{
    return array_diff_key($data, array_flip($columnsToRemove));
}
// Uploads a file to the specified path, generates a unique filename using the model ID and timestamp, saves the file path to the model, and persists the changes.
function UploadFile($path, $image, $model, $request)
{
    $thumbnail = $request;
    $destinationPath = public_path($path);
    $filename = $model->id . time() . '.' . $thumbnail->getClientOriginalExtension();

    $thumbnail->move($destinationPath, $filename);

    $filePath = asset($path) . '/' . $filename;
    $filePath = url($filePath);

    $model->$image = $filePath;

    $model->save();
}

function UploadFile2($path, $image, $model, $request)
{
    $thumbnail = $request;
    $destinationPath = public_path($path);
    $filename = $model->id . '_' . uniqid() . '.' . $thumbnail->getClientOriginalExtension();

    $thumbnail->move($destinationPath, $filename);

    $filePath = asset($path . '/' . $filename);
    $filePath = url($filePath);

    $model->$image = $filePath;
    $model->save();
}

// Uploads a video file to the specified path, validates the file, generates a unique filename using the model ID and timestamp, saves the file path to the model, and persists the changes.
function UploadVideo($path, $fileAttribute, $model, $file)
{
    if (!$file->isValid()) {
        throw new \Exception('Invalid file uploaded.');
    }
    $destinationPath = public_path($path);

    $filename = $model->id . '_' . time() . '.' . $file->getClientOriginalExtension();
    $file->move($destinationPath, $filename);
    $filePath = asset($path . '/' . $filename);
    $model->$fileAttribute = $filePath;
    $model->save();

    // return $filePath;
}
// Generates a four-digit code by incrementing the existing code for a specific table record by ID, or returns '0000' if no ID is provided.
// function GenerateCode($table, $table_id = 0)
// {
//     if ($table_id) {
//         $table = DB::table($table)->where('id', $table_id)->first();
//         $table_code = $table->code;
//         $numberString = $table_code;

//         $number = (int) $numberString;



//         $number++;
//         $code = sprintf('%04d', $number); // '0001'
//         // $code += 1;
//     } else {
//         $code = '0000';
//     }

//     return $code;
// }
function GenerateCode($table)
{
    // Get the maximum existing code value
    $lastCode = DB::table($table)->max('code');

    if ($lastCode) {
        $number = (int) $lastCode; // Convert to number
        $number++;
        $code = sprintf('%04d', $number); // Format with leading zeros

    } else {
        $code = '0001'; // Start with 0001 for first record
    }

    return $code;
}
// Verifies if the authenticated user's API token is valid and not expired, returning true if valid, false otherwise.
function CheckToken()
{
    $lang = 'ar';
    $User = auth('api')->user();
    if (!$User) {
        return false;
    }
    $token = DB::table('oauth_access_tokens')
        ->select('expires_at')
        ->orderBy('created_at', 'desc')
        ->where('user_id', $User->id)
        ->first();

    if ($token->expires_at < Carbon::now()->toDateTimeString()) {
        return false;
    }
    return true;
}
// Verifies if the authenticated employee's API token (named 'employeeToken') is valid and not expired, returning true if valid, false otherwise.
function CheckTokenEmployee()
{
    $employee = auth('employee')->user();
    if (!$employee) {
        return false;
    }
    $token = DB::table('oauth_access_tokens')
        ->select('expires_at')
        ->orderBy('created_at', 'desc')
        ->where('name', 'employeeToken')
        ->where('user_id', $employee->id)
        ->first();

    if ($token->expires_at < Carbon::now()->toDateTimeString()) {
        return false;
    }
    return true;
}
// Deletes a file from the specified path if it exists.
function DeleteFile($path, $filename)
{
    $filePath = public_path($path . '/' . $filename);
    if (File::exists($filePath)) {
        File::delete($filePath);
    }
}
// Checks if a specific value exists in the given column of a table, excluding soft-deleted records.
function CheckExistColumnValue($table, $column, $value)
{
    return DB::table($table)
        ->where($column, $value)
        ->whereNull('deleted_at')
        ->exists();
}
// Checks if a coupon is valid based on its ID, amount spent, date range, minimum spend requirement, and active status.
function CheckCouponValid($id, $amount)
{
    $coupon = Coupon::find($id);

    if (!$coupon) {
        return false;
    }
    $currentDateTime = Carbon::now();

    // Convert start_date and end_date to Carbon instances
    $startDate = Carbon::parse($coupon->start_date);
    $endDate = Carbon::parse($coupon->end_date);

    // Validate conditions
    $isValidDate = $currentDateTime->between($startDate, $endDate);
    $isMinimumSpendMet = $coupon->minimum_spend <= $amount;
    $isActive = $coupon->is_active;

    if ($isValidDate && $isMinimumSpendMet && $isActive == 1) {
        return true;
    }

    return false;
}
// Checks if a user has already used a specific coupon by verifying if an order exists with the given coupon ID and client ID.
function CheckUserCouponUsage($coupon_id, $client_id)
{
    $check = Order::where('coupon_id', $coupon_id)->where('client_id', $client_id)->exists();
    return $check;
}
// Retrieves a coupon by its code and checks if it is associated with a specific branch; returns the coupon object if valid, otherwise returns 0.
function GetCouponId($code, $branch_id)
{
    $coupon = Coupon::where('code', $code)->first();
    if ($coupon) {

        $branch_coupon = BranchCoupon::where('coupon_id', $coupon->id)->where('branch_id', $branch_id)->exists();
    } else {
        return 0;
    }

    if ($coupon && $branch_coupon) {
        return $coupon;
    } else {
        return 0;
    }
}
// Increments coupon usage if under the limit; returns false if limit is reached.
function CountCouponUsage($id)
{
    $coupon = Coupon::find($id);

    if ($coupon) {
        if ($coupon->usage_limit <= $coupon->count_usage) {
            return false;
        }
        $coupon->count_usage = $coupon->count_usage + 1;
        $coupon->save();
    }
    return true;
}
function decreaseCouponUsage($id)
{
    $coupon = Coupon::find($id);

    if ($coupon) {
        if ($coupon->usage_limit <= $coupon->count_usage) {
            return false;
        }
        $coupon->count_usage = $coupon->count_usage - 1;
        $coupon->save();
    }
    return true;
}
function checkCouponApplyDish($coupon_id, $dish_ids, $branch_id)
{
    $query = BranchCoupon::where('coupon_id', $coupon_id)
        ->where('branch_id', $branch_id)
        ->where(function ($q) use ($dish_ids) {
            foreach ($dish_ids as $id) {
                $q->orWhereJsonContains('dish_ids', (string) $id); // cast to string
            }
        });

    return $query->exists();
}
// Retrieves the currently active discount based on the current date.
function CheckDiscountValid()
{
    $discount = Discount::where('start_date', '<=', now())
        ->where('end_date', '>=', now())
        ->first();
    return $discount;
}
// Returns the value of a specific setting column; null if not found.
function getSetting($column)
{
    $setting = Setting::first();

    if ($setting) {
        return $setting->$column;
    }
    return null;
}
// Retrieves a specific setting for a branch; falls back to BranchSetting if not found in Branch.
function getBranchSettings($branchId, $column)
{
    $branch = Branch::find($branchId);
    if ($branch && isset($branch->$column)) {
        return $branch->$column;
    }
    return BranchSetting::where('branch_id', $branchId)->value($column);
}
// Returns a list of enabled payment policies for a branch and order type.
function getBranchPolicyPayment($branchId, $column)
{
    $policies = PaymentPolicies::where('branch_id', $branchId)
        ->where('order_type', $column)
        ->first();
    if (!$policies) {
        return [];
    }

    $enabledPolicies = [];

    foreach ($policies->getAttributes() as $key => $value) {
        if ($value == 1 && !in_array($key, ['id', 'branch_id', 'order_type', 'created_at', 'updated_at'])) {
            $enabledPolicies[] = $key;
        }
    }

    return $enabledPolicies;
}
//
function getCashPaymentPolicy($branchId)
{
    $policies = CashPaymentSetting::where('branch_id', $branchId)
        // ->where('max_cash', $column)
        ->first();
    if (!$policies) {
        return 0;
    }
    return $policies->max_cash;
}
// Calculates the coupon discount amount based on type (fixed or percentage).
function calcCoupon($total_price, $coupon)
{
    if ($coupon->type == 'fixed') {
        return  $coupon->value;
    } else {

        return ($total_price * ($coupon->value / 100));
    }
}
// Applies the coupon to the total price and returns the discounted total.
function applyCoupon($total_price, $coupon)
{
    if ($coupon->type == 'fixed') {
        return $total_price - $coupon->value;
    } else {

        return $total_price - ($total_price * ($coupon->value / 100));
    }
}
// Applies a discount to the total price and returns the discounted total.
function applyDiscount($total_price, $discount)
{
    if ($discount->type == 'fixed') {
        return $total_price - $discount->value;
    } else {
        return $total_price - ($total_price * ($discount->value / 100));
    }
    // return $total_price;
}
// Calculates the discount amount based on type (fixed or percentage).
function calcDiscount($total_price, $discount)
{
    if ($discount->type == 'fixed') {
        return  $discount->value;
    } else {
        return ($total_price * ($discount->value / 100));
    }
}
// Calculates the price after applying or removing tax based on the flag.
function applyTax($total_price, $tax_percentage, $tax_application)
{
    if ($tax_application) {
        return ($total_price / (($tax_percentage / 100) + 1));
    } else {

        return $total_price + ($total_price * ($tax_percentage / 100));
    }
}
// Calculates the tax amount included in the given total amount.
function CalculateTax($tax_percentage, $amount)
{
    $tax = $amount - ($amount / (($tax_percentage / 100) + 1));
    return $tax;
}
// Returns the authenticated user’s type flag or empty string if not logged in.
function CheckUserType()
{
    $User = auth('api')->user();
    if ($User) {
        return $User->flag;
    }
    return '';
}
// Returns the first authenticated guard name from a prioritized list or null if none.
function getAuthenticatedGuard()
{
    $guards = ['employee', 'admin', 'web', 'api', 'client']; // employee first to prioritize

    foreach ($guards as $guard) {
        $user = Auth::guard($guard)->user();
        if ($user) {
            $token = $user->token();

            // Check token scope if using Passport
            if ($token && $token->can('client-access')) {
                return 'api';
            }

            if ($guard === 'employee' && $user instanceof Employee) {
                return $guard;
            }

            if ($guard === 'admin' && $user instanceof \App\Models\User) {
                return $guard;
            }

            if ($guard === 'client' && $user instanceof \App\Models\User) {
                return $guard;
            }

            return $guard; // fallback
        }
    }

    return null;
}
// Checks if a point system exists for the given branch ID.
function isValid($branch_id)
{
    return pointSystem::where('branch_id', $branch_id)->exists();
}
// Checks if the point system is active for the given branch ID.
function isActive($branch_id)
{
    return pointSystem::where('branch_id', $branch_id)->value('active') == 1;
}
function getDeliveryFees($address_id, $branch_id)
{
    $delivery_fees = 0;
    $client_address = ClientAddress::find($address_id);
    if (!$client_address) {
        return 0;
    }
    $area_id = $client_address->area_id;
    $branch_region = BranchRegion::where('region_id', $area_id)->where('branch_id', $branch_id)->first();
    if ($branch_region) {

        $delivery_fees =  $branch_region->delivery_fees;
    }
    return $delivery_fees;
}
// {

//     //get system value redeem
//     $point_redeem = pointSystem::where('branch_id', $branch_id)->value('point_redeem');
//     $limit_redeem = pointSystem::where('branch_id', $branch_id)->value('value_redeem');

//     $user_points = ClientDetail::where('user_id', $client_id)->value('loyalty_points');

//     $points_percent = $user_points * $point_redeem;
//     $redeem_total = 0;
//     // dd($limit_redeem);
//     if ($limit_redeem > $points_percent) {
//         // dd(0);
//         $redeem_total = $total *  $points_percent;
//         $transactions = new pointTransaction();
//         $transactions->customer_id = $client_id;
//         $transactions->order_id = $Order_id;
//         $transactions->type = 'redeem';
//         $transactions->points = $user_points;
//         $transactions->transaction_date = now();
//         $transactions->created_by = $client_id;
//         $transactions->save();

//         //  $point_user = ClientDetail::where('user_id', $client_id)->value('loyalty_points') - $user_points;
//         $client = ClientDetail::where('user_id', $client_id)->first();

//         $client->loyalty_points = 0;
//         $client->save();
//     }

//     return $redeem_total;
// }

// Retrieves the first record from a table where the MD5 hash of 'id' matches the given value.
function get_by_md5_id($id, $table)
{
    return DB::table($table)
        ->where(DB::raw('MD5(id)'), $id)
        ->first();
}
// Retrieves the value of a specific e-invoice setting by key; returns null if not found.
function einvoice_settings($key)
{
    $setting = DB::table('einvoice_settings')->where('key', $key)->value('value');
    return $setting ?? null;
}
// Updates the specified table with the data, where the ID matches
function helper_update_by_id(array $data, $id, $table)
{
    return DB::table($table)->where('id', $id)->update($data);
}
// Finds the nearest active branch to the given latitude and longitude using the Haversine formula.
function getNearestBranch($userLat, $userLon)
{
    $nearestBranch = Branch::select('*')->where('is_active', 1)
        ->selectRaw("(6371 * acos(cos(radians($userLat))
                * cos(radians(latitute))
                * cos(radians(longitute) - radians($userLon))
                + sin(radians($userLat))
                * sin(radians(latitute)))) AS distance")
        ->whereNotNull('latitute')
        ->whereNotNull('longitute')
        ->orderBy('distance', 'asc')
        ->first();
    return $nearestBranch;
}
// Returns the nearest delivery setting within its radius for a given branch and location.
function scopeNearest($IDBranch, $latitude, $longitude)
{
    return DeliverySetting::where('branch_id', $IDBranch)
        ->select('*', DB::raw("
                (6371 * acos(
                    cos(radians($latitude)) *
                    cos(radians(latitude)) *
                    cos(radians(longitude) - radians($longitude)) +
                    sin(radians($latitude)) *
                    sin(radians(latitude))
                )) AS distance
            "))
        ->havingRaw('distance <= radius')
        ->orderBy('distance')->first();
}
// Retrieves the product price based on the pricing method: original, average, or latest purchase price.
function getProductPrice($product_id, $store_id, $unit_id)
{
    $pricing_method = getSetting('pricing_method');
    if ($pricing_method == 'original_price') {
        $price = OpeningBalance::where('product_id', $product_id)->where('unit_id', $unit_id)->where('store_id', $store_id)->first()->price;
    } elseif ($pricing_method == 'avg_price') {
        $price = PurchaseInvoicesDetails::leftJoin('purchase_invoices', 'purchase_invoices.id', '=', 'purchase_invoices_details.purchase_invoices_id')
            ->where('product_id', $product_id)
            ->where('unit_id', $unit_id)
            ->where('store_id', $store_id)
            ->avg('price');
    } else {
        $price = PurchaseInvoicesDetails::leftJoin('purchase_invoices', 'purchase_invoices.id', '=', 'purchase_invoices_details.purchase_invoices_id')
            ->where('product_id', $product_id)
            ->where('unit_id', $unit_id)
            ->where('store_id', $store_id)->orderby('id', 'desc')->first()->price;
    }
    return $price;
}
// Calculates the total paid orders amount for a cashier and employee on a given date and payment method, considering employee shift times and branch.
function CalculateTotalOrders($cashier_machine_id, $employee_id, $date, $payment_method)
{
    $sum_orders = 0;
    $branch_id = 0;
    $cashier_machine_details = CashierMachine::find($cashier_machine_id);
    if ($cashier_machine_details) {
        if ($cashier_machine_details->branches) {
            $branch_id = $cashier_machine_details->branches->id;
        }
    }


    $employee_time =  TimetableService::getTimetableForDate($employee_id, $date);
    if ($branch_id != 0) {
        if ($employee_time['data']['cross_day'] == 0) {
            $orders_totals = Order::where('branch_id', $branch_id)->whereDate('date', $date)->whereTime('created_at', '>=', $employee_time['data']['on_duty_time'])->whereTime('created_at', '<=', $employee_time['data']['off_duty_time'])->get();
        } else {
            $next_day = Carbon::parse($date)->addDays(1);
            $orders_totals_old = Order::where('branch_id', $branch_id)->whereDate('date', $date)->whereTime('created_at', '>=', $employee_time['data']['on_duty_time'])->whereTime('created_at', '>=', $employee_time['data']['off_duty_time'])->get()->toArray();
            $orders_totals_new = Order::where('branch_id', $branch_id)->whereDate('date', $next_day)->whereTime('created_at', '<=', $employee_time['data']['on_duty_time'])->whereTime('created_at', '<=', $employee_time['data']['off_duty_time'])->get()->toArray();
            $orders_totals = array_merge($orders_totals_old, $orders_totals_new);
        }

        if ($orders_totals)
            foreach ($orders_totals as $orders_total) {
                if ($orders_total) {
                    $orders_tot = OrderTransaction::where('order_id', $orders_total['id'])->where('order_type', 'order')->where('payment_status', 'paid')->where('payment_method', $payment_method)->first();
                    if ($orders_tot) {
                        $sum_orders += $orders_tot->paid;
                    }
                }
            }
    }
    return $sum_orders;
}
// Adds a new employee to the BioTime device via API and returns the response or error message.
function addEmployeeToDevice($empCode, $departmentId, $areaIds, $firstName = null, $lastName = null, $hireDate = null, $gender = null, $mobile = null, $email = null)
{
    try {
        $url = 'http://127.0.0.1:8085/personnel/api/employees/';
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0b2tlbl90eXBlIjoiYWNjZXNzIiwiZXhwIjoxNzMxNTgzODk2LCJpYXQiOjE3MzE0OTc0OTYsImp0aSI6Ijk0YzRlYjQzY2I2MzRhMDdhNWIwMzZjOTZmOWM4NDVkIiwidXNlcl9pZCI6MX0.6uMFiXnFHEB_I5baP8qvgWkG_z7BXjPLWaU5QEfuCMg';

        $data = [
            'emp_code' => $empCode,
            'department' => $departmentId,
            'area' => $areaIds,
            'hire_date' => $hireDate ?? now()->toDateString(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => $gender,
            'mobile' => $mobile,
            'email' => $email,
        ];

        $client = new Client();

        $response = $client->post($url, [
            'headers' => [
                'Authorization' => 'JWT ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ]);

        if ($response->getStatusCode() == 201) {
            return json_decode($response->getBody()->getContents(), true);
        } else {
            return 'Failed to add employee. Status code: ' . $response->getStatusCode();
        }
    } catch (\Exception $e) {
        Log::error('Error adding employee to BioTime device: ' . $e->getMessage());
        return 'Error: ' . $e->getMessage();
    }
}
// Calculates the deficit amount based on open, close, and real amounts.
function CalculateDeficitOrder($open_amount = 0, $close_amount = 0, $real_amount = 0)
{
    $remaining_amount = ($close_amount - $open_amount);
    return ($remaining_amount - $real_amount);
}
// Calculates the total approved leave days of a specific type for an employee within the current year.
function CalculateEmployeeLeave($employee_id, $leave_type, $leave_year_count)
{
    $first_day = date('Y-01-01');
    $last_day = date('Y-12-31');
    return $employee_leave_count = LeaveRequest::where('employee_id', $employee_id)->where('leave_type_id', $leave_type)->where('agreement', 2)->whereBetween('date', [$first_day, $last_day])->sum('leave_count');
}
// Checks if there is any open order for the given client ID and returns the first found order.
function CheckExistOrder($client_id)
{
    $Order = Order::where('client_id', $client_id)->where('status', 'open')->fisrt();
    return $Order;
}
// Checks if any payment transaction exists for the specified order ID.
function CheckOrderPaid($order_id)
{
    return  OrderTransaction::where('order_id', $order_id)->exists();
}
// Returns 1 if the order's payment status is 'paid', otherwise returns 0.
function CheckOrderPaidStatus($order_id)
{
    $transaction_status = 0;
    $check_transaction_status = OrderTransaction::where('order_id', $order_id)->first();
    if ($check_transaction_status) {
        $transaction_status = $check_transaction_status->payment_status === 'paid' ? 1 : 0;
    }
    return $transaction_status;
}
// Retrieves all countries with their currency codes and IDs.
function GetCurrencyCodes()
{
    $Currencies = Country::select('currency_code', 'id')->get();
    return $Currencies;
}
// Retrieves all country records from the database.
function GetCountries()
{
    $countries = Country::orderByRaw('
        CASE
            WHEN `order` > 0 THEN 0
            ELSE 1
        END, `order` ASC
    ')->get();
    return $countries;
}

// Retrieves all hotel records from the database.
function GetHotels()
{
    $hotels = Hotel::get();
    return $hotels;
}
// Adds dish-related categories, dishes, addons, and sizes to multiple branches.
function AddBranchesMenu($branch_ids, $dish_id, $menu_integrations = [])
{
    $menu_integration_sizes = [];
    $menu_integration_addons = [];
    $menu_integration_menus = [];
    $menu_integration_ids = [];
    if (count($menu_integrations) > 0) {
        if (count($menu_integrations['sizes']) > 0) {
            $menu_integration_sizes = $menu_integrations['sizes'];
        }
        if (count($menu_integrations['addons']) > 0) {
            $menu_integration_addons = $menu_integrations['addons'];
        }
        if (count($menu_integrations['menus']) > 0) {
            $menu_integration_menus = $menu_integrations['menus'];
        }
        if (count($menu_integrations['menu_ids']) > 0) {
            $menu_integration_ids = $menu_integrations['menu_ids'];
        }
    }
    $add_dish_categories = AddDishCategories($branch_ids, $dish_id);
    $add_dishes = AddDishes($branch_ids, $dish_id, $menu_integration_menus, $menu_integration_ids);
    $add_addon_category = AddAddonCategories($branch_ids, $dish_id);
    $add_addons = AddAddons($branch_ids, $dish_id, $menu_integration_addons, $menu_integration_ids);
    $add_sizes = AddSizes($branch_ids, $dish_id, $menu_integration_sizes, $menu_integration_ids);
}
// Syncs dish categories to given branches, creating or updating branch menu categories accordingly.
function AddDishCategories($branch_ids, $dish_id)
{
    $userId  = authActionSave()['by'];
    $user_type = authActionSave()['type'];

    if ($dish_id != 0) {
        $get_dish = Dish::where('id', $dish_id)->first();
        $get_dish_categories = DishCategory::where('id', $get_dish->category_id)->get();
    } else {
        $get_dish_categories = DishCategory::get();
    }

    if ($get_dish_categories) {
        foreach ($get_dish_categories as $get_dish_category) {
            foreach ($branch_ids as $branch_id) {
                $branch_menu_category = BranchMenuCategory::updateOrCreate(
                    ['dish_category_id' => $get_dish_category->id, 'branch_id' => $branch_id],
                    ['is_active' => $get_dish_category->is_active, 'created_by' => $userId]
                );
            }
        }
    }
}
// Adds or updates dishes for given branches, linking them to branch menu categories and maintaining price and active status.
function AddDishes($branch_ids, $dish_id, $menu_integration_menus, $menu_integration_ids)
{
    $userId  = authActionSave()['by'];
    $user_type = authActionSave()['type'];

    if ($dish_id != 0) {
        $get_dishes = Dish::where('id', $dish_id)->get();
    } else {
        $get_dishes = Dish::get();
    }
    if ($get_dishes) {
        foreach ($get_dishes as $get_dish) {
            foreach ($branch_ids as $branch_id) {
                $get_branch_menu_category = BranchMenuCategory::where('dish_category_id', $get_dish->category_id)->where('branch_id', $branch_id)->first();
                // $branch_menu = BranchMenu::where('dish_id', $get_dish->id)->where('branch_id', $branch_id)->first();
                // if($branch_menu){$branch_menu->is_active = $get_dish->is_active; $branch_menu->save();}
                // $branch_menu_category = BranchMenu::updateOrCreate(
                //     ['dish_id' => $get_dish->id, 'branch_id' => $branch_id],
                //     [
                //         'branch_menu_category_id' => $get_branch_menu_category->id,
                //         'price' => $get_dish->price,
                //         'is_product' => 0,
                //         //'is_product' => $get_dish->price,
                //         'is_active' => $get_dish->is_active,
                //         'created_by' => auth('admin')->id()
                //     ]
                // );

                $existingRecord = BranchMenu::where([
                    'dish_id' => $get_dish->id,
                    'branch_id' => $branch_id
                ])->first();

                if ($existingRecord) {
                    // Update only other fields without changing the price
                    $existingRecord->update([
                        'is_active' => $get_dish->is_active,
                        'modified_by' => $userId,
                    ]);
                    broadcast(new DishStatus($existingRecord, $branch_id, 'update'));
                } else {
                    // Create a new record with the price
                    $menu_ids = null;
                    if (count($menu_integration_ids) > 0) {
                        $menu_ids = $menu_integration_ids;
                    }

                    // Base data for BranchMenu
                    $data = [
                        'dish_id' => $get_dish->id,
                        'branch_id' => $branch_id,
                        'branch_menu_category_id' => $get_branch_menu_category->id,
                        'price' => $get_dish->price,
                        'is_product' => 0,
                        'is_active' => 1,
                        'created_by' => $userId,
                    ];

                    // Add menus integration fields only if not null
                    if (!is_null($menu_ids)) {
                        $data['is_menus_integration'] = 1;
                        $data['menus_integration_ids'] = json_encode($menu_ids, true);
                    }

                    $create = BranchMenu::create($data);

                    if (count($menu_integration_menus) > 0) {
                        foreach ($menu_integration_menus as $menu) {
                            $existingMenuRecord = MenusIntegrationDish::where([
                                'menus_integration_id' => $menu['menus_integration_id'],
                                'branch_menu_id' => $create->id
                            ])->first();

                            if ($existingMenuRecord) {
                                $existingMenuRecord->update([
                                    'price' => $menu['price'],
                                    'is_percentage' => $menu['is_percentage'],
                                    'percentage_amount' => $menu['percentage_amount'],
                                    'is_taxed' => $menu['is_taxed'],
                                    'modified_by_type' => $userId
                                ]);
                            } else {
                                MenusIntegrationDish::create([
                                    'menus_integration_id' => $menu['menus_integration_id'],
                                    'branch_menu_id' => $create->id,
                                    'price' => $menu['price'],
                                    'is_percentage' => $menu['is_percentage'],
                                    'percentage_amount' => $menu['percentage_amount'],
                                    'is_taxed' => $menu['is_taxed'],
                                    'created_by_type' => $userId
                                ]);
                            }
                        }
                    }

                    // event
                    broadcast(new DishChanges($get_dish, $branch_id, 'add'));
                }
            }
        }
    }
}

// Adds addon categories to specified branches if not already linked, activating them by default.
function AddAddonCategories($branch_ids, $dish_id)
{
    $userId  = authActionSave()['by'];
    $user_type = authActionSave()['type'];

    if ($dish_id != 0) {
        $get_dish = Dish::where('id', $dish_id)->with('dishAddonsDetails')->first();
        $addon_categories = $get_dish->dishAddonsDetails->pluck('addon_category_id');
        $get_addon_categories = AddonCategory::whereIn('id', $addon_categories)->get();
    } else {
        $get_addon_categories = AddonCategory::get();
    }

    if ($get_addon_categories) {
        foreach ($get_addon_categories as $get_addon_category) {
            foreach ($branch_ids as $branch_id) {
                // $branch_menu_addon_category = BranchMenuAddonCategory::updateOrCreate(
                //     ['branch_id' => $branch_id, 'addon_category_id' => $get_addon_category->id],
                //     [
                //         'is_active' => 1,
                //         'created_by' => auth('admin')->id()
                //     ]
                // );

                $existingRecord = BranchMenuAddonCategory::where([
                    'branch_id' => $branch_id,
                    'addon_category_id' => $get_addon_category->id
                ])->first();

                if (!$existingRecord) {
                    // Create a new record with the price
                    BranchMenuAddonCategory::create([
                        'branch_id' => $branch_id,
                        'addon_category_id' => $get_addon_category->id,
                        'is_active' => 1,
                        'created_by' => $userId
                    ]);
                }
            }
        }
    }
}

// Syncs dish addons to specified branches by adding new ones and removing outdated entries.
function AddAddons($branch_ids, $dish_id, $menu_integration_addons, $menu_ids)
{
    $userInfo = authActionSave();
    $userId   = $userInfo['by'];
    $userType = $userInfo['type'];

    // Get the relevant addons
    if ($dish_id != 0) {
        $get_dish   = Dish::with('dishAddonsDetails')->find($dish_id);
        $addons_ids = $get_dish?->dishAddonsDetails?->pluck('id') ?? collect();
        $get_addons = DishAddon::whereIn('id', $addons_ids)->get();
    } else {
        $get_addons = DishAddon::all();
    }

    if ($get_addons->isEmpty()) {
        return; // No addons to process
    }

    // Remove old BranchMenuAddons if dish is specified
    if ($dish_id != 0) {
        $addonDataIds = $get_addons->pluck('id')->toArray();

        BranchMenuAddon::where('dish_id', $dish_id)
            ->whereNotIn('dish_addon_id', $addonDataIds)
            ->delete();

        // Limit MenusIntegrationDishAddon deletion to addons linked to this dish
        $branchMenuIds = BranchMenu::where('dish_id', $dish_id)->pluck('id')->toArray();

        MenusIntegrationDishAddon::whereIn('branch_menu_id', $branchMenuIds)
            ->whereNotIn('branch_menu_addon_id', $addonDataIds)
            ->delete();
    }

    // Loop through addons for each branch
    foreach ($get_addons as $get_addon) {
        foreach ($branch_ids as $branch_id) {
            $branch_menu_addon_category = BranchMenuAddonCategory::where([
                'addon_category_id' => $get_addon->addon_category_id,
                'branch_id'         => $branch_id
            ])->first();

            if (!$branch_menu_addon_category) {
                continue; // Skip if category not found
            }

            $existingRecord = BranchMenuAddon::where([
                'dish_id'       => $get_addon->dish_id,
                'branch_id'     => $branch_id,
                'dish_addon_id' => $get_addon->id
            ])->first();

            // Create or update branch addon
            if ($existingRecord) {
                $existingRecord->update([
                    'price'       => $get_addon->price,
                    'is_active'   => 1,
                    'modified_by' => $userId
                ]);
                $create = $existingRecord;
            } else {
                $create = BranchMenuAddon::create([
                    'dish_id'                      => $get_addon->dish_id,
                    'branch_id'                    => $branch_id,
                    'dish_addon_id'                => $get_addon->id,
                    'branch_menu_addon_category_id' => $branch_menu_addon_category->id,
                    'price'                        => $get_addon->price,
                    'is_active'                    => 1,
                    'created_by'                   => $userId,
                ]);
            }

            // Only handle menus integration if menu_ids exist
            if (!empty($menu_ids) && !empty($menu_integration_addons)) {
                $branch_menu = BranchMenu::where([
                    'dish_id'   => $get_addon->dish_id,
                    'branch_id' => $branch_id
                ])->first();

                if ($branch_menu) {
                    foreach ($menu_integration_addons as $addon) {
                        if ($get_addon->id == $addon['dish_addon_id']) {
                            $existingMenuRecord = MenusIntegrationDishAddon::where([
                                'menus_integration_id'  => $addon['menus_integration_id'],
                                'branch_menu_id'        => $branch_menu->id,
                                'branch_menu_addon_id'  => $create->id,
                            ])->first();

                            $menuData = [
                                'price'            => $addon['price'],
                                'modified_by_type' => $userId,
                            ];

                            if ($existingMenuRecord) {
                                $existingMenuRecord->update($menuData);
                            } else {
                                MenusIntegrationDishAddon::create(array_merge($menuData, [
                                    'menus_integration_id'  => $addon['menus_integration_id'],
                                    'branch_menu_id'        => $branch_menu->id,
                                    'branch_menu_addon_id'  => $create->id,
                                    'dish_addon_id'         => $get_addon->id,
                                    'created_by_type'       => $userId,
                                ]));
                            }
                        }
                    }
                }
            }
        }
    }
}

// Syncs dish sizes to specified branches by adding new sizes and removing outdated ones.
// function AddSizes($branch_ids, $dish_id, $menu_integration_sizes, $menu_ids)
// {
//     $userInfo = authActionSave();
//     $userId   = $userInfo['by'];
//     $userType = $userInfo['type'];

//     if ($dish_id != 0) {
//         $get_sizes = DishSize::where('dish_id', $dish_id)->get();
//     } else {
//         $get_sizes = DishSize::get();
//     }

//     if ($get_sizes->isEmpty()) {
//         return; // No addons to process
//     }

//     if ($dish_id != 0) {
//         $sizeDataIds = array_map(function ($sizeData) {
//             return $sizeData['id'] ?? null;
//         }, array_filter($get_sizes->toArray(), function ($value) {
//             return isset($value['id']) && $value['id'] !== null;
//         }));

//         BranchMenuSize::whereNotIn('dish_size_id', $sizeDataIds)
//             ->where('dish_id', $dish_id)
//             ->delete();
//     }

//     foreach ($get_sizes as $get_size) {
//         //$menu = BranchMenu::where('dish_id', $get_size->dish_id)->first();
//         foreach ($branch_ids as $branch_id) {
//             // $branch_menu_category = BranchMenuSize::updateOrCreate(
//             //     ['dish_id' => $get_size->dish_id, 'branch_id' => $branch_id, 'dish_size_id' => $get_size->id],
//             //     [
//             //         'price' => $get_size->price,
//             //         'is_active' => 1,
//             //         'created_by' => auth('admin')->id()
//             //     ]
//             // );

//             $existingRecord = BranchMenuSize::where([
//                 'dish_id' => $get_size->dish_id,
//                 'branch_id' => $branch_id,
//                 'dish_size_id' => $get_size->id,
//             ])->first();

//             if (!$existingRecord) {
//                 // Create a new record with the price
//                 $create = BranchMenuSize::create([
//                     'dish_id' => $get_size->dish_id,
//                     'branch_id' => $branch_id,
//                     'dish_size_id' => $get_size->id,
//                     'price' => $get_size->price,
//                     'is_active' => 1,
//                     'created_by' => $userId,
//                 ]);


//                 $branch_menu = BranchMenu::where(['dish_id' => $get_size->dish_id,'branch_id' => $branch_id])->first();
//                 if (count($menu_ids) > 0) {
//                     if(count($menu_integration_sizes) > 0){
//                         foreach($menu_integration_sizes as $size){
//                             if($get_size->id == $size['dish_size_id']){
//                                 $existingMenuRecord = MenusIntegrationDishSize::where([
//                                     'menus_integration_id' => $size['menus_integration_id'],
//                                     'branch_menu_id' => $branch_menu->id,
//                                     'branch_menu_size_id' => $create->id
//                                 ])->first();

//                                 if ($existingMenuRecord) {
//                                     $existingMenuRecord->update([
//                                         'price' => $size['price'],
//                                         'modified_by_type' => $userId
//                                     ]);
//                                 } else {
//                                     $existingMenuRecord = MenusIntegrationDishSize::create([
//                                         'menus_integration_id' => $size['menus_integration_id'],
//                                         'branch_menu_id' => $branch_menu->id,
//                                         'branch_menu_size_id' => $create->id,
//                                         'price' => $size['price'],
//                                         'created_by_type' => $userId
//                                     ]);
//                                 }
//                             }
//                         }
//                     }
//                 }
//             }
//         }
//     }

// }

function AddSizes($branch_ids, $dish_id, $menu_integration_sizes, $menu_ids)
{
    $userInfo = authActionSave();
    $userId   = $userInfo['by'];
    $userType = $userInfo['type'];

    // Get the relevant sizes
    if ($dish_id != 0) {
        $get_sizes = DishSize::where('dish_id', $dish_id)->get();
    } else {
        $get_sizes = DishSize::all();
    }

    if ($get_sizes->isEmpty()) {
        return; // No sizes to process
    }

    // Remove old BranchMenuSizes if dish is specified
    if ($dish_id != 0) {
        $sizeDataIds = $get_sizes->pluck('id')->toArray();

        BranchMenuSize::where('dish_id', $dish_id)
            ->whereNotIn('dish_size_id', $sizeDataIds)
            ->delete();

        // Restrict MenusIntegrationDishSize deletion to this dish’s branch menus
        $branchMenuIds = BranchMenu::where('dish_id', $dish_id)->pluck('id')->toArray();

        MenusIntegrationDishSize::whereIn('branch_menu_id', $branchMenuIds)
            ->whereNotIn('branch_menu_size_id', $sizeDataIds)
            ->delete();
    }

    // Loop through each size for each branch
    foreach ($get_sizes as $get_size) {
        foreach ($branch_ids as $branch_id) {

            // Check if size already exists in branch
            $existingRecord = BranchMenuSize::where([
                'dish_id'       => $get_size->dish_id,
                'branch_id'     => $branch_id,
                'dish_size_id'  => $get_size->id
            ])->first();

            // Create or update branch size
            if ($existingRecord) {
                $existingRecord->update([
                    'price'       => $get_size->price,
                    'is_active'   => 1,
                    'modified_by' => $userId
                ]);
                $create = $existingRecord;
            } else {
                $create = BranchMenuSize::create([
                    'dish_id'       => $get_size->dish_id,
                    'branch_id'     => $branch_id,
                    'dish_size_id'  => $get_size->id,
                    'price'         => $get_size->price,
                    'is_active'     => 1,
                    'created_by'    => $userId,
                ]);
            }

            // Handle menus integration if menu_ids exist
            if (!empty($menu_ids) && !empty($menu_integration_sizes)) {
                $branch_menu = BranchMenu::where([
                    'dish_id'   => $get_size->dish_id,
                    'branch_id' => $branch_id
                ])->first();

                if ($branch_menu) {
                    foreach ($menu_integration_sizes as $size) {
                        if ($get_size->id == $size['dish_size_id']) {
                            $existingMenuRecord = MenusIntegrationDishSize::where([
                                'menus_integration_id' => $size['menus_integration_id'],
                                'branch_menu_id'       => $branch_menu->id,
                                'branch_menu_size_id'  => $create->id,
                            ])->first();

                            $menuData = [
                                'price'            => $size['price'],
                                'modified_by_type' => $userId,
                            ];

                            if ($existingMenuRecord) {
                                $existingMenuRecord->update($menuData);
                            } else {
                                MenusIntegrationDishSize::create(array_merge($menuData, [
                                    'menus_integration_id' => $size['menus_integration_id'],
                                    'branch_menu_id'       => $branch_menu->id,
                                    'branch_menu_size_id'  => $create->id,
                                    'dish_size_id'         => $get_size->id,
                                    'created_by_type'      => $userId,
                                ]));
                            }
                        }
                    }
                }
            }
        }
    }
}

// Soft marks and then deletes all menu-related records (dishes, sizes, addons) for a given dish ID.
function DeleteMenu($dish_id)
{
    $update_dish = BranchMenu::where('dish_id', $dish_id)->update(['deleted_by' => auth('admin')->id()]);
    $update_size = BranchMenuSize::where('dish_id', $dish_id)->update(['deleted_by' => auth('admin')->id()]);
    $update_addon = BranchMenuAddon::where('dish_id', $dish_id)->update(['deleted_by' => auth('admin')->id()]);
    $dish = BranchMenu::where('dish_id', $dish_id)->get();
    foreach ($dish as $d) {
        $branch_id = $d->branch_id;
        broadcast(new DishStatus($d, $branch_id, 'delete'));
    }
    $delete_dish = BranchMenu::where('dish_id', $dish_id)->delete();
    $delete_size = BranchMenuSize::where('dish_id', $dish_id)->delete();
    $delete_addon = BranchMenuAddon::where('dish_id', $dish_id)->delete();
}
// Soft marks and then deletes all menu-related records (dishes, sizes, addons) for a given branch ID.
function DeleteBranchMenu($branch_id)
{
    $update_dish = BranchMenu::where('branch_id', $branch_id)->update(['deleted_by' => auth('admin')->id()]);
    $update_size = BranchMenuSize::where('branch_id', $branch_id)->update(['deleted_by' => auth('admin')->id()]);
    $update_addon = BranchMenuAddon::where('branch_id', $branch_id)->update(['deleted_by' => auth('admin')->id()]);
    $delete_dish = BranchMenu::where('branch_id', $branch_id)->delete();
    $delete_size = BranchMenuSize::where('branch_id', $branch_id)->delete();
    $delete_addon = BranchMenuAddon::where('branch_id', $branch_id)->delete();
}
// Casts dish boolean fields to proper boolean types for consistent data handling.
function transformDishFields($dish)
{
    $dish->is_active = (bool)$dish->is_active;
    $dish->has_sizes = (bool)$dish->has_sizes;
    $dish->has_addon = (bool)$dish->has_addon;
    return $dish;
}
// Retrieves the ID of the default branch, hiding specific attributes; returns null if none found.
function getDefaultBranch()
{
    $defaultBranch = Branch::where('is_default', 1)->first();
    if ($defaultBranch) {
        $defaultBranch->makeHidden(['name_site', 'address_site']);
        return $defaultBranch->id;
    }
    return null;
}
// Retrieves the top most-ordered active dishes for a given branch, including sizes and price, limited by the specified number.
function getMostDishesOrdered($IDBranch, $limit = 5)
{
    return Dish::with('dishSizes')
        ->join('branch_menus', function ($join) {
            $join->on('dishes.id', '=', 'branch_menus.dish_id')
                ->whereNull('branch_menus.deleted_at');
        })
        ->join('branches', function ($join) {
            $join->on('branches.id', '=', 'branch_menus.branch_id')
                ->whereNull('branches.deleted_at');
        })
        ->join('order_details', function ($join) {
            $join->on('order_details.dish_id', '=', 'dishes.id')
                ->whereNull('order_details.deleted_at');
        })
        ->leftJoin('countries', 'countries.id', '=', 'branches.country_id')
        ->where('branch_menus.branch_id', $IDBranch)
        ->where('branches.is_active', 1)
        ->where('branch_menus.is_active', 1)
        ->where('dishes.is_active', 1)
        ->whereNull('dishes.deleted_at') // Also check for soft-deleted dishes
        ->whereExists(function ($query) {
            $query->select('id')
                ->from('branch_menu_categories')
                ->whereColumn('branch_menu_categories.id', 'branch_menus.branch_menu_category_id')
                ->whereColumn('branch_menu_categories.branch_id', 'branch_menus.branch_id')
                ->where('branch_menu_categories.is_active', 1)
                ->whereNull('branch_menu_categories.deleted_at'); // Handle soft delete
        })
        ->groupBy(
            'dishes.id',
            'dishes.name_ar',
            'dishes.name_en',
            'dishes.image',
            'dishes.price',
            'dishes.has_sizes',
            'dishes.created_at',
            'branch_menus.branch_id',
            'branch_menus.price'
        )
        ->select('dishes.*', 'branch_menus.price')
        ->selectRaw('SUM(order_details.quantity) as total_quantity')
        ->orderByDesc('total_quantity')
        ->orderBy('dishes.created_at', 'desc')
        ->limit($limit)
        ->get();
}
// Checks if a specific dish ID is among the top 5 most-ordered active dishes for a given branch.
function checkDishExistMostOrderd($IDBranch, $id)
{
    $Dishes = BranchMenu::select('dishes.id')
        ->leftJoin('dishes', 'dishes.id', '=', 'branch_menus.dish_id')
        ->leftJoin('branches', 'branches.id', '=', 'branch_menus.branch_id')
        ->join('order_details', 'order_details.dish_id', '=', 'dishes.id')
        ->leftJoin('countries', 'countries.id', '=', 'branches.country_id') // Include countries for consistency
        ->where('branch_menus.branch_id', $IDBranch)
        ->where('branches.is_active', 1) // Match branch active condition
        ->where('branch_menus.is_active', 1) // Match branch menu active condition
        ->where('dishes.is_active', 1) // Match dish active condition
        ->whereExists(function ($query) {
            $query->select('id')
                ->from('branch_menu_categories')
                ->whereColumn('branch_menu_categories.id', 'branch_menus.branch_menu_category_id')
                ->whereColumn('branch_menu_categories.branch_id', 'branch_menus.branch_id')
                ->where('branch_menu_categories.is_active', 1); // Match category active condition
        })

        ->groupBy('dishes.id', 'dishes.name_ar', 'countries.currency_symbol') // Ensure matching grouping
        ->selectRaw('SUM(order_details.quantity) as total_quantity') // Include total quantity
        ->orderByDesc('total_quantity')
        ->orderBy('dishes.created_at', 'desc') // Ensure matching sorting
        ->limit(5)
        ->pluck('id') // Fetch the IDs of the top dishes
        ->toArray();

    if (in_array($id, $Dishes)) {
        return true;
    }
    return false;
}
// Checks if any order detail has used the specified offer ID.
function checkOfferUsed($id)
{
    return OrderDetail::where('offer_id', $id)->exists();
}
// Retrieves the names of recipes for a given dish, optionally filtered by size.
function getDishRecipeNames($dishId, $sizeId = null)
{
    $query = DishDetail::where('dish_id', $dishId);

    if (!is_null($sizeId)) {
        $query->where('dish_size_id', $sizeId);
    } else {
        $query->whereNull('dish_size_id');
    }

    // Use the relationship to fetch recipe names
    return $query->with('recipe')->get()->pluck('recipe.name')->toArray();
}
// Returns the weekday name in English or Arabic based on the day index and language code.
function weekDay($day, $lang)
{
    switch ($day) {
        case 0:
            if ($lang == "en") {
                return "Sunday";
            } else {
                return "الاحد";
            }
            // break;

        case 1:
            if ($lang == "en") {
                return "Monday";
            } else {
                return "الاثنين";
            }
            // break;

        case 2:
            if ($lang == "en") {
                return "Tuesday";
            } else {
                return "الثلاثاء";
            }
            // break;

        case 3:
            if ($lang == "en") {
                return "Wednesday";
            } else {
                return "الاربعاء";
            }
            // break;

        case 4:
            if ($lang == "en") {
                return "Thursday";
            } else {
                return "الخميس";
            }
            // break;

        case 5:
            if ($lang == "en") {
                return "Thursday";
            } else {
                return "الخميس";
            }
            // break;

        default:
            if ($lang == "en") {
                return "Saturday";
            } else {
                return "السبت";
            }
            // break;
    }
}
// Formats a given time to 12-hour format and translates AM/PM to Arabic if the app locale is Arabic; returns "closed" if no time provided.
function formatTimeToArabic($time)
{
    if (!$time) {
        return __('header.closed');
    }

    $lang = app()->getLocale();
    $formattedTime = Carbon::parse($time)->format('h:i A');

    if ($lang === 'ar') {
        $formattedTime = str_replace(['AM', 'PM'], ['صباحاً', 'مساءً'], $formattedTime);
    }

    return $formattedTime;
}
// Retrieves active, non-deleted addresses of the authenticated client, including a count of their in-progress or pending orders; returns null if none found.
function getUserAddress()
{
    $user = Auth::guard('client')->user()->id;
    $addresses = ClientAddress::where('user_id', $user)
        ->where('is_active', 1) // Only active addresses
        ->whereNull('deleted_at') // Exclude deleted addresses
        ->withCount([
            'orders as has_inprogress_or_pending_orders' => function ($query) {
                // Count orders with in-progress or pending statuses
                $query->whereIn('status', ['inprogress', 'pending']);
            }
        ])
        ->get();

    // Return the addresses or null if none found
    return $addresses->isNotEmpty() ? $addresses : null;
}
// Returns the minimum number of addons required for a given dish and addon category; returns 0 if none set.
function minAddons($dish_id, $dish_category_id)
{
    $min_addons = 0;
    $check_max = DishAddon::where('addon_category_id', $dish_category_id)->where('dish_id', $dish_id)->first();
    if ($check_max) {
        $min_addons = $check_max->min_addons;
    }
    return $min_addons;
}
// Returns the maximum number of addons allowed for a given dish and addon category; returns 0 if none set.
function maxAddons($dish_id, $dish_category_id)
{
    $max_addons = 0;
    $check_max = DishAddon::where('addon_category_id', $dish_category_id)->where('dish_id', $dish_id)->first();
    if ($check_max) {
        $max_addons = $check_max->max_addons;
    }
    return $max_addons;
}
// Checks if a user's location is within a specified radius (in km) from a branch using the Haversine formula.
function isInRadius($userLat, $userLon, $branchLat, $branchLon, $radius)
{
    // Radius of Earth in kilometers (use 3958.8 for miles)
    $earthRadius = 6371;

    // Convert degrees to radians
    $userLat = deg2rad($userLat);
    $userLon = deg2rad($userLon);
    $branchLat = deg2rad($branchLat);
    $branchLon = deg2rad($branchLon);

    // Difference in coordinates
    $latDiff = $branchLat - $userLat;
    $lonDiff = $branchLon - $userLon;

    // Haversine formula
    $a = sin($latDiff / 2) * sin($latDiff / 2) +
        cos($userLat) * cos($branchLat) *
        sin($lonDiff / 2) * sin($lonDiff / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    // Calculate distance
    $distance = $earthRadius * $c;

    // Check if the distance is within the radius
    return $distance <= $radius;
}
// Retrieves the employee ID linked to the currently authenticated admin user, or returns null if not found.
function getEmployeeID()
{
    $user = auth('admin')->user();
    if ($user) {
        return Employee::where('user_id', $user->id)->value('id');
    }
    return null;
}
// Returns the branch ID managed by the currently authenticated admin user, or null if none found.
function getBranchManagerID()
{
    $user = auth('admin')->user();
    if ($user) {
        $employee_id = Employee::where('user_id', $user->id)->value('id');
        return Branch::where('employee_id', $employee_id)->value('id');
    }
    return null;
}
// Recursively serializes a document structure into a consistent uppercase-key string representation,
// handling scalar values, arrays, and UUIDs specially for deterministic logging or hashing.
function serializeDocument($documentStructure)
{
    Log::debug('Starting serialization', ['documentStructure' => $documentStructure]);

    if (is_scalar($documentStructure) || is_null($documentStructure)) {
        // Prevent wrapping UUIDs in extra quotes
        if (preg_match('/^[a-f0-9]{64}$/i', $documentStructure)) {
            Log::debug('UUID detected, keeping as is', ['uuid' => $documentStructure]);
            return $documentStructure;  // Do not add quotes for hash-based UUIDs
        }

        $serializedValue = '"' . trim((string) $documentStructure) . '"';
        Log::debug('Scalar/Null value serialized', ['value' => $serializedValue]);
        return $serializedValue;
    }

    $serializedString = '';

    // Ensure sorting consistency
    ksort($documentStructure, SORT_STRING);
    Log::debug('Sorted document keys', ['sortedKeys' => array_keys($documentStructure)]);

    foreach ($documentStructure as $key => $value) {
        $key = strtoupper($key);
        Log::debug("Processing key: $key", ['value' => $value]);

        if (!is_array($value) || isAssocArray($value)) {
            $serializedString .= $key . serializeDocument($value);
            Log::debug("Serialized non-array key: $key", ['serializedString' => $serializedString]);
        } else {
            foreach ($value as $arrayElement) {
                $serializedString .= $key . serializeDocument($arrayElement);
                Log::debug("Serialized array key: $key", ['arrayElement' => $arrayElement]);
            }
        }
    }

    Log::debug('Final serialized string', ['serializedString' => $serializedString]);

    return $serializedString;
}
// Determines if an array is associative (has non-sequential keys) and logs the check result.
function isAssocArray($array)
{
    $isAssoc = array_keys($array) !== range(0, count($array) - 1);
    Log::debug('Checking if array is associative', ['array' => $array, 'isAssoc' => $isAssoc]);
    return $isAssoc;
}
// Formats a 32-character hash string into a standard UUID v4 format with hyphens.
function formatToUUIDv4($hash)
{
    return substr($hash, 0, 8) . '-' .
        substr($hash, 8, 4) . '-' .
        substr($hash, 12, 4) . '-' .
        substr($hash, 16, 4) . '-' .
        substr($hash, 20, 12);
}
// Generates a UUID for a receipt by serializing its data and formatting the result as a UUID v4.
function generateReceiptUUID($receiptData)
{
    $normalizedText = serializeDocument($receiptData);
    //$hashBytes = hash('sha256', $normalizedText);
    $hashBytes = formatToUUIDv4($normalizedText);
    $hexUuid = bin2hex($hashBytes);

    return formatToUUIDv4($hexUuid);
}
// Get all employees with the flag 'officer'
function employees()
{
    return   Employee::where('flag', 'officer')->get();
}
// Get all employees with the flag 'Head chef'
function chefs()
{
    return   Employee::where('flag', 'Head chef')->get();
}
// Get all shift records
function shifts()
{
    return   Shift::get();
}
// Get all employees with the flag 'driver'
function drivers()
{
    return   Employee::where('flag', 'driver')->get();
}
// Get all active branches (not soft deleted)
function branches()
{
    return   Branch::where('is_active', 1)->whereNull('deleted_at')->get();
}
// Get all active cashier machines (not soft deleted)
function getPOS()
{
    return   CashierMachine::whereNull('deleted_at')->get();
}
// Get all vehicle settings
function vehicles()
{
    return   VehicleSetting::all();
}
// Get all cuisine types
function cuisines()
{
    return   Cuisine::all();
}
// Get all unread admin notifications for a given user ID
function getNotifications($id)
{
    return   Notification::where('status', 0)->where('user_id', $id)->where('type', 'admin')->get();
}
// Get optimized delivery route and Google Maps link using Google Directions API
// based on branch location and multiple delivery points
function getSmartDeliveryRoute($branchLat, $branchLng, $deliveryPoints)
{
    $apiKey = env('GOOGLE_API_KEY');
    if (!$apiKey) {
        Log::error("Google API Key is missing.");
        return ['error' => 'Google API key is not configured.'];
    }

    $baseUrl = "https://maps.googleapis.com/maps/api/directions/json";

    // Validate branch coordinates
    if (!is_numeric($branchLat) || !is_numeric($branchLng)) {
        Log::error("Invalid branch coordinates: Lat: $branchLat, Lng: $branchLng");
        return ['error' => 'Invalid branch coordinates.'];
    }

    Log::info("Branch Location: Lat: $branchLat, Lng: $branchLng");

    // Validate delivery points
    if (empty($deliveryPoints)) {
        Log::warning("No delivery points provided.");
        return ['error' => 'No delivery points provided.'];
    }

    foreach ($deliveryPoints as $index => $point) {
        if (!isset($point['lat'], $point['lng']) || !is_numeric($point['lat']) || !is_numeric($point['lng'])) {
            Log::error("Invalid delivery point at index $index: ", $point);
            return ['error' => "Invalid coordinates in delivery point at index $index."];
        }
    }

    Log::info("Received Delivery Points: ", $deliveryPoints);

    // Prepare waypoints
    $waypoints = implode('|', array_map(function ($point) {
        return "{$point['lat']},{$point['lng']}";
    }, $deliveryPoints));

    Log::info("Formatted Waypoints for API: $waypoints");

    // Make API request
    $response = Http::timeout(10)->get($baseUrl, [
        'origin' => "$branchLat,$branchLng",
        'destination' => "$branchLat,$branchLng", // Round trip
        'waypoints' => "optimize:true|$waypoints",
        'key' => $apiKey,
    ]);

    $data = $response->json();

    Log::info("Google API Response: ", $data);

    if (!$response->successful() || !isset($data['status']) || $data['status'] !== 'OK') {
        $errorMessage = $data['error_message'] ?? $data['status'] ?? 'Unknown error';
        Log::error("Google API Error: $errorMessage");
        return ['error' => "Failed to fetch optimized route: $errorMessage"];
    }

    if (!isset($data['routes'][0]['waypoint_order'])) {
        Log::error("Invalid Google API response: Missing waypoint_order.");
        return ['error' => 'Invalid route data returned by Google API.'];
    }

    // Extract optimized order
    $optimizedOrder = $data['routes'][0]['waypoint_order'];
    Log::info("Optimized Order from Google: ", $optimizedOrder);

    // Reorder delivery points
    $optimizedRoute = array_map(function ($index) use ($deliveryPoints) {
        return $deliveryPoints[$index];
    }, $optimizedOrder);

    // Extract leg distances (in meters)
    $legDistances = [];
    if (isset($data['routes'][0]['legs'])) {
        foreach ($data['routes'][0]['legs'] as $leg) {
            $legDistances[] = $leg['distance']['value'] ?? null; // Distance in meters
        }
    }

    Log::info("Leg Distances (meters): ", $legDistances);

    // Generate Google Maps link
    $googleMapLink = "https://www.google.com/maps/dir/$branchLat,$branchLng/" . implode('/', array_map(function ($point) {
        return "{$point['lat']},{$point['lng']}";
    }, $optimizedRoute)) . "/$branchLat,$branchLng";

    Log::info("Generated Google Maps Link: $googleMapLink");

    return [
        'optimized_route' => $optimizedRoute,
        'leg_distances' => $legDistances,
        'google_map_link' => $googleMapLink,
    ];
}
// Calculate an optimized delivery route by finding the nearest delivery point iteratively,
// starting from the branch location, without using external APIs
function getDeliveryRoute($branchLat, $branchLng, $deliveryPoints)
{
    if (empty($deliveryPoints)) {
        return ['error' => 'No delivery points provided'];
    }

    Log::info("Branch Location: Lat: $branchLat, Lng: $branchLng");
    Log::info("Received Delivery Points: ", $deliveryPoints);

    // Start from the branch location
    $currentLocation = ['lat' => $branchLat, 'lng' => $branchLng];

    // List of unvisited delivery points
    $unvisited = $deliveryPoints;

    // Optimized route storage
    $optimizedRoute = [];

    while (!empty($unvisited)) {
        // Find the nearest delivery point
        $nearestIndex = null;
        $nearestDistance = PHP_FLOAT_MAX;

        foreach ($unvisited as $index => $point) {
            $distance = calculateDistance($currentLocation['lat'], $currentLocation['lng'], $point['lat'], $point['lng']);

            if ($distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearestIndex = $index;
            }
        }

        // Move to the nearest point
        if ($nearestIndex !== null) {
            $optimizedRoute[] = $unvisited[$nearestIndex]; // Add to optimized route
            $currentLocation = $unvisited[$nearestIndex]; // Update current location
            unset($unvisited[$nearestIndex]); // Remove visited point
        }
    }

    Log::info("Final Optimized Route: ", $optimizedRoute);

    return [
        'optimized_route' => array_values($optimizedRoute), // Reset array keys
        'message' => 'Optimized route calculated without Google Maps API'
    ];
}
//Calculates the distance between two latitude-longitude points using Haversine formula
function calculateDistance($lat1, $lng1, $lat2, $lng2)
{
    $earthRadius = 6371; // Earth radius in km

    $latDelta = deg2rad($lat2 - $lat1);
    $lngDelta = deg2rad($lng2 - $lng1);

    $a = sin($latDelta / 2) * sin($latDelta / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($lngDelta / 2) * sin($lngDelta / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c; // Distance in km
}
// Filter invoices based on min/max count and amount constraints, prioritizing electronic invoices first
function getFilteredInvoices($invoices, $settings)
{
    // Extract settings
    $minInvoices = $settings['min_no_of_invoices'];
    $maxInvoices = $settings['max_no_of_invoices'];
    $minAmount = $settings['min_amount_of_money'];
    $maxAmount = $settings['max_amount_of_money'];

    // Separate electronic and non-electronic invoices
    $electronicInvoices = array_filter($invoices, function ($invoice) {
        return $invoice['is_electronic'] ?? false;
    });

    $otherInvoices = array_filter($invoices, function ($invoice) {
        return !($invoice['is_electronic'] ?? false);
    });

    // Sort electronic invoices by amount (ascending) to prioritize smaller amounts first
    usort($electronicInvoices, function ($a, $b) {
        return $a['amount'] <=> $b['amount'];
    });

    // Sort other invoices by amount (ascending)
    usort($otherInvoices, function ($a, $b) {
        return $a['amount'] <=> $b['amount'];
    });

    $filteredInvoices = [];
    $totalAmount = 0;

    // First try to add electronic invoices
    foreach ($electronicInvoices as $invoice) {
        if (count($filteredInvoices) < $maxInvoices && ($totalAmount + $invoice['amount']) <= $maxAmount) {
            $filteredInvoices[] = $invoice;
            $totalAmount += $invoice['amount'];
        }
    }

    // If we still need more invoices, add from others
    if (count($filteredInvoices) < $maxInvoices) {
        foreach ($otherInvoices as $invoice) {
            if (count($filteredInvoices) < $maxInvoices && ($totalAmount + $invoice['amount']) <= $maxAmount) {
                $filteredInvoices[] = $invoice;
                $totalAmount += $invoice['amount'];
            }
        }
    }

    // Check if the selection meets the minimum conditions
    if (count($filteredInvoices) >= $minInvoices && $totalAmount >= $minAmount) {
        return array_column($filteredInvoices, 'inv_number');
    }

    return []; // No valid selection found


    //example for the getFilteredInvoices
    // $invoices = [
    //     ['inv_number' => 'invoice_1', 'amount' => 200],
    //     ['inv_number' => 'invoice_2', 'amount' => 400],
    //     ['inv_number' => 'invoice_3', 'amount' => 700],
    //     ['inv_number' => 'invoice_4', 'amount' => 500],
    // ];

    // $settings = [
    //     'min_no_of_invoices' => 2,
    //     'max_no_of_invoices' => 10,
    //     'min_amount_of_money' => 100,
    //     'max_amount_of_money' => 1000,
    // ];

    // $result = getFilteredInvoices($invoices, $settings);
    // print_r($result);

}
// Retrieve and format the list of dishes with details and addons from a previous order by its ID
function previousOrderItems($order_id)
{
    $order = Order::find($order_id);
    $dishes = [];
    if ($order && $order->orderDetails) {
        foreach ($order->orderDetails as $order_details) {

            $order_addon = $order_details->dishAddons;
            if (count($order_details->dishAddons) > 0) {
                $addons = $order_details->dishAddons->pluck('dish_addon_id');
                $addon_categories = ['id' => 1, 'addon' => $addons];
            } else {
                $addon_categories = [];
            }

            $dishes[] = [
                'dish_id' => $order_details->dish_id,
                'quantity' => $order_details->quantity,
                'note' => $order_details->note,
                'sizeId' => $order_details->dish_size_id,
                'addon_categories' => $addon_categories
            ];
        }
    }

    return $dishes;
}
//Processes and updates order items, calculates prices including tax, fees, and coupons,
//updates order totals, handles addons, and creates a corresponding order transaction record.
function openOrderItems($order_id, $type, $coupon, $payment_method, $DataOrderDetails = array())
{
    //settings
    $order = Order::find($order_id);
    $discount = null;
    $total_price_befor_tax = 0;
    $total_addon_price_befor_tax = 0;
    $done = false;
    $tax_application = getBranchSettings($order->branch_id, 'tax_application');
    $tax_percentage = getBranchSettings($order->branch_id, 'tax_percentage');
    $coupon_application = getBranchSettings($order->branch_id, 'coupon_application');
    if ($order->type === 'takeaway') {
        $service_fees = 0;
        $delivery_fees = 0;
        $status = 'pending';
    } elseif ($order->type === 'Delivery') {
        $service_fees = 0;
        $delivery_fees = getBranchSettings($order->branch_id, 'delivery_fees');
        $status = 'pending';
    } elseif ($order->type === 'dine-in') {
        $service_fees = getBranchSettings($order->branch_id, 'service_fees');
        $delivery_fees = 0;
        $status = $order->status === 'pending' ? 'pending' : $order->status;
    }

    $orderDishId = $order->orderDetails->pluck('dish_id');

    if (sizeof($DataOrderDetails)) {
        $total_price_after_tax = 0;
        $total_price_before_tax = 0;
        $tax_value_total = 0;
        $branch_dish_size = 0;

        //check if dish delete
        $dishIds = array_map(function ($item) {
            return $item['dish_id'];
        }, $DataOrderDetails);
        $check_dishes = OrderDetail::whereNotIn('dish_id', $dishIds)->where('order_id', $order->id)->where('status', 'pending')->get();
        if ($check_dishes) {
            foreach ($check_dishes as $check_dish) {
                $check_dish->update(['deleted_by' => $order->created_by]);
                $check_dish->delete();
                if ($check_dish->dishAddons) {
                    foreach ($check_dish->dishAddons as $dishAddons) {
                        $dishAddons->where('status', 'pending')->update(['deleted_by' => $order->created_by]);
                        $dishAddons->delete();
                    }
                }
            }
        }

        foreach ($DataOrderDetails as $DataOrderDetail) {

            if ($type == "web") {
                $Branch_Dish = BranchMenu::where('dish_id', $DataOrderDetail['dish_id'])->where('branch_id', $order->branch_id)->first();
            } else {
                $Branch_Dish = BranchMenu::where('id', $DataOrderDetail['dish_id'])->where('branch_id', $order->branch_id)->first();
            }

            if ($Branch_Dish) {
                $has_size = $Branch_Dish->dish->has_sizes;
                if ($has_size && $DataOrderDetail['sizeId']) {
                    $size_id = $DataOrderDetail['sizeId'];
                    $branch_dish_size = BranchMenuSize::find($size_id);
                    $total =  $branch_dish_size->price;
                } else {
                    $total =  $Branch_Dish->price;
                }
            } else {
                return CustomRespondWithBadRequest(__('order.dish_not_found'));
            }

            $price_before_tax = $tax_application == 1 ? applyTax($total * $DataOrderDetail['quantity'], $tax_percentage, $tax_application) : $total * $DataOrderDetail['quantity'];
            $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage, $total * $DataOrderDetail['quantity']) : CalculateTax($tax_percentage, $price_before_tax);
            $price_after_tax = $price_before_tax + $tax_value;

            if ($DataOrderDetail['dish_id']) {

                $check_orderDetails = OrderDetail::where('dish_id', $DataOrderDetail['dish_id'])->where('order_id', $order->id)->first();
                if ($check_orderDetails && $check_orderDetails->status != 'pending') {
                    if ($DataOrderDetail['quantity'] > $check_orderDetails->quantity) {
                        $new_quantity = $DataOrderDetail['quantity'] - $check_orderDetails->quantity;
                    } elseif ($DataOrderDetail['quantity'] < $check_orderDetails->quantity) {
                        $new_quantity = $DataOrderDetail['quantity'] - $check_orderDetails->quantity;
                    }
                } else {
                    $new_quantity = $DataOrderDetail['quantity'];
                }

                $orderDetails = OrderDetail::updateOrCreate(
                    ['dish_id' => $DataOrderDetail['dish_id'], 'order_id' => $order->id, 'status' => 'pending'],
                    [
                        'quantity' => $new_quantity,
                        'note' => $DataOrderDetail['note'],
                        'dish_size_id' => $branch_dish_size ? $branch_dish_size->dish_size_id : null,
                        'total' => $total,
                        'price_befor_tax' => $price_before_tax,
                        'price_after_tax' => $price_after_tax,
                        'tax_value' => $tax_value,
                        'created_by' => $order->created_by
                    ]
                );

                $total_price_after_tax += $price_after_tax;
                $total_price_before_tax += $price_before_tax;
                $tax_value_total += $tax_value;

                if (!empty($DataOrderDetail['addon_categories'])) {
                    foreach ($DataOrderDetail['addon_categories'] as $addon_category) {
                        foreach ($addon_category['addon'] as $addon_id) {

                            $addon = BranchMenuAddon::find($addon_id);
                            if ($addon) {
                                $price = $addon->price;
                                $price_before_tax = $tax_application == 1 ? applyTax($price * $DataOrderDetail['quantity'], $tax_percentage, $tax_application) : $price * $DataOrderDetail['quantity'];
                                $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage, $price * $DataOrderDetail['quantity']) : CalculateTax($tax_percentage, $price_before_tax);
                                $price_after_tax = $price_before_tax + $tax_value;

                                $orderAddon = OrderAddon::updateOrCreate(
                                    ['dish_addon_id' => $addon->dish_addon_id, 'order_details_id' => $orderDetails->id, 'order_id' => $order_id, 'status' => 'pending'],
                                    [
                                        'quantity' => $DataOrderDetail['quantity'],
                                        'price_before_tax' => $price_before_tax,
                                        'price_after_tax' => $price_after_tax,
                                        'tax_value' => $tax_value,
                                        'created_by' => $order->created_by
                                    ]
                                );
                                $total_price_after_tax += $price_after_tax;
                                $total_price_before_tax += $price_before_tax;
                                $tax_value_total += $tax_value;
                            } else {
                                return CustomRespondWithBadRequest(__('order.addresbs_not_found'));
                            }
                        }
                    }
                }
            }
        }

        if ($coupon && CheckCouponValid($coupon->id, $total_price_befor_tax)) {
            //DB::rollBack();
            //return RespondWithBadRequest($lang, 11);
        }

        $tax_value = $tax_value_total;
        $total_items = $total_price_before_tax;
        //total here is included tax as setting applied so we need get total before tax
        if ($tax_application == 1) {

            $total_items = $total_price_before_tax - $tax_value_total;  // applyTax($total_items, $tax_percentage, $tax_application);  not used
            // $tax_value = CalculateTax($tax_percentage, $total_items); not used
        }
        //apply coupon before tax
        if ($coupon && $coupon_application == 0) {
            $total_items = applyCoupon($total_items, $coupon);
        } else if ($coupon && $coupon_application == 1) { // apply coupon after tax
            $total_items = applyCoupon($total_price_before_tax, $coupon);
        }

        $order->tax_value = $tax_value;
        $order->total_price_befor_tax = $total_items;
        // $Order->total_price_after_tax = ($total_price_after_tax + $service_fees) - $redeem_total;
        $order->total_price_after_tax = $total_items + $service_fees + $delivery_fees + $tax_value;
        $order->save();


        // if ($request['payment_method'] == 'cash' && !$order_id) {
        //     // add event order tracking
        //     $OrderTracking = new OrderTracking();
        //     $OrderTracking->order_id = $order_id;
        //     $OrderTracking->created_by = $order->created_by;
        //     $OrderTracking->save();
        // }

        // if ($status == 'pending' && $request['type'] == 'Takeaway') {
        // $OrderTracking = new OrderTracking();
        // $OrderTracking->order_id = $order_id;
        // $OrderTracking->created_by = $created_by;
        // $OrderTracking->order_status = 'in_progress';
        // $OrderTracking->save();
        // }


        $order_transaction = new OrderTransaction();
        $order_transaction->order_id = $order->id;
        $order_transaction->is_refund = 0;
        $order_transaction->payment_status = 'unpaid';
        $order_transaction->payment_method = $payment_method;
        $order_transaction->transaction_id = Str::uuid()->toString();;
        $order_transaction->paid = $total_items + $service_fees + $delivery_fees;
        $order_transaction->date = date('Y-m-d');
        $order_transaction->created_by = $order->created_by;
        $order_transaction->coupon_id = $coupon ? $coupon->id : null;
        $order_transaction->save();
    }
}
function addOrderItems($order_id, $type, $coupon, $DataOrderDetails = array())
{
    //settings
    $employee = auth('employee')->user();

    $order = Order::find($order_id);
    $discount = null;
    $total_price_befor_tax = 0;
    $total_addon_price_befor_tax = 0;
    $done = false;
    $tax_application = getBranchSettings($order->branch_id, 'tax_application');
    $tax_percentage = getBranchSettings($order->branch_id, 'tax_percentage');
    $coupon_application = getBranchSettings($order->branch_id, 'coupon_application');
    $service_fees_value =  getBranchSettings($order->branch_id, 'service_fees');
    $service_fees_type =  getBranchSettings($order->branch_id, 'service_fees_type');
    $redeem_total = 0;
    $total_offer_price_befor_tax = 0;
    $delivery_fees = 0;

    //$orderDishId = $order->orderDetails->pluck('dish_id');

    if (sizeof($DataOrderDetails)) {
        $total_price_after_tax = 0;
        $total_price_before_tax = 0;
        $tax_value_total = 0;
        $branch_dish_size = 0;
        $service_value_total = 0;

        //check if dish delete
        // $dishIds = array_map(function ($item) {
        //     return $item['dish_id'];
        // }, $DataOrderDetails);

        $dishIds = array_column($DataOrderDetails, 'dish_id');
        if ($type == "web") {
            $getDishIds = BranchMenu::whereIn('dish_id', $dishIds)->where('branch_id', $order->branch_id)->pluck('dish_id');
        } else {
            $getDishIds = BranchMenu::whereIn('id', $dishIds)->where('branch_id', $order->branch_id)->pluck('dish_id');
        }
        $check_dishes = OrderDetail::whereNotIn('dish_id', $getDishIds)->where('order_id', $order->id)->where('status', 'pending')->get();
        // $check_dishes = OrderDetail::whereNotIn('dish_id', $dishIds)->where('order_id', $order->id)->where('status', 'pending')->get();
        if ($check_dishes) {
            foreach ($check_dishes as $check_dish) {
                $check_dish->update(["status" => "cancel", 'modify_by' => $employee->id]);
                $check_dish->save();
                if ($check_dish->dishAddons) {
                    foreach ($check_dish->dishAddons as $dishAddons) {
                        $dishAddons->where('order_details_id', $check_dish->id)->where('status', 'pending')->update(["status" => "cancel", 'modify_by' => auth('employee')->user()->id]);
                        //$dishAddons->where('status', 'pending')->update(["status" => "cancel", 'modify_by' => $employee->id]);
                        $dishAddons->save();
                    }
                }
            }
        }


        foreach ($DataOrderDetails as $DataOrderDetail) {

            if ($type == "web") {
                $Branch_Dish = BranchMenu::where('dish_id', $DataOrderDetail['dish_id'])->where('branch_id', $order->branch_id)->first();
            } else {
                $Branch_Dish = BranchMenu::where('id', $DataOrderDetail['dish_id'])->where('branch_id', $order->branch_id)->first();
            }
            // dd($DataOrderDetail['dish_id'],$order->branch_id);
            // return $Branch_Dish;

            if ($Branch_Dish) {
                $has_size = $Branch_Dish->dish->has_sizes;
                if ($has_size && $DataOrderDetail['sizeId']) {
                    $size_id = $DataOrderDetail['sizeId'];
                    $branch_dish_size = BranchMenuSize::find($size_id);
                    $total =  $branch_dish_size->price;
                } else {
                    $total =  $Branch_Dish->price;
                }
            } else {
                return CustomRespondWithBadRequest(__('order.dish_not_found'));
            }

            // $price_before_tax = $tax_application == 1 ? applyTax($total * $DataOrderDetail['quantity'], $tax_percentage, $tax_application) : $total * $DataOrderDetail['quantity'];
            // $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage, $total * $DataOrderDetail['quantity']) : CalculateTax($tax_percentage, $price_before_tax);
            // $price_after_tax = $price_before_tax + $tax_value;


            // $check_orderDetails = OrderDetail::where('dish_id', $Branch_Dish->dish_id)->where('order_id', $order->id)->first();
            // if ($check_orderDetails && $check_orderDetails->status != 'pending') {
            //     if ($DataOrderDetail['quantity'] > $check_orderDetails->quantity) {
            //         $new_quantity = $DataOrderDetail['quantity'] - $check_orderDetails->quantity;
            //     } elseif ($DataOrderDetail['quantity'] < $check_orderDetails->quantity) {
            //         $new_quantity = $DataOrderDetail['quantity'] - $check_orderDetails->quantity;
            //     }
            // } else {
            //     $new_quantity = $DataOrderDetail['quantity'];
            // }

            $new_quantity = $DataOrderDetail['quantity'];

            // $price_before_tax = $tax_application == 1 ? applyTax($total * $new_quantity, $tax_percentage, $tax_application) : $total * $new_quantity;
            // $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage, $total * $new_quantity) : $price_before_tax * ($tax_percentage / 100);
            // $price_after_tax = $price_before_tax + $tax_value;

            $price_before_tax = $tax_application == 1 ? applyTax($total * $new_quantity, $tax_percentage, $tax_application) : $total * $new_quantity;
            $service_value =  $service_fees_type != 'fixed' ? ($price_before_tax * ($service_fees_value / 100)) : ($service_fees_value / count($DataOrderDetails));
            $priceAfterService =  $price_before_tax + $service_value;
            $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage,  $priceAfterService) : $priceAfterService * ($tax_percentage / 100);
            $price_after_tax = $priceAfterService + $tax_value;

            if ($DataOrderDetail['dish_id']) {

                // $check_orderDetails = OrderDetail::where('dish_id', $Branch_Dish->dish_id)->where('order_id', $order->id)->first();
                // if ($check_orderDetails && $check_orderDetails->status != 'pending') {
                //     if ($DataOrderDetail['quantity'] > $check_orderDetails->quantity) {
                //         $new_quantity = $DataOrderDetail['quantity'] - $check_orderDetails->quantity;
                //     } elseif ($DataOrderDetail['quantity'] < $check_orderDetails->quantity) {
                //         $new_quantity = $DataOrderDetail['quantity'] - $check_orderDetails->quantity;
                //     }
                // } else {
                //     $new_quantity = $DataOrderDetail['quantity'];
                // }


                if (
                    isset($DataOrderDetail['addon_categories'][0]) &&
                    isset($DataOrderDetail['addon_categories'][0]['addon'])
                ) {
                    $addonIds = $DataOrderDetail['addon_categories'][0]['addon'];
                    // Use $addonIds safely here
                } else {
                    // Handle the case where it's not set
                    $addonIds = null;
                }
                //return $addonIds;

                $checkOrderDetails = OrderDetail::where(
                    [
                        'dish_id' => $Branch_Dish->dish_id,
                        'order_id' => $order->id,
                        'status' => 'pending',
                        'dish_size_id' => $branch_dish_size ? $branch_dish_size->dish_size_id : null,
                        'note' => $DataOrderDetail['note']
                    ]
                )->first();

                if ($checkOrderDetails) {
                    $incomingAddon = [];
                    $checkAddon = [];
                    if ($addonIds) {
                        $incomingAddon = BranchMenuAddon::whereIn('id', $addonIds)->where('branch_id', $order->branch_id)->pluck('dish_addon_id')->toArray();
                        $checkAddon = $checkOrderDetails->dishAddons->pluck('dish_addon_id')->toArray();
                    }

                    if (empty(array_diff($incomingAddon, $checkAddon)) && empty(array_diff($checkAddon, $incomingAddon))) {
                        $orderDetails = tap(OrderDetail::where([
                            'dish_id' => $Branch_Dish->dish_id,
                            'order_id' => $order->id,
                            'status' => 'pending',
                            'note' => $DataOrderDetail['note'],
                        ])->first())->update(
                            [
                                'quantity' => $new_quantity,
                                //'quantity' => $DataOrderDetail['quantity'],
                                'total' => $total,
                                'price_befor_tax' => $price_before_tax,
                                'price_after_tax' => $price_after_tax,
                                'tax_value' => $tax_value,
                                'dish_order' => $DataOrderDetail['dish_order'],
                                'created_by' => $order->created_by
                            ]
                        );
                        $order_details_id = $orderDetails->id;
                    } else {
                        $orderDetails = OrderDetail::create(
                            [
                                'dish_id' => $Branch_Dish->dish_id,
                                'order_id' => $order->id,
                                'status' => 'pending',
                                'dish_size_id' => $branch_dish_size ? $branch_dish_size->dish_size_id : null,
                                'note' => $DataOrderDetail['note'],
                                'quantity' => $new_quantity,
                                //'quantity' => $DataOrderDetail['quantity'],
                                'total' => $total,
                                'price_befor_tax' => $price_before_tax,
                                'price_after_tax' => $price_after_tax,
                                'tax_value' => $tax_value,
                                'dish_order' => $DataOrderDetail['dish_order'],
                                'created_by' => $order->created_by
                            ]
                        );
                        $order_details_id = $orderDetails->id;
                    }

                    // $orderDetails = OrderDetail::updateOrCreate(
                    //     [
                    //         'dish_id' => $Branch_Dish->dish_id,
                    //         'order_id' => $order->id,
                    //         'status' => 'pending',
                    //         'dish_size_id' => $branch_dish_size ? $branch_dish_size->dish_size_id : null,
                    //         'note' => $DataOrderDetail['note'],
                    //     ],
                    //     [
                    //         'quantity' => $new_quantity,
                    //         //'quantity' => $DataOrderDetail['quantity'],
                    //         'total' => $total,
                    //         'price_befor_tax' => $price_before_tax,
                    //         'price_after_tax' => $price_after_tax,
                    //         'tax_value' => $tax_value,
                    //         'dish_order' => $DataOrderDetail['dish_order'],
                    //         'created_by' => $order->created_by
                    //     ]
                    // );

                } else {
                    $orderDetails = OrderDetail::create(
                        [
                            'dish_id' => $Branch_Dish->dish_id,
                            'order_id' => $order->id,
                            'status' => 'pending',
                            'dish_size_id' => $branch_dish_size ? $branch_dish_size->dish_size_id : null,
                            'note' => $DataOrderDetail['note'],
                            'quantity' => $new_quantity,
                            //'quantity' => $DataOrderDetail['quantity'],
                            'total' => $total,
                            'price_befor_tax' => $price_before_tax,
                            'price_after_tax' => $price_after_tax,
                            'tax_value' => $tax_value,
                            'dish_order' => $DataOrderDetail['dish_order'],
                            'created_by' => $order->created_by
                        ]
                    );
                    $order_details_id = $orderDetails->id;
                }

                // $orderDetails = OrderDetail::updateOrCreate(
                //     [
                //         'dish_id' => $Branch_Dish->dish_id,
                //         'order_id' => $order->id,
                //         'status' => 'pending',
                //         'dish_size_id' => $branch_dish_size ? $branch_dish_size->dish_size_id : null,
                //         'note' => $DataOrderDetail['note'],
                //     ],
                //     [
                //         'quantity' => $new_quantity,
                //         //'quantity' => $DataOrderDetail['quantity'],
                //         'total' => $total,
                //         'price_befor_tax' => $price_before_tax,
                //         'price_after_tax' => $price_after_tax,
                //         'tax_value' => $tax_value,
                //         'dish_order' => $DataOrderDetail['dish_order'],
                //         'created_by' => $order->created_by
                //     ]
                // );

                $checkOrderDetails = OrderDetail::where('id', $order_details_id)->first();

                $total_price_after_tax += $price_after_tax;
                $total_price_before_tax += $price_before_tax;
                $tax_value_total += $tax_value;
                $service_value_total += $service_value;
                $checkOrderDetails->service_fees = $service_value;
                $checkOrderDetails->save();



                if (!empty($DataOrderDetail['addon_categories'])) {
                    foreach ($DataOrderDetail['addon_categories'] as $addon_category) {
                        foreach ($addon_category['addon'] as $addon_id) {

                            $addon = BranchMenuAddon::find($addon_id);
                            if ($addon) {
                                $price = $addon->price;
                                // $price_before_tax = $tax_application == 1 ? applyTax($price * $DataOrderDetail['quantity'], $tax_percentage, $tax_application) : $price * $DataOrderDetail['quantity'];
                                // $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage, $price * $DataOrderDetail['quantity']) : CalculateTax($tax_percentage, $price_before_tax);
                                // $price_after_tax = $price_before_tax + $tax_value;

                                // $price_before_tax = $tax_application == 1 ? applyTax($price * $new_quantity, $tax_percentage, $tax_application) : $price * $new_quantity;
                                // $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage, $price * $new_quantity) : $price_before_tax * ($tax_percentage / 100);
                                // $price_after_tax = $price_before_tax + $tax_value;

                                $price_before_tax = $tax_application == 1 ? applyTax($price * $new_quantity, $tax_percentage, $tax_application) : $price * $new_quantity;
                                $service_value =  $service_fees_type != 'fixed' ? ($price_before_tax * ($service_fees_value / 100)) : 0;
                                $priceAfterService =  $price_before_tax +  $service_value;
                                $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage,  $priceAfterService) : $priceAfterService * ($tax_percentage / 100);
                                $price_after_tax = $priceAfterService + $tax_value;

                                $orderAddon = OrderAddon::updateOrCreate(
                                    [
                                        'dish_addon_id' => $addon->dish_addon_id,
                                        'order_details_id' => $checkOrderDetails->id,
                                        'order_id' => $order_id,
                                        'status' => 'pending',
                                    ],
                                    [
                                        'quantity' => $new_quantity,
                                        //'quantity' => $new_quantity,
                                        'price_before_tax' => $price_before_tax,
                                        'price_after_tax' => $price_after_tax,
                                        'tax_value' => $tax_value,
                                        'service_fees' => $service_value,
                                        'created_by' => $order->created_by
                                    ]
                                );

                                $total_price_after_tax += $price_after_tax;
                                $total_price_before_tax += $price_before_tax;
                                $tax_value_total += $tax_value;

                                $orderAddon->service_fees = $service_value;
                                $orderAddon->save();
                                $service_value_total += $service_value;
                            } else {
                                return CustomRespondWithBadRequest(__('order.addresbs_not_found'));
                            }
                        }
                    }
                }
            }

            $branch_dish_size = null;
        }

        if ($coupon && !CheckCouponValid($coupon->id, $total_price_befor_tax)) {
            DB::rollBack();

            //return RespondWithBadRequest($lang, 11);
        }
        $tax_value = $tax_value_total;
        $total_items = $total_price_before_tax;
        $coupon_value = 0;
        if ($order->coupon_id && $coupon_application == 0) {
            $coupon_value = calcCoupon($total_items, $coupon);
            $total_items = applyCoupon($total_items, $coupon);
        } else if ($order->coupon_id && $coupon_application == 1) { // apply coupon after tax
            $coupon_value = calcCoupon($total_price_after_tax, $coupon);
            $total_items = applyCoupon($total_price_after_tax, $coupon);
        }

        $order->service_fees = $service_value_total;
        $order->tax_value = $tax_value;
        $order->total_price_befor_tax = $total_price_before_tax;
        $order->coupon_value = $coupon_value;
        $order->total_price_after_tax = $total_price_before_tax - $coupon_value + $service_value_total + $delivery_fees + $tax_value;
        $order->save();



        $order_transaction = OrderTransaction::updateOrCreate(
            [
                'order_id' => $order->id
            ],
            [
                'is_refund' => 0,
                'payment_status' => 'unpaid',
                'transaction_id' => Str::uuid()->toString(),
                'paid' => $total_price_before_tax - $coupon_value + $service_value_total + $delivery_fees + $tax_value,
                'date' => date('Y-m-d'),
                'created_by' => $order->created_by,
                'coupon_id' => $coupon ? $coupon->id : null
            ]
        );
    }
}
function getNewOrderNumber($type, $num, $branch)
{
    if ($type == 'waiter') {
        return 'WT-' . $branch . $num;
    } elseif ($type == 'cashier') {
        return 'CS-' . $branch . $num;
    } elseif ($type == 'customer_service') {
        return 'CSRV-' . $branch . $num;
    } elseif ($type == 'app') {
        return 'APP-' . $branch . $num;
    } elseif ($type == 'site') {
        return 'ST-' . $branch . $num;
    } else {
        return 'ORD-' . $branch . $num; // Default order number format
    }
}

//this function get any employee id and return array of employees in the same shift
function getEmployeesWithSameShift($employeeId, $dateTime)
{
    try {
        $dateTime = Carbon::parse($dateTime);
        $date = $dateTime->toDateString(); // Extract date
        $time = $dateTime->format('H:i:s'); // Extract time
        $dayIndex = $dateTime->dayOfWeek;

        // Step 1: Get the employee’s schedule for the given date
        $schedule = EmployeeSchedule::where('employee_id', $employeeId)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if (!$schedule) {
            return ['status' => false, 'message' => 'No schedule found for the given date.'];
        }

        // Step 2: Get the shift and timetable for the given day
        $shiftDetail = ShiftDetail::where('shift_id', $schedule->shift_id)
            ->where('day_index', $dayIndex)
            ->with('timetable')
            ->first();

        if (!$shiftDetail || !$shiftDetail->timetable) {
            return ['status' => false, 'message' => 'No timetable found for the given day.'];
        }

        $timetable = $shiftDetail->timetable;
        $shiftStartTime = Carbon::parse($timetable->on_duty_time);
        $shiftEndTime = Carbon::parse($timetable->off_duty_time);

        // Step 3: Find all employees with the same shift schedule
        $employeesWithSameShift = EmployeeSchedule::where('shift_id', $schedule->shift_id)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->pluck('employee_id')
            ->toArray();
        if (empty($employeesWithSameShift)) {
            return ['status' => false, 'message' => 'No employees found for this shift.'];
        }

        // Step 4: Filter employees who are actually working at this time
        $workingEmployees = [];
        foreach ($employeesWithSameShift as $empId) {
            // Check if the time falls within the working shift
            if ($dateTime->between($shiftStartTime, $shiftEndTime)) {
                $workingEmployees[] = $empId;
            }
        }

        if (empty($workingEmployees)) {
            return ['status' => false, 'message' => 'No employees are currently working at this time.'];
        }

        return [
            'status' => true,
            'data' => [
                'shift_id' => $schedule->shift_id,
                'timetable' => $timetable,
                'working_employees' => array_values(array_unique($workingEmployees))
            ],
        ];
    } catch (\Exception $e) {
        Log::error("Error retrieving employees with same shift for Employee ID: $employeeId at $dateTime - " . $e->getMessage());
        return ['status' => false, 'message' => 'Error retrieving shift data.'];
    }
}

//this function get one or more ids and return only the cashiers ids
function filterCashiers($employeeIds)
{
    try {
        if (!is_array($employeeIds)) {
            $employeeIds = [$employeeIds];
        }

        // Get only employees with flag = 'cashier'
        $cashierEmployees = Employee::whereIn('id', $employeeIds)
            ->where('flag', 'cashier')
            ->pluck('id');

        return [
            'status' => true,
            'cashier_ids' => $cashierEmployees->toArray()
        ];
    } catch (\Exception $e) {
        Log::error("Error filtering cashier employees: " . $e->getMessage());
        return ['status' => false, 'message' => 'Error filtering cashier employees.'];
    }
}
function addNotification($notify_type, $type, $description_ar, $description_en, $title_ar, $title_en, $user_id, $created_by, $lang, $order, $url = null)
{
    if (is_string($notify_type)) {
        $notify_type = NotificationCategory::where('name_en', $notify_type)->first();
    }
    $notify = new Notification();
    $notify->notify_type = $notify_type->id;
    $notify->type = $type;
    $notify->description_ar = $description_ar;
    $notify->description_en =  $description_en;
    $notify->title_ar = $title_ar;
    $notify->title_en = $title_en;
    $notify->user_id = $user_id;
    $notify->product_id = is_object($order) ? $order->id : $order;

    $notify->created_by = $created_by;
    $notify->url = $url;
    $notify->status = 0;
    $notify->date_time = now();
    $notify->save();
    $user_flag = $type  === 'admin' ? User::where('id', $user_id)->value('flag') : Employee::where('id', $user_id)->value('flag');
    // Determine language
    $title = ($lang == 'ar') ? $notify->title_ar : $notify->title_en;
    $description = ($lang == 'ar') ? $notify->description_ar : $notify->description_en;

    // Data to send
    $result = [
        'orderId' => $order,
        'title' => $title,
        'description' => $description,
        'url' => $url,
    ];
    $data = [
        'id' => (int) $notify->id,
        'notification_type' => $notify_type,
        'statusId' => $notify->product_id,
        'title_ar' => $notify->title_ar,
        'title_en' => $notify->title_en,
        'description_ar' => $notify->description_ar,
        'description_en' => $notify->description_en,
        'is_read' => boolval($notify->status),
        'date' => now()->toDateString()

    ];

    $typeSlug = str_replace(' ', '_', strtolower($type));
    broadcast(new NotificationEvent($notify->user_id, $typeSlug, $data));

    return $result;
}
function sendManagerNotification($type, $description_ar, $description_en, $title_ar, $title_en, $user_id, $created_by, $lang, $complaint, $url = null)
{
    $notify = new Notification();
    $notify->type = $type;
    $notify->description_ar = $description_ar;
    $notify->description_en =  $description_en;
    $notify->title_ar = $title_ar;
    $notify->title_en = $title_en;
    $notify->user_id = $user_id;
    $notify->created_by = $created_by;
    $notify->url = $url;
    $notify->status = 0;
    $notify->date_time = now();
    $notify->save();

    // Determine language
    $title = ($lang == 'ar') ? $notify->title_ar : $notify->title_en;
    $description = ($lang == 'ar') ? $notify->description_ar : $notify->description_en;

    // Data to send
    $result = [
        'complaint_id' => $complaint,
        'title' => $title,
        'description' => $description,
    ];
    return $result;
}
function checkDishBranches($branchId)
{
    return Branch::where('id', $branchId)
        ->where('is_active', 1)
        ->exists() ? 1 : 0;
}
function checkDishBranchCategories($categoryId, $branchId)
{
    return BranchMenuCategory::where('id', $categoryId)
        ->where('branch_id', $branchId)
        ->where('is_active', 1)
        ->exists() ? 1 : 0;
}
function checkMenuDishes($dishId, $branchId)
{
    return BranchMenu::where('branch_id', $branchId)
        ->where('id', $dishId)
        ->where('is_active', 1)
        ->whereHas('branchMenuCategories', function ($q) {
            $q->where('is_active', 1);
        })
        ->exists() ? 1 : 0;
}
function checkDishes($dishId, $branchId)
{
    return BranchMenu::where('branch_id', $branchId)
        ->where('dish_id', $dishId)
        ->where('is_active', 1)
        ->whereHas('branchMenuCategories', function ($q) {
            $q->where('is_active', 1);
        })
        ->exists() ? 1 : 0;
}
function checkDishSizes($dishId, $dishSizeId, $branchId)
{
    return BranchMenuSize::where('branch_id', $branchId)
        ->where('dish_id', $dishId)
        ->where('id', $dishSizeId)
        ->where('is_active', 1)
        ->exists() ? 1 : 0;
}
function checkDishAddons($dishId, $dishAddonId, $branchId)
{
    return BranchMenuAddon::where('branch_id', $branchId)
        ->where('dish_id', $dishId)
        ->where('id', $dishAddonId)
        ->where('is_active', 1)
        ->exists() ? 1 : 0;
}
function getWorkingEmployeesByBranchAndTime($branchId, $dateTime)
{
    try {
        $dateTime = Carbon::parse($dateTime);
        $date = $dateTime->toDateString(); // Extract date
        $time = $dateTime->format('H:i:s'); // Extract time
        $dayIndex = $dateTime->dayOfWeek;

        // Step 1: Get all employees in the branch
        $employees = Employee::where('branch_id', $branchId)->pluck('id')->toArray();

        if (empty($employees)) {
            return ['status' => false, 'message' => 'No employees found for the given branch.'];
        }

        // Step 2: Get employees scheduled to work on this date
        $scheduledEmployees = EmployeeSchedule::whereIn('employee_id', $employees)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->pluck('employee_id')
            ->toArray(); // Returns a list of employee IDs

        if (empty($scheduledEmployees)) {
            return ['status' => false, 'message' => 'No employees scheduled for this date.'];
        }

        // Step 3: Get shift details that match the current day index
        $shiftDetails = ShiftDetail::whereIn('shift_id', function ($query) use ($scheduledEmployees) {
            $query->select('shift_id')
                ->from('employee_schedules')
                ->whereIn('employee_id', $scheduledEmployees);
        })
            ->where('day_index', $dayIndex)
            ->with('timetable')
            ->get();

        if ($shiftDetails->isEmpty()) {
            return ['status' => false, 'message' => 'No shifts found for the given day.'];
        }

        // Step 4: Filter employees based on their shift timetable (time range)
        $workingEmployees = [];
        foreach ($shiftDetails as $shiftDetail) {
            if ($shiftDetail->timetable) {
                $timetable = $shiftDetail->timetable;
                $shiftStartTime = Carbon::parse($timetable->on_duty_time);
                $shiftEndTime = Carbon::parse($timetable->off_duty_time);

                // Consider overnight shifts (cross-day shifts)
                if ($timetable->cross_day) {
                    $shiftEndTime->addDay();
                }

                // Check if the time falls within the working shift
                if ($dateTime->between($shiftStartTime, $shiftEndTime)) {
                    // Find employees who belong to this shift
                    $employeesInShift = EmployeeSchedule::where('shift_id', $shiftDetail->shift_id)
                        ->whereIn('employee_id', $scheduledEmployees)
                        ->pluck('employee_id')
                        ->toArray();

                    $workingEmployees = array_merge($workingEmployees, $employeesInShift);
                }
            }
        }

        if (empty($workingEmployees)) {
            return ['status' => false, 'message' => 'No employees are currently working at this time.'];
        }

        return [
            'status' => true,
            'working_employee_ids' => array_values(array_unique($workingEmployees))
        ];
    } catch (\Exception $e) {
        Log::error("Error retrieving working employees for Branch ID: $branchId at time: $dateTime -> " . $e->getMessage());
        return ['status' => false, 'message' => 'Error retrieving working employees.'];
    }
}
function checkOrders($orderId)
{
    return Order::where('id', $orderId)
        ->exists() ? 1 : 0;
}
function getBranchMenu($dishId, $branch_id)
{
    return BranchMenu::where('dish_id', $dishId)->where('branch_id', $branch_id)->first()->id ?? 0;
}
function getChefEmployees($branchId, $dateTime)
{
    $employeeIds = getWorkingEmployeesByBranchAndTime($branchId, $dateTime);
    if (!$employeeIds['status']) {
        return [];
    }
    return Employee::where('flag', 'Head Chef')
        ->whereIn('id', $employeeIds['working_employee_ids'])
        ->pluck('id');
    //return $employeeId = [21,16,17];
}
function getEmployeeChefs($headChefIds, $branchId)
{
    return ChifManagerLog::whereIn('employee_id',  $headChefIds)
        ->where('branch_id', $branchId)
        ->where('date', date('Y-m-d'))
        ->orderBy('id', 'desc')
        ->get();
}
function getKitchenDishes($orderDetails, $employeeChefs)
{
    $kitchenDishes = [];
    foreach ($orderDetails as $orderDetail) {
        $branchMenuId = getBranchMenu($orderDetail->dish_id, branch_id: $orderDetail->order->branch_id);
        if ($orderDetail->status == 'cancel' || $orderDetail->in_request_return == 1 || $branchMenuId == 0) {
            continue;
        }

        $assignedEmployees = [];

        foreach ($employeeChefs as $chef) {
            //   $chef = $employeeChefs->first();
            if ($chef) {
                $dishFiltrations = json_decode($chef->dish_filtration);
                foreach ($dishFiltrations as $filtration) {
                    foreach ($filtration->dish_categories as $category) {
                        if (in_array($branchMenuId, $category->dishes)) {
                            if (in_array($chef->employee_id, $assignedEmployees)) {
                                continue;
                            }
                            $orderDetail['dish_addons'] = $orderDetail->dishAddons;
                            $orderDetail['dish_size'] = $orderDetail->dishSize;

                            $employeeFound = false;
                            foreach ($kitchenDishes as &$employee) {
                                if ($employee['id'] == $chef->employee_id) {
                                    $dishExists = false;
                                    foreach ($employee['dishes'] as $existingDish) {
                                        if ($existingDish->id == $orderDetail->id) {
                                            $dishExists = true;
                                            break;
                                        }
                                    }
                                    if (!$dishExists) {
                                        $employee['dishes'][] = $orderDetail;
                                    }
                                    // $employee['dishes'][] = $orderDetail;
                                    $employeeFound = true;
                                    break;
                                }
                            }

                            if (!$employeeFound) {
                                $kitchenDishes[] = [
                                    'id' => $chef->employee_id,
                                    'dishes' => [$orderDetail],
                                ];
                            }

                            break;
                        }
                    }
                }
            }
        }
    }

    return $kitchenDishes;
}
function splitDishesOnOrder($order_id)
{
    $order = Order::where('id', $order_id)->first();
    $dateTime = date("Y-m-d H:i:s");
    $headChefIds = getChefEmployees($order->branch_id, $dateTime);
    $employeeChefs = getEmployeeChefs($headChefIds, $order->branch_id);
    $kitchenDishes = getKitchenDishes($order->orderDetailsWithoutCancel, $employeeChefs);
    return $kitchenDishes;
}
function splitDishesOnAllOrders($order_id, $employee_id)
{
    $employeeChefs = ChifManagerLog::where('employee_id', $employee_id)
        ->where('date', date('Y-m-d'))
        ->orderBy('id', 'desc')
        ->get();
    if ($employeeChefs->isEmpty()) {
        return [];
    }

    $branchId = $employeeChefs->first()->branch_id;
    $twentyFourHoursAgo = Carbon::now()->subHours(24);
    $orders = Order::whereIn('id', $order_id)
        ->where('branch_id', $branchId)
        ->where('created_at', ">=", $twentyFourHoursAgo)
        ->get();

    $kitchenDishes = [];

    foreach ($orders as $order) {
        $dishes = getKitchenDishes($order->orderDetailsWithoutCancel, $employeeChefs);
        $kitchenDishes = array_merge($kitchenDishes, $dishes);
    }
    // dd($kitchenDishes);
    return $kitchenDishes;
}

function getEmployeeCuisineV1($employeeId)
{

    $cuisineCategories = [];

    $employee = Employee::find($employeeId);
    if (!$employee) {
        return ['status' => false, 'message' => 'Error no employee found.'];
    }
    $branchId = $employee->branch_id;

    // Get all ChefCuisineCategory records
    $chefCuisineCategories = ChefCuisineCategory::where('employee_id', $employeeId)->get();
    // $cuisineCategoryIds = $chefCuisineCategories->pluck('cuisine_category_id');
    // if ($cuisineCategoryIds->isEmpty()) {
    //     return [];
    // }

    // $cuisineCategoriesRaw = CuisineCategory::whereIn('id', $cuisineCategoryIds)->get();
    // if ($cuisineCategoriesRaw->isEmpty()) {
    //     return [];
    // }
    $results = [];

    foreach ($chefCuisineCategories as $chefCuisineCategory) {
        $dishes = $chefCuisineCategory->dishes;

        $cuisineCategory = CuisineCategory::find($chefCuisineCategory->cuisine_category_id);
        if ($cuisineCategory) {

            if ($dishes[0] == '-1') {

                $cuisine = Cuisine::find($cuisineCategory->cuisine_id);
                if (!$cuisine) continue;

                $dishCategories = [];

                $branchMenuCategories = BranchMenuCategory::where('branch_id', $employee->branch_id)
                    ->where('dish_category_id', $cuisineCategory->dish_category_id) // wrap in array
                    ->where('is_active', 1)
                    ->get();
                if (!empty($branchMenuCategories)) {

                    foreach ($branchMenuCategories as $branchMenuCategory) {
                        $dishes_arr = BranchMenu::leftJoin('dishes', 'dishes.id', 'branch_menus.dish_id')
                            ->where('branch_menus.branch_id', $branchId)
                            ->where('dishes.cuisine_id', $cuisineCategory->cuisine_id)
                            ->where('branch_menus.branch_menu_category_id', $branchMenuCategory->id)
                            ->where('branch_menus.is_active', 1)
                            ->select('branch_menus.*') // optional: add dish columns if needed
                            ->get();

                        // Attach full dish info
                        foreach ($dishes_arr as $dish) {
                            $dish->dish = Dish::find($dish->dish_id);
                        }
                        $dishCategories[] = [
                            'id' => $branchMenuCategory->id,
                            'dish_category_id' => $branchMenuCategory->dish_category_id,
                            'branch_id' => $branchMenuCategory->branch_id,
                            'is_active' => $branchMenuCategory->is_active,
                            'created_by' => $branchMenuCategory->created_by,
                            'modified_by' => $branchMenuCategory->modified_by,
                            'deleted_by' => $branchMenuCategory->deleted_by,
                            'deleted_at' => $branchMenuCategory->deleted_at,
                            'created_at' => $branchMenuCategory->created_at,
                            'updated_at' => $branchMenuCategory->updated_at,
                            'name' => $branchMenuCategory->name,
                            'dishes' => $dishes_arr,
                            'dish_categories' => $branchMenuCategory->dish_categories,
                        ];
                    }
                }


                $results[] = [
                    'cuisine' => $cuisine,
                    'dishCategories' => $dishCategories,
                ];
            } else {


                $cuisine = Cuisine::find($cuisineCategory->cuisine_id);
                if (!$cuisine) continue;

                $dishCategories = [];

                $branchMenuCategories = BranchMenuCategory::where('branch_id', $branchId)
                    ->where('dish_category_id', $cuisineCategory->dish_category_id)
                    ->where('is_active', 1)
                    ->get();
                if (!empty($branchMenuCategories)) {

                    foreach ($branchMenuCategories as $branchMenuCategory) {
                        $dishes_arr = BranchMenu::leftJoin('dishes', 'dishes.id', '=', 'branch_menus.dish_id')
                            ->where('branch_menus.branch_id', $branchId)
                            ->whereIn('branch_menus.dish_id', $dishes) // Make sure $dishes is array of IDs here
                            ->where('dishes.cuisine_id', $cuisineCategory->cuisine_id)
                            // ->where('branch_menus.branch_menu_category_id', $branchMenuCategory->id)
                            ->where('branch_menus.is_active', 1)
                            ->select('branch_menus.*')
                            ->get();
                        // Attach dish object to each item
                        foreach ($dishes_arr as $dish) {
                            $dish->dish = Dish::find($dish->dish_id);
                        }

                        $dishCategories[] = [
                            'id' => $branchMenuCategory->id,
                            'dish_category_id' => $branchMenuCategory->dish_category_id,
                            'branch_id' => $branchMenuCategory->branch_id,
                            'is_active' => $branchMenuCategory->is_active,
                            'created_by' => $branchMenuCategory->created_by,
                            'modified_by' => $branchMenuCategory->modified_by,
                            'deleted_by' => $branchMenuCategory->deleted_by,
                            'deleted_at' => $branchMenuCategory->deleted_at,
                            'created_at' => $branchMenuCategory->created_at,
                            'updated_at' => $branchMenuCategory->updated_at,
                            'name' => $branchMenuCategory->name,
                            'dishes' => $dishes_arr,
                            'dish_categories' => $branchMenuCategory->dish_categories,
                        ];
                    }
                }
                $results[] = [
                    'cuisine' => $cuisine,
                    'dishCategories' => $dishCategories,
                ];
            }
        }
    }
    return $results;
}


function getEmployeeCuisine($employeeId)
{

    $cuisineCategories = [];

    $employee = Employee::find($employeeId);
    if (!$employee) {
        return ['status' => false, 'message' => 'Error no employee found.'];
    }
    $branchId = $employee->branch_id;

    // Get all ChefCuisineCategory records
    $chefCuisineCategories = ChefCuisineCategory::where('employee_id', $employeeId)->get();
    // $cuisineCategoryIds = $chefCuisineCategories->pluck('cuisine_category_id');
    // if ($cuisineCategoryIds->isEmpty()) {
    //     return [];
    // }

    // $cuisineCategoriesRaw = CuisineCategory::whereIn('id', $cuisineCategoryIds)->get();
    // if ($cuisineCategoriesRaw->isEmpty()) {
    //     return [];
    // }
    $results = [];

    foreach ($chefCuisineCategories as $chefCuisineCategory) {
        $dishes = $chefCuisineCategory->dishes;
        $cuisineCategory = CuisineCategory::find($chefCuisineCategory->cuisine_category_id);
        if (!$cuisineCategory) continue;

        $cuisine = Cuisine::find($cuisineCategory->cuisine_id);
        if (!$cuisine) continue;

        // Prepare dish categories
        $branchMenuCategories = BranchMenuCategory::where('branch_id', $branchId)
            ->where('dish_category_id', $cuisineCategory->dish_category_id)
            ->where('is_active', 1)
            ->get();

        if ($branchMenuCategories->isEmpty()) continue;

        foreach ($branchMenuCategories as $branchMenuCategory) {
            $query = BranchMenu::leftJoin('dishes', 'dishes.id', '=', 'branch_menus.dish_id')
                ->where('branch_menus.branch_id', $branchId)
                ->where('dishes.cuisine_id', $cuisine->id)
                ->where('branch_menus.branch_menu_category_id', $branchMenuCategory->id)
                ->where('branch_menus.is_active', 1);

            if ($dishes[0] != '-1') {
                $query->whereIn('branch_menus.dish_id', $dishes);
            }
            $dishes_arr = $query->select('branch_menus.*')->get();
            foreach ($dishes_arr as $dish) {
                $dish->dish = Dish::find($dish->dish_id);
                $dish->name = $dish->dish->name ?? '';
            }

            $dishCategory = [
                'id' => $branchMenuCategory->id,
                'dish_category_id' => $branchMenuCategory->dish_category_id,
                'branch_id' => $branchMenuCategory->branch_id,
                'is_active' => $branchMenuCategory->is_active,
                'created_by' => $branchMenuCategory->created_by,
                'modified_by' => $branchMenuCategory->modified_by,
                'deleted_by' => $branchMenuCategory->deleted_by,
                'deleted_at' => $branchMenuCategory->deleted_at,
                'created_at' => $branchMenuCategory->created_at,
                'updated_at' => $branchMenuCategory->updated_at,
                'name' => $branchMenuCategory->name,
                'dishes' => $dishes_arr,
                'dish_categories' => $branchMenuCategory->dish_categories,
            ];

            // Check if cuisine already exists in results
            $existing = collect($results)->firstWhere('cuisine.id', $cuisine->id);
            if ($existing) {
                // Append to existing dishCategories array
                foreach ($results as &$item) {
                    if ($item['cuisine']['id'] === $cuisine->id) {
                        $item['cuisine']['dishCategories'][] = $dishCategory;
                        break;
                    }
                }
            } else {
                // Add new cuisine with first dishCategory
                $results[] = [
                    'cuisine' => [
                        'id' => $cuisine->id,
                        'is_active' => $cuisine->is_active,
                        'image_path' => $cuisine->image_path,
                        'name' => $cuisine->name,
                        'description' => $cuisine->description,
                        'name_site' => $cuisine->name_site,
                        'description_site' => $cuisine->description_site,
                        'dishCategories' => [$dishCategory],
                    ]
                ];
            }
        }
    }

    return $results;
}


function getEmployeeCuisine66($employeeId)
{
    $cuisineCategories = [];
    //$checkEmployee = Employee::where('id', $employeeId)->exists() ? $employeeId : 0;
    $checkEmployee = Employee::where('id', $employeeId)->first();
    if (!$checkEmployee) {
        return ['status' => false, 'message' => 'Error no employee found.'];
    }

    $checkChefCuisineCategories = ChefCuisineCategory::where('employee_id', $employeeId)->get();
    $checkChefCuisineCategoryId = $checkChefCuisineCategories->pluck('cuisine_category_id');
    if ($checkChefCuisineCategoryId->isEmpty()) {
        return [];
    }

    $checkAllCuisineCategories = CuisineCategory::whereIn('id', $checkChefCuisineCategoryId)->get();
    if ($checkAllCuisineCategories->isEmpty()) {
        return [];
    }

    $cuisine = [];
    $dishCategory = [];

    foreach ($checkAllCuisineCategories as $checkAllCuisineCategory) {
        $cuisine = Cuisine::where('id', $checkAllCuisineCategory->cuisine_id)->first();
        $cuisine['dishCategory'] = BranchMenuCategory::where('dish_category_id', $checkAllCuisineCategory->dish_category_id)->where('branch_id', $checkEmployee->branch_id)->first();
    }
    return $cuisine;
}

function getEmployeeWorkDays($employeeId, $lang)
{
    $checkEmployee = Employee::where('id', $employeeId)->exists() ? $employeeId : 0;

    if ($checkEmployee == 0) {
        return ['status' => false, 'message' => 'Error no employee found.'];
    }

    $checkEmployeeSchedule = EmployeeSchedule::where('employee_id', $employeeId)->orderBy('id', 'desc')->first();
    if (!$checkEmployeeSchedule) {
        return [];
    }

    $checkShiftDetails = ShiftDetail::where('shift_id', $checkEmployeeSchedule->shift_id)->get();
    if ($checkShiftDetails->isEmpty()) {
        return [];
    }

    $dayNames = [
        0 => __('header.Sunday'),
        1 => __('header.Monday'),
        2 => __('header.Tuesday'),
        3 => __('header.Wednesday'),
        4 => __('header.Thursday'),
        5 => __('header.Friday'),
        6 => __('header.Saturday'),
    ];

    $workDays = [];
    $seenDays = [];

    foreach ($checkShiftDetails as $shiftDetails) {
        $dayIndex = $shiftDetails->day_index;

        if (in_array($dayIndex, $seenDays)) {
            continue; // Skip duplicate day_index
        }

        $seenDays[] = $dayIndex;

        $dayName = $dayNames[$dayIndex] ?? 'Unknown Day';
        $workDays[] = $dayName . ': من' . $shiftDetails->timetable->on_duty_time . ' الى' . $shiftDetails->timetable->off_duty_time;
    }



    return $workDays;
}

function getEmployeeWorkSchedule($employeeId, $lang = 'ar')
{
    App::setLocale($lang);

    $employee = Employee::find($employeeId);
    if (!$employee) {
        return ['status' => false, 'message' => __('messages.employee_not_found')];
    }

    $schedule = EmployeeSchedule::where('employee_id', $employeeId)
        ->orderByDesc('id')
        ->first();

    if (!$schedule) {
        return ['status' => false, 'message' => __('messages.schedule_not_found')];
    }

    $shiftDetails = ShiftDetail::where('shift_id', $schedule->shift_id)
        ->with('timetable')
        ->get();

    if ($shiftDetails->isEmpty()) {
        return ['status' => false, 'message' => __('messages.no_shift_details')];
    }

    $dayNames = [
        0 => __('header.Sunday'),
        1 => __('header.Monday'),
        2 => __('header.Tuesday'),
        3 => __('header.Wednesday'),
        4 => __('header.Thursday'),
        5 => __('header.Friday'),
        6 => __('header.Saturday'),
    ];

    // Group by time range
    $grouped = [];
    foreach ($shiftDetails as $detail) {
        $on = date('g:i A', strtotime($detail->timetable->on_duty_time));
        $off = date('g:i A', strtotime($detail->timetable->off_duty_time));
        $key = $on . '-' . $off;

        $grouped[$key][] = $dayNames[$detail->day_index] ?? '—';
    }

    // Format grouped times
    $workTimes = [];
    foreach ($grouped as $timeRange => $days) {
        [$on, $off] = explode('-', $timeRange);

        $formatted = [
            'days' => implode(' - ', $days),
            'time' => ($lang == 'ar')
                ? 'من ' . convertToArabicTime($on) . ' الى ' . convertToArabicTime($off)
                : 'From ' . $on . ' to ' . $off,
        ];

        $workTimes[] = $formatted;
    }

    $timetable = $shiftDetails->first()->timetable;

    // $totalHours = $timetable->work_hours ?? 0;
    $total = calculateWorkHours(
        $timetable->on_duty_time,
        $timetable->off_duty_time,
        $timetable->cross_day
    ) ?? 0;
    // Final formatted output
    return [
        'work_times' => $workTimes,
        'total_hours' => $total['hours'] . ':' . $total['minutes'] . ($lang == 'ar' ? ' ساعات' : ' hours'),
    ];
}
function calculateWorkHours($onDuty, $offDuty, $crossDay = false)
{
    // Parse times using Carbon
    $start = Carbon::createFromFormat('H:i:s', $onDuty);
    $end = Carbon::createFromFormat('H:i:s', $offDuty);

    // If shift passes midnight (e.g., starts 22:00, ends 06:00 next day)
    if ($crossDay && $end->lessThanOrEqualTo($start)) {
        $end->addDay();
    }

    // Calculate total hours and minutes
    $diffInMinutes = $end->diffInMinutes($start);
    $hours = floor($diffInMinutes / 60);
    $minutes = $diffInMinutes % 60;

    // Format like "8 hours", "8 ساعات", etc.
    return [
        'hours' => $hours,
        'minutes' => $minutes,
        'formatted' => ($minutes > 0)
            ? "{$hours}h {$minutes}m"
            : "{$hours}h",
        'arabic' => ($minutes > 0)
            ? "{$hours} ساعات و {$minutes} دقيقة"
            : "{$hours} ساعات",
    ];
}
/**
 * Convert English AM/PM time to Arabic-style format
 */
function convertToArabicTime($time)
{
    $time = str_replace(['AM', 'PM'], ['صباحاً', 'مساءً'], $time);
    $time = str_replace(['am', 'pm'], ['صباحاً', 'مساءً'], $time);
    return str_replace(':', ':', $time);
}
function getEmployeeTodayTimetableId($employeeId)
{
    $today = now();
    $todayIndex = $today->dayOfWeek; // 0 = Sunday, 6 = Saturday

    // Try to find an active schedule within valid date range
    $activeSchedule = EmployeeSchedule::where('employee_id', $employeeId)
        ->orderBy('id', 'desc')
        ->first();
    if (!$activeSchedule) {
        $activeSchedule = EmployeeSchedule::where('employee_id', $employeeId)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->orderBy('id', 'desc')
            ->first();
    }


    // If no active schedule found, fallback to the latest schedule (optional)
    $schedule = $activeSchedule ?? EmployeeSchedule::where('employee_id', $employeeId)
        ->orderBy('id', 'desc')
        ->first();

    if (!$schedule) {
        return null;
    }

    // Get today's shift detail
    $shiftDetail = ShiftDetail::where('shift_id', $schedule->shift_id)
        ->where('day_index', $todayIndex)
        ->first();

    return $shiftDetail ? $shiftDetail->timetable_id : null;
}


function sendToKitchen($order_id, $lang = 'en')
{
    Log::info("Starting sendToKitchen for order $order_id");

    // Fetch dishes split by employees
    $chef_dishes = splitDishesOnOrder($order_id);
    Log::info("Fetched dishes:", ['chef_dishes' => $chef_dishes]);

    // Get order details once
    $order = Order::with(['table.floorPartitions', 'orderDetailsWithoutCancel.dish', 'orderDetailsWithoutCancel.dishSize', 'orderDetailsWithoutCancel.dishAddons'])
        ->find($order_id);
    $orderItemsCount = $order->orderDetailsWithoutCancel->sum('quantity');
    $maxDishTime = $order->orderDetailsWithoutCancel->max(fn($detail) => $detail->dish->time ?? 0);

    $orderData = [
        'order_type' => $order->type,
        'status' => $order->status,
        'order_id' => $order->id,
        'order_number' => $order->order_number,
        'order_items_count' => $orderItemsCount,
        'table_number' => $order->table->table_number ?? null,
        'floor_partition' => $order->table->floorPartitions->name ?? null,
        'floor_partition_ar' => $order->table->floorPartitions->name_ar ?? null,
        'floor_partition_en' => $order->table->floorPartitions->name_en ?? null,
        'order_time' => $maxDishTime,
        'created_at' => $order->created_at,
        'date' => $order->date,
        'time' => $order->time,
    ];

    // Step 1: Calculate the maximum preparation time for all dishes
    $maxPreparationTime = 0;
    foreach ($chef_dishes as $item) {
        foreach ($item['dishes'] as $dish) {
            $prepTime = Dish::where('id', $dish['dish_id'])->value('time') ?? 0;
            if ($prepTime > $maxPreparationTime) {
                $maxPreparationTime = $prepTime;
            }
        }
    }

    // Step 2: Organize all dishes by chef
    $chefsDishes = [];
    foreach ($chef_dishes as $item) {
        $chefId = $item['id'];
        if (!isset($chefsDishes[$chefId])) {
            $chefsDishes[$chefId] = [];
        }

        foreach ($item['dishes'] as $dish) {
            $dish['preparation_time'] = Dish::where('id', $dish['dish_id'])->value('time') ?? 0;
            $dish['order'] = OrderDetail::where('dish_id', $dish['dish_id'])
                // ->where('status', '!=', 'cancel')
                ->where('order_id', $order_id)
                ->value('dish_order') ?? -1;

            $chefsDishes[$chefId][] = $dish;
        }
    }
    foreach ($chefsDishes as $chefId => $dishes) {
        $chef = Employee::find($chefId);
        if (!$chef) continue;

        // Build all order items for this chef
        $orderItems = [];
        foreach ($dishes as $dish) {
            $orderItems[] = [
                'item_id' => $dish['id'],
                'dish_id' => $dish['dish_id'],
                'dish_name' => Dish::find($dish['dish_id'])->name ?? null,
                'dish_name_ar' => Dish::find($dish['dish_id'])->name_ar ?? null,
                'dish_name_en' => Dish::find($dish['dish_id'])->name_en ?? null,
                'size_id' => $dish['dish_size_id'] ?? null,
                'size' => $dish['dish_size_id'] ?
                    (($lang === 'ar') ? optional($dish['dishSize'])->size_name_ar : optional($dish['dishSize'])->size_name_en)
                    : null,
                'quantity' => $dish['quantity'],
                'dish_time' => $dish['preparation_time'],
                'note' => $dish['note'] ?? null,
                'addons' => collect($dish['dishAddons'] ?? [])->map(function ($addon) use ($lang) {
                    return [
                        'addon_category_id' => $addon->Addon->addon_category_id,
                        'addon_id' => $addon->Addon->addon_id,
                        'addon_name' => $lang === 'ar'
                            ? optional($addon->Addon->addons)->name_ar
                            : optional($addon->Addon->addons)->name_en,
                        'addon_name_ar' => optional($addon->Addon->addons)->name_ar,
                        'addon_name_en' => optional($addon->Addon->addons)->name_en,
                    ];
                }),
                'dish_status' => $dish['status'],
            ];
        }

        // Calculate delay based on the dish with longest preparation time
        $maxChefDishTime = max(array_column($orderItems, 'dish_time'));
        $delay = $maxPreparationTime - $maxChefDishTime;

        $responseData = [
            'order_details' => $orderData,
            'order_items' => $orderItems,
        ];

        Log::info("Sending all dishes to chef {$chefId} with delay {$delay} minutes");

        // Send single event for all dishes to this chef
        // if ($delay > 0) {
        //     dispatch(function () use ($chef, $responseData) {
        //         broadcast(new ChefNotify($chef->id, $responseData));
        //     })->delay(now()->addMinutes($delay));
        // } else {

        $title_ar = "يوجد طلب جديد برقم : " . $order->order_number;
        $title_en = "New order Num.: " . $order->order_number;
        addNotification(
            'order',
            'head_chef',
            $title_ar,
            $title_en,
            $title_ar,
            $title_en,
            $chef->id,
            null,
            $lang,
            $order_id
        );

        // broadcast(new ChefNotify($chef->id, $responseData));
        $orderData2 = ['order_id' => $order_id, 'order_type' => $order->type];
        broadcast(new ChefNotify2(
            $chef->id,

            $orderData2
        ));
        // }
    }

    Log::info("Finished sendToKitchen for order $order_id");
}
// function sendToKitchen($order_id, $lang)
// {
//     Log::info("Starting sendToKitchen for order $order_id");

//     // Fetch dishes split by employees
//     $chef_dishes = splitDishesOnOrder($order_id);
//     Log::info("Fetched dishes:", ['chef_dishes' => $chef_dishes]);

//     // Track the maximum preparation time across all dishes
//     $maxPreparationTime = 0;
//     $order = Order::with(['table.floorPartitions', 'orderDetailsWithoutCancel.dish', 'orderDetailsWithoutCancel.dishSize', 'orderDetailsWithoutCancel.dishAddons'])
//         ->find($order_id);
//     $orderItemsCount = $order->orderDetailsWithoutCancel->sum('quantity');
//     $maxDishTime = $order->orderDetailsWithoutCancel->max(fn($detail) => $detail->dish->time ?? 0);

//     $orderData = [
//         'order_type' => $order->type,
//         'status' => $order->status,
//         'order_id' => $order->id,
//         'order_number' => $order->order_number,
//         'order_items_count' => $orderItemsCount,
//         'table_number' => $order->table->table_number ?? null,
//         'floor_partition' => $order->table->floorPartitions->name ?? null,
//         'order_time' => $maxDishTime,
//         'created_at' => $order->created_at,
//         'date' => $order->date,
//         'time' => $order->time,
//     ];
//     $orderItems = [];
//     // Step 1: Calculate the maximum preparation time for all dishes
//     foreach ($chef_dishes as $item) {
//         foreach ($item['dishes'] as $dish) {
//             $dish['preparation_time'] = Dish::where('id', $dish['dish_id'])->value('time') ?? 0;
//             if ($dish['preparation_time'] > $maxPreparationTime) {
//                 $maxPreparationTime = $dish['preparation_time'];
//             }
//         }
//     }



//     // Step 2: Process dishes with relative delays
//     foreach ($chef_dishes as $item) {
//         Log::info("Processing ID: " . $item['id']);

//         foreach ($item['dishes'] as $dish) {
//             Log::info("Dish Details", [
//                 'Dish ID' => $dish->id,
//                 'Status' => $dish->status,
//                 'Quantity' => $dish->quantity,
//                 'Total' => $dish->total,
//                 'Dish ID Reference' => $dish->dish_id
//             ]);

//             // Fetch preparation time for the dish
//             $dish['preparation_time'] = Dish::where('id', $dish['dish_id'])->value('time') ?? 0;

//             // Fetch dish_order from OrderDetail
//             $dish['order'] = OrderDetail::where('dish_id', $dish['dish_id'])
//                 ->where('order_id', $order_id)
//                 ->value('dish_order') ?? -1;

//             Log::info("Dishes with preparation time and order:", ['chef_dishes' => $chef_dishes]);

//             // Step 3: Separate dishes into unordered (-1) and ordered (0,1,2)
//             $unorderedDishes = [];
//             $orderedDishes = [];
//             $addedDishIds = []; // Track dish IDs to avoid duplicates

//             if ($dish['order'] == -1) {
//                 // Check if the dish has already been added
//                 if (!in_array($dish['id'], $addedDishIds)) {
//                     $unorderedDishes[] = $dish; // Add each dish only once
//                     $addedDishIds[] = $dish['id']; // Track the dish ID
//                 }
//             } else {
//                 $orderedDishes[$dish['order']][] = $dish;
//             }

//             Log::info("Unordered dishes:", ['unorderedDishes' => $unorderedDishes]);
//             Log::info("Ordered dishes:", ['orderedDishes' => $orderedDishes]);

//             // Step 4: Handle Unordered Dishes (-1) -> Serve together
//             if (!empty($unorderedDishes)) {
//                 // Group unordered dishes by chef_id
//                 $unorderedDishesByChef = [];
//                 foreach ($unorderedDishes as $dish) {
//                     $unorderedDishesByChef[$dish['chef_id']][] = $dish;
//                 }

//                 // Process dishes for each chef
//                 foreach ($unorderedDishesByChef as $dishes) {
//                     $maxTime = max(array_column($dishes, 'preparation_time')); // Max time for this chef's dishes

//                     foreach ($dishes as $dish) {
//                         $delay = $maxPreparationTime - $dish['preparation_time']; // Relative delay based on global max time
//                         $chef = Employee::find($item['id']);

//                         if ($chef) {
//                             Log::info("Sending unordered dish {$dish['dish_id']} to chef {$chef->id} with delay {$delay} minutes");
//                             $orderItems[] = [
//                                 'item_id' => $dish['id'],
//                                 'dish_id' => $dish['dish_id'],
//                                 'dish_name' => Dish::find($dish['dish_id'])->name ?? null,
//                                 'size_id' => $dish['dish_size_id'] ?? null,
//                                 'size' => $dish['dish_size_id'] ?
//                                     (($lang === 'ar') ? optional($dish['dishSize'])->size_name_ar : optional($dish['dishSize'])->size_name_en)
//                                     : null,
//                                 'quantity' => $dish['quantity'],
//                                 'dish_time' => $dish['preparation_time'],
//                                 'note' => $dish['note'] ?? null,
//                                 'addons' => collect($dish['dishAddons'] ?? [])->map(function ($addon) use ($lang) {
//                                     return [
//                                         'addon_category_id' => $addon->Addon->addon_category_id,
//                                         'addon_id' => $addon->Addon->addon_id,
//                                         'addon_name' => $lang === 'ar'
//                                             ? optional($addon->Addon->addons)->name_ar
//                                             : optional($addon->Addon->addons)->name_en,
//                                     ];
//                                 }),
//                                 'dish_status' => $dish['status'],
//                             ];
//                             $responseData = [
//                                 'order_details' => $orderData,
//                                 'order_items' => $orderItems,
//                                 'order_notes' => $order->note,
//                             ];

//                             dispatch(function () use ($chef, $responseData) {
//                                 broadcast(new ChefNotify($chef, $responseData));
//                             })->delay(now()->addMinutes($delay));
//                         }
//                     }
//                 }
//             }
//         }
//         // Step 4: Handle Ordered Dishes (0 -> 1 -> 2)
//         if (!empty($orderedDishes)) {
//             ksort($orderedDishes); // Sort orders

//             foreach ($orderedDishes as $order => $dishes) {
//                 // Group ordered dishes by chef_id
//                 $orderedDishesByChef = [];
//                 foreach ($dishes as $dish) {
//                     $orderedDishesByChef[$dish['chef_id']][] = $dish;
//                 }

//                 // Process dishes for each chef
//                 foreach ($orderedDishesByChef as $chef_id => $chefDishes) {
//                     $maxTime = max(array_column($chefDishes, 'preparation_time')); // Max time for this chef's dishes

//                     foreach ($chefDishes as $dish) {
//                         $delay = $maxTime - $dish['preparation_time']; // Ensure they finish together
//                         $chef = Employee::find($chef_id);

//                         if ($chef) {
//                             Log::info("Sending dish {$dish['dish_id']} (order $order) to chef {$chef->id} with delay {$delay} minutes");
//                             $orderItems[] = [
//                                 'item_id' => $dish['id'],
//                                 'dish_id' => $dish['dish_id'],
//                                 // 'dish_name' => Dish::find($dish['dish_id'])->name ?? null,
//                                 // 'size_id' => $dish['dish_size_id'] ?? null,
//                                 // 'size' => $dish['dish_size_id'] ?
//                                 //     (($lang === 'ar') ? optional($dish['dishSize'])->size_name_ar : optional($dish['dishSize'])->size_name_en)
//                                 //     : null,
//                                 // 'quantity' => $dish['quantity'],
//                                 // 'dish_time' => $dish['preparation_time'],
//                                 // 'note' => $dish['note'] ?? null,
//                                 // 'addons' => collect($dish['dishAddons'] ?? [])->map(function ($addon) use ($lang) {
//                                 //     return [
//                                 //         'addon_category_id' => $addon->Addon->addon_category_id,
//                                 //         'addon_id' => $addon->Addon->addon_id,
//                                 //         'addon_name' => $lang === 'ar'
//                                 //             ? optional($addon->Addon->addons)->name_ar
//                                 //             : optional($addon->Addon->addons)->name_en,
//                                 //     ];
//                                 // }),
//                                 // 'dish_status' => $dish['status'],
//                             ];
//                             $responseData = [
//                                 'order_details' => $orderData,
//                                 'order_items' => $orderItems,
//                                 // 'order_notes' => $order->note,
//                             ];
//                             dispatch(function () use ($chef, $responseData) {
//                                 broadcast(new ChefNotify($chef, $responseData));

//                                 // broadcast(new ChefNotify($chef, [$dish]));
//                             })->delay(now()->addMinutes($delay));
//                         }
//                     }
//                 }
//             }
//         }
//     }

//     Log::info("Finished sendToKitchen for order $order_id");
// }
function getBranchWorkingHours($branchId)
{
    $lang = app()->getLocale();
    // Map DB day indexes (0-6) to localized names, where 0 = Sunday
    $dayNames = [
        0 => __('header.Sunday'),
        1 => __('header.Monday'),
        2 => __('header.Tuesday'),
        3 => __('header.Wednesday'),
        4 => __('header.Thursday'),
        5 => __('header.Friday'),
        6 => __('header.Saturday')
    ];

    // Fetch and group by unique combinations of opening/closing times and cross_day
    $branchTimes = BranchTime::where('branch_id', $branchId)
        ->where('is_active', 1)
        ->orderBy('day')
        ->get()
        ->groupBy(function ($item) {
            return $item->opening_hour . '|' . $item->closing_hour . '|' . $item->cross_day;
        });

    // Format grouped output
    $result = [];

    foreach ($branchTimes as $key => $group) {
        [$open, $close, $cross] = explode('|', $key);

        $days = $group->pluck('day')->map(function ($d) use ($dayNames) {
            return $dayNames[$d] ?? __('header.Unknown');
        })->toArray();

        // Format times based on current locale
        $formattedOpen = formatTo12Hour($open);
        $formattedClose = formatTo12Hour($close);

        $result[] = [
            'days' => implode(', ', $days),
            'opening_hour' => $formattedOpen,
            'closing_hour' => $formattedClose,
            'cross_day' => (bool) $cross,
        ];
    }

    Log::info("Grouped Branch Working Hours", ['result' => $result]);
    return $result;
}
function formatTo12Hour($time)
{
    try {
        $carbonTime = Carbon::createFromFormat('H:i:s', $time);

        if (app()->getLocale() === 'ar') {
            $period = ($carbonTime->hour < 12) ? 'ص' : 'م';
            return $carbonTime->format('h:i') . ' ' . $period;
        } else {
            return $carbonTime->format('h:i A');
        }
    } catch (\Exception $e) {
        try {
            $carbonTime = Carbon::parse($time);

            if (app()->getLocale() === 'ar') {
                $period = ($carbonTime->hour < 12) ? 'ص' : 'م';
                return $carbonTime->format('h:i') . ' ' . $period;
            } else {
                return $carbonTime->format('h:i A');
            }
        } catch (\Exception $e) {
            return $time;
        }
    }
}
function formatTimeForLocale($time, $isClosingTime = false)
{
    $timestamp = strtotime($time);
    $locale = app()->getLocale();
    App::setLocale($locale);

    if ($locale === 'ar') {
        // Arabic format: 10:00 صباحاً or 11:00 مساءً
        $timePart = date('H:i', $timestamp);
        $hour = date('G', $timestamp);

        if ($hour < 12) {
            $period = 'صباحاً';
        } else {
            $period = 'مساءً';
        }

        return $timePart . ' ' . $period;
    } else {
        // English format: 10:00 am or 11:00 pm
        return date('h:i a', $timestamp);
    }
}
function AddTableReservationLog($data = array())
{
    $check_table_reservation = TableReservation::where('id', $data['table_reservation_id'])->first();
    if (!$check_table_reservation) {
        return ['status' => false, 'message' => 'Error no table reservation found.'];
    }

    $new_data = [
        'table_reservation_id' => $data['table_reservation_id'],
        'client_id' => $data['client_id'] ?? null,
        'cashier_id' => $data['cashier_id'] ?? null,
        'waiter_id' => $data['waiter_id'] ?? null,
        'canceled' => $data['canceled'] ?? '0',
        'canceled_reason' => $data['canceled_reason'] ?? null,
        'date' => $data['date'] ?? null,
        'from' => $data['from'] ?? null,
        'to' => $data['to'] ?? null,
        'created_by' => $check_table_reservation->created_by
    ];

    $dateTime = $data['date'] . ' ' . $data['from'];
    $get_branch_employee = getWorkingEmployeesByBranchAndTime($check_table_reservation->branch_id, $dateTime);

    $add_log = TableReservationLog::firstOrCreate(
        ['table_reservation_id' => $data['table_reservation_id']]
    );
    $add_log->fill($new_data);
    $add_log->save();
}
function getWorkingEmployeesByDate($dateTime)
{
    try {
        $dateTime = Carbon::parse($dateTime);
        $date = $dateTime->toDateString();
        $time = $dateTime->format('H:i:s');
        $dayIndex = $dateTime->dayOfWeek;
        // Step 1: Get employees scheduled to work on this date
        $schedules = EmployeeSchedule::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->get();

        if ($schedules->isEmpty()) {
            return ['status' => false, 'message' => 'No employees scheduled for this date.'];
        }

        $workingEmployees = [];

        foreach ($schedules as $schedule) {
            $shiftDetail = ShiftDetail::where('shift_id', $schedule->shift_id)
                ->where('day_index', $dayIndex)
                ->with('timetable')
                ->first();

            if ($shiftDetail && $shiftDetail->timetable) {
                $timetable = $shiftDetail->timetable;
                $start = Carbon::parse($timetable->on_duty_time);
                $end = Carbon::parse($timetable->off_duty_time);

                if ($timetable->cross_day) {
                    $end->addDay();
                }

                if ($dateTime->between($start, $end)) {
                    $workingEmployees[] = $schedule->employee_id;
                }
            }
        }

        if (empty($workingEmployees)) {
            return ['status' => false, 'message' => 'No employees are working at this time.'];
        }

        return [
            'status' => true,
            'working_employee_ids' => array_values(array_unique($workingEmployees)),
        ];
    } catch (\Exception $e) {
        Log::error("Error in getWorkingEmployeesByDate on $dateTime: " . $e->getMessage());
        return ['status' => false, 'message' => 'Error retrieving working employees.'];
    }
}
function getWaiters()
{
    $query = Employee::where('flag', 'waiter');

    if (auth('admin')->user()->hasRole('Branch Manager')) {
        $branchId = getBranchManagerID();
        $query->where('branch_id', $branchId);
    }

    return $query->get();
}
function getItemCodes()
{
    return ItemCode::all();
}
function getDishes()
{
    return Dish::where('is_active', 1)->get();
}
function getCategories()
{
    return DishCategory::where('is_active', 1)->get();
}


function send_push_notification(
    $user_fcm_token,
    $description_ar,
    $description_en,
    $title_ar,
    $title_en,
    $type,
    $receiver_id,
    $created_by,
    $request_id, // or request ID
    $lang = 'ar',
    $notify_type = null, // <-- add this
    $url = null
) {
    $projectId = "al-koot-74c79";
    //        $projectId = "test-agent-nfgx";

    try {
        // Initialize Google Client
        //            $credentialsFilePath = Storage::path('firebase-credentials.json');
        $credentialsFilePath = base_path('firebase-credentials.json');
        //            dd($credentialsFilePath);
        $client = new \Google_Client();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $access_token = $client->getAccessToken()['access_token'];

        // Prepare notification fields based on language
        $title = $lang === 'ar' ? $title_ar : $title_en;
        $message = $lang === 'ar' ? $description_ar : $description_en;
        if (is_string($notify_type)) {
            $notify_type = NotificationCategory::where('name_en', $notify_type)->value('id') ?? null;
        }
        // Push payload
        $data = [
            "message" => [
                "token" => $user_fcm_token,
                "data" => [
                    "title" => $title,
                    "body" => $message,
                    "type" => $type,
                    "request_id" => (string)$request_id,
                    "url" => $url ?? '',
                    "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                    "lang" => $lang,
                ],
            ]
        ];

        $payload = json_encode($data);

        // Send via cURL
        $headers = [
            "Authorization: Bearer $access_token",
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_POSTFIELDS => $payload,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            Log::error("FCM cURL Error: $error");
            return false;
        }

        if ($httpCode !== 200) {
            Log::error("FCM API Error: HTTP $httpCode - $response");
            return false;
        }

        // Save notification to database
        $notify = new Notification();
        $notify->type = $type;
        $notify->description_ar = $description_ar;
        $notify->description_en = $description_en;
        $notify->title_ar = $title_ar;
        $notify->title_en = $title_en;
        $notify->user_id = $receiver_id;
        $notify->created_by = $created_by;
        $notify->notify_type = $notify_type;
        $notify->url = $url;
        $notify->status = 0;
        $notify->date_time = now();
        $notify->save();

        // Return custom result
        return [
            'request_id' => $request_id,
            'title' => $title,
            'description' => $message,
            'fcm_response' => json_decode($response, true)
        ];
    } catch (\Exception $e) {
        Log::error("FCM Notification Error: " . $e->getMessage());
        return false;
    }
}

function checkEmployeeAttendance($employee, $date): string
{
    if (is_numeric($employee)) {
        $employee = Employee::find($employee);
    }

    if (!$employee) {
        return 'Employee not found';
    }

    $date = Carbon::parse($date);

    // Check for punch-in records
    $punchInCount = BiometricTransaction::where('emp_code', $employee->employee_code)
        ->where('punch_state', '0')
        ->whereDate('punch_time', $date)
        ->count();

    // Check for punch-out records
    $punchOutCount = BiometricTransaction::where('emp_code', $employee->employee_code)
        ->where('punch_state', '1')
        ->whereDate('punch_time', $date)
        ->count();

    return ($punchInCount > 0 || $punchOutCount > 0) ? 'Present' : 'Absent';
}

function checkEmployeeAttendanceStatus(Employee $employee, $date): array
{
    $date = Carbon::parse($date);
    $dayIndex = $date->dayOfWeek; // 0 (Sunday) to 6 (Saturday)

    // Check for punch-in records
    $earliestPunch = BiometricTransaction::where('emp_code', $employee->employee_code)
        ->where('punch_state', '0')
        ->whereDate('punch_time', $date)
        ->orderBy('punch_time', 'asc')
        ->first();

    // If no punch-in, return absent status
    if (!$earliestPunch) {
        return [
            'status' => 'Absent',
            'message' => "Employee {$employee->employee_code} was absent on {$date->toDateString()}",
        ];
    }
    $attendance = Attendance::where('employee_id', $employee->id)->where('date', $date)->first();
    if ($attendance) {
        $schedule = $attendance->employeeSchedule;
    } else {
        $schedule = $employee->scheduleForDate($date);
    }

    // Get the active schedule for the date
    if (!$schedule || !$schedule->shift) {
        return [
            'status' => 'Absent',
            'message' => "Employee {$employee->employee_code} has no active schedule on {$date->toDateString()}",
        ];
    }

    // Get shift details for the specific day
    $shiftDetail = $schedule->shift->details()
        ->where('day_index', $dayIndex)
        ->whereNull('deleted_at')
        ->first();

    if (!$shiftDetail || !$shiftDetail->timetable) {
        return [
            'status' => 'Absent',
            'message' => "Employee {$employee->employee_code} has no timetable for {$date->toDateString()}",
        ];
    }

    $timetable = $shiftDetail->timetable;

    $onDutyTime = Carbon::parse($timetable->on_duty_time);
    $latenessGracePeriod = $timetable->lateness_grace_period;
    $startLateTimeOption = $timetable->start_late_time_option;

    $punchInTime = Carbon::parse($earliestPunch->punch_time)->setDate($date->year, $date->month, $date->day);

    // Determine lateness threshold based on start_late_time_option
    $lateThreshold = $onDutyTime->copy();
    if ($startLateTimeOption === 'after_duty_time_grace_period') {
        $lateThreshold->addMinutes($latenessGracePeriod);
    } elseif ($startLateTimeOption === 'after_duty_time') {
    } elseif ($startLateTimeOption === 'from_duty_time') {
        $lateThreshold = $onDutyTime->copy();
    }

    if ($punchInTime->lessThan($onDutyTime)) {
        $status = 'Early';
        $message = "Employee {$employee->employee_code} punched in early at {$punchInTime->toTimeString()} on {$date->toDateString()}";
    } elseif ($startLateTimeOption === 'after_duty_time_grace_period' && $punchInTime->lessThanOrEqualTo($lateThreshold)) {
        $status = 'On Time';
        $message = "Employee {$employee->employee_code} punched in on time at {$punchInTime->toTimeString()} on {$date->toDateString()}";
    } elseif ($startLateTimeOption === 'after_duty_time' && $punchInTime->equalTo($onDutyTime)) {
        $status = 'On Time';
        $message = "Employee {$employee->employee_code} punched in on time at {$punchInTime->toTimeString()} on {$date->toDateString()}";
    } else {
        $status = 'Late';
        $message = "Employee {$employee->employee_code} punched in late at {$punchInTime->toTimeString()} on {$date->toDateString()}";
    }

    return [
        'status' => $status,
        'message' => $message,
        'punch_in_time' => $punchInTime->toTimeString(),
        'on_duty_time' => $onDutyTime->toTimeString(),
        'late_threshold' => $lateThreshold->toTimeString(),
    ];
}

/**
 * Calculate attendance metrics (overtime, late minutes, early departure)
 */
function calculateAttendanceMetrics(Employee $employee, $date, $clockInTime = null, $clockOutTime = null): array
{
    $date = Carbon::parse($date);
    $dayIndex = $date->dayOfWeek; // 0 (Sunday) to 6 (Saturday)
    // Initialize default values
    $metrics = [
        'overtime_hours' => '0 hours 0 minutes',
        'overtime_minutes' => 0,
        'late_minutes' => 0,
        'early_departure_minutes' => 0,
        'total_hours' => '0 hours 0 minutes',
        'total_minutes' => 0,
        'scheduled_hours' => '0 hours 0 minutes',
        'scheduled_minutes' => 0,
    ];

    // Get the active schedule for the date
    $attendance = Attendance::where('employee_id', $employee->id)->where('date', $date)->first();
    if ($attendance) {
        $schedule = $attendance->employeeSchedule;
    } else {
        $schedule = $employee->scheduleForDate($date);
    }
    if (!$schedule || !$schedule->shift) {
        return $metrics;
    }

    // Get shift details for the specific day
    $shiftDetail = $schedule->shift->details()
        ->where('day_index', $dayIndex)
        ->whereNull('deleted_at')
        ->first();

    // if (!$shiftDetail || !$shiftDetail->timetable) {
    //     return $metrics;
    // }
    if (!$shiftDetail || !$shiftDetail->timetable) {
        $punchInTime = Carbon::parse($clockInTime)->setDate($date->year, $date->month, $date->day);
        $punchOutTime = Carbon::parse($clockOutTime)->setDate($date->year, $date->month, $date->day);

        $totalMinutes = $punchOutTime->diffInMinutes($punchInTime);
        $totalHours = intval($totalMinutes / 60);
        $totalMins = $totalMinutes % 60;
        $metrics['total_hours'] = $totalHours . ' hours ' . $totalMins . ' minutes';
        $metrics['total_minutes'] = $totalMinutes;
        // Calculate overtime (worked more than scheduled)
        // if ($totalMinutes > $scheduledMinutes) {
        // $overtimeMinutes = $totalMinutes - $scheduledMinutes;
        // $overtimeHours = intval($overtimeMinutes / 60);
        // $overtimeMins = $overtimeMinutes % 60;
        $metrics['overtime_hours'] = $totalHours . ' hours ' . $totalMins . ' minutes';
        $metrics['overtime_minutes'] = $totalMinutes;
        // }

        return $metrics;
    }
    $timetable = $shiftDetail->timetable;
    $onDutyTime = Carbon::parse($timetable->on_duty_time)->setDate($date->year, $date->month, $date->day);
    $offDutyTime = Carbon::parse($timetable->off_duty_time)->setDate($date->year, $date->month, $date->day);
    $latenessGracePeriod = $timetable->lateness_grace_period ?? 0;
    $startLateTimeOption = $timetable->start_late_time_option;
    // Handle cross-day shifts
    if ($timetable->cross_day && $offDutyTime->lessThan($onDutyTime)) {
        $offDutyTime->addDay();
    }

    // Calculate scheduled work hours
    $scheduledMinutes = $offDutyTime->diffInMinutes($onDutyTime);
    $scheduledHours = intval($scheduledMinutes / 60);
    $scheduledMins = $scheduledMinutes % 60;
    $metrics['scheduled_hours'] = $scheduledHours . ' hours ' . $scheduledMins . ' minutes';
    $scheduledDecimalHours = $scheduledHours + ($scheduledMins / 60);
    $metrics['scheduled_hours_need'] = round($scheduledDecimalHours, 2); // e.g. 7.50
    $metrics['scheduled_minutes'] = $scheduledMinutes;

    // dd($offDutyTime, $onDutyTime, $scheduledHours, $scheduledMins, $metrics);
    // Calculate late minutes if clock in time is provided
    if ($clockInTime) {
        $punchInTime = Carbon::parse($clockInTime)->setDate($date->year, $date->month, $date->day);

        // Determine lateness threshold based on start_late_time_option
        $lateThreshold = $onDutyTime->copy();
        if ($startLateTimeOption === 'after_duty_time_grace_period') {
            $lateThreshold->addMinutes($latenessGracePeriod);
        } elseif ($startLateTimeOption === 'from_duty_time') {
            $lateThreshold = $onDutyTime->copy();
        }
        // For 'after_duty_time', threshold remains the same as on_duty_time
        if ($punchInTime->greaterThan($lateThreshold)) {
            $metrics['late_minutes'] = $punchInTime->diffInMinutes($lateThreshold);
        }
    }

    // Calculate total hours and overtime/early departure if both times are provided
    if ($clockInTime && $clockOutTime) {
        $punchInTime = Carbon::parse($clockInTime)->setDate($date->year, $date->month, $date->day);
        $punchOutTime = Carbon::parse($clockOutTime)->setDate($date->year, $date->month, $date->day);

        // Handle cross-day punch out
        if ($timetable->cross_day && $punchOutTime->lessThan($punchInTime)) {
            $punchOutTime->addDay();
        }

        // Calculate total worked time
        $totalMinutes = $punchOutTime->diffInMinutes($punchInTime);
        $totalHours = intval($totalMinutes / 60);
        $totalMins = $totalMinutes % 60;
        $metrics['total_hours'] = $totalHours . ' hours ' . $totalMins . ' minutes';
        $metrics['total_minutes'] = $totalMinutes;
        // Calculate overtime (worked more than scheduled)
        if ($totalMinutes > $scheduledMinutes) {
            $overtimeMinutes = $totalMinutes - $scheduledMinutes;
            $overtimeHours = intval($overtimeMinutes / 60);
            $overtimeMins = $overtimeMinutes % 60;
            $metrics['overtime_hours'] = $overtimeHours . ' hours ' . $overtimeMins . ' minutes';
            $metrics['overtime_minutes'] = $overtimeMinutes;
        }

        // Calculate early departure minutes (left before scheduled off duty time)
        if ($punchOutTime->lessThan($offDutyTime)) {
            $metrics['early_departure_minutes'] = $offDutyTime->diffInMinutes($punchOutTime);
        }
    }

    return $metrics;
}
function getBranchFloorPartitions($branchId)
{
    $branch = Branch::with('floors')->find($branchId);
    $floorPartitions = $branch->floors->pluck('floorPartitions')->flatten();
    $floorPartitions->each(function ($partition) {
        $partition->makeHidden('tables');
    });
    return $floorPartitions;
}
function getBranchCategoryDetails($branch_id, $category_id, $type, $count)
{
    $branch_category  = "";
    if ($type == "web") {
        if ($count == "first") {
            $branch_category = BranchMenuCategory::where('dish_category_id', $category_id)->where('branch_id', $branch_id)->where('is_active', 1)->first();
        } else {
            $branch_category = BranchMenuCategory::where('dish_category_id', $category_id)->where('branch_id', $branch_id)->where('is_active', 1)->pluck('dish_category_id');
        }
    } else {
        if ($count == "first") {
            $branch_category = BranchMenuCategory::where('id', $category_id)->where('branch_id', $branch_id)->where('is_active', 1)->first();
        } else {
            $branch_category = BranchMenuCategory::where('id', $category_id)->where('branch_id', $branch_id)->where('is_active', 1)->pluck('dish_category_id');
        }
    }
    return $branch_category;
}
function getBranchMenuDetails($branch_id, $dish_id, $type, $count)
{
    $branch_dish  = "";
    if ($type == "web") {
        if ($count == "first") {
            $branch_dish = BranchMenu::where('dish_id', $dish_id)->where('is_active', 1)->where('branch_id', $branch_id)->first();
        } else {
            $branch_dish = BranchMenu::where('dish_id', $dish_id)->where('is_active', 1)->where('branch_id', $branch_id)->pluck('dish_id');
        }
    } else {
        if ($count == "first") {
            $branch_dish = BranchMenu::where('id', $dish_id)->where('is_active', 1)->where('branch_id', $branch_id)->first();
        } else {
            $branch_dish = BranchMenu::where('id', $dish_id)->where('is_active', 1)->where('branch_id', $branch_id)->pluck('dish_id');
        }
    }
    return $branch_dish;
}
function getBranchSizeDetails($branch_id, $size_id, $type, $count)
{
    $branch_size = "";
    if ($type == "web") {
        if ($count == "first") {
            $branch_size = BranchMenuSize::where('dish_size_id', $size_id)->where('is_active', 1)->where('branch_id', $branch_id)->first();
        } else {
            $branch_size = BranchMenuSize::where('dish_size_id', $size_id)->where('is_active', 1)->where('branch_id', $branch_id)->pluck('dish_size_id');
        }
    } else {
        if ($count == "first") {
            $branch_size = BranchMenuSize::where('id', $size_id)->where('is_active', 1)->where('branch_id', $branch_id)->first();
        } else {
            $branch_size = BranchMenuSize::where('id', $size_id)->where('is_active', 1)->where('branch_id', $branch_id)->pluck('dish_size_id');
        }
    }
    return $branch_size;
}
function getBranchAddonDetails($branch_id, $addon_id, $type, $count)
{
    $branch_addon = "";
    if ($type == "web") {
        if ($count == "first") {
            $branch_addon = BranchMenuAddon::where('dish_addon_id', $addon_id)->where('is_active', 1)->where('branch_id', $branch_id)->first();
        } else {
            $branch_addon = BranchMenuAddon::where('dish_addon_id', $addon_id)->where('is_active', 1)->where('branch_id', $branch_id)->pluck('dish_addon_id');
        }
    } else {
        if ($count == "first") {
            $branch_addon = BranchMenuAddon::where('id', $addon_id)->where('is_active', 1)->where('branch_id', $branch_id)->first();
        } else {
            $branch_addon = BranchMenuAddon::where('id', $addon_id)->where('is_active', 1)->where('branch_id', $branch_id)->pluck('dish_addon_id');
        }
    }
    return $branch_addon;
}
function cancelOrderReason($order_id, $reason, $reason_id = null, $item_id = null)
{
    $checkReason = false;
    if (auth('api')->check()) {
        $user = auth('api')->user();
        $user_flag = "client";
    } elseif (auth('employee')->check()) {
        $user = auth('employee')->user();
        $user_flag = $user->flag;
    } elseif (auth('admin')->check()) {
        $user = auth('admin')->user();
        $user_flag = "admin";
    } else {
        $user = null;
    }
    $addReason = new CancellationReason();
    $addReason->type = $user_flag;
    $addReason->order_id = $order_id;
    $addReason->order_details_id = $item_id ?? null;
    $addReason->reason_id = $reason_id ?? null;
    $addReason->reason = $reason ?? null;
    $addReason->user_id = $user->id;
    $addReason->created_by = $user->id;
    $addReason->save();
    if ($addReason) {
        $checkReason = true;
    }
    return $checkReason;
}
function convertPolicyToMethod($policy)
{
    if ($policy == "no_payment_required") {
        return "cash";
    } else if ($policy == "deposit_required") {
        return "deposit";
    } else  if ($policy == "full_payment_required") {
        return "credit";
    } else if ($policy == "credit") {
        return "credit";
    }
    return 'cash';
}
function getApprove($id, $model)
{
    $checkApprove = $model::find($id); // cleaner than where()->first()

    // If model not found, return false immediately
    if (!$checkApprove) {
        return false;
    }

    $authEmployee = auth('employee')->user();

    // Allow if employee owns the record
    if ($checkApprove->employee_id == $authEmployee->id) {
        return true;
    }

    // Allow if employee has privileged roles
    if (
        $authEmployee->hasRole('HR_Manager') ||
        $authEmployee->hasRole('LocalWork Admin') ||
        $authEmployee->hasRole('superAdmin')
    ) {
        return true;
    }

    // Otherwise deny
    return false;
}

function getEmployeesForNotify($branch_id, $time, $flag)
{
    $employees_ids = getWorkingEmployeesByBranchAndTime($branch_id, $time);
    if ($employees_ids['status']) {
        $employees = Employee::whereIn('id', $employees_ids['working_employee_ids'])->where('flag', $flag)->get();
    }
    return $employees ?? null;
}

function TablesWithState($status, $branch)
{
    return Table::where('status', $status)->where('branch_id', $branch)->get();
}
//Format float number to 2 decimal places
function formatFloat($value)
{
    if ($value === null || $value === '') {
        return null;
    }
    return round((float) $value, 2);
}
function Areas()
{
    return Area::whereIn(
        'id',
        BranchRegion::where('is_active', 1)
            ->pluck('region_id')
    )->get();
}

function runNotificationToEmployees($branch_id, $notifyData, $created_by, $order_id, $lang)
{
    $now = now();
    $order = Order::find($order_id);
    // Define roles to notify
    $roles = ['cashier'];
    // Only add waiter role if order is dine in
    $allowedNotifyTypes = ['table', 'invoice', 'order'];
    if (
        in_array($notifyData['notification_type'], $allowedNotifyTypes)
    ) {
        if (
            $notifyData['notification_type'] === 'order' && $order &&
            $order->order_type === 'dine_in'
        ) {
            $roles[] = 'waiter';
        } elseif (in_array($notifyData['notification_type'], ['table', 'invoice'])) {
            $roles[] = 'waiter';
        }
    }
    $employeesByRole = [];
    foreach ($roles as $role) {
        $employees = getEmployeesForNotify($branch_id, $now, $role);
        // if ($employees && (!is_iterable($employees) || count($employees) === 0)) {
        //     $errorKey = $role === 'cashier' ? 'recipes.nocashiersworknowinbranch' : 'recipes.nowaitersworknowinbranch';
        //     return respondError('Validation Error.', 404, ['error' => __($errorKey)]);
        // }
        $employeesByRole[$role] = $employees;
    }
    $employeeId = auth('employee')->check() ? auth('employee')->id() : null;
    if (!empty($employeesByRole)) {
        foreach ($employeesByRole as $role => $employees) {
            if (!empty($employees)) {

                foreach ($employees as $employee) {
                    // Skip the current authenticated employee if matched
                    if ($employeeId && $employee->id == $employeeId) {
                        continue;
                    }
                    addNotification(
                        $notifyData['notification_type'],
                        $role,
                        $notifyData['description_ar'],
                        $notifyData['description_en'],
                        $notifyData['title_ar'],
                        $notifyData['title_en'],
                        $employee->id,
                        $created_by,
                        $lang,
                        $order_id
                    );
                }
            }
        }
    }
    return RespondWithSuccessRequest($lang, 1);
}
function getAuthenticatedUser()
{
    $guards = ['admin', 'client', 'employee', 'web', 'api'];
    foreach ($guards as $guard) {
        if (auth($guard)->check()) {
            return auth($guard)->user();
        }
    }
    return null;
}

function authActionSave(): array
{
    $data = [];
    if (auth('employee')->check()) {
        $data['by'] = auth('employee')->id();
        $data['type'] = 'employee';
    } elseif (auth('admin')->check()) {
        $data['by'] = auth('admin')->id();
        $data['type'] = 'admin';
    }

    return $data;
}

function paginateOrGetAll($query, $request, $hiddenFields = [], $visibleFields = [])
{
    $perPage = $request->query('per_page');
    $page = $request->query('page', 1);
    if (is_null($perPage)) {
        $results = $query->get();

        if (!empty($hiddenFields)) {
            $results->makeHidden($hiddenFields);
        }
        if (!empty($visibleFields)) {
            $results->makeVisible($visibleFields);
        }

        return [
            'data' => $results,
            'meta' => [
                'totalItems'   => $results->count(),
                'itemsPerPage' => 'all',
                'totalPages'   => 1,
                'currentPage'  => 1
            ]
        ];
    } else {
        if (count($query->get()) > 0) {

            $paginated = $query->paginate($perPage, ['*'], 'page', $page);

            $collection = $paginated->getCollection();
            if (!empty($hiddenFields)) {
                $collection->makeHidden($hiddenFields);
            }
            if (!empty($visibleFields)) {
                $collection->makeVisible($visibleFields);
            }

            return [
                'data' => $collection, // return collection, not array
                'meta' => [
                    'totalItems'   => $paginated->total(),
                    'itemsPerPage' => $paginated->perPage(),
                    'totalPages'   => $paginated->lastPage(),
                    'currentPage'  => $paginated->currentPage()
                ]
            ];
        }
        return [
            'data' => [],
            'meta' => [
                'totalItems'   => 0,
                'itemsPerPage' => 'all',
                'totalPages'   => 1,
                'currentPage'  => 1
            ]
        ];
    }
}


// dalia code
function ResponseWithSuccess($lang, $data, $code = 200, $meta = [])
{
    $APICode = ApiCode($code);

    $response_array = [
        'status'  => true,
        'message' => $lang == 'ar'
            ? $APICode->api_code_message_ar
            : $APICode->api_code_message_en,
        'code'    => $code,
        'data'    => $data,
        'meta'    => $meta
    ];

    // Handle Resource Collections -> Convert to array
    // if ($data instanceof \Illuminate\Http\Resources\Json\JsonResource ||
    //     $data instanceof \Illuminate\Http\Resources\Json\AnonymousResourceCollection) {
    //     $data = $data->response()->getData(true);
    // }


    // // Merge $data into root response
    // $response_array = array_merge($response_array, $data);

    // Add meta if provided
    if (!empty($meta)) {
        $response_array['meta'] = $meta;
    }

    return Response::json($response_array, 200);
}
/////////////////////////////
function formatPrice($amount)
{
    // Check if number has decimals
    $decimal = $amount - floor($amount);

    if ($decimal == 0) {
        // If whole number (like 4200), just return as integer
        return (int)$amount;
    }

    // If decimals, round UP to nearest 0.10
    return ceil($amount * 10) / 10;
}

//function for log permission or role
function logPermissionsAndRoleChanges($action, $data = [])
{
    return  RolePermissionLog::create([
        'causer_id'   => auth('employee')->user()->id ?? auth('admin')->user()->id,
        'causer_type' => auth('employee')->user() ? auth('employee')->user()->flag : 'admin',
        'action'      => $action,
        'employee_id' => $data['employee_id'] ?? null,
        'role_id'     => $data['role_id'] ?? null,
        'permission_ids' => $data['permission_ids'] ?? null,
        'extra_data'  => $data['extra_data'] ?? null,
    ]);
}

function getAllEmployeesSupervised($id)
{
    $employees = Employee::where('supervisor_id', $id)->get();

    if ($employees->isEmpty()) {
        return null;
    }

    return $employees;
}

if (!function_exists('getSupervisedEmployees')) {
    function getSupervisedEmployees($id)
    {
        $allEmployees = collect();

        $directEmployees = collect(getAllEmployeesSupervised($id));

        if (count($directEmployees) == 0) {
            return $allEmployees;
        }

        foreach ($directEmployees as $employee) {
            $allEmployees->push($employee);

            $childEmployees = getSupervisedEmployees($employee->id);

            $allEmployees = $allEmployees->merge($childEmployees);
        }

        return $allEmployees->unique('id');
    }
}

if (!function_exists('generateRequestNumber')) {
    function generateRequestNumber()
    {
        $year = now()->year;
        $prefix = "LR-$year-";

        // Get the last number for this year
        $last = LeaveRequest::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->value('request_num');

        if ($last && preg_match('/LR-' . $year . '-(\d+)/', $last, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    if (!function_exists('getLocalizedData')) {
        function getLocalizedData($model, $lang = 'ar')
        {
            // For name field
            if ($lang == 'en' && !empty($model->name_en)) {
                $name = $model->name_en;
            } else {
                $name = $model->name_ar;
            }

            // For description field
            if ($lang == 'en' && !empty($model->description_en)) {
                $description = $model->description_en;
            } else {
                $description = $model->description_ar;
            }

            return [
                'id' => $model->id,
                'name' => $name ?? null,
                'description' => $description ?? null
            ];
        }
    }

    function storeProductTransaction($product_brand_id, $quantity, $unit_id, $barcode, $production_date, $expire_date, $type, $id, $model_name, $model_id)
    {

        $product_transaction = new ProductTransaction();
        $product_transaction->product_brand_id = $product_brand_id;
        $product_transaction->quantity = $quantity;
        $product_transaction->unit_id = $unit_id ?? null;
        $product_transaction->barcode = $barcode;
        $product_transaction->production_date = $production_date;
        $product_transaction->expiration_date = $expire_date;
        $product_transaction->type = $type ?? 'in';
        $product_transaction->created_by = $id;
        $product_transaction->date = today();
        $product_transaction->save();


        $product_transaction_log = new ProductTransactionsLog();
        $product_transaction_log->product_transaction_id = $product_transaction->id;
        $product_transaction_log->model_name = $model_name;
        $product_transaction_log->model_id = $model_id;
        $product_transaction->created_by = $id;

        $product_transaction_log->save();

        $lang = app()->getLocale();

        if ($type == 'out') {
            $lang = app()->getLocale();
            $settings = InventorySetting::first();
            $notificationRecipient = $settings->notification_recipient ?? 'inventory manager';

            // Prepare a fake request with product_brand_id
            $request = new Request(['product_brand_id' => $product_brand_id]);
            $request->headers->set('lang', $lang);

            // Call your existing controller method
            $response = app(PurchaseRequestController::class)->getSuggestion($request);
            $data = $response->getData(true);

            $product = ProductBrand::with('productStores', 'product', 'brand')->find($product_brand_id);
            $suggestion = $data['data']['suggestion'] ?? null;

            if ($suggestion) {
                $min_qty            = $suggestion['min_limit'];
                $current_qty        = $suggestion['current_quantity'];
                $product_brand_name = $suggestion['product_brand_name'];
                $suggestedQty       = $suggestion['suggestions_count'];

                $store_id = optional($product->productStores->first())->store_id;
                if ($store_id) {
                    $employees = InventoryEmployee::where('store_id', $store_id)->get();

                    foreach ($employees as $employee) {
                        if (
                            ($notificationRecipient == 'inventory manager' && $employee->hasRole('Inventory_Manager')) ||
                            ($notificationRecipient == 'warehouse_staff')
                        ) {
                            $title_ar = 'تنبيه انخفاض المخزون';
                            $title_en = 'Inventory Low Stock Alert';

                            $body_ar = "المنتج {$product_brand_name}  الكمية الحالية {$current_qty} أقل من الحد الأدنى المطلوب {$min_qty}. الكمية المقترحة لإعادة الطلب هي {$suggestedQty}.";
                            $body_en = "The product {$product_brand_name} current quantity {$current_qty} is below the minimum required limit of {$min_qty}. The suggested reorder quantity is {$suggestedQty}.";

                            if ($employee->device_token) {
                                send_push_notification(
                                    $employee->device_token,
                                    $body_ar,
                                    $body_en,
                                    $title_ar,
                                    $title_en,
                                    'inventory_alert',
                                    $employee->id,
                                    $employee->id,
                                    $employee->id,
                                    $lang,
                                    7
                                );
                            }
                        }
                    }
                }
            }
        }


        return  $product_transaction;
    }
    function getProductQuantity($product_id, $type = 'base', $barcode = null, $includeBarcodes = false, $expiry = false)
    {
        $productBrand = ProductBrand::with([
            'units',
            'transactions',
            'defaultUnit',
            'baseUnit'
        ])->find($product_id);

    if (!$productBrand) {
        return [
            'quantity' => 0,
            'default_unit' => ['id' => null, 'name' => null],
            'base_unit' => ['id' => null, 'name' => null],
            'barcodes' => []
        ];
    }
        // Determine the target unit based on type
        $targetUnitId = $type === 'default' ? $productBrand->default_unit_id : $productBrand->base_unit_id;

        $transactions = $productBrand->transactions;
        // If specific barcode requested
        if ($barcode) {
            $transactions = $transactions->where('barcode', $barcode);
            $quantity = calculateQuantityForTransactions($transactions, $productBrand, $targetUnitId);

            return $quantity;
        }

        // Otherwise calculate all barcodes + total
        $barcodes = $transactions->groupBy('barcode');
        $barcodeQuantities = [];
        $totalQuantity = 0;

        foreach ($barcodes as $barcodeValue => $barcodeTransactions) {
            $qty = calculateQuantityForTransactions($barcodeTransactions, $productBrand, $targetUnitId);

            $barcodeQuantities[] = [
                'barcode' => $barcodeValue,
                'quantity' => $qty,
                'expiry' => $expiry ? $barcodeTransactions->first()->expiration_date : null,

            ];
            $totalQuantity += $qty;
        }

        $response = [
            'quantity' => $totalQuantity,
            'default_unit' => [
                'id' => $productBrand->default_unit_id,
                'name' => $productBrand->defaultUnit->name ?? null
            ],
            'base_unit' => [
                'id' => $productBrand->base_unit_id,
                'name' => $productBrand->baseUnit->name ?? null
            ]
        ];

        if ($includeBarcodes) {
            $response['barcodes'] = $barcodeQuantities;
        }

        return $response;
    }
    function convertToBaseUnit($quantity, $unitId, $productBrand)
    {
        // If the unit is already the base unit → no conversion
        if ($unitId == $productBrand->base_unit_id) {
            return $quantity;
        }

        // Find the unit info
        $unit = $productBrand->units->firstWhere('id', $unitId);

        if (!$unit) {
            return $quantity;
        }

        return $quantity * $unit->factor;
    }

    function findBarcodesWithQuantity($productBrandId, $requestedQty, $requestedUnitId, $expiry = false)
    {
        $productBrand = ProductBrand::with([
            'units',
            'defaultUnit',
            'baseUnit',
            'transactions'
        ])->find($productBrandId);

        if (!$productBrand) {
            return ['error' => true, 'message' => 'Product brand not found'];
        }

        $requestedQtyBase = convertToBaseUnit(
            $requestedQty,
            $requestedUnitId,
            $productBrand
        );

        $grouped = $productBrand->transactions->groupBy('barcode');

        $matched = [];
        $today = date('Y-m-d');

        foreach ($grouped as $barcode => $transactionsForBarcode) {

            $barcodeQtyBase = calculateQuantityForTransactions(
                $transactionsForBarcode,
                $productBrand,
                $productBrand->base_unit_id
            );

            if ($barcodeQtyBase >= $requestedQtyBase) {

                $expiryDate = $expiry ? $transactionsForBarcode->first()->expiration_date : null;

                // Skip expired
                if ($expiry && $expiryDate < $today) {
                    continue;
                }

                $matched[] = [
                    'barcode' => $barcode,
                    'available_quantity' => $barcodeQtyBase,
                    'required_quantity' => $requestedQtyBase,
                    'expiration_date' => $expiryDate,
                    'production_date' => $expiry ? $transactionsForBarcode->first()->production_date : null,
                ];
            }
        }

        if (empty($matched)) {
            return [];
        }

        // Sort by nearest expiry
        usort($matched, function ($a, $b) {
            return strtotime($a['expiry']) <=> strtotime($b['expiry']);
        });

        return $matched[0];
    }


    /**
     * Helper to calculate quantity in the target unit for a set of transactions
     */
    function calculateQuantityForTransactions($transactions, $productBrand, $targetUnitId)
    {
        $quantity = 0;

        foreach ($transactions as $t) {
            $qtyInTarget = $t->quantity;

            if ($t->unit_id && $t->unit_id != $targetUnitId) {
                $factor = getConversionFactor($productBrand, $t->unit_id, $targetUnitId);
                $qtyInTarget = $t->quantity * $factor;
            }

            $quantity += $t->type === 'in' ? $qtyInTarget : -$qtyInTarget;
        }

        return $quantity;
    }

    /**
     * Recursive conversion factor finder between units
     */
    function getConversionFactor($productBrand, $fromUnitId, $toUnitId, $visited = [])
    {
        if ($fromUnitId == $toUnitId) return 1;
        $visited[] = $fromUnitId;

        $unit = $productBrand->units
            ->where('first_unit_id', $fromUnitId)
            ->first();

        if ($unit && !in_array($unit->second_unit_id, $visited)) {
            return $unit->factor * getConversionFactor($productBrand, $unit->second_unit_id, $toUnitId, $visited);
        }

        return 1;
    }

    function checkModuleStatus($moduleName)
    {
        try {
            $module = SystemModule::where('name', $moduleName)
                ->where('is_active', 1)
                ->first();

            return !is_null($module);
        } catch (\Exception $e) {
            return false;
        }
    }

    function applyDateFilter($query, $filter)
    {
        if (!$filter) {
            return $query;
        }

        switch ($filter) {
            case 'today':
                return $query->whereDate('created_at', now()->toDateString());

            case 'yesterday':
                return $query->whereDate('created_at', now()->subDay()->toDateString());

            case 'before_yesterday':
                return $query->whereDate('created_at', now()->subDays(2)->toDateString());

            case 'this_week':
                return $query->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);

            case 'this_month':
                return $query->whereBetween('created_at', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ]);

            default:
                return $query;
        }
    }
function getCurrentModuleDependOnRoute(Request $request, $table)
{
    // PROCUREMENT
    if ($request->is("api/procurement/$table") ||
        $request->is("api/procurement/$table/*")) {
        return 'procurement';
    }

    // INVENTORY
    if ($request->is("api/inventory/$table") ||
        $request->is("api/inventory/$table/*")) {
        return 'inventory';
    }

    // HR
    if ($request->is("api/hr/$table") ||
        $request->is("api/hr/$table/*")) {
        return 'hr';
    }

    return null;
}

    function checkModuleActivation($moduleId)
    {
        try {
            $module = SystemModule::where('id', $moduleId)
                ->where('is_active', 1)
                ->first();

            return !is_null($module);
        } catch (\Exception $e) {
            return false;
        }
    }


}

function formatDateTime($datetime, $lang = 'en', $type = 'datetime')
{
    if (!$datetime) {
        return null;
    }

    // Always parse from raw value
    $dt = $datetime instanceof Carbon
        ? $datetime
        : Carbon::parse($datetime);

    if ($type === 'date') {
        return $dt->format('Y-m-d');
    }

    if ($lang === 'ar') {
        return $dt->format('Y-m-d h:i') . ' ' .
            ($dt->format('A') === 'AM' ? 'صباحًا' : 'مساءً');
    }

    return $dt->format('Y-m-d h:i A');
}

function addJournalToFacility($facility)
{
    $check_facility = Journal::where('facility_id', $facility->id)->get();
    if (count($check_facility) == 0) {
        $data = [
            [
                'name_ar' => 'أصول',
                'name_en' => 'assets',
                'facility_id' => $facility->id,
                'type' => 'assets',
                'account_type' => 'debit',
                'currency_id' => $facility->currency_id,
                'level' => 1,
                'code' => 1,
                'is_active' => 1,
                'created_by' => $facility->created_by,
                'created_by_type' => $facility->created_by_type,
            ],
            [
                'name_ar' => 'خصوم',
                'name_en' => 'Liabilities',
                'facility_id' => $facility->id,
                'type' => 'liabilities',
                'account_type' => 'credit',
                'currency_id' => $facility->currency_id,
                'level' => 1,
                'code' => 2,
                'is_active' => 1,
                'created_by' => $facility->created_by,
                'created_by_type' => $facility->created_by_type,
            ],
            [
                'name_ar' => 'حقوق ملكية',
                'name_en' => 'Equity',
                'facility_id' => $facility->id,
                'type' => 'equity',
                'account_type' => 'credit',
                'currency_id' => $facility->currency_id,
                'level' => 1,
                'code' => 3,
                'is_active' => 1,
                'created_by' => $facility->created_by,
                'created_by_type' => $facility->created_by_type,
            ],
            [
                'name_ar' => 'ايرادات',
                'name_en' => 'Revenue',
                'facility_id' => $facility->id,
                'type' => 'revenue',
                'account_type' => 'credit',
                'currency_id' => $facility->currency_id,
                'level' => 1,
                'code' => 4,
                'is_active' => 1,
                'created_by' => $facility->created_by,
                'created_by_type' => $facility->created_by_type,

            ],
            [
                'name_ar' => 'مصروفات',
                'name_en' => 'Expense',
                'facility_id' => $facility->id,
                'type' => 'expense',
                'account_type' => 'debit',
                'currency_id' => $facility->currency_id,
                'level' => 1,
                'code' => 5,
                'is_active' => 1,
                'created_by' => $facility->created_by,
                'created_by_type' => $facility->created_by_type,

            ]
        ];
        Journal::insert($data);
    }
}

function addCurrencyExchangeToFacility($facility)
{
    $data = [
        'currency_id' => $facility->currency_id,
        'exchange_currency_id' => $facility->currency_id,
        'exchange_value' => 1,
        'date' => date('Y-m-d'),
        'facility_id' => $facility->id,
        'is_active' => 1,
        'created_by' => $facility->created_by,
        'created_by_type' => $facility->created_by_type,
    ];
    CurrencyExchange::insert($data);
}
