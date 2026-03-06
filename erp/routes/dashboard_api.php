<?php

use App\Http\Controllers\Dashboard\testController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use Illuminate\Session\Middleware\StartSession;
use App\Http\Controllers\Api\DashboardAPIs\ClientApiController;
use App\Http\Controllers\Api\DashboardAPIs\InsuranceController;
use App\Http\Controllers\Api\DashboardAPIs\VehicleApiController;
use App\Http\Controllers\Api\DashboardAPIs\OrderChangeController;
use App\Http\Controllers\Api\DashboardAPIs\BranchReportController;
use App\Http\Controllers\Api\DashboardAPIs\FiledOfStudyController;
use App\Http\Controllers\Api\DashboardAPIs\InsuranceLogController;
use App\Http\Controllers\Api\DashboardAPIs\WaiterRequestController;
use App\Http\Controllers\Api\DashboardAPIs\CashierMachineController;
use App\Http\Controllers\Api\DashboardAPIs\CashierSettingController;
use App\Http\Controllers\Api\DashboardAPIs\EducationLevelController;
use App\Http\Controllers\Api\DashboardAPIs\OrderDashboardController;
use App\Http\Controllers\Api\DashboardAPIs\VehicleSettingController;
use App\Http\Controllers\Api\DashboardAPIs\OrderComplaintsController;
use App\Http\Controllers\Api\DashboardAPIs\MenusIntegrationController;
use App\Http\Controllers\Api\DashboardAPIs\InsuranceEmployeeController;
use App\Http\Controllers\Api\DashboardAPIs\CashPaymentSettingController;
use App\Http\Controllers\Api\DashboardAPIs\DeliveryComplaintsController;
use App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs\BalanceCotroller;
use App\Http\Controllers\Api\DashboardAPIs\CashierBalancesReportsController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\FAQController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\CityController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\GiftController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\LogoController;
use App\Http\Controllers\Api\DashboardAPIs\KitchenPerformanceReportController;
use App\Http\Controllers\Api\DashboardAPIs\KitchenSettingsAPIs\DishController;
use App\Http\Controllers\Api\DashboardAPIs\KitchenSettingsAPIs\MenuController;
use App\Http\Controllers\Api\DashboardAPIs\InsuranceEmployeeLogController;
use App\Http\Controllers\Api\DashboardAPIs\CashierPerformanceReportsController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\BrandController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\ColorController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\FloorController;
use App\Http\Controllers\Api\DashboardAPIs\KitchenSettingsAPIs\AddonController;
use App\Http\Controllers\Api\DashboardAPIs\OrderReturnInvoiceRequestController;
use App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs\OrdersReportsController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\BranchController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\CouponController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\pointsController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\SliderController;
use App\Http\Controllers\Api\DashboardAPIs\KitchenSettingsAPIs\RecipeController;
use App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs\FeedbackReportController;
use App\Http\Controllers\Api\DashboardAPIs\CustomerDeliveryOrderReportController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\ApiCodeController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\CountryController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\RegionsController;
use App\Http\Controllers\Api\DashboardAPIs\KitchenSettingsAPIs\CuisineController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\BankNameController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\DiscountController;
use App\Http\Controllers\Api\DashboardAPIs\KitchenSettingsAPIs\CategoryController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\BranchMenuController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\StaticPageController;
use App\Http\Controllers\Api\DashboardAPIs\DeliveryEearningsPaymentsReportController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\PaymentTypeController;
use App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs\HangingOrdersReportController;
use App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs\OrdersReportsCancelController;
use App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs\WaiterRequestReportController;
use App\Http\Controllers\Api\DashboardAPIs\DeliveryPerformanceMetricsReportController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\ReturnPolicyController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\TableGeneralController;
use App\Http\Controllers\Api\DashboardAPIs\KitchenSettingsAPIs\DishCategoryController;
use App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs\BestSellerDishReportController;
use App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs\BookingRevenueReportController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\BranchSettingController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\MaritalStatusController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\PrivacyPolicyController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\UniversityApiController;
use App\Http\Controllers\Api\DashboardAPIs\KitchenSettingsAPIs\AddonCategoryController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\BranchMenuSizeController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\EmployeeStatusController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\FloorPartitionController;
use App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs\TableReservationReportController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\BranchMenuAddonController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\PaymentPoliciesController;
use App\Http\Controllers\Api\DashboardAPIs\HomeController as DashboardAPIsHomeController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\BusinessActivityController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\EthnicBackgroundController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\PaymentFrequencyController;
use App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs\WaiterTableServiceReportController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\BranchMenuCategoryController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\TermsAndConditionsController;
use App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs\BookingCancellationReportApiController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\CompanyProfileSettingController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\MilitaryServiceStatusController;
use App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs\CancelledDeliveryOrdersReportController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\BranchMenuAddonCategoryController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\OrderCancellationReasonController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\PolicyPaymentReservationController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\ContactInformationSettingController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\SocialMediaInformationSettingController;
use App\Http\Controllers\Api\DashboardAPIs\HotelController;
use App\Http\Controllers\Api\DashboardAPIs\InvoiceController;
use App\Http\Controllers\Api\DashboardAPIs\ReturnInvoiceController;

Route::prefix('ordercancel')->group(function () {
    Route::get('/list', [OrdersReportsCancelController::class, 'list']);
    Route::get('/show', [OrdersReportsCancelController::class, 'show']);
});

// Start Discounts
Route::group(['prefix' => 'discounts'], function () {
    Route::get('discountList', [DiscountController::class, 'index']);
    Route::get('showDiscount/{id}', [DiscountController::class, 'show']);
    Route::post('addDiscount', [DiscountController::class, 'store']);
    Route::put('updateDiscount/{id}', [DiscountController::class, 'update']);
    Route::delete('deleteDiscount/{id}', [DiscountController::class, 'destroy']);
    Route::post('restoreDiscount/{id}', [DiscountController::class, 'restore']);
});
// End Discounts

//Start Coupons
Route::group(['prefix' => 'coupons'], function () {
    Route::get('couponList', [CouponController::class, 'index']);
    Route::get('validateCoupon/{id}', [CouponController::class, 'isCouponValid']);
    Route::post('check-coupon', [CouponController::class, 'isCouponValid']);
});
// End Coupons

// Static Pages
Route::get('/static-page', [StaticPageController::class, 'index'])->name('api.static.pages');

// Slider
Route::get('/slider', [SliderController::class, 'index'])->name('api.slider');

// Dish Menu related routes grouped by DishCategoryController
Route::controller(DishCategoryController::class)->group(function () {
    Route::get('/dish-menu', 'menuDishes')->name('api.dish.menu');
    Route::get('/dish-menu-details', 'menuDishesDetails')->name('api.dish.menu.details');
});

// Branch related routes
Route::get('/branch-near', [BranchController::class, 'listBranchAndNearFilter'])->name('api.branch.near');

//payment-policies
Route::prefix('payment-policies')->controller(PaymentPoliciesController::class)->group(function () {
    Route::get('types', 'policies_types');
    Route::get('reservation', 'policies_reservation');
});

Route::get('country', [CountryController::class, 'index']);
Route::get('city', [CityController::class, 'index']);

