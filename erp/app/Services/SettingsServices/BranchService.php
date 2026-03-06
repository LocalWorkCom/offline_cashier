<?php

namespace App\Services\SettingsServices;

use App\Events\BranchUpdates;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use App\Models\Country;
use App\Models\BranchTime;
use App\Models\BranchCoupon;
use App\Models\BranchDiscount;
use App\Models\BranchRegion;
use App\Models\Area;
use App\Models\Offer;
use App\Models\PaymentPolicies;
use App\Models\PolicyPaymentReservation;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class BranchService
{
    public function index(Request $request)
    {
        $lang = session()->get('locale');

        $withTrashed = $request->query('withTrashed', false);
        $query = $withTrashed
            ? Branch::withTrashed()->with(['country', 'creator', 'deleter', 'floors'])
            : Branch::with(['country', 'creator', 'deleter', 'floors']);

        // Add branch manager filter
        if (auth('admin')->user()->hasRole('Branch Manager')) {
            $employee_id = getEmployeeID();
            if ($employee_id) {
                $query->where('employee_id', $employee_id);
            }
        }
        // Get paginated results
        $branches = $query->withCount('employees')->get();
        // Return formatted response with pagination metadata
        return ResponseWithSuccessData($lang, $branches, 1);
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
            //'employee_id' => 'unique:employees,id|integer|exists:employees,id',
            'latitute' => 'required|numeric|between:-90,90',
            'longitute' => 'required|numeric|between:-180,180',
            'country_id' => 'required|exists:countries,id',
            'phone' => 'required|numeric|regex:/^[0-9]{' . $country->length . '}$/',
            //'phone' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255',
            //'manager_name' => 'required|string|max:255',
            // 'opening_hour' => 'required',
            // 'closing_hour' => 'required',
            'has_kids_area' => 'required|boolean',
            'is_delivery' => 'required|boolean',
            'is_default' => 'required|boolean',
            'time.*.day' => 'integer',
            // 'time.*.opening_hour' => 'nullable|date_format:h:i A',
            // 'time.*.closing_hour' => 'nullable|date_format:h:i A',
            'time.*.cross_day' => 'nullable|boolean',
            'tax_application' => 'required|in:0,1',
            'coupon_application' => 'required|in:0,1',
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

            'code' => 'required|string',
            'street' => 'required|string',
            'buildingNumber' => 'required|numeric',
            'auto_close_chat' => 'required|numeric',
            'is_table_reservation' => 'required|in:0,1',
            'is_takeaway' => 'required|in:0,1',
            'is_live' => 'nullable|in:0,1',

            'delivery_fees' => 'nullable|numeric',
            'time' => 'required|array',
            'region' => 'required|array',
            // 'tax_apply' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
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

                    // 🔹 Log assignment for existing employee
                    logPermissionsAndRoleChanges('branch_manager_assigned', [
                        'employee_id'    => $employee_data->id,
                        'role_id'        => $branchmanagerRoleapi->id,
                        'permission_ids' => $employee_data->getAllPermissions()->pluck('id')->toArray(),
                        'extra_data'     => [
                            'branch_id' => $branch->id,
                            'reason'    => 'Employee promoted as branch manager (existing user)'
                        ]
                    ]);
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
                    // 🔹 Log assignment for new employee
                    logPermissionsAndRoleChanges('branch_manager_assigned', [
                        'employee_id'    => $employee_data->id,
                        'role_id'        => $branchmanagerRole2->id,
                        'permission_ids' => $employee_data->getAllPermissions()->pluck('id')->toArray(),
                        'extra_data'     => [
                            'branch_id' => $branch->id,
                            'reason'    => 'Employee promoted as branch manager (new user created)'
                        ]
                    ]);
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

        return ResponseWithSuccessData($lang, $branch, 1);
        // } catch (\Exception $e) {
        //     Log::error('Error creating branch: ' . $e->getMessage());
        //     return RespondWithBadRequestData($lang, 2);
        // }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $lang = session()->get('locale');
        try {
            $branch = Branch::with(['country', 'creator', 'deleter'])->findOrFail($id);
            return ResponseWithSuccessData($lang, $branch, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching branch: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function show_branch_region($city_id, $branch_id)
    {
        $lang = session()->get('locale');
        try {
            if ($branch_id != 0) {
                $cond = ['is_active' => 1, 'branch_id !=' => $branch_id];
            } else {
                $cond = ['is_active' => 1];
            }
            $branch_region_ids = BranchRegion::where($cond)->pluck('region_id')->toArray();

            $branch_regions = Area::where('city_id', $city_id)
                // ->when(!empty($branch_region_ids), function ($query) use ($branch_region_ids) {
                //     $query->whereNotIn('id', $branch_region_ids);
                // })
                ->get();
            // \Log::debug('Branch Region Data:', ['data' => $branch_region_ids->toArray()]);
            $data = [
                "branch_regions" => $branch_regions,
                "branch_region_ids" => $branch_region_ids,
            ];

            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching branches: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function sync($branch_id)
    {
        $lang = session()->get('locale');
        try {
            $branchId = array($branch_id);
            AddBranchesMenu($branchId, 0, $menu_integrations=[]);
            $branches =  Branch::with(['country', 'creator', 'deleter', 'floors'])->get();
            return ResponseWithSuccessData($lang, $branches, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching branch: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $lang = session()->get('locale');
        App::setLocale($lang);
        $branch = Branch::findOrFail($id);
        $country = Country::findOrFail($request->country_id);
        // Validation
        $validator = Validator::make($request->all(), [
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'address_en' => 'required|string',
            'address_ar' => 'required|string',
            'latitute' => 'required|numeric|between:-90,90',
            'longitute' => 'required|numeric|between:-180,180',
            'country_id' => 'required|exists:countries,id',
            //'employee_id' => 'required|integer|unique:employees,id,'.$branch->employee_id,
            'employee_id' => 'required|integer|exists:employees,id',
            //'phone' => 'required|numeric|max:'.$country->length.'|min:'.$country->length.'|regex:/[0-9]{'.$country->length.'}/',
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
            'tax_application' => 'required|in:0,1',
            'coupon_application' => 'required|in:0,1',
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

            'code' => 'required|string',
            'street' => 'required|string',
            'buildingNumber' => 'required|numeric',
            'auto_close_chat' => 'required|numeric',
            'is_table_reservation' => 'required|in:0,1',
            'is_takeaway' => 'required|in:0,1',
            'is_live' => 'nullable|in:0,1',

            'delivery_fees' => 'nullable|numeric',
            //'tax_apply' => 'nullable|in:0,1',
            'area.*.region_id' => 'required|exists:areas,id',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        if ($request->name_en != $branch->name_en) {
            $validator->sometimes('name_en', 'unique:branches,name_en,' . $branch->id, function () {
                return true;
            });
        }

        if ($request->name_ar != $branch->name_ar) {
            $validator->sometimes('name_ar', 'unique:branches,name_ar,' . $branch->id, function () {
                return true;
            });
        }
        //try {
        //unassign manager form other branch
        $check_branch = Branch::where('employee_id', $request->employee_id)->first();
        if ($check_branch) {
            if ($check_branch->id != $id) {
                $check_branch->employee_id = null;
                $check_branch->save();
            }
        }
        // $manager_name = $this->employeeDetails($request->employee_id);
        $branch->update([
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'address_en' => $request->address_en,
            'address_ar' => $request->address_ar,
            'latitute' => $request->latitute, // Matches the database field
            'longitute' => $request->longitute, // Matches the database field
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

        if ($request->time) {
            $timeIds = array_map(function ($item) {
                return $item['id'];
            }, array_filter($request->time, function ($value) {
                return $value['id'] !== null;
            }));

            $check_times = BranchTime::whereNotIn('id', $timeIds)->where('branch_id', $branch->id)->get();
            if ($check_times) {
                foreach ($check_times as $check_time) {
                    $check_time->update([
                        'deleted_by' => authActionSave()['by'],
                        'deleted_by_type' =>  authActionSave()['type'],
                    ]);
                    $check_time->delete();
                }
            }

            foreach ($request->time as $branch_time) {
                if ($branch_time['id']) {
                    $check_id = BranchTime::where('id', $branch_time['id'])->first();
                    if ($check_id) {
                        $branch_time = BranchTime::updateOrCreate(
                            ['id' => $branch_time['id'], 'branch_id' => $branch->id],
                            ['day' => $branch_time['day'], 'opening_hour' => $branch_time['opening_hour'], 'closing_hour' => $branch_time['closing_hour'], 'cross_day' => $branch_time['cross_day'], 'modified_by' => authActionSave()['by'], 'modified_by_type' => authActionSave()['type']],

                        );
                        $delete_old_time = BranchTime::where(['branch_id' => $branch->id, 'day' => $branch_time['day']])->where('id', '!=', $branch_time['id'])->first();
                        if ($delete_old_time) {
                            $delete_old_time->delete();
                        }
                    }
                } else {
                    $branch_time = BranchTime::firstOrCreate(
                        ['branch_id' => $branch->id, 'day' => $branch_time['day']],
                        ['opening_hour' => $branch_time['opening_hour'], 'closing_hour' => $branch_time['closing_hour'], 'cross_day' => $branch_time['cross_day'], 'created_by' => authActionSave()['by'], 'created_by_type' => authActionSave()['type']]
                    );
                }
            }
        }

        if ($request->region) {
            $regionIds = array_map(function ($item) {
                return $item['id'];
            }, array_filter($request->region, function ($value) {
                return $value['id'] !== null;
            }));

            $check_regions = BranchRegion::whereNotIn('id', $regionIds)->where('branch_id', $branch->id)->get();
            if ($check_regions) {
                foreach ($check_regions as $check_region) {
                    $check_region->update([
                        'deleted_by' => authActionSave()['by'],
                        'deleted_by_type' =>  authActionSave()['type'],
                    ]);
                    $check_region->delete();
                }
            }

            foreach ($request->region as $branch_region) {
                if ($branch_region['id']) {
                    $check_id = BranchRegion::where('id', $branch_region['id'])->first();
                    if ($check_id) {
                        $branch_region = BranchRegion::updateOrCreate(
                            ['id' => $branch_region['id'], 'branch_id' => $branch->id],
                            ['region_id' => $branch_region['region_id'], 'delivery_fees' => $branch_region['delivery_fees'], 'modified_by' => authActionSave()['by'], 'modified_by_type' => authActionSave()['type']]
                        );
                        $delete_old_region = BranchRegion::where(['branch_id' => $branch->id, 'region_id' => $branch_region['region_id']])->where('id', '!=', $branch_region['id'])->first();
                        if ($delete_old_region) {
                            $delete_old_region->delete();
                        }
                    }
                } else {
                    $branch_region = BranchRegion::firstOrCreate(
                        ['branch_id' => $branch->id, 'region_id' => $branch_region['region_id']],
                        ['delivery_fees' => $branch_region['delivery_fees'], 'created_by' => authActionSave()['by'], 'created_by_type' => authActionSave()['type']]
                    );
                }
            }
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
            // 🔹 Log old manager role removal
            logPermissionsAndRoleChanges('branch_manager_removed', [
                'employee_id' => $employee_data->id,
                'role_id'     => $branchmanagerRoleapi->id,
                'extra_data'  => [
                    'branch_id' => $branch->id,
                    'reason'    => 'Removed as branch manager'
                ]
            ]);
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
                // 🔹 Log new manager role assignment
                logPermissionsAndRoleChanges('branch_manager_assigned', [
                    'employee_id'    => $new_employee->id,
                    'role_id'        => $branchmanagerRoleapi->id,
                    'permission_ids' => $new_employee->getAllPermissions()->pluck('id')->toArray(),
                    'extra_data'     => [
                        'branch_id' => $branch->id,
                        'reason'    => 'Assigned as new branch manager'
                    ]
                ]);
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
                    // 🔹 Log new manager role assignment (fresh user case)
                    logPermissionsAndRoleChanges('branch_manager_assigned', [
                        'employee_id'    => $new_employee->id,
                        'role_id'        => $branchmanagerRoleapi->id,
                        'permission_ids' => $new_employee->getAllPermissions()->pluck('id')->toArray(),
                        'extra_data'     => [
                            'branch_id' => $branch->id,
                            'reason'    => 'Assigned as new branch manager (new user created)'
                        ]
                    ]);
                }
            }

            $new_employee->branch_id = $branch->id;
            $new_employee->save();
        }
        $branch->employee_id = $request->employee_id;
        $branch->save();
        $branch->refresh();
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
        broadcast(new BranchUpdates(
            $branch->id,
            $data
        ));
        return ResponseWithSuccessData($lang, $branch, 1);
        // } catch (\Exception $e) {
        //     Log::error('Error updating branch: ' . $e->getMessage());
        //     return RespondWithBadRequestData($lang, 2);
        // }
    }

    /**
     * Soft delete the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $lang = session()->get('locale');
        //try {
        $branch = Branch::where('id', $id)->with('branchCoupons.orders', function ($query) use ($id) {
            $query->where('branch_id', $id);
        })->with('branchDiscounts.orders', function ($query) use ($id) {
            $query->where('branch_id', $id);
        })->first();

        //is_default
        if ($branch->is_default == 1) {
            return RespondWithBadRequestIsDefault();
        }

        //employess
        if ($branch->employess()->exists()) {
            return RespondWithBadRequestNotHavePermeation();
        }

        //orders
        if ($branch->orders()->exists()) {
            return RespondWithBadRequestNotHavePermeation();
        }

        if ($branch->cashierMachines()->exists()) {
            return RespondWithBadRequestNotHavePermeation();
        }

        //coupon_orders
        $coupon_orders = $branch->branchCoupons->flatMap(function ($coupon) {
            return $coupon->orders; // This collects all orders from all coupons
        });
        if ($coupon_orders->count() > 0) {
            return RespondWithBadRequestNotHavePermeation();
        } else {
            $delete_coupon_branch = BranchCoupon::where('branch_id', $id)->delete();
        }

        //discount_orders
        $discount_orders = $branch->branchDiscounts->flatMap(function ($discount) {
            return $discount->orders; // This collects all orders from all coupons
        });
        if ($discount_orders->count() > 0) {
            return RespondWithBadRequestNotHavePermeation();
        } else {
            $delete_discount_branch = BranchDiscount::where('branch_id', $id)->delete();
        }

        //Offer
        $offers_ids = $branch->getOffers()->pluck('id');
        $offer_order_details = $branch->orders->flatMap(function ($details) use ($offers_ids) {
            return $details->orderDetails->whereIn('offer_id', $offers_ids)->pluck('offer_id');
        });
        $delete_offer_branches = $branch->getOffers()->whereNotIn('id', $offer_order_details)->where('branch_id', '!=', -1)->values()->pluck('id');
        if ($delete_offer_branches->isNotEmpty()) {
            Offer::whereIn('id', $delete_offer_branches)
                ->update(['branch_id' => null]);
        }

        //branchMenus
        if ($branch->branchMenus()->exists()) {
            DeleteBranchMenu($id);
        }

        $branch->update(['deleted_by' => auth('admin')->id()]);
        $branch->delete();

        $branch_times = BranchTime::where('branch_id', $id)->get();
        if ($branch_times) {
            foreach ($branch_times as $branch_time) {
                $branch_time->update(['deleted_by' => auth('admin')->id()]);
                $branch_time->delete();
            }
        }

        $region_times = BranchRegion::where('branch_id', $id)->get();
        if ($region_times) {
            foreach ($region_times as $region_time) {
                $region_time->update(['deleted_by' => auth()->id()]);
                $region_time->delete();
            }
        }

        return ResponseWithSuccessData($lang, null, 1);
        // } catch (\Exception $e) {
        //     Log::error('Error deleting branch: ' . $e->getMessage());
        //     return RespondWithBadRequestData($lang, 2);
        // }
    }

    /**
     * Restore a soft-deleted branch.
     */
    public function restore(Request $request, $id)
    {
        $lang = session()->get('locale');
        try {
            $branch = Branch::withTrashed()->findOrFail($id);
            $branch->restore();
            return ResponseWithSuccessData($lang, $branch, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring branch: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function employeeDetails($id)
    {
        $lang = session()->get('locale');
        $employee_details = Employee::where('id', $id)->first();
        if ($employee_details) {
            return $field = $employee_details;
        } else {
            return null;
        }
    }

    public function change_status($id)
    {
        $lang = session()->get('locale');
        try {
            $user_id =  Auth::guard('admin')->user()->id;
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
            $branch->modified_by = $user_id;
            $branch->save();
            return ResponseWithSuccessData($lang, $branch, 1);
        } catch (\Exception $e) {
            Log::error('Error deleting branch menu category: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function payment_policy($branch_id)
    {
        $lang = session()->get('locale');
        $paymentPolicies = PaymentPolicies::where('branch_id', $branch_id)->get();
        $results = $paymentPolicies->mapWithKeys(function ($policy) {
            $methods = [];

            if ($policy->no_payment_required == 1) {
                $methods[] = 'no_payment_required';
            }
            if ($policy->deposit_required == 1) {
                $methods[] = 'deposit_required';
            }
            if ($policy->full_payment_required == 1) {
                $methods[] = 'full_payment_required';
            }

            return [
                $policy->order_type => $methods,
            ];
        });
        return $results;
    }

    public function reservation_policy()
    {
        $lang = session()->get('locale');
        $paymentPolicies = PolicyPaymentReservation::first();
        $results = ['payment' => strip_tags($paymentPolicies->payment) ?? null, 'reservation' => strip_tags($paymentPolicies->reservation) ?? null];
        return $results;
    }
}
