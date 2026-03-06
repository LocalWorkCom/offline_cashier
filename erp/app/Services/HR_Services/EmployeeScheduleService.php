<?php

namespace App\Services\HR_Services;

use App\Models\EmployeeSchedule;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeScheduleService
{
    private $lang;

    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }
    public function index()
    {
        $schedules = EmployeeSchedule::with(['shift.details.timetable']);

        return $schedules;
    }
    public function show($id, $lang = 'ar')
    {
        $schedules = EmployeeSchedule::with(['shift.details.timetable'])->find($id);
        if (!$schedules) {
            $message = $lang == 'en' ? 'Schedule not found' : 'جدول العمل غير موجود';
            return ['status' => 'error', 'message' => $message];
        }
        return $schedules;
    }
    public function store($data)
    {
        $created_by = auth('employee')->user()->id ?? auth('admin')->user()->id;

        EmployeeSchedule::where('employee_id', $data['employee_id'])
            ->where(function ($query) use ($data) {
                $query->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                    ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                    ->orWhere(function ($q) use ($data) {
                        $q->where('start_date', '<=', $data['start_date'])
                            ->where('end_date', '>=', $data['end_date']);
                    });
            })
            ->delete();

        $schedule = new EmployeeSchedule();
        $schedule->shift_id = $data['shift_id'];
        $schedule->employee_id = $data['employee_id'];
        $schedule->start_date = $data['start_date'];
        $schedule->end_date = $data['end_date'];
        $schedule->created_by = $created_by;
        $schedule->created_at = now();
        $schedule->save();

        return $schedule->load('shift.details.timetable');
    }

    public function update($data, $id, $lang = 'ar')
    {
        $updated_by = auth('employee')->user()->id ?? auth('admin')->user()->id;

        EmployeeSchedule::where('employee_id', $data['employee_id'])
            ->where('id', '!=', $id)
            ->where(function ($query) use ($data) {
                $query->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                    ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                    ->orWhere(function ($q) use ($data) {
                        $q->where('start_date', '<=', $data['start_date'])
                            ->where('end_date', '>=', $data['end_date']);
                    });
            })
            ->delete();

        $schedule = EmployeeSchedule::find($id);
        if (!$schedule) {
            $message = $lang == 'en' ? 'Schedule not found' : 'جدول العمل غير موجود';
            return respondError($message, 404);
        }
        $schedule->shift_id = $data['shift_id'];
        $schedule->employee_id = $data['employee_id'];
        $schedule->start_date = $data['start_date'];
        $schedule->end_date = $data['end_date'];
        $schedule->modified_by = $updated_by;
        $schedule->updated_at = now();
        $schedule->save();

        return $schedule->load('shift.details.timetable');
    }

    public function delete($id)
    {
        $deleted_by = auth('employee')->user()->id ?? auth('admin')->user()->id;

        $schedule = EmployeeSchedule::find($id);
        $counEmp = EmployeeSchedule::where('employee_id',$schedule->employee_id)->count();
        $now = Carbon::today();
        $checkDelete = $now->between(
            Carbon::parse($schedule->start_date)->startOfDay(),
            Carbon::parse($schedule->end_date)->endOfDay()
        );
        $flag = false;
        if($now < Carbon::parse($schedule->start_date)->startOfDay() && $counEmp == 1){
            $flag = true;
        }
        if($checkDelete){
            return ['status' => 'error', 'message' => 'Schedule cannot be deleted during its active period'];
        }
        $schedule->deleted_by = $deleted_by;
        $schedule->save();
        $schedule->delete();

        return ['status' => 'success', 'message' => 'Schedule deleted successfully', 'flag' => $flag];
    }

    public function setDefault($data)
    {
        $created_by = auth('employee')->user()->id ?? auth('admin')->user()->id;

        $employees = Employee::query();

        // If specific employees are selected → ignore all other filters
        if (!empty($data['employees'])) {
            $employeeIds = Employee::whereIn('id', $data['employees'])
                ->pluck('id')
                ->toArray();
        } else {
            // Build query based on filters (AND logic)
            if (!empty($data['branches'])) {
                $employees->whereIn('branch_id', $data['branches']);
            }

            if (!empty($data['departments'])) {
                $employees->whereIn('department_id', $data['departments']);
            }

            if (!empty($data['positions'])) {
                $employees->whereIn('position_id', $data['positions']);
            }

            $employeeIds = $employees->distinct()->pluck('id')->toArray();
        }
        // If no employees matched, stop
        if (empty($employeeIds)) {
            return [
                'status' => 'false',
                'message' => __('employee.no_employees_found_for_filters')
            ];
        }

        // Delete conflicting schedules for these employees
        EmployeeSchedule::whereIn('employee_id', $employeeIds)
            ->where(function ($query) use ($data) {
                $query->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                    ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                    ->orWhere(function ($q) use ($data) {
                        $q->where('start_date', '<=', $data['start_date'])
                            ->where('end_date', '>=', $data['end_date']);
                    });
            })
            ->delete();

        // Create new schedules for each employee
        foreach ($employeeIds as $employeeId) {
            EmployeeSchedule::create([
                'shift_id'    => $data['shift_id'],
                'employee_id' => $employeeId,
                'start_date'  => $data['start_date'],
                'end_date'    => $data['end_date'],
                'created_by'  => $created_by,
                'created_at'  => now(),
            ]);
        }

        return [
            'status' => 'success',
            'message' => __('employee.default_schedules_set_successfully_for_employees') . ': ' . implode(', ', $employeeIds),
        ];
    }
}