Route::middleware(['auth:employee'])->group(function () {

    //bank_names
    Route::group(['prefix' => 'bank_name'], function () {
        Route::get('/index', [BankNameController::class, 'index'])->middleware('role_or_permission_api:view bank_names');
        Route::post('store', [BankNameController::class, 'store'])->middleware('role_or_permission_api:create bank_names');
        Route::get('show/{id}', [BankNameController::class, 'show'])->middleware('role_or_permission_api:view bank_names');
        Route::post('update/{id}', [BankNameController::class, 'update'])->middleware('role_or_permission_api:update bank_names');
        Route::delete('delete/{id}', [BankNameController::class, 'delete'])->middleware('role_or_permission_api:delete bank_names');
    });

    //education_level
    Route::group(['prefix' => 'education_level'], function () {
        Route::get('/', [EducationLevelController::class, 'index'])->middleware('role_or_permission_api:view education_level');
        Route::get('/{id}', [EducationLevelController::class, 'show'])->middleware('role_or_permission_api:view education_level');
        Route::post('/', [EducationLevelController::class, 'store'])->middleware('role_or_permission_api:create education_level');
        Route::post('/update/{id}', [EducationLevelController::class, 'update'])->middleware('role_or_permission_api:update education_level');
        Route::delete('/{id}', [EducationLevelController::class, 'delete'])->middleware('role_or_permission_api:delete education_level');
    });

    //ethnic_backgrounds
    Route::group(['prefix' => 'ethnic_background'], function () {
        Route::get('/index', [EthnicBackgroundController::class, 'index'])->middleware('role_or_permission_api:view ethnic_backgrounds');
        Route::post('store', [EthnicBackgroundController::class, 'store'])->middleware('role_or_permission_api:create ethnic_backgrounds');
        Route::get('show/{id}', [EthnicBackgroundController::class, 'show'])->middleware('role_or_permission_api:view ethnic_backgrounds');
        Route::post('update/{id}', [EthnicBackgroundController::class, 'update'])->middleware('role_or_permission_api:update ethnic_backgrounds');
        Route::delete('delete/{id}', [EthnicBackgroundController::class, 'delete'])->middleware('role_or_permission_api:delete ethnic_backgrounds');
    });

    //employee_status
    Route::group(['prefix' => 'employee_status'], function () {
        Route::post('store', [EmployeeStatusController::class, 'store'])->middleware('role_or_permission_api:create employee_status');
        Route::get('show/{id}', [EmployeeStatusController::class, 'show'])->middleware('role_or_permission_api:view ethnic_backgrounds');
        Route::get('/index', [EmployeeStatusController::class, 'index'])->middleware('role_or_permission_api:view employee_status');
        Route::post('update/{id}', [EmployeeStatusController::class, 'update'])->middleware('role_or_permission_api:update employee_status');
        Route::delete('delete/{id}', [EmployeeStatusController::class, 'delete'])->middleware('role_or_permission_api:delete employee_status');
    });

    // countries
    Route::group(['prefix' => 'countries'], function () {
        Route::get('/', [CountryController::class, 'index'])->middleware('role_or_permission_api:view countries');

        Route::get('/show/{id}', [CountryController::class, 'show'])->middleware('role_or_permission_api:view countries');
        Route::post('store', [CountryController::class, 'store'])->middleware('role_or_permission_api:create countries');
        Route::post('update/{id}', [CountryController::class, 'update'])->middleware('role_or_permission_api:update countries');
        Route::delete('delete/{id}', [CountryController::class, 'destroy'])->middleware('role_or_permission_api:delete countries');
    });
    // city
    Route::group(['prefix' => 'city'], function () {
        Route::get('/', [CityController::class, 'index'])->middleware('role_or_permission_api:view cities');
        Route::get('/show/{id}', [CityController::class, 'show'])->middleware('role_or_permission_api:view cities');
        Route::post('store', [CityController::class, 'store'])->middleware('role_or_permission_api:create cities');
        Route::post('update/{id}', [CityController::class, 'update'])->middleware('role_or_permission_api:update cities');
        Route::delete('delete/{id}', [CityController::class, 'destroy'])->middleware('role_or_permission_api:delete cities');
    });

    // region
    Route::group(['prefix' => 'region'], function () {
        Route::get('/',  [RegionsController::class, 'index'])->middleware('role_or_permission_api:view regions');
        Route::get('/show/{id}', [RegionsController::class, 'show'])->middleware('role_or_permission_api:view regions');
        Route::post('store', [RegionsController::class, 'store'])->middleware('role_or_permission_api:create regions');
        Route::post('update/{id}', [RegionsController::class, 'update'])->middleware('role_or_permission_api:update regions');
        Route::delete('delete/{id}', [RegionsController::class, 'destroy'])->middleware('role_or_permission_api:delete regions');
    });

    Route::group(['prefix' => 'menus-integration'], function () {
        Route::get('index', [MenusIntegrationController::class, 'index'])->middleware('role_or_permission_api:view dashboard');
    });

    //Business Activity
    Route::group(['prefix' => 'business_activity'], function () {
        Route::get('index', [BusinessActivityController::class, 'index'])->middleware('role_or_permission_api:view business_activity');
        Route::post('store', [BusinessActivityController::class, 'store'])->middleware('role_or_permission_api:create business_activity');
        Route::get('show/{id}', [BusinessActivityController::class, 'show'])->middleware('role_or_permission_api:view business_activity');
        Route::post('update/{id}', [BusinessActivityController::class, 'update'])->middleware('role_or_permission_api:update business_activity');
        Route::delete('delete/{id}', [BusinessActivityController::class, 'delete'])->middleware('role_or_permission_api:delete business_activity');
    });
    //Company Profile Settings
    Route::group(['prefix' => 'company_profile_setting'], function () {
        Route::get('/index', [CompanyProfileSettingController::class, 'index'])->middleware('role_or_permission_api:view company_profile_setting');
        Route::post('store', [CompanyProfileSettingController::class, 'store'])->middleware('role_or_permission_api:create company_profile_setting');
        Route::get('show/{id}', [CompanyProfileSettingController::class, 'show'])->middleware('role_or_permission_api:view company_profile_setting');
        Route::post('update/{id}', [CompanyProfileSettingController::class, 'update'])->middleware('role_or_permission_api:update company_profile_setting');
        Route::delete('delete/{id}', [CompanyProfileSettingController::class, 'delete'])->middleware('role_or_permission_api:delete company_profile_setting');
    });

    //Contact Information Setting
    Route::group(['prefix' => 'contact_information_setting'], function () {
        Route::get('/index', [ContactInformationSettingController::class, 'index'])->middleware('role_or_permission_api:view company_profile_setting');
        Route::post('store', [ContactInformationSettingController::class, 'store'])->middleware('role_or_permission_api:create company_profile_setting');
        Route::get('show/{id}', [ContactInformationSettingController::class, 'show'])->middleware('role_or_permission_api:view company_profile_setting');
        Route::post('update/{id}', [ContactInformationSettingController::class, 'update'])->middleware('role_or_permission_api:update company_profile_setting');
        Route::delete('delete/{id}', [ContactInformationSettingController::class, 'delete'])->middleware('role_or_permission_api:delete company_profile_setting');
    });

    //Social Media Information Setting
    Route::group(['prefix' => 'social_media_information_setting'], function () {
        Route::get('/index', [SocialMediaInformationSettingController::class, 'index'])->middleware('role_or_permission_api:view company_profile_setting');
        Route::post('store', [SocialMediaInformationSettingController::class, 'store'])->middleware('role_or_permission_api:create company_profile_setting');
        Route::get('show/{id}', [SocialMediaInformationSettingController::class, 'show'])->middleware('role_or_permission_api:view company_profile_setting');
        Route::post('update/{id}', [SocialMediaInformationSettingController::class, 'update'])->middleware('role_or_permission_api:update company_profile_setting');
        Route::delete('delete/{id}', [SocialMediaInformationSettingController::class, 'delete'])->middleware('role_or_permission_api:delete company_profile_setting');
    });

    Route::group(['prefix' => '/reports/table_reservations'], function () {
        Route::get('/index', [TableReservationReportController::class, 'index'])->middleware('role_or_permission_api:view report_table_reservations');
        Route::get('/show/{id}', [TableReservationReportController::class, 'show'])->middleware('role_or_permission_api:detail report_table_reservations');
    });
    Route::group(['prefix' => '/reports/feedbacks'], function () {
        Route::get('/index', [FeedbackReportController::class, 'index'])->middleware('role_or_permission_api:view report_feedbacks');
        Route::get('/show/{id}', [FeedbackReportController::class, 'show'])->middleware('role_or_permission_api:detail report_feedbacks');
    });

    //tables
    Route::group(['prefix' => 'tables'], function () {
        Route::get('index', [TableGeneralController::class, 'index']);
        Route::get('show/{id}', [TableGeneralController::class, 'show']);
        Route::post('add', [TableGeneralController::class, 'add'])->middleware('role_or_permission_api:add tables');
        Route::post('edit', [TableGeneralController::class, 'edit'])->middleware('role_or_permission_api:update tables');
        Route::delete('delete/{id}', [TableGeneralController::class, 'delete'])->middleware('role_or_permission_api:delete tables');
    });

    //floor
    Route::group(['prefix' => 'floors'], function () {
        Route::get('index', [FloorController::class, 'index'])->middleware('role_or_permission_api:view floors');
        Route::get('show/{id}', [FloorController::class, 'show'])->middleware('role_or_permission_api:view floors');
        Route::post('add', [FloorController::class, 'add'])->middleware('role_or_permission_api:add floors');
        Route::post('edit', [FloorController::class, 'edit'])->middleware('role_or_permission_api:update floors');
        Route::delete('delete/{id}', [FloorController::class, 'delete'])->middleware('role_or_permission_api:delete floors');
    });
    Route::group(['prefix' => 'orders'], function () {
        Route::post('/change-Status/{id}', [OrderChangeController::class, 'changeStatus']);
    });

    //floor-partitions
    Route::group(['prefix' => 'floor-partitions'], function () {
        Route::get('index', [FloorPartitionController::class, 'index'])->middleware('role_or_permission_api:view floor-partitions');
        Route::get('show/{id}', [FloorPartitionController::class, 'show'])->middleware('role_or_permission_api:view floor-partitions');
        Route::post('add', [FloorPartitionController::class, 'add'])->middleware('role_or_permission_api:add floor-partitions');
        Route::post('edit', [FloorPartitionController::class, 'edit'])->middleware('role_or_permission_api:update floor-partitions');
        Route::delete('delete/{id}', [FloorPartitionController::class, 'delete'])->middleware('role_or_permission_api:delete floor-partitions');
    });
    //branch setting
    Route::group(['prefix' => 'branch-setting'], function () {
        Route::get('index', [BranchSettingController::class, 'index'])->middleware('role_or_permission_api:view branch-setting');
        Route::get('show/{id}', [BranchSettingController::class, 'show'])->middleware('role_or_permission_api:view branch-setting');
        Route::post('add', [BranchSettingController::class, 'add'])->middleware('role_or_permission_api:add branch-setting');
        Route::post('edit/{id}', [BranchSettingController::class, 'edit'])->middleware('role_or_permission_api:update branch-setting');
        Route::delete('delete/{id}', [BranchSettingController::class, 'delete'])->middleware('role_or_permission_api:delete branch-setting');
    });

    //insurances
    Route::prefix('insurances')->group(function () {
        Route::get('/log', [InsuranceLogController::class, 'index'])->middleware('role_or_permission_api:log insurance');
        Route::get('/', [InsuranceController::class, 'index'])->middleware('role_or_permission_api:view insurance');
        Route::post('/', [InsuranceController::class, 'store'])->middleware('role_or_permission_api:create insurance');
        Route::delete('/{insurance_id}', [InsuranceController::class, 'destroy'])->middleware('role_or_permission_api:delete insurance');
        Route::put('/{insurance_id}', [InsuranceController::class, 'update'])->middleware('role_or_permission_api:edit insurance');
    });

    // insurance employees
    Route::prefix('insurance_employees')->group(function () {
        Route::get('/log', [InsuranceEmployeeLogController::class, 'index'])->middleware('role_or_permission_api:insurance_employee.log');
        Route::get('/', [InsuranceEmployeeController::class, 'index'])->middleware('role_or_permission_api:insurance_employee.view');
        Route::post('/', [InsuranceEmployeeController::class, 'store'])->middleware('role_or_permission_api:insurance_employee.create');
        Route::delete('/{insurance_employee_id}', [InsuranceEmployeeController::class, 'destroy'])->middleware('role_or_permission_api:insurance_employee.delete');
        Route::put('/{insurance_employee_id}', [InsuranceEmployeeController::class, 'update'])->middleware('role_or_permission_api:insurance_employee.edit');
    });

    // Start branches
    Route::group(['prefix' => 'branches'], function () {
        Route::get('branchList', [BranchController::class, 'index'])->middleware('role_or_permission_api:view branches');
        Route::get('showBranch/{id}', [BranchController::class, 'show'])->middleware('role_or_permission_api:view branches');
        Route::post('addBranch', [BranchController::class, 'store'])->middleware('role_or_permission_api:add branches'); // admin only
        Route::put('updateBranch/{id}', [BranchController::class, 'update'])->middleware('role_or_permission_api:update branches');
        Route::delete('deleteBranch/{id}', [BranchController::class, 'destroy'])->middleware('role_or_permission_api:delete branches'); // admin only
        Route::get('changeStatus/{id}', [BranchController::class, 'change_status'])->middleware('role_or_permission_api:view branches'); //new
        Route::get('sync/{id}', [BranchController::class, 'sync'])->name('branch.sync2')->middleware('role_or_permission_api:view branches'); //new

        // Route::get('/categories', [BranchMenuCategoryController::class, 'index'])->name('branch.categories.list');
        Route::group(['prefix' => 'categories'], function () {
            Route::get('show/{id}', [BranchMenuCategoryController::class, 'show'])->middleware('role_or_permission_api:show branch_categories');; //categories show
            Route::get('showAll/{branch_id}', [BranchMenuCategoryController::class, 'show_branch'])->middleware('role_or_permission_api:view branch_categories');; //categories all
            Route::get('changeStatus/{id}', [BranchMenuCategoryController::class, 'change_status'])->middleware('role_or_permission_api:change branch_categories'); //categories active
        });

        // Route::get('/menus', [BranchMenuController::class, 'index'])->name('branch.menus.list');
        Route::group(['prefix' => 'menus'], function () {
            Route::get('show/{id}', [BranchMenuController::class, 'show'])->middleware('role_or_permission_api:show branch_menus'); //menus show
            Route::put('update/{id}', [BranchMenuController::class, 'update'])->middleware('role_or_permission_api:update branch_menus'); //menus update
            Route::get('showAll/{branch_id}', [BranchMenuController::class, 'show_branch'])->middleware('role_or_permission_api:view branch_menus'); //menus all
            Route::get('changeStatus/{id}', [BranchMenuController::class, 'change_status'])->middleware('role_or_permission_api:change branch_menus'); //menus active
        });

        Route::group(['prefix' => 'menu/addon/categories'], function () {
            Route::get('show/{id}', [BranchMenuAddonCategoryController::class, 'show'])->middleware('role_or_permission_api:show branch_menu_addon_categories'); //addonsCategories show
            Route::get('showAll/{branch_id}', [BranchMenuAddonCategoryController::class, 'show_branch'])->middleware('role_or_permission_api:view branch_menu_addon_categories'); //addonsCategories all
            Route::get('changeStatus/{id}', [BranchMenuAddonCategoryController::class, 'change_status'])->middleware('role_or_permission_api:change branch_menu_addon_categories'); //addonsCategories active
        });
        Route::group(['prefix' => 'menu/addons'], function () {
            Route::get('show/{id}', [BranchMenuAddonController::class, 'show'])->middleware('role_or_permission_api:view branch_menu_addons');; //addons show
            Route::put('update/{id}', [BranchMenuAddonController::class, 'update'])->middleware('role_or_permission_api:update branch_menu_addons'); //addons update
            Route::get('showAll/{branch_id}', [BranchMenuAddonController::class, 'show_branch'])->middleware('role_or_permission_api:view branch_menu_addons'); //addons all
            Route::get('changeStatus/{id}', [BranchMenuAddonController::class, 'change_status'])->middleware('role_or_permission_api:change branch_menu_addons'); //addons active
        });
        Route::group(['prefix' => 'menu/sizes'], function () {
            Route::get('show/{id}', [BranchMenuSizeController::class, 'show'])->middleware('role_or_permission_api:view branch_menu_sizes'); //sizes show
            Route::put('update/{id}', [BranchMenuSizeController::class, 'update'])->middleware('role_or_permission_api:update branch_menu_sizes'); //sizes update
            Route::get('showAll/{branch_id}', [BranchMenuSizeController::class, 'show_branch'])->middleware('role_or_permission_api:view branch_menu_sizes'); //sizes all
            Route::get('changeStatus/{id}', [BranchMenuSizeController::class, 'change_status'])->middleware('role_or_permission_api:change branch_menu_sizes'); //sizes active
        });
    });
    // End branches

    //Start Color
    Route::group(['prefix' => 'color'], function () {
        Route::get('/', [ColorController::class, 'index'])->middleware('role_or_permission_api:view color');
        Route::post('/add', [ColorController::class, 'store'])->middleware('role_or_permission_api:create color');
        Route::get('/get', [ColorController::class, 'show'])->middleware('role_or_permission_api:view color');
        Route::post('/edit', [ColorController::class, 'update'])->middleware('role_or_permission_api:update color');
        Route::delete('/delete', [ColorController::class, 'destroy'])->middleware('role_or_permission_api:delete color');
    });

    //marital_statuses
    Route::group(['prefix' => 'marital_statuses'], function () {
        Route::get('/', [MaritalStatusController::class, 'index'])->middleware('role_or_permission_api:view marital_statuses');
        Route::get('/{id}', [MaritalStatusController::class, 'show'])->middleware('role_or_permission_api:view marital_statuses');
        Route::post('store', [MaritalStatusController::class, 'store'])->middleware('role_or_permission_api:create marital_statuses');
        Route::post('update/{id}', [MaritalStatusController::class, 'update'])->middleware('role_or_permission_api:update marital_statuses');
        Route::delete('delete/{id}', [MaritalStatusController::class, 'delete'])->middleware('role_or_permission_api:delete marital_statuses');
    });

    //military_service_statuses
    Route::group(['prefix' => 'military_service_statuses'], function () {
        Route::get('/', [MilitaryServiceStatusController::class, 'index'])->middleware('role_or_permission_api:view military_service_statuses');
        Route::get('/{id}', [MilitaryServiceStatusController::class, 'show'])->middleware('role_or_permission_api:view military_service_statuses');
        Route::post('store', [MilitaryServiceStatusController::class, 'store'])->middleware('role_or_permission_api:create military_service_statuses');
        Route::post('update/{id}', [MilitaryServiceStatusController::class, 'update'])->middleware('role_or_permission_api:update military_service_statuses');
        Route::delete('delete/{id}', [MilitaryServiceStatusController::class, 'delete'])->middleware('role_or_permission_api:delete military_service_statuses');
    });

    //payment-types
    Route::group(['prefix' => 'payment-type'], function () {
        Route::get('/', [PaymentTypeController::class, 'index'])->middleware('role_or_permission_api:view payment_types');
        Route::post('store', [PaymentTypeController::class, 'store'])->middleware('role_or_permission_api:create payment_types');
        Route::get('show/{id}', [PaymentTypeController::class, 'show'])->middleware('role_or_permission_api:view payment_types');
        Route::post('update/{id}', [PaymentTypeController::class, 'update'])->middleware('role_or_permission_api:update payment_types');
        Route::delete('delete/{id}', [PaymentTypeController::class, 'delete'])->middleware('role_or_permission_api:delete payment_types');
    });

    //payment-frequencies
    Route::group(['prefix' => 'payment-frequency'], function () {
        Route::get('/', [PaymentFrequencyController::class, 'index'])->middleware('role_or_permission_api:view payment_frequencies');
        Route::post('store', [PaymentFrequencyController::class, 'store'])->middleware('role_or_permission_api:create payment_frequencies');
        Route::get('show/{id}', [PaymentFrequencyController::class, 'show'])->middleware('role_or_permission_api:view payment_frequencies');
        Route::post('update/{id}', [PaymentFrequencyController::class, 'update'])->middleware('role_or_permission_api:update payment_frequencies');
        Route::delete('delete/{id}', [PaymentFrequencyController::class, 'delete'])->middleware('role_or_permission_api:delete payment_frequencies');
    });

    Route::group(['prefix' => 'vehicle-settings'], function () {
        Route::get('/', [VehicleSettingController::class, 'index'])->middleware('role_or_permission_api:view vehicle_settings');
        Route::post('/update/{id}', [VehicleSettingController::class, 'update'])->middleware('role_or_permission_api:update vehicle_settings');
        Route::get('/show/{id}', [VehicleSettingController::class, 'show'])->middleware('role_or_permission_api:view vehicle_settings');
    });


    Route::group(['prefix' => 'vehicles'], function () {
        Route::get('/list', [VehicleApiController::class, 'index'])->middleware('role_or_permission_api:view vehicles');
        Route::get('/show/{id}', [VehicleApiController::class, 'show'])->middleware('role_or_permission_api:view vehicles');

        Route::post('store', [VehicleApiController::class, 'store'])->middleware('role_or_permission_api:create vehicles');
        Route::post('update/{id}', [VehicleApiController::class, 'update'])->middleware('role_or_permission_api:update vehicles');
        Route::delete('delete/{id}', [VehicleApiController::class, 'destroy'])->middleware('role_or_permission_api:delete vehicles');
    });
    Route::group(['prefix' => 'cash-setting'], function () {
        Route::get('/', [CashPaymentSettingController::class, 'index'])->middleware('role_or_permission_api:view cashPaymentSetting');
        Route::post('add', [CashPaymentSettingController::class, 'store'])->middleware('role_or_permission_api:create cashPaymentSetting');
        Route::get('/show/{id}', [CashPaymentSettingController::class, 'show'])->middleware('role_or_permission_api:view cashPaymentSetting');
        Route::post('edit', [CashPaymentSettingController::class, 'update'])->middleware('role_or_permission_api:update cashPaymentSetting');
        Route::delete('delete', [CashPaymentSettingController::class, 'destroy'])->middleware('role_or_permission_api:delete cashPaymentSetting');
    });

    Route::get('/dashboard/home', [DashboardAPIsHomeController::class, 'index'])->middleware('role_or_permission_api:view dashboard');
    Route::get('/report/branches', [BranchReportController::class, 'index'])->middleware('role_or_permission_api:view report_branches');
    Route::get('/report/branches/{id}', [BranchReportController::class, 'show'])->middleware('role_or_permission_api:view report_branches');
    Route::post('/report/customer-service-delivery-orders', [CustomerDeliveryOrderReportController::class, 'list'])->middleware('role_or_permission_api:view report_customer_service_delivery_orders');

    Route::get('/filed_of_study', [FiledOfStudyController::class, 'index']);
    Route::group(['prefix' => 'filed_of_study'], function () {
        Route::post('store', [FiledOfStudyController::class, 'store']);
        Route::post('update/{id}', [FiledOfStudyController::class, 'update']);
        Route::delete('delete/{id}', [FiledOfStudyController::class, 'delete']);
    });
    //university
    Route::get('/university', [UniversityApiController::class, 'index'])->middleware('role_or_permission_api:view university');
    Route::group(['prefix' => 'university'], function () {
        Route::get('/show/{id}', [UniversityApiController::class, 'show'])->middleware('role_or_permission_api:view university');

        Route::post('store', [UniversityApiController::class, 'store'])->middleware('role_or_permission_api:create university');
        Route::post('update/{id}', [UniversityApiController::class, 'update'])->middleware('role_or_permission_api:update university');
        Route::delete('delete/{id}', [UniversityApiController::class, 'destroy'])->middleware('role_or_permission_api:delete university');
    });

    Route::group(['prefix' => 'kitchen_performance'], function () {
        Route::get('/list', [KitchenPerformanceReportController::class, 'index']);
        Route::get('/show/{id}', [KitchenPerformanceReportController::class, 'show']);
    });
    Route::group(['prefix' => 'cashier-performance'], function () {
        Route::get('/list', [CashierPerformanceReportsController::class, 'list']);
        Route::get('/show/{id}', [CashierPerformanceReportsController::class, 'show']);
    });
    Route::group(['prefix' => 'delivery_performance_metrics_report'], function () {
        Route::get('/list', [DeliveryPerformanceMetricsReportController::class, 'index']);
        Route::get('/show/{id}', [DeliveryPerformanceMetricsReportController::class, 'show']);
    });

    Route::group(['prefix' => 'reports'], function () {
        Route::group(['prefix' => 'cashier-balances'], function () {
            Route::get('/list', [CashierBalancesReportsController::class, 'index']);
            Route::get('/show/{id}', [CashierBalancesReportsController::class, 'show']);
        });
    });


    Route::group(['prefix' => 'delivery_earnings_payments_report'], function () {
        Route::get('/list', [DeliveryEearningsPaymentsReportController::class, 'index']);
        Route::get('/show/{id}', [DeliveryEearningsPaymentsReportController::class, 'show']);
    });

    //API_codes
    Route::group(['prefix' => 'api_code'], function () {
        Route::get('/', [ApiCodeController::class, 'index']);
        Route::post('store', [ApiCodeController::class, 'store']);
        Route::post('update/{id}', [ApiCodeController::class, 'update']);
    });

    Route::prefix('reports')->group(function () {

        //To be removed
        Route::prefix('/ordercancel')->group(function () {
            Route::get('/list', [OrdersReportsCancelController::class, 'list'])->middleware('role_or_permission_api:view order_cancel_report');
            Route::get('/show/{id}', [OrdersReportsCancelController::class, 'show'])->middleware('role_or_permission_api:view order_cancel_report');
        });
        //New
        Route::prefix('/cancelled-delivery-orders')->group(function () {
            Route::get('/list', [CancelledDeliveryOrdersReportController::class, 'list'])->middleware('role_or_permission_api:view order_cancel_report');
            Route::get('/show/{id}', [CancelledDeliveryOrdersReportController::class, 'show'])->middleware('role_or_permission_api:view order_cancel_report');
        });

        Route::group(['prefix' => 'orders'], function () {
            Route::get('list', [OrdersReportsController::class, 'list'])->middleware('role_or_permission_api:view report_orders');
            Route::get('detail/{id}', [OrdersReportsController::class, 'show'])->middleware('role_or_permission_api:detail report_orders');
        });
        Route::group(['prefix' => 'hanging-orders'], function () {
            Route::get('list', [HangingOrdersReportController::class, 'hangingOrders'])->middleware('role_or_permission_api:view report_orders');
            Route::get('detail/{id}', [HangingOrdersReportController::class, 'hangingOrdersDetails'])->middleware('role_or_permission_api:detail report_orders');
        });
        Route::group(['prefix' => 'branch-safe'], function () {
            Route::get('/', [BalanceCotroller::class, 'index'])->middleware('role_or_permission_api:access reports branch safe');
            Route::get('/show/{id}', [BalanceCotroller::class, 'show'])->middleware('role_or_permission_api:access reports branch safe');
            Route::get('/cashier-balance', [BalanceCotroller::class, 'indexCashierBalance'])->middleware('role_or_permission_api:access reports branch safe');
            Route::get('/cashier-balance/{id}', [BalanceCotroller::class, 'showCashierBalance'])->middleware('role_or_permission_api:access reports branch safe');
            Route::get('/cashier-balance-transaction/{id}', [BalanceCotroller::class, 'showCashierBalanceTransaction'])->middleware('role_or_permission_api:access reports branch safe');
        });

        Route::group(['prefix' => 'waiter_table_service_report'], function () {
            Route::get('list', [WaiterTableServiceReportController::class, 'index'])->middleware('role_or_permission_api:view waiter_table_service_report');
            Route::get('/{id}', [WaiterTableServiceReportController::class, 'show'])->middleware('role_or_permission_api:detail waiter_table_service_report');
        });

        Route::group(['prefix' => 'waiter_requests'], function () {
            Route::get('list', [WaiterRequestReportController::class, 'index'])->middleware('role_or_permission_api:view report_waiter_requests');
            Route::post('/search', [WaiterRequestReportController::class, 'search'])->middleware('role_or_permission_api:view report_waiter_requests');
        });

        Route::group(['prefix' => 'booking_revenue'], function () {
            Route::get('list', [BookingRevenueReportController::class, 'index'])->middleware('role_or_permission_api:view report_booking_revenue');
            Route::get('show/{id}', [BookingRevenueReportController::class, 'show'])->middleware('role_or_permission_api:detail report_booking_revenue');
        });

        Route::group(['prefix' => 'booking-cancellations'], function () {
            Route::get('list', [BookingCancellationReportApiController::class, 'index'])->middleware('role_or_permission_api:view report_booking_cancellation');
            Route::get('/{id}', [BookingCancellationReportApiController::class, 'show'])->middleware('role_or_permission_api:detail report_booking_cancellation');
        });
    });
    Route::get('cashiers/list', [BalanceCotroller::class, 'allcashiers']);
    Route::get('waiters/list', [WaiterTableServiceReportController::class, 'allwaiters']);

    // Start Category
    Route::group(['prefix' => 'category'], function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('store', [CategoryController::class, 'store']);
        Route::post('update/{id}', [CategoryController::class, 'update']);
        Route::get('delete/{id}', [CategoryController::class, 'delete']);
        Route::post('product-brand-category-colors', [CategoryController::class, 'product_color_store']);
        Route::post('product-brand-size-colors', [CategoryController::class, 'product_size_store']);
    });

    Route::get('itemCode/list', [RecipeController::class, 'itemCodeList'])->name('itemCodeList.index');
    Route::get('recipes/list/products', [RecipeController::class, 'listProducts']);

    Route::prefix('recipes')->group(function () {
        Route::get('/list', [RecipeController::class, 'index'])->name('recipes.index')->middleware('role_or_permission_api:view recipes');
        Route::post('/create', [RecipeController::class, 'store'])->name('recipes.store')->middleware('role_or_permission_api:create recipes');
        Route::get('/view/{id}', [RecipeController::class, 'show'])->name('recipes.show')->middleware('role_or_permission_api:view recipes');
        Route::post('/update/{id}', [RecipeController::class, 'update'])->name('recipes.update')->middleware('role_or_permission_api:update recipes');
        Route::delete('/delete/{id}', [RecipeController::class, 'destroy'])->name('recipes.destroy')->middleware('role_or_permission_api:delete recipes');
    });

    //cuisines
    Route::prefix('cuisines')->group(function () {
        Route::get('cuisineList', [CuisineController::class, 'index'])->name('cuisines.list')->middleware('role_or_permission_api:view cuisines');
        Route::post('addCuisine', [CuisineController::class, 'store'])->name('cuisines.add')->middleware('role_or_permission_api:create cuisines');
        Route::get('showCuisine/{id}', [CuisineController::class, 'show'])->name('cuisines.show')->middleware('role_or_permission_api:view cuisines');
        Route::post('updateCuisine/{id}', [CuisineController::class, 'update'])->name('cuisines.update')->middleware('role_or_permission_api:update cuisines');
        Route::delete('deleteCuisine/{id}', [CuisineController::class, 'destroy'])->name('cuisines.delete')->middleware('role_or_permission_api:delete cuisines');
        Route::post('restoreCuisine/{id}', [CuisineController::class, 'restore'])->name('cuisines.restore')->middleware('role_or_permission_api:restore cuisines');
        Route::post('assign-dish-categories/{id}', [CuisineController::class, 'assignDishCategories'])->middleware('role_or_permission_api:delete cuisines');
    });

    //Dish Categories
    Route::prefix('dish-categories')->group(function () {
        Route::get('/', [DishCategoryController::class, 'index'])->name('dish_categories.index')->middleware('role_or_permission_api:view dish_categories');
        Route::post('/', [DishCategoryController::class, 'store'])->name('dish_categories.store')->middleware('role_or_permission_api:create dish_categories');
        Route::post('/{id}', [DishCategoryController::class, 'update'])->name('dish_categories.update')->middleware('role_or_permission_api:update dish_categories');
        Route::delete('/{id}', [DishCategoryController::class, 'delete'])->name('dish_categories.delete')->middleware('role_or_permission_api:delete dish_categories');
        Route::get('/show/{id}', [DishCategoryController::class, 'show'])->name('dish_categories.show')->middleware('role_or_permission_api:view dish_categories');
    });

    //dishes
    Route::prefix('dishes')->group(function () {
        Route::get('/list', [DishController::class, 'index'])->name('dishes.index')->middleware('role_or_permission_api:view dishes');
        Route::post('/create', [DishController::class, 'store'])->name('dishes.store');
        Route::get('/view/{id}', [DishController::class, 'show'])->name('dishes.show')->middleware('role_or_permission_api:view dishes');
        Route::post('/update/{id}', [DishController::class, 'update'])->name('dishes.update')->middleware('role_or_permission_api:update dishes');
        Route::delete('/delete/{id}', [DishController::class, 'destroy'])->name('dishes.destroy')->middleware('role_or_permission_api:delete dishes');
        Route::post('/restore/{id}', [DishController::class, 'restore'])->name('dishes.restore')->middleware('role_or_permission_api:restore dishes');
        Route::post('/save-ingredient', [DishController::class, 'saveIngredient'])->name('dishes.save-ingredient')->middleware('role_or_permission_api:create dishes');
    });

    // Best Seller Dish Report
    Route::prefix('report/best-seller-dishes')->group(function () {
        Route::get('/', [BestSellerDishReportController::class, 'index'])->middleware('role_or_permission_api:view best_seller_dish_report');
        Route::get('/{id}', [BestSellerDishReportController::class, 'show'])->middleware('role_or_permission_api:view best_seller_dish_report');
    });

    //addons
    Route::prefix('addons')->group(function () {
        Route::get('/list', [AddonController::class, 'index'])->middleware('role_or_permission_api:view addons');
        Route::get('/show/{id}', [AddonController::class, 'show'])->middleware('role_or_permission_api:view addons');
        Route::post('/add', [AddonController::class, 'store'])->middleware('role_or_permission_api:create addons');
        Route::post('/update/{id}', [AddonController::class, 'update'])->middleware('role_or_permission_api:update addons');
        Route::delete('/delete/{id}', [AddonController::class, 'destroy'])->middleware('role_or_permission_api:delete addons');
    });
    Route::prefix('payment_policies')->group(function () {
        Route::get('/list', [PaymentPoliciesController::class, 'index'])->middleware('role_or_permission_api:view payment_policies');
        Route::get('/show/{id}', [PaymentPoliciesController::class, 'show'])->middleware('role_or_permission_api:view payment_policies');
        Route::post('/add', [PaymentPoliciesController::class, 'store'])->middleware('role_or_permission_api:create payment_policies');
        Route::post('/update/{id}', [PaymentPoliciesController::class, 'update'])->middleware('role_or_permission_api:update payment_policies');
        Route::delete('/delete/{id}', [PaymentPoliciesController::class, 'destroy'])->middleware('role_or_permission_api:delete payment_policies');
    });
    Route::prefix('logo')->group(function () {
        Route::get('/list', [LogoController::class, 'index'])->middleware('role_or_permission_api:view logos');
        Route::get('/show/{id}', [LogoController::class, 'show'])->middleware('role_or_permission_api:view logos');
        Route::post('/add', [LogoController::class, 'store'])->middleware('role_or_permission_api:create logos');
        Route::post('/update/{id}', [LogoController::class, 'update'])->middleware('role_or_permission_api:update logos');
        Route::delete('/delete/{id}', [LogoController::class, 'destroy'])->middleware('role_or_permission_api:delete logos');
    });
    Route::prefix('slider')->group(function () {
        Route::get('/list', [SliderController::class, 'index'])->middleware('role_or_permission_api:view sliders');
        Route::get('/show/{id}', [SliderController::class, 'show'])->middleware('role_or_permission_api:view sliders');
        Route::post('/add', [SliderController::class, 'store'])->middleware('role_or_permission_api:create sliders');
        Route::post('/update/{id}', [SliderController::class, 'update'])->middleware('role_or_permission_api:update sliders');
        Route::delete('/delete/{id}', [SliderController::class, 'destroy'])->middleware('role_or_permission_api:delete sliders');
    });

    Route::prefix('termsandconditions')->group(function () {
        Route::get('/list', [TermsAndConditionsController::class, 'index'])->middleware('role_or_permission_api:view terms');
        Route::get('/show/{id}', [TermsAndConditionsController::class, 'show'])->middleware('role_or_permission_api:view terms');
        Route::post('/add', [TermsAndConditionsController::class, 'store'])->middleware('role_or_permission_api:create terms');
        Route::post('/update/{id}', [TermsAndConditionsController::class, 'update'])->middleware('role_or_permission_api:update terms');
        Route::delete('/delete/{id}', [TermsAndConditionsController::class, 'destroy'])->middleware('role_or_permission_api:delete terms');
    });
    Route::prefix('cancel_reasons')->group(function () {
        Route::get('/list', [OrderCancellationReasonController::class, 'index'])->middleware('role_or_permission_api:view cancellation_reasons');
        Route::get('/show/{id}', [OrderCancellationReasonController::class, 'show'])->middleware('role_or_permission_api:view cancellation_reasons');
        Route::post('/add', [OrderCancellationReasonController::class, 'store'])->middleware('role_or_permission_api:create cancellation_reasons');
        Route::post('/update/{id}', [OrderCancellationReasonController::class, 'update'])->middleware('role_or_permission_api:update cancellation_reasons');
        Route::delete('/delete/{id}', [OrderCancellationReasonController::class, 'destroy'])->middleware('role_or_permission_api:delete cancellation_reasons');
    });

    Route::group(['prefix' => 'return-invoice'], function () {
        Route::get('/', [ReturnInvoiceController::class, 'index'])->middleware('role_or_permission_api:view returnInvoiceRequest');
        Route::get('/{id}', [ReturnInvoiceController::class, 'show'])->middleware('role_or_permission_api:show returnInvoiceRequest');
    });

    Route::group(['prefix' => 'invoice'], function () {
        Route::get('/', [InvoiceController::class, 'index'])->middleware('role_or_permission_api:view invoice');
        Route::get('/{id}', [InvoiceController::class, 'show'])->middleware('role_or_permission_api:view invoice');
    });

    Route::group(['prefix' => 'hotel'], function () {
        Route::get('/{id}', [HotelController::class, 'show'])->middleware('role_or_permission_api:show hotels');
        Route::post('/', [HotelController::class, 'store'])->middleware('role_or_permission_api:create hotels');
        Route::post('/{id}', [HotelController::class, 'update'])->middleware('role_or_permission_api:update hotels');
        Route::delete('/{id}', [HotelController::class, 'delete'])->middleware('role_or_permission_api:delete hotels');
    });

    Route::group(['prefix' => 'privacy'], function () {
        Route::get('/list', [PrivacyPolicyController::class, 'index'])->middleware('role_or_permission_api:view privacies');
        Route::post('/add', [PrivacyPolicyController::class, 'store'])->middleware('role_or_permission_api:create privacies');
        Route::get('/show/{id}', [PrivacyPolicyController::class, 'show'])->middleware('role_or_permission_api:view privacies');
        Route::post('/update/{id}', [PrivacyPolicyController::class, 'update'])->middleware('role_or_permission_api:update privacies');
        Route::delete('/delete/{id}', [PrivacyPolicyController::class, 'destroy'])->middleware('role_or_permission_api:delete privacies');
    });


    Route::group(['prefix' => 'return'], function () {
        Route::get('/list', [ReturnPolicyController::class, 'index'])->middleware('role_or_permission_api:view returns');
        Route::post('/add', [ReturnPolicyController::class, 'store'])->middleware('role_or_permission_api:create returns');
        Route::get('/show/{id}', [ReturnPolicyController::class, 'show'])->middleware('role_or_permission_api:view returns');
        Route::post('/update/{id}', [ReturnPolicyController::class, 'update'])->middleware('role_or_permission_api:update returns');
        Route::delete('/delete/{id}', [ReturnPolicyController::class, 'destroy'])->middleware('role_or_permission_api:delete returns');
    });

    Route::group(['prefix' => 'payment_reservation'], function () {
        Route::get('/list', [PolicyPaymentReservationController::class, 'index'])->middleware('role_or_permission_api:view payment_reservation_policies');
        Route::post('/add', [PolicyPaymentReservationController::class, 'store'])->middleware('role_or_permission_api:create payment_reservation_policies');
        Route::get('/show/{id}', [PolicyPaymentReservationController::class, 'show'])->middleware('role_or_permission_api:view payment_reservation_policies');
        Route::post('/update/{id}', [PolicyPaymentReservationController::class, 'update'])->middleware('role_or_permission_api:update payment_reservation_policies');
        Route::delete('/delete/{id}', [PolicyPaymentReservationController::class, 'destroy'])->middleware('role_or_permission_api:delete payment_reservation_policies');
    });

    Route::group(['prefix' => 'faq'], function () {
        Route::get('/list', [FAQController::class, 'index'])->middleware('role_or_permission_api:view faqs');
        Route::post('/add', [FAQController::class, 'store'])->middleware('role_or_permission_api:create faqs');
        Route::get('/show/{id}', [FAQController::class, 'show'])->middleware('role_or_permission_api:view faqs');
        Route::post('/update/{id}', [FAQController::class, 'update'])->middleware('role_or_permission_api:update faqs');
        Route::delete('/delete/{id}', [FAQController::class, 'destroy'])->middleware('role_or_permission_api:delete faqs');
    });


    // point-system
    Route::prefix('point_system')->group(function () {
        Route::get('/', [pointsController::class, 'index']);
        Route::post('/', [pointsController::class, 'store']); // it not allow to add any system
        Route::get('/{id}/{branch}', [pointsController::class, 'show']);
        Route::post('/{id}', [pointsController::class, 'update']);
        Route::delete('/{id}', [pointsController::class, 'destroy']);
        Route::prefix('transactions')->group(function () {});
    });

    //brands
    Route::prefix('brands')->group(function () {
        Route::get('/list', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/show/{id}', [BrandController::class, 'show'])->name('brands.show');
        Route::post('/create', [BrandController::class, 'store'])->name('brands.store');
        Route::post('/update/{id}', [BrandController::class, 'update'])->name('brands.update');
        Route::delete('/delete/{id}', [BrandController::class, 'destroy'])->name('brands.destroy');
        Route::post('/restore/{id}', [BrandController::class, 'restore'])->name('brands.restore');
    });

    // Gifts
    Route::group(['prefix' => 'gifts'], function () {
        Route::get('/', [GiftController::class, 'index'])->name('gifts.index');
        Route::get('/{id}', [GiftController::class, 'show'])->name('gifts.show');
        Route::post('/', [GiftController::class, 'store'])->name('gifts.store');
        Route::put('/{id}', [GiftController::class, 'update'])->name('gifts.update');
        Route::delete('/{id}', [GiftController::class, 'destroy'])->name('gifts.destroy');
        Route::post('/apply-to-users', [GiftController::class, 'applyGiftToUsers']);
        Route::post('/apply-to-branch', [GiftController::class, 'applyGiftByBranch']);
    });
    Route::prefix('menu')->group(function () {
        Route::get('/list', [MenuController::class, 'index'])->name('menu.index');
        Route::get('/show/{branch_id}', [MenuController::class, 'show'])->name('menu.show');
        Route::post('/create', [MenuController::class, 'store'])->name('menu.store');     //clone menu
        Route::put('/update/{branch_id}', [MenuController::class, 'update'])->name('menu.update');
        Route::delete('/delete', [MenuController::class, 'destroy'])->name('menu.destroy');
        Route::post('/restore', [MenuController::class, 'restore'])->name('menu.restore');
    });

    // Addon Categories routes
    Route::prefix('addonCategories')->name('api.addon_categories.')->group(function () {
        Route::get('/list', [AddonCategoryController::class, 'index'])->name('index');
        Route::get('show/{id}', [AddonCategoryController::class, 'show'])->name('show');
        Route::post('/add', [AddonCategoryController::class, 'store'])->name('store');
        Route::post('update/{id}', [AddonCategoryController::class, 'update'])->name('update');
        Route::delete('delete/{id}', [AddonCategoryController::class, 'destroy'])->name('destroy');
        Route::post('{id}/restore', [AddonCategoryController::class, 'restore'])->name('restore');
    });
    //return invoice request
    Route::prefix('return-invoice-request')->group(function () {
        Route::get('invoice/{id}', [OrderReturnInvoiceRequestController::class, 'showInvoice']);
        Route::get('/', [OrderReturnInvoiceRequestController::class, 'index']);
        Route::get('/{id}', [OrderReturnInvoiceRequestController::class, 'show']);
        Route::post('/{id}', [OrderReturnInvoiceRequestController::class, 'update']);
    });

    //complaints
    Route::get('/complaints', [OrderComplaintsController::class, 'index'])->middleware('role_or_permission_api:access complaints');
    Route::group(['prefix' => 'complaints'], function () {
        Route::get('show/{id}', [OrderComplaintsController::class, 'show'])->middleware('role_or_permission_api:access complaints');
        Route::put('update/{id}', [OrderComplaintsController::class, 'changeStatus'])->middleware('role_or_permission_api:access complaints');
        Route::delete('delete/{id}', [OrderComplaintsController::class, 'delete'])->middleware('role_or_permission_api:access complaints');
    });

    //change table
    Route::post('/order/changetable', [OrderComplaintsController::class, 'changeOrderTable'])->middleware('role_or_permission_api:changeTable');
    Route::get('/list/orders', [OrderDashboardController::class, 'listOrders'])->middleware('role_or_permission_api:listOrders');
    Route::get('/orderDetails/{id}', [OrdersReportsController::class, 'show'])->middleware('role_or_permission_api:detailsOrder');
    Route::get('/ShowOrderDetails/{id}', [OrdersReportsController::class, 'details'])->middleware('role_or_permission_api:detailsOrder');

    Route::group(['prefix' => 'coupons'], function () {
        Route::get('list', [CouponController::class, 'index'])->middleware('role_or_permission_api:view coupons');
        Route::get('show/{id}', [CouponController::class, 'show'])->middleware('role_or_permission_api:view coupons');
        Route::post('add', [CouponController::class, 'store'])->middleware('role_or_permission_api:create coupons');
        Route::put('update/{id}', [CouponController::class, 'update'])->middleware('role_or_permission_api:update coupons');
        Route::delete('delete/{id}', [CouponController::class, 'destroy'])->middleware('role_or_permission_api:delete coupons');
        Route::post('restore/{id}', [CouponController::class, 'restore'])->middleware('role_or_permission_api:update coupons');
    });

    //hanging-orders
    Route::get('/hanging-orders', [DeliveryComplaintsController::class, 'index'])->middleware('role_or_permission_api:access hanging-orders');
    Route::group(['prefix' => 'hanging-orders'], function () {
        Route::get('show/{id}', [DeliveryComplaintsController::class, 'show'])->middleware('role_or_permission_api:access hanging-orders');
        Route::put('update/{id}', [DeliveryComplaintsController::class, 'changeStatus'])->middleware('role_or_permission_api:access hanging-orders');
        Route::delete('delete/{id}', [DeliveryComplaintsController::class, 'delete'])->middleware('role_or_permission_api:access hanging-orders');
    });

    //waiter-requests
    Route::get('/waiter-requests', [WaiterRequestController::class, 'index']);
    Route::get('/waiter-requests/{id}', [WaiterRequestController::class, 'showInvoice']);
    Route::post('/reject/request', [WaiterRequestController::class, 'rejectRequest']);
    Route::get('/accept/request/{id}', [WaiterRequestController::class, 'acceptRequest']);
    Route::get('/request/ajax-print', [WaiterRequestController::class, 'ajaxPrint']);

    //cashier-machine
    Route::prefix('cashier_machines')->group(function () {
        Route::get('/', [CashierMachineController::class, 'index'])->middleware('role_or_permission_api:view cashier_machines');
        Route::get('/{id}', [CashierMachineController::class, 'show'])->middleware('role_or_permission_api:view cashier_machines');
        Route::post('/', [CashierMachineController::class, 'store'])->middleware('role_or_permission_api:create cashier_machines');
        Route::post('/{id}', [CashierMachineController::class, 'update'])->middleware('role_or_permission_api:update cashier_machines');
        Route::delete('/{id}', [CashierMachineController::class, 'destroy'])->middleware('role_or_permission_api:delete cashier_machines');
    });

    //electronic-invoices
    Route::get('/posName', [CashierSettingController::class, 'posName']);
    Route::prefix('electronic_invoices')->group(function () {
        Route::get('/', [CashierSettingController::class, 'index'])->middleware('role_or_permission_api:view einvoices_superadmin');
        Route::get('/show/{id}', [CashierSettingController::class, 'show'])->middleware('role_or_permission_api:view einvoices_superadmin');
        Route::get('/employees', [CashierSettingController::class, 'createSetting'])->middleware('role_or_permission_api:create officer_assign_setting');
        Route::post('/', [CashierSettingController::class, 'store'])->middleware('role_or_permission_api:create officer_assign_setting');
        Route::get('/setting', [CashierSettingController::class, 'setting'])->middleware('role_or_permission_api:view officer_assign_setting');
        Route::post('/update/{id}', [CashierSettingController::class, 'update'])->middleware('role_or_permission_api:update officer_assign_setting');
    });

    // clients
    Route::group(['prefix' => 'clients'], function () {
        Route::get('list', [ClientApiController::class, 'index'])->middleware('role_or_permission_api:view clients');
        Route::get('show/{id}', [ClientApiController::class, 'show'])->middleware('role_or_permission_api:view clients');
        Route::post('addNew', [ClientApiController::class, 'store'])->middleware('role_or_permission_api:create clients');
        Route::post('updateUser/{id}', [ClientApiController::class, 'update'])->middleware('role_or_permission_api:update clients');
        Route::delete('deleteUser/{id}', [ClientApiController::class, 'destroy'])->middleware('role_or_permission_api:delete clients');
        Route::post('restore/{id}', [ClientApiController::class, 'restore'])->middleware('role_or_permission_api:update clients');
    });
});
