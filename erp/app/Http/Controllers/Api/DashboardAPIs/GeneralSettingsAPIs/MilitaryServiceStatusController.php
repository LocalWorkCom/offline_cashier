<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Models\MilitaryServiceStatus;
use Illuminate\Http\Request;
use App\Services\HR_Services\MilitaryServiceStatusService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MilitaryServiceStatusController extends Controller
{
    protected $militaryServiceStatusService;

    public function __construct(MilitaryServiceStatusService $militaryServiceStatusService)
    {
        $this->militaryServiceStatusService = $militaryServiceStatusService;
    }
    public function index(Request $request)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $query = $this->militaryServiceStatusService->index();

        // Add country filter directly in controller
        if ($request->has('country_id') && $request->country_id) {
            $query->where('country_id', $request->country_id);
        }

        $response = paginateOrGetAll($query, $request, null);
        return ResponseWithSuccessDataPaginated($lang, $response, 1);
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $militaryServiceStatus = MilitaryServiceStatus::withCount('employees')->find($id);
        if (!$militaryServiceStatus) {
            $message = $lang == 'en' ? 'Military Service Status not found.' : 'حالة الخدمة العسكرية غير موجودة.';
            return respondError($message, 404);
        }

        return ResponseWithSuccessData($lang, $militaryServiceStatus, 1);
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                Rule::unique('military_service_statuses', 'name_ar')->whereNull('deleted_at')
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('military_service_statuses', 'name_en')->whereNull('deleted_at')
            ],
            'country_id' => 'required|exists:countries,id',
        ], [
            'name_ar.required' => __('validation.required', ['attributes' => 'Arabic name'], $lang),
            'name_ar.unique' => __('validation.unique', ['attributes' => 'Arabic name'], $lang),
            'name_en.required' => __('validation.required', ['attributes' => 'English name'], $lang),
            'name_en.unique' => __('validation.unique', ['attributes' => 'English name'], $lang),
            'country_id.required' => __('validation.required', ['attributes' => 'Country'], $lang),
            'country_id.exists' => __('validation.exists', ['attributes' => 'Country'], $lang),
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $result = $this->militaryServiceStatusService->store($request);

        return ResponseWithSuccessData($lang, $result, 1);
    }
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $militaryServiceStatus = MilitaryServiceStatus::find($id);
        if (!$militaryServiceStatus) {
            $message = $lang == 'en' ? 'Military Service Status not found.' : 'حالة الخدمة العسكرية غير موجودة.';
            return respondError($message, 404);
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|unique:military_service_statuses,name_ar,' . $id,
            'name_en' => 'required|string|unique:military_service_statuses,name_en,' . $id,
            'country_id' => 'required|exists:countries,id',
        ], [
            'name_ar.required' => __('validation.required', ['attributes' => 'Arabic name'], $lang),
            'name_ar.unique' => __('validation.unique', ['attributes' => 'Arabic name'], $lang),
            'name_en.required' => __('validation.required', ['attributes' => 'English name'], $lang),
            'name_en.unique' => __('validation.unique', ['attributes' => 'English name'], $lang),
            'country_id.required' => __('validation.required', ['attributes' => 'Country'], $lang),
            'country_id.exists' => __('validation.exists', ['attributes' => 'Country'], $lang),
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $result = $this->militaryServiceStatusService->update($request, $id);

        return ResponseWithSuccessData($lang, $result, 1);
    }
    public function delete(Request $request, $id)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $militaryServiceStatus = MilitaryServiceStatus::find($id);
        if (!$militaryServiceStatus) {
            $message = $lang == 'en' ? 'Military Service Status not found.' : 'حالة الخدمة العسكرية غير موجودة.';
            return respondError($message, 404);
        }

        $result = $this->militaryServiceStatusService->delete($id);

        return ResponseWithSuccessData($lang, $result, 1);
    }
}
