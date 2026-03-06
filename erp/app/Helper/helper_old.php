<?php

use App\Events\ChefNotify;
use App\Models\ActionBackLog;
use App\Models\AddonCategory;
use App\Models\APICode;
use App\Models\BiometricTransaction;
use App\Models\Branch;
use App\Models\BranchCoupon;
use App\Models\BranchMenu;
use App\Models\BranchMenuAddon;
use App\Models\BranchMenuAddonCategory;
use App\Models\BranchMenuCategory;
use App\Models\BranchMenuSize;
use App\Models\BranchSetting;
use App\Models\BranchTime;
use App\Models\CancellationReason;
use App\Models\CashierMachine;
use App\Models\ChatChannel;
use App\Models\ChefCuisineCategory;
use App\Models\ChifManagerLog;
use App\Models\ClientAddress;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Cuisine;
use App\Models\CuisineCategory;
use App\Models\DeliverySetting;
use App\Models\Discount;
use App\Models\Dish;
use App\Models\DishAddon;
use App\Models\DishCategory;
use App\Models\DishDetail;
use App\Models\DishSize;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\FinanceSetting;
use App\Models\ItemCode;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\OpeningBalance;
use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderTransaction;
use App\Models\PaymentPolicies;
use App\Models\pointSystem;
use App\Models\PurchaseInvoicesDetails;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\ShiftDetail;
use App\Models\TableReservation;
use App\Models\TableReservationLog;
use App\Models\VehicleSetting;
use App\Services\HR_Services\TimetableService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Pusher\Pusher;

function RespondWithSuccessRequest($lang, $code)
{


    //bad or invalid request missing some params
    $response = new stdClass();
    $APICode = APICode::where('code', $code)->first();
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


    $APICode = APICode::where('code', $code)->first();
    $response_array = array(
        'status' => false,
        // 'apiTitle' => $lang == 'ar' ? $APICode->api_code_title_ar : $APICode->api_code_title_en,
        'message' => $lang == 'ar' ? $APICode->api_code_message_ar : $APICode->api_code_message_en,
        'code' => 401
    );
    $response_code = 200;
    $response = Response::json($response_array, $response_code);
    return $response;
}

