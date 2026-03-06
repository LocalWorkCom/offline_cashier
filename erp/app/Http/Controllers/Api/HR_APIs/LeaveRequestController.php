<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveSetting;
use App\Models\LeaveSettingPosition;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\HR_Services\LeaveRequestService;

class LeaveRequestController extends Controller
{

    protected $leaveRequestService;

    public function __construct(LeaveRequestService $leaveRequestService)
    {
        $this->leaveRequestService = $leaveRequestService;
    }
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 4);
            }

            return $this->leaveRequestService->index($request);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Internal Server Error',
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ], 500);
        }
    }

    public function add(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        // try {
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 4);
        }
        $employee = Auth::guard('employee')->user();

        $employeeId = $employee->hasPermissionTo('create leave_requests_for_all_employees', 'employee')
            ? $request->employee_id
            : $employee->id;

        $request->merge(['employee_id' => $employeeId]);
        return $this->leaveRequestService->add($request);
        // } catch (\Exception $e) {
        //     return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        // }
    }

    public function show(Request $request, $id)
    {
        $lang =  $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 4);
            }
            return $data = $this->leaveRequestService->show($id);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function edit(Request $request, $id)
    {
        $lang =  $request->header('lang', 'en');
        // try {
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 4);
        }
        return $data = $this->leaveRequestService->edit($request, $id);
        // } catch (\Exception $e) {
        //     return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        // }
    }

    public function delete(Request $request, $id)
    {
        $lang =  $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 4);
            }
            return $data = $this->leaveRequestService->delete($request, $id);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function change_status(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        // try {
        return $data = $this->leaveRequestService->ChangeStatus($request);
        // } catch (\Exception $e) {
        //     return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        // }
    }

    public function employee_leaves(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        // try {
        return $data = $this->leaveRequestService->EmployeeLeaves($request);
        // } catch (\Exception $e) {
        //     return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        // }
    }

    public function employee_leaves_month($employee_id, $from, $to)
    {
        $lang = app()->getLocale();
        // try {
        return $data = $this->leaveRequestService->EmployeeLeavesMonth($employee_id, $from, $to);
        // return ResponseWithSuccessData($lang, $data, 1);
        // } catch (\Exception $e) {
        //     return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        // }
    }

}
