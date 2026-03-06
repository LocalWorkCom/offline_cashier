<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Models\MaritalStatus;
use Illuminate\Http\Request;
use App\Services\HR_Services\MaritalStatusService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MaritalStatusController extends Controller
{
    protected $maritalStatusService;

    public function __construct(MaritalStatusService $maritalStatusService)
    {
        $this->maritalStatusService = $maritalStatusService;
    }
    public function index(Request $request)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $query =  $this->maritalStatusService->index();
        $response = paginateOrGetAll($query, $request, null);
        return ResponseWithSuccessDataPaginated($lang, $response, 1);
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $maritalStatus = MaritalStatus::withCount('employees')->find($id);
        if (!$maritalStatus) {
            $message = $lang == 'en' ? 'Marital Status not found.' : 'الحالة الاجتماعية غير موجودة.';
            return respondError($message, 404);
        }

        return ResponseWithSuccessData($lang, $maritalStatus, 1);
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                Rule::unique('marital_statuses', 'name_ar')->whereNull('deleted_at')
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('marital_statuses', 'name_en')->whereNull('deleted_at')
            ],
        ], [
            'name_ar.required' => __('validation.required', ['attributes' => 'Arabic name'], $lang),
            'name_ar.unique' => __('validation.unique', ['attributes' => 'Arabic name'], $lang),
            'name_en.required' => __('validation.required', ['attributes' => 'English name'], $lang),
            'name_en.unique' => __('validation.unique', ['attributes' => 'English name'], $lang),
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $result = $this->maritalStatusService->store($request, false);

        return ResponseWithSuccessData($lang, $result, 1);
    }
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $MaritalStatus = MaritalStatus::find($id);
        if (!$MaritalStatus) {
            $message = $lang == 'en' ? 'Marital Status not found.' : 'الحالة الاجتماعية غير موجودة.';
            return respondError($message, 404);
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|unique:marital_statuses,name_ar,' . $id,
            'name_en' => 'required|string|unique:marital_statuses,name_en,' . $id,
        ], [
            'name_ar.required' => __('validation.required', ['attributes' => 'Arabic name'], $lang),
            'name_ar.unique' => __('validation.unique', ['attributes' => 'Arabic name'], $lang),
            'name_en.required' => __('validation.required', ['attributes' => 'English name'], $lang),
            'name_en.unique' => __('validation.unique', ['attributes' => 'English name'], $lang),
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $result = $this->maritalStatusService->update($request, $id, false);

        return ResponseWithSuccessData($lang, $result, 1);
    }
    public function delete(Request $request, $id)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $MaritalStatus = MaritalStatus::find($id);
        if (!$MaritalStatus) {
            $message = $lang == 'en' ? 'Marital Status not found.' : 'الحالة الاجتماعية غير موجودة.';
            return respondError($message, 404);
        }

        $result = $this->maritalStatusService->delete($id);

        return ResponseWithSuccessData($lang, $result, 1);
    }
}