function RespondWithUnauthorizedRequest($lang, $code)
{
    $APICode = APICode::where('code', $code)->first();
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

function generateUUIDERecipt($json) {}

function CustomRespondWithBadRequest($message)
{


    $response_array = array(
        'status' => false,
        // 'apiTitle' => $lang == 'ar' ? $APICode->api_code_title_ar : $APICode->api_code_title_en,
        'message' => $message,
        'code' => 400
    );
    $response_code = 200;
    $response = Response::json($response_array, $response_code);
    return $response;
}

function RespondWithBadRequestWithData($data)
{

    $response_array = array(
        'status' => false,
        // 'apiTitle' => trans('validation.validator_title'),
        'message' => trans('validation.validator_msg'),
        'code' => 401,
        'data' => $data
    );
    $response_code = 200;
    $response = Response::json($response_array, $response_code);
    return $response;
}

function GetNextID($table, $type)
{
    $nextId = DB::table($table)->where('make_type', $type)->count() + 1;
    return $nextId;
}

function GetLastID($table)
{
    $nextId = DB::table($table)->max('id');
    return $nextId;
}

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

function BaseUrl()
{
    $myUrl = "";
    if (isset($_SERVER['HTTPS'])) $myUrl .= "https://";
    else $myUrl .= "http://";
    if ($_SERVER['SERVER_NAME'] == "erp.test/") return "http://erp.test/";
    return $myUrl . $_SERVER['SERVER_NAME'];
}

// function BaseUrl()
// {
//     $myUrl = "";

//     // Check if the connection is secure (HTTPS)
//     if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
//         $myUrl .= "https://";
//     } else {
//         $myUrl .= "http://";
//     }

//     // Return specific base URL for 'erp.test/'
//     if ($_SERVER['SERVER_NAME'] === "erp.test/") {
//         return "http://erp.test/";
//     }

//     // Default behavior for other server names
//     return $myUrl . $_SERVER['SERVER_NAME'];
// }

function AddDays($date, $daysNumber)
{
    $startDate = Carbon::parse($date);
    $daysNumber = $daysNumber;

    $next_date = $startDate->copy()->addDays($daysNumber);
    return $next_date->toDateString();
}

function formatTime($time)
{
    $to = Carbon::createFromFormat('H:i:s', $time)->format('h:i A');
    $toDay = str_replace(['AM', 'PM'], ['ص', 'م'], $to);
    return $toDay;
}

function convertToArabicNumerals($number)
{
    $westernArabicNumerals = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $easternArabicNumerals = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    return str_replace($westernArabicNumerals, $easternArabicNumerals, $number);
}

function ApiCode($code)
{
    $APICode = APICode::where('code', $code)->first();
    return $APICode;
}

function ResponseWithSuccessData($lang, $data, $code)
{
    $APICode = ApiCode($code);
    $response_array = array(
        'status' => true,
        // 'apiTitle' => $lang == 'ar' ? $APICode->api_code_title_ar : $APICode->api_code_title_en,
        'message' => $lang == 'ar' ? $APICode->api_code_message_ar : $APICode->api_code_message_en,
        'code' => 200,
        'data' => $data
    );
    $response_code = 200;
    $response = Response::json($response_array, $response_code);

    return $response;
}

function RespondWithBadRequestData($lang, $code)
{

    $APICode = ApiCode($code);
    $response_array = array(
        'status' => true,
        // 'apiTitle' => $lang == 'ar' ? $APICode->api_code_title_ar : $APICode->api_code_title_en,
        'message' => $lang == 'ar' ? $APICode->api_code_message_ar : $APICode->api_code_message_en,
        'code' => 400,
        'data' => null
    );
    $response_code = 200;
    $response = Response::json($response_array, $response_code);
    return $response;
}

function translateDataColumns($data, $lang, $translateColumns)
{
    $translatedData = $data;
    foreach ($translateColumns as $column) {
        // Determine the translated column name based on language
        $translatedColumn = $column . ($lang === 'ar' ? '_ar' : '_en');

        // Check if the translated column exists in the data
        if (array_key_exists($translatedColumn, $data)) {
            // Replace the original column with the translated one
            $translatedData[$column] = $data[$translatedColumn];
        } else {
            // Optionally handle missing translated columns (e.g., use a default value)
            $translatedData[$column] = null;
        }
    }

    return $translatedData;
}

function removeColumns($data, $columnsToRemove)
{
    return array_diff_key($data, array_flip($columnsToRemove));
}

function UploadFile($path, $image, $model, $request)
{
    $thumbnail = $request;
    $destinationPath = public_path($path); // Ensure this is the public directory path
    $filename = $model->id . time() . '.' . $thumbnail->getClientOriginalExtension();

    // Move the file to the destination directory
    $thumbnail->move($destinationPath, $filename);

    // Generate the asset path and remove the leading slash if exists
    $filePath = asset($path) . '/' . $filename;
    $filePath = url($filePath); // Remove the first slash if present

    // Save the file path to the model
    $model->$image = $filePath;

    // Save the model with the updated image path
    $model->save();
}

function UploadVideo($path, $fileAttribute, $model, $file)
{
    // Ensure the file is valid
    if (!$file->isValid()) {
        throw new \Exception('Invalid file uploaded.');
    }

    // Define the destination path
    $destinationPath = public_path($path); // Ensure this is the public directory path

    // Generate a unique filename
    $filename = $model->id . '_' . time() . '.' . $file->getClientOriginalExtension();

    // Move the file to the destination directory
    $file->move($destinationPath, $filename);

    // Generate the asset path
    $filePath = asset($path . '/' . $filename);

    // Save the file path to the model
    $model->$fileAttribute = $filePath;

    // Save the model with the updated file path
    $model->save();

    // return $filePath; // Return the file path for further use if needed
}

function GenerateCode($table, $table_id = 0)
{

    if ($table_id) {

        $table = DB::table($table)->where('id', $table_id)->first();
        $table_code = $table->code;
        $numberString = $table_code;

        $number = (int)$numberString;

        $number++;
        $code = sprintf('%04d', $number); // '0001'
        // $code += 1;
    } else {
        $code = '0000';
    }
    return $code;
}

function CheckToken()
{
    $lang = 'ar';
    $User = auth('api')->user();
    if (!$User) {
        return false;
    }
    $token = DB::table('oauth_access_tokens')
        ->select('expires_at') // Get the necessary fields
        ->orderBy('created_at', 'desc') // Order by creation date (ascending)
        ->where('user_id', $User->id) // Filter by the user's ID
        ->first();

    if ($token->expires_at < Carbon::now()->toDateTimeString()) {
        return false;
    }
    return true;
}

function CheckTokenEmployee()
{
    $employee = auth('employee')->user();
    //    dd(auth('employee')->user());
    if (!$employee) {
        return false;
    }
    $token = DB::table('oauth_access_tokens')
        ->select('expires_at') // Get the necessary fields
        ->orderBy('created_at', 'desc') // Order by creation date (ascending)
        ->where('name', 'employeeToken') // Order by creation date (ascending)
        ->where('user_id', $employee->id) // Filter by the user's ID
        ->first();

    if ($token->expires_at < Carbon::now()->toDateTimeString()) {
        return false;
    }
    return true;
}

if (!function_exists('DeleteFile')) {
    function DeleteFile($path, $filename)
    {
        $filePath = public_path($path . '/' . $filename);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }
}

//not used
function RespondWithBadRequestNoChange()
{

    $response_array = array(
        'status' => true,
        // 'apiTitle' => trans('validation.NoChange'),
        'message' => trans('validation.NoChangeMessage'),
        'code' => 401,
        'data' => []
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
        'data' => []
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
        'data' => []
    );
    $response_code = 200;
    $response = Response::json($response_array, $response_code);
    return $response;
}

//not used
function RespondWithBadRequestNotExist()
{

    $response_array = array(
        'status' => false,  // Set success to false to indicate an error
        // 'apiTitle' => trans('validation.NotExist'),
        'message' => trans('validation.NotExistMessage'),
        'code' => 401,
        'data' => []
    );

    // Change the response code to 404 for "Not Found"
    $response_code = 200;

    return Response::json($response_array, $response_code);
}

//not have Permeation
function RespondWithBadRequestNotHavePermeation()
{

    $response_array = array(
        'status' => false,  // Set success to false to indicate an error
        // 'apiTitle' => trans('validation.NotHavePermeation'),
        'message' => trans('validation.NotHavePermeationMessage'),
        'code' => 403,
        'data' => []
    );

    // Change the response code to 404 for "Not Found"
    $response_code = 200;

    return Response::json($response_array, $response_code);
}

//not Closing
function RespondWithBadRequestIsDefault()
{

    $response_array = array(
        'status' => false,  // Set success to false to indicate an error
        // 'apiTitle' => trans('validation.NotAvailable'),
        'message' => trans('validation.IsDefaultMessage'),
        'code' => 403,
        'data' => []
    );

    // Change the response code to 404 for "Not Found"
    $response_code = 200;

    return Response::json($response_array, $response_code);
}

//not date
function RespondWithBadRequestNotDate()
{

    $response_array = array(
        'status' => false,  // Set success to false to indicate an error
        // 'apiTitle' => trans('validation.NotDate'),
        'message' => trans('validation.NotDateMessage'),
        'code' => 400,
        'data' => []
    );

    // Change the response code to 404 for "Not Found"
    $response_code = 200;

    return Response::json($response_array, $response_code);
}

//not add
function RespondWithBadRequestNotAdd()
{

    $response_array = array(
        'status' => false,  // Set success to false to indicate an error
        // 'apiTitle' => trans('validation.NotAddMore'),
        'message' => trans('validation.NotAddMoreMessage'),
        'code' => 400,
        'data' => []
    );

    // Change the response code to 404 for "Not Found"
    $response_code = 200;

    return Response::json($response_array, $response_code);
}

//not available
function RespondWithBadRequestNotAvailable()
{

    $response_array = array(
        'status' => false,  // Set success to false to indicate an error
        // 'apiTitle' => trans('validation.NotAvailable'),
        'message' => trans('validation.NotAvailableMessage'),
        'code' => 404,
        'data' => []
    );

    // Change the response code to 404 for "Not Found"
    $response_code = 200;

    return Response::json($response_array, $response_code);
}

//not Closing
function RespondWithBadRequestNotClosing()
{

    $response_array = array(
        'status' => false,  // Set success to false to indicate an error
        // 'apiTitle' => trans('validation.NotAvailable'),
        'message' => trans('validation.NotClosingMessage'),
        'code' => 403,
        'data' => []
    );

    // Change the response code to 404 for "Not Found"
    $response_code = 200;

    return Response::json($response_array, $response_code);
}

function RespondWithBadRequestNoLeave()
{

    $response_array = array(
        'status' => false,  // Set success to false to indicate an error
        // 'apiTitle' => trans('validation.NoLeave'),
        'message' => trans('validation.NoLeaveMessage'),
        'code' => 400,
        'data' => []
    );

    // Change the response code to 404 for "Not Found"
    $response_code = 200;

    return Response::json($response_array, $response_code);
}


//not used
function RespondWithBadRequestDataExist()
{

    $response_array = array(
        'status' => false,  // Set success to false to indicate an error
        // 'apiTitle' => trans('validation.DataExist'),
        'message' => trans('validation.DataExistMessage'),
        'code' => 401,
        'data' => []
    );

    // Change the response code to 404 for "Not Found"
    $response_code = 200;

    return Response::json($response_array, $response_code);
}

function CheckExistColumnValue($table, $column, $value)
{
    return DB::table($table)
        ->where($column, $value)
        ->whereNull('deleted_at') // Ensures only active records are checked
        ->exists();
}


function CheckCouponValid($id, $amount)
{
    $coupon = Coupon::find($id);

    if (!$coupon) {
        return false; // Coupon doesn't exist
    }

    // Current date and time
    $currentDateTime = Carbon::now();

    // Convert start_date and end_date to Carbon instances
    $startDate = Carbon::parse($coupon->start_date);
    $endDate = Carbon::parse($coupon->end_date);

    // Validate conditions
    $isValidDate = $currentDateTime->between($startDate, $endDate);
    $isMinimumSpendMet = $coupon->minimum_spend <= $amount;
    $isActive = $coupon->is_active;
    // Check all conditions

    if ($isValidDate && $isMinimumSpendMet && $isActive == 1) {
        return true;
    }

    return false; // If any condition fails
}

function CheckUserCouponUsage($coupon_id, $client_id)
{
    $check = Order::where('coupon_id', $coupon_id)->where('client_id', $client_id)->exists();
    return $check;
}

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

function CheckDiscountValid()
{
    $discount = Discount::where('start_date', '<=', now())
        ->where('end_date', '>=', now())
        ->first();
    return $discount;
}

function getSetting($column)
{
    $setting = Setting::first();

    if ($setting) {
        return $setting->$column;
    }

    return null;  // or handle this as needed
}

function getBranchSettings($branchId, $column)
{
    $branch = Branch::find($branchId);
    if ($branch && isset($branch->$column)) {
        return $branch->$column;
    }

    return BranchSetting::where('branch_id', $branchId)->value($column);
}

function getBranchPolicyPayment($branchId, $column)
{
    $policies = PaymentPolicies::where('branch_id', $branchId)
        ->where('order_type', $column)
        ->first(); // Assuming one policy per branch/order_type
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

function calcCoupon($total_price, $coupon)
{
    if ($coupon->type == 'fixed') {
        return $coupon->value;
    } else {

        return ($total_price * ($coupon->value / 100));
    }
}

// table_reservation_deposit
function applyCoupon($total_price, $coupon)
{
    if ($coupon->type == 'fixed') {
        return $total_price - $coupon->value;
    } else {

        return $total_price - ($total_price * ($coupon->value / 100));
    }
}

function applyDiscount($total_price, $discount)
{
    if ($discount->type == 'fixed') {
        return $total_price - $discount->value;
    } else {
        return $total_price - ($total_price * ($discount->value / 100));
    }
    return $total_price;
}

function calcDiscount($total_price, $discount)
{
    if ($discount->type == 'fixed') {
        return $discount->value;
    } else {
        return ($total_price * ($discount->value / 100));
    }
}

// Function to apply tax
function applyTax($total_price, $tax_percentage, $tax_application)
{
    if ($tax_application) {
        return ($total_price / (($tax_percentage / 100) + 1));
    } else {

        return $total_price + ($total_price * ($tax_percentage / 100));
    }
}

function CalculateTax($tax_percentage, $amount)
{
    $tax = $amount - ($amount / (($tax_percentage / 100) + 1));
    // dd($tax,($tax_percentage / 100), $tax_percentage);
    return $tax;
}

function CheckUserType()
{
    $User = auth('api')->user();
    if ($User) {
        return $User->flag;
    }
    return '';
}

function getAuthenticatedGuard()
{
    $guards = ['employee', 'admin', 'web', 'api', 'client']; // employee first to prioritize

    foreach ($guards as $guard) {
        $user = Auth::guard($guard)->user();
        if ($user) {
            // Optional: confirm it's the correct model
            if ($guard === 'employee' && $user instanceof \App\Models\Employee) {
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

function isValid($branch_id)
{
    return pointSystem::where('branch_id', $branch_id)->exists();
}

function isActive($branch_id)
{
    return pointSystem::where('branch_id', $branch_id)->value('active') == 1;
}

// function calculateEarnPoint($total, $branch, $order_id, $user_id)
// {

//     //get system value earn
//     $value_percent = pointSystem::where('branch_id', $branch)->value('value_earn');

//     //get num of points of total of order
//     $points_num = $total * ($value_percent / 100);

//     $transactions = new pointTransaction();
//     $transactions->customer_id = $user_id;
//     $transactions->order_id = $order_id;
//     $transactions->type = 'earn';
//     $transactions->points = $points_num;
//     $transactions->transaction_date = now();
//     $transactions->created_by  = $user_id;
//     $transactions->save();

//     $point_user = ClientDetail::where('user_id', $user_id)->value('loyalty_points') + $points_num;
//     $point = ClientDetail::where('user_id', $user_id)->first();
//     $point->loyalty_points = $point_user;
//     $point->save();

//     return  $points_num;
// }

// function calculateRedeemPoint($total, $branch_id, $Order_id, $client_id)
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

function get_by_md5_id($id, $table)
{
    return DB::table($table)
        ->where(DB::raw('MD5(id)'), $id)
        ->first();
}

function einvoice_settings($key)
{
    $setting = DB::table('einvoice_settings')->where('key', $key)->value('value');

    return $setting ?? null;
}

function helper_update_by_id(array $data, $id, $table)
{
    // Update the specified table with the data, where the ID matches
    return DB::table($table)->where('id', $id)->update($data);
}


function getNearestBranch($userLat, $userLon)
{
    $nearestBranch = Branch::select('*')->where('is_active', 1) // Select all columns
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


    $employee_time = TimetableService::getTimetableForDate($employee_id, $date);
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

function CalculateDeficitOrder($open_amount = 0, $close_amount = 0, $real_amount = 0)
{
    $remaining_amount = ($close_amount - $open_amount);
    return ($remaining_amount - $real_amount);
}

function CalculateEmployeeLeave($employee_id, $leave_type, $leave_year_count)
{
    $first_day = date('Y-01-01');
    $last_day = date('Y-12-31');
    return $employee_leave_count = LeaveRequest::where('employee_id', $employee_id)->where('leave_type_id', $leave_type)->where('agreement', 2)->whereBetween('date', [$first_day, $last_day])->sum('leave_count');
}

function CheckExistOrder($client_id)
{
    $Order = Order::where('client_id', $client_id)->where('status', 'open')->fisrt();
    return $Order;
}

function CheckOrderPaid($order_id)
{

    return OrderTransaction::where('order_id', $order_id)->exists();
}

function CheckOrderPaidStatus($order_id)
{
    $transaction_status = 0;
    $check_transaction_status = OrderTransaction::where('order_id', $order_id)->first();
    if ($check_transaction_status) {
        $transaction_status = $check_transaction_status->payment_status === 'paid' ? 1 : 0;
    }
    return $transaction_status;
}

function GetCurrencyCodes()
{
    $Currencies = Country::select('currency_code', 'id')->get();
    return $Currencies;
}

function GetCountries()
{
    $countries = Country::get();
    return $countries;
}

function AddBranchesMenu($branch_ids, $dish_id)
{
    $add_dish_categories = AddDishCategories($branch_ids, $dish_id);
    $add_dishes = AddDishes($branch_ids, $dish_id);
    $add_addon_category = AddAddonCategories($branch_ids, $dish_id);
    $add_addons = AddAddons($branch_ids, $dish_id);
    $add_sizes = AddSizes($branch_ids, $dish_id);
}

function AddDishCategories($branch_ids, $dish_id)
{
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
                    ['is_active' => $get_dish_category->is_active, 'created_by' => auth('admin')->id()]
                );
            }
        }
    }
}

function AddDishes($branch_ids, $dish_id)
{
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
                        'modified_by' => auth('admin')->id(),
                    ]);
                } else {
                    // Create a new record with the price
                    BranchMenu::create([
                        'dish_id' => $get_dish->id,
                        'branch_id' => $branch_id,
                        'branch_menu_category_id' => $get_branch_menu_category->id,
                        'price' => $get_dish->price,
                        'is_product' => 0,
                        'is_active' => 1,
                        'created_by' => auth('admin')->id()
                    ]);
                }
            }
        }
    }
}

