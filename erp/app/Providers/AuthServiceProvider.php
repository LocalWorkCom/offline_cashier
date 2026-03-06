<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        // Specify the guard for the @can directive
        Gate::before(function ($user, $ability) {
            if ($user->guardName === 'admin') {
                return $user->hasPermissionTo($ability, 'admin');
            }
        });
        //
        //        Passport::routes();
        //        Passport::tokensCan([
        //            'user' => 'Access User API',
        //            'employee' => 'Access Employee API',
        //            ]);

        $this->registerPolicies();
        Passport::tokensCan([
        'client-access' => 'Access for client app',
        'employee-access' => 'Access for employee app',
        // add other scopes if needed
    ]);

    Passport::setDefaultScope([
        'client-access',
    ]);
        // Optional: token expiration
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
    }
}
