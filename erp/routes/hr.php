<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use Illuminate\Session\Middleware\StartSession;
use App\Http\Controllers\Api\HR_APIs\BounsController;
use App\Http\Controllers\Api\HR_APIs\DelayController;
use App\Http\Controllers\Api\HR_APIs\ShiftController;
use App\Http\Controllers\Api\HR_APIs\AbsenceSettingController;
use App\Http\Controllers\Api\HR_APIs\DeviceController;
use App\Http\Controllers\Api\HR_APIs\AdvanceController;
use App\Http\Controllers\Api\HR_APIs\BioTimeController;
use App\Http\Controllers\Api\HR_APIs\PayrollController;
use App\Http\Controllers\Api\HR_APIs\PenaltyController;
use App\Http\Controllers\Api\HR_APIs\EmployeeController;
use App\Http\Controllers\Api\HR_APIs\HrReportController;
use App\Http\Controllers\Api\HR_APIs\PositionController;
use App\Http\Controllers\Api\HR_APIs\DelayTimeController;
use App\Http\Controllers\Api\HR_APIs\HRRequestController;
use App\Http\Controllers\Api\HR_APIs\HRServiceController;
use App\Http\Controllers\Api\HR_APIs\HrSettingController;
use App\Http\Controllers\Api\HR_APIs\LeaveTypeController;
use App\Http\Controllers\Api\HR_APIs\PayrollControllerV2;
use App\Http\Controllers\Api\HR_APIs\TimetableController;
use App\Http\Controllers\Api\HR_APIs\ViolationController;
use App\Http\Controllers\Api\HR_APIs\AttendanceController;
use App\Http\Controllers\Api\HR_APIs\DepartmentController;
use App\Http\Controllers\Api\HR_APIs\NationalityController;
use App\Http\Controllers\Api\HR_APIs\DocumentTypeController;
use App\Http\Controllers\Api\HR_APIs\EmployeeAuthController;
use App\Http\Controllers\Api\HR_APIs\LeaveHolidayController;
use App\Http\Controllers\Api\HR_APIs\LeaveRequestController;
use App\Http\Controllers\Api\HR_APIs\LeaveSettingController;
use App\Http\Controllers\Api\HR_APIs\OvertimeTypeController;
use App\Http\Controllers\Api\HR_APIs\AlertSettingsController;
use App\Http\Controllers\Api\HR_APIs\BonusSettingsController;
use App\Http\Controllers\Api\HR_APIs\CompanyPolicyController;
use App\Http\Controllers\Api\HR_APIs\ExcuseRequestController;
use App\Http\Controllers\Api\HR_APIs\ExcuseSettingController;
use App\Http\Controllers\Api\HR_APIs\PenaltyReasonController;
use App\Http\Controllers\Api\HR_APIs\AdvanceRequestController;
use App\Http\Controllers\Api\HR_APIs\AdvanceSettingController;
use App\Http\Controllers\Api\HR_APIs\DelayDeductionController;
use App\Http\Controllers\Api\HR_APIs\EmployeeDeviceController;
use App\Http\Controllers\Api\HR_APIs\PrivilegeTypesController;
use App\Http\Controllers\Api\HR_APIs\EmployeeWarningController;
use App\Http\Controllers\Api\HR_APIs\JobTypesSettingController;
use App\Http\Controllers\Api\HR_APIs\OvertimeSettingController;
use App\Http\Controllers\Api\HR_APIs\WarningSettingsController;
use App\Http\Controllers\Api\HR_APIs\EmployeeDocumentController;
use App\Http\Controllers\Api\HR_APIs\EmployeeScheduleController;
use App\Http\Controllers\Api\HR_APIs\PenaltyDeductionController;
use App\Http\Controllers\Api\HR_APIs\JobRelatedPenaltyController;
use App\Http\Controllers\Api\HR_APIs\PerformanceReviewsController;
use App\Http\Controllers\Api\HR_APIs\EmployeeTerminationController;
use App\Http\Controllers\Api\HR_APIs\TemporarySuspensionController;
use App\Http\Controllers\Api\HR_APIs\LateDeductionSettingController;
use App\Http\Controllers\Api\HR_APIs\LeaveSettingPositionController;
use App\Http\Controllers\Api\HR_APIs\SalaryAdvanceRequestController;
use App\Http\Controllers\Api\HR_APIs\SalaryAdvanceSettingController;
use App\Http\Controllers\Api\HR_APIs\EmployeeOpeningBalanceController;
use App\Http\Controllers\Api\HR_APIs\EmployeeRateController;
use App\Http\Controllers\Api\HR_APIs\HomeController;
use App\Http\Controllers\Api\HR_APIs\NotificationCategoriesController;

Route::post('create-password-first-login', [EmployeeAuthController::class, 'createPassword'])->name('createPassword');
Route::post('employees/resetpassword', [EmployeeAuthController::class, 'resetPassword']);

Route::post('hr-app-login', [EmployeeAuthController::class, 'login'])->name('login.hr_app');

