<?php

namespace App\Traits;

trait AuthenticatesWithGuards
{
    public function getAuthenticatedUser()
    {
        foreach (['client', 'api', 'admin','employee','adminApp'] as $guard) {
            if (auth($guard)->check()) {
                return auth($guard)->user();
            }
        }

        return null;
    }
}
