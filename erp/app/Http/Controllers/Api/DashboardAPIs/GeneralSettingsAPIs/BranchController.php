<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Offer;
use App\Models\Branch;
use App\Models\Country;
use App\Models\Employee;
use App\Models\BranchTime;
use App\Traits\BranchTrait;
use App\Models\BranchCoupon;
use App\Models\BranchRegion;
use Illuminate\Http\Request;
use App\Events\BranchUpdates;
use App\Models\BranchDiscount;
use App\Models\FloorPartition;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Services\SettingsServices\BranchService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BranchController extends Controller
{
    use BranchTrait;
    protected $branchService;

    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }

    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');

            // Check authentication first
            $employee = auth('employee')->user();

            $query = Branch::with([
                'country',
                'city', // Add city relationship
                'businessActivity', // Add business activity relationship (you'll need to create this)
                'branchTimes',
                'branchRegions.regions',
                'employees' // Load employees to get details
            ])->withCount('employees'); // This gives you employees count

            // Filter branches based on user role if authenticated
            if ($employee) {
                // If user is Branch Manager, only show their branch
                $flags = ['waiter', 'cashier', 'customer_service', 'branch manager'];

                // Add branch manager filter
                if (in_array($employee->flag, $flags)) {
                    $branch_id = $employee->branch_id;
                    if ($branch_id || $employee->hasRole('Branch_Manager')) {
                        $query->where('id', $employee->branch_id);
                    }
                }
            }

            // Use the helper function for pagination
            $result = paginateOrGetAll($query, $request, null);

            // Transform the response
            if (isset($result['data'])) {
                $result['data'] = collect($result['data'])->map(function ($branch) {
                    $branchData = is_array($branch) ? $branch : $branch->toArray();

                    // Rename branch_times to time and branch_regions to region
                    $branchData['time'] = $branchData['branch_times'] ?? [];
                    $branchData['region'] = $branchData['branch_regions'] ?? [];

                    // Add employees count and details
                    $branchData['employees_count'] = $branchData['employees_count'] ?? 0;
                    $branchData['employees_details'] = $branchData['employees'] ?? [];

                    // City details are already included via the relationship
                    // Business activity details are already included via the relationship

                    unset($branchData['branch_times'], $branchData['branch_regions'], $branchData['employees']);
                    return $branchData;
                })->toArray();
            } else {
                // Handle non-paginated response
                $result = collect($result)->map(function ($branch) {
                    $branchData = is_array($branch) ? $branch : $branch->toArray();

                    $branchData['time'] = $branchData['branch_times'] ?? [];
                    $branchData['region'] = $branchData['branch_regions'] ?? [];

                    // Add employees count and details
                    $branchData['employees_count'] = $branchData['employees_count'] ?? 0;
                    $branchData['employees_details'] = $branchData['employees'] ?? [];

                    unset($branchData['branch_times'], $branchData['branch_regions'], $branchData['employees']);
                    return $branchData;
                })->toArray();
            }

            $result['data'] = collect($result['data'])->map(function($branch) use($lang) {
                $branch['is_active'] = $branch['is_active'] ? ($lang == 'en' ? 'Active' : 'نشط') : ($lang == 'en' ? 'Not Active' : 'غير نشط');
                return $branch;
            });

            return ResponseWithSuccessDataPaginated($lang, $result, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2); // Server error
        }
    }
    public function change_status(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = Branch::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            $active = 1;
            $branch = Branch::findOrFail($id);

            //is_default
            if ($branch->is_default == 1) {
                return response()->json([
                    'success' => false,
                    'data'   => ['is_active' => -1]
                ], 403);
            }

            if ($branch->is_active == 1) {
                $active = 0;
            }
            $branch->is_active = $active;
            $branch->modified_by = authActionSave()['by'];
            $branch->modified_by_type = authActionSave()['type'];
            $branch->save();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function sync(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = Branch::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            $response = $this->branchService->sync($id);
            $responseData = $response->original;
            $message = $responseData['message'];

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $lang = session()->get('locale');
        $country = Country::findOrFail($request->country_id);

        App::setLocale($lang);

        // Get country for phone validation

        // Validation
        $validator = Validator::make($request->all(), [
            'name_en' => 'required|string|max:255|unique:branches,name_en',
            'name_ar' => 'required|string|max:255|unique:branches,name_ar',
            'address_en' => 'required|string',
            'address_ar' => 'required|string',
            //'country_id' => 'required|integer|exists:countries,id',
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('employees', 'id')->where(function ($query) {
                    $query->whereIn('flag', ['employee', 'branch manager']);
                }),
            ],
            'latitute' => 'required|numeric|between:-90,90',
            'longitute' => 'required|numeric|between:-180,180',
            'country_id' => 'required|exists:countries,id',
            'phone' => 'required|numeric|regex:/^[0-9]{' . $country->length . '}$/',
            //'phone' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255',
            //'manager_name' => 'required|string|max:255',
            // 'opening_hour' => 'required',
            // 'closing_hour' => 'required',
            'has_kids_area' => 'required|in:true,false',
            'is_delivery' => 'required|in:true,false',
            'is_default' => 'required|in:true,false',
            'time.*.day' => 'integer',
            // 'time.*.opening_hour' => 'nullable|date_format:h:i A',
            // 'time.*.closing_hour' => 'nullable|date_format:h:i A',
            'time.*.cross_day' => 'nullable|in:true,false',
            'tax_application' => 'required|in:true,false',
            'coupon_application' => 'required|in:true,false',
            'tax_percentage' => [
                'required_if:tax_application,1',
                'numeric',
                'min:0',
                'max:100',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->tax_application == 1 && $value == 0) {
                        $fail(__('branch.TaxPercentage must be greater than 0 when tax application is set to 1.'));
                    }
                },
            ],
            'time_cancellation' => 'nullable|integer',
            'delivery_time' => 'nullable|integer',
            'service_fees' => 'nullable|numeric',
            'service_fees_type' => 'required_if:service_fees,!=,null|in:fixed,percentage',

            'code' => 'required|string',
            'street' => 'required|string',
            'buildingNumber' => 'required|numeric',
            'auto_close_chat' => 'required|numeric',
            'is_table_reservation' => 'required|in:true,false',
            'is_takeaway' => 'required|in:true,false',
            'is_live' => 'nullable|in:true,false',

            'delivery_fees' => 'nullable|numeric',
            'time' => 'required|array',
            'region' => 'required|array',
            // 'tax_apply' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ], 400);
        }


        // try {

        // $user_id = Auth::guard('api')->user()->id;

        // Unassign manager from other branch if needed
        if ($request->has('employee_id')) {
            $check_branch = Branch::where('employee_id', $request->employee_id)->first();
            if ($check_branch) {
                $check_branch->employee_id = null;
                $check_branch->save();
            }
        }

        // Create the branch
        $branch = Branch::create([
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'address_en' => $request->address_en,
            'address_ar' => $request->address_ar,
            'latitute' => $request->latitute,
            'longitute' => $request->longitute,
            'country_id' => $request->country_id,
            'city_id' => $request->city_id,
            'area_id' => $request->region_id,
            'buildingNumber' => $request->buildingNumber,
            'phone' => $request->phone,
            'email' => $request->email,
            'employee_id' => $request->employee_id,
            'manager_name' => $request->manager_name,
            'street' => $request->street,
            'opening_hour' => $request->opening_hour,
            'closing_hour' => $request->closing_hour,
            'has_kids_area' => $request->has_kids_area,
            'is_delivery' => $request->is_delivery,
            'is_default' => $request->is_default,
            'tax_application' => $request->tax_application,
            'coupon_application' => $request->coupon_application,
            'tax_percentage' => $request->tax_percentage,
            'time_cancellation' => $request->time_cancellation,
            'delivery_time' => $request->delivery_time,
            'service_fees' => $request->service_fees,
            'company_profile_setting_id' => $request->company_profile_setting_id ?? null,
            'service_fees_type' => $request->service_fees_type ?? null,
            'delivery_fees' => $request->delivery_fees,
            'tax_apply' => 1,
            'is_active' => $request->is_active ?? 1,
            'created_by' => authActionSave()['by'],
            'created_by_type' => authActionSave()['type'],
            'code' => $request->code,
            'auto_close_chat' => $request->auto_close_chat,
            'is_table_reservation' => $request->is_table_reservation,
            'is_takeaway' => $request->is_takeaway,
            'is_live' => $request->is_live ?? 0,
        ]);
        // Log::debug('Branch created', $branch->toArray());

        // Add branch times
        if ($request->time) {
            foreach ($request->time as $branch_time) {

                BranchTime::firstOrCreate(
                    ['branch_id' => $branch->id, 'day' => $branch_time['day']],
                    [
                        'opening_hour' => $branch_time['opening_hour'],
                        'closing_hour' => $branch_time['closing_hour'],
                        'cross_day' => $branch_time['cross_day'],
                        'created_by' => authActionSave()['by'],
                        'created_by_type' =>  authActionSave()['type'],
                    ]
                );
            }
        }

        // Add branch regions
        if ($request->region) {
            foreach ($request->region as $branch_region) {
                BranchRegion::firstOrCreate(
                    ['branch_id' => $branch->id, 'region_id' => $branch_region['region_id']],
                    [
                        'delivery_fees' => $branch_region['delivery_fees'],
                        'created_by' => authActionSave()['by'],
                        'created_by_type' => authActionSave()['type'],
                    ]
                );
            }
        }


        // // Add branches to menu
        $branche_ids = [$branch->id];
        AddBranchesMenu($branche_ids, $dish_id = 0, $menu_integrations=[]);

        // Handle branch manager assignment
        if ($request->has('employee_id')) {
            $employee_data = Employee::find($request->employee_id);
            if ($employee_data) {
                $employee_data->branch_id = $branch->id;
                $employee_data->flag = 'branch manager';
                $employee_data->save();

                if ($employee_data->user_id) {
                    $branchmanagerRoleapi = Role::firstOrCreate([
                        'name' => 'Branch_Manager',
                        'guard_name' => 'employee'
                    ]);
                    $branchmanagerRoledashboard = Role::firstOrCreate([
                        'name' => 'Branch Manager',
                        'guard_name' => 'admin'
                    ]);
                    $user = User::withTrashed()->find($employee_data->user_id);
                    if ($user) {
                        $user->flag = 'admin';
                        $user->deleted_at = null;
                        $user->is_active = 1;
                        $user->save();
                        $user->assignRole($branchmanagerRoledashboard);
                    }
                    $employee_data->assignRole($branchmanagerRoleapi);
                } else {
                    $user = new User();
                    $user->email = $employee_data->email;
                    $user->name = $employee_data->first_name . ' ' . $employee_data->last_name;
                    $user->password = Hash::make('123456');
                    $user->phone = $employee_data->phone_number;
                    $user->flag = 'admin';
                    $user->save();

                    $branchmanagerRole = Role::firstOrCreate(['name' => 'Branch Manager', 'guard_name' => 'admin']);
                    $branchmanagerRole2 = Role::firstOrCreate(['name' => 'Branch_Manager', 'guard_name' => 'employee']);

                    $employee_data->user_id = $user->id;
                    $employee_data->save();
                    $user->assignRole($branchmanagerRole);
                    $employee_data->assignRole($branchmanagerRole2);
                }
            }
        }

        // Handle default branch
        if ($request->is_default == 1) {
            Branch::where('is_default', 1)
                ->where('id', '!=', $branch->id)
                ->update(['is_default' => 0]);
        }
        // dd($branch);
        $branch->load(['branchTimes', 'branchRegions.regions']);

        // Transform the response
        $branchData = $branch->toArray();
        $branchData['time'] = $branchData['branch_times'] ?? [];
        $branchData['region'] = $branchData['branch_regions'] ?? [];
        unset($branchData['branch_times'], $branchData['branch_regions']);

        return ResponseWithSuccessData($lang, $branchData, 1);
        // } catch (\Exception $e) {
        //     Log::error('Error creating branch: ' . $e->getMessage());
        //     return RespondWithBadRequestData($lang, 2);
        // }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $branch = Branch::withCount('employees')->with([
            'country',
            'city', // Add city relationship
            'area', // Add city relationship
            'businessActivity', // Add business activity relationship (you'll need to create this)
            'creator',
            'employees',
            'deleter'
        ])->find($id);

        if (!$branch) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        // Load the relationships separately and manually add them to the response
        $branch->load(['branchTimes', 'branchRegions.regions']);

        // Manually transform the response
        $branchData = $branch->toArray();
        $branchData['time'] = $branchData['branch_times'] ?? [];
        $branchData['region'] = $branchData['branch_regions'] ?? [];

        // Remove the original relationship keys
        unset($branchData['branch_times'], $branchData['branch_regions']);

        return ResponseWithSuccessData($lang, $branchData, 1);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = Branch::where('id', $id)->exists();
            App::setLocale($lang);

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $branch = Branch::findOrFail($id);
            $user = auth()->user();

            // Check if user is HR Manager or HR Employee
            $isHRUser = $user && ($user->hasRole('HR_Manager') || $user->hasRole('HR_Employee'));

            // If HR user, only allow updating phone and email
            if ($isHRUser) {
                $validator = Validator::make($request->all(), [
                    'phone' => 'sometimes|numeric',
                    'email' => 'sometimes|nullable|string|email|max:255',
                    'address_en' => 'sometimes|nullable|string',
                    'address_ar' => 'sometimes|nullable|string',
                    'country_id' => 'sometimes|nullable|exists:countries,id',

                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'code' => 400,
                        'status' => false,
                        'message' => 'Validation Error.',
                        'data' => null,
                        'errorData' => $validator->errors(),
                        'validation_type' => true
                    ], 400);
                }

                // Update only allowed fields
                $updateData = [];
                if ($request->has('phone')) {
                    $updateData['phone'] = $request->phone;
                }
                if ($request->has('email')) {
                    $updateData['email'] = $request->email;
                }
                if ($request->has('address_en')) {
                    $updateData['address_en'] = $request->address_en;
                }
                if ($request->has('address_ar')) {
                    $updateData['address_ar'] = $request->address_ar;
                }
                if ($request->has('country_id')) {
                    $updateData['country_id'] = $request->country_id;
                }
                if (!empty($updateData)) {
                    $updateData['modified_by'] = authActionSave()['by'];
                    $updateData['modified_by_type'] = authActionSave()['type'];

                    $branch->update($updateData);
                }

                $branch->refresh();
                $branch->load(['branchTimes', 'branchRegions.regions']);

                // Transform the response
                $branchData = $branch->toArray();
                $branchData['time'] = $branchData['branch_times'] ?? [];
                $branchData['region'] = $branchData['branch_regions'] ?? [];
                unset($branchData['branch_times'], $branchData['branch_regions']);

                return ResponseWithSuccessData($lang, $branchData, 1);
            }

            // Original validation and update logic for non-HR users
            $country = Country::findOrFail($request->country_id);

            $validator = Validator::make($request->all(), [
                'name_ar' => [
                    'required',
                    'string',
                    Rule::unique('branches')->whereNull('deleted_at')->ignore($id)
                ],
                'name_en' => [
                    'required',
                    'string',
                    Rule::unique('branches')->whereNull('deleted_at')->ignore($id)
                ],
                'address_en' => 'required|string',
                'address_ar' => 'required|string',
                'latitute' => 'required|numeric|between:-90,90',
                'longitute' => 'required|numeric|between:-180,180',
                'country_id' => 'required|exists:countries,id',
                'employee_id' => [
                    'required',
                    'integer',
                    $tt = Rule::exists('employees', 'id')->where(function ($query) {
                        $query->whereIn('flag', ['employee', 'branch manager']);
                    }),
                ],                //'phone' => 'required|numeric|max:'.$country->length.'|min:'.$country->length.'|regex:/[0-9]{'.$country->length.'}/',
                'phone' => 'required|numeric|regex:/^[0-9]{' . $country->length . '}$/',
                'email' => 'nullable|string|email|max:255',
                // 'opening_hour' => 'required',
                // 'closing_hour' => 'required',
                'has_kids_area' => 'required|boolean',
                'is_delivery' => 'required|boolean',
                'is_default' => 'required|boolean',
                'time.*.day' => 'integer',
                // 'time.*.opening_hour' => 'nullable|date_format:h:i A',
                // 'time.*.closing_hour' => 'nullable|date_format:h:i A',
                'time.*.cross_day' => 'nullable|boolean',
                'tax_application' => 'required|boolean',
                'coupon_application' => 'required|boolean',
                'tax_percentage' => [
                    'required_if:tax_application,1',
                    'numeric',
                    'min:0',
                    'max:100',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->tax_application == 1 && $value == 0) {
                            $fail(__('branch.TaxPercentage must be greater than 0 when tax application is set to 1.'));
                        }
                    },
                ],
                'time_cancellation' => 'nullable|integer|min:0',
                'delivery_time' => 'nullable|integer|min:0',
                'service_fees' => 'nullable|numeric|min:0',
                'code' => 'required|string',
                'street' => 'required|string',
                'buildingNumber' => 'required|numeric',
                'auto_close_chat' => 'required|numeric',
                'is_table_reservation' => 'required|boolean',
                'is_takeaway' => 'required|boolean',
                'is_live' => 'nullable|boolean',
                'delivery_fees' => 'nullable|numeric|min:0',
                'region.*.delivery_fees' => 'nullable|numeric|min:0',
                //'tax_apply' => 'nullable|in:0,1',
                'area.*.region_id' => 'required|exists:areas,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => 'Validation Error.',
                    'data' => null,
                    'errorData' => $validator->errors(),
                    'validation_type' => true
                ], 400);
            }

            // Original update logic continues here...
            // [Keep all your existing non-HR update logic below]
            //unassign manager form other branch
            $check_branch = Branch::where('employee_id', $request->employee_id)->first();
            if ($check_branch) {
                if ($check_branch->id != $id) {
                    $check_branch->employee_id = null;
                    $check_branch->save();
                }
            }
            // $manager_name = $this->employeeDetails($request->employee_id);
            // dd( $request->is_default, $branch->is_default);
            if($branch->is_default == true && $request->is_default == false){
                return RespondWithBadRequestIsDefaultNoChange();
            }
            $branch->update([
                'name_en' => $request->name_en,
                'name_ar' => $request->name_ar,
                'address_en' => $request->address_en,
                'address_ar' => $request->address_ar,
                'latitute' => $request->latitute,
                'longitute' => $request->longitute,
                'country_id' => $request->country_id,
                'buildingNumber' => $request->buildingNumber,
                'city_id' => $request->city_id,
                'area_id' => $request->region_id,
                'phone' => $request->phone,
                'email' => $request->email,
                // 'employee_id' => $request->employee_id,
                'street' => $request->street,

                //'manager_name' => $manager_name->first_name,
                'opening_hour' => $request->opening_hour,
                'closing_hour' => $request->closing_hour,
                'has_kids_area' => $request->has_kids_area,
                'is_delivery' => $request->is_delivery,
                'is_default' => $request->is_default,
                'tax_application' => $request->tax_application,
                'coupon_application' => $request->coupon_application,
                'tax_percentage' => $request->tax_percentage,
                'time_cancellation' => $request->time_cancellation,
                'delivery_time' => $request->delivery_time,
                'service_fees' => $request->service_fees,
                'company_profile_setting_id' => $request->company_profile_setting_id,
                'service_fees_type' => $request->service_fees_type,
                'delivery_fees' => $request->delivery_fees,
                'tax_apply' => 1,
                //'tax_apply' => $request->tax_apply,
                'is_active' => $request->is_active,
                'modified_by' => authActionSave()['by'],
                'modified_by_type' => authActionSave()['type'],
                'code' => $request->code,
                'auto_close_chat' => $request->auto_close_chat,
                'is_table_reservation' => $request->is_table_reservation,
                'is_takeaway' => $request->is_takeaway,
                'is_live' => $request->is_live,
            ]);

            if ($request->is_default == 1) {
                $check_branch_default = Branch::where('is_default', 1)->where('id', '!=', $id)->first();
                if ($check_branch_default) {
                    $check_branch_default->is_default = 0;
                    $check_branch_default->save();
                }
            }

            if ($request->has('time')) {
                $timeIds = [];

                foreach ($request->time as $branch_time) {
                    if (isset($branch_time['id']) && $branch_time['id']) {
                        // Update existing time
                        $time = BranchTime::where('id', $branch_time['id'])
                            ->where('branch_id', $branch->id)
                            ->first();

                        if ($time) {
                            $time->update([
                                'day' => $branch_time['day'],
                                'opening_hour' => $branch_time['opening_hour'],
                                'closing_hour' => $branch_time['closing_hour'],
                                'cross_day' => $branch_time['cross_day'] ?? false,
                                'modified_by' => authActionSave()['by'],
                                'modified_by_type' => authActionSave()['type']
                            ]);
                            $timeIds[] = $time->id;
                        }
                    } else {
                        // Create new time
                        $time = BranchTime::create([
                            'branch_id' => $branch->id,
                            'day' => $branch_time['day'],
                            'opening_hour' => $branch_time['opening_hour'],
                            'closing_hour' => $branch_time['closing_hour'],
                            'cross_day' => $branch_time['cross_day'] ?? false,
                            'created_by' => authActionSave()['by'],
                            'created_by_type' => authActionSave()['type']
                        ]);
                        $timeIds[] = $time->id;
                    }
                }

                // Delete times that weren't included in the request
                BranchTime::where('branch_id', $branch->id)
                    ->whereNotIn('id', $timeIds)
                    ->update([
                        'deleted_by' => authActionSave()['by'],
                        'deleted_by_type' => authActionSave()['type']
                    ]);

                BranchTime::where('branch_id', $branch->id)
                    ->whereNotIn('id', $timeIds)
                    ->delete();
            }

            // Handle branch regions - FIXED
            if ($request->has('region')) {
                $regionIds = [];

                foreach ($request->region as $branch_region) {
                    if (isset($branch_region['id']) && $branch_region['id']) {
                        // Update existing region
                        $region = BranchRegion::where('id', $branch_region['id'])
                            ->where('branch_id', $branch->id)
                            ->first();

                        if ($region) {
                            $region->update([
                                'region_id' => $branch_region['region_id'],
                                'delivery_fees' => $branch_region['delivery_fees'],
                                'modified_by' => authActionSave()['by'],
                                'modified_by_type' => authActionSave()['type']
                            ]);
                            $regionIds[] = $region->id;
                        }
                    } else {
                        // Create new region
                        $region = BranchRegion::create([
                            'branch_id' => $branch->id,
                            'region_id' => $branch_region['region_id'],
                            'delivery_fees' => $branch_region['delivery_fees'],
                            'created_by' => authActionSave()['by'],
                            'created_by_type' => authActionSave()['type']
                        ]);
                        $regionIds[] = $region->id;
                    }
                }

                // Delete regions that weren't included in the request
                BranchRegion::where('branch_id', $branch->id)
                    ->whereNotIn('id', $regionIds)
                    ->update([
                        'deleted_by' => authActionSave()['by'],
                        'deleted_by_type' => authActionSave()['type']
                    ]);

                BranchRegion::where('branch_id', $branch->id)
                    ->whereNotIn('id', $regionIds)
                    ->delete();
            }

            $employee_data = Employee::find($branch->employee_id);
            $branchmanagerRoleapi = Role::firstOrCreate([
                'name' => 'Branch_Manager',
                'guard_name' => 'employee'
            ]);
            $branchmanagerRoledashboard = Role::firstOrCreate([
                'name' => 'Branch Manager',
                'guard_name' => 'admin'
            ]);

            if ($employee_data) {
                $employee_user_old = User::find($employee_data->user_id);
                $employee_data->branch_id = null;
                $employee_data->flag = 'employee';
                $employee_data->save();
                if ($employee_user_old) {
                    $employee_data->syncRoles([]);
                    $employee_user_old->syncRoles([]);
                    // $employee_user_old->delete();
                }
            }

            if ($request->employee_id != $branch->employee_id) {
                $new_employee = Employee::find($request->employee_id);

                if ($new_employee->user_id != null) {
                    $new_employee->flag = 'branch manager';
                    $new_employee->save();
                    $user = User::withTrashed()->find($new_employee->user_id);
                    if ($user) {
                        $user->flag = 'admin';
                        $user->deleted_at = null;
                        $user->is_active = 1;
                        $user->save();
                        $user->assignRole($branchmanagerRoledashboard);
                    }
                    $new_employee->assignRole($branchmanagerRoleapi);
                } else {
                    $user = new User();
                    $user->email = $new_employee->email;
                    $user->name = $new_employee->first_name . ' ' . $new_employee->last_name;
                    $user->password = Hash::make('123456'); //ask for how to send pass to manager and give him update pass
                    $user->phone = $new_employee->phone_number;
                    $user->flag = 'admin';
                    $user->save();
                    if ($user) {
                        $employee_user = Employee::find($new_employee->id);
                        $employee_user->user_id = $user->id;
                        $employee_user->branch_id = $branch->id;
                        $employee_user->flag = 'branch manger';
                        $employee_user->save();
                        $user->assignRole($branchmanagerRoledashboard);
                        $new_employee->assignRole($branchmanagerRoleapi);
                    }
                }

                $new_employee->branch_id = $branch->id;
                $new_employee->save();
            }

            $branch->employee_id = $request->employee_id;
            $branch->save();
            $branch->refresh();
            $branch->load(['branchTimes', 'branchRegions.regions']);

            // Transform the response
            $branchData = $branch->toArray();
            $branchData['time'] = $branchData['branch_times'] ?? [];
            $branchData['region'] = $branchData['branch_regions'] ?? [];
            unset($branchData['branch_times'], $branchData['branch_regions']);

            $branch_regions = BranchRegion::where('branch_id', $branch->id)->get();

            $data = [
                'tax_percentage' => $branch->tax_percentage,
                'tax_application' => $branch->tax_application,
                'coupon_application' => $branch->coupon_application,
                'service_fees' => $branch->service_fees,
                'service_fees_type' => $branch->service_fees_type,
                'delivery_fees' => $branch_regions->map(fn($row) => [
                    'region_id' => $row->region_id,
                    'region_name' => $row->regions->name ?? null,
                    'delivery_fees' => (float) $row->delivery_fees,
                ])->all()
            ];
            broadcast(new BranchUpdates($branch->id, $data));

            return ResponseWithSuccessData($lang, $branchData, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Soft delete the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = Branch::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $branch = Branch::where('id', $id)
                ->with('branchCoupons.orders', function ($query) use ($id) {
                    $query->where('branch_id', $id);
                })
                ->with('branchDiscounts.orders', function ($query) use ($id) {
                    $query->where('branch_id', $id);
                })
                ->first();

            // Check if branch is default
            if ($branch->is_default == 1) {
                return RespondWithBadRequestIsDefault();
            }

            // Check for employees
            if ($branch->employess()->exists()) {
                return RespondWithBadRequestNotHavePermeation();
            }

            // Check for orders
            if ($branch->orders()->exists()) {
                return RespondWithBadRequestNotHavePermeation();
            }

            // Check for cashier machines
            if ($branch->cashierMachines()->exists()) {
                return RespondWithBadRequestNotHavePermeation();
            }

            // Check for coupon orders
            $coupon_orders = $branch->branchCoupons->flatMap(function ($coupon) {
                return $coupon->orders;
            });
            if ($coupon_orders->count() > 0) {
                return RespondWithBadRequestNotHavePermeation();
            } else {
                BranchCoupon::where('branch_id', $id)->delete();
            }

            // Check for discount orders
            $discount_orders = $branch->branchDiscounts->flatMap(function ($discount) {
                return $discount->orders;
            });
            if ($discount_orders->count() > 0) {
                return RespondWithBadRequestNotHavePermeation();
            } else {
                BranchDiscount::where('branch_id', $id)->delete();
            }

            // Handle offers
            $offers_ids = $branch->getOffers()->pluck('id');
            $offer_order_details = $branch->orders->flatMap(function ($details) use ($offers_ids) {
                return $details->orderDetails->whereIn('offer_id', $offers_ids)->pluck('offer_id');
            });
            $delete_offer_branches = $branch->getOffers()->whereNotIn('id', $offer_order_details)
                ->where('branch_id', '!=', -1)
                ->values()
                ->pluck('id');
            if ($delete_offer_branches->isNotEmpty()) {
                Offer::whereIn('id', $delete_offer_branches)
                    ->update(['branch_id' => null]);
            }

            // Handle branch menus
            if ($branch->branchMenus()->exists()) {
                DeleteBranchMenu($id);
            }

            // Soft delete branch
            $branch->deleted_by = authActionSave()['by'];
            $branch->deleted_by_type = authActionSave()['type'];
            $branch->save();
            $branch->delete();

            // Handle branch times
            $branch_times = BranchTime::where('branch_id', $id)->get();
            if ($branch_times) {
                foreach ($branch_times as $branch_time) {
                    $branch_time->update([
                        'deleted_by' => authActionSave()['by'],
                        'deleted_by_type' => authActionSave()['type']
                    ]);
                    $branch_time->delete();
                }
            }

            // Handle region times
            $region_times = BranchRegion::where('branch_id', $id)->get();
            if ($region_times) {
                foreach ($region_times as $region_time) {
                    $region_time->update([
                        'deleted_by' => authActionSave()['by'],
                        'deleted_by_type' => authActionSave()['type']
                    ]);
                    $region_time->delete();
                }
            }

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    /**
     * Restore a soft-deleted branch.
     */
    public function restore(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $branch = Branch::withTrashed()->findOrFail($id);
            $branch->restore();
            return ResponseWithSuccessData($lang, $branch, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring branch: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function listBranchAndNearFilter_old(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            // Latitude and Longitude should either both be present, or the country, city, and area should be present
            'latitude' => ['nullable', 'numeric', 'regex:/^-?\d+(\.\d+)?$/', 'required_without:country_id,city_id,area_id'],
            'longitude' => ['nullable', 'numeric', 'regex:/^-?\d+(\.\d+)?$/', 'required_without:country_id,city_id,area_id'],
            'country_id' => ['nullable', 'exists:countries,id', 'required_without:latitude,longitude'],
            'city_id' => ['nullable', 'exists:cities,id', 'required_without:latitude,longitude'],
            'area_id' => ['nullable', 'exists:areas,id', 'required_without:latitude,longitude'],
        ], [
            'latitude.required_without' => __('validation.latitude_or_location.required'),
            'latitude.numeric' => __('validation.latitude.numeric'),
            'latitude.regex' => __('validation.latitude.regex'),
            'longitude.required_without' => __('validation.longitude_or_location.required'),
            'longitude.numeric' => __('validation.longitude.numeric'),
            'longitude.regex' => __('validation.longitude.regex'),
            'country_id.exists' => __('validation.country_id.exists'),
            'city_id.exists' => __('validation.city_id.exists'),
            'area_id.exists' => __('validation.area_id.exists'),
            'country_id.required_without' => __('validation.country_id.required_without'),
            'city_id.required_without' => __('validation.city_id.required_without'),
            'area_id.required_without' => __('validation.area_id.required_without'),
        ]);
        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }
        // Retrieve validated data
        $userLat = $request->query('latitude');
        $userLon = $request->query('longitude');
        $all = $request->query('all', 1);
        $searchName = $request->query('name');
        $countryId = $request->query('country_id');
        $cityId = $request->query('city_id');
        $areaId = $request->query('area_id');

        $data = [];

        // Determine the correct column for branch name based on language
        $nameColumn = ($lang === 'ar') ? 'branches.name_ar' : 'branches.name_en'; // Explicitly specify the table name
        $query = Branch::where('is_active', 1);

        // Filter by location if latitude and longitude are provided
        if ($userLat && $userLon) {
            $query->select('*')
                ->selectRaw("(6371 * acos(cos(radians($userLat))
                      * cos(radians(latitude))
                      * cos(radians(longitude) - radians($userLon))
                      + sin(radians($userLat))
                      * sin(radians(latitude)))) AS distance")
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('distance', 'asc');
        }
        // Filter by country, city, and area if provided
        elseif ($countryId && $cityId && $areaId) {
            $query->where('country_id', $countryId)
                ->where('city_id', $cityId)
                ->where('area_id', $areaId);
        }

        // Filter by branch status if requested
        if ($request->has('branchesStatus')) {
            $query->where('is_open', 1);
        }

        // Fetch branches based on the conditions
        $branches = $query->get();

        if ($branches->isEmpty()) {
            return response()->json(null);
        }

        $branch = $branches->first();
        $services = [];

        // Determine the services offered by the branch
        if ($branch->is_delivery) {
            $services[] = __("header.deliveryTo");
        }
        if ($branch->is_takeaway) {
            $services[] = __("header.pickup");
        }
        if ($branch->is_table_reservation) {
            $services[] = __("header.reservation");
        }

        $branch->services = implode(' - ', $services);
        $branch->working_times = getBranchWorkingHours($branch->id);
        $branch->is_default = (bool)$branch->is_default;
        $branch->tax_apply = (bool)$branch->tax_apply;
        // Convert boolean values to 0 or 1
        $branch->is_open = (bool)$branch->is_open;
        $branch->is_delivery = (bool)$branch->is_delivery;
        $branch->is_takeaway = (bool)$branch->is_takeaway;
        $branch->is_table_reservation = (bool)$branch->is_table_reservation;
        $branch->is_active = (bool)$branch->is_active;
        $branch->has_kids_area = (bool)$branch->has_kids_area;
        $branch->tax_application = (bool)$branch->tax_application;
        $branch->coupon_application = (bool)$branch->coupon_application;
        $branch->is_live = (bool)$branch->is_live;

        $branch->makeHidden(['name_site', 'address_site']);
        $data['branch'] = $branch;
        return ResponseWithSuccessData($lang, $data, 1);
    }
}
