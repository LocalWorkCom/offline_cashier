<?php

//use App\Events\NotifySent;
use App\Http\Controllers\Dashboard\testController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\FAQController;
use App\Http\Controllers\Dashboard\DishController;
use App\Http\Controllers\Dashboard\GiftController;
use App\Http\Controllers\Dashboard\LogoController;
use App\Http\Controllers\Dashboard\RoleController;
//use App\Http\Controllers\Dashboard\ShiftDetailController;
use App\Http\Controllers\Dashboard\SizeController;
use App\Http\Controllers\Dashboard\UnitController;
use App\Http\Controllers\Dashboard\AddonController;
use App\Http\Controllers\Dashboard\BrandController;
use App\Http\Controllers\Dashboard\ColorController;
use App\Http\Controllers\Dashboard\FloorController;
use App\Http\Controllers\Dashboard\HotelController;
//use App\Http\Controllers\Api\WaiterAPIs\InvoiceController;
//use App\Http\Controllers\chatController;
use App\Http\Controllers\Dashboard\OfferController;
use App\Http\Controllers\Dashboard\OrderController;
use App\Http\Controllers\Dashboard\ShiftController;
use App\Http\Controllers\Dashboard\TableController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Dashboard\BranchController;
use App\Http\Controllers\Dashboard\CitiesController;
use App\Http\Controllers\Dashboard\ClientController;
use App\Http\Controllers\Dashboard\CouponController;
use App\Http\Controllers\Dashboard\RecipeController;
use App\Http\Controllers\Dashboard\SliderController;
//use App\Http\Controllers\PusherController;
//use App\Models\User;
//use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Dashboard\VendorController;
use App\Http\Controllers\Dashboard\CountryController;
//use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Dashboard\CuisineController;
use App\Http\Controllers\Dashboard\JobTypeController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\RateOldController;
use App\Http\Controllers\Dashboard\RegionsController;
use App\Http\Controllers\Dashboard\VehicleController;
use App\Http\Controllers\Dashboard\BankNameController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\CurrencyController;
use App\Http\Controllers\Dashboard\DiscountController;
use App\Http\Controllers\Dashboard\EinvoiceController;
use App\Http\Controllers\Dashboard\EmployeeController;
use App\Http\Controllers\Dashboard\EReceiptController;
use App\Http\Controllers\Dashboard\PositionController;
use App\Http\Controllers\Dashboard\PurchaseController;
use App\Http\Controllers\Auth\ForgetPasswordController;
use App\Http\Controllers\Dashboard\LeaveTypeController;
use App\Http\Controllers\Dashboard\TimeTableController;
use App\Http\Controllers\Dashboard\ViolationController;
use App\Http\Controllers\Dashboard\BranchMenuController;
//use App\Http\Controllers\Dashboard\ReportController;
use App\Http\Controllers\Dashboard\BranchSafeController;
use App\Http\Controllers\Dashboard\ComplaintsController;
use App\Http\Controllers\Dashboard\DepartmentController;
use App\Http\Controllers\Dashboard\PermissionController;
use App\Http\Controllers\Dashboard\RateReportController;
use App\Http\Controllers\Dashboard\UniversityController;
use App\Http\Controllers\Dashboard\ApplicationController;
use App\Http\Controllers\Dashboard\DishProductController;
use App\Http\Controllers\Dashboard\NationalityController;
use App\Http\Controllers\Dashboard\OfferDetailController;
use App\Http\Controllers\Dashboard\PaymentTypeController;
use App\Http\Controllers\Dashboard\DishCategoryController;
use App\Http\Controllers\Dashboard\FiledOfStudyController;
use App\Http\Controllers\Dashboard\LeaveSettingController;
use App\Http\Controllers\Dashboard\OrderSettingController;
use App\Http\Controllers\Dashboard\ReturnPolicyController;
use App\Http\Controllers\Dashboard\AddonCategoryController;
use App\Http\Controllers\Dashboard\BranchSettingController;
use App\Http\Controllers\Dashboard\MaritalStatusController;
use App\Http\Controllers\Dashboard\OrdersReportsController;
use App\Http\Controllers\Dashboard\PrivacyPolicyController;
use App\Http\Controllers\Dashboard\ReturnInvoiceController;
use App\Http\Controllers\Dashboard\ViolationTypeController;
use App\Http\Controllers\Dashboard\BranchMenuSizeController;
use App\Http\Controllers\Dashboard\cashierMachineController;
use App\Http\Controllers\Dashboard\CashierSettingController;
use App\Http\Controllers\Dashboard\CustomerReportController;
use App\Http\Controllers\Dashboard\EducationLevelController;
use App\Http\Controllers\Dashboard\EmployeeStatusController;
use App\Http\Controllers\Dashboard\FeedbackReportController;
use App\Http\Controllers\Dashboard\FloorPartitionController;
use App\Http\Controllers\Dashboard\VehicleSettingController;
use App\Http\Controllers\Dashboard\BranchesReportsController;
use App\Http\Controllers\Dashboard\BranchMenuAddonController;
use App\Http\Controllers\Dashboard\PaymentPoliciesController;

//use App\Http\Controllers\Dashboard\SettingDeliveryController;
use App\Http\Controllers\Dashboard\BusinessActivityController;
use App\Http\Controllers\Dashboard\EmployeeScheduleController;
use App\Http\Controllers\Dashboard\EthnicBackgroundController;
use App\Http\Controllers\Dashboard\PaymentFrequencyController;
use App\Http\Controllers\Dashboard\PurchasingReportController;
use App\Http\Controllers\Dashboard\BranchMenuCategoryController;
use App\Http\Controllers\Dashboard\CashPaymentSettingController;
use App\Http\Controllers\Dashboard\DeliveryComplaintsController;
use App\Http\Controllers\Dashboard\TermsAndConditionsController;
use App\Http\Controllers\Dashboard\ChefCuisineCategoryController;
use App\Http\Controllers\Dashboard\DeliveryOrderReportController;
use App\Http\Controllers\Dashboard\WaiterRequestReportController;
use App\Http\Controllers\Dashboard\BestSellerDishReportController;
use App\Http\Controllers\Dashboard\BookingRevenueReportController;
use App\Http\Controllers\Dashboard\LeaveSettingPositionController;
use App\Http\Controllers\Dashboard\ReturnInvoiceRequestController;
use App\Http\Controllers\Dashboard\CompanyProfileSettingController;
use App\Http\Controllers\Dashboard\DeliveryOrdersReportsController;
use App\Http\Controllers\Dashboard\MilitaryServiceStatusController;
use App\Http\Controllers\Dashboard\CancelledOrdersReportsController;
use App\Http\Controllers\Dashboard\CashierBalancesReportsController;
use App\Http\Controllers\Dashboard\TableReservationReportController;
use App\Http\Controllers\Dashboard\BranchMenuAddonCategoryController;
use App\Http\Controllers\Dashboard\OrderCancellationReasonController;
use App\Http\Controllers\Dashboard\KitchenPerformanceReportController;
use App\Http\Controllers\Dashboard\PaymentReservationPolicyController;
use App\Http\Controllers\Dashboard\WaiterTableServiceReportController;
use App\Http\Controllers\Dashboard\BookingCancellationReportController;
use App\Http\Controllers\Dashboard\CashierPerformanceReportsController;
use App\Http\Controllers\Dashboard\ContactInformationSettingController;
use App\Http\Controllers\Dashboard\DeliveryComplaintsReportsController;
use App\Http\Controllers\Dashboard\SocialMediaInformationSettingController;
use App\Http\Controllers\Dashboard\DeliveryEearningsPaymentsReportController;
use App\Http\Controllers\Dashboard\DeliveryPerformanceMetricsReportController;
use App\Http\Controllers\Dashboard\CashierBalanceTransactionsReportsController;
use App\Http\Controllers\Dashboard\CashierBranchSafeReportsController;
use App\Http\Controllers\Dashboard\CustomerServiceDeliveryOrdersReportsController;
use App\Http\Controllers\Dashboard\InvoiceController;
use App\Http\Controllers\Dashboard\WaiterRequestController;

//use Illuminate\Support\Facades\Request;

/*
|--------------------------------------------------------------------------
| dashboard  Routes
|--------------------------------------------------------------------------
|
*/

Route::get('request/applications/{id}', [ApplicationController::class, 'show'])->name('position.applications.fill');
Route::post('/application-form', [ApplicationController::class, 'storeApplication'])->name('application.submit');

