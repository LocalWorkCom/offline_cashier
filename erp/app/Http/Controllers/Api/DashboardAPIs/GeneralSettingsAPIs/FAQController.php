<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Models\FAQ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Services\SettingsServices\BranchService;
use App\Services\SettingsServices\FAQService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FAQController extends Controller
{

    protected $FAQService;

    public function __construct(FAQService $FAQService)
    {
        $this->FAQService = $FAQService;
    }
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        try {
            // Get the query builder from service
            $query = $this->FAQService->index($request);

            // Handle pagination or get all
            $response = paginateOrGetAll($query, $request, null);

            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching FAQ : ' . $e->getMessage());
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
            'question_ar' => 'required|string',
            'question_en' => 'required|string',
            'answer_ar' => 'required|string',
            'answer_en' => 'required|string',
            'active' => 'required|boolean',

        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $policy = $this->FAQService->store($request, null);

        return ResponseWithSuccessData($lang, $policy, 1);
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        $exists = FAQ::where('id', $id)->exists();

        if (!$exists) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        try {
            $policy = $this->FAQService->show($id);

            return ResponseWithSuccessData($lang, $policy, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching FAQ: ' . $e->getMessage());
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
            $exists = FAQ::where('id', $id)->exists();

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            app()->setLocale($lang);
            $validator = Validator::make($request->all(), [
                'name_ar' => 'nullable|string|max:255',
                'name_en' => 'nullable|string|max:255',
                'question_ar' => 'nullable|string',
                'question_en' => 'nullable|string',
                'answer_ar' => 'nullable|string',
                'answer_en' => 'nullable|string',
                'active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return respondError(
                    $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                    400,
                    $validator->errors()
                );
            }

            $data = $this->FAQService->update($request, $id);
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
        $exists = FAQ::where('id', $id)->exists();

        if (!$exists) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        try {
            $response = $this->FAQService->destroy($id, $lang);

            // If response is a JSON error (bad request, etc.), return it directly
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                return $response;
            }

            // Return success response
            $message = $lang === 'ar' ? 'تم حذف الاسئلة المتكررة بنجاح' : 'FAQ deleted successfully';
            return ResponseWithSuccessData($lang, $message, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching FAQ: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
