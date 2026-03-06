<?php

namespace App\Http\Controllers\Api\HR_APIs;

use Carbon\Carbon;
use App\Models\Branch;
use App\Models\Country;
use App\Models\Employee;
use App\Events\TotalPaid;
use App\Models\Nationality;
use Illuminate\Support\Str;
use App\Models\SystemModule;
use Illuminate\Http\Request;
use App\Models\EmployeeMachine;
use App\Models\PaymentPolicies;
use Illuminate\Validation\Rule;
use App\Models\EmployeeFacility;
use App\Models\EmployeeSchedule;
use App\Models\InventorySetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\EmployeeOpeningBalance;
use App\Models\PaymentPolicyInvoiceCount;
use Illuminate\Support\Facades\Validator;
use App\Services\HR_Services\TimetableService;
use App\Http\Controllers\Api\CashierAPIs\CashierBalanceController;

class EmployeeAuthController extends Controller
{
    protected $timeTableService;

    // Inject the service via constructor
    public function __construct(TimetableService $timeTableService)
    {
        $this->timeTableService = $timeTableService;
    }

    public function login(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            App::setLocale($lang);

            $requestPath = $request->path();

            $loginMappings = [
                '/cashier-login' => ['cashier'],
                '/delivery-login' => ['driver'],
                '/waiter-login' => ['waiter'],
                '/customer-service-login' => ['customer_service'],
                '/kitchen-login' => ['Head Chef'],
                '/dashboard-login' => ['admin', 'branch manager', 'hr', 'inventory',  'purchase', 'finance', 'employee', 'cashier', 'driver', 'waiter', 'customer_service', 'Head Chef'],
                '/hr-app-login' => ['admin', 'branch manager', 'hr', 'inventory',  'purchase', 'finance', 'employee', 'cashier', 'driver', 'waiter', 'customer_service', 'Head Chef']

            ];

            $matchedFlags = null;
            foreach ($loginMappings as $pattern => $flags) {
                if (Str::contains($requestPath, $pattern)) {
                    $matchedFlags = $flags;
                    break;
                }
            }

            if (!$matchedFlags) {
                return respondError(__('validation.unknownFlag'), 400, ['flag' => __('validation.unknownFlag')]);
            }

            $messages = [
                'email_or_phone.required_without' => __('validation.custom.email_or_phone.required_without'),
                'email_or_phone.notfound' => __('validation.custom.email_or_phone.notfound'),
                'password.required'       => __('validation.password.required'),
                'country_code.required_if' => __('validation.country_code.required_if'),
            ];

            $validator = Validator::make($request->all(), [
                "device_token" => "nullable|string",
                "email_or_phone" => "required_without:national_id|string",
                "national_id" => "required_without:email_or_phone",
                "password" => "required",
                "country_code" => "required_if:email_or_phone," . ($this->isPhone($request->email_or_phone) ? $request->email_or_phone : ''),
            ], $messages);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }
            if ($request->filled('national_id')) {
                $user = Employee::where('national_id', $request->national_id)
                    ->whereIn('flag', $matchedFlags)
                    ->first();
                $loginMethod = 'national_id';
            } else {
                $isEmail = filter_var($request->email_or_phone, FILTER_VALIDATE_EMAIL);
                $loginMethod = $isEmail ? 'email' : 'phone';

                $query = Employee::whereIn('flag', $matchedFlags);

                $user = $isEmail
                    ? $query->where('email', $request->email_or_phone)->first()
                    : $query->where('phone_number', $request->email_or_phone)
                    ->where('country_code', $request->country_code)
                    ->first();
            }
            if (!$user) {
                $errorMessage = $request->filled('national_id')
                    ? __('validation.nationalIdDoesntExist')
                    : ($isEmail ? __('validation.emailDoesntExist') : __('validation.phoneDoesntExist'));
                $errorField = $request->filled('national_id') ? 'national_id' : 'email_or_phone';

                return respondError(($lang == 'en' ? 'User Not Found.' : 'لم يتم العثور على المستخدم.'), 404, [
                    $errorField => [$errorMessage]
                ]);
            }

