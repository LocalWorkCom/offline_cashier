<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeWarning;
use App\Services\HR_Services\EmployeeWarningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EmployeeWarningController extends Controller
{
    protected $employeeWarningService;

    public function __construct(EmployeeWarningService $employeeWarningService)
    {
        $this->employeeWarningService = $employeeWarningService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'employee_id' => 'nullable|exists:employees,id',
            'approval_status' => 'nullable|in:pending,approved,rejected',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $response = $this->employeeWarningService->index($request);

        $response = paginateOrGetAll($response, $request, ['']);
        return ResponseWithSuccessDataPaginated($lang, $response, 1);
    }
    public function employeeWarnings(Request $request)
    {
        $lang = $request->header('lang', 'en');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $response = $this->employeeWarningService->employeeWarnings($request);

        if (is_array($response) && isset($response['status']) && $response['status'] === false) {
            return respondError($response['message'], 404);
        }

        return ResponseWithSuccessData($lang, $response, 1);
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'en');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'description' => 'required|string',
            'issue_date' => 'required|date',
            'consequences' => 'required|string',
            'action_plan' => 'nullable|string',
            'hr_name' => 'nullable|string|max:255',
            'hr_signature' => 'nullable|string|max:255',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $employee = Employee::where('id', $request->employee_id)
            ->where('status', 'active')
            ->first();

        if (!$employee) {
            return respondError(
                $lang == 'en' ? 'Employee not found or inactive.' : 'الموظف غير موجود أو غير نشط.',
                404
            );
        }

        $warning = $this->employeeWarningService->store($request);

        return ResponseWithSuccessData($lang, $warning, 1);
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        App::setLocale($lang);

        $warning = EmployeeWarning::find($id);

        if (!$warning) {
            return respondErrorData(
                $lang == 'en' ? 'Warning not found.' : 'التحذير غير موجود.',
                404
            );
        }

        $policy = $this->employeeWarningService->show($id);

        return ResponseWithSuccessData($lang, $policy, 1);
    }
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        App::setLocale($lang);

        $warning = EmployeeWarning::find($id);

        if (!$warning) {
            return respondError(
                $lang === 'en' ? 'Warning not found.' : 'التحذير غير موجود.',
                404
            );
        }

        $validator = Validator::make($request->all(), [
            'employee_id'   => 'nullable|exists:employees,id',
            'description'   => 'nullable|string',
            'issue_date'    => 'nullable|date',
            'consequences'  => 'nullable|string',
            'action_plan'   => 'nullable|string',
            'hr_signature'  => 'nullable|string|max:255',
            'document'      => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang === 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $employeeId = $request->employee_id ?? $warning->employee_id;

        $employee = Employee::where('id', $employeeId)
            ->where('status', 'active')
            ->first();

        if (!$employee) {
            return respondErrorData(
                $lang === 'en' ? 'Employee not found or inactive.' : 'الموظف غير موجود أو غير نشط.',
                404
            );
        }

        $warning = $this->employeeWarningService->update($request, $id);

        return ResponseWithSuccessData($lang, $warning, 1);
    }
    public function acknowledge(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        App::setLocale($lang);

        $warning = EmployeeWarning::find($id);

        if (!$warning) {
            return respondError(
                $lang == 'en' ? 'Warning not found.' : 'التحذير غير موجود.',
                404
            );
        }

        if (!$warning->canBeAcknowledged()) {
            return respondError(
                $lang == 'en' ? 'Warning cannot be acknowledged.' : 'لا يمكن تأكيد او رفض التحذير.',
                400
            );
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:acknowledge,refuse',
        ]);

        if ($validator->fails()) {
            return respondError($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.', 400, $validator->errors());
        }

        $response = $this->employeeWarningService->acknowledge($request, $id);

        return ResponseWithSuccessData($lang, $response, 1);
    }
    public function confirmDelivery($id)
    {
        $lang = request()->header('lang', 'en');
        App::setLocale($lang);

        $warning = EmployeeWarning::find($id);

        if (!$warning) {
            return respondError(
                $lang == 'en' ? 'Warning not found.' : 'التحذير غير موجود.',
                404
            );
        }

        // Check if HR can confirm delivery
        if (!$warning->canBeConfirmedByHr()) {
            return respondError(
                $lang == 'en' ? 'Delivery cannot be confirmed.' : 'لا يمكن تأكيد التسليم.',
                400
            );
        }

        if (is_null($warning->viewed_at)) {
            return respondError(
                $lang == 'en' ? 'Cannot confirm delivery: Warning not viewed by employee.' : 'لا يمكن تأكيد التسليم: التحذير لم يُعرض من قبل الموظف.',
                400
            );
        }

        $response = $this->employeeWarningService->confirmDelivery($id);

        return ResponseWithSuccessData($lang, $response, 1);
    }

    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        App::setLocale($lang);

        $warning = EmployeeWarning::find($id);

        if (!$warning) {
            return respondErrorData(
                $lang == 'en' ? 'Warning not found.' : 'التحذير غير موجود.',
                404
            );
        }

        $warning = $this->employeeWarningService->delete($id);

        return ResponseWithSuccessData($lang, $warning, 1);
    }
}
