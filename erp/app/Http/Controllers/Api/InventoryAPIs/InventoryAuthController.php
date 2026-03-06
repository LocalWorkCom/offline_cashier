<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Branch;
use App\Models\Country;
use App\Models\Employee;
use App\Models\EmployeeMachine;
use App\Models\EmployeeOpeningBalance;
use App\Models\Nationality;
use App\Services\TimetableService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class InventoryAuthController extends Controller
{
    protected $timeTableService;

    // Inject the service via constructor
    public function __construct(TimetableService $timeTableService)
    {
        $this->timeTableService = $timeTableService;
    }
    //    public function Register(Request $request)
    //    {
    //        $lang = $request->header('lang', 'ar');
    //        App::setLocale($lang);
    //
    //        $validator = Validator::make($request->all(), [
    //            "name" => "required|string",
    //            "email" => "required|email|unique:users",
    //            'country_code' => 'required|string',
    //            "password" => "required|min:6",
    //            'phone' => [
    //                'required',
    //                'string',
    //                Rule::unique('users')->where(function ($query) use ($request) {
    //                    return $query->where('country_code', $request->country_code);
    //                }),
    //            ],
    //            "birth_date" => "nullable|date",
    //
    //        ]);
    //
    //        if ($validator->fails()) {
    //            return respondError('Validation Error.', 400, $validator->errors());
    //        }
    //
    //        $country = Country::where('phone_code', $request->country_code)->first();
    //
    //        if (!$country) {
    //            return respondError('Invalid country code.', 400, [
    //                'credential' => [__('validation.countryCodeExists')]
    //            ]);
    //        }
    //
    //        $user = new User();
    //        $user->name = $request->name;
    //        $user->email = $request->email;
    //        $user->flag = 'client';
    //        $user->phone = $request->phone;
    //        $user->country_id = $country->id;
    //        $user->birth_date = $request->birth_date;
    //        $user->country_code = $request->country_code;
    //        $user->password = Hash::make($request->password);
    //        $user->save();
    //
    //        event(new UserRegistered($user));
    //
    //        return RespondWithSuccessRequest($lang, 23);
    //    }

    public function login(Request $request)
    {
        $user = Employee::where('email', $request->email_or_phone)
            ->where('country_code', $request->country_code)
            ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken("employeeToken")->accessToken;

            return response()->json([
                'status' => 'success',
                'token' => $token,
                'user' => $user
            ]);
        } else {
            return response()->json(['status' => 'failed'], 401);
        }
    }
}