            // Validate credentials
            switch ($loginMethod) {
                case 'national_id':
                    $isUser = password_verify($request->password, $user->password);
                    break;
                case 'email':
                    $isUser = (
                        $request->email_or_phone == $user->email &&
                        password_verify($request->password, $user->password)
                    );
                    break;
                case 'phone':
                    $isUser = (
                        $request->email_or_phone == $user->phone_number &&
                        $request->country_code == $user->country_code &&
                        password_verify($request->password, $user->password)
                    );
                    break;
            }
            if ($requestPath === 'api/dashboard-login' && $user && $user->flag === 'purchase') {
                if ($user && $user->is_first_login) {
                    $data = [
                        'message' => $lang == 'en'
                            ? 'This is your first login, please create your own new password.'
                            : 'هذه المرة الأولى لتسجيل الدخول. من فضلك قم بإنشاء كلمة المرور الخاصة بك',
                        'is_first_login' => true
                    ];

                    return ResponseWithSuccessData($lang, $data, 1);
                }
                if ($user && $user->employee_status_id == 7) {
                    return respondError(($lang == 'en' ? 'Your account is suspended. Please contact the administrator.' : 'حسابك موقوف. يرجى الاتصال بالمسؤول.'), 400);
                }
            }

            if (!$isUser) {
                return respondError(($lang == 'en' ? 'Password Error.' : 'خطأ في كلمة المرور.'), 404, [
                    'credential' => [__('validation.passwordMatch')]
                ]);
            }
            if ((!$user || !$user->branch_id) && !$user->hasRole('superAdmin', 'employee')) {
                return respondError(($lang == 'en' ? 'not allow to login without assigned to branch' : 'عفوا المستخدم غير موجه لفرع للسماح بالدخول '), 400);
            }
            if (Str::contains($requestPath, '/dashboard-login') && $user->is_active === 0) {
                $message = $lang === 'en'
                    ? 'This account is deactivated. Only active employees can access the dashboard.'
                    : 'هذا الحساب غير نشط. يمكن فقط للموظفين النشطين الدخول إلى لوحة التحكم.';
                return respondError($message, 403);
            }
            $expiresIn = 86400;
            $rememberMe = filter_var($request->input('remember_me', false), FILTER_VALIDATE_BOOLEAN);

            // 1 day if normal login, 30 days if remember_me = true
            $expiresIn = $rememberMe ? 60 * 60 * 24 * 30 : 60 * 60 * 24;

            $token = $user->createToken("employeeToken")->accessToken;
            $expiresAt = Carbon::now()->addSeconds($expiresIn);
            $formattedExpiresAt = $expiresAt->format('Y-m-d H:i:s');