function AddAddonCategories($branch_ids, $dish_id)
{
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
                        'created_by' => auth('admin')->id()
                    ]);
                }
            }
        }
    }
}

function AddAddons($branch_ids, $dish_id)
{
    if ($dish_id != 0) {
        $get_dish = Dish::where('id', $dish_id)->with('dishAddonsDetails')->first();
        $addons = $get_dish->dishAddonsDetails->pluck('id');
        $get_addons = DishAddon::whereIn('id', $addons)->get();
    } else {
        $get_addons = DishAddon::get();
    }

    if ($get_addons->isNotEmpty()) {

        if ($dish_id != 0) {
            $addonDataIds = array_map(function ($addonData) {
                return $addonData['id'] ?? null;
            }, array_filter($get_addons->toArray(), function ($value) {
                return isset($value['id']) && $value['id'] !== null;
            }));

            BranchMenuAddon::whereNotIn('dish_addon_id', $addonDataIds)
                ->where('dish_id', $dish_id)
                ->delete();
        }

        foreach ($get_addons as $get_addon) {
            //$menu = BranchMenu::where('dish_id', $get_addon->dish_id)->first();
            foreach ($branch_ids as $branch_id) {
                $branch_menu_addon_category = BranchMenuAddonCategory::where('addon_category_id', $get_addon->addon_category_id)->where('branch_id', $branch_id)->first();
                // $branch_menu_category = BranchMenuAddon::updateOrCreate(
                //     ['dish_id' => $get_addon->dish_id, 'branch_id' => $branch_id, 'dish_addon_id' => $get_addon->id],
                //     [
                //         'branch_menu_addon_category_id' => $branch_menu_addon_category->id,
                //         'price' => $get_addon->price,
                //         'is_active' => 1,
                //         'created_by' => auth('admin')->id()
                //     ]
                // );

                $existingRecord = BranchMenuAddon::where([
                    'dish_id' => $get_addon->dish_id,
                    'branch_id' => $branch_id,
                    'dish_addon_id' => $get_addon->id
                ])->first();

                if (!$existingRecord) {
                    // Create a new record with the price
                    BranchMenuAddon::create([
                        'dish_id' => $get_addon->dish_id,
                        'branch_id' => $branch_id,
                        'dish_addon_id' => $get_addon->id,
                        'branch_menu_addon_category_id' => $branch_menu_addon_category->id,
                        'price' => $get_addon->price,
                        'is_active' => 1,
                        'created_by' => auth('admin')->id()
                    ]);
                }
            }
        }
    }
}

