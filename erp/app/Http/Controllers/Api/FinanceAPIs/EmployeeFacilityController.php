<?php

namespace App\Http\Controllers\Api\FinanceAPIs;

use App\Helper\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\EmployeeFacility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class EmployeeFacilityController extends Controller
{

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'en');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }

        $validator = Validator::make(request()->all(), [
            'facility_id' => 'required|integer|exists:facilities,id',
        ], [
            'facility_id.required' => $lang == 'en' ? 'Facility is required' : 'المنشأة مطلوبة',
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation error' : 'خطأ في التحقق',
                400,
                $validator->errors()->all()
            );
        }

        $employee = Auth::guard('employee')->user();
        $oldFacility = EmployeeFacility::where('employee_id', $employee->id)
                                    ->where('is_active', 1)
                                    ->where('facility_id', '!=', $request->facility_id)
                                    ->first();

        if ($oldFacility) {
            $oldFacility->update([
                'is_active'        => 0,
                'modified_by'      => authActionSave()['by'],
                'modified_by_type' => authActionSave()['type'],
            ]);
        }

        $data = EmployeeFacility::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'facility_id' => $request->facility_id,
            ],
            [
                'is_active'       => 1,
                'created_by'      => authActionSave()['by'],
                'created_by_type' => authActionSave()['type'],
                'modified_by'      => authActionSave()['by'],
                'modified_by_type' => authActionSave()['type'],
            ]
        );

        $data->refresh();
        $result = [
            'id' => $data->facility?->id,
            'name' => $data->facility?->name_ar,
            'code' => $data->facility?->code,
            'logo' => $data->facility?->logo,
        ];

        return ResponseWithSuccessData(request()->header('lang', 'ar'), $result, 1);
    }

}
