<?php

namespace App\Http\Controllers\Api\ProcurementAPIS;

use App\Models\PaymentInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Services\ProcurementServices\PaymentIntervalService;
use App\Http\Resources\PaymentIntervalResource;

class PaymentIntervalController extends Controller
{
    protected $paymentIntervalService;

    public function __construct(PaymentIntervalService $paymentIntervalService)
    {
        $this->paymentIntervalService = $paymentIntervalService;
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
            $query = $this->paymentIntervalService->index($request);

            // Apply additional filters from request
            if ($request->filled('payment_interval_id')) {
                $query->where('id', $request->payment_interval_id);
            }
            // Filter by name (search in both Arabic and English names)
            if ($request->filled('name')) {
                $searchTerm = $request->name;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name_ar', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('name_en', 'LIKE', "%{$searchTerm}%");
                });
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

            //  Date filter (today, yesterday, before_yesterday, this_week, this_month)
            $query = applyDateFilter($query, $request->date_filter);

            //  Pagination or all results
            $paymentIntervals = paginateOrGetAll($query, $request, null, null);

            $result = new PaymentIntervalResource(collect($paymentIntervals['data']), $lang);
          

            return ResponseWithSuccessDataPaginated($lang, ['data'=>$result ,'meta'=> $paymentIntervals['meta']], 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve payment intervals',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            $paymentIntervalExists = PaymentInterval::where('id', $id)->exists();

            if (!$paymentIntervalExists) {
                return respondError($lang === 'ar' ? 'لم يتم العثور على فترة الدفع.' : 'Payment interval not found', 404);
            }

            // Use service to get the base query
            $query = $this->paymentIntervalService->index($request);

            $paymentInterval = $query->where('id', $id)->first();

            if (!$paymentInterval) {
                return respondError($lang === 'ar' ? 'لم يتم العثور على فترة الدفع.' : 'Payment interval not found', 404);
            }

            // Use the same Resource as index method
            $paymentIntervalCollection = collect([$paymentInterval]);
            $responseData = new PaymentIntervalResource($paymentIntervalCollection, $lang);

            // Since it's a single PaymentInterval, get the first item from the collection
            $formattedData = $responseData->toArray($request)[0] ?? [];

            return ResponseWithSuccessData($lang, $formattedData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve payment interval',
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
            $result = $this->paymentIntervalService->store($request);

            // Check if service returned an error response
             if (isset($result['code']) && $result['code'] !== 200) {
                return response()->json($result, $result['code']);
            }
            // Load relationships for the response
            $paymentInterval = $result->load(['createdBy', 'modifiedBy']);

            $paymentIntervalCollection = collect([$paymentInterval]);
            $responseData = new PaymentIntervalResource($paymentIntervalCollection, $lang);

            $formattedData = $responseData->toArray($request)[0] ?? [];

            return ResponseWithSuccessData($lang, $formattedData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating payment interval',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            // Use service to handle update
            $result = $this->paymentIntervalService->update($request, $id);

            // Check if service returned an error response
            if (isset($result['code']) && $result['code'] !== 200) {
                return response()->json($result, $result['code']);
            }

            // Load relationships for the response
            $paymentInterval = $result->load(['createdBy', 'modifiedBy']);

            $paymentIntervalCollection = collect([$paymentInterval]);
            $responseData = new PaymentIntervalResource($paymentIntervalCollection, $lang);

            $formattedData = $responseData->toArray($request)[0] ?? [];

            return ResponseWithSuccessData($lang, $formattedData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update payment interval',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        try {
            // service ALREADY returns a full JSON response
            return $this->paymentIntervalService->delete($id);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
