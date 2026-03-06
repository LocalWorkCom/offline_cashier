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
use App\Models\User;
use Illuminate\Support\Facades\Storage;

use Google\Service\Datastream\Merge;

class CustomerService
{
    public function add(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $data = $request->validated();
            $result = [
                'name'          => $data['name'] ?? null,
                'phone'          => $data['phone'] ?? null,
                'flag' => 'client',
                'is_active' => 1,
                'created_by' => authActionSave()['by'],
                'created_by_type' => authActionSave()['type'],
            ];
            $user = User::create($result);
            return $user;
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    public function list(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $data = User::where('flag', 'client')->get();
            $users = $data->map(function ($user) use ($lang) {
                return[
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone
                ];
            });

            return ResponseWithSuccessData(request()->header('lang', 'ar'), $users, 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }
}