function AddSizes($branch_ids, $dish_id)
{
    if ($dish_id != 0) {
        $get_sizes = DishSize::where('dish_id', $dish_id)->get();
    } else {
        $get_sizes = DishSize::get();
    }

    if ($get_sizes->isNotEmpty()) {

        if ($dish_id != 0) {
            $sizeDataIds = array_map(function ($sizeData) {
                return $sizeData['id'] ?? null;
            }, array_filter($get_sizes->toArray(), function ($value) {
                return isset($value['id']) && $value['id'] !== null;
            }));

            BranchMenuSize::whereNotIn('dish_size_id', $sizeDataIds)
                ->where('dish_id', $dish_id)
                ->delete();
        }

        foreach ($get_sizes as $get_size) {
            //$menu = BranchMenu::where('dish_id', $get_size->dish_id)->first();
            foreach ($branch_ids as $branch_id) {
                // $branch_menu_category = BranchMenuSize::updateOrCreate(
                //     ['dish_id' => $get_size->dish_id, 'branch_id' => $branch_id, 'dish_size_id' => $get_size->id],
                //     [
                //         'price' => $get_size->price,
                //         'is_active' => 1,
                //         'created_by' => auth('admin')->id()
                //     ]
                // );

                $existingRecord = BranchMenuSize::where([
                    'dish_id' => $get_size->dish_id,
                    'branch_id' => $branch_id,
                    'dish_size_id' => $get_size->id,
                ])->first();

                if (!$existingRecord) {
                    // Create a new record with the price
                    BranchMenuSize::create([
                        'dish_id' => $get_size->dish_id,
                        'branch_id' => $branch_id,
                        'dish_size_id' => $get_size->id,
                        'price' => $get_size->price,
                        'is_active' => 1,
                        'created_by' => auth('admin')->id(),
                    ]);
                }
            }
        }
    }
}

function DeleteMenu($dish_id)
{
    $update_dish = BranchMenu::where('dish_id', $dish_id)->update(['deleted_by' => auth('admin')->id()]);
    $update_size = BranchMenuSize::where('dish_id', $dish_id)->update(['deleted_by' => auth('admin')->id()]);
    $update_addon = BranchMenuAddon::where('dish_id', $dish_id)->update(['deleted_by' => auth('admin')->id()]);
    $delete_dish = BranchMenu::where('dish_id', $dish_id)->delete();
    $delete_size = BranchMenuSize::where('dish_id', $dish_id)->delete();
    $delete_addon = BranchMenuAddon::where('dish_id', $dish_id)->delete();
}

function DeleteBranchMenu($branch_id)
{
    $update_dish = BranchMenu::where('branch_id', $branch_id)->update(['deleted_by' => auth('admin')->id()]);
    $update_size = BranchMenuSize::where('branch_id', $branch_id)->update(['deleted_by' => auth('admin')->id()]);
    $update_addon = BranchMenuAddon::where('branch_id', $branch_id)->update(['deleted_by' => auth('admin')->id()]);
    $delete_dish = BranchMenu::where('branch_id', $branch_id)->delete();
    $delete_size = BranchMenuSize::where('branch_id', $branch_id)->delete();
    $delete_addon = BranchMenuAddon::where('branch_id', $branch_id)->delete();
}

if (!function_exists('transformDishFields')) {
    function transformDishFields($dish)
    {
        $dish->is_active = (bool)$dish->is_active;
        $dish->has_sizes = (bool)$dish->has_sizes;
        $dish->has_addon = (bool)$dish->has_addon;
        return $dish;
    }
}


function getDefaultBranch()
{
    $defaultBranch = Branch::where('is_default', 1)->first();
    if ($defaultBranch) {
        $defaultBranch->makeHidden(['name_site', 'address_site']);
        return $defaultBranch->id;
    }
    return null;
}

function getBranchInfo($id)
{
    return $defaultBranch = Branch::where('id', $id)->first()->name_site;
}


function respondError($error, $code, $errorMessages = [])
{
    if ($code == 404) {
        $code1 = 404;
    } elseif ($code == 500) {
        $code1 = 500;
    } else {
        $code1 = 200;
    }
    $response = [
        'code' => $code,
        'status' => false,
        'message' => $error,
        'data' => null,
        'errorData' => null,
        'validation_type' => true,
    ];

    if (!empty($errorMessages)) {
        $response['errorData'] = $errorMessages;
    }
    return response()->json($response, $code1);
}


function respondErrorData($error, $code, $errorMessages = [])
{
    if ($code == 404) {
        $code1 = 404;
    } elseif ($code == 500) {
        $code1 = 500;
    } else {
        $code1 = 200;
    }
    $response = [
        'code' => $code,
        'status' => false,
        'message' => $error,
        'data' => null,
        'errorData' => null,
        'validation_type' => false,
    ];

    if (!empty($errorMessages)) {
        $response['errorData']['error'] = $errorMessages;
    }

    return response()->json($response, $code1);
}

function respondEmptyrData($error, $code, $errorMessages = [])
{
    if ($code == 404) {
        $code1 = 404;
    } elseif ($code == 500) {
        $code1 = 500;
    } else {
        $code1 = 200;
    }
    $response = [
        'code' => $code,
        'status' => true,
        'message' => $error,
        'data' => null,
        'errorData' => null
    ];

    return response()->json($response, $code1);
}

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

//function checkDishExistMostOrderd($IDBranch, $id)
//{
//    $Dishes =  BranchMenu::select('dishes.*')
//        ->leftJoin('dishes', 'dishes.id', 'branch_menus.dish_id')
//        ->join('order_details', 'order_details.dish_id', '=', 'dishes.id')
//        ->groupBy('dishes.id', 'dishes.name_ar')
//        ->where('branch_id', $IDBranch)
//        ->where('branch_menus.is_active', 1)
//        ->selectRaw('SUM(order_details.quantity) as total_quantity')
//        // ->selectRaw('countries.currency_symbol as currency_symbol') // Select the currency symbol
//        ->orderByDesc('total_quantity')
//        ->limit(5)
//        ->pluck('id')->toArray();
//    if (in_array($id, $Dishes)) {
//        return true;
//    }
//    return false;
//}

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

function checkOfferUsed($id)
{
    return OrderDetail::where('offer_id', $id)->exists();
}

function getAddressFromLatLong($latitude, $longitude)
{
    $apiKey = env('GOOGLE_MAPS_API_KEY');
    $url = "https://maps.googleapis.com/maps/api/geocode/json";

    $response = Http::get($url, [
        'latlng' => "{$latitude},{$longitude}",
        'key' => $apiKey,
    ]);

    if ($response->successful() && isset($response['results'][0])) {
        $result = $response['results'][0];
        return [
            'formatted_address' => $result['formatted_address'] ?? null,
            'city' => collect($result['address_components'])->firstWhere('types', 'locality')['long_name'] ?? null,
            'state' => collect($result['address_components'])->firstWhere('types', 'administrative_area_level_1')['long_name'] ?? null,
            'country' => collect($result['address_components'])->firstWhere('types', 'country')['long_name'] ?? null,
            'postal_code' => collect($result['address_components'])->firstWhere('types', 'postal_code')['long_name'] ?? null,
        ];
    }

    return null;
}

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

