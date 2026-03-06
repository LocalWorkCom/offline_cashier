<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\PayrollResource;
use App\Models\Advance;
use App\Models\DelayDeduction;
use App\Models\Payroll;
use App\Models\PenaltyDeduction;
use App\Services\HR_Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    private $lang;
    protected $PayrollService;

    public function __construct(Request $request, PayrollService $PayrollService)
    {
        $this->lang = $request->header('lang', 'ar');
        if (!CheckToken()) {
            return RespondWithBadRequest($this->lang, 5);
        }
        $this->PayrollService = $PayrollService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payrolls = Payroll::get();
        return ResponseWithSuccessData($this->lang, PayrollResource::collection($payrolls), 1);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function save(Request $request, string $id = null)
    {
        $data = $request->validate([
            'employee_id' => 'required|numeric|exists:employees,id',
            'advance_id' => 'nullable|numeric|exists:advances,id',
            'penalty_deduction_id' => 'nullable|numeric|exists:penalty_deductions,id',
            'delay_deduction_id' => 'nullable|numeric|exists:delay_deductions,id',
            'base_salary' => 'required|numeric',
            'bonus' => 'nullable|numeric',
            'taxes' => 'nullable|numeric',
            'insurance' => 'nullable|numeric',
            'pay_date' => 'required|date',
        ]);
        $penalty_deduction = PenaltyDeduction::where('id', $data['penalty_deduction_id'])->first();
        $delay_deduction = DelayDeduction::where('id', $data['delay_deduction_id'])->first();
        $advance = Advance::where('id', $data['advance_id'])->first();
        $data['deductions'] = $penalty_deduction['deduction_amount'] + $delay_deduction['deduction_amount'];
        $data['advance'] = $advance['amount_per_month'];
        $data['net_salary'] = ($data['base_salary'] + ($data['bonus'] ?? 0)) - ($data['advance'] + $data['deductions']
                + ($data['taxes'] ?? 0)
                + ($data['insurance'] ?? 0)
            );
        $id == null ?$data['created_by'] =Auth::guard('api')->user()->id
            : $data['modified_by'] =Auth::guard('api')->user()->id;

        $payroll = Payroll::updateOrCreate(['id' => $id], $data);

        return ResponseWithSuccessData($this->lang, $payroll, 1);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Payroll::find($id);

        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        return ResponseWithSuccessData($this->lang, PayrollResource::make($data), 1);
    }
    public function showEmployee(string $id)
    {
        $data = Payroll::where('employee_id', $id)->orderBy('id', 'desc')->first(); //return the last payroll
        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        return ResponseWithSuccessData($this->lang, PayrollResource::make($data), 1);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Payroll::find($id);

        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        $data->deleted_by = Auth::guard('api')->user()->id;
        $data->save();
        $data->delete();

        return ResponseWithSuccessData($this->lang, PayrollResource::make($data), 1);
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        $data = Payroll::withTrashed()->find($id);

        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        $data->restore();
        return ResponseWithSuccessData($this->lang, PayrollResource::make($data), 1);
    }

    public function PayrollTermination(Request $request, $id)
    {
        $data = $this->PayrollService->generatePayrollSheetForTermination($request->type, $id);
        // if (!$data) {
        //     return RespondWithBadRequestData($this->lang, 2);
        // }
        return ResponseWithSuccessData($this->lang, $data, 1);
    }
}
