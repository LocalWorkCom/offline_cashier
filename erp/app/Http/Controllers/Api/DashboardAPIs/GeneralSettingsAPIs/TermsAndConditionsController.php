<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Models\TermsAndCondition;
use App\Models\TermsAndConditions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Services\SettingsServices\BranchService;
use App\Services\SettingsServices\TermsAndConditionsService;
use Google\Service\AndroidManagement\TermsAndConditions as AndroidManagementTermsAndConditions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TermsAndConditionsController extends Controller
{

    protected $TermsAndConditionsService;

    public function __construct(TermsAndConditionsService $TermsAndConditionsService)
    {
        $this->TermsAndConditionsService = $TermsAndConditionsService;
    }
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        try {
            // Get the query builder from service
            $query = $this->TermsAndConditionsService->index($request);

            // Handle pagination or get all
            $response = paginateOrGetAll($query, $request, null);
            $response['data'] = $response['data']->map(function($term) use($lang) {
                $term->active = $term->active == 1 ? ($lang == 'en' ? 'Active' : 'نشط') : ($lang == 'en' ? 'Not Active' : 'غير نشط');
                return $term;
            });

            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching TermsAndConditions : ' . $e->getMessage());
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
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'required|string',
            'description_en' => 'required|string',
            'active' => 'required|boolean',

        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $policy = $this->TermsAndConditionsService->store($request, null);

        return ResponseWithSuccessData($lang, $policy, 1);
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        $exists = TermsAndCondition::where('id', $id)->exists();

        if (!$exists) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        try {
            $policy = $this->TermsAndConditionsService->show($id);

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
            $exists = TermsAndCondition::where('id', $id)->exists();

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            app()->setLocale($lang);
            $validator = Validator::make($request->all(), [
                'name_ar' => 'nullable|string|max:255',
                'name_en' => 'nullable|string|max:255',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return respondError(
                    $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                    400,
                    $validator->errors()
                );
            }

            $data = $this->TermsAndConditionsService->update($request, $id);
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
        $exists = TermsAndCondition::where('id', $id)->exists();

        if (!$exists) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        try {
            $response = $this->TermsAndConditionsService->destroy($id, $lang);

            // If response is a JSON error (bad request, etc.), return it directly
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                return $response;
            }

            // Return success response
            $message = $lang === 'ar' ? 'تم حذف الشروط والاحكام بنجاح' : 'TermsAndConditions deleted successfully';
            return ResponseWithSuccessData($lang, $message, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching TermsAndConditions: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
