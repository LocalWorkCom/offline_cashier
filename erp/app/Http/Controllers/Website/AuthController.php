<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\RegisterClientRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Branch;
use App\Models\ClientAddress;
use App\Models\User;
use App\Services\AuthAppService;
use App\Services\AuthWebService;
use App\Services\ClientServices\ClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    //this is for website client auth and profile
    protected $clientService;
    protected $checkToken;
    protected $authService;
    protected $authappService;

    public function __construct(AuthWebService $authService, ClientService $clientService, AuthAppService $authappService)
    {
        $this->authService = $authService;
        $this->clientService = $clientService;
        $this->authappService = $authappService;

        $this->checkToken = false;
    }

    public function register(RegisterClientRequest $request)
    {
        // this work for website register
        $user = $this->clientService->registerClient($request->all(), 'client');
        Auth::guard('client')->login($user);
        Session::regenerate();
        // Save address
        if (!empty($request['address']) && $request['address'] !== 'null') {
            $decodedString = json_decode($request['address']);
            $decodedArray = json_decode($decodedString, true);
            if (method_exists($this, 'saveAddress')) {
                $address_id = $this->saveAddress($decodedArray, $request['location']);
                session(['new_address_id' => $address_id]);
            }
        }
        return response()->json([
            'status' => 200,
            'message' =>  null,
            'errors' => null,
        ], 200);
    }

    protected function saveAddress(array $decodedArray, $location)
    {
        $address = $decodedArray;
        $decodedString = json_decode($location);

        $locationArray = json_decode($decodedString, true);
        // dd($address['type']);
        if (!$address) {
            return response()->json(['status' => 'error', 'message' => 'Address data is missing'], 422);
        }
        if ($address['type'] == 'home') {
            $type = 'apartment';
        } elseif ($address['type'] == 'work') {
            $type = 'office';
        } elseif ($address['type'] == 'hotel') {
            $type = 'hotel';
        } else {
            $type = 'villa';
        }
        $userId = Auth::guard('client')->user()->id;

        // Update all other addresses for this user to is_default = 0
        // ClientAddress::where('user_id', $userId)->update(['is_default' => 0]);

        // Create and save the new address
        $addresssa = new ClientAddress();
        $addresssa->user_id = $userId;
        $addresssa->state = $locationArray['state'] ?? null;
        $addresssa->latitude = $locationArray['lat'];
        $addresssa->longtitude = $locationArray['lng'];
        $addresssa->is_default = 1;
        $addresssa->city = $locationArray['city'] ?? null;
        $addresssa->address_type = $type;
        $addresssa->floor_number = $address['floor'];
        $addresssa->apartment_number = $address['num'];
        $addresssa->country_code = $address['country_code'];
        $addresssa->address_phone = $address['phone'];
        $addresssa->address = $address['detail'];
        $addresssa->notes = $address['mark'];
        if ($address['type'] == 'hotel') {
            $addresssa->hotel_id = (int)$address['name'];
        } else {
            $addresssa->building = $address['name'];
        }
        $addresssa->save();
        if ($addresssa) {
            return $addresssa->id;
        } else {
            return null;
        }
    }
   public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email_or_phone' => 'required|string',
        'password' => 'required|string',
        'country_code_login' => 'required|string',
        'address' => 'nullable|json',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors(),
        ], 422);
    }

    try {
        $input = $request->only(['email_or_phone', 'password', 'country_code_login']);
        $input['user_login'] = $input['email_or_phone'];

        $user = $this->authService->loginUser($input, 'client', 'client');

        $address_id = null;
        $branchId = $request->cookie('branch_id');
        $branch = Branch::find($branchId);

        if (!$branch) {
            return response()->json([
                'status' => 'error',
                'message' => __('auth.branch_not_found'),
            ], 400);
        }

        // Check if location was provided
        if ($request->latData && $request->longData) {
            $existingAddress = ClientAddress::where('user_id', $user->id)
                ->where('latitude', $request->latData)
                ->where('longtitude', $request->longData)
                ->first();

            if ($existingAddress) {
                session(['nolat' => true, 'new_address_id' => $existingAddress->id]);
            } else {
                $fallbackAddress = ClientAddress::where('user_id', $user->id)
                    ->where('area_id', $branch->area_id)
                    ->first()
                    ?? ClientAddress::where('user_id', $user->id)
                        ->where('is_default', 1)
                        ->first();

                if ($fallbackAddress) {
                    session(['new_address_id' => $fallbackAddress->id]);
                }
            }

        } elseif (is_null($request->address) || $request->address === 'null') {
            // No map location and no address JSON
            $fallbackAddress = ClientAddress::where('user_id', $user->id)
                ->where('area_id', $branch->area_id)
                ->first()
                ?? ClientAddress::where('user_id', $user->id)
                    ->where('is_default', 1)
                    ->first();

            if ($fallbackAddress) {
                session(['new_address_id' => $fallbackAddress->id]);
            }

        } else {
            // Save new address from provided JSON
            $decodedAddress = json_decode($request->address, true);
            $address_id = $this->saveAddress($decodedAddress, $request->location);
            session(['new_address_id' => $address_id]);
        }

        return response()->json([
            'status' => 'success',
            'message' => __('auth.login_success'),
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'errors' => $e->errors(),
        ], 422);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => __('auth.login_failed'),
            'details' => $e->getMessage(), // Optional: Remove in production
        ], 500);
    }
}

    public function logout(Request $request)
    {
        $guard = $this->authService->logout($request, 'client');

        if ($guard) {
            return redirect()->route('home'); // Client logout redirect
        }
    }

    public function checkPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phoneforget' => 'required|string',
            'country_code_forget' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // ✅ Map the request fields to expected keys
        $phoneData = [
            'phone' => $request->input('phoneforget'),
            'country_code' => $request->input('country_code_forget'),
        ];

        // ✅ Use your service method
        $response = $this->authappService->verifyPhone($phoneData, App::getLocale());

        if (!is_array($response) || array_key_exists('status', $response) && $response['status'] == false) {
            return response()->json([
                'status' => false,
                'errors' => $response['errorData'],
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'phone' => $response['data']['phone'],
            'country_code_forget' => $response['data']['country_code'],
        ]);
    }
    public function resetPassword(Request $request)
    {
        $credentials = [
            'phone' => $request->phone,
            'country_code' => $request->country_code,
            'password' => $request->passwordforget,
            'password_confirm' => $request->passwordforget_confirmation,
        ];
        $response = $this->authappService->resetPassword($credentials, app()->getLocale());

        if (!is_array($response) || array_key_exists('status', $response) && $response['status'] == false) {
            return response()->json([
                'status' => false,
                'message' => $response['message'] ?? 'Validation Error',
                'errorData' => $response['errorData'] ?? [],
            ], $response['code'] ?? 422);
        }

        if (!Auth::guard('client')->check()) {
            Auth::guard('client')->loginUsingId($response['user']['id']);
            $request->session()->regenerate();
        }

        return response()->json([
            'token' => $response['token'],
            'user' => $response['user']
        ]);
    }

    public function viewProfile()
    {
        return view('website.auth.profile');
    }

    public function changePassword(ChangePasswordRequest $request)
    {

        try {
            $user = auth('client')->user();

            // 🔔 Call service method
            $this->authService->changePassword($user, $request->password);

            return redirect()->route('website.profile.view')->with('message', __('auth.password_updated'));
        } catch (\Exception $e) {
            Log::error('Password change failed', ['exception' => $e]);
            return back()->with('error', __('auth.update_failed'));
        }
    }
    public function updateProfile(UpdateProfileRequest $request)
    {

        try {
            $user = auth('client')->user();
            $this->authService->updateProfile($request->validated(), $user);

            return redirect()->route('website.profile.view')->with('message', __('auth.profile_updated'));
        } catch (ValidationException  $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Profile update failed', ['exception' => $e]);
            return back()->with('error', __('auth.update_failed'));
        }
    }
}
