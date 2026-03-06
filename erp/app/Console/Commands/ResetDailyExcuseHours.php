<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;

class ResetDailyExcuseHours extends Command
{
    protected $signature = 'reset:daily-excuse-hours';
    protected $description = 'Resets daily excuse hours for all employees';

    public function handle()
    {
        Employee::query()->update(['used_daily_excuse_hours' => 0]);
        $this->info('Daily excuse hours reset successfully.');
    }
}
