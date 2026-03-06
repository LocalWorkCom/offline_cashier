<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Models\PaymentPolicies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Services\SettingsServices\BranchService;
use App\Services\SettingsServices\PaymentPoliciesService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentPoliciesController extends Controller
{
    protected $branchService;
    protected $paymentPoliciesService;

    public function __construct(BranchService $branchService, PaymentPoliciesService $paymentPoliciesService)
    {
        $this->branchService = $branchService;
        $this->paymentPoliciesService = $paymentPoliciesService;
    }
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        try {
            // Get the query builder from service
            $query = $this->paymentPoliciesService->index($request);

            // Handle pagination or get all
            $response = paginateOrGetAll($query, $request, null);

            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching payment policies: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);

        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|exists:branches,id',
            'order_type' => 'required|string|in:takeaway,delivery,dine-in,reservation_with_order,reservation_without_order',
            'invoice_count' => 'nullable|integer|min:0',
            'no_payment_required' => 'in:true,false',
            'deposit_required' => 'in:true,false',
            'full_payment_required' => 'in:true,false',
            'table_cancelation_value_type' => 'in:true,false',
        ]);

        $employeeType = authActionSave()['type'];

        if ($employeeType === 'employee') {
            $employee = auth('employee')->user();

            // Only apply branch check if user is branch manager
            if ($employeeType === 'employee') {
                $employee = auth('employee')->user();

                if ($employee && $employee->flag === 'branch manager') {
                    $validator->after(function ($validator) use ($employee, $request, $lang) {
                        if ((int) $employee->branch_id !== (int) $request->branch_id) {
                            $validator->errors()->add(
                                'branch_id',
                                __('validation.branch_manager_branch_only', [], $lang)
                            );
                        }
                    });
                }
            }
        }

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $policy = $this->paymentPoliciesService->store($request, null);

        return ResponseWithSuccessData($lang, $policy, 1);
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $policy = $this->paymentPoliciesService->show($id);

            // Hide the branch relationship and add branch_name
            $policy->makeHidden('branch');
            $policy->branch_name = $lang === 'ar' ? $policy->branch->name_ar : $policy->branch->name_en;

            return ResponseWithSuccessData($lang, $policy, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'سياسه الدفع غير موجودة' : 'payment policy not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching payment policy: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $policy = PaymentPolicies::findOrFail($id);

            app()->setLocale($lang);
            $validator = Validator::make($request->all(), [
                // 'id' => 'required|exists:payment_policies,id',
                'branch_id' => 'required|exists:branches,id',
                'order_type' => 'required|string|in:takeaway,delivery,dine-in,reservation_with_order,reservation_without_order',
                'invoice_count' => 'nullable|integer|min:0',
                'no_payment_required' => 'in:true,false',
                'deposit_required' => 'in:true,false',
                'full_payment_required' => 'in:true,false',
                'table_cancelation_value_type' => 'in:true,false',
            ]);

            $employeeType = authActionSave()['type'];

            if ($employeeType === 'employee') {
                $employee = auth('employee')->user();

                if ($employee && $employee->flag === 'branch manager') {
                    $validator->after(function ($validator) use ($employee, $request, $lang) {
                        if ((int) $employee->branch_id !== (int) $request->branch_id) {
                            $validator->errors()->add(
                                'branch_id',
                                __('validation.branch_manager_branch_only', [], $lang)
                            );
                        }
                    });
                }
            }

            if ($validator->fails()) {
                return respondError(
                    $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                    400,
                    $validator->errors()
                );
            }

            $policy = $this->paymentPoliciesService->update($request, $id);
            return ResponseWithSuccessData($lang, $policy, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? ' سياسه الدفع غير موجودة' : 'Payment Policy not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching recipe: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $response = $this->paymentPoliciesService->destroy($id, $lang);

            // If response is a JSON error (bad request, etc.), return it directly
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                return $response;
            }

            // Return success response
            $message = $lang === 'ar' ? 'تم حذف الفئة بنجاح' : 'Category deleted successfully';
            return ResponseWithSuccessData($lang, $message, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'فئات لأضافات غير موجودة' : 'addon caegory not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching recipe: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function policies_types(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $results = $this->branchService->payment_policy($request->branch_id);
        return ResponseWithSuccessData($lang, $results, 1);
    }

    public function policies_reservation(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $results = $this->branchService->reservation_policy();
        return ResponseWithSuccessData($lang, $results, 1);
    }
}
