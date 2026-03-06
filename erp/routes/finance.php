<?php

use App\Http\Controllers\Api\FinanceAPIs\AccountAdjustmentTypeController;
use App\Http\Controllers\Api\FinanceAPIs\AccountAdjustmentTypeLogController;
use App\Http\Controllers\Api\FinanceAPIs\AssetController;
use App\Http\Controllers\Api\FinanceAPIs\AssetDocumentController;
use App\Http\Controllers\Api\FinanceAPIs\AssetDocumentLogController;
use App\Http\Controllers\Api\FinanceAPIs\AssetLogController;
use App\Http\Controllers\Api\FinanceAPIs\AssetLookupsController;
use App\Http\Controllers\Api\FinanceAPIs\AssetTransportController;
use App\Http\Controllers\Api\FinanceAPIs\AssetTransportLogController;
use App\Http\Controllers\Api\FinanceAPIs\BondController;
use App\Http\Controllers\Api\FinanceAPIs\BondImportController;
use App\Http\Controllers\Api\FinanceAPIs\BondLogController;
use App\Http\Controllers\Api\FinanceAPIs\BondSettingController;
use App\Http\Controllers\Api\FinanceAPIs\BondSettingLogController;
use App\Http\Controllers\Api\FinanceAPIs\EmploymentIncomeTaxBracketController;
use App\Http\Controllers\Api\FinanceAPIs\EmploymentIncomeTaxBracketLogController;
use App\Http\Controllers\Api\FinanceAPIs\FacilityBranchController;
use App\Http\Controllers\Api\FinanceAPIs\FacilityBranchLogController;
use App\Http\Controllers\Api\FinanceAPIs\FacilityCompanyLogController;
use App\Http\Controllers\Api\FinanceAPIs\FacilityLogController;
use App\Http\Controllers\Api\FinanceAPIs\GeneralTaxController;
use App\Http\Controllers\Api\FinanceAPIs\GeneralTaxLogController;
use App\Http\Controllers\Api\FinanceAPIs\JournalEntryDetailsLogController;
use App\Http\Controllers\Api\FinanceAPIs\JournalEntryLogController;
use App\Http\Controllers\Api\FinanceAPIs\JournalFacilitiesLogController;
// use App\Http\Controllers\Api\FinanceAPIs\JournalImportController;
use App\Http\Controllers\Api\FinanceAPIs\JournalLogController;

use App\Http\Controllers\Api\FinanceAPIs\JournalController;
use App\Http\Controllers\Api\FinanceAPIs\JournalEntryController;
use App\Http\Controllers\Api\FinanceAPIs\FacilityController;
use App\Http\Controllers\Api\FinanceAPIs\CurrencyController;
use App\Http\Controllers\Api\FinanceAPIs\CostCenterController;
use App\Http\Controllers\Api\FinanceAPIs\BranchController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\CountryController;
use App\Http\Controllers\Api\FinanceAPIs\CompanyController;
use App\Http\Controllers\Api\FinanceAPIs\CustomerController;
use App\Http\Controllers\Api\FinanceAPIs\EmployeeFacilityController;
use App\Http\Controllers\Api\FinanceAPIs\VendorController;

use App\Models\AssetTransportLog;
use Illuminate\Support\Facades\Route;