function weekDay($day, $lang)
{
    switch ($day) {
        case 0:
            if ($lang == "en") {
                return "Sunday";
            } else {
                return "الاحد";
            }
            break;

        case 1:
            if ($lang == "en") {
                return "Monday";
            } else {
                return "الاثنين";
            }
            break;

        case 2:
            if ($lang == "en") {
                return "Tuesday";
            } else {
                return "الثلاثاء";
            }
            break;

        case 3:
            if ($lang == "en") {
                return "Wednesday";
            } else {
                return "الاربعاء";
            }
            break;

        case 4:
            if ($lang == "en") {
                return "Thursday";
            } else {
                return "الخميس";
            }
            break;

        case 5:
            if ($lang == "en") {
                return "Thursday";
            } else {
                return "الخميس";
            }
            break;


        default:
            if ($lang == "en") {
                return "Saturday";
            } else {
                return "السبت";
            }
            break;
    }
}

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

function minAddons($dish_id, $dish_category_id)
{
    $min_addons = 0;
    $check_max = DishAddon::where('addon_category_id', $dish_category_id)->where('dish_id', $dish_id)->first();
    if ($check_max) {
        $min_addons = $check_max->min_addons;
    }
    return $min_addons;
}

function maxAddons($dish_id, $dish_category_id)
{
    $max_addons = 0;
    $check_max = DishAddon::where('addon_category_id', $dish_category_id)->where('dish_id', $dish_id)->first();
    if ($check_max) {
        $max_addons = $check_max->max_addons;
    }
    return $max_addons;
}

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

function getEmployeeID()
{
    $user = auth('admin')->user();
    if ($user) {
        return Employee::where('user_id', $user->id)->value('id');
    }
    return null;
}

function getBranchManagerID()
{
    $user = auth('admin')->user();
    if ($user) {
        $employee_id = Employee::where('user_id', $user->id)->value('id');

        return Branch::where('employee_id', $employee_id)->value('id');
    }
    return null;
}


function serializeDocument($documentStructure)
{
    Log::debug('Starting serialization', ['documentStructure' => $documentStructure]);

    if (is_scalar($documentStructure) || is_null($documentStructure)) {
        // Prevent wrapping UUIDs in extra quotes
        if (preg_match('/^[a-f0-9]{64}$/i', $documentStructure)) {
            Log::debug('UUID detected, keeping as is', ['uuid' => $documentStructure]);
            return $documentStructure;  // Do not add quotes for hash-based UUIDs
        }

        $serializedValue = '"' . trim((string)$documentStructure) . '"';
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


function isAssocArray($array)
{
    $isAssoc = array_keys($array) !== range(0, count($array) - 1);
    Log::debug('Checking if array is associative', ['array' => $array, 'isAssoc' => $isAssoc]);
    return $isAssoc;
}

function formatToUUIDv4($hash)
{
    return substr($hash, 0, 8) . '-' .
        substr($hash, 8, 4) . '-' .
        substr($hash, 12, 4) . '-' .
        substr($hash, 16, 4) . '-' .
        substr($hash, 20, 12);
}

function generateReceiptUUID($receiptData)
{
    $normalizedText = serializeDocument($receiptData);
    //$hashBytes = hash('sha256', $normalizedText);
    $hashBytes = formatToUUIDv4($normalizedText);
    $hexUuid = bin2hex($hashBytes);

    return formatToUUIDv4($hexUuid);
}

function employees()
{
    return Employee::where('flag', 'officer')->get();
}

function chefs()
{
    return Employee::where('flag', 'Head chef')->get();
}

function shifts()
{
    return Shift::get();
}

function drivers()
{
    return Employee::where('flag', 'driver')->get();
}

function branches()
{
    return Branch::where('is_active', 1)->whereNull('deleted_at')->get();
}

function getPOS()
{
    return CashierMachine::whereNull('deleted_at')->get();
}

function vehicles()
{
    return VehicleSetting::all();
}

function cuisines()
{
    return Cuisine::all();
}

function getNotifications($id)
{
    return Notification::where('status', 0)->where('user_id', $id)->where('type', 'admin')->get();
}

function pusherConfigration()
{
    return new Pusher(
        config('broadcasting.connections.pusher.key'),
        config('broadcasting.connections.pusher.secret'),
        config('broadcasting.connections.pusher.app_id'),
        [
            'cluster' => config('broadcasting.connections.pusher.options.cluster'),
            'useTLS' => true,
        ]
    );
}

//smart route with example

//this function should use google map key
function getSmartDeliveryRoute($branchLat, $branchLng, $deliveryPoints)
{
    $apiKey = env('GOOGLE_API_KEY'); // Google Maps API key
    $baseUrl = "https://maps.googleapis.com/maps/api/directions/json";

    Log::info("Branch Location: Lat: $branchLat, Lng: $branchLng");
    Log::info("Received Delivery Points: ", $deliveryPoints);

    // Prepare waypoints (excluding branch, which is the start)
    $waypoints = implode('|', array_map(function ($point) {
        return "{$point['lat']},{$point['lng']}";
    }, $deliveryPoints));

    Log::info("Formatted Waypoints for API: $waypoints");

    // Google Directions API request with optimized waypoints
    $response = Http::get($baseUrl, [
        'origin' => "$branchLat,$branchLng",
        'destination' => "$branchLat,$branchLng", // Round trip
        'waypoints' => "optimize:true|$waypoints",
        'key' => $apiKey,
    ]);

    $data = $response->json();

    Log::info("Google API Response: ", $data);

    if ($data['status'] !== 'OK') {
        Log::error("Google API Error: " . $data['status']);
        return ['error' => 'Failed to fetch optimized route'];
    }

    // Extract optimized order
    $optimizedOrder = $data['routes'][0]['waypoint_order'];

    Log::info("Optimized Order from Google: ", $optimizedOrder);

    // Reorder delivery points based on optimization
    $optimizedRoute = array_map(function ($index) use ($deliveryPoints) {
        return $deliveryPoints[$index];
    }, $optimizedOrder);

    Log::info("Final Optimized Route: ", $optimizedRoute);

    $googleMapLink = "https://www.google.com/maps/dir/$branchLat,$branchLng/" . implode('/', array_map(function ($point) {
        return "{$point['lat']},{$point['lng']}";
    }, $optimizedRoute));

    Log::info("Generated Google Maps Link: $googleMapLink");

    return [
        'optimized_route' => $optimizedRoute,
        'google_map_link' => $googleMapLink
    ];
}


// Example getSmartDeliveryRoute
// $branch = ['lat' => 29.3759, 'lng' => 47.9774]; // Branch coordinates
// $deliveryPoints = [
//     ['order_id' => 101, 'lat' => 29.3785, 'lng' => 47.9904],  // Order 101
//     ['order_id' => 102, 'lat' => 29.3400, 'lng' => 47.9200],  // Order 102
//     ['order_id' => 103, 'lat' => 29.4000, 'lng' => 47.9500]   // Order 103
// ];

// $result = getSmartDeliveryRoute($branch['lat'], $branch['lng'], $deliveryPoints);
// dd($result) or log the result;
//expected result
// [
//     "optimized_route" => [
//         ["order_id" => 102, "lat" => 29.3400, "lng" => 47.9200], // Order 102 (first stop)
//         ["order_id" => 103, "lat" => 29.4000, "lng" => 47.9500], // Order 103 (second stop)
//         ["order_id" => 101, "lat" => 29.3785, "lng" => 47.9904]  // Order 101 (third stop)
//     ],
//     "google_map_link" => "https://www.google.com/maps/dir/29.3759,47.9774/29.3400,47.9200/29.4000,47.9500/29.3785,47.9904"
// ]

//smart route without google map key
// function getSmartDeliveryRoute($branchLat, $branchLng, $deliveryPoints)
// {
//     if (empty($deliveryPoints)) {
//         return ['error' => 'No delivery points provided'];
//     }

//     Log::info("Branch Location: Lat: $branchLat, Lng: $branchLng");
//     Log::info("Received Delivery Points: ", $deliveryPoints);

//     // Start from the branch location
//     $currentLocation = ['lat' => $branchLat, 'lng' => $branchLng];

//     // List of unvisited delivery points
//     $unvisited = $deliveryPoints;

//     // Optimized route storage
//     $optimizedRoute = [];

//     while (!empty($unvisited)) {
//         // Find the nearest delivery point
//         $nearestIndex = null;
//         $nearestDistance = PHP_FLOAT_MAX;

//         foreach ($unvisited as $index => $point) {
//             $distance = calculateDistance($currentLocation['lat'], $currentLocation['lng'], $point['lat'], $point['lng']);

//             if ($distance < $nearestDistance) {
//                 $nearestDistance = $distance;
//                 $nearestIndex = $index;
//             }
//         }

//         // Move to the nearest point
//         if ($nearestIndex !== null) {
//             $optimizedRoute[] = $unvisited[$nearestIndex]; // Add to optimized route
//             $currentLocation = $unvisited[$nearestIndex]; // Update current location
//             unset($unvisited[$nearestIndex]); // Remove visited point
//         }
//     }

//     Log::info("Final Optimized Route: ", $optimizedRoute);

//     return [
//         'optimized_route' => array_values($optimizedRoute), // Reset array keys
//         'message' => 'Optimized route calculated without Google Maps API'
//     ];
// }

/**
 * Calculate the distance between two latitude-longitude points using Haversine formula
 */
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


//function to get the invoices that will be uploaded to the portal
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
                    $total = $branch_dish_size->price;
                } else {
                    $total = $Branch_Dish->price;
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
    $service_fees_value = getBranchSettings($order->branch_id, 'service_fees');
    $service_fees_type = getBranchSettings($order->branch_id, 'service_fees_type');
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
                    $total = $branch_dish_size->price;
                } else {
                    $total = $Branch_Dish->price;
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
            $service_value = $service_fees_type != 'fixed' ? ($price_before_tax * ($service_fees_value / 100)) : ($service_fees_value / count($DataOrderDetails));
            $priceAfterService = $price_before_tax + $service_value;
            $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage, $priceAfterService) : $priceAfterService * ($tax_percentage / 100);
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
                                $service_value = $service_fees_type != 'fixed' ? ($price_before_tax * ($service_fees_value / 100)) : 0;
                                $priceAfterService = $price_before_tax + $service_value;
                                $tax_value = $tax_application == 1 ? CalculateTax($tax_percentage, $priceAfterService) : $priceAfterService * ($tax_percentage / 100);
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
// function getEmployeesWithSameShift($dateTime)
// {
//     try {
//         $dateTime = Carbon::parse($dateTime);
//         $date = $dateTime->toDateString(); // e.g., "2025-05-26"
//         $dayIndex = $dateTime->dayOfWeek; // 0 (Sun) to 6 (Sat)
//         // Step 1: Get all employees scheduled to work on this date
//         $schedules = EmployeeSchedule::where('start_date', '<=', $date)
//             ->where('end_date', '>=', $date)
//             ->get();
//         if ($schedules->isEmpty()) {
//             return ['status' => false, 'message' => 'No scheduled employees found for the given date.'];
//         }
//         $workingEmployees = [];
//         foreach ($schedules as $schedule) {
//             $shiftDetail = ShiftDetail::where('shift_id', $schedule->shift_id)
//                 ->where('day_index', $dayIndex)
//                 ->with('timetable')
//                 ->first();
//             if ($shiftDetail && $shiftDetail->timetable) {
//                 $timetable = $shiftDetail->timetable;
//                 $start = Carbon::parse($timetable->on_duty_time);
//                 $end = Carbon::parse($timetable->off_duty_time);
//                 // Handle overnight shifts
//                 if ($timetable->cross_day) {
//                     $end->addDay();
//                 }
//                 if ($dateTime->between($start, $end)) {
//                     $workingEmployees[] = $schedule->employee_id;
//                 }
//             }
//         }
//         if (empty($workingEmployees)) {
//             return ['status' => false, 'message' => 'No employees are currently working at this time.'];
//         }
//         return [
//             'status' => true,
//             'working_employee_ids' => array_values(array_unique($workingEmployees)),
//         ];
//     } catch (\Exception $e) {
//         Log::error("Error finding employees working at $dateTime: " . $e->getMessage());
//         return ['status' => false, 'message' => 'Error retrieving working employees.'];
//     }
// }
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

function addNotification($type, $description_ar, $description_en, $title_ar, $title_en, $user_id, $created_by, $lang, $order, $url = null)
{
    $notify = new Notification();
    $notify->type = $type;
    $notify->description_ar = $description_ar;
    $notify->description_en = $description_en;
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
        'orderId' => $order,
        'title' => $title,
        'description' => $description,
        'url' => $url,
    ];
    return $result;
}

