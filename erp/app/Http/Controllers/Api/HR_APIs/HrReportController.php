<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeReportResource;
use App\Http\Resources\PayrollResource;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;

class HrReportController extends Controller
{
    private $lang;
    private $delays;
    private $penalties;
    private $advances;
    private $payrolls;

    public function __construct(Request $request)
    {
        $this->lang = $request->header('lang', 'ar');
        if (!CheckToken()) {
            return RespondWithBadRequest($this->lang, 5);
        }

        $this->delays =new DelayController();
        $this->penalties=new PenaltyController();
        $this->advances=new AdvanceController($request);
        $this->payrolls=new PayrollController($request);

    }

    //delays
    public function listDelaysReport(Request $request)
    {
        return $this->delays->index($request);
    }
    public function employeeDelaysReport($id, Request $request)
    {
        return $this->delays->show($id, $request);
    }
    ///////////////////////////////////////////////////////////////////////////
    //penalties
    public function listPenaltiesReport(Request $request)
    {
        return $this->penalties->index($request);
    }
    public function employeePenaltiesReport($id,Request $request)
    {
        return $this->penalties->show($id, $request);
    }
    ///////////////////////////////////////////////////////////////////////////
    //advances
    public function listAdvancesReport()
    {
        return $this->advances->index();
    }
    public function employeeAdvancesReport($id)
    {
        return $this->advances->show($id);
    }
    ///////////////////////////////////////////////////////////////////////////
    //payrolls
    public function listPayrollsReport()
    {
        return $this->payrolls->index();
    }
    public function employeePayrollsReport($id)
    {
        $data = Payroll::where('employee_id', $id)->orderBy('id', 'desc')->get(); //return all payrolls
        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        return ResponseWithSuccessData($this->lang, PayrollResource::collection($data), 1);
    }

    public function employeePayrollReport($id)
    {
        return $this->payrolls->showEmployee($id); //return the last payroll
    }
    ///////////////////////////////////////////////////////////////////////////
    //employees details
    public function listEmployeesReport()
    {
        $employees = Employee::get();
        return ResponseWithSuccessData($this->lang,EmployeeReportResource::collection($employees),1);

    }
    public function employeeReport($id)
    {
        $employee = Employee::find($id)->first();
        return ResponseWithSuccessData($this->lang,EmployeeReportResource::make($employee),1);
    }
}
