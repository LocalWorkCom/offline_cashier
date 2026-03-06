<?php

namespace App\Http\Controllers\Api\ClientAPIs;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Country;
use App\Models\Order;
use App\Services\AuthWebService;
use App\Services\ClientServices\ClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClientController extends Controller
{
    //this is for client updates with auth
    protected $clientService;
    protected $checkToken;
    protected $authService;

    public function __construct(AuthWebService $authService, ClientService $clientService)
    {
        $this->authService = $authService;
        $this->clientService = $clientService;
        $this->checkToken = false;
    }

    public function viewProfile(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $user = Auth::user();

        return ResponseWithSuccessData($lang, $user, 18);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {

        try {
            $lang = $request->header('lang', 'ar');
            App::setLocale($lang);
            $user = auth('api')->user();
            $this->authService->updateProfile($request->validated(), $user);

            return ResponseWithSuccessData($lang, $user, 19);
        } catch (ValidationException  $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Profile update failed', ['exception' => $e]);
            return back()->with('error', __('auth.update_failed'));
        }
    }
    public function changePassword(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $messages = [
            "password.required" => __('validation.newPasswordRequired'),
            "password_confirm.required" => __('validation.confirmPassword'),
            "password_confirm.same" => __('validation.confirmPasswordSame'),
            'password.new' => __('validation.newOldPassword')
        ];

        $validator = Validator::make($request->all(), [
            "password" => "required|min: 6",
            "password_confirm" => "required|same:password",
        ], $messages);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $user = Auth::user();

        if (Hash::check($request->password, $user->password)) {
            return respondError('Password Error', 403, ['password' => [__('validation.newOldPassword')]]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        $user->tokens()->delete();
        $token = $user->createToken("MyApp")->accessToken;

        $userData = $user->only(['id', 'name', 'email', 'country_code', 'phone']);
        $success = [
            "token" => $token,
            "user" => $userData,
        ];

        return ResponseWithSuccessData($lang, $success, 15);
    }
    public function deactivateProfile(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $user = Auth::user();

        try {
            $user->update([
                'is_active' => 0,
            ]);

            return ResponseWithSuccessData($lang, null, 1);
        } catch (\Exception $e) {
            Log::error('Error deactivating user profile: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