            // Update token expiration in database
            $lastToken = DB::table('oauth_access_tokens')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastToken) {
                DB::table('oauth_access_tokens')
                    ->where('id', $lastToken->id)
                    ->update(['updated_at' => now(), 'expires_at' => $formattedExpiresAt]);
            }


            if ($request->device_token) {
                $user->device_token = $request->device_token;
                $user->save();
            }

            $today = Carbon::now()->format('Y-m-d');
            $time = Carbon::now();
            $formattedTime = $time->format('h:i');
            $meridiem = $lang === 'en' ? $time->format('A') : ($time->format('A') === 'AM' ? 'ص' : 'م');
            $time = $formattedTime . ' ' . $meridiem;
            $employee_schedule = EmployeeSchedule::where('employee_id', $user->id)
                ->latest('id')->whereNull('deleted_at')
                ->first();
            $userFlag = $user->flag;

            $skipFlagsIfModuleInactive = ['inventory', 'finance'];
            $systemModuleStatus = SystemModule::where('id', 1)->value('is_active');

            // Check if we should skip schedule validation
            $skipScheduleCheck = in_array($userFlag, $skipFlagsIfModuleInactive) && !$systemModuleStatus;

            if (!$skipScheduleCheck) {
                if (!$employee_schedule) {
                    $message = $lang == 'en'
                        ? 'No schedule assigned to employee'
                        : 'لا يوجد جدول مخصص للموظف';
                    return respondError($message, 404);
                }
                $today = now()->toDateString();

                // Check if schedule is not yet started or already ended
                if ($today < $employee_schedule->start_date || $today > $employee_schedule->end_date) {
                    $message = $lang == 'en'
                        ? 'You cannot login. Your work schedule has not started or has already ended.'
                        : 'لا يمكنك تسجيل الدخول، لم يبدأ جدول عملك بعد أو انتهى بالفعل.';
                    return respondError($message, 400);
                }
            }
            $shift = $this->timeTableService->getTimetableForDate($user->id, $today);
            if (!($shift['status'] == false)) {
                $shiftType = $lang == 'en' ? $shift['data']['timetable']['name_en'] : $shift['data']['timetable']['name_ar'];
                $user->shift_start = $shift['data']['on_duty_time'];
                $user->shift_end = $shift['data']['off_duty_time'];
                $user->shift_type = $shiftType;
                $user->employee_schedule_id = $shift['data']['employee_schedule_id'];
                $user->cross_day = $shift['data']['cross_day'];
            } else {
                $user->shift_start = null;
                $user->shift_end = null;
                $user->shift_type = null;
                $user->employee_schedule_id = null;
                $user->cross_day = null;
            }

            $branch = $user->branch_id ? Branch::find($user->branch_id) : null;
            $user->branch = $branch ? ($lang == 'en' ? $branch->name_en : $branch->name_ar) : null;
            $nationality = $user->nationality_id ? Nationality::find($user->nationality_id) : null;
            $user->nationality = $nationality ? ($lang == 'en' ? $nationality->name_en : $nationality->name_ar) : null;

            if ($user->marital_status_id && $user->maritalStatus) {
                $user->setAttribute('marital_status', $user->maritalStatus->{'name_' . $lang});
                unset($user->maritalStatus);
            }

            $user->gender = $user->gender !== null
                ? ($lang === 'ar' ? ($user->gender === 'female' ? 'أنثى' : 'ذكر') : $user->gender)
                : $user->gender;

            $flagTranslations = [
                'cashier' => 'كاشير',
                'driver' => 'طيار',
                'waiter' => 'نادل',
                'customer_service' => 'خدمة العملاء',
                'Head Chef' => 'رئيس طهاة',
                'admin' => 'مدير',
                'hr' => 'شؤون الموظفين',
                'inventory' => 'المخازن',
            ];
            $userFlag = $user->flag;
            $user->flag = $lang === 'ar' ? ($flagTranslations[$user->flag] ?? $user->flag) : $user->flag;

            if ($user->flag === 'driver' || in_array('driver', $matchedFlags)) {
                $user->load(['country', 'city', 'area', 'vehicle', 'vehicle.type']);
                $user->country_id = $user->country?->id;
                $user->country_name = $lang === 'en' ? $user->country?->name_en : $user->country?->name_ar;
                $user->city_id = $user->city?->id;
                $user->city_name = $lang === 'en' ? $user->city?->name_en : $user->city?->name_ar;
                $user->full_name = $user->first_name . ' ' . $user->last_name;
                $user->area_id = $user->area?->id;
                $user->area_name = $lang === 'en' ? $user->area?->name_en : $user->area?->name_ar;
                $user->vehicle_type = $user->vehicle?->type?->vehicle_type ?? null;
                $user->vehicle_license = $user->vehicle?->license ?? null;
                $user->makeHidden(['country', 'city', 'area', 'vehicle', 'vehicle.type']);
            }

            $branch_id = $user->branch_id ?? Branch::where('is_default', 1)->first()->id;
            $branch_new = $branch_id != null
                ? Branch::with('country:currency_symbol,id') // Include only required fields from the `country` table
                ->where('id', $branch_id)
                ->select(
                    'name_ar',
                    'name_en',
                    'address_ar',
                    'address_en',
                    'is_default',
                    'tax_application',
                    'coupon_application',
                    'tax_percentage',
                    'time_cancellation',
                    'delivery_time',
                    'service_fees',
                    'tax_apply',
                    'delivery_fees',
                    'service_fees_type',
                    'phone',
                    'country_id' // Ensure this is included for the relation to work
                )
                ->first()
                : null;

            if ($branch_new) {
                $branch_new->is_default == 0 ? $branch_new->is_default = false : $branch_new->is_default = true;
                $branch_new->tax_application == 0 ? $branch_new->tax_application = false : $branch_new->tax_application = true;
                $branch_new->coupon_application == 0 ? $branch_new->coupon_application = false : $branch_new->coupon_application = true;
                $branch_new->tax_apply == 0 ? $branch_new->tax_apply = false : $branch_new->tax_apply = true;
                $branch_new->currency_symbol = $branch_new->country->currency_symbol ?? null;
                $branch_new->makeHidden(['name_site', 'address_site', 'country']);
                $branch_new->makeVisible(['phone']);
            }

            $user->makeHidden(['roles', 'permissions', 'password', 'created_by', 'modified_by', 'deleted_by']);

            $data = [
                "access_token" => $token,
                'employee' => $user,
                'branch' => $branch_new,
                'day' => $today,
                'time' => $time,
                'access_dashboard' => (bool)$user->is_active,

            ];

            $count = Employee::where('supervisor_id', $user->id)->count();
            // dd($count);
            $data['is_manager'] = $count > 0 ? true : false;

            if (in_array('cashier', $matchedFlags)) {
                $cashier_machine_id = EmployeeMachine::where('employee_id', $user->id)
                    ->orderby('id', 'desc')
                    ->first()->cashier_machine_id ?? null;
                $user->cashier_machine_id = $cashier_machine_id;

                $previousClosingBalance = EmployeeOpeningBalance::where('cashier_machine_id', $user->cashier_machine_id)
                    ->where('type', 2)
                    ->orderBy('date', 'desc')
                    ->where('date', '<', $today)
                    ->orWhere('date', $today)
                    ->orderBy('date', 'desc')
                    ->orderBy('time', 'desc')
                    ->first() ?? null;

                $existingOpeningBalance = EmployeeOpeningBalance::where('cashier_machine_id', $user->cashier_machine_id)
                    ->where('employee_schedule_id', $user->employee_schedule_id)
                    ->where('date', $today)
                    ->where('type', 1)
                    ->first();

                $data['is_open_balance'] = (bool)$existingOpeningBalance;
                $data['opened_balance_id'] = $existingOpeningBalance?->id;
                //                $data['visa_balance'] = $previousClosingBalance ? (int)$previousClosingBalance->close_visa : 0;

                $request->merge([
                    'cashier_machine_id' => $user->cashier_machine_id,
                    'employee_schedule_id' => $user->employee_schedule_id,
                    'shift_start' => $user->shift_start,
                    'shift_end' => $user->shift_end,
                ]);
                $request->headers->set('Authorization', 'Bearer ' . $data['access_token']);

                $cashierBalanceController = app(CashierBalanceController::class);
                $response = $cashierBalanceController->getCloseBalance($request);
                //                dd($response);

                //balance keys
                if ($response->original['status']) {
                    $data['cash_total'] = $response->original['data']['close_cash'];
                    $data['visa_total'] = $response->original['data']['close_visa'];
                    $data['balance_total'] = $response->original['data']['close_visa'] + $response->original['data']['close_cash'];

                    broadcast(new TotalPaid($user->id, $data));
                } else {
                    $data['cash_total'] = 0;
                    $data['visa_total'] = 0;
                    $data['balance_total'] = 0;
                }

                $paymentpolices = PaymentPolicies::where('branch_id', $user->branch_id)->pluck('id')->toArray();
                $methods = PaymentPolicyInvoiceCount::whereIn('payment_policy_id', $paymentpolices)->get();
                foreach ($methods as $method) {
                    $data[$method->paymentPolicy->order_type . '_print_number'] = $method->invoice_count;
                }
            }
            if (Str::contains($requestPath, '/dashboard-login') && $userFlag === 'inventory') {
                $inventoryEmployee = DB::table('inventory_employees')
                    ->where('employee_id', $user->id)
                    ->first();

                $can_suggest = InventorySetting::first()->auto_purchase_order_on_low_stock;
                $data['warehouse_id'] = $inventoryEmployee ? (int)$inventoryEmployee->store_id : null;
                $data['purchase_Suggestion'] = (bool)  $can_suggest;
            }
            if (in_array($userFlag, ['admin', 'branch manager', 'hr', 'inventory', 'purchase', 'finance'])) {
                $data['is_admin'] = true;
                $data['role'] = $user->getRoleNames();
                $data['permissions'] = $user->getAllPermissions()->pluck('name');
                // $data['dashboard_flag'] = $userFlag; // Now using 'flag' terminology
            } else {
                $data['is_admin'] = false;

                $permissions = $user->permissions->pluck('name');
                if ($permissions->isNotEmpty()) {
                    // user has no role, but has direct permissions
                    $data['role'] = null;
                    $data['permissions'] = $permissions;
                }
            }

            // Add system modules data as array
            $systemModules = SystemModule::select('name', 'is_active')->get();
            $data['system_modules'] = $systemModules->mapWithKeys(function ($module) {
                return [$module->name => (bool)$module->is_active];
            })->toArray();

            //this comment will work when there is will be more than one warehouse in inventory system
            //    if (Str::contains($requestPath, '/dashboard-login') && $userFlag === 'inventory') {
            //     $inventoryStores = DB::table('inventory_employees')
            //         ->where('employee_id', $user->id)
            //         ->pluck('store_id')
            //         ->map(fn($id) => (int) $id)
            //         ->toArray();

            //     $data['warehouse_ids'] = !empty($inventoryStores) ? $inventoryStores : [];
            // }

            //start facility data
            $data['facility'] = [];
            $facility_data = EmployeeFacility::where('employee_id', $user->id)->where('is_active', 1)->first();
            if ($facility_data) {
                $data['facility'] = [
                    'id' => $facility_data->facility?->id,
                    'name' => $facility_data->facility?->name_ar,
                    'code' => $facility_data->facility?->code,
                    'logo' => $facility_data->facility?->logo,
                ];
            }

            activity('employee-auth-' . $user->flag)
                ->causedBy($user)
                ->performedOn($user)

                ->event('login')
                ->withProperties([
                    'ip' => request()->ip(),
                    'flag' => $user->flag,
                    'type' => 'employee  has been login at ' . now()
                ])
                ->log('Employee logged in');

            //end of facility data
            return ResponseWithSuccessData($lang, $data, 12);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred.' : 'حدث خطأ.'), 500, ['error' => $e->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $user = auth('employee')->user();

        // $status = $request->header('status', false);

        // Require status true for 'cashier' and 'driver' only
        // if (in_array($user->flag, ['cashier', 'driver'])) {
        //     if (!$status || $status !== 'true') {
        //         return RespondWithBadRequestData($lang, 2); // Invalid or missing status
        //     }
        // }
        activity('employee-auth-' . $user->flag)
            ->causedBy($user)
            ->performedOn($user)
            ->event('logout')
            ->withProperties([
                'ip' => request()->ip(),
                'flag' => $user->flag,
                'type' => 'employee  has been logout at ' . now()
            ])
            ->log('Employee logout in');

        $user->token()->revoke();

        return ResponseWithSuccessData($lang, null, 16);
    }

    private function isPhone($input)
    {
        return preg_match('/^\+?[0-9]+$/', $input) && !filter_var($input, FILTER_VALIDATE_EMAIL);
    }

    public function updateProfile(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $requestPath = $request->path(); // safer than full URL
        $loginMappings = [
            '/cashier' => 'cashier',
            '/delivery' => 'driver',
            '/waiter' => 'waiter',
            '/customer-service' => 'customer_service',
            '/kitchen' => 'Head Chef',
        ];

        $flag = null;

        foreach ($loginMappings as $pattern => $role) {
            if (Str::contains($requestPath, $pattern)) {
                $flag = $role;
                break; // stop at first match
            }
        }
        if ($request->isMethod('get') && empty($request->all())) {
            return ResponseWithSuccessData($lang, [
                'full_name' => $employee->first_name . ' ' . $employee->last_name,
                'country_code' => $employee->country_code,
                'phone_number' => $employee->phone_number,
            ], 1);
        }
        $phone_length = Country::where('phone_code', $request->country_code)->value('length');

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'country_code' => 'required|string',
            'city_id' => ($flag === 'driver' ? 'required' : 'nullable') . '|exists:cities,id',
            'area_id' => ($flag === 'driver' ? 'required' : 'nullable') . '|exists:areas,id',
            'branch_id' => ($flag === 'customer_service' ? 'required' : 'nullable') . '|exists:branches,id',
            'phone_number' => [
                'required',
                'string',
                Rule::unique('employees')->ignore($employee->id, 'id')->where(function ($query) use ($request) {
                    return $query->where('country_code', $request->country_code);
                }),
                function ($attribute, $value, $fail) use ($phone_length) {
                    if ($phone_length && strlen($value) != $phone_length) {
                        $fail(__('validation.custom.phone.length', ['attribute' => __('auth.phone'), 'length' => $phone_length]));
                    }
                },
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }
        // try {
        $fullName = trim($request->full_name);
        $nameParts = preg_split('/\s+/', $fullName, 2); // Split into max 2 parts

        $country_id = Country::where('phone_code', $request->country_code)->value('id');
        $firstName = $nameParts[0] ?? null;
        $lastName = $nameParts[1] ?? null;

        $updateData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'country_code' => $request->country_code,
            'phone_number' => $request->phone_number,
        ];

        if ($flag === 'driver') {
            $updateData['country_id'] = $country_id;
            $updateData['city_id'] = $request->city_id;
            $updateData['area_id'] = $request->area_id;
        }

        if ($flag === 'customer_service') {
            $updateData['branch_id'] = $request->branch_id;
        }
        if ($request->hasFile('image')) {
            if ($employee->image) {
                DeleteFile('images/employees', $employee->image);
            }
            $image = $request->file('image');
            UploadFile('images/employees', 'image', $employee, $image);
            $updateData['image'] = $image;
        }

        $employee->update($updateData);

        $employeeData = [
            'full_name' => trim($employee->first_name . ' ' . $employee->last_name),
            'country_code' => $employee->country_code,
            'phone_number' => $employee->phone_number,
        ];

        if ($flag === 'driver') {
            $employeeData['city_id'] = (int)$employee->city_id;
            $employeeData['city'] = $employee->city?->name;
            $employeeData['area_id'] = (int)$employee->area_id;
            $employeeData['area'] = $employee->area?->name;
            $employeeData['image_url'] = $employee->image ?? null;
        }

        if ($flag === 'customer_service') {
            $employeeData['branch_id'] = (int)$employee->branch_id;
        }
        activity('employee-auth-' . $employee->flag)
            ->causedBy($employee)
            ->performedOn($employee)

            ->event('updateProfile')
            ->withProperties([
                'ip' => request()->ip(),
                'flag' => $employee->flag,
                'type' => 'employee updated profile at ' . now()
            ])
            ->log('Employee updateProfile in');

        return ResponseWithSuccessData($lang, $employeeData, 19);
    }

    public function createPassword(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);


        $messages = [
            'email_or_phone.required'     => __('validation.email_or_phone.required'),
            'password.required'           => __('validation.password.required'),
            'password.confirmed'          => __('validation.password_confirmation'),
            'password.regex'              => __('validation.password.regex'),
            'password.min'              => __('validation.password.min'),
            'country_code.required_if'    => __('validation.country_code.required_if'),
        ];

        $validator = Validator::make($request->all(), [
            "device_token"  => "nullable|string",

            "email_or_phone" => "required_without:national_id|string",
            "national_id"    => "required_without:email_or_phone",

            "password" => [
                "required",
                "confirmed",
                "min:8",
                "regex:/[A-Z]/",
                "regex:/[a-z]/",
                "regex:/[0-9]/",
                "regex:/[@$!%*#?&]/",
            ],

            "country_code" => "required_if:email_or_phone," . (
                $this->isPhone($request->email_or_phone)
                ? $request->email_or_phone
                : ''
            ),

        ], $messages);


        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }
        if ($request->filled('national_id')) {
            $user = Employee::where('national_id', $request->national_id)
                ->first();
            $loginMethod = 'national_id';
        } else {
            $isEmail = filter_var($request->email_or_phone, FILTER_VALIDATE_EMAIL);
            $loginMethod = $isEmail ? 'email' : 'phone';
            $user = $isEmail
                ? Employee::where('email', $request->email_or_phone)->first()
                : Employee::where('phone_number', $request->email_or_phone)
                ->where('country_code', $request->country_code)
                ->first();
        }

        if (!$user) {
            $errorMessage = $request->filled('national_id')
                ? __('validation.nationalIdDoesntExist')
                : ($isEmail ? __('validation.emailDoesntExist') : __('validation.phoneDoesntExist'));
            $errorField = $request->filled('national_id') ? 'national_id' : 'email_or_phone';

            return respondError(($lang == 'en' ? 'User Not Found.' : 'لم يتم العثور على المستخدم.'), 404, [
                $errorField => [$errorMessage]
            ]);
        }

        if (!$user->is_first_login) {
            return RespondWithErrorMsg(__('validation.passwordAlreadySet'));
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->is_first_login = false;
        $user->save();
        activity('employee-auth-' . $user->flag)
            ->causedBy($user)
            ->performedOn($user)

            ->event('firstLoginPassword')
            ->withProperties([
                'ip' => request()->ip(),
                'flag' => $user->flag,
                'type' => 'employee has been set his new password at ' . now()
            ])
            ->log('Employee firstLoginPassword in');
        return RespondWithSuccessMsg($lang == 'en'
            ? 'Password set successfully. You can now log in with your new password.'
            : 'تم تعيين كلمة المرور بنجاح. يمكنك الآن تسجيل الدخول باستخدام كلمة المرور الجديدة.');
    }

    public function resetPassword(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $messages = [
            'email_or_phone.required' => __('validation.email_or_phone.required'),
            'password.required' => __('validation.password.required'),
            'password.confirmed' => __('validation.password_confirmation'),
            'country_code.required_if' => __('validation.country_code.required_if'),
        ];

        $validator = Validator::make($request->all(), [
            "email_or_phone" => "required_without:national_id|string",
            "national_id" => "required_without:email_or_phone",
            "password" => "required|confirmed",
            "country_code" => "required_if:email_or_phone," . ($this->isPhone($request->email_or_phone) ? $request->email_or_phone : ''),
        ], $messages);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        // Identify user
        if ($request->filled('national_id')) {
            $user = Employee::where('national_id', $request->national_id)->first();
        } else {
            $isEmail = filter_var($request->email_or_phone, FILTER_VALIDATE_EMAIL);
            $user = $isEmail
                ? Employee::where('email', $request->email_or_phone)->first()
                : Employee::where('phone_number', $request->email_or_phone)
                ->where('country_code', $request->country_code)
                ->first();
        }

        if (!$user) {
            $errorMessage = $request->filled('national_id')
                ? __('validation.nationalIdDoesntExist')
                : ($isEmail ? __('validation.emailDoesntExist') : __('validation.phoneDoesntExist'));
            $errorField = $request->filled('national_id') ? 'national_id' : 'email_or_phone';

            return respondError(($lang == 'en' ? 'User Not Found.' : 'لم يتم العثور على المستخدم.'), 404, [
                $errorField => [$errorMessage]
            ]);
        }
        // Prevent reusing the same old password
        if (Hash::check($request->password, $user->password)) {
            $errorField = 'password';
            $errorMessage = $lang == 'en'
                ? 'The new password must be different from the old password.'
                : 'يجب أن تكون كلمة المرور الجديدة مختلفة عن كلمة المرور القديمة.';
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, [
                $errorField => [$errorMessage]
            ]);
        }
        // Reset password
        $user->password = Hash::make($request->password);
        // $user->is_first_login = false;
        $user->save();
        activity('employee-auth-' . $user->flag)
            ->causedBy($user)
            ->performedOn($user)

            ->event('resetpassword')
            ->withProperties([
                'ip' => request()->ip(),
                'flag' => $user->flag,
                'type' => 'employee has been reset password at ' . now()
            ])
            ->log('Employee resetpassword in');
        return RespondWithSuccessMsg($lang == 'en'
            ? 'Password reset successfully. You can now log in with your new password.'
            : 'تمت إعادة تعيين كلمة المرور بنجاح. يمكنك الآن تسجيل الدخول باستخدام كلمة المرور الجديدة.');
    }

    public function changePassword(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $messages = [
            'password.required'   => __('validation.newPasswordRequired'),
            'password.confirmed'  => __('validation.password_confirmation'),
            'password.min'        => __('validation.password.min'),
        ];

        $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed|min:6',
        ], $messages);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();

            // ✅ Move the "confirmed" error to password_confirmation
            if (isset($errors['password'])) {
                foreach ($errors['password'] as $key => $error) {
                    if (
                        str_contains($error, __('validation.password_confirmation')) ||
                        str_contains(strtolower($error), 'confirmation')
                    ) {
                        // Keep Arabic/English translation text for password_confirmation
                        $errors['password_confirmation'][] = $error;
                        unset($errors['password'][$key]);
                    }
                }

                // If password is empty after removing confirmation
                if (empty($errors['password'])) {
                    unset($errors['password']);
                }
            }

            // ✅ Replace each password error message with its validation key (not translation)
            $formattedErrors = [];
            foreach ($errors as $field => $messagesArr) {
                $formattedErrors[$field] = [];
                foreach ($messagesArr as $msg) {
                    if ($field === 'password' && str_contains($msg, __('validation.password.min'))) {
                        $formattedErrors[$field][] = 'validation.password.min';
                    } elseif ($field === 'password' && str_contains($msg, __('validation.password.required'))) {
                        $formattedErrors[$field][] = 'validation.password.required';
                    } else {
                        // Keep original message for others (like confirmation)
                        $formattedErrors[$field][] = $msg;
                    }
                }
            }

            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $formattedErrors
            );
        }



        $user = auth('employee')->user();
        //   if ($user->employee_status_id !=) {
        //             return respondError(($lang == 'en' ? 'New password must be different from the current one.' : 'يجب أن تكون كلمة المرور الجديدة مختلفة عن الحالية.'), 400);
        //         }
        // Check if new password is same as old one
        if (Hash::check($request->password, $user->password)) {
            return respondError(($lang == 'en' ? 'New password must be different from the current one.' : 'يجب أن تكون كلمة المرور الجديدة مختلفة عن الحالية.'), 400);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();
        $user->tokens()->delete();
        activity('employee-auth-' . $user->flag)
            ->causedBy($user)
            ->performedOn($user)

            ->event('changepassword')
            ->withProperties([
                'ip' => request()->ip(),
                'flag' => $user->flag,
                'type' => 'employee has been change passwordat ' . now()
            ])
            ->log('Employee changepassword in');
        // return RespondWithSuccessMsg($lang == 'en'
        //     ? 'Password changed successfully.'
        //     : 'تم تغيير كلمة المرور بنجاح.');
        return respondSuccess($lang == 'en'
            ? 'Password changed successfully.'
            : 'تم تغيير كلمة المرور بنجاح.', null);
    }
}
