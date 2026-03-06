<?php

// app/Console/Commands/GeneratePayrollSheet.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HR_Services\PayrollService;
use Carbon\Carbon;


class GeneratePayrollSheet extends Command
{
    protected $signature = 'payroll:generate {type}';
    protected $description = 'Generate payroll sheet for employees by type (daily, weekly, monthly)';

    public function handle(PayrollService $payrollService)
    {
        $type = $this->argument('type');

        if (!in_array($type, ['daily', 'weekly', 'monthly'])) {
            $this->error("Invalid type. Use: daily, weekly, or monthly.");
            return 1;
        }
        $payrollSheet = $payrollService->generatePayrollSheet($type);

        if ($payrollSheet && isset($payrollSheet['success']) && $payrollSheet['success']) {
            $this->info("✅ Payroll sheet generated successfully for '{$type}' employees.");
        } else {
            $message = $payrollSheet['message'] ?? 'Unknown error.';
            $this->error("❌ Failed to generate payroll sheet: {$message}");
        }

        return 0;
    }
}
