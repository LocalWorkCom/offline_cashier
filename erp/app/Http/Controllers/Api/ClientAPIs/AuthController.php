<?php

namespace App\Http\Controllers\Api\ClientAPIs;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterClientRequest;
use App\Models\ClientAddress;
use App\Models\Country;
use App\Models\Otp;
use App\Models\User;
use App\Services\AuthAppService;
use App\Services\AuthWebService;
use App\Services\ClientServices\ClientService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    //this is for client app auth
    protected $clientService;
    protected $checkToken;
    protected $authService;

    public function __construct(ClientService $clientService, AuthAppService $authService)
    {
        $this->clientService = $clientService;
        $this->authService = $authService;

        $this->checkToken = false;
    }

    public function register(RegisterClientRequest $request)
    { //this work for client app
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $this->clientService->registerClient($request->all(), 'api');

        return RespondWithSuccessRequest($lang, 23);
    }

    public function login(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $response = $this->authService->login(
            $request->all(),
            'api',   // guard
            'client',   // flag
            $lang
        );
        if (!is_array($response) || (array_key_exists('status', $response) && $response['status'] == false) || (isset($response['status']) && $response['status'] == 403)) {
            return respondError('Validation Error.', 400,  $response['errorData']);
        }
        // Check if client has addresses only for api guard
        $hasAddress = DB::table('client_addresses')->where('user_id', $response['user']['id'])->exists();
        $response['has_address'] = $hasAddress;
        return ResponseWithSuccessData($lang, $response, 12);
    }

    public function verifyPhone(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $response = $this->authService->verifyPhone($request->all(), $lang);
        if (!is_array($response) || array_key_exists('status', $response) && $response['status'] == false || $response['status'] === 403) {
            return respondError('Validation Error.', 400,  $response['errorData']);
        }

        return ResponseWithSuccessData($lang, $response['data'], 35);
    }

    public function resetPassword(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $response = $this->authService->resetPassword($request->all(), $lang);

        if (!is_array($response) || array_key_exists('status', $response) && $response['status'] == false) {
            return respondError('Validation Error.', 400,  $response['errorData']);
        }

        return ResponseWithSuccessData($lang, $response, 15);
    }


    // private function verifyPhone($phone, $otpInput)
    // {
    //     $otpRecord = Otp::where('phone', $phone)
    //         ->where('otp', $otpInput)
    //         ->first();

    //     // Check if OTP exists and is not expired
    //     if ($otpRecord && Carbon::now()->lt(Carbon::parse($otpRecord->expires_at))) {
    //         // OTP is valid, delete it after verification
    //         $otpRecord->delete();

    //         return true;
    //     }

    //     return false; // OTP is invalid or expired
    // }

    // public function generateAndSendOtp($phone)
    // {
    //     $otp = rand(100000, 999999); // Generate a 6-digit OTP

    //     // Save OTP in the database with an expiration time (e.g., 5 minutes)
    //     Otp::updateOrCreate(
    //         ['phone' => $phone],
    //         [
    //             'otp' => $otp,
    //             'expires_at' => now()->addMinutes(5),
    //         ]
    //     );

    //     // Send OTP to the user via SMS or other methods
    //     // Example: Log OTP for demonstration purposes
    //     Log::info("OTP for phone $phone: $otp");

    //     return true; // Return success
    // }

    public function Logout(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $response = $this->authService->logout(auth('api')->user(), $lang);

        return ResponseWithSuccessData($lang, $response['data'], 16);
    }
}
