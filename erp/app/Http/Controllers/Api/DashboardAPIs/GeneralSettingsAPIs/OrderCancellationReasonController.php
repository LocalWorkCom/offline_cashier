<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Models\OrderCancellationReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Services\SettingsServices\OrderCancellationReasonService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderCancellationReasonController extends Controller
{
    protected $orderCancellationReasonService;
    protected $fildes_hidden = [
        'created_by',
        'created_by_type',
        'updated_by',
        'updated_by_type',
        'deleted_by',
        'deleted_by_type',
        // 'reason'
        // 'reason_ar',
        // 'reason_en'
    ];
    public function __construct(OrderCancellationReasonService $orderCancellationReasonService)
    {
        $this->orderCancellationReasonService = $orderCancellationReasonService;
    }
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        try {

            $withTrashed = $request->query('withTrashed', false);
            $query = $this->orderCancellationReasonService->index($withTrashed);
            $response = paginateOrGetAll($query, $request, $this->fildes_hidden);
            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching payment policies: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $reason = $this->orderCancellationReasonService->show($id);
            $reason->makeHidden($this->fildes_hidden);


            return ResponseWithSuccessData($lang, $reason, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'السبب غير موجودة' : 'Reason caegory not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching recipe: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $CancellationReason = $this->orderCancellationReasonService->store($request->all());

        // If the service returned a JsonResponse (error), return it directly
        if ($CancellationReason instanceof \Illuminate\Http\JsonResponse) {
            return $CancellationReason;
        }

        return ResponseWithSuccessData($lang, $CancellationReason, 1);
    }
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
        $OrderCancellationReason = OrderCancellationReason::findOrFail($id);

        $addonCategory = $this->orderCancellationReasonService->update($request->all(), $id);

        if ($addonCategory instanceof \Illuminate\Http\JsonResponse) {
            return $addonCategory;
        }

        return ResponseWithSuccessData($lang, $addonCategory->makeHidden($this->fildes_hidden), 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'السبب غير موجودة' : 'Reason not found';
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
            $response = $this->orderCancellationReasonService->delete($id);

            // If response is a JSON error (bad request, etc.), return it directly
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                return $response;
            }

            // Return success response
            $message = $lang === 'ar' ? 'تم حذف السبب بنجاح' : 'ٌReason deleted successfully';
            return ResponseWithSuccessData($lang, $message, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? ' السبب غير موجودة' : 'ٌReason not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching recipe: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
