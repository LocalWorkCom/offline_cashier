<?php

namespace App\Providers;


use App\Repositories\InsuranceEmployeeLogRepository;
use App\Repositories\InsuranceEmployeeLogRepositoryInterface;
use App\Repositories\InsuranceEmployeeRepository;
use App\Repositories\InsuranceEmployeeRepositoryInterface;
use App\Models\Insurance;
use App\Models\InsuranceEmployee;
use App\Models\InsuranceEmployeeLog;
use App\Models\InsuranceLog;
use App\Repositories\InsuranceLogRepository;
use App\Repositories\InsuranceLogRepositoryInterface;
use App\Repositories\InsuranceRepository;
use App\Repositories\InsuranceRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Maatwebsite\Excel\Sheet;

class DependencyServiceProvider extends ServiceProvider
{
    public function register()
    {

        $this->app->singleton(InsuranceRepositoryInterface::class, function ($app) {
            return new InsuranceRepository($app->make(Insurance::class));
        });

        $this->app->singleton(InsuranceLogRepositoryInterface::class, function ($app) {
            return new InsuranceLogRepository($app->make(InsuranceLog::class));
        });

        $this->app->singleton(InsuranceEmployeeRepositoryInterface::class, function ($app) {
            return new InsuranceEmployeeRepository($app->make(InsuranceEmployee::class));
        });

        $this->app->singleton(InsuranceEmployeeLogRepositoryInterface::class, function ($app) {
            return new InsuranceEmployeeLogRepository($app->make(InsuranceEmployeeLog::class));
        });

    }

    public function boot(): void
    {
        Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
            $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
        });
    }
}
