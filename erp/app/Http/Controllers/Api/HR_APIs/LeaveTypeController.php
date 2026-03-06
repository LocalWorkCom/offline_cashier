<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\LeaveSetting;
use App\Models\LeaveType;
use App\Models\EmployeeLeave;
use App\Services\HR_Services\LeaveTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $leaveTypeService;

    public function __construct(LeaveTypeService $leaveTypeService)
    {
        $this->leaveTypeService = $leaveTypeService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $employee = auth()->user();
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 4);
            }
            $data = $this->leaveTypeService->index($request);
            $fields = ['name_site'];
            $visible = ['name', 'name_ar', 'name_en'];
            $response = paginateOrGetAll($data, $request, $fields, $visible);
            $response['data']->transform(function ($item) use ($employee) {
                $flag = LeaveSetting::where('leave_type_id', $item->id)
                    ->where('country_id', $employee->country_id)
                    ->first();
                $item->required_doc = $flag?->upload_certificate;
                return $item;
            });
            $response['data']->transform(function ($item) use ($employee) {
                $leave_count = EmployeeLeave::where('leave_type_id', $item->id)
                    ->where('employee_id', $employee->id)
                    ->first();
                $item->day_count = $leave_count?->day_count ?? 0;
                $item->day_paid = $leave_count?->day_paid ?? 0;
                $item->day_unpaid = $leave_count?->day_unpaid ?? 0;
                $item->required_doc = $item->required_doc == "yes" ? true : false;
                return $item;
            });
            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function add(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 4);
            }
            return $response = $this->leaveTypeService->add($request);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function edit(Request $request ,$id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 4);
            }
            return $response = $this->leaveTypeService->edit($request, $id);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function show(Request $request ,$id)
    {
        $lang =  $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 4);
            }
            return $data = $this->leaveTypeService->show($request , $id);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $request['lang'] = $lang;
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 4);
            }
            return $response = $this->leaveTypeService->delete($request, $id);
        } catch (\Exception $e) {
            // return RespondWithBadRequest($lang, 2);
            return respondError(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }
}
