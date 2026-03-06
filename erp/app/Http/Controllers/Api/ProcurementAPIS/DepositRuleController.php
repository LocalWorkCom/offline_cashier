<?php

namespace App\Http\Controllers\Api\ProcurementAPIS;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Inventory\DepositRuleResource;
use App\Models\DepositRule;
use App\Models\PurchasingBudget;
use App\Services\ProcurementServices\DepositRuleService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DepositRuleController extends Controller
{
    protected $depositRule;

    public function __construct(DepositRuleService $depositRule)
    {
        $this->depositRule = $depositRule;
    }

    private function getAuthenticatedEmployee()
    {
        $employee = auth('employee')->user();
        return $employee;
    }

    public function index(Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            // Use service to get the query builder
            $query = $this->depositRule->index($request);

            $purchasingBudgets = paginateOrGetAll($query, $request, null, null);
            $result = DepositRuleResource::collection($purchasingBudgets['data'])
                ->map(function ($resource) use ($lang) {
                    return (new DepositRuleResource($resource, $lang))->resolve();
                });
            return ResponseWithSuccessDataPaginated($lang, ['data' => $result, 'meta' => $purchasingBudgets['meta']], 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve purchasing budgets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            $purchasingBudgetExists = DepositRule::where('id', $id)->exists();

            if (!$purchasingBudgetExists) {
                return respondError($lang === 'ar' ? 'الميزانية غير موجودة' : 'budget not found', 404);
            }

            // Use service to get the base query
            $query = $this->depositRule->show($request, $id);

        $responseData = new DepositRuleResource($query, $lang);

            return ResponseWithSuccessData($lang, $responseData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve payment method',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
        $systemModuleStatus = checkModuleActivation(3); //Finance module id

        // if ($systemModuleStatus) {
        //     return respondError($lang == 'en' ?  'This API is stopped. You can only be done from the finance module.' : 'هذه الوجهه متوقفه . يمكن الطلب من مدير الحسابات', 400);
        // }
        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                                'unique:deposit_rules,name_ar'
,
                'min:3',
                'max:50',
                'regex:/^[\pL\s\-\(\)]+$/u'
            ],
               'name_en' => [
                'required',
                'unique:deposit_rules,name_en',
                'string',
                'min:3',
                'max:50',
                'regex:/^[\pL\s\-\(\)]+$/u'
            ],
            'percentage' => 'required|integer|min:1|max:100',
            'applicable_to' => 'required|in:all,restricted',
            'vendor_type' => 'nullable|in:individual,company,local_market',
            'conditions' => 'nullable|string',
            'active' => 'nullable|boolean',
            'linked_high_value_rule_id' => 'nullable|exists:high_value_rules,id',

            'vendor_ids' => [
                'array',
                'required_if:applicable_to,restricted',
            ],
            'vendor_ids.*' => 'exists:vendors,id',
        ]);

        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }

        // Use service to handle creation
        $result = $this->depositRule->store($request);
        $result->load('highValueRule');
        $responseData = new DepositRuleResource($result);

        return ResponseWithSuccessData($lang, $responseData->toArray($request), 1);
    }

    public function update(Request $request, $id)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'ar');
            app()->setLocale($lang);
            $systemModuleStatus = checkModuleActivation(3); //Finance module id

            // if ($systemModuleStatus) {
            //     return respondError($lang == 'en' ?  'This API is stopped. You can only be done from the finance module.' : 'هذه الوجهه متوقفه . يمكن الطلب من مدير الحسابات', 400);
            // }
            $validator = Validator::make($request->all(), [
                'name_ar' => [
                    'required',
                    'string',
                    'min:3',
                    'max:50',
                    'regex:/^[\pL\s\-\(\)]+$/u',
                    Rule::unique('deposit_rules', 'name_ar')
                        ->ignore($id ?? null),
                ],
                  'name_en' => [
                    'required',
                    'string',
                    'min:3',
                    'max:50',
                    'regex:/^[\pL\s\-\(\)]+$/u',
                    Rule::unique('deposit_rules', 'name_en')
                        ->ignore($id ?? null),
                ],
                'percentage' => 'required|integer|min:1|max:100',
                'applicable_to' => 'required|in:all,restricted',
                'vendor_type' => 'nullable|in:individual,company,local_market',
                'conditions' => 'nullable|string',
                'active' => 'nullable|boolean',
                'linked_high_value_rule_id' => 'nullable|exists:high_value_rules,id',
                'vendor_ids' => [
                    'array',
                    'required_if:applicable_to,restricted',
                ],
                'vendor_ids.*' => 'exists:vendors,id',
            ]);
            if ($validator->fails()) {
                return respondError($lang === 'ar' ? 'خطأ في التحقق من الصحة' : 'Validation error', 400, $validator->errors());
            }

            $result = $this->depositRule->update($request, $id);
            $result->load('highValueRule');

            $responseData = new DepositRuleResource($result);


            return ResponseWithSuccessData($lang, $responseData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update payment method',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function deleteMultiple(Request $request)
    {
        $lang = $request->header('lang', 'en');
        app()->setLocale($lang);
        $systemModuleStatus = checkModuleActivation(3); //Finance module id

        // if ($systemModuleStatus) {
        //     return respondError($lang == 'en' ?  'This API is stopped. You can only be done from the finance module.' : 'هذه الوجهه متوقفه . يمكن الطلب من مدير الحسابات', 400);
        // }
        // Validate input
        $validator = Validator::make($request->all(), [
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:deposit_rules,id'
        ]);

        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }

        $ids = $request->ids;

        // Fetch the rules (only non-deleted ones)
        $rules = DepositRule::whereIn('id', $ids)->whereNull('deleted_at')->get();

        if ($rules->count() === 0) {
            return respondError(__('No valid records found to delete'), 404);
        }

        // Soft delete with deleted_by
        $deletedBy = authActionSave()['by'] ?? null;

        foreach ($rules as $rule) {
            $rule->deleted_by = $deletedBy;
            $rule->save();

            $rule->delete(); // Soft delete
        }

        return ResponseWithSuccessData($lang, null, 1);
    }
}
