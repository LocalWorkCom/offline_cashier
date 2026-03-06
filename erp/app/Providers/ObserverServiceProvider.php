<?php

namespace App\Providers;


use App\Observers\InsuranceEmployeeObserver;

use App\Models\Insurance;
use App\Models\InsuranceEmployee;
use App\Observers\InsuranceObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Insurance::observe(InsuranceObserver::class);
        InsuranceEmployee::observe(InsuranceEmployeeObserver::class);
    }
}
