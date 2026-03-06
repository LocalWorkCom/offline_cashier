<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use App\Models\RolePermissionLog;
use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleOrPermissionAPIMiddleware
{
    public function handle($request, Closure $next, $roleOrPermission)
    {
        $lang = $request->header('lang', 'ar');
        $user = auth('employee')->user();



        logPermissionsAndRoleChanges('unauthorized_access', [
            'causer_id'   => $user->id,
            'causer_type' =>  authActionSave()['type'],
            'action'      => 'unauthorized_access',
            'employee_id' => auth()->id(),
            'extra_data'  => [
                'url'    => $request->fullUrl(),
                'method' => $request->method(),
                'ip'     => $request->ip(),
                'route'  => $request->route() ? $request->route()->getName() : null,
            ]
        ]);
        $employees = Employee::where('branch_id', $user->branch_id)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'HR_Manager')
                    ->where('guard_name', 'employee');
            })
            ->get();
        // foreach ($employees as $employee) {
        //     //here will make notification to hr manager that there is an access denied
        //     //    send_push_notification(
        //     //     $employee->id,
        //     //     'unauthorized_access',
        //     //     'Access Denied Alert',
        //     //     'You do not have permission to access this resource: ' . $request->fullUrl(),
        //     //     null,
        //     //     null,
        //     //     'employee'
        //     // );
        // }
        if ($user->hasRole($roleOrPermission, 'employee') || $user->hasPermissionTo($roleOrPermission, 'employee')) {
            return $next($request);
        }

        // Redirect to a 403 error page if the user doesn't have the required role or permission
        // return RespondWithBadRequest($lang, 102);

        return  response()->json([
                'status' => false,
                'message' => $lang == 'ar' ? 'غير مسموح' : 'forbidden',
                'code' => 403,
            ], 403);
    }
}
