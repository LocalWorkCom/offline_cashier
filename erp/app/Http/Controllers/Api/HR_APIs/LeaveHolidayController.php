<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Services\HR_Services\HolidaysService;
use App\Models\Country;
use App\Models\LeaveNational;
use Carbon\Carbon;

class LeaveHolidayController extends Controller
{
    protected $holidaysService;
    public function __construct(HolidaysService $holidaysService)
    {
        $this->holidaysService = $holidaysService;
    }
    /**
     * Display a listing of the resource.
     */

    public function index_calender(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            // Validate the input
            $validator = Validator::make($request->all(), [
                'country_id' => 'required|exists:countries,id',
                'date' => 'required|date'
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $countryCode = Country::where('id', $request->country_id)->first()->code;
            $date = Carbon::parse($request->date);
            $type = 'all';
            $leave_nationals = $this->holidaysService->getHolidays($countryCode, $date, $lang, $type);
            // $leave_nationals = $this->holidaysService->getAllHolidays($countryCode, $date);

            return ResponseWithSuccessData($lang, $leave_nationals, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function index(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            // Validate the input
            $validator = Validator::make($request->all(), [
                'country_id' => 'required|exists:countries,id',
                'year' => 'required|integer|min:1900|max:2100'
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $countryCode = Country::where('id', $request->country_id)->first()->code;
            $leave_nationals = LeaveNational::where('country_code', $countryCode)->whereYear('holiday_date', $request->year)->get();

            return ResponseWithSuccessData($lang, $leave_nationals, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function edit(Request $request, $id)
    {
        $lang =  $request->header('lang', 'en');
        // try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 4);
            }
            return $data = $this->holidaysService->edit($request, $id);
        // } catch (\Exception $e) {
        //     return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        // }
    }

    public function change_status(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        // try {
        return $data = $this->holidaysService->ChangeStatus($request);
        // } catch (\Exception $e) {
        //     return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        // }
    }


}
