<?php

namespace App\Http\Controllers\Api\ProcurementAPIS;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Http\Resources\Inventory\PurchasingBudgetResource;
use App\Models\Permission;
use App\Models\PurchasingBudget;
use App\Services\ProcurementServices\PurchasingBudgetService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PurchasingBudgetController extends Controller
{
    protected $purchasingBudgetService;

    public function __construct(PurchasingBudgetService $purchasingBudgetService)
    {
        $this->purchasingBudgetService = $purchasingBudgetService;
    }

    private function getAuthenticatedEmployee()
    {
        $employee = auth('employee')->user();
        return $employee;
    }

    public function budgetPermissionList(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        // List only the permissions you want to return
        $permissionNames = [
            'view purchasing-budget',
            'create purchasing-budget',
            'update purchasing-budget',
            'view purchasing-budget-history',
        ];

        $permissions = Permission::whereIn('name', $permissionNames)
            ->get();
        $permissions = Permission::whereIn('name', $permissionNames)
            ->get()
            ->map(function ($item) use ($lang) {
                return [
                    'id'   => $item->id,
                    'name' => $lang == 'en'
                        ? $item->name_en
                        : $item->name_ar
                ];
            });
        return response()->json([
            'status' => true,
            'data' => $permissions
        ]);
    }
    public function index(Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            // Use service to get the query builder
            $query = $this->purchasingBudgetService->index($request);

            $purchasingBudgets = paginateOrGetAll($query, $request, null, null);

            // Ensure we have a Collection (handles array or Collection)
            $items = collect($purchasingBudgets['data']);

            // General totals for all budgets combined, using the budget fields
            $generalTotals = [
                'totalDeposit'   => $items->sum(fn($budget) => floatval($budget->base_amount ?? 0) + floatval($budget->increase_amount ?? 0)),
                'totalDeduction' => $items->sum(fn($budget) => (floatval($budget->base_amount ?? 0) + floatval($budget->increase_amount ?? 0)) - floatval($budget->remaining_amount ?? 0)),
                'totalIncrease'  => $items->sum(fn($budget) => floatval($budget->increase_amount ?? 0)),
            ];

            // List of budgets (use resource to format)
            $result = PurchasingBudgetResource::collection($items)->resolve();

            // Combine general totals at the start of the object
            $data = (object) array_merge($generalTotals, [
                'list' => $result
            ]);

            return ResponseWithSuccessDataPaginated(
                $lang,
                ['data' => $data, 'meta' => $purchasingBudgets['meta']],
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

            $purchasingBudgetExists = PurchasingBudget::where('id', $id)->exists();

            if (!$purchasingBudgetExists) {
                return respondError($lang === 'ar' ? 'الميزانية غير موجودة' : 'budget not found', 404);
            }

            // Use service to get the base query
            $query = $this->purchasingBudgetService->show($request, $id);

            $responseData = new PurchasingBudgetResource($query);

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
            'month' => [
                'required',
                'integer',
                'between:1,12',
                Rule::unique('purchasing_budgets')
                    ->where(fn($q) => $q->where('year', $request->year))
                    ->ignore($id ?? null),
            ],
            'year' => [
                'required',
                'integer',
                'min:2000',
                function ($attribute, $value, $fail) use ($request, $lang) {
                    $currentYear = now()->year;
                    $currentMonth = now()->month;

                    $message = $lang === 'ar'
                        ? 'لا يمكن أن يكون الشهر/السنة المحددة في الماضي.'
                        : 'The selected month/year cannot be in the past.';

                    if ($value < $currentYear) {
                        return $fail($message);
                    }

                    if ($value == $currentYear && $request->month < $currentMonth) {
                        return $fail($message);
                    }
                }
            ],
            'base_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);


        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }

        // Use service to handle creation
        $result = $this->purchasingBudgetService->store($request);

        $responseData = new PurchasingBudgetResource($result);

        return ResponseWithSuccessData($lang, $responseData->toArray($request), 1);
    }

    public function update(Request $request, $id)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');
            $systemModuleStatus = checkModuleActivation(3); //Finance module id

            // if ($systemModuleStatus) {
            //     return respondError($lang == 'en' ?  'This API is stopped. You can only be done from the finance module.' : 'هذه الوجهه متوقفه . يمكن الطلب من مدير الحسابات', 400);
            // }
            $validator = Validator::make($request->all(), [
                'notes' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return respondError($lang === 'ar' ? 'خطأ في التحقق من الصحة' : 'Validation error', 400, $validator->errors());
            }

            $result = $this->purchasingBudgetService->update($request, $id);

            $responseData = new PurchasingBudgetResource($result);


            return ResponseWithSuccessData($lang, $responseData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update payment method',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function increaseBudgetAmount(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $this->getAuthenticatedEmployee();
        $lang = $request->header('lang', 'en');
        $budgetExists = PurchasingBudget::find($id);
        if (!$budgetExists) {
            return respondError($lang === 'ar' ? 'الميزانية غير موجودة' : 'budget not found', 404);
        }
        $validator = Validator::make($request->all(), [
            'increase_amount' => 'required|numeric|min:0.01',
        ]);
        if ($validator->fails()) {
            return respondError($lang === 'ar' ? 'خطأ في التحقق من الصحة' : 'Validation error', 400, $validator->errors());
        }

        $result = $this->purchasingBudgetService->increaseBudgetAmount($request, $budgetExists);
        return ResponseWithSuccessData($lang, $result, 1);
    }
    public function storeNotify(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $this->getAuthenticatedEmployee();
        $lang = $request->header('lang', 'en');
        $budgetExists = PurchasingBudget::find($id);
        if (!$budgetExists) {
            return respondError($lang === 'ar' ? 'الميزانية غير موجودة' : 'budget not found', 404);
        }
        $validator = Validator::make($request->all(), [
            'increase_ids' => 'required|array',
            'notes' => 'nullable|string',
            'reasone' => 'required|string',
            'check_notify' => 'required|boolean',
        ]);
        if ($validator->fails()) {
            return respondError($lang === 'ar' ? 'خطأ في التحقق من الصحة' : 'Validation error', 400, $validator->errors());
        }
        if ($request->notes) {
            $this->purchasingBudgetService->update($request, $id);
        }
        $result = $this->purchasingBudgetService->storeNotify($request);
        return ResponseWithSuccessData($lang, $result, 1);
    }
    public function historyBudget(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $this->getAuthenticatedEmployee();
        $lang = $request->header('lang', 'en');


        $result = $this->purchasingBudgetService->historyBudget();
        return ResponseWithSuccessData($lang, $result, 1);
    }
}