Route::post('dashboard-login', [EmployeeAuthController::class, 'login'])->name('login.dashboard');
Route::middleware(['auth:employee'])->group(function () {
    Route::get('employee_logout',  [EmployeeAuthController::class, 'logout'])->name('logout.dashboard');
    Route::get('hr-home', [HomeController::class, 'index']);

    Route::post('employees/changepassword', [EmployeeAuthController::class, 'changePassword']);

    Route::post('/hierarchies', [EmployeeController::class, 'getHierarchies']);

    Route::group(['prefix' => 'performance_reviews'], function () {
        Route::get('list', [PerformanceReviewsController::class, 'index']);
        Route::post('add', [PerformanceReviewsController::class, 'add'])->middleware('role_or_permission_api:add performance_reviews');
        Route::get('report', [PerformanceReviewsController::class, 'report']);
    });

    Route::group(['prefix' => 'temporary_suspensions'], function () {
        Route::get('list', [TemporarySuspensionController::class, 'index']);
        Route::post('add', [TemporarySuspensionController::class, 'add'])->middleware('role_or_permission_api:add temporary_suspensions');
        Route::post('update/{id}', [TemporarySuspensionController::class, 'update'])->middleware('role_or_permission_api:approval_status temporary_suspensions');
        Route::get('report', [TemporarySuspensionController::class, 'report']);
    });

    Route::group(['prefix' => 'payroll'], function () {
        Route::get('/payroll_sheets/list', [PayrollControllerV2::class, 'index'])->middleware('role_or_permission_api:list payroll_sheets');
        Route::post('/approve/{id}', [PayrollControllerV2::class, 'approve'])->middleware('role_or_permission_api:approve payroll_sheets');
        Route::post('/returnCorrection/{id}', [PayrollControllerV2::class, 'returnCorrection'])->middleware('role_or_permission_api:returnCorrection payroll_sheets');
        Route::post('/resubmit/{id}', [PayrollControllerV2::class, 'resubmit'])->middleware('role_or_permission_api:resubmit payroll_sheets');
        Route::post('/updatePayrollItem/{id}', [PayrollControllerV2::class, 'updatePayrollItem'])->middleware('role_or_permission_api:updatePayrollItem payroll_sheets');
    });



    // nationalities
    Route::group(['prefix' => 'nationality'], function () {
        Route::get('/index', [NationalityController::class, 'index'])->middleware('role_or_permission_api:view nationalities');
        Route::get('/show/{id}', [NationalityController::class, 'show'])->middleware('role_or_permission_api:view nationalities');
        Route::post('store', [NationalityController::class, 'store'])->middleware('role_or_permission_api:create nationalities');
        Route::post('update/{id}', [NationalityController::class, 'update'])->middleware('role_or_permission_api:update nationalities');
        Route::delete('delete/{id}', [NationalityController::class, 'destroy'])->middleware('role_or_permission_api:delete nationalities');
    });


    Route::group(['prefix' => 'profile'], function () {
        Route::get('/show', [EmployeeController::class, 'show'])->name('employees.profile.show')->middleware('role_or_permission_api:view profile');
        Route::post('/update', [EmployeeController::class, 'updateProfile'])->name('employees.profile.update')->middleware('role_or_permission_api:update profile');
    });

    //leave-types
    Route::group(['prefix' => 'leaves-type'], function () {
        Route::get('index', [LeaveTypeController::class, 'index']); //->middleware('role_or_permission_api:view leave_types');
        Route::post('add', [LeaveTypeController::class, 'add'])->middleware('role_or_permission_api:create leave_types');
        Route::post('edit/{id}', [LeaveTypeController::class, 'edit'])->middleware('role_or_permission_api:update leave_types');
        Route::get('delete/{id}', [LeaveTypeController::class, 'delete'])->middleware('role_or_permission_api:delete leave_types');
        Route::get('show/{id}', [LeaveTypeController::class, 'show'])->middleware('role_or_permission_api:view leave_types');
    });

    //leaves-setting
    Route::group(['prefix' => 'leaves-setting'], function () {
        Route::get('index', [LeaveSettingController::class, 'index'])->middleware('role_or_permission_api:view leave_settings');
        Route::post('add', [LeaveSettingController::class, 'add'])->middleware('role_or_permission_api:create leave_settings');
        Route::post('edit', [LeaveSettingController::class, 'edit'])->middleware('role_or_permission_api:update leave_settings');
        Route::get('delete/{id}', [LeaveSettingController::class, 'delete'])->middleware('role_or_permission_api:delete leave_settings');
    });

    // leave-setting-positions
    Route::group(['prefix' => 'leave-setting-positions'], function () {
        Route::get('index', [LeaveSettingPositionController::class, 'index'])->middleware('role_or_permission_api:view leave_setting_positions');
        Route::post('add', [LeaveSettingPositionController::class, 'add'])->middleware('role_or_permission_api:create leave_setting_positions');
        Route::post('edit', [LeaveSettingPositionController::class, 'edit'])->middleware('role_or_permission_api:update leave_setting_positions');
        Route::get('delete/{id}', [LeaveSettingPositionController::class, 'delete'])->middleware('role_or_permission_api:delete leave_setting_positions');
    });

    //leave-holidays
    Route::group(['prefix' => 'leave-holidays'], function () {
        Route::post('index_calender', [LeaveHolidayController::class, 'index_calender']);
        Route::post('index', [LeaveHolidayController::class, 'index']);
        Route::post('edit/{id}', [LeaveHolidayController::class, 'edit']);
        Route::post('change-status', [LeaveHolidayController::class, 'change_status']);
    });

    //leave-request
    Route::group(['prefix' => 'leave-request'], function () {
        Route::get('index', [LeaveRequestController::class, 'index'])->middleware('role_or_permission_api:view leave_requests');
        Route::post('add', [LeaveRequestController::class, 'add']);
        Route::get('show/{id}', [LeaveRequestController::class, 'show'])->middleware('role_or_permission_api:view leave_requests');
        Route::delete('delete/{id}', [LeaveRequestController::class, 'delete'])->middleware('role_or_permission_api:delete leave_requests');
        Route::post('edit/{id}', [LeaveRequestController::class, 'edit'])->middleware('role_or_permission_api:update leave_requests');
        Route::post('change-status', [LeaveRequestController::class, 'change_status'])->middleware('role_or_permission_api:update leave_requests');
        Route::get('employee-leaves', [LeaveRequestController::class, 'employee_leaves'])->middleware('role_or_permission_api:view leave_requests');
        Route::get('/employee-leaves-month/{employee_id}/{form}/{to}', [LeaveRequestController::class, 'employee_leaves_month'])->middleware('role_or_permission_api:view leave_requests');
    });

    // positions
    Route::group(['prefix' => 'positions'], function () {
        Route::get('index', [PositionController::class, 'index'])->middleware('role_or_permission_api:view positions');
        Route::get('show/{id}', [PositionController::class, 'show'])->middleware('role_or_permission_api:view positions');
        Route::post('add', [PositionController::class, 'store'])->middleware('role_or_permission_api:create positions');
        Route::post('edit/{id}', [PositionController::class, 'update'])->middleware('role_or_permission_api:update positions');
        Route::delete('delete/{id}', [PositionController::class, 'delete'])->middleware('role_or_permission_api:delete positions');
    });

    Route::prefix('termination')->group(function () {
        Route::post('employee/{id}', [EmployeeTerminationController::class, 'viewed_notification'])->middleware('role_or_permission_api:viewed notification');
        Route::post('/approval-request/{id}', [EmployeeTerminationController::class, 'approval_request_change_status'])->middleware('role_or_permission_api:approval request of termination');
    });
    Route::prefix('warning_settings')->group(function () {
        Route::get('/', [WarningSettingsController::class, 'index'])
            ->name('warning_settings.index')
            ->middleware('role_or_permission_api:view warning_settings');
    });
    //salary advance settings
    Route::group(['prefix' => 'salary-advance-settings'], function () {
        Route::get('/', [SalaryAdvanceSettingController::class, 'index'])->middleware('role_or_permission_api:view salary_advance_settings');
        Route::post('/', [SalaryAdvanceSettingController::class, 'store'])->middleware('role_or_permission_api:create salary_advance_settings');
        Route::get('/{id}', [SalaryAdvanceSettingController::class, 'show'])->middleware('role_or_permission_api:view salary_advance_settings');
        Route::post('/{id}', [SalaryAdvanceSettingController::class, 'update'])->middleware('role_or_permission_api:update salary_advance_settings');
        Route::delete('/{id}', [SalaryAdvanceSettingController::class, 'destroy'])->middleware('role_or_permission_api:delete salary_advance_settings');
    });

    //job type settings
    Route::group(['prefix' => 'job-types-settings'], function () {
        Route::get('/', [JobTypesSettingController::class, 'index'])->middleware('role_or_permission_api:view job-types-settings');
        Route::post('/', [JobTypesSettingController::class, 'store'])->middleware('role_or_permission_api:create job-types-settings');
        Route::get('/{id}', [JobTypesSettingController::class, 'show'])->middleware('role_or_permission_api:view job-types-settings');
        Route::post('/{id}', [JobTypesSettingController::class, 'update'])->middleware('role_or_permission_api:update job-types-settings');
        Route::delete('/{id}', [JobTypesSettingController::class, 'destroy'])->middleware('role_or_permission_api:delete job-types-settings');
    });
    Route::get('/view/DepartmentHierarchy', [EmployeeController::class, 'getDepartmentHierarchy']);

    Route::prefix('employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'getEmployees']);
        Route::get('/view/roleandpermission', [EmployeeController::class, 'getEmployeeRoleAndPermission']);

        Route::post('/assigncuisine', [EmployeeController::class, 'assignCuisine'])->middleware('role_or_permission_api:view chef-cuisine');
        Route::get('/cuisineCategories/list', [EmployeeController::class, 'listCuisineCategories']);
        Route::get('/ChefAssignedCuisines/list', [EmployeeController::class, 'getChefAssignedCuisines'])->middleware('role_or_permission_api:view chef-cuisine');
        Route::delete('/unassignchef/{id}', [EmployeeController::class, 'unassignChef'])->middleware('role_or_permission_api:delete chef-cuisine');

        Route::get('/chefs/list', [EmployeeController::class, 'listChefs']);
        Route::post('/create/dashboardAccount', [EmployeeController::class, 'createAccessDashboard'])->middleware('role_or_permission_api:create AccessDashboard');
        Route::post('/changeSttatus/dashboardAccount', [EmployeeController::class, 'toggleDashboardAccess'])->middleware('role_or_permission_api:changeStatus AccessDashboard');

        Route::get('/cuisine-category-dishes/list/{categoryId}', [EmployeeController::class, 'listCuisineCategoryDishes']);
        Route::get('/list', [EmployeeController::class, 'index'])->middleware('role_or_permission_api:view employees');
        Route::get('/show/{id}', [EmployeeController::class, 'show'])->middleware('role_or_permission_api:view employees');
        Route::post('/create', [EmployeeController::class, 'store'])->middleware('role_or_permission_api:create employees');
        Route::post('/update/{id}', [EmployeeController::class, 'update'])->middleware('role_or_permission_api:update employees');
        Route::delete('/delete/{id}', [EmployeeController::class, 'destroy'])->middleware('role_or_permission_api:delete employees');
        Route::post('/restore/{id}', [EmployeeController::class, 'restore'])->middleware('role_or_permission_api:restore employees');
        Route::get('/team', [EmployeeController::class, 'myTeam']);
    });

    // Bonus Settings Routes
    Route::prefix('bonus-settings')->group(function () {
        Route::get('index', [BonusSettingsController::class, 'index'])->middleware('role_or_permission_api:view bonus_settings');
        Route::get('show/{id}', [BonusSettingsController::class, 'show'])->middleware('role_or_permission_api:view bonus_settings');
        Route::post('add', [BonusSettingsController::class, 'store'])->middleware('role_or_permission_api:create bonus_settings');
        Route::post('edit/{id}', [BonusSettingsController::class, 'update'])->middleware('role_or_permission_api:update bonus_settings');
        Route::get('delete/{id}', [BonusSettingsController::class, 'destroy'])->middleware('role_or_permission_api:delete bonus_settings');
    });


    // WarningSettings Routes
    Route::prefix('warning_settings')->group(function () {

        Route::post('/', [WarningSettingsController::class, 'store'])
            ->name('warning_settings.store')
            ->middleware('role_or_permission_api:create warning_settings');

        Route::put('/update/{warningSettings}', [WarningSettingsController::class, 'update'])
            ->name('warning_settings.update')
            ->middleware('role_or_permission_api:update warning_settings');
        Route::delete('/{warningSettings}', [WarningSettingsController::class, 'destroy'])
            ->name('warning_settings.destroy')
            ->middleware('role_or_permission_api:delete warning_settings');
    });

    Route::prefix('violations')->group(function () {
        Route::get('/', [ViolationController::class, 'index'])->middleware('role_or_permission_api:view violations');
        Route::get('/report', [ViolationController::class, 'frequentViolatorsReport'])->name('violations.report')->middleware('role_or_permission_api:view violation_report');
        Route::post('/create', [ViolationController::class, 'store'])->name('violations.store')->middleware('role_or_permission_api:create violations');
        Route::post('/assign/employee', [ViolationController::class, 'assignEmployee'])->name('violations.assign.employee')->middleware('role_or_permission_api:assign violations');
        Route::put('/update/{id}', [ViolationController::class, 'update'])->name('violations.update')->middleware('role_or_permission_api:update violations');
        Route::get('/employee', [ViolationController::class, 'listForEmployee'])->name('violations.listForEmployee');
    });

    Route::prefix('notifications')->group(function () {
        Route::prefix('categories')->group(function () {
            Route::get('/list', [NotificationCategoriesController::class, 'index'])->middleware('role_or_permission_api:view notification_categories');
            Route::get('/show/{id}', [NotificationCategoriesController::class, 'show'])->middleware('role_or_permission_api:view notification_categories');
            Route::post('/create', [NotificationCategoriesController::class, 'store'])->middleware('role_or_permission_api:create notification_categories');
            Route::post('/update/{id}', [NotificationCategoriesController::class, 'update'])->middleware('role_or_permission_api:update notification_categories');
        });
    });

    // penalties
    Route::group(['prefix' => 'penalties'], function () {
        // Penalty reasons
        Route::get('reasons/', [PenaltyReasonController::class, 'index'])->middleware('role_or_permission_api:view penalty_reasons');
        Route::post('reasons/store', [PenaltyReasonController::class, 'store'])->middleware('role_or_permission_api:create penalty_reasons');
        Route::get('reasons/{id}', [PenaltyReasonController::class, 'show'])->middleware('role_or_permission_api:view penalty_reasons');
        Route::put('reasons/update/{id}', [PenaltyReasonController::class, 'update'])->middleware('role_or_permission_api:update penalty_reasons');
        Route::delete('reasons/delete/{id}', [PenaltyReasonController::class, 'destroy'])->middleware('role_or_permission_api:delete penalty_reasons');
        Route::post('reasons/restore/{id}', [PenaltyReasonController::class, 'restore'])->middleware('role_or_permission_api:update penalty_reasons');
        // Penalties
        Route::get('/', [PenaltyController::class, 'index'])->middleware('role_or_permission_api:view penalties');
        Route::post('/store', [PenaltyController::class, 'store'])->middleware('role_or_permission_api:create penalties');
        Route::get('/{id}', [PenaltyController::class, 'show'])->middleware('role_or_permission_api:view penalties');
        Route::put('/update/{id}', [PenaltyController::class, 'update'])->middleware('role_or_permission_api:update penalties');
        Route::delete('/delete/{id}', [PenaltyController::class, 'destroy'])->middleware('role_or_permission_api:delete penalties');
        Route::post('/restore/{id}', [PenaltyController::class, 'restore'])->middleware('role_or_permission_api:update penalties');
        Route::get('/approval-request/list', [PenaltyController::class, 'get_approvals'])->middleware('role_or_permission_api:view employee penalty requests');
        Route::post('/approval-request/{id}', [PenaltyController::class, 'approval_request_change_status'])->middleware('role_or_permission_api:update employee penalty requests');
        Route::get('/report/data', [PenaltyController::class, 'penaltyReport'])->middleware('role_or_permission_api:view penalties report');
    });

    // deductions
    Route::group(['prefix' => 'deductions'], function () {
        // Penalties Deductions
        Route::get('penalties/', [PenaltyDeductionController::class, 'index'])->middleware('role_or_permission_api:view penalty_deductions');
        Route::post('penalties/store', [PenaltyDeductionController::class, 'save'])->middleware('role_or_permission_api:create penalty_deductions');
        Route::get('penalties/{id}', [PenaltyDeductionController::class, 'show'])->middleware('role_or_permission_api:view penalty_deductions');
        Route::put('penalties/update/{id}', [PenaltyDeductionController::class, 'save'])->middleware('role_or_permission_api:update penalty_deductions');
        Route::delete('penalties/delete/{id}', [PenaltyDeductionController::class, 'destroy'])->middleware('role_or_permission_api:delete penalty_deductions');
        Route::post('penalties/restore/{id}', [PenaltyDeductionController::class, 'restore'])->middleware('role_or_permission_api:update penalty_deductions');
        // Delays Deductions
        Route::get('delays/', [DelayDeductionController::class, 'index'])->middleware('role_or_permission_api:view delay_deductions');
        Route::post('delays/store', [DelayDeductionController::class, 'save'])->middleware('role_or_permission_api:create delay_deductions');
        Route::get('delays/{id}', [DelayDeductionController::class, 'show'])->middleware('role_or_permission_api:view delay_deductions');
        Route::put('delays/update/{id}', [DelayDeductionController::class, 'save'])->middleware('role_or_permission_api:update delay_deductions');
        Route::delete('delays/delete/{id}', [DelayDeductionController::class, 'destroy'])->middleware('role_or_permission_api:delete delay_deductions');
        Route::post('delays/restore/{id}', [DelayDeductionController::class, 'restore'])->middleware('role_or_permission_api:update delay_deductions');
    });

    //termination
    Route::prefix('termination')->group(function () {
        Route::get('/', [EmployeeTerminationController::class, 'allTermination'])->middleware('role_or_permission_api:all termination');
        Route::get('/{id}', [EmployeeTerminationController::class, 'showTermination'])->middleware('role_or_permission_api:show termination');
        Route::post('/', [EmployeeTerminationController::class, 'addTermination'])->middleware('role_or_permission_api:add termination');
        Route::post('update/{id}', [EmployeeTerminationController::class, 'updateTermination'])->middleware('role_or_permission_api:update termination');
        Route::delete('/{id}', [EmployeeTerminationController::class, 'deleteTermination'])->middleware('role_or_permission_api:delete termination');
        Route::post('/approval-request/{id}', [EmployeeTerminationController::class, 'approval_request_change_status'])->middleware('role_or_permission_api:approval request of termination');
        Route::get('/show/report', [EmployeeTerminationController::class, 'generate_termination_report'])->middleware('role_or_permission_api:generate termination report');
    });

    //Alert
    Route::get('/type-notification', [AlertSettingsController::class, 'typeNotificationAllow'])->middleware('role_or_permission_api:show notification active');
    Route::prefix('alert')->group(callback: function () {
        Route::get('/', [AlertSettingsController::class, 'allTypeAlert'])->middleware('role_or_permission_api:view type alert');
        Route::get('/attendance-events', [AlertSettingsController::class, 'showAttendanceEvents'])->middleware('role_or_permission_api:all attendance event');
        Route::post('/', [AlertSettingsController::class, 'sendNotification'])->middleware('role_or_permission_api:send alert');
    });

    Route::prefix('company-policies')->group(function () {
        Route::get('/', [CompanyPolicyController::class, 'index'])
            ->middleware('role_or_permission_api:view company_policies');
        Route::get('/{id}', [CompanyPolicyController::class, 'show'])
            ->middleware('role_or_permission_api:view company_policies');
        Route::get('/{id}/acknowledgements', [CompanyPolicyController::class, 'acknowledgements'])
            ->middleware('role_or_permission_api:view company_policies_acknowledgements');
        Route::post('/', [CompanyPolicyController::class, 'store'])
            ->middleware('role_or_permission_api:create company_policies');
        Route::post('/{id}', [CompanyPolicyController::class, 'update'])
            ->middleware('role_or_permission_api:update company_policies');
        Route::delete('/{id}', [CompanyPolicyController::class, 'destroy'])
            ->middleware('role_or_permission_api:delete company_policies');
    });

    Route::prefix('employee-warnings')->group(function () {
        Route::get('/', [EmployeeWarningController::class, 'index'])
            ->middleware('role_or_permission_api:view warnings');
        Route::get('/track-employee-warnings', [EmployeeWarningController::class, 'employeeWarnings'])
            ->middleware('role_or_permission_api:view employee_warnings');
        Route::get('/{id}', [EmployeeWarningController::class, 'show'])
            ->middleware('role_or_permission_api:view warnings');
        Route::post('/', [EmployeeWarningController::class, 'store'])
            ->middleware('role_or_permission_api:create warnings');
        Route::post('/{id}', [EmployeeWarningController::class, 'update'])
            ->middleware('role_or_permission_api:update warnings');
        Route::post('/{id}/acknowledge', [EmployeeWarningController::class, 'acknowledge'])
            ->middleware('role_or_permission_api:acknowledge warnings');
        Route::post('/{id}/confirm-delivery', [EmployeeWarningController::class, 'confirmDelivery'])
            ->middleware('role_or_permission_api:confirm warnings_delivery');
        Route::delete('/{id}', [EmployeeWarningController::class, 'destroy'])
            ->middleware('role_or_permission_api:delete warnings');
    });

    //overtime-type
    Route::group(['prefix' => 'overtime-type'], function () {
        Route::get('index', [OvertimeTypeController::class, 'index']);
        Route::post('add', [OvertimeTypeController::class, 'add']);
        Route::post('edit', [OvertimeTypeController::class, 'edit']);
        Route::get('delete/{id}', [OvertimeTypeController::class, 'delete']);
    });

    //OvertimeSetting
    Route::group(['prefix' => 'overtime-setting'], function () {
        Route::get('index', [OvertimeSettingController::class, 'index']);
        Route::post('add', [OvertimeSettingController::class, 'add']);
        Route::post('edit', [OvertimeSettingController::class, 'edit']);
        Route::get('delete/{id}', [OvertimeSettingController::class, 'delete']);
    });

    //excuses requests
    Route::prefix('excuse-requests')->group(function () {
        Route::get('list', [ExcuseRequestController::class, 'index'])->name('excuse_requests.index');
        Route::get('view/{id}', [ExcuseRequestController::class, 'show'])->name('excuse_requests.show');
        Route::post('create', [ExcuseRequestController::class, 'store'])->name('excuse_requests.store');
        Route::get('/pending', [ExcuseRequestController::class, 'pendingRequests'])->name('excuse-requests.pending');
        Route::put('approve/{id}', [ExcuseRequestController::class, 'approve'])->name('excuse_requests.approve');
        Route::put('reject/{id}', [ExcuseRequestController::class, 'reject'])->name('excuse_requests.reject');
        Route::put('cancel/{id}', [ExcuseRequestController::class, 'cancel'])->name('excuse_requests.cancel');
        Route::post('restore/{id}', [ExcuseRequestController::class, 'restore'])->name('excuse_requests.restore');
        Route::put('{id}/update', [ExcuseRequestController::class, 'update'])->name('excuse-requests.update');
    });

    // Excuse Settings Routes
    Route::prefix('excuse-settings')->group(function () {
        Route::get('/show', [ExcuseSettingController::class, 'show'])->name('excuse-settings.show');
        Route::put('/update', [ExcuseSettingController::class, 'update'])->name('excuse-settings.update');
    });

    Route::prefix('timetables')->group(function () {
        Route::get('/list', [TimetableController::class, 'index'])->middleware('role_or_permission_api:view timetables');
        Route::post('/create', [TimetableController::class, 'store'])->middleware('role_or_permission_api:create timetables');
        Route::get('/show/{id}', [TimetableController::class, 'show'])->middleware('role_or_permission_api:view timetables');
        Route::put('/update/{id}', [TimetableController::class, 'update'])->middleware('role_or_permission_api:update timetables');
        Route::delete('/delete/{id}', [TimetableController::class, 'destroy'])->middleware('role_or_permission_api:delete timetables');
        Route::post('/restore/{id}', [TimetableController::class, 'restore'])->middleware('role_or_permission_api:restore timetables');
    });

    Route::prefix('shifts')->group(function () {
        Route::get('/list', [ShiftController::class, 'index'])->middleware('role_or_permission_api:view shifts');
        Route::post('/create', [ShiftController::class, 'store'])->middleware('role_or_permission_api:create shifts');
        Route::get('/show/{id}', [ShiftController::class, 'show'])->middleware('role_or_permission_api:view shifts');
        Route::put('/update/{id}', [ShiftController::class, 'update'])->middleware('role_or_permission_api:update shifts');
        Route::delete('/delete/{id}', [ShiftController::class, 'destroy'])->middleware('role_or_permission_api:delete shifts');
        Route::post('/restore/{id}', [ShiftController::class, 'restore']);
    });

    Route::prefix('employee-schedules')->group(function () {
        Route::get('/list', [EmployeeScheduleController::class, 'index'])->name('employee-schedules.index');
        Route::post('/create', [EmployeeScheduleController::class, 'store'])->name('employee-schedules.store');
        Route::post('/create-default', [EmployeeScheduleController::class, 'setDefault'])->name('employee-schedules.storeDefault');
        Route::get('/show/{id}', [EmployeeScheduleController::class, 'show'])->name('employee-schedules.show');
        Route::put('/update/{id}', [EmployeeScheduleController::class, 'update'])->name('employee-schedules.update');
        Route::delete('/delete/{id}', [EmployeeScheduleController::class, 'destroy'])->name('employee-schedules.destroy');
        Route::post('/restore/{id}', [EmployeeScheduleController::class, 'restore'])->name('employee-schedules.restore');
        Route::get('/my-schedules', [EmployeeScheduleController::class, 'getEmployeeSchedules'])->name('employee-schedules.mySchedules');
    });

    Route::group(['prefix' => 'biotime'], function () {
        Route::post('/clock-in-out', [BioTimeController::class, 'clockInOut']);
    });

    // Attendance
    Route::group(['prefix' => 'attendance'], function () {
        Route::get('/my', [AttendanceController::class, 'getMyAttendance']);
        Route::get('/lastActivity', [AttendanceController::class, 'lastActivity']);
        Route::get('/my/summary', [AttendanceController::class, 'getMyAttendanceSummary']);
        Route::get('/employee/{employeeId}', [AttendanceController::class, 'getAttendanceByEmployee'])->middleware('role_or_permission_api:view attendance');
        Route::get('/all', [AttendanceController::class, 'getAllAttendance'])->middleware('role_or_permission_api:view attendance');
        Route::get('/summary', [AttendanceController::class, 'getAttendanceSummary'])->middleware('role_or_permission_api:view attendance');
        Route::get('/daily-report', [AttendanceController::class, 'getDailyAttendanceReport'])->middleware('role_or_permission_api:view attendance');
    });
    // Route::get('/range-report', [AttendanceController::class, 'generateRangeReport'])->middleware('role_or_permission_api:view attendance');

    Route::prefix('late-deduction-settings')->group(function () {
        Route::get('/', [LateDeductionSettingController::class, 'index']);
        Route::get('/{id}', [LateDeductionSettingController::class, 'show']);
        Route::post('/', [LateDeductionSettingController::class, 'store']);
        Route::put('/{id}', [LateDeductionSettingController::class, 'update']);
        Route::delete('/{id}', [LateDeductionSettingController::class, 'destroy']);
    });
    Route::prefix('absence-settings')->group(function () {
        Route::get('/', [AbsenceSettingController::class, 'index']);
        Route::get('/{id}', [AbsenceSettingController::class, 'show']);
        Route::post('/', [AbsenceSettingController::class, 'store']);
        Route::put('/{id}', [AbsenceSettingController::class, 'update']);
        Route::delete('/{id}', [AbsenceSettingController::class, 'destroy']);
    });
    Route::group(['prefix' => 'hr-settings'], function () {
        Route::get('/', [HrSettingController::class, 'index'])->middleware('role_or_permission_api:view hr_settings');
        Route::post('/', [HrSettingController::class, 'store'])->middleware('role_or_permission_api:create hr_settings');
        Route::get('/branch/{branchId}', [HrSettingController::class, 'getByBranch'])->middleware('role_or_permission_api:view hr_settings');
        Route::post('/branch/{branchId}', [HrSettingController::class, 'updateByBranch'])->middleware('role_or_permission_api:update hr_settings');
        Route::delete('/branch/{branchId}', [HrSettingController::class, 'destroy'])->middleware('role_or_permission_api:delete hr_settings');
    });


    Route::group(['prefix' => 'penalties'], function () {
        // Penalty reasons
        Route::get('reasons/', [PenaltyReasonController::class, 'index']);
        Route::post('reasons/store', [PenaltyReasonController::class, 'store']);
        Route::get('reasons/{id}', [PenaltyReasonController::class, 'show']);
        Route::put('reasons/update/{id}', [PenaltyReasonController::class, 'update']);
        Route::delete('reasons/delete/{id}', [PenaltyReasonController::class, 'destroy']);
        Route::post('reasons/restore/{id}', [PenaltyReasonController::class, 'restore']);
        // Penalties
        Route::get('/', [PenaltyController::class, 'index']);
        Route::post('/store', [PenaltyController::class, 'store']);
        Route::get('/{id}', [PenaltyController::class, 'show']);
        Route::put('/update/{id}', [PenaltyController::class, 'update']);
        Route::delete('/delete/{id}', [PenaltyController::class, 'destroy']);
        Route::post('/restore/{id}', [PenaltyController::class, 'restore']);
        Route::post('/approval-request/{id}', [PenaltyController::class, 'approval_request_change_status']);
        Route::post('/request', [PenaltyController::class, 'getRequest']);
    });

    Route::group(['prefix' => 'delays'], function () {
        // Delay times
        Route::get('times/', [DelayTimeController::class, 'index']);
        Route::post('times/store', [DelayTimeController::class, 'store']);
        Route::get('times/{id}', [DelayTimeController::class, 'show']);
        Route::put('times/update/{id}', [DelayTimeController::class, 'update']);
        Route::delete('times/delete/{id}', [DelayTimeController::class, 'destroy']);
        Route::post('times/restore/{id}', [DelayTimeController::class, 'restore']);
        // Delays
        Route::get('/', [DelayController::class, 'index']);
        Route::post('/store', [DelayController::class, 'store']);
        Route::get('/{id}', [DelayController::class, 'show']);
        Route::put('/update/{id}', [DelayController::class, 'update']);
        Route::delete('/delete/{id}', [DelayController::class, 'destroy']);
        Route::post('/restore/{id}', [DelayController::class, 'restore']);
    });

    Route::group(['prefix' => 'advances'], function () {
        // Advance Settings
        Route::get('settings/', [AdvanceSettingController::class, 'index']);
        Route::post('settings/store', [AdvanceSettingController::class, 'store']);
        Route::get('settings/{id}', [AdvanceSettingController::class, 'show']);
        Route::put('settings/update/{id}', [AdvanceSettingController::class, 'update']);
        Route::delete('settings/delete/{id}', [AdvanceSettingController::class, 'destroy']);
        Route::post('settings/restore/{id}', [AdvanceSettingController::class, 'restore']);
        // Advance Requests
        Route::get('requests/', [AdvanceRequestController::class, 'index']);
        Route::post('requests/store', [AdvanceRequestController::class, 'save']);
        Route::get('requests/{id}', [AdvanceRequestController::class, 'show']);
        Route::put('requests/update/{id}', [AdvanceRequestController::class, 'save']);
        Route::delete('requests/delete/{id}', [AdvanceRequestController::class, 'destroy']);
        Route::post('requests/restore/{id}', [AdvanceRequestController::class, 'restore']);
        // Advance
        Route::get('/', [AdvanceController::class, 'index']);
        Route::post('/store', [AdvanceController::class, 'save']);
        Route::get('/{id}', [AdvanceController::class, 'show']);
        Route::put('/update/{id}', [AdvanceController::class, 'save']);
        Route::delete('/delete/{id}', [AdvanceController::class, 'destroy']);
        Route::post('/restore/{id}', [AdvanceController::class, 'restore']);
    });

    Route::group(['prefix' => 'deductions'], function () {
        // Penalties Deductions
        Route::get('penalties/', [PenaltyDeductionController::class, 'index']);
        Route::post('penalties/store', [PenaltyDeductionController::class, 'save']);
        Route::get('penalties/{id}', [PenaltyDeductionController::class, 'show']);
        Route::put('penalties/update/{id}', [PenaltyDeductionController::class, 'save']);
        Route::delete('penalties/delete/{id}', [PenaltyDeductionController::class, 'destroy']);
        Route::post('penalties/restore/{id}', [PenaltyDeductionController::class, 'restore']);
        // Delays Deductions
        Route::get('delays/', [DelayDeductionController::class, 'index']);
        Route::post('delays/store', [DelayDeductionController::class, 'save']);
        Route::get('delays/{id}', [DelayDeductionController::class, 'show']);
        Route::put('delays/update/{id}', [DelayDeductionController::class, 'save']);
        Route::delete('delays/delete/{id}', [DelayDeductionController::class, 'destroy']);
        Route::post('delays/restore/{id}', [DelayDeductionController::class, 'restore']);
    });

    //Payroll
    Route::group(['prefix' => 'payrolls'], function () {
        Route::get('/', [PayrollController::class, 'index']);
        Route::post('/store', [PayrollController::class, 'save']);
        Route::get('/{id}', [PayrollController::class, 'show']);
        Route::get('employee/{id}', [PayrollController::class, 'showEmployee']);
        Route::put('/update/{id}', [PayrollController::class, 'save']);
        Route::delete('/delete/{id}', [PayrollController::class, 'destroy']);
        Route::post('/restore/{id}', [PayrollController::class, 'restore']);
        Route::post('/termination/{id}', [PayrollController::class, 'PayrollTermination']);
    });

    Route::prefix('devices')->group(function () {
        Route::get('/', [DeviceController::class, 'index'])->name('devices.index');
        Route::get('/{id}', [DeviceController::class, 'show'])->name('devices.show');
        Route::post('/', [DeviceController::class, 'store'])->name('devices.store');
        Route::put('/{id}', [DeviceController::class, 'update'])->name('devices.update');
        Route::delete('/{id}', [DeviceController::class, 'destroy'])->name('devices.destroy');
    });

    Route::post('/biotime/add-employee', [EmployeeDeviceController::class, 'addEmployeeToDevice'])->name('biotime.add_employee');
    // HR Reports routes
    Route::controller(HrReportController::class)
        ->prefix('hr-reports')
        ->group(function () {
            Route::get('delays', 'listDelaysReport');
            Route::get('delays/{id}', 'employeeDelaysReport');
            Route::get('penalties', 'listPenaltiesReport');
            Route::get('penalties/{id}', 'employeePenaltiesReport');
            Route::get('advances', 'listAdvancesReport');
            Route::get('advances/{id}', 'employeeAdvancesReport');
            Route::get('payrolls', 'listPayrollsReport');
            Route::get('payrolls/{id}', 'employeePayrollsReport');
            Route::get('payroll/{id}', 'employeePayrollReport');
            Route::get('employees/details', 'listEmployeesReport');
            Route::get('employees/details/{id}', 'employeeReport');
        });

    //employee-opening-balances
    Route::group(['prefix' => 'employee-opening-balances'], function () {
        Route::post('open-day-balance', [EmployeeOpeningBalanceController::class, 'open_day_balance']);
        Route::post('close-day-balance', [EmployeeOpeningBalanceController::class, 'close_day_balance']);
    });

    Route::post('/employees/by-department', [EmployeeController::class, 'getEmployeeByDepartment']);
    Route::post('/children/employees', [EmployeeController::class, 'getChildrenEmployee']);

    Route::group(['prefix' => 'departments'], function () {

        Route::post('/', [DepartmentController::class, 'index']);
        Route::get('/list', [DepartmentController::class, 'list'])->middleware('role_or_permission_api:view departments');
        Route::get('/show/{id}', [DepartmentController::class, 'show'])->middleware('role_or_permission_api:view departments');
        Route::post('/add', [DepartmentController::class, 'store'])->middleware('role_or_permission_api:create departments');
        Route::post('/update/{id}', [DepartmentController::class, 'update'])->middleware('role_or_permission_api:update departments');
        Route::delete('/delete/{id}', [DepartmentController::class, 'destroy'])->middleware('role_or_permission_api:delete departments');
    });
    Route::group(['prefix' => 'bonus_requests'], function () {

        Route::get('/', [BounsController::class, 'index'])->middleware('role_or_permission_api:view bonus_requests');
        Route::post('/create', [BounsController::class, 'create'])->middleware('role_or_permission_api:create bonus_request');
        Route::post('/change_status/{id}', [BounsController::class, 'changeStatus'])->middleware('role_or_permission_api:change bonus_request_status');
    });
    // Show available HR services (Leave Request, Salary Advance, etc.)

    Route::prefix('hr-requests')->group(function () {
        // Get all HR requests
        Route::get('/', [HRRequestController::class, 'index']);

        // Get a single HR request by ID
        Route::get('/show/{id}', [HRRequestController::class, 'show']);

        // Create a new HR request
        // Route::post('/store', [HRRequestController::class, 'store']);

        // Update an existing HR request
        // Route::post('/update/{id}', [HRRequestController::class, 'update']);

        // Delete an HR request
        // Route::post('/delete/{id}', [HRRequestController::class, 'destroy']);
    });
    Route::prefix('emplyee-rates')->group(function () {
        Route::get('/', [EmployeeRateController::class, 'index']);
        Route::post('/', [EmployeeRateController::class, 'store']);
    });


    Route::prefix('hr-services')->group(function () {
        // Get all HR services
        Route::get('/', [HRServiceController::class, 'index']);

        // Get a single HR service by ID
        Route::get('/show/{id}', [HRServiceController::class, 'show']);

        // Create a new HR service
        Route::post('/store', [HRServiceController::class, 'store']);

        // Update an existing HR service
        Route::post('/update/{id}', [HRServiceController::class, 'update']);

        // Delete an HR service
        Route::post('/delete/{id}', [HRServiceController::class, 'destroy']);
    });


    Route::prefix('salary-advance-requests')->group(function () {
        // Get all requests
        Route::get('/', [SalaryAdvanceRequestController::class, 'index']);

        // Get a single request by ID
        Route::get('/show/{id}', [SalaryAdvanceRequestController::class, 'show']);

        // Create a new request
        Route::post('/store', [SalaryAdvanceRequestController::class, 'store']);

        Route::post('/change_status/{id}', [SalaryAdvanceRequestController::class, 'changeStatus']);
        // Update a request by ID
        Route::post('/update/{id}', [SalaryAdvanceRequestController::class, 'update']);

        // Delete a request by ID
        Route::delete('/delete/{id}', [SalaryAdvanceRequestController::class, 'destroy']);
    });


    Route::prefix('document_types')->controller(DocumentTypeController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/store', 'store');
    });
    Route::prefix('employee_documents')->controller(EmployeeDocumentController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/store', 'store');
    });

    //privilege-type
    Route::group(['prefix' => 'privilege-types'], function () {
        Route::get('/', [PrivilegeTypesController::class, 'index'])->middleware('role_or_permission_api:view privilege types');
        Route::get('/{id}', [PrivilegeTypesController::class, 'show'])->middleware('role_or_permission_api:view privilege types');
        Route::post('/', [PrivilegeTypesController::class, 'add'])->middleware('role_or_permission_api:add privilege types');
        Route::post('/{id}', [PrivilegeTypesController::class, 'edit'])->middleware('role_or_permission_api:update privilege types');
        Route::delete('/{id}', [PrivilegeTypesController::class, 'delete'])->middleware('role_or_permission_api:delete privilege types');
    });

    //job-related-penalty
    Route::group(['prefix' => 'job-related-penalty'], function () {
        Route::get('/', [JobRelatedPenaltyController::class, 'index']);
        Route::get('/report', [JobRelatedPenaltyController::class, 'report']);
        Route::get('/{id}', [JobRelatedPenaltyController::class, 'show']);
        Route::post('/', [JobRelatedPenaltyController::class, 'add']);
        Route::post('/changeStatus/{id}', [JobRelatedPenaltyController::class, 'changeStatus']);
        Route::post('/{id}', [JobRelatedPenaltyController::class, 'update']);
        Route::delete('/{id}', [JobRelatedPenaltyController::class, 'delete']);
    });
});