// Route::group(['middleware' => ['employee.auth', 'employee.flag:admin', 'role_or_permission_api:Finance']], function () {
Route::middleware(['auth:employee'])->group(function () {
    // Route::prefix('finance')->group(function () {
    Route::prefix('finance')->name('finance.')->group(function () {

            Route::get('currencies/showList', [CurrencyController::class, 'showList'])->name('currencies.showList');

            //facilities
            Route::prefix('facilities')->name('facilities.')->group(function () {
                // Manage facilities
                Route::get('/', [FacilityController::class, 'index']);
                Route::post('/', [FacilityController::class, 'store']);
                Route::get('/{id}', [FacilityController::class, 'show']);
                Route::put('/{id}', [FacilityController::class, 'update']);
                Route::delete('/{id}', [FacilityController::class, 'destroy']);
                // Route::delete('/{id}', [FacilityController::class, 'destroy'])->middleware('role_or_permission_api:delete facilities');
            });

            Route::prefix('countries')->name('countries.')->group(function () {
                Route::get('/', [CountryController::class, 'index']);
                Route::get('/showList', [CountryController::class, 'showList'])->name('showList');
            });

            Route::prefix('branches')->name('branches.')->group(function () {
                Route::get('/list', [BranchController::class, 'index']);
            });

            Route::prefix('companies')->name('companies.')->group(function () {
                Route::get('/', [CompanyController::class, 'index']);
                Route::get('/list', [CompanyController::class, 'list'])->name('list');
                Route::get('/{id}', [CompanyController::class, 'show']);
            });

        Route::middleware(['facility.active'])->group(function () {

            // Route::prefix('facilities-log')->group(function () {
            //     Route::get('/', [FacilityLogController::class, 'index'])->middleware('role_or_permission_api:facilities.view');
            //     Route::get('/{id}', [FacilityLogController::class, 'show'])->middleware('role_or_permission_api:facilities.show');
            //     Route::get('/{id}/facility', [FacilityLogController::class, 'logs'])->middleware('role_or_permission_api:facilities.show');
            // });

            // Route::prefix('currencies')->group(function () {
            Route::prefix('currencies')->name('currencies.')->group(function () {
                // Manage currencies
                Route::get('/', [CurrencyController::class, 'index']);
                Route::post('/', [CurrencyController::class, 'store']);
                // Route::get('/showList', [CurrencyController::class, 'showList'])->name('showList');
                Route::get('/{id}', [CurrencyController::class, 'show']);
                Route::put('/{id}', [CurrencyController::class, 'update']);
                Route::delete('/{id}', [CurrencyController::class, 'destroy']);
            });

            // Route::prefix('cost-centers')->group(function () {
            Route::prefix('cost-centers')->name('cost-centers.')->group(function () {
                Route::get('/', [CostCenterController::class, 'index']);
                Route::post('/', [CostCenterController::class, 'store']);
                Route::post('/comparison', [CostCenterController::class, 'comparison']);
                Route::get('/showList', [CostCenterController::class, 'showList'])->name('showList');
                Route::put('/archive/{id}', [CostCenterController::class, 'archive']);
                Route::get('/{id}', [CostCenterController::class, 'show']);
                Route::put('/{id}', [CostCenterController::class, 'update']);
                Route::delete('/{id}', [CostCenterController::class, 'destroy']);
            });

            // Route::prefix('journals')->group(function () {
            Route::prefix('journals')->name('journals.')->group(function () {
                // Import/export
                // Route::get('/import-template', [JournalImportController::class, 'template']);
                // Route::post('/import', [JournalImportController::class, 'import']);
                // Route::post('/export', [JournalImportController::class, 'export']);

                // Manage journals
                // Route::get('/list', [JournalController::class, 'list']);
                // Route::get('/archived', [JournalController::class, 'getArchivedJournals']);
                // Route::patch('/{id}/archive', [JournalController::class, 'archive']);
                // Route::patch('/{id}/restore', [JournalController::class, 'restoreArchivedAccount']);

                Route::get('/export-template', [JournalController::class, 'templateExportJournals']);
                Route::post('/import', [JournalController::class, 'importJournals']);

                Route::get('/', [JournalController::class, 'index']);
                Route::post('/', [JournalController::class, 'store']);
                Route::get('/selcetList', [JournalController::class, 'select_list'])->name('selcetList');
                Route::get('/trialBalance', [JournalController::class, 'trialBalance'])->name('trialBalance');
                Route::get('/accountStatement/{id}', [JournalController::class, 'account_statement'])->name('accountStatement');
                Route::get('/chartAccount/{id}', [JournalController::class, 'chart_account'])->name('chartAccount');
                Route::get('/list', [JournalController::class, 'list'])->name('list');
                Route::get('/showList', [JournalController::class, 'showList'])->name('showList');
                Route::get('/{id}', [JournalController::class, 'show']);
                Route::put('/archive/{id}', [JournalController::class, 'archive']);
                Route::put('/{id}', [JournalController::class, 'update']);
                Route::delete('/{id}', [JournalController::class, 'destroy']);

            });

            // Route::prefix('customers')->group(function () {
            Route::prefix('customers')->name('customers.')->group(function () {
                Route::get('/list', [CustomerController::class, 'list'])->name('list');
                Route::post('/', [CustomerController::class, 'store']);
            });

            // Route::prefix('vendors')->group(function () {
            Route::prefix('vendors')->name('vendors.')->group(function () {
                Route::get('/list', [VendorController::class, 'list'])->name('list');
                Route::post('/', [VendorController::class, 'store']);
            });

            // Route::prefix('journal-entry')->group(function () {
            Route::prefix('journal-entries')->name('journal-entries.')->group(function () {
                Route::get('/', [JournalEntryController::class, 'index']);
                Route::post('/', [JournalEntryController::class, 'store']);
                Route::get('/{id}', [JournalEntryController::class, 'show']);
                Route::put('/{id}', [JournalEntryController::class, 'update']);
                Route::delete('/{id}', [JournalEntryController::class, 'destroy']);
            });

            Route::prefix('employees')->name('employees.')->group(function () {
                Route::post('/changeFacility', [EmployeeFacilityController::class, 'store']);
            });

        });

    //journals
    /*Route::prefix('journals')->group(function () {
        // Import/export
        Route::get('/import-template', [JournalImportController::class, 'template'])->middleware('role_or_permission_api:journals.import');
        Route::post('/import', [JournalImportController::class, 'import'])->middleware('role_or_permission_api:journals.import');
        Route::post('/export', [JournalImportController::class, 'export'])->middleware('role_or_permission_api:journals.export');

        // Manage journals
        Route::get('/list', [JournalController::class, 'list'])->middleware('role_or_permission_api:journals.create');
        Route::get('/archived', [JournalController::class, 'getArchivedJournals'])->middleware('role_or_permission_api:journals.delete');
        Route::patch('/{id}/archive', [JournalController::class, 'archive'])->middleware('role_or_permission_api:journals.delete');
        Route::patch('/{id}/restore', [JournalController::class, 'restoreArchivedAccount'])->middleware('role_or_permission_api:journals.delete');
        Route::get('/', [JournalController::class, 'index'])->middleware('role_or_permission_api:journals.view');
        Route::post('/', [JournalController::class, 'store'])->middleware('role_or_permission_api:journals.create');
        Route::get('/{id}', [JournalController::class, 'show'])->middleware('role_or_permission_api:journals.show');
        Route::put('/{id}', [JournalController::class, 'update'])->middleware('role_or_permission_api:journals.edit');
        Route::delete('/{id}', [JournalController::class, 'destroy'])->middleware('role_or_permission_api:journals.delete');

    });

    Route::prefix('journals-log')->group(function () {
        Route::get('/', [JournalLogController::class, 'index'])->middleware('role_or_permission_api:journals.create');
        Route::get('/{id}', [JournalLogController::class, 'show'])->middleware('role_or_permission_api:journals.create');
        Route::get('/{id}/journal', [JournalLogController::class, 'logs'])->middleware('role_or_permission_api:journals.create');
    });

    Route::prefix('journal-facilities-log')->group(function () {
        Route::get('/', [JournalFacilitiesLogController::class, 'index'])->middleware('role_or_permission_api:journals.create');
        Route::get('/{id}', [JournalFacilitiesLogController::class, 'show'])->middleware('role_or_permission_api:journals.create');
        Route::get('/{id}/journal-facility', [JournalFacilitiesLogController::class, 'logs'])->middleware('role_or_permission_api:journals.create');
    });*/

    //bonds-settings
    /*Route::prefix('bonds-setting')->group(function () {

        // Manage bonds-setting
        Route::get('/', [BondSettingController::class, 'index'])->middleware('role_or_permission_api:bonds-setting.view');
        Route::post('/', [BondSettingController::class, 'store'])->middleware('role_or_permission_api:bonds-setting.create');
        Route::get('/{id}', [BondSettingController::class, 'show'])->middleware('role_or_permission_api:bonds-setting.show');
        Route::put('/{id}', [BondSettingController::class, 'update'])->middleware('role_or_permission_api:bonds-setting.edit');
        Route::delete('/{id}', [BondSettingController::class, 'destroy'])->middleware('role_or_permission_api:bonds-setting.delete');
    });

    Route::prefix('bonds-setting-log')->group(function () {
        Route::get('/', [BondSettingLogController::class, 'index'])->middleware('role_or_permission_api:bonds-setting.view');
        Route::get('/{id}', [BondSettingLogController::class, 'show'])->middleware('role_or_permission_api:bonds-setting.show');
        Route::get('/{id}/bond-setting', [BondSettingLogController::class, 'logs'])->middleware('role_or_permission_api:bonds-setting.show');
    });

    //employment-income-tax-brackets
    Route::prefix('employment-income-tax-brackets')->group(function () {

        // Manage employment_income_tax_brackets
        Route::get('/', [EmploymentIncomeTaxBracketController::class, 'index'])->middleware('role_or_permission_api:employment_income_tax_brackets.view');
        Route::post('/', [EmploymentIncomeTaxBracketController::class, 'store'])->middleware('role_or_permission_api:employment_income_tax_brackets.create');
        Route::get('/{id}', [EmploymentIncomeTaxBracketController::class, 'show'])->middleware('role_or_permission_api:employment_income_tax_brackets.show');
        Route::put('/{id}', [EmploymentIncomeTaxBracketController::class, 'update'])->middleware('role_or_permission_api:employment_income_tax_brackets.edit');
        Route::delete('/{id}', [EmploymentIncomeTaxBracketController::class, 'destroy'])->middleware('role_or_permission_api:employment_income_tax_brackets.delete');
    });

    Route::prefix('employment-income-tax-brackets-log')->group(function () {
        Route::get('/', [EmploymentIncomeTaxBracketLogController::class, 'index'])->middleware('role_or_permission_api:employment_income_tax_brackets.view');
        Route::get('/{id}', [EmploymentIncomeTaxBracketLogController::class, 'show'])->middleware('role_or_permission_api:employment_income_tax_brackets.show');
        Route::get('/{id}/tax-bracket', [EmploymentIncomeTaxBracketLogController::class, 'logs'])->middleware('role_or_permission_api:employment_income_tax_brackets.show');
    });

    //general-taxes
    Route::prefix('general-taxes')->group(function () {

        // Manage general_taxes
        Route::get('/', [GeneralTaxController::class, 'index'])->middleware('role_or_permission_api:general_taxes.view');
        Route::post('/', [GeneralTaxController::class, 'store'])->middleware('role_or_permission_api:general_taxes.create');
        Route::get('/{id}', [GeneralTaxController::class, 'show'])->middleware('role_or_permission_api:general_taxes.show');
        Route::put('/{id}', [GeneralTaxController::class, 'update'])->middleware('role_or_permission_api:general_taxes.edit');
        Route::delete('/{id}', [GeneralTaxController::class, 'destroy'])->middleware('role_or_permission_api:general_taxes.delete');
    });

    Route::prefix('general-taxes-log')->group(function () {
        Route::get('/', [GeneralTaxLogController::class, 'index'])->middleware('role_or_permission_api:general_taxes.create');
        Route::get('/{id}', [GeneralTaxLogController::class, 'show'])->middleware('role_or_permission_api:general_taxes.create');
        Route::get('/{id}/tax', [GeneralTaxLogController::class, 'logs'])->middleware('role_or_permission_api:general_taxes.create');
    });

    //Manage journal_entries
    Route::prefix('journal-entries')->group(function () {
        // Manage journal_entries
        Route::get('/settings', [JournalEntryController::class, 'journalSettings'])->middleware('role_or_permission_api:journal_entries.create');
        Route::get('/', [JournalEntryController::class, 'index'])->middleware('role_or_permission_api:journal_entries.view');
        Route::post('/', [JournalEntryController::class, 'store'])->middleware('role_or_permission_api:journal_entries.create');
        Route::get('/{id}', [JournalEntryController::class, 'show'])->middleware('role_or_permission_api:journal_entries.show');
        Route::put('/{id}', [JournalEntryController::class, 'update'])->middleware('role_or_permission_api:journal_entries.edit');
        Route::delete('/{id}', [JournalEntryController::class, 'destroy'])->middleware('role_or_permission_api:journal_entries.delete');

    });

    Route::prefix('journal-entries-log')->group(function () {
        Route::get('/', [JournalEntryLogController::class, 'index'])->middleware('role_or_permission_api:journal_entries.create');
        Route::get('/{id}', [JournalEntryLogController::class, 'show'])->middleware('role_or_permission_api:journal_entries.create');
        Route::get('/{id}/journal', [JournalEntryLogController::class, 'logs'])->middleware('role_or_permission_api:journal_entries.create');
    });

    Route::prefix('journal-entry-details-log')->group(function () {
        Route::get('/', [JournalEntryDetailsLogController::class, 'index'])->middleware('role_or_permission_api:journal_entries.create');
        Route::get('/{id}', [JournalEntryDetailsLogController::class, 'show'])->middleware('role_or_permission_api:journal_entries.create');
        Route::get('/{id}/journal-entry-details', [JournalEntryDetailsLogController::class, 'logs'])->middleware('role_or_permission_api:journal_entries.create');
    });

    //Manage adjustment-types
    Route::prefix('adjustment-types')->group(function () {

        // Manage adjustment-types
        Route::get('/', [AccountAdjustmentTypeController::class, 'index'])->middleware('role_or_permission_api:accounting_adjustment_types.view');
        Route::post('/', [AccountAdjustmentTypeController::class, 'store'])->middleware('role_or_permission_api:accounting_adjustment_types.create');
        Route::get('/{id}', [AccountAdjustmentTypeController::class, 'show'])->middleware('role_or_permission_api:accounting_adjustment_types.show');
        Route::put('/{id}', [AccountAdjustmentTypeController::class, 'update'])->middleware('role_or_permission_api:accounting_adjustment_types.edit');
        Route::delete('/{id}', [AccountAdjustmentTypeController::class, 'destroy'])->middleware('role_or_permission_api:accounting_adjustment_types.delete');
    });

    Route::prefix('adjustment-types-log')->group(function () {
        Route::get('/', [AccountAdjustmentTypeLogController::class, 'index'])->middleware('role_or_permission_api:accounting_adjustment_types.create');
        Route::get('/{id}', [AccountAdjustmentTypeLogController::class, 'show'])->middleware('role_or_permission_api:accounting_adjustment_types.create');
        Route::get('/{id}/adjustment-type', [AccountAdjustmentTypeLogController::class, 'logs'])->middleware('role_or_permission_api:accounting_adjustment_types.create');
    });

    //Bonds
    Route::prefix('bonds')->group(function () {

        // Import/export
        Route::get('/import-template', [BondImportController::class, 'template'])->middleware('role_or_permission_api:bonds.import');
        Route::post('/import', [BondImportController::class, 'import'])->middleware('role_or_permission_api:bonds.import');
        Route::post('/export', [BondImportController::class, 'export'])->middleware('role_or_permission_api:bonds.export');
        Route::post('/export/{bond_id}', [BondImportController::class, 'generatePaper'])->middleware('role_or_permission_api:bonds.export');

        // Manage bonds
        Route::get('/clients', [BondController::class, 'clients'])->middleware('role_or_permission_api:bonds.create');
        Route::get('/types', [BondController::class, 'types'])->middleware('role_or_permission_api:bonds.create');
        Route::get('/settings', [BondController::class, 'settings'])->middleware('role_or_permission_api:bonds.create');
        Route::get('/', [BondController::class, 'index'])->middleware('role_or_permission_api:bonds.view');
        Route::post('/', [BondController::class, 'store'])->middleware('role_or_permission_api:bonds.create');
        Route::post('/{id}', [BondController::class, 'update'])->middleware('role_or_permission_api:bonds.edit');
        Route::get('/{id}', [BondController::class, 'show'])->middleware('role_or_permission_api:bonds.show');
        Route::delete('/{id}', [BondController::class, 'destroy'])->middleware('role_or_permission_api:bonds.delete');
    });

    Route::prefix('bonds-log')->group(function () {
        Route::get('/', [BondLogController::class, 'index'])->middleware('role_or_permission_api:bonds.create');
        Route::get('/{id}', [BondLogController::class, 'show'])->middleware('role_or_permission_api:bonds.create');
        Route::get('/{id}/bond', [BondLogController::class, 'logs'])->middleware('role_or_permission_api:bonds.create');
    });

    //Manage Assets
    Route::prefix('assets')->group(function () {

        // Lookups helps create assets
        Route::get('/status', [AssetLookupsController::class, 'status'])->middleware('role_or_permission_api:assets.create');
        Route::get('/depreciation', [AssetLookupsController::class, 'depreciation'])->middleware('role_or_permission_api:assets.create');
        Route::get('/depreciationMethods', [AssetLookupsController::class, 'depreciationMethods'])->middleware('role_or_permission_api:assets.create');
        Route::get('/depreciationPeriodTypes', [AssetLookupsController::class, 'depreciationPeriodTypes'])->middleware('role_or_permission_api:assets.create');
        Route::get('/depreciationTypes', [AssetLookupsController::class, 'depreciationTypes'])->middleware('role_or_permission_api:assets.create');
        Route::get('/stored', [AssetLookupsController::class, 'stored'])->middleware('role_or_permission_api:assets.create');
        Route::get('/productiveLifeTypes', [AssetLookupsController::class, 'productiveLifeTypes'])->middleware('role_or_permission_api:assets.create');

        // Manage Assets
        Route::get('/', [AssetController::class, 'index'])->middleware('role_or_permission_api:assets.view');
        Route::post('/', [AssetController::class, 'store'])->middleware('role_or_permission_api:assets.create');
        Route::get('/{id}', [AssetController::class, 'show'])->middleware('role_or_permission_api:assets.show');
        Route::post('/{id}', [AssetController::class, 'update'])->middleware('role_or_permission_api:assets.edit');
        Route::delete('/{id}', [AssetController::class, 'destroy'])->middleware('role_or_permission_api:assets.delete');
    });

    Route::prefix('assets-log')->group(function () {
        Route::get('/', [AssetLogController::class, 'index'])->middleware('role_or_permission_api:assets.create');
        Route::get('/{id}', [AssetLogController::class, 'show'])->middleware('role_or_permission_api:assets.create');
        Route::get('/{id}/asset', [AssetLogController::class, 'logs'])->middleware('role_or_permission_api:assets.create');
    });

    Route::prefix('asset-documents')->group(function () {
        Route::get('/', [AssetDocumentController::class, 'index'])->middleware('role_or_permission_api:assets.create');
        Route::get('/{id}', [AssetDocumentController::class, 'show'])->middleware('role_or_permission_api:assets.create');
        Route::get('/{asset_document_id}/download', [AssetDocumentController::class, 'download'])->middleware('role_or_permission_api:asset_document.download');
    });

    Route::prefix('asset-documents-log')->group(function () {
        Route::get('/', [AssetDocumentLogController::class, 'index'])->middleware('role_or_permission_api:assets.create');
        Route::get('/{id}', [AssetDocumentLogController::class, 'show'])->middleware('role_or_permission_api:assets.create');
        Route::get('/{id}/document', [AssetDocumentLogController::class, 'logs'])->middleware('role_or_permission_api:assets.create');
    });


    //Manage FacilityCompany
    Route::prefix('facility-companies')->group(function () {
        Route::get('/', [FacilityCompanyController::class, 'index'])->middleware('role_or_permission_api:facility_companies.view');
        Route::post('/', [FacilityCompanyController::class, 'store'])->middleware('role_or_permission_api:facility_companies.create');
        Route::get('/{id}', [FacilityCompanyController::class, 'show'])->middleware('role_or_permission_api:facility_companies.show');
        Route::put('/{id}', [FacilityCompanyController::class, 'update'])->middleware('role_or_permission_api:facility_companies.edit');
        Route::delete('/{id}', [FacilityCompanyController::class, 'destroy'])->middleware('role_or_permission_api:facility_companies.delete');
    });
    //FacilityCompany Log
    Route::prefix('facility-companies-log')->group(function () {
        Route::get('/', [FacilityCompanyLogController::class, 'index'])->middleware('role_or_permission_api:facility_companies.create');
        Route::get('/{id}', [FacilityCompanyLogController::class, 'show'])->middleware('role_or_permission_api:facility_companies.create');
        Route::get('/{id}/facility', [FacilityCompanyLogController::class, 'logs'])->middleware('role_or_permission_api:facility_companies.create');
    });


    //Manage FacilityBranch
    Route::prefix('facility-branches')->group(function () {
        Route::get('/', [FacilityBranchController::class, 'index'])->middleware('role_or_permission_api:facility_branches.view');
        Route::post('/', [FacilityBranchController::class, 'store'])->middleware('role_or_permission_api:facility_branches.create');
        Route::get('/{id}', [FacilityBranchController::class, 'show'])->middleware('role_or_permission_api:facility_branches.show');
        Route::put('/{id}', [FacilityBranchController::class, 'update'])->middleware('role_or_permission_api:facility_branches.edit');
        Route::delete('/{id}', [FacilityBranchController::class, 'destroy'])->middleware('role_or_permission_api:facility_branches.delete');
    });
    //FacilityCompany Log
    Route::prefix('facility-branches-log')->group(function () {
        Route::get('/', [FacilityBranchLogController::class, 'index'])->middleware('role_or_permission_api:facility_branches.create');
        Route::get('/{id}', [FacilityBranchLogController::class, 'show'])->middleware('role_or_permission_api:facility_branches.create');
        Route::get('/{id}/branch', [FacilityBranchLogController::class, 'logs'])->middleware('role_or_permission_api:facility_branches.create');
    });


    //Manage AssetTransport
    Route::prefix('asset-transports')->group(function () {
        Route::get('/', [AssetTransportController::class, 'index'])->middleware('role_or_permission_api:asset_transports.view');
        Route::post('/', [AssetTransportController::class, 'store'])->middleware('role_or_permission_api:asset_transports.create');
        Route::get('/{id}', [AssetTransportController::class, 'show'])->middleware('role_or_permission_api:asset_transports.show');
        Route::put('/{id}', [AssetTransportController::class, 'update'])->middleware('role_or_permission_api:asset_transports.edit');
        Route::delete('/{id}', [AssetTransportController::class, 'destroy'])->middleware('role_or_permission_api:asset_transports.delete');
    });

    Route::prefix('asset-transports-log')->group(function () {
        Route::get('/', [AssetTransportLogController::class, 'index'])->middleware('role_or_permission_api:asset_transports.create');
        Route::get('/{id}', [AssetTransportLogController::class, 'show'])->middleware('role_or_permission_api:asset_transports.create');
        Route::get('/{id}/asset-transport', [AssetTransportLogController::class, 'logs'])->middleware('role_or_permission_api:asset_transports.create');
    });*/
    });
});

