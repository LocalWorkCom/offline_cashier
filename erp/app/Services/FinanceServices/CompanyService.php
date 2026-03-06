<?php

namespace App\Services\FinanceServices;

use App\Models\CompanyProfileSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Http\Resources\Finance\CompanyResource;
use Illuminate\Support\Facades\Storage;

use Google\Service\Datastream\Merge;

class CompanyService
{
    public function getAll(Request $request)
    {
        $companies = CompanyProfileSetting::query();
        return $companies;
    }

    public function show(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $data = CompanyProfileSetting::with(['companyPolicy', 'businessActivity', 'branch', 'socialMediaInformation'])->find($request->id);
            if (!$data) {
                return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            return ResponseWithSuccessData(request()->header('lang', 'ar'), new CompanyResource($data), 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    public function list(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $data = CompanyProfileSetting::get();
            $companies = $data->map(function ($comapny) use ($lang) {
                return[
                    'id' => $comapny->id,
                    'name' => $lang === 'ar' ? $comapny->name_ar : $comapny->name_en
                ];
            });

            return ResponseWithSuccessData(request()->header('lang', 'ar'), $companies, 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

}
