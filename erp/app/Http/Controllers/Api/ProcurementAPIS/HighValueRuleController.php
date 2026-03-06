<?php

namespace App\Http\Controllers\Api\ProcurementAPIS;

use App\Http\Resources\Inventory\HighValueRuleResource;
use App\Services\ProcurementServices\HighValueRuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Models\HighValueRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HighValueRuleController extends Controller
{
    protected $highValueRuleService;

    public function __construct(HighValueRuleService $highValueRuleService)
    {
        $this->highValueRuleService = $highValueRuleService;
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
            $query = $this->highValueRuleService->index($request);

            $result = paginateOrGetAll($query, $request, null, null);
            $responseData = HighValueRuleResource::collection($result['data']);

            return ResponseWithSuccessDataPaginated(
                $lang,
                [
                    'data' => $responseData,
                    'meta' => $result['meta']
                ],
                1
            );
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

            $highValueRule = HighValueRule::where('id', $id)->exists();

            if (!$highValueRule) {
                return respondError($lang === 'ar' ? 'هذه القاعده غير موجوده' : 'High value rule not found', 404);
            }


            $query = $this->highValueRuleService->show($request, $id);
            $responseData = new HighValueRuleResource($query);


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
            'name_ar' => 'required|string|min:3|max:50|unique:high_value_rules,name_ar',
            'name_en' => 'required|string|min:3|max:50|unique:high_value_rules,name_en',
            'notes' => 'required|string',

            'financial_limit' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'DRR' => 'required|string',
            'max_deposit_percentage' => 'required|integer|min:1|max:100'
        ]);
        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }

        // Use service to handle creation
        $result = $this->highValueRuleService->store($request);

        $responseData = new HighValueRuleResource($result);

        return ResponseWithSuccessData($lang, $responseData, 1);
    }

    public function update(Request $request, $id)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');
            $highValueRule = HighValueRule::where('id', $id)->exists();
            $systemModuleStatus = checkModuleActivation(3); //Finance module id

            // if ($systemModuleStatus) {
            //     return respondError($lang == 'en' ?  'This API is stopped. You can only be done from the finance module.' : 'هذه الوجهه متوقفه . يمكن الطلب من مدير الحسابات', 400);
            // }
            if (!$highValueRule) {
                return respondError($lang === 'ar' ? 'هذه القاعده غير موجوده' : 'High value rule not found', 404);
            }

            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string|min:3|max:50|unique:high_value_rules,name_ar,' . $id,
                                'name_en' => 'required|string|min:3|max:50|unique:high_value_rules,name_en,' . $id,
            'notes' => 'required|string',

                'financial_limit' => 'required',
                'DRR' => 'required|string'
            ]);
            if ($validator->fails()) {
                return respondError($lang === 'ar' ? 'خطأ في التحقق من الصحة' : 'Validation error', 400, $validator->errors());
            }


            $result = $this->highValueRuleService->update($request, $id);

            $responseData = new HighValueRuleResource($result);


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
            'ids.*' => 'integer|exists:high_value_rules,id'
        ]);

        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }

        $ids = $request->ids;

        // Fetch the rules (only non-deleted ones)
        $rules = HighValueRule::whereIn('id', $ids)->whereNull('deleted_at')->get();

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