function sendManagerNotification($type, $description_ar, $description_en, $title_ar, $title_en, $user_id, $created_by, $lang, $complaint, $url = null)
{
    $notify = new Notification();
    $notify->type = $type;
    $notify->description_ar = $description_ar;
    $notify->description_en = $description_en;
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

//
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
    return ChifManagerLog::whereIn('employee_id', $headChefIds)
        ->where('branch_id', $branchId)
        ->where('date', date('Y-m-d'))
        ->get();
}

function getKitchenDishes($orderDetails, $employeeChefs)
{
    $kitchenDishes = [];
    foreach ($orderDetails as $orderDetail) {
        $branchMenuId = getBranchMenu($orderDetail->dish_id, $orderDetail->order->branch_id);
        if ($orderDetail->status == 'cancel' || $branchMenuId == 0) {
            continue;
        }

        $assignedEmployees = [];

        foreach ($employeeChefs as $chef) {
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

                        //break 2;
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
    $kitchenDishes = getKitchenDishes($order->orderDetails, $employeeChefs);
    return $kitchenDishes;
}

function splitDishesOnAllOrders($order_id, $employee_id)
{
    $employeeChefs = ChifManagerLog::where('employee_id', $employee_id)
        ->where('date', date('Y-m-d'))
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
        $dishes = getKitchenDishes($order->orderDetails, $employeeChefs);
        $kitchenDishes = array_merge($kitchenDishes, $dishes);
    }

    return $kitchenDishes;
}

function getEmployeeCuisine($employeeId)
{
    $cuisineCategories = [];
    $dishCategories = [];
    //$checkEmployee = Employee::where('id', $employeeId)->exists() ? $employeeId : 0;
    $checkEmployee = Employee::where('id', $employeeId)->first();
    if (!$checkEmployee) {
        return ['status' => false, 'message' => 'Error no employee found.'];
    }

    $checkChefCuisineCategoryId = ChefCuisineCategory::where('employee_id', $employeeId)->pluck('cuisine_category_id');
    if ($checkChefCuisineCategoryId->isEmpty()) {
        return [];
    }

    $checkAllCuisineCategories = CuisineCategory::whereIn('id', $checkChefCuisineCategoryId)->get();
    if ($checkAllCuisineCategories->isEmpty()) {
        return [];
    }

    $cuisineCategoriesGrouped = $checkAllCuisineCategories->groupBy('cuisine_id');

    foreach ($cuisineCategoriesGrouped as $cuisineId => $allDishCategories) {

        $cuisine = Cuisine::where('id', $cuisineId)->first();

        $dishCategoriesId = $allDishCategories->pluck('dish_category_id');

        if ($dishCategoriesId->isEmpty()) {
            $dishCategories = [];
        } else {
            //$dishCategoriesDetails = DishCategory::whereIn('id', $dishCategoriesId)->with('dishes')->get();
            $dishCategoriesDetails = BranchMenuCategory::where('branch_id', $checkEmployee->branch_id)->whereIn('dish_category_id', $dishCategoriesId)->with('branchMenus')->get();
            if ($dishCategoriesDetails->isEmpty()) {
                $dishCategoriesDetails = [];
            }
        }

        $cuisineCategories[] = [
            'cuisine' => $cuisine,
            'dishCategories' => $dishCategoriesDetails
        ];

        $dishCategories = [];
    }

    return $cuisineCategories;
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

function sendToKitchen($order_id)
{
    Log::info("Starting sendToKitchen for order $order_id");

    // Fetch dishes split by employees
    $chef_dishes = splitDishesOnOrder($order_id);
    Log::info("Fetched dishes:", ['chef_dishes' => $chef_dishes]);

    // Track the maximum preparation time across all dishes
    $maxPreparationTime = 0;

    // Step 1: Calculate the maximum preparation time for all dishes
    foreach ($chef_dishes as $item) {
        foreach ($item['dishes'] as $dish) {
            $dish['preparation_time'] = Dish::where('id', $dish['dish_id'])->value('time') ?? 0;
            if ($dish['preparation_time'] > $maxPreparationTime) {
                $maxPreparationTime = $dish['preparation_time'];
            }
        }
    }

    // Step 2: Process dishes with relative delays
    foreach ($chef_dishes as $item) {
        Log::info("Processing ID: " . $item['id']);

        foreach ($item['dishes'] as $dish) {
            Log::info("Dish Details", [
                'Dish ID' => $dish->id,
                'Status' => $dish->status,
                'Quantity' => $dish->quantity,
                'Total' => $dish->total,
                'Dish ID Reference' => $dish->dish_id
            ]);

            // Fetch preparation time for the dish
            $dish['preparation_time'] = Dish::where('id', $dish['dish_id'])->value('time') ?? 0;

            // Fetch dish_order from OrderDetail
            $dish['order'] = OrderDetail::where('dish_id', $dish['dish_id'])
                ->where('order_id', $order_id)
                ->value('dish_order') ?? -1;

            Log::info("Dishes with preparation time and order:", ['chef_dishes' => $chef_dishes]);

            // Step 3: Separate dishes into unordered (-1) and ordered (0,1,2)
            $unorderedDishes = [];
            $orderedDishes = [];
            $addedDishIds = []; // Track dish IDs to avoid duplicates

            if ($dish['order'] == -1) {
                // Check if the dish has already been added
                if (!in_array($dish['id'], $addedDishIds)) {
                    $unorderedDishes[] = $dish; // Add each dish only once
                    $addedDishIds[] = $dish['id']; // Track the dish ID
                }
            } else {
                $orderedDishes[$dish['order']][] = $dish;
            }

            Log::info("Unordered dishes:", ['unorderedDishes' => $unorderedDishes]);
            Log::info("Ordered dishes:", ['orderedDishes' => $orderedDishes]);

            // Step 4: Handle Unordered Dishes (-1) -> Serve together
            if (!empty($unorderedDishes)) {
                // Group unordered dishes by chef_id
                $unorderedDishesByChef = [];
                foreach ($unorderedDishes as $dish) {
                    $unorderedDishesByChef[$dish['chef_id']][] = $dish;
                }

                // Process dishes for each chef
                foreach ($unorderedDishesByChef as $dishes) {
                    $maxTime = max(array_column($dishes, 'preparation_time')); // Max time for this chef's dishes

                    foreach ($dishes as $dish) {
                        $delay = $maxPreparationTime - $dish['preparation_time']; // Relative delay based on global max time
                        $chef = Employee::find($item['id']);

                        if ($chef) {
                            Log::info("Sending unordered dish {$dish['dish_id']} to chef {$chef->id} with delay {$delay} minutes");

                            dispatch(function () use ($chef, $dish) {
                                broadcast(new ChefNotify($chef, [$dish]));
                            })->delay(now()->addMinutes($delay));
                        }
                    }
                }
            }
        }
        // Step 4: Handle Ordered Dishes (0 -> 1 -> 2)
        if (!empty($orderedDishes)) {
            ksort($orderedDishes); // Sort orders

            foreach ($orderedDishes as $order => $dishes) {
                // Group ordered dishes by chef_id
                $orderedDishesByChef = [];
                foreach ($dishes as $dish) {
                    $orderedDishesByChef[$dish['chef_id']][] = $dish;
                }

                // Process dishes for each chef
                foreach ($orderedDishesByChef as $chef_id => $chefDishes) {
                    $maxTime = max(array_column($chefDishes, 'preparation_time')); // Max time for this chef's dishes

                    foreach ($chefDishes as $dish) {
                        $delay = $maxTime - $dish['preparation_time']; // Ensure they finish together
                        $chef = Employee::find($chef_id);

                        if ($chef) {
                            Log::info("Sending dish {$dish['dish_id']} (order $order) to chef {$chef->id} with delay {$delay} minutes");

                            dispatch(function () use ($chef, $dish) {
                                broadcast(new ChefNotify($chef, [$dish]));
                            })->delay(now()->addMinutes($delay));
                        }
                    }
                }
            }
        }
    }

    Log::info("Finished sendToKitchen for order $order_id");
}


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
            'cross_day' => (bool)$cross,
        ];
    }

    Log::info("Grouped Branch Working Hours", ['result' => $result]);
    return $result;
}

