<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Shift;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Position;
use App\Services\HR_Services\EmployeeScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class EmployeeScheduleController extends Controller
{
    protected $employeeScheduleService;
    protected $checkToken;

    public function __construct(EmployeeScheduleService $employeeScheduleService)
    {
        $this->employeeScheduleService = $employeeScheduleService;
        $this->checkToken = false;
    }

    public function index()
    {
        $schedules = $this->employeeScheduleService->index()->get();
        $employees = Employee::all();
        $shifts = Shift::all();
        $branches = Branch::all();
        $departments = Department::all();
        $positions = Position::all();
        return view('dashboard.employeeSchedule.index', compact('schedules', 'employees', 'shifts', 'branches', 'departments', 'positions'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $this->employeeScheduleService->store($validatedData);
        return redirect()->route('employeeSchedules.list')->with('success', 'Employee Schedule created successfully!');
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $this->employeeScheduleService->update($validatedData, $id, $this->checkToken);
        return redirect()->route('employeeSchedules.list')->with('success', 'Employee Schedule updated successfully!');
    }

    public function delete($id)
    {
        $this->employeeScheduleService->delete($id);
        return redirect()->route('employeeSchedules.list')->with('success', 'Employee Schedule deleted successfully!');
    }

    public function checkConflict(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'schedule_id' => 'nullable|exists:employee_schedules,id'
        ]);

        $conflict = EmployeeSchedule::where('employee_id', $validated['employee_id'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_date', '<=', $validated['start_date'])
                            ->where('end_date', '>=', $validated['end_date']);
                    });
            })
            ->when($validated['schedule_id'], function ($query, $scheduleId) {
                $query->where('id', '!=', $scheduleId);
            })
            ->exists();

        return response()->json(['conflict' => $conflict]);
    }

    public function setDefault(Request $request)
    {
        $validatedData = $request->validate([
            'branches' => 'nullable|array',
            'branches.*' => 'exists:branches,id',
            'departments' => 'nullable|array',
            'departments.*' => 'exists:departments,id',
            'positions' => 'nullable|array',
            'positions.*' => 'exists:positions,id',
            'employees' => 'nullable|array',
            'employees.*' => 'exists:employees,id',
            'shift_id' => 'required|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $this->employeeScheduleService->setDefault($validatedData);
        return redirect()->route('employeeSchedules.list')->with('success', 'Default schedule set successfully!');
    }
}
