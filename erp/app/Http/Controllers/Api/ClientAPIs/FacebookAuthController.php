<?php

namespace App\Http\Controllers\Api\ClientAPIs;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
class FacebookAuthController extends Controller
{
    private $lang;

    public function __construct(Request $request)
    {
        $this->lang = $request->header('lang', 'ar');
    }

    public function redirect()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function callback()
    {
        try {
            $facebook_user = Socialite::driver('facebook')->user();
            $user = User::where('facebook_id', $facebook_user->id)->first();
            if (!$user) {
                $new_user = User::create([
                    'name' => $facebook_user->name,
                    'email' => $facebook_user->email,
                    'facebook_id' => $facebook_user->id,
                    'country_id' => Country::first()->id,
                    'phone' => 'null',
                ]);
                $token = $new_user->createToken("FacebookAuthToken")->accessToken;
                $new_user->save();
                Auth::login($new_user);
                $new_user->token = $token;
                return ResponseWithSuccessData($this->lang,$new_user,12);

            } else{
                Auth::login($user);
                $token = $user->createToken("FacebookAuthToken")->accessToken;
                $user->save();
                $user->token = $token;
                return ResponseWithSuccessData($this->lang,$user,12);

            }
        } catch (\Exception $e) {
            return RespondWithBadRequest($this->lang, 14);
        }
    }
}