function formatTo12Hour($time)
{
    try {
        $carbonTime = \Carbon\Carbon::createFromFormat('H:i:s', $time);

        if (app()->getLocale() === 'ar') {
            $period = ($carbonTime->hour < 12) ? 'ص' : 'م';
            return $carbonTime->format('h:i') . ' ' . $period;
        } else {
            return $carbonTime->format('h:i A');
        }
    } catch (\Exception $e) {
        try {
            $carbonTime = \Carbon\Carbon::parse($time);

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
            $period = 'صباحاً'; // Morning
        } else {
            $period = 'مساءً'; // Evening
        }

        return $timePart . ' ' . $period;
    } else {
        // English format: 10:00 am or 11:00 pm
        return date('h:i a', $timestamp);
    }
}

function formatTimeRangeForLocale($startTime, $endTime)
{
    $formattedStart = formatTimeForLocale($startTime);
    $formattedEnd = formatTimeForLocale($endTime, true);

    return $formattedStart . ' - ' . $formattedEnd;
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

function createChannel($sender, $senderType, $receiver, $receiverType, $chatWith)
{
    // Find or create chat channel
    $channel = ChatChannel::firstOrCreate(
        [
            'initiator_id' => $sender->id,
            'initiator_type' => $senderType,
            'participant_id' => $receiver->id,
            'participant_type' => $receiverType,
            'chat_with' => $chatWith,
        ],
        [
            'status' => 'pending',
            'started_at' => now(),
        ]
    );
    return $channel;
}

function checkChatChannel($order)
{
    $contact = [];
    $user = null;
    $userType = null;

    if (auth('api')->check()) {
        $user = auth('api')->user();
        $userType = 'client';
    } elseif (auth('client')->check()) {
        $user = auth('client')->user();
        $userType = 'client';
    } elseif (auth('employee')->check()) {
        $user = auth('employee')->user();
        $userType = 'employee';
    } elseif (auth('admin')->check()) {
        $user = auth('admin')->user();
        $userType = 'employee';
    }
    if (is_object($order) && isset($order->id)) {
        $order = Order::where('id', $order->id)->first();
    } else {
        $order = Order::where('id', $order)->first();
    }
    if (!$order) {
        return $contact;
    }
    if ($order->type = 'delivery' && $order->tracking->last()->order_status == 'on_way') {
        $clientId = $order->client_id;
        $deliveryId = $order->delivery_id ?? null;

        $has_channel = ChatChannel::where(function ($query) use ($clientId, $deliveryId) {
            $query->where('initiator_id', $clientId)
                ->where('participant_id', $deliveryId);
        })->orWhere(function ($query) use ($clientId, $deliveryId) {
            $query->where('initiator_id', $deliveryId)
                ->where('participant_id', $clientId);
        })->first();
        if (!$has_channel) {
            $delivery = Employee::where('id', $deliveryId)->first();
            $has_channel = createChannel($user, $userType, $delivery, 'driver', 'driver');
        }
        $contact = [
            'delivery_id' => $order->delivery->id ?? null,
            'delivery_name' => $order->delivery->first_name . ' ' . $order->delivery->last_name ?? null,
            'delivery_phone' => $order->delivery->phone_number ?? null,
            'delivery_image' => $order->delivery->image ?? '/front/AlKout-Resturant/SiteAssets/images/delivery-man.png',
            'chat_channel_id' => $has_channel->id ?? null,
        ];
    }

    return $contact;
}

if (!function_exists('send_push_notification')) {

    //    function send_push_notification($usertoken, $title, $message, $type)
    //    {
    //        $projectId = "test-agent-nfgx"; // Your Firebase project ID
    //
    //        try {
    //            // Path to your service account JSON file
    //            $credentialsFilePath = Storage::path('firebase-credentials.json');
    //            // Initialize Google Client
    //            $client = new Google_Client();
    //            $client->setAuthConfig($credentialsFilePath);
    //            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
    //            $client->refreshTokenWithAssertion();
    //            $token = $client->getAccessToken();
    //            $access_token = $token['access_token'];
    //
    //            // Prepare headers
    //            $headers = [
    //                "Authorization: Bearer $access_token",
    //                'Content-Type: application/json'
    //            ];
    //
    //            // Prepare notification payload
    //            $data = [
    //                "message" => [
    //                    "token" => $usertoken,
    //                    "notification" => [
    //                        "title" => $title,
    //                        "body" => $message,
    //                    ],
    //                    "data" => [
    //                        "type" => $type,
    //                        "click_action" => "FLUTTER_NOTIFICATION_CLICK" // Important for Flutter
    //                    ]
    //                ]
    //            ];
    //
    //            $payload = json_encode($data);
    //
    //            // Send request to FCM
    //            $ch = curl_init();
    //            curl_setopt_array($ch, [
    //                CURLOPT_URL => "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
    //                CURLOPT_POST => true,
    //                CURLOPT_HTTPHEADER => $headers,
    //                CURLOPT_RETURNTRANSFER => true,
    //                CURLOPT_SSL_VERIFYPEER => true, // Should be true in production
    //                CURLOPT_POSTFIELDS => $payload,
    //            ]);
    //
    //            $response = curl_exec($ch);
    //            $error = curl_error($ch);
    //            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //            curl_close($ch);
    //
    //            // Log errors if any
    //            if ($error) {
    //                Log::error("FCM cURL Error: $error");
    //                return false;
    //            }
    //
    //            if ($httpCode !== 200) {
    //                Log::error("FCM API Error: HTTP $httpCode - $response");
    //                return false;
    //            }
    //
    //            return json_decode($response, true);
    //        } catch (\Exception $e) {
    //            Log::error("FCM Notification Error: " . $e->getMessage());
    //            return false;
    //        }
    //    }
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

        // Get the active schedule for the date
        $schedule = $employee->scheduleForDate($date);
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

function getBranchStatus($date, $branch_id)
{
    return $currentTime = $date;
    $currentDay = $currentTime->dayOfWeek; // 0 (Sunday) to 6 (Saturday)
    $branch = Branch::find($branch_id);
    if (!$branch) {
        return false;
    }
    return $branchTime = $branch->branchTimeItem($currentDay)->first();
    // If no branch time exists for the current day, return false
    if (!$branchTime) {
        return false;
    }

    $openingTime = \Carbon\Carbon::parse($branchTime->opening_hour);
    $closingTime = \Carbon\Carbon::parse($branchTime->closing_hour);

    // Handle cross-day (e.g., closing past midnight)
    if ($branchTime->cross_day) {
        $closingTime = $closingTime->addDay(); // Extend closing to next day
    }

    // Check if the current time is within the opening and closing times
    $check_current_time = $currentTime->between($openingTime, $closingTime);
    if (!$check_current_time) {
        return 111;
    }
    return $check_current_time;
}

function getBranchMenuDetails($branch_id, $dish_id, $type, $count)
{
    $branch_dish = "";
    if ($type == "web") {
        if ($count == "first") {
            $branch_dish = BranchMenu::where('dish_id', $dish_id)->where('branch_id', $branch_id)->first();
        } else {
            $branch_dish = BranchMenu::whereIn('dish_id', $dish_id)->where('branch_id', $branch_id)->pluck('dish_id');
        }
    } else {
        if ($count == "first") {
            $branch_dish = BranchMenu::where('id', $dish_id)->where('branch_id', $branch_id)->first();
        } else {
            $branch_dish = BranchMenu::whereIn('id', $dish_id)->where('branch_id', $branch_id)->pluck('dish_id');
        }
    }
    return $branch_dish;
}

function getBranchSizeDetails($branch_id, $size_id, $type, $count)
{
    $branch_size = "";
    if ($type == "web") {
        if ($count == "first") {
            $branch_size = BranchMenuSize::where('dish_size_id', $size_id)->where('branch_id', $branch_id)->first();
        } else {
            $branch_size = BranchMenuSize::whereIn('dish_size_id', $size_id)->where('branch_id', $branch_id)->pluck('dish_size_id');
        }
    } else {
        if ($count == "first") {
            $branch_size = BranchMenuSize::where('id', $size_id)->where('branch_id', $branch_id)->first();
        } else {
            $branch_size = BranchMenuSize::whereIn('id', $size_id)->where('branch_id', $branch_id)->pluck('dish_size_id');
        }
    }
    return $branch_size;
}

function getBranchAddonDetails($branch_id, $addon_id, $type, $count)
{
    $branch_addon = "";
    if ($type == "web") {
        if ($count == "first") {
            $branch_addon = BranchMenuAddon::where('dish_addon_id', $addon_id)->where('branch_id', $branch_id)->first();
        } else {
            $branch_addon = BranchMenuAddon::whereIn('dish_addon_id', $addon_id)->where('branch_id', $branch_id)->pluck('dish_addon_id');
        }
    } else {
        if ($count == "first") {
            $branch_addon = BranchMenuAddon::where('id', $addon_id)->where('branch_id', $branch_id)->first();
        } else {
            $branch_addon = BranchMenuAddon::whereIn('id', $addon_id)->where('branch_id', $branch_id)->pluck('dish_addon_id');
        }
    }
    return $branch_addon;
}

function cancelOrderReason($order_id, $reason_id, $reason, $item_id = null)
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
    $addReason->order_details_id = $item_id;
    $addReason->reason_id = $reason_id;
    $addReason->reason = $reason;
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
        return "part";
    } else {
        return "credit";
    }
    return 'cash';
}

if (!function_exists('getFinanceSetting')) {
    function getFinanceSetting(string $key, string $module, $default = null)
    {
        $setting = FinanceSetting::where('setting_key', $key)
            ->where('setting_module', $module)
            ->where('is_active', true)
            ->first();

        if (!$setting) return $default;

        return $setting->type === 'boolean'
            ? filter_var($setting->setting_value, FILTER_VALIDATE_BOOLEAN)
            : $setting->setting_value;
    }
}