Route::get('/dashboard/login', [LoginController::class, 'showLoginForm'])->name('dashboard.login');
Route::post('/dashboard/login', [LoginController::class, 'login'])->name('dashboard.submitlogin');
Route::post('/dashboard/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/dashboard/forgot-password', [ForgetPasswordController::class, 'showLinkRequestForm'])->name('dashboard.password.request');
Route::post('/dashboard/forgot-password', [ForgetPasswordController::class, 'sendResetLinkEmail'])->name('dashboard.password.email');
Route::get('/dashboard/reset-password/{token}', [ForgetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/dashboard/reset-password', [ForgetPasswordController::class, 'reset'])->name('dashboard.password.update');

Route::get('/error/403', function () {
    return view('dashboard.error.403');
})->name('dashboard.error.403');



Route::get('dashboard', function () {
    return view('dashboard.index');
})->name('dashboard.home')->middleware(['auth:admin', 'admin', 'role_or_permission:view dashboard']);


Route::prefix('dashboard')->middleware('admin')->group(function () {

    //    Route::get('/test-notification', function() {
    //        $user = \App\Models\User::find(9);
    //        if ($user && $user->fcm_token) {
    //            // Log the token to ensure it's correct
    ////            \Log::info("Sending notification to token: " . $user->fcm_token);
    //
    ////            $response = send_push_notification($user->fcm_token, "Test", "Hello World", "test");
    //            $response = send_push_notification(
    //                $user->fcm_token,
    //                'يوجد شكوى مرسلة',
    //                'New complaint received',
    //                'شكوى جديدة',
    //                'New Complaint',
    //                'admin',
    //                $user->id,
    //                20,
    //                2,
    //                'ar',
    //                'http://127.0.0.1:8000/dashboard/complaints/show/' . 2
    //            );
    //            dd($response);
    //            broadcast(new NotifySent(User::find($user->id), $response));
    //
    //
    //            return response()->json([
    //                'token' => $user->fcm_token,
    //                'response' => $response
    //            ]);
    //        }
    //        return response()->json(['error' => 'No token found.']);
    //    });

    // product
    Route::get('/products', [ProductController::class, 'index'])->name('products.list')->middleware('role_or_permission:view products');
    Route::group(['prefix' => 'product'], function () {
        Route::get('/create', [ProductController::class, 'create'])->name('product.create')->middleware('role_or_permission:create products');
        Route::post('store', [ProductController::class, 'store'])->name('product.store')->middleware('role_or_permission:create products');
        Route::get('show/{id}', [ProductController::class, 'show'])->name('product.show')->middleware('role_or_permission:view products');
        Route::get('edit/{id}', [ProductController::class, 'edit'])->name('product.edit')->middleware('role_or_permission:update products');
        Route::put('update/{id}', [ProductController::class, 'update'])->name('product.update')->middleware('role_or_permission:update products');
        Route::delete('delete/{id}', [ProductController::class, 'delete'])->name('product.delete')->middleware('role_or_permission:delete products');
        // Route::get('{id}/inventory', [ProductInventoryController::class, 'getInventory']);

    });
    // product_units
    Route::get('/products/unit/list/{productId}', [ProductController::class, 'unit'])
        ->name('products.units.list')
        ->middleware('role_or_permission:view product_units');

    Route::post('product/{id}/units/save', [ProductController::class, 'saveUnits'])
        ->name('product.units.save')
        ->middleware('role_or_permission:view product_units');

    // product_sizes
    Route::get('/products/size/list/{productId}', [ProductController::class, 'size'])
        ->name('products.sizes.list')
        ->middleware('role_or_permission:view product_sizes');

    Route::post('product/{id}/sizes/save', [ProductController::class, 'saveSizes'])
        ->name('product.sizes.save')
        ->middleware('role_or_permission:view product_sizes');

    // product_colors
    Route::get('/products/color/list/{productId}', [ProductController::class, 'color'])
        ->name('products.colors.list')
        ->middleware('role_or_permission:view product_colors');

    Route::post('product/{id}/colors/save', [ProductController::class, 'saveColors'])
        ->name('product.colors.save')
        ->middleware('role_or_permission:view product_colors');

    // categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.list')->middleware('role_or_permission:view categories');
    Route::group(['prefix' => 'category'], function () {
        Route::get('create', [CategoryController::class, 'create'])->name('category.create')->middleware('role_or_permission:create categories');
        Route::post('store', [CategoryController::class, 'store'])->name('category.store')->middleware('role_or_permission:create categories');
        Route::get('show/{id}', [CategoryController::class, 'show'])->name('category.show')->middleware('role_or_permission:view categories');
        Route::get('edit/{id}', [CategoryController::class, 'edit'])->name('category.edit')->middleware('role_or_permission:update categories');
        Route::put('update/{id}', [CategoryController::class, 'update'])->name('category.update')->middleware('role_or_permission:update categories');
        Route::delete('delete/{id}', [CategoryController::class, 'delete'])->name('category.delete')->middleware('role_or_permission:delete categories');
    });

    // countries
    Route::get('/countries', [CountryController::class, 'index'])->name('countries.list')->middleware('role_or_permission:view countries');
    Route::group(['prefix' => 'country'], function () {
        Route::any('/get', [CountryController::class, 'show'])->name('country.show')->middleware('role_or_permission:view countries');
        Route::post('store', [CountryController::class, 'store'])->name('country.store')->middleware('role_or_permission:create countries');
        Route::put('update/{id}', [CountryController::class, 'update'])->name('country.update')->middleware('role_or_permission:update countries');
        Route::delete('delete/{id}', [CountryController::class, 'destroy'])->name('country.delete')->middleware('role_or_permission:delete countries');
        Route::get('/shows/{id}', [CountryController::class, 'show'])->name('country.shows')->middleware('role_or_permission:view countries');
        Route::post('/countries/check-order', [CountryController::class, 'checkOrder'])->name('countries.check-order');
    });

    // city
    Route::group(['prefix' => 'city'], function () {
        Route::get('/all/{id}', [CitiesController::class, 'index'])->name('city.list')->middleware('role_or_permission:view cities');
        Route::post('store', [CitiesController::class, 'store'])->name('city.store')->middleware('role_or_permission:create cities');
        Route::put('update/{id}', [CitiesController::class, 'update'])->name('city.update')->middleware('role_or_permission:update cities');
        Route::delete('delete/{id}', [CitiesController::class, 'destroy'])->name('city.delete')->middleware('role_or_permission:delete cities');
        Route::get('/show/{id}', [CitiesController::class, 'show'])->name('city.shows')->middleware('role_or_permission:view cities');
        Route::get('/show_all/{country_id}', [CitiesController::class, 'show_all'])->name('city.show_all')->middleware('role_or_permission:view cities');
    });

    // region
    Route::group(['prefix' => 'region'], function () {
        Route::get('/all/{id}',  [RegionsController::class, 'index'])->name('region.list')->middleware('role_or_permission:view regions');
        Route::post('store', [RegionsController::class, 'store'])->name('region.store')->middleware('role_or_permission:create regions');
        Route::put('update/{id}', [RegionsController::class, 'update'])->name('region.update')->middleware('role_or_permission:update regions');
        Route::delete('delete/{id}', [RegionsController::class, 'destroy'])->name('region.delete')->middleware('role_or_permission:delete regions');
        Route::get('/show/{id}', [RegionsController::class, 'show'])->name('region.shows')->middleware('role_or_permission:view regions');
        Route::get('/show_all/{city_id}', [RegionsController::class, 'show_all'])->name('region.show_all')->middleware('role_or_permission:view regions');
    });

    // nationalities
    Route::get('/nationalities', [NationalityController::class, 'index'])->name('nationality.list')->middleware('role_or_permission:view nationalities');
    Route::group(['prefix' => 'nationality'], function () {
        Route::any('/get', [NationalityController::class, 'show'])->name('nationality.show')->middleware('role_or_permission:view nationalities');
        Route::post('store', [NationalityController::class, 'store'])->name('nationality.store')->middleware('role_or_permission:create nationalities');
        Route::put('update/{id}', [NationalityController::class, 'update'])->name('nationality.update')->middleware('role_or_permission:update nationalities');
        Route::delete('delete/{id}', [NationalityController::class, 'destroy'])->name('nationality.delete')->middleware('role_or_permission:delete nationalities');
        Route::get('/shows/{id}', [NationalityController::class, 'show'])->name('nationality.shows')->middleware('role_or_permission:view nationalities');
    });

    // units
    Route::get('/units', [UnitController::class, 'index'])->name('units.list')->middleware('role_or_permission:view units');
    Route::group(['prefix' => 'unit'], function () {
        Route::post('store', [UnitController::class, 'store'])->name('unit.store')->middleware('role_or_permission:create units');
        Route::put('update/{id}', [UnitController::class, 'update'])->name('unit.update')->middleware('role_or_permission:update units');
        Route::delete('delete/{id}', [UnitController::class, 'delete'])->name('unit.delete')->middleware('role_or_permission:delete units');
    });

    // colors
    Route::get('/colors', [ColorController::class, 'index'])->name('colors.list')->middleware('role_or_permission:view colors');
    Route::group(['prefix' => 'color'], function () {
        Route::post('store', [ColorController::class, 'store'])->name('color.store')->middleware('role_or_permission:create colors');
        Route::put('update/{id}', [ColorController::class, 'update'])->name('color.update')->middleware('role_or_permission:update colors');
        Route::delete('delete/{id}', [ColorController::class, 'delete'])->name('color.delete')->middleware('role_or_permission:delete colors');
    });

    // hotels
    Route::get('/hotels', [HotelController::class, 'index'])->name('hotels.list')->middleware('role_or_permission:view hotels');
    Route::get('/city/hotel/{coutry_id}', [HotelController::class, 'city'])->name('hotels.city');
    Route::get('/region/hotel/{coutry_id}', [HotelController::class, 'region'])->name('hotels.region');
    Route::group(['prefix' => 'hotel'], function () {
        Route::post('store', [HotelController::class, 'store'])->name('hotel.store')->middleware('role_or_permission:create hotels');
        Route::put('update/{id}', [HotelController::class, 'update'])->name('hotel.update')->middleware('role_or_permission:update hotels');
        Route::delete('delete/{id}', [HotelController::class, 'delete'])->name('hotel.delete')->middleware('role_or_permission:delete hotels');
    });

    // sizes
    Route::get('/sizes', [SizeController::class, 'index'])->name('sizes.list')->middleware('role_or_permission:view sizes');
    Route::group(['prefix' => 'size'], function () {
        Route::post('store', [SizeController::class, 'store'])->name('size.store')->middleware('role_or_permission:create sizes');
        Route::put('update/{id}', [SizeController::class, 'update'])->name('size.update')->middleware('role_or_permission:update sizes');
        Route::delete('delete/{id}', [SizeController::class, 'delete'])->name('size.delete')->middleware('role_or_permission:delete sizes');
    });

    // branches
    Route::get('/branches', [BranchController::class, 'index'])->name('branches.list')->middleware('role_or_permission:view branches');
    Route::group(['prefix' => 'branch'], function () {
        Route::get('create', [BranchController::class, 'create'])->name('branch.create')->middleware('role_or_permission:create branches');
        Route::post('store', [BranchController::class, 'store'])->name('branch.store')->middleware('role_or_permission:create branches');
        Route::get('show/{id}', [BranchController::class, 'show'])->name('branch.show')->middleware('role_or_permission:view branches');
        Route::get('sync/{id}', [BranchController::class, 'sync'])->name('branch.sync')->middleware('role_or_permission:view branches');
        Route::get('edit/{id}', [BranchController::class, 'edit'])->name('branch.edit')->middleware('role_or_permission:update branches');
        Route::put('update/{id}', [BranchController::class, 'update'])->name('branch.update')->middleware('role_or_permission:update branches');
        Route::delete('delete/{id}', [BranchController::class, 'delete'])->name('branch.delete')->middleware('role_or_permission:delete branches');
        Route::get('changeStatus/{id}', [BranchController::class, 'change_status'])->name('branch.changeStatus')->middleware('role_or_permission:update branches');
        Route::get('orders/{id}', [BranchController::class, 'orders'])->name('branch.orders')->middleware('role_or_permission:view branches');
        Route::get('ordersStatus/{id}/{status}', [BranchController::class, 'orders_status'])->name('branch.ordersStatus')->middleware('role_or_permission:view branches');
        Route::get('showBranchRegion/{city_id}/{branch_id}', [BranchController::class, 'show_branch_region'])->name('branch.showBranchRegion')->middleware('role_or_permission:view branches');
        Route::get('get-branches-by-company/{id}', [BranchController::class, 'getBranchesByCompany'])->name('company.branches');

        //Route::get('/categories', [BranchMenuCategoryController::class, 'index'])->name('branch.categories.list')->middleware('role_or_permission:view branch_menu_categories');
        Route::get('/categories', [BranchMenuCategoryController::class, 'index'])->name('branch.categories.list')->middleware('role_or_permission:view branch_menu_categories');
        Route::get('/categories', [BranchMenuCategoryController::class, 'index'])->name('branch.categories.list')->middleware('role_or_permission:view branch_menu_categories');
        Route::group(['prefix' => 'categories'], function () {
            Route::get('show/{id}', [BranchMenuCategoryController::class, 'show'])->name('branch.categories.show')->middleware('role_or_permission:view branch_menu_categories');
            Route::put('update/{id}', [BranchMenuCategoryController::class, 'update'])->name('branch.categories.update')->middleware('role_or_permission:update branch_menu_categories');
            Route::get('showAll/{branch_id}', [BranchMenuCategoryController::class, 'show_branch'])->name('branch.categories.show.all')->middleware('role_or_permission:view branch_menu_categories');
            Route::get('changeStatus/{id}', [BranchMenuCategoryController::class, 'change_status'])->name('branch.categories.changeStatus')->middleware('role_or_permission:update branch_menu_categories');
        });

        //Route::get('/menus', [BranchMenuController ::class, 'index'])->name('branch.menus.list')->middleware('role_or_permission:view branch_menus');
        Route::get('/menus', [BranchMenuController::class, 'index'])->name('branch.menus.list')->middleware('role_or_permission:view branch_menus');
        Route::get('/menus', [BranchMenuController::class, 'index'])->name('branch.menus.list')->middleware('role_or_permission:view branch_menus');
        Route::group(['prefix' => 'menus'], function () {
            Route::get('show/{id}', [BranchMenuController::class, 'show'])->name('branch.menus.show')->middleware('role_or_permission:view branch_menus');
            Route::put('update/{id}', [BranchMenuController::class, 'update'])->name('branch.menus.update')->middleware('role_or_permission:update branch_menus');
            Route::get('showAll/{branch_id}', [BranchMenuController::class, 'show_branch'])->name('branch.menus.show.all')->middleware('role_or_permission:view branch_menus');
            Route::get('changeStatus/{id}', [BranchMenuController::class, 'change_status'])->name('branch.menus.changeStatus')->middleware('role_or_permission:update branch_menus');
        });

        //Route::get('/menu/addons/categories', [BranchMenuAddonCategoryController::class, 'index'])->name('branch.menu.addons.categories.list')->middleware('role_or_permission:view branch_menu_category_addons');
        Route::get('/menu/addons/categories', [BranchMenuAddonCategoryController::class, 'index'])->name('branch.menu.addons.categories.list')->middleware('role_or_permission:view branch_menu_category_addons');
        Route::get('/menu/addons/categories', [BranchMenuAddonCategoryController::class, 'index'])->name('branch.menu.addons.categories.list')->middleware('role_or_permission:view branch_menu_category_addons');
        Route::group(['prefix' => 'menu/addon/categories'], function () {
            Route::get('show/{id}', [BranchMenuAddonCategoryController::class, 'show'])->name('branch.menu.addon.category.show')->middleware('role_or_permission:view branch_menu_category_addons');
            Route::put('update/{id}', [BranchMenuAddonCategoryController::class, 'update'])->name('branch.menu.addon.category.update')->middleware('role_or_permission:update branch_menu_category_addons');
            Route::get('showAll/{branch_id}', [BranchMenuAddonCategoryController::class, 'show_branch'])->name('branch.menu.addon.category.show.all')->middleware('role_or_permission:view branch_menu_category_addons');
            Route::get('changeStatus/{id}', [BranchMenuAddonCategoryController::class, 'change_status'])->name('branch.menu.addon.category.changeStatus')->middleware('role_or_permission:changestatus branch_menu_category_addons');
        });

        //Route::get('/menu/addons', [BranchMenuAddonController::class, 'index'])->name('branch.menu.addons.list')->middleware('role_or_permission:view branch_menu_addons');
        Route::get('/menu/addons', [BranchMenuAddonController::class, 'index'])->name('branch.menu.addons.list')->middleware('role_or_permission:view branch_menu_addons');
        Route::get('/menu/addons', [BranchMenuAddonController::class, 'index'])->name('branch.menu.addons.list')->middleware('role_or_permission:view branch_menu_addons');
        Route::group(['prefix' => 'menu/addons'], function () {
            Route::get('show/{id}', [BranchMenuAddonController::class, 'show'])->name('branch.menu.addons.show')->middleware('role_or_permission:view branch_menu_addons');
            Route::put('update/{id}', [BranchMenuAddonController::class, 'update'])->name('branch.menu.addons.update')->middleware('role_or_permission:update branch_menu_addons');
            Route::get('showAll/{branch_id}', [BranchMenuAddonController::class, 'show_branch'])->name('branch.menu.addons.show.all')->middleware('role_or_permission:view branch_menu_addons');
            Route::get('changeStatus/{id}', [BranchMenuAddonController::class, 'change_status'])->name('branch.menu.addons.changeStatus')->middleware('role_or_permission:update branch_menu_addons');
        });

        // Route::get('/menu/sizes', [BranchMenuSizeController::class, 'index'])->name('branch.menu.sizes.list')->middleware('role_or_permission:view branch_menu_sizes');
        Route::get('/menu/sizes', [BranchMenuSizeController::class, 'index'])->name('branch.menu.sizes.list')->middleware('role_or_permission:view branch_menu_sizes');
        Route::get('/menu/sizes', [BranchMenuSizeController::class, 'index'])->name('branch.menu.sizes.list')->middleware('role_or_permission:view branch_menu_sizes');
        Route::group(['prefix' => 'menu/sizes'], function () {
            Route::get('show/{id}', [BranchMenuSizeController::class, 'show'])->name('branch.menu.sizes.show')->middleware('role_or_permission:view branch_menu_sizes');
            Route::put('update/{id}', [BranchMenuSizeController::class, 'update'])->name('branch.menu.sizes.update')->middleware('role_or_permission:update branch_menu_sizes');
            Route::get('showAll/{branch_id}', [BranchMenuSizeController::class, 'show_branch'])->name('branch.menu.sizes.show.all')->middleware('role_or_permission:view branch_menu_sizes');
            Route::get('changeStatus/{id}', [BranchMenuSizeController::class, 'change_status'])->name('branch.menu.sizes.changeStatus')->middleware('role_or_permission:update branch_menu_sizes');
        });
    });

    // brands
    Route::get('/brands', [BrandController::class, 'index'])->name('brands.list')->middleware('role_or_permission:view brands');
    Route::group(['prefix' => 'brand'], function () {
        Route::get('create', [BrandController::class, 'create'])->name('brand.create')->middleware('role_or_permission:create brands');
        Route::post('store', [BrandController::class, 'store'])->name('brand.store')->middleware('role_or_permission:create brands');
        Route::get('show/{id}', [BrandController::class, 'show'])->name('brand.show')->middleware('role_or_permission:view brands');
        Route::get('edit/{id}', [BrandController::class, 'edit'])->name('brand.edit')->middleware('role_or_permission:update brands');
        Route::put('update/{id}', [BrandController::class, 'update'])->name('brand.update')->middleware('role_or_permission:update brands');
        Route::delete('delete/{id}', [BrandController::class, 'delete'])->name('brand.delete')->middleware('role_or_permission:delete brands');
    });

    // floors
    Route::get('/floors', [FloorController::class, 'index'])->name('floors.list')->middleware('role_or_permission:view floors');
    Route::group(['prefix' => 'floor'], function () {
        Route::post('store', [FloorController::class, 'store'])->name('floor.store')->middleware('role_or_permission:create floors');
        Route::get('show/{id}', [FloorController::class, 'show'])->name('floor.show')->middleware('role_or_permission:view floors');
        Route::put('update/{id}', [FloorController::class, 'update'])->name('floor.update')->middleware('role_or_permission:update floors');
        Route::delete('delete/{id}', [FloorController::class, 'delete'])->name('floor.delete')->middleware('role_or_permission:delete floors');
        Route::get('branch/{branch_id}', [FloorController::class, 'branch'])->name('floor.branch')->middleware('role_or_permission:view floors');
    });

    // floor-partitions
    Route::get('/branch/floors', [FloorPartitionController::class, 'fetchFloorsForBranch'])->name('filterfloor');

    Route::get('/floor-partitions', [FloorPartitionController::class, 'index'])->name('floorPartitions.list')->middleware('role_or_permission:view floor_partitions');
    Route::group(['prefix' => 'floor-partition'], function () {
        Route::post('store', [FloorPartitionController::class, 'store'])->name('floorPartition.store')->middleware('role_or_permission:create floor_partitions');
        Route::get('show/{id}', [FloorPartitionController::class, 'show'])->name('floorPartition.show')->middleware('role_or_permission:view floor_partitions');
        Route::get('show_data/{id}', [FloorPartitionController::class, 'show_data'])->name('floorPartition.show_data')->middleware('role_or_permission:view floor_partitions');
        Route::put('update/{id}', [FloorPartitionController::class, 'update'])->name('floorPartition.update')->middleware('role_or_permission:update floor_partitions');
        Route::delete('delete/{id}', [FloorPartitionController::class, 'delete'])->name('floorPartition.delete')->middleware('role_or_permission:delete floor_partitions');
        Route::get('showAll/{floor_id}', [FloorPartitionController::class, 'show_all'])->name('floorPartition.show.all')->middleware('role_or_permission:view floor_partitions');
    });

    // tables
    Route::get('/tables', [TableController::class, 'index'])->name('tables.list')->middleware('role_or_permission:view tables');
    Route::group(['prefix' => 'table'], function () {
        Route::post('store', [TableController::class, 'store'])->name('table.store')->middleware('role_or_permission:create tables');
        Route::get('show/{id}', [TableController::class, 'show'])->name('table.show')->middleware('role_or_permission:view tables');
        Route::put('update/{id}', [TableController::class, 'update'])->name('table.update')->middleware('role_or_permission:update tables');
        Route::delete('delete/{id}', [TableController::class, 'delete'])->name('table.delete')->middleware('role_or_permission:delete tables');
        Route::get('showAll/{floor_id}/{type}', [TableController::class, 'show_all'])->name('table.show.all')->middleware('role_or_permission:view tables');
    });

    // leave-types
    Route::get('/leave-types', [LeaveTypeController::class, 'index'])->name('leave-types.list')->middleware('role_or_permission:view leave_types');
    Route::group(['prefix' => 'leave-type'], function () {
        Route::post('store', [LeaveTypeController::class, 'store'])->name('leave-type.store')->middleware('role_or_permission:create leave_types');
        Route::get('show/{id}', [LeaveTypeController::class, 'show'])->name('leave-type.show')->middleware('role_or_permission:view leave_types');
        Route::put('update/{id}', [LeaveTypeController::class, 'update'])->name('leave-type.update')->middleware('role_or_permission:update leave_types');
        Route::delete('delete/{id}', [LeaveTypeController::class, 'delete'])->name('leave-type.delete')->middleware('role_or_permission:delete leave_types');
    });

    // leave-settings
    Route::get('/leave-settings', [LeaveSettingController::class, 'index'])->name('leave-settings.list')->middleware('role_or_permission:view leave_settings');
    Route::group(['prefix' => 'leave-setting'], function () {
        Route::post('store', [LeaveSettingController::class, 'store'])->name('leave-setting.store')->middleware('role_or_permission:create leave_settings');
        Route::get('show/{id}', [LeaveSettingController::class, 'show'])->name('leave-setting.show')->middleware('role_or_permission:view leave_settings');
        Route::put('update/{id}', [LeaveSettingController::class, 'update'])->name('leave-setting.update')->middleware('role_or_permission:update leave_settings');
        Route::delete('delete/{id}', [LeaveSettingController::class, 'delete'])->name('leave-setting.delete')->middleware('role_or_permission:delete leave_settings');
        Route::get('show_data/{column_name}/{column_val}', [LeaveSettingController::class, 'show_data'])->name('leave-setting.show_data')->middleware('role_or_permission:view leave_settings');
    });

    // leave-setting-positions
    Route::get('/leave-setting-positions', [LeaveSettingPositionController::class, 'index'])->name('leave-setting-positions.list')->middleware('role_or_permission:view leave_setting_positions');
    Route::group(['prefix' => 'leave-setting-positions'], function () {
        Route::post('store', [LeaveSettingPositionController::class, 'store'])->name('leave-setting-positions.store')->middleware('role_or_permission:create leave_setting_positions');
        Route::get('show/{id}', [LeaveSettingPositionController::class, 'show'])->name('leave-setting-positions.show')->middleware('role_or_permission:view leave_setting_positions');
        Route::put('update/{id}', [LeaveSettingPositionController::class, 'update'])->name('leave-setting-positions.update')->middleware('role_or_permission:update leave_setting_positions');
        Route::delete('delete/{id}', [LeaveSettingPositionController::class, 'delete'])->name('leave-setting-positions.delete')->middleware('role_or_permission:delete leave_setting_positions');
    });

    // clients
    Route::get('/clients', [ClientController::class, 'index'])->name('client.index')->middleware('role_or_permission:view client_details');
    Route::group(['prefix' => 'client'], function () {
        Route::get('create', [ClientController::class, 'create'])->name('client.create')->middleware('role_or_permission:create client_details');
        Route::post('store', [ClientController::class, 'store'])->name('client.store')->middleware('role_or_permission:create client_details');
        Route::get('show/{id}', [ClientController::class, 'show'])->name('client.show')->middleware('role_or_permission:view client_details');
        Route::get('edit/{id}', [ClientController::class, 'edit'])->name('client.edit')->middleware('role_or_permission:update client_details');
        Route::put('update/{id}', [ClientController::class, 'update'])->name('client.update')->middleware('role_or_permission:update client_details');
        Route::delete('delete/{id}', [ClientController::class, 'destroy'])->name('client.delete')->middleware('role_or_permission:delete client_details');
    });

    //website setting
    Route::get('/logos', [LogoController::class, 'index'])->name('logos.list')->middleware('role_or_permission:view logos');
    Route::group(['prefix' => 'logo'], function () {
        Route::post('store', [LogoController::class, 'store'])->name('logo.store')->middleware('role_or_permission:create logos');
        Route::get('show/{id}', [LogoController::class, 'show'])->name('logo.show')->middleware('role_or_permission:view logos');
        Route::put('update/{id}', [LogoController::class, 'update'])->name('logo.update')->middleware('role_or_permission:update logos');
        Route::delete('delete/{id}', [LogoController::class, 'destroy'])->name('logo.delete')->middleware('role_or_permission:delete logos');
    });

    Route::get('/sliders', [SliderController::class, 'index'])->name('sliders.list')->middleware('role_or_permission:view sliders');
    Route::group(['prefix' => 'slider'], function () {
        Route::get('create', [SliderController::class, 'create'])->name('slider.create')->middleware('role_or_permission:create sliders');
        Route::post('store', [SliderController::class, 'store'])->name('slider.store')->middleware('role_or_permission:create sliders');
        Route::get('show/{id}', [SliderController::class, 'show'])->name('slider.show')->middleware('role_or_permission:view sliders');
        Route::get('edit/{id}', [SliderController::class, 'edit'])->name('slider.edit')->middleware('role_or_permission:update sliders');
        Route::put('update/{id}', [SliderController::class, 'update'])->name('slider.update')->middleware('role_or_permission:update sliders');
        Route::delete('delete/{id}', [SliderController::class, 'destroy'])->name('slider.delete')->middleware('role_or_permission:delete sliders');
    });

    Route::get('/rates', [RateOldController::class, 'index'])->name('rates.list')->middleware('role_or_permission:view rates');
    Route::group(['prefix' => 'rate'], function () {
        //    Route::post('store', [RateOldController::class, 'store'])->name('rate.store')->middleware('role_or_permission:create rates');
        Route::get('show/{id}', [RateOldController::class, 'show'])->name('rate.show')->middleware('role_or_permission:view rates');
        Route::put('update/{id}', [RateOldController::class, 'update'])->name('rate.update')->middleware('role_or_permission:update rates');
        Route::delete('delete/{id}', [RateOldController::class, 'destroy'])->name('rate.delete')->middleware('role_or_permission:delete rates');
    });

    Route::get('/terms', [TermsAndConditionsController::class, 'index'])->name('terms.list')->middleware('role_or_permission:view terms');
    Route::group(['prefix' => 'term'], function () {
        Route::get('create', [TermsAndConditionsController::class, 'create'])->name('term.create')->middleware('role_or_permission:create terms');
        Route::post('store', [TermsAndConditionsController::class, 'store'])->name('term.store')->middleware('role_or_permission:create terms');
        Route::get('show/{id}', [TermsAndConditionsController::class, 'show'])->name('term.show')->middleware('role_or_permission:view terms');
        Route::get('edit/{id}', [TermsAndConditionsController::class, 'edit'])->name('term.edit')->middleware('role_or_permission:update terms');
        Route::put('update/{id}', [TermsAndConditionsController::class, 'update'])->name('term.update')->middleware('role_or_permission:update terms');
        Route::delete('delete/{id}', [TermsAndConditionsController::class, 'destroy'])->name('term.delete')->middleware('role_or_permission:delete terms');
    });

    Route::get('/privacies', [PrivacyPolicyController::class, 'index'])->name('privacies.list')->middleware('role_or_permission:view privacies');
    Route::group(['prefix' => 'privacy'], function () {
        Route::get('create', [PrivacyPolicyController::class, 'create'])->name('privacy.create')->middleware('role_or_permission:create privacies');
        Route::post('store', [PrivacyPolicyController::class, 'store'])->name('privacy.store')->middleware('role_or_permission:create privacies');
        Route::get('show/{id}', [PrivacyPolicyController::class, 'show'])->name('privacy.show')->middleware('role_or_permission:view privacies');
        Route::get('edit/{id}', [PrivacyPolicyController::class, 'edit'])->name('privacy.edit')->middleware('role_or_permission:update privacies');
        Route::put('update/{id}', [PrivacyPolicyController::class, 'update'])->name('privacy.update')->middleware('role_or_permission:update privacies');
        Route::delete('delete/{id}', [PrivacyPolicyController::class, 'destroy'])->name('privacy.delete')->middleware('role_or_permission:delete privacies');
    });

    Route::get('/returns', [ReturnPolicyController::class, 'index'])->name('returns.list')->middleware('role_or_permission:view returns');
    Route::group(['prefix' => 'return'], function () {
        Route::get('create', [ReturnPolicyController::class, 'create'])->name('return.create')->middleware('role_or_permission:create returns');
        Route::post('store', [ReturnPolicyController::class, 'store'])->name('return.store')->middleware('role_or_permission:create returns');
        Route::get('show/{id}', [ReturnPolicyController::class, 'show'])->name('return.show')->middleware('role_or_permission:view returns');
        Route::get('edit/{id}', [ReturnPolicyController::class, 'edit'])->name('return.edit')->middleware('role_or_permission:update returns');
        Route::put('update/{id}', [ReturnPolicyController::class, 'update'])->name('return.update')->middleware('role_or_permission:update returns');
        Route::delete('delete/{id}', [ReturnPolicyController::class, 'destroy'])->name('return.delete')->middleware('role_or_permission:delete returns');
    });

    Route::get('/faqs', [FAQController::class, 'index'])->name('faqs.list')->middleware('role_or_permission:view faqs');
    Route::group(['prefix' => 'faq'], function () {
        Route::get('create', [FAQController::class, 'create'])->name('faq.create')->middleware('role_or_permission:create faqs');
        Route::post('store', [FAQController::class, 'store'])->name('faq.store')->middleware('role_or_permission:create faqs');
        Route::get('show/{id}', [FAQController::class, 'show'])->name('faq.show')->middleware('role_or_permission:view faqs');
        Route::get('edit/{id}', [FAQController::class, 'edit'])->name('faq.edit')->middleware('role_or_permission:update faqs');
        Route::put('update/{id}', [FAQController::class, 'update'])->name('faq.update')->middleware('role_or_permission:update faqs');
        Route::delete('delete/{id}', [FAQController::class, 'destroy'])->name('faq.delete')->middleware('role_or_permission:delete faqs');
    });

    Route::get('/currencies', [CurrencyController::class, 'index'])->name('currencies.list')->middleware('role_or_permission:view currencies');
    Route::group(['prefix' => 'currency'], function () {
        Route::post('store', [CurrencyController::class, 'store'])->name('currency.store')->middleware('role_or_permission:create currencies');
        Route::get('show/{id}', [CurrencyController::class, 'show'])->name('currency.show')->middleware('role_or_permission:view currencies');
        Route::put('update/{id}', [CurrencyController::class, 'update'])->name('currency.update')->middleware('role_or_permission:update currencies');
        Route::delete('delete/{id}', [CurrencyController::class, 'destroy'])->name('currency.delete')->middleware('role_or_permission:delete currencies');
    });

    Route::get('/complaints', [ComplaintsController::class, 'index'])->name('complaints.list')->middleware('role_or_permission:view complaints');
    Route::group(['prefix' => 'complaints'], function () {
        Route::get('show/{id}', [ComplaintsController::class, 'show'])->name('complaint.show')->middleware('role_or_permission:view complaints');
        Route::get('edit/{id}', [ComplaintsController::class, 'edit'])->name('complaint.edit')->middleware('role_or_permission:update complaints');
        Route::put('update/{id}', [ComplaintsController::class, 'changeStatus'])->name('complaint.update')->middleware('role_or_permission:update complaints');
        Route::delete('delete/{id}', [ComplaintsController::class, 'delete'])->name('complaint.delete')->middleware('role_or_permission:delete complaints');
        Route::get('/notifications/{id}/mark-as-read/{user_id}', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    });

    // coupons
    Route::get('/coupons', [CouponController::class, 'index'])->name('coupons.list')->middleware('role_or_permission:view coupons');
    Route::group(['prefix' => 'coupon'], function () {
        Route::get('create', [CouponController::class, 'create'])->name('coupon.create')->middleware('role_or_permission:create coupons');
        Route::post('store', [CouponController::class, 'store'])->name('coupon.store')->middleware('role_or_permission:create coupons');
        Route::get('show/{id}', [CouponController::class, 'show'])->name('coupon.show')->middleware('role_or_permission:view coupons');
        Route::get('edit/{id}', [CouponController::class, 'edit'])->name('coupon.edit')->middleware('role_or_permission:update coupons');
        Route::put('update/{id}', [CouponController::class, 'update'])->name('coupon.update')->middleware('role_or_permission:update coupons');
        Route::delete('delete/{id}', [CouponController::class, 'delete'])->name('coupon.delete')->middleware('role_or_permission:delete coupons');
        Route::get('/branches/{branch_id}/categories', [CouponController::class, 'getBranchCategories'])->name('branches.categories.filterget');
        Route::get('/categories/{category_id}/dishes/{branch_id}', [CouponController::class, 'getCategoryDishes'])->name('categories.dishes');
    });

    // discounts
    Route::get('/discounts', [DiscountController::class, 'index'])->name('discounts.list')->middleware('role_or_permission:view discounts');
    Route::group(['prefix' => 'discount'], function () {
        Route::get('create', [DiscountController::class, 'create'])->name('discount.create')->middleware('role_or_permission:create discounts');
        Route::post('store', [DiscountController::class, 'store'])->name('discount.store')->middleware('role_or_permission:create discounts');
        Route::get('show/{id}', [DiscountController::class, 'show'])->name('discount.show')->middleware('role_or_permission:view discounts');
        Route::get('edit/{id}', [DiscountController::class, 'edit'])->name('discount.edit')->middleware('role_or_permission:update discounts');
        Route::put('update/{id}', [DiscountController::class, 'update'])->name('discount.update')->middleware('role_or_permission:update discounts');
        Route::delete('delete/{id}', [DiscountController::class, 'delete'])->name('discount.delete')->middleware('role_or_permission:delete discounts');
    });

    // discount_dish
    Route::get('/discounts/dish/list/{discountId}', [DiscountController::class, 'dish'])
        ->name('discounts.dishes.list')
        ->middleware('role_or_permission:view discount_dishes');

    Route::post('discount/{id}/dishes/save', [DiscountController::class, 'saveDishes'])
        ->name('discount.dishes.save')
        ->middleware('role_or_permission:view discount_dishes');



    // gifts
    Route::get('/gifts', [GiftController::class, 'index'])->name('gifts.list')->middleware('role_or_permission:view gifts');
    Route::group(['prefix' => 'gift'], function () {
        Route::post('store', [GiftController::class, 'store'])->name('gift.store')->middleware('role_or_permission:create gifts');
        Route::put('update/{id}', [GiftController::class, 'update'])->name('gift.update')->middleware('role_or_permission:update gifts');
        Route::delete('delete/{id}', [GiftController::class, 'delete'])->name('gift.delete')->middleware('role_or_permission:delete gifts');
    });
    // offers
    Route::get('/offers', [OfferController::class, 'index'])->name('offers.list')->middleware('role_or_permission:view offers');
    Route::group(['prefix' => 'offer'], function () {
        Route::get('create', [OfferController::class, 'create'])->name('offer.create')->middleware('role_or_permission:create offers');
        Route::post('store', [OfferController::class, 'store'])->name('offer.store')->middleware('role_or_permission:create offers');
        Route::get('show/{id}', [OfferController::class, 'show'])->name('offer.show')->middleware('role_or_permission:view offers');
        Route::get('edit/{id}', [OfferController::class, 'edit'])->name('offer.edit')->middleware('role_or_permission:update offers');
        Route::put('update/{id}', [OfferController::class, 'update'])->name('offer.update')->middleware('role_or_permission:update offers');
        Route::delete('delete/{id}', [OfferController::class, 'destroy'])->name('offer.delete')->middleware('role_or_permission:delete offers');
    });

    // offer-details
    Route::get('/offer-details/{id}', [OfferDetailController::class, 'index'])->name('offerDetails.list')->middleware('role_or_permission:view offerDetails');
    Route::group(['prefix' => 'offer-detail'], function () {
        Route::get('create', [OfferDetailController::class, 'create'])->name('offerDetail.create')->middleware('role_or_permission:create offerDetails');
        Route::post('store', [OfferDetailController::class, 'store'])->name('offerDetail.store')->middleware('role_or_permission:create offerDetails');
        Route::get('show/{id}', [OfferDetailController::class, 'show'])->name('offerDetail.show')->middleware('role_or_permission:view offerDetails');
        Route::get('edit/{id}', [OfferDetailController::class, 'edit'])->name('offerDetail.edit')->middleware('role_or_permission:update offerDetails');
        Route::put('update/{id}', [OfferDetailController::class, 'update'])->name('offerDetail.update')->middleware('role_or_permission:update offerDetails');
        Route::delete('delete/{id}', [OfferDetailController::class, 'destroy'])->name('offerDetail.delete')->middleware('role_or_permission:delete offerDetails');
    });

    //positions
    Route::get('/positions', [PositionController::class, 'index'])->name('positions.index')->middleware('role_or_permission:view positions');
    Route::group(['prefix' => 'position'], function () {
        Route::post('store', [PositionController::class, 'store'])->name('position.store')->middleware('role_or_permission:create positions');
        Route::put('update/{id}', [PositionController::class, 'update'])->name('position.update')->middleware('role_or_permission:update positions');
        Route::delete('delete/{id}', [PositionController::class, 'destroy'])->name('position.delete')->middleware('role_or_permission:delete positions');
        Route::group(['prefix' => 'applications'], function () {
            Route::get('create/{id}', [ApplicationController::class, 'create'])->name('position.applications.create')->middleware('role_or_permission:create applications positions');
            Route::post('store', [ApplicationController::class, 'store'])->name('position.applications.store')->middleware('role_or_permission:create applications positions');
            Route::get('edit/{id}', [ApplicationController::class, 'edit'])->name('position.applications.edit')->middleware('role_or_permission:update applications positions');
            Route::put('update/{id}', [ApplicationController::class, 'update'])->name('position.applications.update')->middleware('role_or_permission:update applications positions');
            Route::get('viewAnswers/{id}', [ApplicationController::class, 'viewAnswers'])->name('position.applications.answers')->middleware('role_or_permission:view applications answers');
            Route::get('viewAnswer/{id}', [ApplicationController::class, 'viewAnswer'])->name('position.applications.answer')->middleware('role_or_permission:view applications answers');
            Route::put('applications/{application}/status', [ApplicationController::class, 'updateStatus'])
                ->name('applications.updateStatus')->middleware('role_or_permission:update applications answers');
            // Route::post('{id}/submit', [ApplicationController::class, 'submit'])->name('position.applications.submit');
        });
    });

    //payment-types
    Route::get('/payment-types', [PaymentTypeController::class, 'index'])->name('payment_types.list')->middleware('role_or_permission:view payment_types');
    Route::group(['prefix' => 'payment-type'], function () {
        Route::post('store', [PaymentTypeController::class, 'store'])->name('payment_type.store')->middleware('role_or_permission:create payment_types');
        Route::get('show/{id}', [PaymentTypeController::class, 'show'])->name('payment_type.show')->middleware('role_or_permission:view payment_types');
        Route::put('update/{id}', [PaymentTypeController::class, 'update'])->name('payment_type.update')->middleware('role_or_permission:update payment_types');
        Route::delete('delete/{id}', [PaymentTypeController::class, 'destroy'])->name('payment_type.delete')->middleware('role_or_permission:delete payment_types');
    });

    //payment-frequencies
    Route::get('/payment-frequencies', [PaymentFrequencyController::class, 'index'])->name('payment_frequencies.list')->middleware('role_or_permission:view payment_frequencies');
    Route::group(['prefix' => 'payment-frequency'], function () {
        // Route::post('store', [PaymentFrequencyController::class, 'store'])->name('payment_frequency.store')->middleware('role_or_permission:create payment_frequencies');
        Route::get('show/{id}', [PaymentFrequencyController::class, 'show'])->name('payment_frequency.show')->middleware('role_or_permission:view payment_frequencies');
        // Route::put('update/{id}', [PaymentFrequencyController::class, 'update'])->name('payment_frequency.update')->middleware('role_or_permission:update payment_frequencies');
        // Route::delete('delete/{id}', [PaymentFrequencyController::class, 'destroy'])->name('payment_frequency.delete')->middleware('role_or_permission:delete payment_frequencies');
    });

    //employees
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.list')->middleware('role_or_permission:view employees');
    Route::group(['prefix' => 'employee'], function () {
        Route::get('create', [EmployeeController::class, 'create'])->name('employee.create')->middleware('role_or_permission:create employees');
        Route::post('store', [EmployeeController::class, 'store'])->name('employee.store')->middleware('role_or_permission:create employees');
        Route::get('show/{id}', [EmployeeController::class, 'show'])->name('employee.show')->middleware('role_or_permission:view employees');
        Route::get('edit/{id}', [EmployeeController::class, 'edit'])->name('employee.edit')->middleware('role_or_permission:update employees');
        Route::put('update/{id}', [EmployeeController::class, 'update'])->name('employee.update')->middleware('role_or_permission:update employees');
        Route::delete('delete/{id}', [EmployeeController::class, 'destroy'])->name('employee.delete')->middleware('role_or_permission:delete employees');
        Route::post('import', [EmployeeController::class, 'import'])->name('employee.import')->middleware('role_or_permission:create employees');
        Route::get('/positions/{department}', [EmployeeController::class, 'getPositionsByDepartment'])->name('positions.byDepartment');
        Route::get('/fetch-supervisors', [EmployeeController::class, 'fetchSupervisors'])->name('fetch.supervisors');
        Route::post('/hierarchies', [EmployeeController::class, 'getHierarchies'])->name('filter.hierarchies');
        Route::get('/hierarchies', [EmployeeController::class, 'getHierarchies'])->name('employee.hierarchies');
        Route::get('/universities/by-country/{country}', [EmployeeController::class, 'getByCountry'])->name('universities.by.country');
    });
    Route::post('/employees/import', [EmployeeController::class, 'importExcel'])->name('employees.import');

    //departments
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.list')->middleware('role_or_permission:view departments');
    Route::group(['prefix' => 'department'], function () {
        Route::post('store', [DepartmentController::class, 'store'])->name('department.store')->middleware('role_or_permission:create departments');
        Route::put('update/{id}', [DepartmentController::class, 'update'])->name('department.update')->middleware('role_or_permission:update departments');
        Route::delete('delete/{id}', [DepartmentController::class, 'destroy'])->name('department.delete')->middleware('role_or_permission:delete departments');
        Route::get('get-subdepartments/{departmentId}', [DepartmentController::class, 'getSubDepartments'])->name('subdepartment.get');
        Route::get('/positions/{id}', [DepartmentController::class, 'getPositions'])->name('department.positions');
        Route::get('/branch/{id}', [DepartmentController::class, 'getDepartmentsByBranch'])->name('branch.department');
    });

    //roles
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.list')->middleware('role_or_permission:view roles');
    Route::group(['prefix' => 'role'], function () {
        Route::get('/show/{id}', [RoleController::class, 'show'])->name('role.show')->middleware('role_or_permission:view roles');
        Route::get('/create', [RoleController::class, 'create'])->name('role.create')->middleware('role_or_permission:create roles');
        Route::post('store', [RoleController::class, 'store'])->name('role.store')->middleware('role_or_permission:create roles');
        Route::get('/edit/{id}', [RoleController::class, 'edit'])->name('role.edit')->middleware('role_or_permission:update roles');
        Route::put('update/{id}', [RoleController::class, 'update'])->name('role.update')->middleware('role_or_permission:update roles');
        Route::delete('delete/{id}', [RoleController::class, 'destroy'])->name('role.delete')->middleware('role_or_permission:delete roles');
    });

    //permissions
    Route::group(['prefix' => 'permissions'], function () {
        Route::get('/', [PermissionController::class, 'index'])->name('permissions.list')->middleware('role_or_permission:view permissions');
        Route::get('/show/{id}', [PermissionController::class, 'show'])->name('permission.show')->middleware('role_or_permission:view permissions');
        Route::get('/create', [PermissionController::class, 'create'])->name('permission.create')->middleware('role_or_permission:create permissions');
        Route::post('store', [PermissionController::class, 'store'])->name('permission.store')->middleware('role_or_permission:create permissions');
        Route::get('{id}/edit', [PermissionController::class, 'edit'])->name('permissions.edit')->middleware('role_or_permission:update permissions');
        Route::put('update', [PermissionController::class, 'update'])->name('permission.update')->middleware('role_or_permission:update permissions');
        Route::get('delete/{id}', [PermissionController::class, 'destroy'])->name('permission.delete')->middleware('role_or_permission:delete permissions');
    });

    //orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.list')->middleware('role_or_permission:view orders');
    Route::get('/order/store', [OrderController::class, 'store'])->name('order.store')->middleware('role_or_permission:create orders');
    Route::get('/order/add', [OrderController::class, 'create'])->name('order.add')->middleware('role_or_permission:create orders');
    Route::get('/order/show/{id}', [OrderController::class, 'show'])->name('order.show')->middleware('role_or_permission:view orders');
    Route::post('/order/change', [OrderController::class, 'changeStatus'])->name('order.change')->middleware('role_or_permission:change status orders');
    Route::post('/order-addon/change', [OrderController::class, 'changeAddonStatus'])->name('order.addon.change')->middleware('role_or_permission:view orders');
    Route::get('/order/download/{id}', [OrderController::class, 'downloadOrder'])->name('order.download')->middleware('role_or_permission:download orders');
    Route::post('/order-detail/change', [OrderController::class, 'changeItemStatus'])->name('order.detail.change')->middleware('role_or_permission:change status orders');
    Route::post('/order/change-status/{id}', [OrderController::class, 'changeStatusQr'])->name('order.change.status')->middleware('role_or_permission:change status orders');
    Route::get('/order/invoice/{id}', [OrderController::class, 'showInvoice'])->name('order.invoice')->middleware('role_or_permission:view purchase_invoices');
    Route::get('/order/print/{id}/{type}', [OrderController::class, 'printReceipt'])->name('order.print')->middleware('role_or_permission:print orders');
    Route::get('/dashboard/check-payment-status/{orderId}', [OrderController::class, 'checkPaymentStatus'])->name('check.payment.status');
    Route::post('/order/changetable', [OrderController::class, 'changeOrderTable'])->name('order.table.change')->middleware('role_or_permission:changeTable');

    //order_settings
    Route::get('/order_settings', [OrderSettingController::class, 'index'])->name('order_settings.list')->middleware('role_or_permission:view order_settings');
    Route::group(['prefix' => 'order_setting'], function () {
        Route::get('create', [OrderSettingController::class, 'create'])->name('order_setting.create')->middleware('role_or_permission:create order_settings');
        Route::post('store', [OrderSettingController::class, 'store'])->name('order_setting.store')->middleware('role_or_permission:create order_settings');
        Route::get('show/{id}', [OrderSettingController::class, 'show'])->name('order_setting.show')->middleware('role_or_permission:view order_settings');
        Route::get('edit/{id}', [OrderSettingController::class, 'edit'])->name('order_setting.edit')->middleware('role_or_permission:update order_settings');
        Route::put('update/{id}', [OrderSettingController::class, 'update'])->name('order_setting.update')->middleware('role_or_permission:update order_settings');
        Route::delete('delete/{id}', [OrderSettingController::class, 'destroy'])->name('order_setting.delete')->middleware('role_or_permission:delete order_settings');
    });

    //hanging-orders
    Route::get('/hanging-orders', [DeliveryComplaintsController::class, 'index'])->name('hanging-orders.list')->middleware('role_or_permission:view hanging-orders');
    Route::group(['prefix' => 'hanging-orders'], function () {
        Route::get('show/{id}', [DeliveryComplaintsController::class, 'show'])->name('hanging-order.show')->middleware('role_or_permission:view hanging-orders');
        Route::get('edit/{id}', [DeliveryComplaintsController::class, 'edit'])->name('hanging-order.edit')->middleware('role_or_permission:update hanging-orders');
        Route::put('update/{id}', [DeliveryComplaintsController::class, 'changeStatus'])->name('hanging-order.update')->middleware('role_or_permission:update hanging-orders');
        Route::delete('delete/{id}', [DeliveryComplaintsController::class, 'delete'])->name('hanging-order.delete')->middleware('role_or_permission:delete hanging-orders');
    });

    //cancel_reasons
    Route::get('/cancel_reasons', [OrderCancellationReasonController::class, 'index'])->name('cancel_reasons.list')->middleware('role_or_permission:view cancellation_reasons');
    Route::group(['prefix' => 'cancel_reasons'], function () {
        Route::post('store', [OrderCancellationReasonController::class, 'store'])->name('cancel_reasons.store')->middleware('role_or_permission:create cancellation_reasons');
        Route::put('update/{id}', [OrderCancellationReasonController::class, 'update'])->name('cancel_reasons.update')->middleware('role_or_permission:update cancellation_reasons');
        Route::delete('cancel_reasons/{id}', [OrderCancellationReasonController::class, 'destroy'])->name('cancel_reasons.delete')->middleware('role_or_permission:delete cancellation_reasons');
    });

    //vendors
    Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index')->middleware('role_or_permission:view vendors');
    Route::group(['prefix' => 'vendor'], function () {
        Route::get('create', [VendorController::class, 'create'])->name('vendor.create')->middleware('role_or_permission:create vendors');
        Route::post('store', [VendorController::class, 'store'])->name('vendor.store')->middleware('role_or_permission:create vendors');
        Route::get('show/{id}', [VendorController::class, 'show'])->name('vendor.show')->middleware('role_or_permission:view vendors');
        Route::get('edit/{id}', [VendorController::class, 'edit'])->name('vendor.edit')->middleware('role_or_permission:update vendors');
        Route::put('update/{id}', [VendorController::class, 'update'])->name('vendor.update')->middleware('role_or_permission:update vendors');
        Route::delete('delete/{id}', [VendorController::class, 'destroy'])->name('vendor.delete')->middleware('role_or_permission:delete vendors');
    });

    //purchases
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index')->middleware('role_or_permission:view purchase_invoices');
    Route::group(['prefix' => 'purchase'], function () {
        Route::get('create', [PurchaseController::class, 'create'])->name('purchase.create')->middleware('role_or_permission:create purchase_invoices');
        Route::post('store', [PurchaseController::class, 'store'])->name('purchase.store')->middleware('role_or_permission:create purchase_invoices');
        Route::get('show/{id}', [PurchaseController::class, 'show'])->name('purchase.show')->middleware('role_or_permission:view purchase_invoices');
        Route::get('edit/{id}', [PurchaseController::class, 'edit'])->name('purchase.edit')->middleware('role_or_permission:update purchase_invoices');
        Route::put('update/{id}', [PurchaseController::class, 'update'])->name('purchase.update')->middleware('role_or_permission:update purchase_invoices');
        Route::delete('delete/{id}', [PurchaseController::class, 'destroy'])->name('purchase.delete')->middleware('role_or_permission:delete purchase_invoices');
        Route::get('print/{id}', [PurchaseController::class, 'print'])->name('purchase.print')->middleware('role_or_permission:print purchase_invoices');
        Route::get('invoice/{id}', [PurchaseController::class, 'showInvoice'])->name('purchase.showInvoice')->middleware('role_or_permission:print purchase_invoices');
    });

    //dishes
    Route::prefix('/dishes')->group(function () {
        Route::get('/', [DishController::class, 'index'])->name('dashboard.dishes.index')->middleware('role_or_permission:view dishes');
        Route::get('/create', [DishController::class, 'create'])->name('dashboard.dishes.create')->middleware('role_or_permission:view dishes');
        Route::post('/', [DishController::class, 'store'])->name('dashboard.dishes.store')->middleware('role_or_permission:view dishes');
        Route::get('/{id}', [DishController::class, 'show'])->name('dashboard.dishes.show')->middleware('role_or_permission:view dishes');
        Route::get('/{id}/edit', [DishController::class, 'edit'])->name('dashboard.dishes.edit')->middleware('role_or_permission:view dishes');
        Route::put('/{id}', [DishController::class, 'update'])->name('dashboard.dishes.update')->middleware('role_or_permission:view dishes');
        Route::post('delete/{id}', [DishController::class, 'destroy'])->name('dashboard.dishes.destroy')->middleware('role_or_permission:view dishes');
        Route::post('/{id}/restore', [DishController::class, 'restore'])->name('dashboard.dishes.restore')->middleware('role_or_permission:view dishes');
        Route::get('/{id}/ingredient', [DishController::class, 'showIngredientForm'])->name('dashboard.dishes.ingredient');
        Route::post('/save-ingredient', [DishController::class, 'saveIngredient'])->name('dashboard.dishes.ingredient.save');
    });

    //dish-categories
    Route::prefix('dish-categories')->group(function () {
        Route::get('/', [DishCategoryController::class, 'index'])->name('dashboard.dish-categories.index')->middleware('role_or_permission:view dish_categories');
        Route::get('/dish-categories/{id}/show', [DishCategoryController::class, 'show'])->name('dashboard.dish-categories.show')->middleware('role_or_permission:view dish_categories');
        Route::get('/create', [DishCategoryController::class, 'create'])->name('dashboard.dish-categories.create')->middleware('role_or_permission:create dish_categories');
        Route::post('/', [DishCategoryController::class, 'store'])->name('dashboard.dish-categories.store')->middleware('role_or_permission:create dish_categories');
        Route::get('/{id}/edit', [DishCategoryController::class, 'edit'])->name('dashboard.dish-categories.edit')->middleware('role_or_permission:update dish_categories');
        Route::put('/{id}', [DishCategoryController::class, 'update'])->name('dashboard.dish-categories.update')->middleware('role_or_permission:update dish_categories');
        Route::delete('/{id}', [DishCategoryController::class, 'delete'])->name('dashboard.dish-categories.delete')->middleware('role_or_permission:delete dish_categories');
        Route::post('/restore/{id}', [DishCategoryController::class, 'restore'])->name('dashboard.dish-categories.restore')->middleware('role_or_permission:create dish_categories');
    });

    //dish_products
    Route::prefix('dish_products')->name('dashboard.dish_products.')->group(function () {
        Route::get('create', [DishProductController::class, 'create'])->name('create')->middleware('role_or_permission:create products');
        Route::post('store', [DishProductController::class, 'store'])->name('store')->middleware('role_or_permission:create products');
    });

    // Recipe
    Route::prefix('recipes')->group(function () {
        Route::get('/', [RecipeController::class, 'index'])->name('dashboard.recipes.index')->middleware('role_or_permission:view recipes');
        Route::get('/show/{id}', [RecipeController::class, 'show'])->name('dashboard.recipes.show')->middleware('role_or_permission:view recipes');
        Route::get('/create', [RecipeController::class, 'create'])->name('dashboard.recipes.create')->middleware('role_or_permission:create recipes');
        Route::post('/', [RecipeController::class, 'store'])->name('dashboard.recipes.store')->middleware('role_or_permission:create recipes');
        Route::get('/edit/{id}', [RecipeController::class, 'edit'])->name('dashboard.recipes.edit')->middleware('role_or_permission:update recipes');
        Route::put('/{id}', [RecipeController::class, 'update'])->name('dashboard.recipes.update')->middleware('role_or_permission:update recipes');
        Route::delete('/{id}', [RecipeController::class, 'delete'])->name('dashboard.recipes.delete')->middleware('role_or_permission:delete recipes');
        Route::post('/restore/{id}', [RecipeController::class, 'restore'])->name('dashboard.recipes.restore')->middleware('role_or_permission:create recipes');
    });

    //addon
    Route::prefix('/addons')->group(function () {
        Route::get('/', [AddonController::class, 'index'])->name('dashboard.addons.index')->middleware('role_or_permission:view addons');
        Route::get('/create', [AddonController::class, 'create'])->name('dashboard.addons.create')->middleware('role_or_permission:create addons');
        Route::post('/', [AddonController::class, 'store'])->name('dashboard.addons.store')->middleware('role_or_permission:create addons');
        Route::get('/{id}', [AddonController::class, 'show'])->name('dashboard.addons.show')->middleware('role_or_permission:view addons');
        Route::get('/{id}/edit', [AddonController::class, 'edit'])->name('dashboard.addons.edit')->middleware('role_or_permission:update addons');
        Route::put('/{id}', [AddonController::class, 'update'])->name('dashboard.addons.update')->middleware('role_or_permission:update addons');
        Route::delete('/{id}', [AddonController::class, 'destroy'])->name('dashboard.addons.destroy')->middleware('role_or_permission:delete addons');
        Route::post('/{id}/restore', [AddonController::class, 'restore'])->name('dashboard.addons.restore')->middleware('role_or_permission:restore addons');
    });

    //addon-categories
    Route::prefix('/addon-categories')->group(function () {
        Route::get('/', [AddonCategoryController::class, 'index'])->name('dashboard.addon_categories.index')->middleware('role_or_permission:view addon_categories');
        Route::get('/create', [AddonCategoryController::class, 'create'])->name('dashboard.addon_categories.create')->middleware('role_or_permission:create addon_categories');
        Route::post('/', [AddonCategoryController::class, 'store'])->name('dashboard.addon_categories.store')->middleware('role_or_permission:create addon_categories');
        Route::get('/{id}', [AddonCategoryController::class, 'show'])->name('dashboard.addon_categories.show')->middleware('role_or_permission:view addon_categories');
        Route::get('/{id}/edit', [AddonCategoryController::class, 'edit'])->name('dashboard.addon_categories.edit')->middleware('role_or_permission:update addon_categories');
        Route::put('/{id}', [AddonCategoryController::class, 'update'])->name('dashboard.addon_categories.update')->middleware('role_or_permission:update addon_categories');
        Route::delete('/{id}', [AddonCategoryController::class, 'destroy'])->name('dashboard.addon_categories.destroy')->middleware('role_or_permission:delete addon_categories');
        Route::post('/{id}/restore', [AddonCategoryController::class, 'restore'])->name('dashboard.addon_categories.restore')->middleware('role_or_permission:restore addon_categories');
    });

    //cuisines
    Route::prefix('/cuisines')->group(function () {
        Route::get('/', [CuisineController::class, 'index'])->name('dashboard.cuisines.index')->middleware('role_or_permission:view cuisines');
        Route::get('/create', [CuisineController::class, 'create'])->name('dashboard.cuisines.create')->middleware('role_or_permission:create cuisines');
        Route::post('/', [CuisineController::class, 'store'])->name('dashboard.cuisines.store')->middleware('role_or_permission:create cuisines');
        Route::get('/{id}', [CuisineController::class, 'show'])->name('dashboard.cuisines.show')->middleware('role_or_permission:view cuisines');
        Route::get('/{id}/edit', [CuisineController::class, 'edit'])->name('dashboard.cuisines.edit')->middleware('role_or_permission:update cuisines');
        Route::put('/{id}', [CuisineController::class, 'update'])->name('dashboard.cuisines.update')->middleware('role_or_permission:update cuisines');
        Route::post('delete/{id}', [CuisineController::class, 'destroy'])->name('dashboard.cuisines.destroy')->middleware('role_or_permission:delete cuisines');
        Route::post('/restore/{id}', [CuisineController::class, 'restore'])->name('dashboard.cuisines.restore')->middleware('role_or_permission:restore cuisines');
    });
    Route::post('/cuisines/{id}/assign-dish-categories', [CuisineController::class, 'assignDishCategories'])->name('dashboard.cuisines.assign');
    // routes/web.php or routes/api.php
    Route::get('/dashboard/cuisine-categories/{id}/dishes', [CuisineController::class, 'getDishesForCategory'])->name('categoriesemployee');

    //chef-cuisine
    Route::get('/chef-cuisine', [ChefCuisineCategoryController::class, 'index'])->name('chefCuisine.index')->middleware('role_or_permission:view chef-cuisine');
    Route::group(['prefix' => 'chefCuisine'], function () {
        Route::get('create', [ChefCuisineCategoryController::class, 'create'])->name('chefCuisine.create')->middleware('role_or_permission:create chef-cuisine');
        Route::post('store', [ChefCuisineCategoryController::class, 'store'])->name('chefCuisine.store')->middleware('role_or_permission:create chef-cuisine');
        Route::delete('/{id}', [ChefCuisineCategoryController::class, 'destroy'])->name('chefCuisine.destroy')->middleware('role_or_permission:delete chef-cuisine');
    });
    Route::get('/get-available-cuisines/{chefId}', [ChefCuisineCategoryController::class, 'getAvailableCuisines']);
    Route::get('/get-dishes-by-cuisine-category/{cuisineCategoryId}', [ChefCuisineCategoryController::class, 'getDishesByCuisineCategory'])->name('dishes.by-cuisine-category');
    Route::get('/job-types', [JobTypeController::class, 'index'])->name('job_types.list')->middleware('role_or_permission:view job_types');
    Route::group(['prefix' => 'job-type'], function () {
        Route::post('store', [JobTypeController::class, 'store'])->name('job_type.store')->middleware('role_or_permission:create job_types');
        Route::get('show/{id}', [JobTypeController::class, 'show'])->name('job_type.show')->middleware('role_or_permission:view job_types');
        Route::put('update/{id}', [JobTypeController::class, 'update'])->name('job_type.update')->middleware('role_or_permission:update job_types');
        Route::delete('delete/{id}', [JobTypeController::class, 'destroy'])->name('job_type.delete')->middleware('role_or_permission:delete job_types');
    });

    //reports
    Route::group(['prefix' => 'reports'], function () {
        Route::group(['prefix' => 'customers'], function () {
            Route::get('list', [CustomerReportController::class, 'customers'])->name('reports.customers.list')->middleware('role_or_permission:view customers_reports');
            Route::get('list/orders', [CustomerReportController::class, 'mostcustomers'])->name('reports.most.customers.list')->middleware('role_or_permission:view report_most_customer_place_order');
            Route::get('list/{id}/order', [CustomerReportController::class, 'mostcustomerdetail'])
                ->name('reports.most.customers.detail')->middleware('role_or_permission:view report_most_customer_place_order');
            Route::get('pdf', [CustomerReportController::class, 'savePdf'])->name('reports.savepdf')->middleware('role_or_permission:save report_pdf');
        });
        Route::group(['prefix' => 'best_seller_dish'], function () {
            Route::get('list', [BestSellerDishReportController::class, 'index'])->name('reports.best_seller_dish.list')->middleware('role_or_permission:view report_best_seller_dishes');
            Route::get('/{id}', [BestSellerDishReportController::class, 'show'])->name('reports.best_seller_dish.show')->middleware('role_or_permission:detail report_best_seller_dishes');
            Route::get('print/{id}', [BestSellerDishReportController::class, 'print'])->name('reports.best_seller_dish.print')->middleware('role_or_permission:save report_pdf');
        });
        Route::group(['prefix' => 'purchasing'], function () {
            Route::get('list', [PurchasingReportController::class, 'list'])->name('reports.purchasing.list')->middleware('role_or_permission:view purchasing_reports');
        });
        Route::group(['prefix' => 'orders'], function () {
            Route::get('list', [OrdersReportsController::class, 'list'])->name('reports.orders.list')->middleware('role_or_permission:view report_orders');
            Route::get('/{id}', [OrdersReportsController::class, 'show'])->name('reports.orders.show')->middleware('role_or_permission:detail report_orders');
        });
        Route::group(['prefix' => 'cancelled-orders'], function () {
            Route::get('list', [CancelledOrdersReportsController::class, 'list'])->name('reports.cancelled_orders.list')->middleware('role_or_permission:view report_cancelled_orders');
            Route::get('/{id}', [CancelledOrdersReportsController::class, 'show'])->name('reports.cancelled_orders.show')->middleware('role_or_permission:detail report_cancelled_orders');
        });
        Route::group(['prefix' => 'delivery-orders'], function () {
            Route::get('list', [DeliveryOrdersReportsController::class, 'list'])->name('reports.delivery.orders.list')->middleware('role_or_permission:view report_delivery_orders');
            Route::get('/{id}', [DeliveryOrdersReportsController::class, 'show'])->name('reports.delivery.orders.show')->middleware('role_or_permission:detail report_delivery_orders');
        });
        Route::group(['prefix' => 'customer-service-delivery-orders'], function () {
            Route::get('list', [CustomerServiceDeliveryOrdersReportsController::class, 'list'])->name('reports.customer_service_delivery_orders.list')->middleware('role_or_permission:view report_customer_service_delivery_orders');
            Route::get('/{id}', [CustomerServiceDeliveryOrdersReportsController::class, 'show'])->name('reports.customer_service_delivery_orders.show')->middleware('role_or_permission:detail report_customer_service_delivery_orders');
        });
        Route::group(['prefix' => 'branches'], function () {
            Route::get('list', [BranchesReportsController::class, 'index'])->name('reports.branches.list')->middleware('role_or_permission:view report_branches');
            Route::get('/{id}', [BranchesReportsController::class, 'show'])->name('reports.branches.show')->middleware('role_or_permission:detail report_branches');
        });
        Route::group(['prefix' => 'cashier-balances'], function () {
            Route::get('/list', [CashierBalancesReportsController::class, 'index'])->name('reports.cashier_balances.list')->middleware('role_or_permission:view report_cashier_balances');
            Route::get('/show/{id}', [CashierBalancesReportsController::class, 'show'])->name('reports.cashier_balances.show')->middleware('role_or_permission:detail report_cashier_balances');
        });
        Route::group(['prefix' => 'cashier-branch-safe'], function () {
            Route::get('/', [CashierBranchSafeReportsController::class, 'index'])->name('reports.cashier_branch-safe.list')->middleware('role_or_permission:view report_cashier_balances');
            Route::get('/{id}', [CashierBranchSafeReportsController::class, 'show'])->name('reports.cashier_branch-safe.show')->middleware('role_or_permission:detail report_cashier_balances');
        });
        Route::group(['prefix' => 'cashier-balance-transactions'], function () {
            Route::get('list', [CashierBalanceTransactionsReportsController::class, 'index'])->name('reports.cashier_balance_transactions.list')->middleware('role_or_permission:view report_cashier_balance_transactions');
            Route::get('/{id}', [CashierBalanceTransactionsReportsController::class, 'show'])->name('reports.cashier_balance_transactions.show')->middleware('role_or_permission:detail report_cashier_balance_transactions');
        });
        Route::group(['prefix' => 'rates'], function () {
            Route::get('list', [RateReportController::class, 'index'])->name('reports.rates.list')->middleware('role_or_permission:view report_rates');
            Route::get('/{id}', [RateReportController::class, 'show'])->name('reports.rates.show')->middleware('role_or_permission:detail report_rates');
        });
        Route::group(['prefix' => 'feedbacks'], function () {
            Route::get('list', [FeedbackReportController::class, 'index'])->name('reports.feedbacks.list')->middleware('role_or_permission:view report_feedbacks');
            Route::get('/{id}', [FeedbackReportController::class, 'show'])->name('reports.feedbacks.show')->middleware('role_or_permission:detail report_feedbacks');
        });
        Route::group(['prefix' => 'hanging-orders'], function () {
            Route::get('/list', [DeliveryComplaintsReportsController::class, 'index'])->name('reports.hanging-orders.list')->middleware('role_or_permission:view report_hanging_orders');
            Route::get('/{id}', [DeliveryComplaintsReportsController::class, 'show'])->name('reports.hanging-order.show')->middleware('role_or_permission:detail report_hanging_orders');
        });
        Route::group(['prefix' => 'table_reservations'], function () {
            Route::get('list', [TableReservationReportController::class, 'index'])->name('reports.table_reservations.list')->middleware('role_or_permission:view report_table_reservations');
            Route::get('/{id}', [TableReservationReportController::class, 'show'])->name('reports.table_reservations.show')->middleware('role_or_permission:detail report_table_reservations');
        });
        Route::group(['prefix' => 'booking-revenue'], function () {
            Route::get('list', [BookingRevenueReportController::class, 'index'])->name('reports.booking_revenue.list')->middleware('role_or_permission:view report_booking_revenue');
            Route::get('/{id}', [BookingRevenueReportController::class, 'show'])->name('reports.booking_revenue.show')->middleware('role_or_permission:detail report_booking_revenue');
        });
        Route::group(['prefix' => 'booking-cancellation'], function () {
            Route::get('list', [BookingCancellationReportController::class, 'index'])->name('reports.booking_cancellation.list')->middleware('role_or_permission:view report_booking_cancellation');
            Route::get('/{id}', [BookingCancellationReportController::class, 'show'])->name('reports.booking_cancellation.show')->middleware('role_or_permission:detail report_booking_cancellation');
        });
        Route::group(['prefix' => 'waiter_table_service_report'], function () {
            Route::get('list', [WaiterTableServiceReportController::class, 'index'])->name('reports.waiter_table_service_report.list')->middleware('role_or_permission:view waiter_table_service_report');
            Route::get('/{id}', [WaiterTableServiceReportController::class, 'show'])->name('reports.waiter_table_service_report.show')->middleware('role_or_permission:detail waiter_table_service_report');
        });
        Route::group(['prefix' => 'delivery_order_report'], function () {
            Route::get('list', [DeliveryOrderReportController::class, 'index'])->name('reports.delivery_order_report.list')->middleware('role_or_permission:view delivery_order_report');
            Route::get('/{id}', [DeliveryOrderReportController::class, 'show'])->name('reports.delivery_order_report.show')->middleware('role_or_permission:detail delivery_order_report');
        });
        Route::group(['prefix' => 'delivery_earnings_payments_report'], function () {
            Route::get('/list', [DeliveryEearningsPaymentsReportController::class, 'index'])->name('reports.delivery_earnings_payments_report.list')->middleware('role_or_permission:view delivery_earnings_payments_report');
            Route::get('/show/{id}', [DeliveryEearningsPaymentsReportController::class, 'show'])->name('reports.delivery_earnings_payments_report.show')->middleware('role_or_permission:detail delivery_earnings_payments_report');
        });
        Route::group(['prefix' => 'delivery_performance_metrics_report'], function () {
            Route::get('/list', [DeliveryPerformanceMetricsReportController::class, 'index'])->name('reports.delivery_performance_metrics_report.list')->middleware('role_or_permission:view delivery_performance_metrics_report');
            Route::get('/show/{id}', [DeliveryPerformanceMetricsReportController::class, 'show'])->name('reports.delivery_performance_metrics_report.show')->middleware('role_or_permission:detail delivery_performance_metrics_report');
        });
        Route::group(['prefix' => 'waiter_requests'], function () {
            Route::get('list', [WaiterRequestReportController::class, 'index'])->name('reports.waiter_requests.list')->middleware('role_or_permission:view report_waiter_requests');
            Route::post('/search', [WaiterRequestReportController::class, 'search'])->name('reports.waiter_requests.search')->middleware('role_or_permission:view report_waiter_requests');
        });

        Route::group(['prefix' => 'kitchen_performance'], function () {
            Route::get('/list', [KitchenPerformanceReportController::class, 'index'])->name('reports.kitchen_performance.list')->middleware('role_or_permission:view report_kitchen_performance');
            Route::post('/search', [KitchenPerformanceReportController::class, 'search'])->name('reports.kitchen_performance.search')->middleware('role_or_permission:detail report_kitchen_performance');
        });
        Route::group(['prefix' => 'cashier-performance'], function () {
            Route::get('/list', [CashierPerformanceReportsController::class, 'list'])->name('reports.cashier_performance.list')->middleware('role_or_permission:view report_cashier_performance');
            Route::get('/show/{id}', [CashierPerformanceReportsController::class, 'show'])->name('reports.cashier_performance.show')->middleware('role_or_permission:detail report_cashier_performance');
        });
        Route::group(['prefix' => 'branch_safe'], function () {
            Route::get('/', [BranchSafeController::class, 'index'])->name('reports.branch_safe.index')->middleware('role_or_permission:view report_branch_safe');
            Route::get('/{id}', [BranchSafeController::class, 'show'])->name('reports.branch_safe.show')->middleware('role_or_permission:show report_branch_safe');
        });
    });

    //einvoices
    Route::prefix('einvoices')->name('dashboard.einvoices.')->group(function () {
        Route::get('view', [CashierSettingController::class, 'index'])->name('list')->middleware('role_or_permission:view einvoices_superadmin');
        Route::post('/get-pos', [CashierSettingController::class, 'getPosByBranch'])->name('get.pos');
        Route::post('/filter/einvoice', [CashierSettingController::class, 'filter'])->name('superadmin.filter');

        Route::get('setting', [CashierSettingController::class, 'setting'])->name('setting.list')->middleware('role_or_permission:view officer_assign_setting');
        Route::get('setting/create', [CashierSettingController::class, 'createSetting'])->name('setting.create')->middleware('role_or_permission:create officer_assign_setting');
        Route::post('setting/add', [CashierSettingController::class, 'store'])->name('setting.store')->middleware('role_or_permission:create officer_assign_setting');
        Route::get('setting/edit/{id}', [CashierSettingController::class, 'edit'])->name('setting.edit')->middleware('role_or_permission:update officer_assign_setting');
        Route::post('setting/{id}', [CashierSettingController::class, 'update'])->name('setting.update')->middleware('role_or_permission:update officer_assign_setting');

        Route::post('show/filter', [EinvoiceController::class, 'filterInvoices'])->name('filter');
        Route::post('show/test', [EinvoiceController::class, 'testInvoices'])->name('test');
        Route::post('show/reset', [EinvoiceController::class, 'resetInvoices'])->name('reset');
        Route::post('show/upload', [EinvoiceController::class, 'uploadInvoices'])->name('upload');
        Route::get('/cashier-settings/{cashierMachineId}', [EinvoiceController::class, 'getCashierSettings']);
        Route::get('show', [EinvoiceController::class, 'show'])->name('show')->middleware('role_or_permission:view einvoices');
        Route::get('submitted', [EinvoiceController::class, 'submitted'])->name('submitted')->middleware('role_or_permission:view einvoices');
        Route::post('einvoice/send', [EinvoiceController::class, 'SendPortal'])->name('einvoice.send');
        Route::post('ereceipt/send', [EReceiptController::class, 'SendPortal'])->name('ereceipt.send');
        Route::get('ereceipt/details/{id}', [EReceiptController::class, 'GetReceiptDetails'])->name('ereceipt.details');
        // ->middleware('role_or_permission:send einvoices');
    });

    //cashier_machines
    Route::prefix('cashier_machines')->name('dashboard.cashierMachines.')->group(function () {
        Route::get('index', [cashierMachineController::class, 'index'])->name('list')->middleware('role_or_permission:view cashier_machines');
        Route::post('store', [cashierMachineController::class, 'store'])->name('store')->middleware('role_or_permission:create cashier_machines');
        Route::post('update/{id}', [cashierMachineController::class, 'update'])->name('update')->middleware('role_or_permission:update cashier_machines');
        Route::delete('dashboard/cashier_machines/delete/{id}', [cashierMachineController::class, 'destroy'])
            ->name('delete')
            ->middleware('role_or_permission:delete cashier_machines');
        Route::get('/cashier/get-employees', [CashierMachineController::class, 'getEmployeesByBranch'])
            ->name('cashier.getEmployeesByBranch');
        Route::get('/cashier/get-employees-edit', [CashierMachineController::class, 'getEmployeesForEdit'])
            ->name('cashier.getEmployeesForEdit');
    });

    //cash payment limits
    Route::get('/cash-settings', [CashPaymentSettingController::class, 'index'])->name('cashPaymentSettings.list')->middleware('role_or_permission:view cashPaymentSetting');
    Route::group(['prefix' => 'cash-setting'], function () {
        Route::get('create', [CashPaymentSettingController::class, 'create'])->name('cashPaymentSetting.create')->middleware('role_or_permission:create cashPaymentSetting');
        Route::post('store', [CashPaymentSettingController::class, 'store'])->name('cashPaymentSetting.store')->middleware('role_or_permission:create cashPaymentSetting');
        Route::get('show/{id}', [CashPaymentSettingController::class, 'show'])->name('cashPaymentSetting.show')->middleware('role_or_permission:view cashPaymentSetting');
        Route::get('edit/{id}', [CashPaymentSettingController::class, 'edit'])->name('cashPaymentSetting.edit')->middleware('role_or_permission:update cashPaymentSetting');
        Route::put('update/{id}', [CashPaymentSettingController::class, 'update'])->name('cashPaymentSetting.update')->middleware('role_or_permission:update cashPaymentSetting');
        Route::delete('delete/{id}', [CashPaymentSettingController::class, 'destroy'])->name('cashPaymentSetting.delete')->middleware('role_or_permission:delete cashPaymentSetting');
    });
    // Route::post('waiter/split/order/{id}', [InvoiceController::class, 'divideInvoice'])->name('split.request');
    // Route::post('waiter/merge/order/{id}', [InvoiceController::class, 'mergeInvoices'])->name('merge.request');
    // Route::group(['prefix' => 'cash-setting'], function () {
    // Route::group(['prefix' => 'cash-setting'], function () {
    //     Route::get('create', [CashPaymentSettingController::class, 'create'])->name('cashPaymentSetting.create')->middleware('role_or_permission:create cashPaymentSetting');
    //     Route::post('store', [CashPaymentSettingController::class, 'store'])->name('cashPaymentSetting.store')->middleware('role_or_permission:create cashPaymentSetting');
    //     Route::get('show/{id}', [CashPaymentSettingController::class, 'show'])->name('cashPaymentSetting.show')->middleware('role_or_permission:view cashPaymentSetting');
    //     Route::get('edit/{id}', [CashPaymentSettingController::class, 'edit'])->name('cashPaymentSetting.edit')->middleware('role_or_permission:update cashPaymentSetting');
    //     Route::put('update/{id}', [CashPaymentSettingController::class, 'update'])->name('cashPaymentSetting.update')->middleware('role_or_permission:update cashPaymentSetting');
    //     Route::delete('delete/{id}', [CashPaymentSettingController::class, 'destroy'])->name('cashPaymentSetting.delete')->middleware('role_or_permission:delete cashPaymentSetting');
    // });

    //waiter-requests
    Route::get('/waiter-requests', [WaiterRequestController::class, 'index'])->name('waiterrequest.list');
    Route::get('/waiter-requests/{id}', [WaiterRequestController::class, 'showInvoice'])->name('waiter.request.show');
    Route::post('/reject/request', [WaiterRequestController::class, 'rejectRequest'])->name('reject.waiter.request');
    Route::get('/accept/request/{id}', [WaiterRequestController::class, 'acceptRequest'])->name('accept.waiter.request');
    Route::get('/request/ajax-print', [WaiterRequestController::class, 'ajaxPrint'])->name('request.print.ajax');


    //Vehicle
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.list')->middleware('role_or_permission:view vehicles');
    Route::group(['prefix' => 'vehicles'], function () {
        Route::post('store', [VehicleController::class, 'store'])->name('vehicles.store')->middleware('role_or_permission:create vehicles');
        Route::put('update/{id}', [VehicleController::class, 'update'])->name('vehicles.update')->middleware('role_or_permission:update vehicles');
        Route::delete('vehicles/{id}', [VehicleController::class, 'destroy'])->name('vehicles.delete')->middleware('role_or_permission:delete vehicles');
    });

    //VehicleSetting
    Route::get('/vehicle_settings', [VehicleSettingController::class, 'index'])->name('vehicle_settings.index')->middleware('role_or_permission:view vehicle_settings');
    Route::group(['prefix' => 'vehicle_setting'], function () {
        Route::get('edit/{id}', [VehicleSettingController::class, 'edit'])->name('vehicle_setting.edit')->middleware('role_or_permission:update vehicle_settings');
        Route::put('update/{id}', [VehicleSettingController::class, 'update'])->name('vehicle_setting.update')->middleware('role_or_permission:update vehicle_settings');
    });

    //Company Profile Settings
    Route::get('/company_profile_setting', [CompanyProfileSettingController::class, 'index'])->name('company_profile_setting.list')->middleware('role_or_permission:view company_profile_setting');
    Route::group(['prefix' => 'company_profile_setting'], function () {
        Route::get('create', [CompanyProfileSettingController::class, 'create'])->name('company_profile_setting.create')->middleware('role_or_permission:create company_profile_setting');
        Route::post('store', [CompanyProfileSettingController::class, 'store'])->name('company_profile_setting.store')->middleware('role_or_permission:create company_profile_setting');
        Route::get('show/{id}', [CompanyProfileSettingController::class, 'show'])->name('company_profile_setting.show')->middleware('role_or_permission:view company_profile_setting');
        Route::get('edit/{id}', [CompanyProfileSettingController::class, 'edit'])->name('company_profile_setting.edit')->middleware('role_or_permission:update company_profile_setting');
        Route::put('update/{id}', [CompanyProfileSettingController::class, 'update'])->name('company_profile_setting.update')->middleware('role_or_permission:update company_profile_setting');
        Route::delete('delete/{id}', [CompanyProfileSettingController::class, 'delete'])->name('company_profile_setting.delete')->middleware('role_or_permission:delete company_profile_setting');
    });

    //Contact Information Setting
    Route::get('/contact_information_setting', [ContactInformationSettingController::class, 'index'])->name('contact_information_setting.list')->middleware('role_or_permission:view company_profile_setting');
    Route::group(['prefix' => 'contact_information_setting'], function () {
        Route::get('create', [ContactInformationSettingController::class, 'create'])->name('contact_information_setting.create')->middleware('role_or_permission:create company_profile_setting');
        Route::post('store', [ContactInformationSettingController::class, 'store'])->name('contact_information_setting.store')->middleware('role_or_permission:create company_profile_setting');
        Route::get('show/{id}', [ContactInformationSettingController::class, 'show'])->name('contact_information_setting.show')->middleware('role_or_permission:view company_profile_setting');
        Route::get('edit/{id}', [ContactInformationSettingController::class, 'edit'])->name('contact_information_setting.edit')->middleware('role_or_permission:update company_profile_setting');
        Route::put('update/{id}', [ContactInformationSettingController::class, 'update'])->name('contact_information_setting.update')->middleware('role_or_permission:update company_profile_setting');
        Route::delete('delete/{id}', [ContactInformationSettingController::class, 'delete'])->name('contact_information_setting.delete')->middleware('role_or_permission:delete company_profile_setting');
    });

    //Social Media Information Setting
    Route::get('/social_media_information_setting', [SocialMediaInformationSettingController::class, 'index'])->name('social_media_information_setting.list')->middleware('role_or_permission:view company_profile_setting');
    Route::group(['prefix' => 'social_media_information_setting'], function () {
        Route::get('create', [SocialMediaInformationSettingController::class, 'create'])->name('social_media_information_setting.create')->middleware('role_or_permission:create company_profile_setting');
        Route::post('store', [SocialMediaInformationSettingController::class, 'store'])->name('social_media_information_setting.store')->middleware('role_or_permission:create company_profile_setting');
        Route::get('show/{id}', [SocialMediaInformationSettingController::class, 'show'])->name('social_media_information_setting.show')->middleware('role_or_permission:view company_profile_setting');
        Route::get('edit/{id}', [SocialMediaInformationSettingController::class, 'edit'])->name('social_media_information_setting.edit')->middleware('role_or_permission:update company_profile_setting');
        Route::put('update/{id}', [SocialMediaInformationSettingController::class, 'update'])->name('social_media_information_setting.update')->middleware('role_or_permission:update company_profile_setting');
        Route::delete('delete/{id}', [SocialMediaInformationSettingController::class, 'delete'])->name('social_media_information_setting.delete')->middleware('role_or_permission:delete company_profile_setting');
    });

    //Business Activity
    Route::get('/business_activity', [BusinessActivityController::class, 'index'])->name('business_activity.list')->middleware('role_or_permission:view business_activity');
    Route::group(['prefix' => 'business_activity'], function () {
        Route::get('create', [BusinessActivityController::class, 'create'])->name('business_activity.create')->middleware('role_or_permission:create business_activity');
        Route::post('store', [BusinessActivityController::class, 'store'])->name('business_activity.store')->middleware('role_or_permission:create business_activity');
        Route::get('show/{id}', [BusinessActivityController::class, 'show'])->name('business_activity.show')->middleware('role_or_permission:view business_activity');
        Route::get('edit/{id}', [BusinessActivityController::class, 'edit'])->name('business_activity.edit')->middleware('role_or_permission:update business_activity');
        Route::put('update/{id}', [BusinessActivityController::class, 'update'])->name('business_activity.update')->middleware('role_or_permission:update business_activity');
        Route::delete('delete/{id}', [BusinessActivityController::class, 'delete'])->name('business_activity.delete')->middleware('role_or_permission:delete business_activity');
    });

    //payment_policies
    Route::get('/payment_policies', [PaymentPoliciesController::class, 'index'])->name('payment_policies.list')->middleware('role_or_permission:view payment_policies');
    Route::group(['prefix' => 'payment_policies'], function () {
        Route::get('create', [PaymentPoliciesController::class, 'create'])->name('payment_policies.create')->middleware('role_or_permission:create payment_policies');
        Route::post('store', [PaymentPoliciesController::class, 'store'])->name('payment_policies.store')->middleware('role_or_permission:create payment_policies');
        Route::get('show/{id}', [PaymentPoliciesController::class, 'show'])->name('payment_policies.show')->middleware('role_or_permission:view payment_policies');
        Route::get('edit/{id}', [PaymentPoliciesController::class, 'edit'])->name('payment_policies.edit')->middleware('role_or_permission:update payment_policies');
        Route::put('update/{id}', [PaymentPoliciesController::class, 'update'])->name('payment_policies.update')->middleware('role_or_permission:update payment_policies');
        Route::delete('payment_policies/{id}', [PaymentPoliciesController::class, 'delete'])->name('payment_policies.delete')->middleware('role_or_permission:delete payment_policies');
    });

    //payment_reservation_policies
    Route::get('/payment_reservation_policies', [PaymentReservationPolicyController::class, 'index'])->name('payment_reservation_policy.list')->middleware('role_or_permission:view payment_reservation_policies');
    Route::group(['prefix' => 'payment_reservation_policies'], function () {
        Route::post('update', [PaymentReservationPolicyController::class, 'update'])->name('payment_reservation_policy.update')->middleware('role_or_permission:update payment_reservation_policies');
    });

    //branch_settings
    Route::get('/branch_settings', [BranchSettingController::class, 'index'])->name('branch_settings.list');
    Route::group(['prefix' => 'branch_settings'], function () {
        Route::get('create', [BranchSettingController::class, 'create'])->name('branch_settings.create');
        Route::post('store', [BranchSettingController::class, 'store'])->name('branch_settings.store');
        Route::get('show/{id}', [BranchSettingController::class, 'show'])->name('branch_settings.show');
        Route::get('edit/{id}', [BranchSettingController::class, 'edit'])->name('branch_settings.edit');
        Route::put('update/{id}', [BranchSettingController::class, 'update'])->name('branch_settings.update');
        Route::delete('branch_settings/{id}', [BranchSettingController::class, 'delete'])->name('branch_settings.delete');
    });
    Route::get('/branch_settings/payment_policies', [BranchSettingController::class, 'getPaymentPolicies'])->name('branch_settings.getPaymentPolicies');

    //ethnic_backgrounds
    Route::get('/ethnic_backgrounds', [EthnicBackgroundController::class, 'index'])->name('ethnic_backgrounds.list')->middleware('role_or_permission:view ethnic_backgrounds');
    Route::group(['prefix' => 'ethnic_background'], function () {
        Route::post('store', [EthnicBackgroundController::class, 'store'])->name('ethnic_background.store')->middleware('role_or_permission:create ethnic_backgrounds');
        Route::put('update/{id}', [EthnicBackgroundController::class, 'update'])->name('ethnic_background.update')->middleware('role_or_permission:update ethnic_backgrounds');
        Route::delete('delete/{id}', [EthnicBackgroundController::class, 'delete'])->name('ethnic_background.delete')->middleware('role_or_permission:delete ethnic_backgrounds');
    });

    //bank_names
    Route::get('/bank_names', [BankNameController::class, 'index'])->name('bank_names.list')->middleware('role_or_permission:view bank_names');
    Route::group(['prefix' => 'bank_name'], function () {
        Route::post('store', [BankNameController::class, 'store'])->name('bank_name.store')->middleware('role_or_permission:create bank_names');
        Route::put('update/{id}', [BankNameController::class, 'update'])->name('bank_name.update')->middleware('role_or_permission:update bank_names');
        Route::delete('delete/{id}', [BankNameController::class, 'delete'])->name('bank_name.delete')->middleware('role_or_permission:delete bank_names');
    });

    //marital_statuses
    Route::group(['prefix' => 'marital_statuses'], function () {
        Route::get('/', [MaritalStatusController::class, 'index'])->name('marital_statuses.list')->middleware('role_or_permission:view marital_statuses');
        Route::post('store', [MaritalStatusController::class, 'store'])->name('marital_status.store')->middleware('role_or_permission:create marital_statuses');
        Route::put('update/{id}', [MaritalStatusController::class, 'update'])->name('marital_status.update')->middleware('role_or_permission:update marital_statuses');
        Route::delete('delete/{id}', [MaritalStatusController::class, 'delete'])->name('marital_status.delete')->middleware('role_or_permission:delete marital_statuses');
    });

    //military_service_statuses
    Route::group(['prefix' => 'military_service_statuses'], function () {
        Route::get('/', [MilitaryServiceStatusController::class, 'index'])->name('military_service_statuses.list')->middleware('role_or_permission:view military_service_statuses');
        Route::post('store', [MilitaryServiceStatusController::class, 'store'])->name('military_service_status.store')->middleware('role_or_permission:create military_service_statuses');
        Route::put('update/{id}', [MilitaryServiceStatusController::class, 'update'])->name('military_service_status.update')->middleware('role_or_permission:update military_service_statuses');
        Route::delete('delete/{id}', [MilitaryServiceStatusController::class, 'delete'])->name('military_service_status.delete')->middleware('role_or_permission:delete military_service_statuses');
    });

    //employee_status
    Route::get('/employee_status', [EmployeeStatusController::class, 'index'])->name('employee_status.list')->middleware('role_or_permission:view employee_status');
    Route::group(['prefix' => 'employee_status'], function () {
        Route::post('store', [EmployeeStatusController::class, 'store'])->name('employee_status.store')->middleware('role_or_permission:create employee_status');
        Route::put('update/{id}', [EmployeeStatusController::class, 'update'])->name('employee_status.update')->middleware('role_or_permission:update employee_status');
        Route::delete('delete/{id}', [EmployeeStatusController::class, 'delete'])->name('employee_status.delete')->middleware('role_or_permission:delete employee_status');
    });

    //university
    Route::get('/university', [UniversityController::class, 'index'])->name('university.list')->middleware('role_or_permission:view university');
    Route::group(['prefix' => 'university'], function () {
        Route::post('store', [UniversityController::class, 'store'])->name('university.store')->middleware('role_or_permission:create university');
        Route::put('update/{id}', [UniversityController::class, 'update'])->name('university.update')->middleware('role_or_permission:update university');
        Route::delete('delete/{id}', [UniversityController::class, 'delete'])->name('university.delete')->middleware('role_or_permission:delete university');
    });

    //education_level
    Route::get('/education_level', [EducationLevelController::class, 'index'])->name('education_level.list')->middleware('role_or_permission:view education_level');
    Route::group(['prefix' => 'education_level'], function () {
        Route::post('store', [EducationLevelController::class, 'store'])->name('education_level.store')->middleware('role_or_permission:create education_level');
        Route::put('update/{id}', [EducationLevelController::class, 'update'])->name('education_level.update')->middleware('role_or_permission:update education_level');
        Route::delete('delete/{id}', [EducationLevelController::class, 'delete'])->name('education_level.delete')->middleware('role_or_permission:delete education_level');
    });

    //filed_of_study
    Route::get('/filed_of_study', [FiledOfStudyController::class, 'index'])->name('filed_of_study.list')->middleware('role_or_permission:view filed_of_study');
    Route::group(['prefix' => 'filed_of_study'], function () {
        Route::post('store', [FiledOfStudyController::class, 'store'])->name('filed_of_study.store')->middleware('role_or_permission:create filed_of_study');
        Route::put('update/{id}', [FiledOfStudyController::class, 'update'])->name('filed_of_study.update')->middleware('role_or_permission:update filed_of_study');
        Route::delete('delete/{id}', [FiledOfStudyController::class, 'delete'])->name('filed_of_study.delete')->middleware('role_or_permission:delete filed_of_study');
    });

    //time-tables
    Route::get('/time-tables', [TimeTableController::class, 'index'])->name('timeTable.index')->middleware('role_or_permission:view timetables');
    Route::group(['prefix' => 'time-table'], function () {
        Route::get('create', [TimeTableController::class, 'create'])->name('timeTable.create')->middleware('role_or_permission:create timetables');
        Route::post('store', [TimeTableController::class, 'store'])->name('timeTable.store')->middleware('role_or_permission:create timetables');
        Route::get('show/{id}', [TimeTableController::class, 'show'])->name('timeTable.show')->middleware('role_or_permission:view timetables');
        Route::get('edit/{id}', [TimeTableController::class, 'edit'])->name('timeTable.edit')->middleware('role_or_permission:update timetables');
        Route::put('update/{id}', [TimeTableController::class, 'update'])->name('timeTable.update')->middleware('role_or_permission:update timetables');
        Route::delete('delete/{id}', [TimeTableController::class, 'delete'])->name('timeTable.delete')->middleware('role_or_permission:delete timetables');
    });

    //shifts
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.list')->middleware('role_or_permission:view shifts');
    Route::group(['prefix' => 'shift'], function () {
        Route::post('store', [ShiftController::class, 'store'])->name('shift.store')->middleware('role_or_permission:create shifts');
        Route::put('update/{id}', [ShiftController::class, 'update'])->name('shift.update')->middleware('role_or_permission:update shifts');
        Route::delete('delete/{id}', [ShiftController::class, 'delete'])->name('shift.delete')->middleware('role_or_permission:delete shifts');
    });
    Route::get('/shifts/{shift}/details', [ShiftController::class, 'details'])->name('shifts.details');

    //employee-schedules
    Route::get('/employee-schedules', [EmployeeScheduleController::class, 'index'])->name('employeeSchedules.list')->middleware('role_or_permission:view timetables');
    Route::group(['prefix' => 'employee-schedule'], function () {
        Route::post('store', [EmployeeScheduleController::class, 'store'])->name('employeeSchedule.store')->middleware('role_or_permission:create timetables');
        Route::put('update/{id}', [EmployeeScheduleController::class, 'update'])->name('employeeSchedule.update')->middleware('role_or_permission:update timetables');
        Route::delete('delete/{id}', [EmployeeScheduleController::class, 'delete'])->name('employeeSchedule.delete')->middleware('role_or_permission:delete timetables');
        Route::post('check-conflict', [EmployeeScheduleController::class, 'checkConflict'])->name('employeeSchedule.checkConflict');
        Route::post('set-default', [EmployeeScheduleController::class, 'setDefault'])->name('employeeSchedule.setDefault');
    });

    //violation
    Route::get('/violation', [ViolationController::class, 'index'])->name('violations.list')->middleware('role_or_permission:view violations');
    Route::group(['prefix' => 'violation'], function () {
        Route::get('create', [ViolationController::class, 'create'])->name('violation.create')->middleware('role_or_permission:create violations');
        Route::post('store', [ViolationController::class, 'store'])->name('violation.store')->middleware('role_or_permission:create violations');
        Route::get('show/{id}', [ViolationController::class, 'show'])->name('violation.show')->middleware('role_or_permission:view violations');
        Route::get('edit/{id}', [ViolationController::class, 'edit'])->name('violation.edit')->middleware('role_or_permission:update violations');
        Route::put('update/{id}', [ViolationController::class, 'update'])->name('violation.update')->middleware('role_or_permission:update violations');
        Route::patch('/{id}/resolve', [ViolationController::class, 'resolve'])->name('violation.resolve');

        Route::delete('delete/{id}', [ViolationController::class, 'delete'])->name('violation.delete')->middleware('role_or_permission:delete violations');
    });

    //violation_types
    Route::get('/violation_types', [ViolationTypeController::class, 'index'])->name('violation_types.list')->middleware('role_or_permission:view violation_types');
    Route::group(['prefix' => 'violation_types'], function () {
        Route::post('store', [ViolationTypeController::class, 'store'])->name('violation_type.store')->middleware('role_or_permission:create violation_types');
        Route::put('update/{id}', [ViolationTypeController::class, 'update'])->name('violation_type.update')->middleware('role_or_permission:update violation_types');
        Route::delete('delete/{id}', [ViolationTypeController::class, 'delete'])->name('violation_type.delete')->middleware('role_or_permission:delete violation_types');
    });

    //holidays-calendar
    Route::get('holidays-calendar', [HolidayController::class, 'showCalendar']);

    Route::group(['prefix' => 'return-invoice-request'], function () {
        Route::get('/', [ReturnInvoiceRequestController::class, 'index'])->name('return-invoice-request.index')->middleware('role_or_permission:view returnInvoiceRequest');
        Route::get('/show/{id}', [ReturnInvoiceRequestController::class, 'show'])->name('return-invoice-request.show')->middleware('role_or_permission:show returnInvoiceRequest');
        Route::post('/update/{id}', [ReturnInvoiceRequestController::class, 'update']);
        Route::post('/update-waste/{id}', [ReturnInvoiceRequestController::class, 'updateWaste']);
    });

    Route::group(['prefix' => 'return-invoice'], function () {
        Route::get('/', [ReturnInvoiceController::class, 'index'])->name('return-invoice.index')->middleware('role_or_permission:view returnInvoiceRequest');
        Route::get('/{id}', [ReturnInvoiceController::class, 'show'])->name('return-invoice.show')->middleware('role_or_permission:show returnInvoiceRequest');
    });

    Route::group(['prefix' => 'invoice'], function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('invoice.index')->middleware('role_or_permission:view invoice');
        Route::get('/{id}', [InvoiceController::class, 'show'])->name('invoice.show')->middleware('role_or_permission:view invoice');
    });
});
