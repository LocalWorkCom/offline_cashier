<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckFacilityActive
{
    public function handle(Request $request, Closure $next)
    {
        $employee = auth('employee')->user();
        $lang = $request->header('lang', 'ar');

        if (!$employee || !$employee->employeeFacility) {
            return respondError(
                $lang == 'en' ? 'An error occurred' : 'حصل خطأ',
                403,
                $lang == 'en'
                    ? ['No facility is associated with the employee.']
                    : ['لا يوجد منشأة مرتبطة بالمستخدم']
            );
        }

        $facility = $employee->employeeFacility->facility;

        if (!$facility || $facility->is_active != 1) {
            return respondError(
                $lang == 'en' ? 'An error occurred' : 'حصل خطأ',
                403,
                $lang == 'en'
                    ? ['The facility is inactive.']
                    : ['المنشأة غير مفعّلة']
            );
        }

        return $next($request);
    }
}
