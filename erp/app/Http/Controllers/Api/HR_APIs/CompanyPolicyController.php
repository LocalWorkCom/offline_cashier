<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\CompanyPolicy;
use App\Services\HR_Services\CompanyPolicyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class CompanyPolicyController extends Controller
{
    protected $companyPolicyService;

    public function __construct(CompanyPolicyService $companyPolicyService)
    {
        $this->companyPolicyService = $companyPolicyService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        App::setLocale($lang);

        $policies = $this->companyPolicyService->index($request);

        if (is_array($policies) && isset($policies['status']) && $policies['status'] === false) {
            return respondError($policies['message'], 400, $policies['errorData']);
        }

        $response = paginateOrGetAll($policies, $request, ['']);
        return ResponseWithSuccessDataPaginated($lang, $response, 1);
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'en');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:company_profile_settings,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,doc,docx|max:2048',
            'version' => 'nullable|string|max:10',
        ]);

        $validator->validate();
        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $policy =  $this->companyPolicyService->store($request);

        return ResponseWithSuccessData($lang, $policy, 1);
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        $policy = CompanyPolicy::find($id);
        if (!$policy) {
            return respondErrorData(
                $lang == 'en' ? 'This policy does not exist.' : 'هذه السياسة غير موجودة.',
                404
            );
        }
        $policy = $this->companyPolicyService->show($id);

        return ResponseWithSuccessData($lang, $policy, 1);
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        App::setLocale($lang);

        $policy = CompanyPolicy::find($id);

        if (!$policy) {
            return respondErrorData(
                $lang == 'en' ? 'This policy does not exist.' : 'هذه السياسة غير موجودة.',
                404
            );
        }

        $validator = Validator::make($request->all(), [
            'company_id' => 'nullable|exists:company_profile_settings,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'version' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $policy = $this->companyPolicyService->update($request, $id);

        return ResponseWithSuccessData($lang, $policy, 1);
    }
    public function acknowledgements(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        App::setLocale($lang);

        $policy = CompanyPolicy::find($id);

        if (!$policy) {
            return respondErrorData(
                $lang == 'en' ? 'This policy does not exist.' : 'هذه السياسة غير موجودة.',
                404
            );
        }

        $acknowledgements = $this->companyPolicyService->acknowledgements($id);

        if (isset($acknowledgements['status']) && $acknowledgements['status'] === false) {
            return respondError($acknowledgements['message'], 404, $acknowledgements['data']);
        }

        return ResponseWithSuccessData($lang, $acknowledgements, 1);
    }

    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        App::setLocale($lang);

        $policy = CompanyPolicy::find($id);

        if (!$policy) {
            return respondErrorData(
                $lang == 'en' ? 'This policy does not exist.' : 'هذه السياسة غير موجودة.',
                404
            );
        }

        $result = $this->companyPolicyService->delete($id);

        return ResponseWithSuccessData($lang, $result, 1);
    }
}
