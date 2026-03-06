<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Models\PolicyPaymentReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Services\SettingsServices\BranchService;
use App\Services\SettingsServices\PolicyPaymentReservationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PolicyPaymentReservationController extends Controller
{

    protected $PolicyPaymentReservationService;

    public function __construct(PolicyPaymentReservationService $PolicyPaymentReservationService)
    {
        $this->PolicyPaymentReservationService = $PolicyPaymentReservationService;
    }
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        try {
            // Get the query builder from service
            $query = $this->PolicyPaymentReservationService->index($request);

            // Handle pagination or get all
            $response = paginateOrGetAll($query, $request, null);

            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching PolicyPaymentReservation : ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        app()->setLocale($lang);

        $validator = Validator::make($request->all(), [
            'payment_ar' => 'required|string',
            'payment_en' => 'required|string',
            'reservation_ar' => 'required|string',
            'reservation_en' => 'required|string',

        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $policy = $this->PolicyPaymentReservationService->store($request, null);

        return ResponseWithSuccessData($lang, $policy, 1);
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        $exists = PolicyPaymentReservation::where('id', $id)->exists();

        if (!$exists) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        try {
            $policy = $this->PolicyPaymentReservationService->show($id);

            return ResponseWithSuccessData($lang, $policy, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching recipe: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        try {

            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            $exists = PolicyPaymentReservation::where('id', $id)->exists();

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            app()->setLocale($lang);
            $validator = Validator::make($request->all(), [
                'payment_ar' => 'nullable|string',
                'payment_en' => 'nullable|string',
                'reservation_ar' => 'nullable|string',
                'reservation_en' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return respondError(
                    $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                    400,
                    $validator->errors()
                );
            }

            $data = $this->PolicyPaymentReservationService->update($request, $id);
            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching recipe: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        $exists = PolicyPaymentReservation::where('id', $id)->exists();

        if (!$exists) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        try {
            $response = $this->PolicyPaymentReservationService->destroy($id, $lang);

            // If response is a JSON error (bad request, etc.), return it directly
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                return $response;
            }

            // Return success response
            $message = $lang === 'ar' ? 'تم حذف سياسة الدفع والحجز بنجاح' : 'PolicyPaymentReservation deleted successfully';
            return ResponseWithSuccessData($lang, $message, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching PolicyPaymentReservation: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
