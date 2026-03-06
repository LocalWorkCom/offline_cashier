<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Country;
use App\Models\LeaveNational;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Models\EmployeeLeaveLog;
use App\Models\EmployeeLeave;
use App\Models\Employee;
use Carbon\Carbon;
use App\Services\HR_Services\HolidaysService;
// use GPBMetadata\Google\Api\Log;
use Illuminate\Support\Facades\Log;

class GenerateYearlyHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:generate {year?}';
    protected $description = 'Generate public holidays for the given year and insert them into leave_nationals and leave_requests';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->argument('year') ?? now()->year;
        $this->info("Generating holidays for year {$year}...");

        // Example: Define your holidays here (can also fetch from API)
        // $holidays = [
        //     ['name_en' => 'New Year\'s Day', 'name_ar' => 'رأس السنة', 'date' => "{$year}-01-01"],
        //     ['name_en' => 'Labor Day', 'name_ar' => 'عيد العمال', 'date' => "{$year}-05-01"],
        //     ['name_en' => 'National Day', 'name_ar' => 'اليوم الوطني', 'date' => "{$year}-09-23"],
        //     // Add more holidays or load from DB/API...
        // ];

        $countries = Country::get();
        $year = Carbon::now()->year;
        $leaveType = LeaveType::where('id', 3)->first(); // adjust as needed

        // foreach ($countries as $country) {
            $country = Country::where('code', 'EG')->first();
            Log::info('Processing country holidays', ['country_id' => $country]);
            $employees = Employee::where('country_id', $country->id)->get();
            $service = new HolidaysService();
            $holidays = $service->getAllHolidays($country->code, Carbon::create($year, 1, 1), "en");
            $count_holidays = count($holidays);
            Log::info('Processing country holidays', ['holidays' => $holidays]);
            foreach ($holidays as $holiday) {
                // Insert into leave_nationals if not exists
                $exists = LeaveNational::where('country_id', $country->id)
                    ->whereDate('current_date', $holiday['date'])
                    ->first();

                if (!$exists) {
                    LeaveNational::create([
                        // 'country_id' => $country->id,
                        'country_code' => $country->code,
                        'leave_type_id' => $leaveType?->id,
                        'name_ar' => $holiday['name'],
                        'name_en' => $holiday['name_en'],
                        'date' => Carbon::now(),
                        'current_date' => $holiday['date'],
                        'holiday_date' => $holiday['date'],
                        'status' => 1,
                        'created_by' => 1,
                        'created_by_type' => 'employee'
                    ]);
                }

                // Add leave_requests for employees
                foreach ($employees as $employee) {
                    $existsRequest = LeaveRequest::where('employee_id', $employee->id)
                        ->whereDate('from', $holiday['date'])
                        ->whereDate('to', $holiday['date'])
                        ->first();

                    if (!$existsRequest) {
                        $existsRequest = LeaveRequest::create([
                            'employee_id' => $employee->id,
                            'leave_type_id' => $leaveType?->id,
                            'date' => $holiday['date'],
                            'from' => $holiday['date'],
                            'to' => $holiday['date'],
                            'leave_count' => 1,
                            'resone' => 'اجازة رسمية: ' . $holiday['name'],
                            'status' => 'confirm',
                            'position_id' => $employee->position_id,
                            'request_num' => generateRequestNumber(),
                            'created_by' => 1,
                        ]);
                    }

                    $existsRequest = EmployeeLeaveLog::where('employee_id', $employee->id)->where('leave_request_id', $existsRequest)
                        ->whereDate('from', $holiday['date'])
                        ->whereDate('to', $holiday['date'])
                        ->first();

                    if (!$existsRequest) {
                        EmployeeLeaveLog::create([
                            'employee_id' => $employee->id,
                            'position_id' => $employee->position_id,
                            'leave_request_id' => $existsRequest,
                            'leave_type_id' => $leaveType?->id,
                            'date' => $holiday['date'],
                            'from' => $holiday['date'],
                            'to' => $holiday['date'],
                            'day_count' => 1,
                            'day_paid' => 1,
                            'day_unpaid' => 0,
                            'deduction_value' => 0,
                            'deduction_days' => 0,
                            'resone' => 'اجازة رسمية: ' . $holiday['name']
                        ]);
                    }
                }
            }

            foreach($employees as $employee){
                $exists_employee_leave = EmployeeLeave::where('employee_id', $employee->id)->where('position_id', $employee->position_id)->where('leave_type_id', 3)->first(); // adjust as needed

                if (!$exists_employee_leave) {
                    EmployeeLeave::create([
                        'employee_id' => $employee->id,
                        'position_id' => $employee->position_id,
                        'leave_type_id' => 3,
                        'day_count' => $count_holidays,
                        'day_paid' => $count_holidays,
                        'day_unpaid' => 0
                    ]);
                }
            }


        // }

        $this->info("Holidays for {$year} have been generated successfully.");
    }
}
