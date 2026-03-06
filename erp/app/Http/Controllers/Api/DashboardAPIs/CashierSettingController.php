<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CashierMachine;
use App\Models\Employee;
use App\Services\CashierSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CashierSettingController extends Controller
{
    protected $cashierSettingSevice;

    public function __construct(CashierSettingService $cashierSettingSevice)
    {
        $this->cashierSettingSevice = $cashierSettingSevice;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $validator = Validator::make($request->all(), [
            'date' => 'nullable|date'
        ]);
        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }
            $data = $this->cashierSettingSevice->index($request);
            return ResponseWithSuccessDataPaginated($lang, $data, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function posName(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $data = getPOS();
            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function createSetting(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $data = $this->cashierSettingSevice->createSetting($request);
            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $this->cashierSettingSevice->store($request);
            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function setting(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $data = $this->cashierSettingSevice->setting($request);
            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $data = $this->cashierSettingSevice->show($request, $id);
            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $employeeId = $request->input('employee_id');
            $branchId = $request->input('branch_id');
            $posIds = $request->input('pos_ids', []);

            if (!Employee::find($employeeId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => $lang === 'en' ? 'Employee not found.' : 'لم يتم العثور على الموظف.',
                    'code' => 404
                ], 404);
            }

            if (!Branch::find($branchId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => $lang === 'en' ? 'Branch not found.' : 'لم يتم العثور على الفرع.',
                    'code' => 404
                ], 404);
            }

            if (!empty($posIds)) {
                $invalidPosIds = array_diff($posIds, CashierMachine::whereIn('id', $posIds)->pluck('id')->toArray());
                if (!empty($invalidPosIds)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $lang === 'en' ? 'Invalid POS IDs: ' . implode(', ', $invalidPosIds) : ' ماكيات البيع غير صالحة: ' . implode(', ', $invalidPosIds),
                        'code' => 404
                    ], 404);
                }
            }

            $result = $this->cashierSettingSevice->update($request, $id);
            if (!$result) {
                return response()->json([
                    'status' => 'error',
                    'message' => $lang === 'ar' ? 'الحد الأدنى يجب أن يكون أقل من الحد الأقصى لماكينة البيع ' : 'Minimum must be less than maximum for POS',
                    'code' => 400
                ], 400);
            }
            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
