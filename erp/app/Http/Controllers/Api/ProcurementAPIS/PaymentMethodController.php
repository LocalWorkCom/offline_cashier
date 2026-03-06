<?php

namespace App\Http\Controllers\Api\ProcurementAPIS;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Services\ProcurementServices\PaymentMethodService;
use App\Http\Resources\PaymentMethodResource;

class PaymentMethodController extends Controller
{
    protected $paymentMethodService;

    public function __construct(PaymentMethodService $paymentMethodService)
    {
        $this->paymentMethodService = $paymentMethodService;
    }

    private function getAuthenticatedEmployee()
    {
        $employee = auth('employee')->user();
        if (!$employee) {
            abort(response()->json(['message' => 'Unauthorized'], 401));
        }
        return $employee;
    }

    public function index(Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            // Use service to get the query builder
            $query = $this->paymentMethodService->index($request);

            // Apply additional filters from request
            if ($request->filled('payment_method_id')) {
                $query->where('id', $request->payment_method_id);
            }

            // Filter by name (search in both Arabic and English names)
            if ($request->filled('name')) {
                $searchTerm = $request->name;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name_ar', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('name_en', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Filter by type
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('from')) {
                $query->where('created_at', '>=', $request->from);
            }

            if ($request->filled('to')) {
                $query->where('created_at', '<=', $request->to);
            }

            if ($request->has('status')) {
                $status = $request->boolean('status');
                $query->where('status', $status);
            }

            // 📅 Date filter (today, yesterday, before_yesterday, this_week, this_month)
            $query = applyDateFilter($query, $request->date_filter);

            // 📄 Pagination or all results
            $paymentMethods = paginateOrGetAll($query, $request, null, null);
            $result = new PaymentMethodResource(collect($paymentMethods['data']), $lang);


            return ResponseWithSuccessDataPaginated($lang, ['data' => $result, 'meta' => $paymentMethods['meta']], 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve payment methods',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            $paymentMethodExists = PaymentMethod::where('id', $id)->exists();

            if (!$paymentMethodExists) {
                return respondError($lang === 'ar' ? 'لم يتم العثور على طريقة الدفع.' : 'Payment method not found', 404);
            }

            // Use service to get the base query
            $query = $this->paymentMethodService->index($request);

            $paymentMethod = $query->where('id', $id)->first();

            if (!$paymentMethod) {
                return respondError($lang === 'ar' ? 'لم يتم العثور على طريقة الدفع.' : 'Payment method not found', 404);
            }

            // Use the same Resource as index method
            $paymentMethodCollection = collect([$paymentMethod]);
            $responseData = new PaymentMethodResource($paymentMethodCollection, $lang);

            // Since it's a single PaymentMethod, get the first item from the collection
            $formattedData = $responseData->toArray($request)[0] ?? [];

            return ResponseWithSuccessData($lang, $formattedData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve payment method',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            // Use service to handle creation
            $result = $this->paymentMethodService->store($request);

            // Check if service returned an error response
            if (isset($result['code']) && $result['code'] !== 200) {
                return response()->json($result, $result['code']);
            }


            // Load relationships for the response
            $paymentMethod = $result->load(['createdBy', 'modifiedBy']);

            $paymentMethodCollection = collect([$paymentMethod]);
            $responseData = new PaymentMethodResource($paymentMethodCollection, $lang);

            $formattedData = $responseData->toArray($request)[0] ?? [];

            return ResponseWithSuccessData($lang, $formattedData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating payment method',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            if ($id >= 1 && $id <= 7) {
                return response()->json([
                    'code' => 403,
                    'status' => false,
                    'message' => $lang == 'en' ? 'Cannot edit protected records (ID 1-7)' : 'لا يمكن تعديل السجلات المحمية (ID 1-7)',
                    'data' => null,
                    'errorData' => null,
                    'validation_type' => true
                ], 403);
            }
            // Use service to handle update
            $result = $this->paymentMethodService->update($request, $id);

            // Check if service returned an error response
            if (isset($result['code']) && $result['code'] !== 200) {
                return response()->json($result, $result['code']);
            }


            // Load relationships for the response
            $paymentMethod = $result->load(['createdBy', 'modifiedBy']);

            $paymentMethodCollection = collect([$paymentMethod]);
            $responseData = new PaymentMethodResource($paymentMethodCollection, $lang);

            $formattedData = $responseData->toArray($request)[0] ?? [];

            return ResponseWithSuccessData($lang, $formattedData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update payment method',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        try {
            if ($id >= 1 && $id <= 7) {
                return response()->json([
                    'code' => 403,
                    'status' => false,
                    'message' => $lang == 'en' ? 'Cannot edit protected records (ID 1-7)' : 'لا يمكن حذف السجلات المحمية (ID 1-7)',
                    'data' => null,
                    'errorData' => null,
                    'validation_type' => true
                ], 403);
            }
            // service ALREADY returns a full JSON response
            return $this->paymentMethodService->delete($id);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
