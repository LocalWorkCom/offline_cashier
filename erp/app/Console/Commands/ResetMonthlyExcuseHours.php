<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;

class ResetMonthlyExcuseHours extends Command
{
    protected $signature = 'reset:monthly-excuse-hours';
    protected $description = 'Resets monthly excuse hours for all employees';

    public function handle()
    {
        Employee::query()->update(['used_monthly_excuse_hours' => 0]);
        $this->info('Monthly excuse hours reset successfully.');
    }
}
