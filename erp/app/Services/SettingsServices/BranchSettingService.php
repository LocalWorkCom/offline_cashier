<?php

namespace App\Services\SettingsServices;

use Illuminate\Http\Request;
use App\Models\BranchSetting;
use App\Models\PaymentPolicies;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BranchSettingService
{
    public function index(Request $request, $checkToken)
    {
        $lang = app()->getLocale();

        // Check token validity
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $query = BranchSetting::with('branch');

        $guard = getAuthenticatedGuard();
        $user  = auth($guard)->user();

        if ($guard === 'employee') {
            // dd( $query->get());
            $flags = ['waiter', 'cashier', 'customer_service', 'branch manager'];

            if (in_array($user->flag, $flags)) {

                $branch_id = $user->branch_id;
                if ($branch_id || $user->hasRole('Branch_Manager')) {
                    $query->where('branch_id', $branch_id);
                    // dd($user->flag);
                }
            }
        } elseif ($guard === 'admin') {
            //  dd( $query->get());

            if ($user && $user->hasRole('Branch Manager')) {
                $branch_id = getBranchManagerID();
                if ($branch_id) {
                    $query->where('branch_id', $branch_id);
                }
            }
        }

        // Fetch branch settings with the branch relationship
        $branchSettings = $query;

        return  $branchSettings;
    }

    public function add(Request $request)
    {

        try {
            $lang = $request->header('lang', 'en');

            // Validation rules
            $rules = [
                'branch_id' => 'required|exists:branches,id',

                'deposit_without_order_deduction_policy' => 'required|in:none,full,part',
                'deposit_without_order_deduction_percentage' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->deposit_without_order_deduction_policy != 'none' && $value <= 0) {
                            $fail(__('validation.deposit_without_order_must_be_greater'));
                        }
                    }
                ],

                'deposit_with_order_deduction_policy' => 'required|in:none,full,part',
                'deposit_with_order_deduction_percentage' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->deposit_with_order_deduction_policy != 'none' && $value <= 0) {
                            $fail(__('validation.deposit_with_order_must_be_greater'));
                        }
                    }
                ],

                'full_paid_order_deduction_policy' => 'required|in:none,full,part',
                'full_paid_order_deduction_percentage' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->full_paid_order_deduction_policy != 'none' && $value <= 0) {
                            $fail(__('validation.full_paid_order_must_be_greater'));
                        }
                    }
                ],

                'alert_before_arrival_minutes' => 'required|integer|min:0',
                'alert_after_arrival_minutes' => 'required|integer|min:0',
                'table_session_minutes' => 'required|integer|min:0',
                'takeaway_session_minutes' => 'required|integer|min:0',
                'capacity_takeaway' => 'required|integer|min:0',

                'table_reservation_deposit' => 'required|integer|min:0',
                'order_reservation_deposit' => 'required|numeric|min:0|max:100',

                'table_cancelation_time_allowed' => 'nullable|numeric|min:0',
                'takeaway_deposit_value_if_1' => 'nullable|numeric|min:0|max:100',

                'full_payment_preparation_setting' => 'required|boolean',
                'deposit_preparation_setting' => 'required|boolean',
                'no_payment_preparation_setting' => 'required|boolean',
            ];

            $customMessages = [
                'takeaway_deposit_value_if_1.required' => __('validation.takeaway_deposit_required'),
                'table_cancelation_time_allowed.required' => __('validation.table_cancelation_time_required'),
                'deposit_without_order_deduction_percentage.required_if' => __('validation.percentage_required_when_not_none'),
                'deposit_with_order_deduction_percentage.required_if' => __('validation.percentage_required_when_not_none'),
                'full_paid_order_deduction_percentage.required_if' => __('validation.percentage_required_when_not_none'),
            ];

            $existingSetting = BranchSetting::where('branch_id', $request->branch_id)
                ->whereNull('deleted_at') // Explicitly check for non-deleted records
                ->first();
            if ($existingSetting) {
                return respondError(($lang == 'en' ? 'branch_setting_already_exists.' : 'إعدادات الفرع موجودة بالفعل'), 400);
            }

            $validator = Validator::make($request->all(), $rules, $customMessages);
            // Set custom attribute names for more readable error messages
            $validator->setAttributeNames([
                'takeaway_deposit_value_if_1' => __('validation.attributes.takeaway_deposit_value_if_1'),
                'table_cancelation_time_allowed' => __('validation.attributes.table_cancelation_time_allowed'),
                'deposit_without_order_deduction_percentage' => __('validation.attributes.deposit_without_order_deduction_percentage'),
                'deposit_with_order_deduction_percentage' => __('validation.attributes.deposit_with_order_deduction_percentage'),
                'full_paid_order_deduction_percentage' => __('validation.attributes.full_paid_order_deduction_percentage'),
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


            // Set default values for optional fields if empty
            $request->merge([
                'table_cancelation_time_allowed' => $request->table_cancelation_time_allowed ?? 0,
                'takeaway_deposit_value_if_1' => $request->takeaway_deposit_value_if_1 ?? 0,
            ]);

            // Ensure percentage values are 0 if policy is 'none'
            if ($request->deposit_without_order_deduction_policy === 'none') {
                $request->merge(['deposit_without_order_deduction_percentage' => 0]);
            }

            if ($request->deposit_with_order_deduction_policy === 'none') {
                $request->merge(['deposit_with_order_deduction_percentage' => 0]);
            }

            if ($request->full_paid_order_deduction_policy === 'none') {
                $request->merge(['full_paid_order_deduction_percentage' => 0]);
            }

            // Create new BranchSetting
            $branchSetting = new BranchSetting($request->only([
                'branch_id',
                'deposit_without_order_deduction_policy',
                'deposit_without_order_deduction_percentage',
                'deposit_with_order_deduction_policy',
                'deposit_with_order_deduction_percentage',
                'full_paid_order_deduction_policy',
                'full_paid_order_deduction_percentage',
                'alert_before_arrival_minutes',
                'alert_after_arrival_minutes',
                'table_session_minutes',
                'takeaway_session_minutes',
                'takeaway_deposit_value_if_1',
                'table_cancelation_time_allowed',
                'capacity_takeaway',
                'table_reservation_deposit',
                'order_reservation_deposit',
                'full_payment_preparation_setting',
                'no_payment_preparation_setting',
                'deposit_preparation_setting',
            ]));

            $branchSetting->created_by = authActionSave()['by'];
            $branchSetting->created_by_type = authActionSave()['type'];

            // Refresh the model to get any database defaults or changes
            $branchSetting->refresh();
            $branchSetting->save();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function update(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $branchSetting = BranchSetting::findOrFail($id);

        // Get payment policy based on branch and order type
        $paymentPolicy = PaymentPolicies::where('branch_id', $request->branch_id)
            ->when($request->order_type, function ($query) use ($request) {
                return $query->where('order_type', $request->order_type);
            })
            ->first();

        // Validation rules
        $rules = [

            'branch_id' => 'required|exists:branches,id',
            'deposit_without_order_deduction_policy' => 'required|in:none,full,part',
            'deposit_without_order_deduction_percentage' => [
                // 'nullable',
                'required_if:deposit_without_order_deduction_policy,part,full',
                'numeric',
                'min:0.1',
                'max:100',
            ],
            'deposit_with_order_deduction_policy' => 'required|in:none,full,part',
            'deposit_with_order_deduction_percentage' => [
                // 'nullable',
                'required_if:deposit_with_order_deduction_policy,part,full',
                'numeric',
                'min:0.1',
                'max:100',
            ],
            'full_paid_order_deduction_policy' => 'required|in:none,full,part',
            'full_paid_order_deduction_percentage' => [
                // 'nullable',
                'required_if:full_paid_order_deduction_policy,part,full',
                'numeric',
                'min:0.1',
                'max:100',
            ],
            'alert_before_arrival_minutes' => 'required|integer|min:0',
            'alert_after_arrival_minutes' => 'required|integer|min:0',
            'table_session_minutes' => 'required|integer|min:0',
            'takeaway_session_minutes' => 'required|integer|min:0',
            'capacity_takeaway' => 'required|integer|min:0',
            'table_reservation_deposit' => 'required|integer|min:0',
            'order_reservation_deposit' => 'required|numeric|min:0|max:100',
            'table_cancelation_time_allowed' => 'nullable|numeric|min:0',
            'takeaway_deposit_value_if_1' => 'nullable|numeric|min:0|max:100',
            'full_payment_preparation_setting' => 'required|boolean',
            'no_payment_preparation_setting' => 'required|boolean',
            'deposit_preparation_setting' => 'required|boolean',

        ];


        // Custom messages
        $customMessages = [
            'takeaway_deposit_value_if_1.required' => __('validation.takeaway_deposit_required'),
            'table_cancelation_time_allowed.required' => __('validation.table_cancelation_time_required'),
            'deposit_without_order_deduction_percentage.required_if' => __('validation.percentage_required_when_not_none'),
            'deposit_with_order_deduction_percentage.required_if' => __('validation.percentage_required_when_not_none'),
            'full_paid_order_deduction_percentage.required_if' => __('validation.percentage_required_when_not_none'),
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);

        $validator->setAttributeNames([
            'takeaway_deposit_value_if_1' => __('validation.attributes.takeaway_deposit_value_if_1'),
            'table_cancelation_time_allowed' => __('validation.attributes.table_cancelation_time_allowed'),
            'deposit_without_order_deduction_percentage' => __('validation.attributes.deposit_without_order_deduction_percentage'),
            'deposit_with_order_deduction_percentage' => __('validation.attributes.deposit_with_order_deduction_percentage'),
            'full_paid_order_deduction_percentage' => __('validation.attributes.full_paid_order_deduction_percentage'),
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

        // Handle default percentage if needed
        if (is_null($request->deposit_without_order_deduction_percentage) && in_array($request->deposit_without_order_deduction_policy, ['none'])) {
            $request->merge(['deposit_without_order_deduction_percentage' => 0.00]);
        }
        if (empty($request->table_cancelation_time_allowed)) {
            $request->merge(['table_cancelation_time_allowed' => 0.0]);
        }

        if (empty($request->takeaway_deposit_value_if_1)) {
            $request->merge(['takeaway_deposit_value_if_1' => 0.0]);
        }
        if ($request->deposit_without_order_deduction_policy === 'none') {
            $request->merge(['deposit_without_order_deduction_percentage' => 0]);
        }

        if ($request->deposit_with_order_deduction_policy === 'none') {
            $request->merge(['deposit_with_order_deduction_percentage' => 0]);
        }

        if ($request->full_paid_order_deduction_policy === 'none') {
            $request->merge(['full_paid_order_deduction_percentage' => 0]);
        }
        // Update model
        $data = $request->only(array_keys($rules));
        $branchSetting->update($data);

        $branchSetting->modified_by = authActionSave()['by'];
        $branchSetting->modified_by_type = authActionSave()['type'];


        $branchSetting->save();

        // Refresh the model to get updated attributes
        $branchSetting->refresh();
        return RespondWithSuccessRequest($lang, 1);
    }

    public function delete(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = BranchSetting::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            $branchSetting = BranchSetting::find($id);

            if (!$branchSetting) {
                return RespondWithBadRequestData($lang, 8);
            }

            $branchSetting->deleted_by = authActionSave()['by'];
            $branchSetting->deleted_by_type = authActionSave()['type'];
            $branchSetting->save();
            $branchSetting->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function restore(Request $request, $id)
    {
        try {
            $lang = app()->getLocale();
            $branchSetting = BranchSetting::withTrashed()->findOrFail($id);
            $branchSetting->restore();

            return ResponseWithSuccessData($lang, $branchSetting, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring payment branchSetting: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
