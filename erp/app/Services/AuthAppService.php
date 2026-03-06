<?php


namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthAppService
{
    //this work for web as login use session
    //this service work in login logout for client and admin
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function login(array $data, string $guard, string $flag, string $lang = 'ar')
    {
        app()->setLocale($lang);

        $messages = [
            'email_or_phone.required' => __('validation.email_or_phone.required'),
            'password.required' => __('validation.password.required'),
            'country_code.required_if' => __('validation.country_code.required_if'),
        ];

        $rules = [
            "device_token" => "nullable|string",
            "email_or_phone" => "required",
            "password" => "required",
            "country_code" => "required_if:email_or_phone," . ($this->isPhone($data['email_or_phone'] ?? '') ? $data['email_or_phone'] : ''),
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {

            return [
                'code' => 404,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
            ];
        }

        $emailOrPhone = $data['email_or_phone'];
        $isEmail = filter_var($emailOrPhone, FILTER_VALIDATE_EMAIL);

        $userQuery = User::query();

        if ($isEmail) {
            $userQuery->where('email', $emailOrPhone);
        } else {
            $userQuery->where('phone', $emailOrPhone)
                ->where('country_code', $data['country_code'] ?? '');
        }

        $user = $userQuery->first();
        if (!$user || ($flag && $user->flag !== $flag)) {
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => [
                    'email_or_phone' => $isEmail ? __('validation.emailDoesntExist') : __('validation.phoneDoesntExist')
                ]
            ];
        }

        if (!Hash::check($data['password'], $user->password)) {
            return [
                'code' => 403,
                'status' => true,
                'message' => __('Password Error'),
                'data' => null,
                'errorData' => [
                    'credential' => [__('validation.passwordMatch')]
                ]
            ];
        }

        // Create token with expiration 24h
        $expiresIn = 86400;
        $tokenResult = $user->createToken("myToken", ['client-access']);
        $token = $tokenResult->accessToken;
        $tokenResult->token->expires_at = Carbon::now()->addSeconds($expiresIn);
        $tokenResult->token->save();


        // Save device token if present
        if (!empty($data['device_token'])) {
            $user->fcm_token = $data['device_token'];
            $user->save();
        }


        $responseData = [
            "access_token" => $token,
            'user' => $user,
        ];


        return  $responseData;
    }
    public function verifyPhone(array $data, string $lang = 'ar')
    {
        App::setLocale($lang);

        $messages = [
            "phone.required" => __('validation.email_or_phone.required'),
            "country_code.required" => __('validation.country_code.required'),
            "phone.exists" => __('validation.phoneDoesntExist'),
        ];

        $validator = Validator::make($data, [
            "phone" => "required",
            "country_code" => "required",
        ], $messages);

        if ($validator->fails()) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
            ];
        }

        $userExists = User::where('phone', $data['phone'])
            ->where('country_code', $data['country_code'])
            ->exists();

        if (!$userExists) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => [
                    'phone' => [__('validation.phoneDoesntExist')]
                ]
            ];
        }

        return [
            'code' => 200,
            'status' => true,
            'message' => __('Phone verified successfully'),
            'data' => [
                'phone' => $data['phone'],
                'country_code' => $data['country_code'],
            ],
            'errorData' => null,
        ];
    }
    public function resetPassword(array $data, string $lang = 'ar')
    {
        App::setLocale($lang);

        $messages = [
            "phone.required" => __('validation.email_or_phone.required'),
            "country_code.required" => __('validation.country_code.required'),
            "phone.exists" => __('validation.phoneDoesntExist'),
            "password.required" => __('validation.newPasswordRequired'),
            "password_confirm.required" => __('validation.confirmPassword'),
            "password_confirm.same" => __('validation.confirmPasswordSame'),
        ];

        $validator = Validator::make($data, [
            "phone" => "required",
            "country_code" => "required",
            "password" => "required|min:6",
            "password_confirm" => "required|same:password",
        ], $messages);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => 'Validation Error.',
                'code' => 400,
                'errorData' => $validator->errors()
            ];
        }

        $user = User::where('phone', $data['phone'])
            ->where('country_code', $data['country_code'])
            ->first();

        if (!$user) {
            return [
                'status' => false,
                'message' => 'Validation Error.',
                'code' => 400,
                'errorData' => ['phone' => [__('validation.phoneDoesntExist')]]
            ];
        }

        if (Hash::check($data['password'], $user->password)) {
            return [
                'status' => false,
                'message' => 'Password Error',
                'code' => 403,
                'errorData' => ['password' => [__('validation.newOldPassword')]]
            ];
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        $token = $user->createToken("MyApp")->accessToken;
        $userData = $user->only(['id', 'name', 'email', 'country_code', 'phone']);

        return  [
            'token' => $token,
            'user' => $userData
        ];
    }

    // Simple phone check helper
    private function isPhone($input): bool
    {
        return preg_match('/^\+?[0-9]{7,15}$/', $input);
    }

    public function logout($user, $lang = 'ar')
    {
        App::setLocale($lang);

        if ($user && $user->token()) {
            $user->token()->revoke();
        }

        return [
            'code' => 200,
            'status' => true,
            'message' => __('Logout successful'),
            'data' => null,
        ];
    }
}
