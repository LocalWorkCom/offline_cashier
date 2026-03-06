<?php


namespace App\Services;

use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthWebService
{
    //this work for web as login use session
    //this service work in login logout for client and admin
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function loginUser(array $credentials, string $guard, string $flag)
    {
        $loginValue = $credentials['email_or_phone'] ?? null;

        $user = User::where(function ($query) use ($loginValue) {
            $query->where('email', $loginValue)
                ->orWhere('phone', $loginValue)
                ->orWhere('national_id', $loginValue);
        })
            ->when(isset($credentials['country_code_login']), function ($query) use ($credentials) {
                $query->where('country_code', $credentials['country_code_login']);
            })
            ->first();
        if (!$user || ($flag && $user->flag !== $flag)) {

            throw ValidationException::withMessages([
                'email_or_phone' => [__('auth.only_' . $flag)],
            ]);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => [__('auth.invalid_credentials')],
            ]);
        }

        Auth::guard($guard)->login($user);
        session()->regenerate();

        return $user;
    }

    public function logout(Request $request, $guard)
    {
        Auth::guard($guard)->logout();

        $request->session()->forget($guard);
        $request->session()->invalidate();

        $request->session()->regenerateToken();
        return true;
    }
    public function changePassword($user, string $newPassword): bool
    {
        $user->password = Hash::make($newPassword);
        return $user->save();
    }

    public function updateProfile(array $data, $user)
    {
        $user->name = $data['name'];
        $user->phone = $data['phone'];
        $user->email = $data['email'];
        $user->birth_date = $data['birth_date'] ?? null;
        $user->country_code = $data['country_code'];
        $user->country_id = Country::where('phone_code', $data['country_code'])->value('id');
        $user->save();

        return $user;
    }
}
