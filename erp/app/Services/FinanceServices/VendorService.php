<?php

namespace App\Services\FinanceServices;

use App\Models\CostCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Http\Resources\Finance\CostCenterResource;
use App\Models\Branch;
use App\Models\CompanyProfileSetting;
use App\Models\Vendor;
use Illuminate\Support\Facades\Storage;

use Google\Service\Datastream\Merge;

class VendorService
{
    public function add(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $data = $request->validated();
            $result = [
                'name_ar'        => $data['name'] ?? null,
                'phone'          => $data['phone'] ?? null,
                'created_by' => authActionSave()['by'],
                'created_by_type' => authActionSave()['type'],
            ];
            $user = Vendor::create($result);
            return $user;
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    public function list(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $data = Vendor::get();
            $vendors = $data->map(function ($vendor) use ($lang) {
                return[
                    'id' => $vendor->id,
                    'name' => $lang === 'ar' ? $vendor->name_ar : $vendor->name_en,
                    'phone' => $vendor->phone
                ];
            });
            return ResponseWithSuccessData(request()->header('lang', 'ar'), $vendors, 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }
}
