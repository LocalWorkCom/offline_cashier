<?php


namespace App\Services\HR_Services;

use App\Models\BonusRequest;
use App\Models\BonusRequestTrack;
use App\Models\BonusSettings;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class BounsService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function getFilteredBonusRequests(array $filters)
    {
        $query = BonusRequest::query();

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['bonus_type'])) {
            $query->where('bonus_type', $filters['bonus_type']);
        }

        if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
            $query->whereBetween('payout_date', [$filters['from_date'], $filters['to_date']]);
        }
        return $query->latest()->get();
    }


    public function addBonusRequest(array $data)
    {
        $user = auth('employee')->user();
        $employee = Employee::find($data['employee_id']);
        $settings = BonusSettings::first(); // assume single row settings

        if (!$employee || !$settings) {
            throw new \Exception("Employee or bonus settings not found.");
        }

        $bonusValue = $data['bonus_value'] ?? 0;
        $activePayrollSetting = $employee->activePayrollSetting();
        if (!$activePayrollSetting) {
            throw new \Exception("Active payroll setting not found for employee.");
        }
        $salaryValue = $activePayrollSetting->salary_value;
        // 1. Cap by max percentage
        $maxAllowed = ($settings->max_bonus_percentage / 100) * $salaryValue;
        if ($bonusValue > $maxAllowed) {
            $bonusValue = $maxAllowed;
        }

        // 2. Cap by fixed amount
        if ($settings->fixed_bonus_cap > 0 && $bonusValue > $settings->fixed_bonus_cap) {
            $bonusValue = $settings->fixed_bonus_cap;
        }

        // 3. Handle days convertible to money (optional, if request has 'days')
        if (!empty($data['days'])) {
            $days = min($data['days'], $settings->days_convertible_to_money);
            if ($activePayrollSetting->salary_type == 'dialy') {

                $dailyRate = $salaryValue;
            } else if ($activePayrollSetting->salary_type == 'monthly') {
                $dailyRate = $salaryValue / 30; // assuming 30 days in a month
            } else if ($activePayrollSetting->salary_type == 'weekly') {
                $dailyRate = $salaryValue / 7; // assuming 8 hours in a day
            } else {
                $dailyRate = 0;
            }
            $bonusValue += $days * $dailyRate;
        }

        // 4. Disbursement timing
        $payoutDate = $data['payout_date'] ?? null;

        if ($settings->disbursement_timing === 'monthly_salary') {
            // leave payout_date = null -> will be tied to payroll closing
            $payoutDate = null;
        } elseif ($settings->disbursement_timing === 'specific_date') {
            if (empty($data['payout_date'])) {
                throw new \Exception("Specific payout date required for this bonus.");
            }
            $payoutDate = $data['payout_date'];
        }


        // Save request
        $request = BonusRequest::create([
            'department_id' => $data['department_id'],
            'employee_id'   => $employee->id,
            'bonus_type'    => $data['bonus_type'],
            'bonus_value'   => $bonusValue,
            'status'        => 'Pending',
            'bonus_reason'  => $data['reason'],
            'payout_date'   => $payoutDate,
            'created_by'    => $user->id,
        ]);

        BonusRequestTrack::create([
            'bonus_request_id' => $request->id,
            'status'           => 'Pending',
            'reason'           => null,
            'payout_date'      => $payoutDate,
            'created_by'       => $user->id
        ]);

        return $request;
    }

    public function changeStatus(int $bonusRequestId, string $newStatus, ?string $reason = null, ?string $payoutDate = null)
    {
        $user = auth('employee')->user();

        $bonusRequest = BonusRequest::find($bonusRequestId);

        if (!$bonusRequest) {
            return null;
        }

        if ($bonusRequest->status === $newStatus) {
            return $bonusRequest;
        }



        // Update main record
        $bonusRequest->status = $newStatus;
        $bonusRequest->save();

        // Create track record
        BonusRequestTrack::create([
            'bonus_request_id' => $bonusRequest->id,
            'status'           => $newStatus,
            'reason'           => $reason,
            'payout_date'      => $payoutDate,
            'created_by'       => $user->id
        ]);

        return $bonusRequest;
    }
}