Route::post('waiter-login', [EmployeeAuthController::class, 'login'])->name('login.waiter');

Route::middleware(['employee.auth', 'employee.flag:waiter'])->group(function () {



    Route::get('waiter-logout',  [EmployeeAuthController::class, 'logout'])->name('logout.waiter');

    Route::post('waiter/update-profile', [EmployeeAuthController::class, 'updateProfile'])->name('update.waiter');
    Route::get('waiter/update-profile', [EmployeeAuthController::class, 'updateProfile'])->name('get.profile.waiter');
});

Route::post('delivery-login', [EmployeeAuthController::class, 'login'])->name('login.delivery');

Route::middleware(['employee.auth', 'employee.flag:driver'])->group(function () {

    Route::get('delivery-logout', [EmployeeAuthController::class, 'logout'])->name('logout.delivery');

    Route::post('delivery/update-profile', [EmployeeAuthController::class, 'updateProfile'])->name('update.delivery');
    Route::get('delivery/update-profile', [EmployeeAuthController::class, 'updateProfile'])->name('get.profile.delivery');
});

Route::post('cashier-login', [EmployeeAuthController::class, 'login'])->name('login.cashier');


Route::middleware(['employee.auth', 'employee.flag:cashier'])->group(function () {
    Route::get('cashier-logout', [EmployeeAuthController::class, 'logout'])->name('logout.cashier');
    Route::post('cashier/update-profile', [EmployeeAuthController::class, 'updateProfile'])->name('update.cashier');
    Route::get('cashier/update-profile', [EmployeeAuthController::class, 'updateProfile'])->name('get.profile.cashier');
});

Route::post('customer-service-login', [EmployeeAuthController::class, 'login'])->name('login.customer-service');
Route::middleware(['employee.auth', 'employee.flag:customer_service'])->group(function () {
    Route::get('customer-service-logout',  [EmployeeAuthController::class, 'logout'])->name('logout.customer-service');
    Route::post('customer-service/update-profile', [EmployeeAuthController::class, 'updateProfile'])->name('update.customer-service');
    Route::get('customer-service/update-profile', [EmployeeAuthController::class, 'updateProfile'])->name('get.profile.customer-service');
});

Route::post('kitchen-login', [EmployeeAuthController::class, 'login'])->name('login.kitchen');
Route::middleware(['employee.auth', 'employee.flag:Head Chef'])->group(function () {
    Route::get('kitchen-logout',  [EmployeeAuthController::class, 'logout'])->name('logout.kitchen');
});
