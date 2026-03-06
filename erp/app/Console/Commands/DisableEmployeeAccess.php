<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\Employee;
use App\Models\TerminationOfService;
use Carbon\Carbon;
use Google\Service\ServiceUsage\TermsOfService;
use Illuminate\Console\Command;

class DisableEmployeeAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:disable-employee-access';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable system access for employees whose last working day is today';

    public function handle()
    {
        $today = Carbon::today();
        $user = auth('employee')->user();

        $terminations = TerminationOfService::whereDate('last_day', $today)->get();
        $department = Department::where('name_en', 'IT')->first();
        if (!$department) {
            return;
        }
        $employeesIt = Employee::where('department_id', $department->id)->get();
        foreach ($terminations as $termination) {
            $employee = Employee::find($termination->employee_id);

            if (!$employee) {
                continue;
            }
            foreach ($employeesIt as $employeeIt) {
                send_push_notification(
                    $employeeIt->device_token,
                    "تم إنهاء خدمة الموظف {$employee->first_name} {$employee->last_name}. يُرجى إلغاء صلاحيات الوصول الخاصة به.",
                    "Employee {$employee->first_name} {$employee->last_name} has been terminated. Revoke their access permissions.",
                    "أنهاء الخدمة",
                    "Termination",
                    "employee",
                    $employeeIt->id,
                    $user->id,
                    $user->id,
                    'ar',
                    11
                );
            }
        }
    }
}
