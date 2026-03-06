<?php


use App\Http\Controllers\Api\Finance\AccountingOperationController;
use App\Http\Controllers\Api\Finance\AccountingOperationLogController;
use App\Http\Controllers\Api\Finance\AssetDepreciationController;
use App\Http\Controllers\Api\Finance\AssetDepreciationLogController;
use App\Http\Controllers\Api\Finance\AssetMaintenanceController;
use App\Http\Controllers\Api\Finance\AssetMaintenanceLogController;
use App\Http\Controllers\Api\Finance\AssetSaleController;
use App\Http\Controllers\Api\Finance\AssetSaleLogController;
use App\Http\Controllers\Api\Finance\AuditSettingController;
use App\Http\Controllers\Api\Finance\AuditSettingLogController;
use App\Http\Controllers\Api\Finance\AuditStatusController;
use App\Http\Controllers\Api\Finance\AuditStatusLogController;
use App\Http\Controllers\Api\Finance\BankNameController;
use App\Http\Controllers\Api\Finance\BranchController;
use App\Http\Controllers\Api\Finance\CostCenterController;
use App\Http\Controllers\Api\Finance\CostCenterLogController;
use App\Http\Controllers\Api\Finance\CurrencyController;
use App\Http\Controllers\Api\Finance\EmploymentIncomeTaxController;
use App\Http\Controllers\Api\Finance\EmploymentIncomeTaxLogController;
use App\Http\Controllers\Api\Finance\FinanceSettingController;
use App\Http\Controllers\Api\Finance\FinanceSettingLogController;
use App\Http\Controllers\Api\Finance\InsuranceController;
use App\Http\Controllers\Api\Finance\InsuranceEmployeeController;
use App\Http\Controllers\Api\Finance\InsuranceEmployeeLogController;
use App\Http\Controllers\Api\Finance\InsuranceLogController;
use App\Http\Controllers\Api\Finance\ManualEntryTypeController;
use App\Http\Controllers\Api\Finance\NotificationStatusController;
use App\Http\Controllers\Api\Finance\NotificationStatusLogController;
use App\Http\Controllers\Api\Finance\PartnerCompanyController;
use App\Http\Controllers\Api\Finance\PartnerCompanyLogController;
use App\Http\Controllers\Api\Finance\PartnerController;
use App\Http\Controllers\Api\Finance\PartnerLogController;
use App\Http\Controllers\Api\Finance\ReceiptStatusController;
use App\Http\Controllers\Api\Finance\PeriodicLiabilityLogController;
use App\Http\Controllers\Api\Finance\PeriodicLiabilityController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['employee.auth', 'employee.flag:admin', 'role_or_permission_api:Finance']], function () {
    // currencies
    Route::prefix('currencies')->group(function () {
        Route::get('/', [CurrencyController::class, 'index'])->middleware('role_or_permission_api:currencies.view');
        Route::post('/', [CurrencyController::class, 'store'])->middleware('role_or_permission_api:currencies.create');;
        Route::delete('/{currency_id}', [CurrencyController::class, 'destroy'])->middleware('role_or_permission_api:currencies.delete');;
        Route::put('/{currency_id}', [CurrencyController::class, 'update'])->middleware('role_or_permission_api:currencies.edit');;
    });

    // bank names
    Route::prefix('bank_names')->group(function () {
        Route::get('/', [BankNameController::class, 'index'])->middleware('role_or_permission_api:bank_names.view');
        Route::post('/', [BankNameController::class, 'store'])->middleware('role_or_permission_api:bank_names.create');;
        Route::delete('/{bank_name_id}', [BankNameController::class, 'destroy'])->middleware('role_or_permission_api:bank_names.delete');;
        Route::put('/{bank_name_id}', [BankNameController::class, 'update'])->middleware('role_or_permission_api:bank_names.edit');;
    });

    // accounting operation
    Route::prefix('accounting_operations')->group(function () {
        Route::get('/log', [AccountingOperationLogController::class, 'index'])->middleware('role_or_permission_api:accounting_operation_setting.log');
        Route::get('/', [AccountingOperationController::class, 'index'])->middleware('role_or_permission_api:accounting_operation_setting.view');
        Route::post('/', [AccountingOperationController::class, 'store'])->middleware('role_or_permission_api:accounting_operation_setting.create');
        Route::delete('/{accounting_operation_id}', [AccountingOperationController::class, 'destroy'])->middleware('role_or_permission_api:accounting_operation_setting.delete');;
        Route::put('/{accounting_operation_id}', [AccountingOperationController::class, 'update'])->middleware('role_or_permission_api:accounting_operation_setting.edit');;
    });

    // branches
    Route::get('company/{company_profile_setting_id}/branches', [BranchController::class, 'index'])->middleware('role_or_permission_api:branches.view');

    // partner
    Route::prefix('partners')->group(function () {
        Route::get('/log', [PartnerLogController::class, 'index'])->middleware('role_or_permission_api:partner.log');
        Route::get('/', [PartnerController::class, 'index'])->middleware('role_or_permission_api:partner.view');
        Route::post('/', [PartnerController::class, 'store'])->middleware('role_or_permission_api:partner.create');;
        Route::delete('/{partner_id}', [PartnerController::class, 'destroy'])->middleware('role_or_permission_api:partner.delete');;
        Route::put('/{partner_id}', [PartnerController::class, 'update'])->middleware('role_or_permission_api:partner.edit');
    });

    // partner companies
    Route::prefix('partner_companies')->group(function () {
        Route::get('/log', [PartnerCompanyLogController::class, 'index'])->middleware('role_or_permission_api:partner_company.log');
        Route::get('/', [PartnerCompanyController::class, 'index'])->middleware('role_or_permission_api:partner_company.view');
        Route::post('/', [PartnerCompanyController::class, 'store'])->middleware('role_or_permission_api:partner_company.create');
        Route::delete('/{partner_company_id}', [PartnerCompanyController::class, 'destroy'])->middleware('role_or_permission_api:partner_company.delete');
        Route::put('/{partner_company_id}', [PartnerCompanyController::class, 'update'])->middleware('role_or_permission_api:partner_company.edit');
    });

    // manual entry types
    Route::prefix('manual_entry_types')->group(function () {
        Route::get('/', [ManualEntryTypeController::class , 'index'])->middleware('role_or_permission_api:manual_entry_type.view');
        Route::post('/', [ManualEntryTypeController::class, 'store'])->middleware('role_or_permission_api:manual_entry_type.create');
        Route::delete('/{manual_entry_type_id}', [ManualEntryTypeController::class, 'destroy'])->middleware('role_or_permission_api:manual_entry_type.delete');
        Route::put('/{manual_entry_type_id}', [ManualEntryTypeController::class, 'update'])->middleware('role_or_permission_api:manual_entry_type.edit');
    });

    // audit statuses
    Route::prefix('audit_statuses')->group(function () {
        Route::get('/log', [AuditStatusLogController::class, 'index'])->middleware('role_or_permission_api:audit_status.log');
        Route::get('/', [AuditStatusController::class, 'index'])->middleware('role_or_permission_api:audit_status.view');
        Route::post('/', [AuditStatusController::class, 'store'])->middleware('role_or_permission_api:audit_status.create');
        Route::delete('/{audit_status_id}', [AuditStatusController::class, 'destroy'])->middleware('role_or_permission_api:audit_status.delete');
        Route::put('/{audit_status_id}', [AuditStatusController::class, 'update'])->middleware('role_or_permission_api:audit_status.edit');
    });

    // receipt statuses
    Route::prefix('receipt_statuses')->group(function () {
        Route::get('/', [ReceiptStatusController::class, 'index'])->middleware('role_or_permission_api:receipt_status.view');
        Route::post('/', [ReceiptStatusController::class, 'store'])->middleware('role_or_permission_api:receipt_status.create');
        Route::delete('/{receipt_status_id}', [ReceiptStatusController::class, 'destroy'])->middleware('role_or_permission_api:receipt_status.delete');
        Route::put('/{receipt_status_id}', [ReceiptStatusController::class, 'update'])->middleware('role_or_permission_api:receipt_status.edit');
    });

    // finance settings
    Route::prefix('finance_settings')->group(function () {
        Route::put('/', [FinanceSettingController::class, 'update'])->middleware('role_or_permission_api:finance_setting.edit');
        Route::get('/', [FinanceSettingController::class, 'index'])->middleware('role_or_permission_api:finance_setting.view');
        Route::get('/log', [FinanceSettingLogController::class, 'index'])->middleware('role_or_permission_api:finance_setting.log');
    });

    // notification status settings
    Route::prefix('notification_status_settings')->group(function () {
        Route::put('/', [NotificationStatusController::class, 'update'])->middleware('role_or_permission_api:notification_status_setting.edit');
        Route::get('/', [NotificationStatusController::class, 'index'])->middleware('role_or_permission_api:notification_status_setting.view');
        Route::get('/log', [NotificationStatusLogController::class, 'index'])->middleware('role_or_permission_api:notification_status_setting.log');
    });

    // audit setting
    Route::prefix('audit_settings')->group(function () {
        Route::get('/log', [AuditSettingLogController::class, 'index'])->middleware('role_or_permission_api:audit_setting.log');
        Route::get('/', [AuditSettingController::class, 'index'])->middleware('role_or_permission_api:audit_setting.view');
        Route::post('/', [AuditSettingController::class, 'store'])->middleware('role_or_permission_api:audit_setting.create');
        Route::delete('/{audit_setting_id}', [AuditSettingController::class, 'destroy'])->middleware('role_or_permission_api:audit_setting.delete');
        Route::put('/{audit_setting_id}', [AuditSettingController::class, 'update'])->middleware('role_or_permission_api:audit_setting.edit');
    });

    Route::prefix('insurances')->group(function () {
        Route::get('/log', [InsuranceLogController::class, 'index'])->middleware('role_or_permission_api:insurance.log');
        Route::get('/', [InsuranceController::class, 'index'])->middleware('role_or_permission_api:insurance.view');
        Route::post('/', [InsuranceController::class, 'store'])->middleware('role_or_permission_api:insurance.create');
        Route::delete('/{insurance_id}', [InsuranceController::class, 'destroy'])->middleware('role_or_permission_api:insurance.delete');
        Route::put('/{insurance_id}', [InsuranceController::class, 'update'])->middleware('role_or_permission_api:insurance.edit');
    });

    // employment income taxes
    Route::prefix('employment_income_taxes')->group(function () {
        Route::get('/log', [EmploymentIncomeTaxLogController::class, 'index'])->middleware('role_or_permission_api:employee_income_tax.log');
        Route::get('/', [EmploymentIncomeTaxController::class, 'index'])->middleware('role_or_permission_api:employee_income_tax.view');
        Route::post('/', [EmploymentIncomeTaxController::class, 'store'])->middleware('role_or_permission_api:employee_income_tax.create');
        Route::delete('/{employment_income_tax_id}', [EmploymentIncomeTaxController::class, 'destroy'])->middleware('role_or_permission_api:employee_income_tax.delete');
        Route::put('/{employment_income_tax_id}', [EmploymentIncomeTaxController::class, 'update'])->middleware('role_or_permission_api:employee_income_tax.edit');
    });

    // cost centers
    Route::prefix('cost_centers')->group(function () {
        Route::get('/log', [CostCenterLogController::class, 'index'])->middleware('role_or_permission_api:cost_center.log');
        Route::get('/', [CostCenterController::class, 'index'])->middleware('role_or_permission_api:cost_center.view');
        Route::post('/', [CostCenterController::class, 'store'])->middleware('role_or_permission_api:cost_center.create');
        Route::delete('/{cost_center_id}', [CostCenterController::class, 'destroy'])->middleware('role_or_permission_api:cost_center.delete');
        Route::put('/{cost_center_id}', [CostCenterController::class, 'update'])->middleware('role_or_permission_api:cost_center.edit');
    });

    // periodic liabilities
    Route::prefix('periodic_liabilities')->group(function () {
        Route::get('/log', [PeriodicLiabilityLogController::class, 'index'])->middleware('role_or_permission_api:periodic_liability.log');
        Route::get('/', [PeriodicLiabilityController::class, 'index'])->middleware('role_or_permission_api:periodic_liability.view');
        Route::post('/', [PeriodicLiabilityController::class, 'store'])->middleware('role_or_permission_api:periodic_liability.create');
        Route::delete('/{periodic_liability_id}', [PeriodicLiabilityController::class, 'destroy'])->middleware('role_or_permission_api:periodic_liability.delete');
        Route::put('/{periodic_liability_id}', [PeriodicLiabilityController::class, 'update'])->middleware('role_or_permission_api:periodic_liability.edit');
    });

    // asset depreciation
    Route::prefix('asset_depreciations')->group(function () {
        Route::get('/log', [AssetDepreciationLogController::class, 'index'])->middleware('role_or_permission_api:asset_depreciation.log');
        Route::get('/', [AssetDepreciationController::class, 'index'])->middleware('role_or_permission_api:asset_depreciation.view');
        Route::post('/', [AssetDepreciationController::class, 'store'])->middleware('role_or_permission_api:asset_depreciation.create');
        Route::delete('/{asset_depreciation_id}', [AssetDepreciationController::class, 'destroy'])->middleware('role_or_permission_api:asset_depreciation.delete');
        Route::put('/{asset_depreciation_id}', [AssetDepreciationController::class, 'update'])->middleware('role_or_permission_api:asset_depreciation.edit');
    });

    // asset sales
    Route::prefix('asset_sales')->group(function () {
        Route::get('/log', [AssetSaleLogController::class, 'index'])->middleware('role_or_permission_api:asset_sale.log');
        Route::get('/', [AssetSaleController::class, 'index'])->middleware('role_or_permission_api:asset_sale.view');
        Route::post('/', [AssetSaleController::class, 'store'])->middleware('role_or_permission_api:asset_sale.create');
        Route::delete('/{asset_sale_id}', [AssetSaleController::class, 'destroy'])->middleware('role_or_permission_api:asset_sale.delete');
        Route::put('/{asset_sale_id}', [AssetSaleController::class, 'update'])->middleware('role_or_permission_api:asset_sale.edit');
    });

    // asset maintenance
    Route::prefix('asset_maintenances')->group(function () {
        Route::get('/log', [AssetMaintenanceLogController::class, 'index'])->middleware('role_or_permission_api:asset_maintenance.log');
        Route::get('/', [AssetMaintenanceController::class, 'index'])->middleware('role_or_permission_api:asset_maintenance.view');
        Route::post('/', [AssetMaintenanceController::class, 'store'])->middleware('role_or_permission_api:asset_maintenance.create');
        Route::delete('/{asset_maintenance_id}', [AssetMaintenanceController::class, 'destroy'])->middleware('role_or_permission_api:asset_maintenance.delete');
        Route::put('/{asset_maintenance_id}', [AssetMaintenanceController::class, 'update'])->middleware('role_or_permission_api:asset_maintenance.edit');
    });

    // insurance employees
    Route::prefix('insurance_employees')->group(function () {
        Route::get('/log', [InsuranceEmployeeLogController::class, 'index'])->middleware('role_or_permission_api:insurance_employee.log');
        Route::get('/', [InsuranceEmployeeController::class, 'index'])->middleware('role_or_permission_api:insurance_employee.view');
        Route::post('/', [InsuranceEmployeeController::class, 'store'])->middleware('role_or_permission_api:insurance_employee.create');
        Route::delete('/{insurance_employee_id}', [InsuranceEmployeeController::class, 'destroy'])->middleware('role_or_permission_api:insurance_employee.delete');
        Route::put('/{insurance_employee_id}', [InsuranceEmployeeController::class, 'update'])->middleware('role_or_permission_api:insurance_employee.edit');
    });
});

