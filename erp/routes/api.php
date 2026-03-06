<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PrintController;
use Illuminate\Session\Middleware\StartSession;
use App\Http\Controllers\Api\GetInvoiceController;
use App\Http\Controllers\Dashboard\CitiesController;
use App\Http\Controllers\Api\AdminAPIs\RoleController;
use App\Http\Controllers\Api\ClientAPIs\AuthController;
use App\Http\Controllers\Api\ClientAPIs\HomeController;
use App\Http\Controllers\Api\ClientAPIs\RateController;
use App\Http\Controllers\Api\ClientAPIs\OfferController;
use App\Http\Controllers\Api\ClientAPIs\OrderController;
use App\Http\Controllers\Api\WaiterAPIs\TableController;
use App\Http\Controllers\Api\ClientAPIs\ClientController;
use App\Http\Controllers\Api\InventoryAPIs\LineController;
use App\Http\Controllers\Api\InventoryAPIs\SizeController;
use App\Http\Controllers\Api\InventoryAPIs\UnitController;
use App\Http\Controllers\Api\CashierAPIs\addressController;
use App\Http\Controllers\Api\ClientAPIs\TakeawayController;
use App\Http\Controllers\Api\InventoryAPIs\ShelfController;
use App\Http\Controllers\Api\InventoryAPIs\StoreController;
use App\Http\Controllers\Api\KitchenAPIs\KitchenController;
use App\Http\Controllers\Api\AdminAPIs\PermissionController;
use App\Http\Controllers\Api\CashierAPIs\DeliveryController;
use App\Http\Controllers\Api\InventoryAPIs\VendorController;
use App\Http\Controllers\Api\ClientAPIs\GoogleAuthController;
use App\Http\Controllers\Api\ClientAPIs\UserCouponController;
use App\Http\Controllers\Api\CashierAPIs\MenuDishesController;
use App\Http\Controllers\Api\ClientAPIs\MostPopularController;
use App\Http\Controllers\Api\ClientAPIs\OfferDetailController;
use App\Http\Controllers\Api\ClientAPIs\OrderRefundController;
use App\Http\Controllers\Api\InventoryAPIs\DivisionController;

use App\Http\Controllers\Api\WaiterAPIs\WaiterOrderController;
use App\Http\Controllers\Dashboard\NotificationSendController;
use App\Http\Controllers\Api\ClientAPIs\FacebookAuthController;
use App\Http\Controllers\Api\CashierAPIs\CashierOrderController;

use App\Http\Controllers\Api\CashierAPIs\CashierTableController;
use App\Http\Controllers\Api\ClientAPIs\OrderTrackingController;
use App\Http\Controllers\Api\KitchenAPIs\KitchenOrderController;
use App\Http\Controllers\Api\InventoryAPIs\CustomFieldController;

use App\Http\Controllers\Api\InventoryAPIs\OrderReportController;
use App\Http\Controllers\Api\InventoryAPIs\SupplyOrderController;
use App\Http\Controllers\Api\CashierAPIs\CashierBalanceController;
use App\Http\Controllers\Api\CashierAPIs\CashierInvoiceController;
use App\Http\Controllers\Api\DeliveryAPIs\DeliveryOrderController;

use App\Http\Controllers\Api\DeliveryAPIs\DeliveryRouteController;

use App\Http\Controllers\Api\InventoryAPIs\RejectReasonController;

use App\Http\Controllers\Api\ClientAPIs\ClientAddressApiController;
use App\Http\Controllers\Api\ClientAPIs\TableReservationController;
use App\Http\Controllers\Api\DeliveryAPIs\DeliveryBalanceController;

use App\Http\Controllers\Api\InventoryAPIs\StoreInventoryController;
use App\Http\Controllers\Api\InventoryAPIs\PurchaseInvoiceController;
use App\Http\Controllers\Api\KitchenAPIs\KitchenFiltrationController;
use App\Http\Controllers\Api\CustomerServiceAPIs\ComplaintsController;
use App\Http\Controllers\Api\DeliveryAPIs\DeliveryAddressesController;
use App\Http\Controllers\Api\DeliveryAPIs\DeliveryComplaintController;
use App\Http\Controllers\Api\InventoryAPIs\OrderTransactionController;
use App\Http\Controllers\Api\InventoryAPIs\StoreTransactionController;

use App\Http\Controllers\Api\InventoryAPIs\CategoryInventoryController;

use App\Http\Controllers\Api\InventoryAPIs\SupplyOrderReasonController;

use App\Http\Controllers\Api\InventoryAPIs\ProductTransactionController;

use App\Http\Controllers\Api\DeliveryAPIs\DeliveryNotificationsController;
use App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs\CityController;
use App\Http\Controllers\Api\CustomerServiceAPIs\CustomerServiceChatController;
use App\Http\Controllers\Api\CustomerServiceAPIs\CustomerServiceOrderController;
use App\Http\Controllers\Api\CustomerServiceAPIs\AddressCustomerServiceController;

$basePath = base_path("routes");

if (File::exists("{$basePath}/inventory.php")) {
    require "{$basePath}/inventory.php";
}

if (File::exists("{$basePath}/procurement.php")) {
    require "{$basePath}/procurement.php";
}

if (File::exists("{$basePath}/dashboard_api.php")) {
    require "{$basePath}/dashboard_api.php";
}

if (File::exists("{$basePath}/hr.php")) {
    require "{$basePath}/hr.php";
}

if (File::exists("{$basePath}/finance.php")) {
    require "{$basePath}/finance.php";
}

if (File::exists("{$basePath}/settings.php")) {
    require "{$basePath}/settings.php";
}

// Route::any('/print', [CashierOrderController::class, 'print']);
Route::any('/print-kitchen', [CashierOrderController::class, 'printKitchen']);
Route::any('/print-waiter', [CashierOrderController::class, 'printwaiter']);
Route::any('/print-editor-cancel', [CashierOrderController::class, 'EditorCancelPrintKitchen']);


/////////////////////////////////////Chat API & Firebase///////////////////////////////////////////////////

Route::post('/chat/sender', [ChatController::class, 'store']);
Route::post('setToken', [NotificationSendController::class, 'setToken'])->name('firebase.token');
Route::controller(ChatController::class)
    ->prefix('chat')
    ->name('api.chat.')
    ->group(function () {
        Route::post('/markAsRead', 'markAsRead')->name('markAsRead');
        Route::get('/messages/{channel_id}', 'getChatMessages')->name('messages');
        Route::get('/close/{channel_id}', 'closeChat')->name('closeChat');
    });
/////////////////////////////////////Chat API///////////////////////////////////////////////////



/////////////////////////////////////////////////////// Start General routes for all users (uses without auth) /////////////////////////////////////
//Start Country Apis
Route::get('delivery/citiesandareas', [DeliveryAddressesController::class, 'getCitiesAndAreas']);
Route::post('/search-branches', [TakeawayController::class, 'searchBranches']);
Route::post('/checkAvailability', [TakeawayController::class, 'checkAvailability']);
Route::post('/checkOrderCapacity', [TakeawayController::class, 'checkOrderCapacity']);

//end Country
Route::get('/areas/{channel_id}', [addressController::class, 'getAreasByBranch']);
Route::get('/listHotels', [addressController::class, 'listHotel']);

//table-reservations
Route::controller(TableReservationController::class)
    ->prefix('table-reservations')
    ->group(function () {
        Route::post('get-session', 'getSession')->name('table-reservations.get-session');
        Route::post('check-reservation', 'checkReservation')->name('table-reservations.check-reservation');
        Route::post('checkout', 'checkout')->name('table-reservations.checkout');
    });

// Most Popular
Route::get('/popular', [MostPopularController::class, 'getMostPopular'])->name('api.popular');


// Home
Route::get('/home', [HomeController::class, 'index'])->name('api.home');

// User Coupon related routes grouped by UserCouponController
Route::controller(UserCouponController::class)->prefix('user-coupon')->name('api.user_coupon.')->group(function () {
    Route::get('/', 'index')->name('list');
    Route::get('/{id}', 'show')->name('show');
});

//Rates
Route::prefix('rates')->controller(RateController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('show/{id}', 'show');
    Route::post('restore/{id}', 'restore');
});


//apis for menu dishes
Route::get('/menu-categories-lite', [MenuDishesController::class, 'categoriesByBranch'])->name('api.menu.categories.lite');
Route::get('/menu-dishes-lite', [MenuDishesController::class, 'categoryDishesByBranch'])->name('api.menu.categories.lite.category');


Route::get('/menu-dishes', [MenuDishesController::class, 'index'])->name('api.menu.dishes');
// hanan inifinite scroll

Route::get('/menu-dishesE', [MenuDishesController::class, 'indexOptimized'])->name('api.indexOptimized');
Route::get('/menu-ultrafast', [MenuDishesController::class, 'indexUltraFast'])->name('api.menu.ultrafast');
Route::get('/menu-categories', [MenuDishesController::class, 'categories'])->name('api.menu.categories');
Route::get('/dish/details', [MenuDishesController::class, 'getDishDetails']);
/////////////////////////////////////////////////////// End General routes for all users (uses without auth) /////////////////////////////////////

Route::post('orders/request-cancel/change-status', [CashierInvoiceController::class, 'requestChangeStatus']);

Route::middleware(['auth:employee'])->group(function () {

    Route::post('/assign-to-modules', [PermissionController::class, 'assignPermissionsToModules']);

    //module
    Route::group(['prefix' => 'modules'], function () {
        Route::get('/', [PermissionController::class, 'listModules']);
    });

    // End Product
    Route::post('orders/cashier/request-cancel', [CashierOrderController::class, 'requestCancellation']);
    Route::get('/getAreas/{id}', [addressController::class, 'getAreas']);
    Route::get('/getAllAreas', [addressController::class, 'getAllAreas']);
    Route::post('/check_available_areas', [addressController::class, 'check_available_areas']);

    Route::get('/notifications', [DeliveryNotificationsController::class, 'getNotifications']);
    Route::get('/notification/{id}', [DeliveryNotificationsController::class, 'markNotificationAsRead']);

    Route::get('deliveries', [DeliveryController::class, 'index'])->name('deliveries.list');
    Route::post('/orders/change-status/{id}', [DeliveryOrderController::class, 'updateTrackingStatus']);
    Route::post('/customer-service/address/check', [AddressCustomerServiceController::class, 'check']);


    //Start Size
    Route::group(['prefix' => 'size'], function () {
        Route::any('/', [SizeController::class, 'index']);
        Route::any('/add', [SizeController::class, 'store']);
        Route::any('/get', [SizeController::class, 'show']);
        Route::any('/edit', [SizeController::class, 'update']);
        Route::any('/delete', [SizeController::class, 'destroy']);
    });
    //end Size

    Route::get('/contact-us', [DeliveryComplaintController::class, 'contactUs']);
    Route::get('list-branches',  [CustomerServiceOrderController::class, 'listBranches']);
    Route::get('city', [CityController::class, 'index']);
    Route::get('/city/{country_id}', [CitiesController::class, 'show_all']);

    // Start unit
    Route::group(['prefix' => 'unit'], function () {
        Route::get('/', [UnitController::class, 'index']);
        Route::post('store', [UnitController::class, 'store']);
        Route::post('update/{id}', [UnitController::class, 'update']);
        Route::delete('delete/{id}', [UnitController::class, 'delete']);
    });
    // End unit

    //termination

    Route::prefix('store')->group(function () {
        Route::post('/color', [CustomFieldController::class, 'storeColor']);
        Route::post('/size', [CustomFieldController::class, 'storeSize']);
    });

    Route::prefix('notifications')->group(function () {
        Route::post('/send', [NotificationSendController::class, 'send'])->name('notification.send');
    });

    //Roles
    Route::group(['prefix' => 'roles'], function () {
        Route::get('/', [RoleController::class, 'index'])->middleware('role_or_permission_api:view roles');
        Route::post('store', [RoleController::class, 'store'])->middleware('role_or_permission_api:create roles');
        Route::post('update/{id}', [RoleController::class, 'update'])->middleware('role_or_permission_api:update roles');
        Route::post('/employee/assign-role', [RoleController::class, 'assignRole'])->middleware('role_or_permission_api:assign roles');
        Route::get('show/{id}', [RoleController::class, 'show'])->middleware('role_or_permission_api:view roles');
        Route::get('delete/{id}', [RoleController::class, 'destroy'])->middleware('role_or_permission_api:delete roles');
    });
    //Permissions
    Route::group(['prefix' => 'permissions'], function () {
        Route::get('/', [PermissionController::class, 'index']);
        Route::get('/{guard}', [PermissionController::class, 'listOfPermissions']);
        Route::get('/module/{module}', [PermissionController::class, 'listOfPermissionsModule']);

        Route::post('store', [PermissionController::class, 'store'])->middleware('role_or_permission_api:create permissions');
        Route::get('show/{id}', [PermissionController::class, 'show'])->middleware('role_or_permission_api:view permissions');
        Route::post('update/{id}', [PermissionController::class, 'update']);
        Route::post('assignemployeepermission', [PermissionController::class, 'assignEmployeePermission']);
        // Route::post('updateEmployeePermission', [PermissionController::class, 'updateEmployeePermission']);

        Route::get('delete/{id}', [PermissionController::class, 'destroy'])->middleware('role_or_permission_api:delete permissions');
    });

    //purchase_invoice
    Route::prefix('purchase-invoices')->group(function () {
        Route::get('/', [PurchaseInvoiceController::class, 'index'])->name('purchase-invoices.index');
        Route::post('/', [PurchaseInvoiceController::class, 'store'])->name('purchase-invoices.store');
        Route::put('/{id}', [PurchaseInvoiceController::class, 'update'])->name('purchase-invoices.update');
        Route::get('/{id}', [PurchaseInvoiceController::class, 'show'])->name('purchase-invoices.show');
    });

    //Reports
    Route::prefix('reports')->group(function () {

        Route::get('customers/list/orders', [CustomerServiceOrderController::class, 'mostcustomers'])->middleware('role_or_permission_api:view report_customers');

        //purchase-invoices Reports
        Route::prefix('purchase-invoices')->group(function () {
            Route::get('/', [PurchaseInvoiceController::class, 'getPurchaseInvoiceReport']);
        });
    });

    Route::group(['prefix' => 'order_refund'], function () {
        Route::get('/', [OrderRefundController::class, 'index']);
        Route::post('store', [OrderRefundController::class, 'store']);
        Route::post('change_status', [OrderRefundController::class, 'change_status']);
    });

    Route::group(['prefix' => 'order_tracking'], function () {
        Route::post('/', [OrderTrackingController::class, 'index']);
        Route::post('store', [OrderTrackingController::class, 'store']);
    });

    Route::group(['prefix' => 'order_transaction'], function () {
        Route::post('/', [OrderTransactionController::class, 'index']);
        Route::post('store', [OrderTransactionController::class, 'store']);
    });

    Route::group(['prefix' => 'order-report'], function () {
        Route::post('/', [OrderReportController::class, 'OrderReport']);
        Route::post('/details', [OrderReportController::class, 'OrderReportDetails']);
        Route::post('/refund', [OrderReportController::class, 'OrderRefundReport']);
        Route::post('/refund/details', [OrderReportController::class, 'OrderRefundReportDetails']);
    });
    // Start Category
    Route::group(['prefix' => 'category'], function () {
        Route::get('{id}/inventory', [CategoryInventoryController::class, 'getInventory']);
    });

    // vendors
    Route::group(['prefix' => 'vendors'], function () {
        Route::get('vendorList', [VendorController::class, 'index']);
        Route::get('showVendor/{id}', [VendorController::class, 'show']);
        Route::post('addVendor', [VendorController::class, 'store']);
        Route::put('updateVendor/{id}', [VendorController::class, 'update']);
        Route::delete('deleteVendor/{id}', [VendorController::class, 'destroy']);
        Route::post('restoreVendor/{id}', [VendorController::class, 'restore']);
    });


    //Lines
    Route::group(['prefix' => 'lines'], function () {
        Route::get('lineList', [LineController::class, 'index']);
        Route::get('showLine/{id}', [LineController::class, 'show']);
        Route::post('addLine', [LineController::class, 'store']);
        Route::put('updateLine/{id}', [LineController::class, 'update']);
        Route::delete('deleteLine/{id}', [LineController::class, 'destroy']);
        Route::post('restoreLine/{id}', [LineController::class, 'restore']); // Restore soft-deleted line
    });

    // Divisions
    Route::group(['prefix' => 'divisions'], function () {
        Route::get('divisionList', [DivisionController::class, 'index']);
        Route::get('showDivision/{id}', [DivisionController::class, 'show']);
        Route::post('addDivision', [DivisionController::class, 'store']);
        Route::put('updateDivision/{id}', [DivisionController::class, 'update']);
        Route::delete('deleteDivision/{id}', [DivisionController::class, 'destroy']);
        Route::post('restoreDivision/{id}', [DivisionController::class, 'restore']); // Restore route
    });

    //table-reservations
    Route::group(['prefix' => 'table-reservations'], function () {
        Route::post('index', [TableReservationController::class, 'index']);
        Route::post('add', [TableReservationController::class, 'add']);
        Route::post('edit', [TableReservationController::class, 'edit']);
        Route::get('delete/{id}', [TableReservationController::class, 'delete']);
        Route::post('change-status', [TableReservationController::class, 'change_status']);
    });

    // Google and Facebook auth
    Route::middleware([StartSession::class])->group(function () {
        // Google Auth
        Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
        Route::get('/auth/google/call-back', [GoogleAuthController::class, 'callback'])->name('google.callback');
        // Facebook Auth
        Route::get('/auth/facebook/redirect', [FacebookAuthController::class, 'redirect'])->name('facebook.redirect');
        Route::get('/auth/facebook/call-back', [FacebookAuthController::class, 'callback'])->name('facebook.callback');
    });

    // Offers routes
    Route::controller(OfferController::class)
        ->prefix('offers')
        ->name('offers.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('store', 'save')->name('store');
            Route::get('{id}', 'show')->name('show');
            Route::put('update/{id}', 'save')->name('update');
            Route::delete('delete/{id}', 'destroy')->name('destroy');
            Route::post('restore/{id}', 'restore')->name('restore');
        });

    // Offer details routes
    Route::controller(OfferDetailController::class)
        ->prefix('offer/details')
        ->name('offer.details.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('store', 'save')->name('store');
            Route::get('{id}', 'show')->name('show');
            Route::put('update/{id}', 'save')->name('update');
            Route::delete('delete/{id}', 'destroy')->name('destroy');
            Route::post('restore/{id}', 'restore')->name('restore');
        });

    Route::get('cashier-branch-safe/{id}', [CashierBalanceController::class, 'showCashierData'])->name('cashier.showCashierData');
});

Route::middleware(['employee.auth', 'employee.flag:waiter'])->group(function () {

    Route::post('waiter/table/change-status/{id}', [TableController::class, 'update']);

    Route::post('waiter/request/split/order',  [WaiterOrderController::class, 'requestSplit']);
    Route::post('waiter/request/merge/order',  [WaiterOrderController::class, 'requestMerge']);


    Route::group(['prefix' => 'waiter/orders'], function () {
        Route::get('/list', [WaiterOrderController::class, 'listOrders']);
        Route::get('/order-details/{id}', [WaiterOrderController::class, 'orderDetails']);
        Route::get('/order-invoice/{id}', [WaiterOrderController::class, 'orderInvoice']);
        Route::post('/order-place/{type}', [WaiterOrderController::class, 'orderPlace_v2']);
        Route::post('/order-view-item/{type}/{id}', [WaiterOrderController::class, 'orderViewItem']);
        Route::post('/order-edit-item/{type}', [WaiterOrderController::class, 'orderEditItem']);
        Route::post('/order-cancel', [WaiterOrderController::class, 'orderCancel']);
        Route::post('/order-change-table', [WaiterOrderController::class, 'changeOrderTable']);
        Route::post('/print', [WaiterOrderController::class, 'printInvoice']);

    });

    Route::post('waiter/send-order-to-cashier', [WaiterOrderController::class, 'sendOrderToCashier']);
});

Route::middleware(['employee.auth', 'employee.flag:driver'])->group(function () {
    Route::group(['prefix' => 'orders'], function () {
        Route::get('/order-location/{id}', [DeliveryOrderController::class, 'orderLocation']);
        Route::get('/order-details/{id}', [DeliveryOrderController::class, 'orderDetails']);
        Route::get('/active-orders', [DeliveryOrderController::class, 'activeOrders']);
        Route::get('/past-orders', [DeliveryOrderController::class, 'pastOrders']);
        Route::get('/recent-orders', [DeliveryOrderController::class, 'recentOrders']);
    });


    Route::get('delivery-route', [DeliveryRouteController::class, 'getOptimizedRoute']);

    Route::get('delivery/current-balance', [DeliveryBalanceController::class, 'getCurrent'])->name('currentBalance.delivery');

    Route::post('delivery/close-balance', [DeliveryBalanceController::class, 'closeBalance'])->name('closeBalance.delivery');

    Route::get('delivery/notifications', [DeliveryNotificationsController::class, 'getNotifications']);

    Route::post('delivery/complaint', [DeliveryComplaintController::class, 'store'])->name('delivery.complaint');
});

Route::get('delivery/complaint-reasons', [DeliveryComplaintController::class, 'index'])->name('delivery.complaint.reasons');


Route::middleware(['employee.auth', 'employee.flag:cashier'])->group(function () {

    // Route::group(['prefix' => 'orders'], function () {
    //     Route::get('/', [CashierOrderController::class, 'listOrders']);
    //     Route::get('/list', [CashierOrderController::class, 'listOrdersDetails']);
    //     Route::get('/orderDetails/{id}', [CashierOrderController::class, 'orderDetails']);
    //     Route::post('/cashier/store/{type}', [CashierOrderController::class, 'placeOrder_v2']);
    //     Route::post('/cashier/update/order', [CashierOrderController::class, 'updateOrder']);
    //     Route::post('/change-orderStatus/{id}', [CashierOrderController::class, 'updateOrderStatus']);
    //     Route::get('/cancel', [CashierOrderController::class, 'cancel']);
    //     Route::post('/cashier/order-cancel', [CashierOrderController::class, 'orderCancel']);
    //     Route::post('/cashier/order-edit-item/{type}', [CashierOrderController::class, 'orderEditItem']);
    //     Route::get('/cashier/order-view-item/{type}/{id}', [CashierOrderController::class, 'orderViewItem']);
    // });

    Route::group(['prefix' => 'orders'], function () {
        Route::get('/', [CashierOrderController::class, 'listOrders']);
        Route::get('/list', [CashierOrderController::class, 'listOrdersDetails']);
        Route::get('/listnew/{id}', [CashierOrderController::class, 'orderDetailsById']);

        Route::get('/listE', [CashierOrderController::class, 'listOrdersDetailsEnchance']);
        Route::get('/orderDetails/{id}', [CashierOrderController::class, 'orderDetails']);
        Route::post('/tableorderDetails', [CashierTableController::class, 'TableorderDetails']);
        Route::post('/order-change-table', [CashierTableController::class, 'changeOrderTable']);
        Route::post('/cashier/store/{type}', [CashierOrderController::class, 'placeOrder_v2']);
        Route::post('/cashier/offline/store/{type}', [CashierOrderController::class, 'orderoffline']);
        Route::post('/cashier/update/order', [CashierOrderController::class, 'updateOrder']);
        Route::post('/change-orderStatus/{id}', [CashierOrderController::class, 'updateOrderStatus']);
        Route::get('/cancel', [CashierOrderController::class, 'cancel']);
        Route::post('/cashier/order-cancel', [CashierOrderController::class, 'orderCancel']);
        Route::post('/cashier/order-edit-item/{type}', [CashierOrderController::class, 'orderEditItem']);
        Route::get('/cashier/order-view-item/{type}/{id}', [CashierOrderController::class, 'orderViewItem']);
        Route::post('/split', [CashierOrderController::class, 'requestSplit']);
        Route::post('/merge', [CashierOrderController::class, 'mergeRequest']);
        Route::post('/changeOrderType', [CashierOrderController::class, 'changeOrderType']);
    });

    Route::group(['prefix' => 'invoices'], function () {
        Route::get('/', [CashierInvoiceController::class, 'listOrdersInvoices']);
        // listOrdersInvoicesEnchance
        Route::get('/d', [CashierInvoiceController::class, 'listOrdersInvoicesEnchance']);
  
        Route::get('/invoice-details/{id}', [CashierInvoiceController::class, 'orderInvoiceDetails']);
        Route::get('/invoice/{id}', [CashierInvoiceController::class, 'invoiceDetails']);
        Route::post('/update/{id}', [CashierInvoiceController::class, 'updateOrderInvoice']);
        Route::post('/print', [CashierInvoiceController::class, 'printInvoice']);
    });

    Route::post('cashier/add/address', [addressController::class, 'store'])->name('cashier.add.address');



    Route::post('cashier/get-open-balance', [CashierBalanceController::class, 'getOpenBalance'])->name('get.openBalance.cashier');
    Route::post('cashier/open-balance', [CashierBalanceController::class, 'openBalance'])->name('openBalance.cashier');

    Route::post('cashier/get-close-balance', [CashierBalanceController::class, 'getCloseBalance'])->name('get.closeBalance.cashier');
    Route::post('cashier/get-current-balance', [CashierBalanceController::class, 'getCurrentBalance'])->name('get.currentBalance.cashier');
    Route::post('cashier/close-balance', [CashierBalanceController::class, 'closeBalance'])->name('closeBalance.cashier');

    Route::post('cashier/balances', [CashierBalanceController::class, 'getBalancesForBranchSafe'])->name('cashier.balancesForBranchSafe');
    Route::post('cashier/send-branch-safe', [CashierBalanceController::class, 'sendToBranchSafe'])->name('cashier.sendToBranchSafe');
    Route::get('cashier/branch-safe/{id}', [CashierBalanceController::class, 'showCashierData']);

    Route::get('cashier/shift-orders-count/{cashierMachineId}', [CashierBalanceController::class, 'getOrderCountsForShift'])->name('cashier.getShiftOrdersCount');

    Route::post('makeCancelRequest', [GetInvoiceController::class, 'cancel']);
});

Route::middleware(['employee.auth', 'employee.flag:customer_service'])->group(function () {


    Route::group(['prefix' => 'customer-service'], function () {
        Route::group(['prefix' => '/orders'], function () {
            Route::get('/list', [CustomerServiceOrderController::class, 'listOrders']);
            Route::get('/order-details/{id}', [CustomerServiceOrderController::class, 'orderDetails']);
            Route::post('/store/api', [CustomerServiceOrderController::class, 'placeOrder']);
        });
        Route::get('/chats', [CustomerServiceChatController::class, 'getActiveChats']);
        Route::get('/notifications', [NotificationSendController::class, 'index'])->name('customer-service.notifications');
        Route::post('/notifications', [NotificationSendController::class, 'read'])->name('customer-service.notifications.read');

        Route::get('/hanging-orders', [CustomerServiceOrderController::class, 'hangingOrders']);
        Route::get('/hanging-orders-details', [CustomerServiceOrderController::class, 'hangingOrdersDetails']);
        Route::post('/hanging-orders/change-status', [CustomerServiceOrderController::class, 'changeStatus'])->name('customer-service.hanging-order.change-status');
        Route::post('/hanging-orders/send-manager', [CustomerServiceOrderController::class, 'sendRequest'])->name('customer-service.hanging-order.send-manager');


        Route::group(['prefix' => '/complaints'], function () {
            Route::get('/list', [ComplaintsController::class, 'index'])->name('customer-service.complaints.list');
            Route::post('/store', [ComplaintsController::class, 'store'])->name('customer-service.complaints.store');
            Route::post('/add-comment', [ComplaintsController::class, 'addComment'])->name('customer-service.add-comment');
            Route::post('/change-status', [ComplaintsController::class, 'changeStatus'])->name('customer-service.change-status');
            Route::post('/send-manager', [ComplaintsController::class, 'sendRequest'])->name('customer-service.send-manager');
        });
        Route::group(['prefix' => '/address'], function () {
            Route::get('/check/{id}', [AddressCustomerServiceController::class, 'checkDelivery']);
            Route::post('/add', [AddressCustomerServiceController::class, 'store']);
        });

        Route::get('/citiesandareas', [AddressCustomerServiceController::class, 'getCitiesAndAreas']);
        Route::get('sent/cashier/{id}',  [CustomerServiceOrderController::class, 'sendToCashier']);
    });
});

Route::middleware(['employee.auth', 'employee.flag:Head Chef'])->group(function () {

    Route::group(['prefix' => 'kitchen/dish-filtration'], function () {
        Route::post('add', [KitchenFiltrationController::class, 'dishFiltration'])->name('kitchen.dish-filtration.add');
        Route::get('get', [KitchenFiltrationController::class, 'getDishFiltration'])->name('kitchen.get-dish-filtration.get');
    });

    //Route::get('kitchen/split-dishes-on-order', [KitchenFiltrationController::class, 'splitDishesOnOrders'])->name('kitchen.split-dishes-on-order');
    Route::get('kitchen/split-dishes-on-order', [KitchenFiltrationController::class, 'splitDishesOnOrders'])->name('kitchen.split-dishes-on-order');
    Route::post('kitchen/split-dishes-on-all-order', [KitchenFiltrationController::class, 'splitDishesOnAllOrders'])->name('kitchen.split-dishes-on-all-order');
    Route::get('kitchen/profile', [KitchenFiltrationController::class, 'profile'])->name('kitchen.profile');


    Route::group(['prefix' => 'kitchen'], function () {
        Route::group(['prefix' => '/orders'], function () {
            Route::get('/list', [KitchenOrderController::class, 'listOrders']);
            Route::post('/update-dish-status', [KitchenOrderController::class, 'updateDishStatus']);
            Route::get('/order-details/{id}', [KitchenOrderController::class, 'orderDetails']);
            Route::post('/order-dish-details', [KitchenController::class, 'orderDishDetails']);
        });
        Route::post('/dish-ingrediant', [KitchenController::class, 'getDishIngrediant']);
        Route::post('/dish-ingrediant/steps', [KitchenController::class, 'getIngrediantSteps']);
    });
});


/////////////////////////////////////////////////////// Start routes for Client App (uses api guard auth) /////////////////////////////////////

/////////////////////////////////////Auth Client API///////////////////////////////////////////////////

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('/verify-phone', [AuthController::class, 'verifyPhone']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
/////////////////////////////////////Auth Client API///////////////////////////////////////////////////

Route::group(['middleware' => ['auth:api']], function () {
    //auth apis
    Route::any('logout', [AuthController::class, 'logout']);
    // User Favourites
    Route::controller(HomeController::class)->group(function () {
        Route::get('/favourite-user', 'showFavorites')->name('favourite.user');
        Route::post('/favourite-user', 'storeFavorite')->name('favourite.user.store');
        Route::delete('/unfavourite-user/{id}', 'deleteFavorite')->name('unfavourite.user');
    });


    Route::group(['prefix' => 'order'], function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('store', [OrderController::class, 'store']);
        Route::get('reOrder', [OrderController::class, 'reOrder']);
        Route::get('cancel', [OrderController::class, 'cancel']);
        Route::post('invoice', [OrderController::class, 'orderInvoice']);
        Route::post('refund', [OrderController::class, 'orderRefund']);
        Route::post('paymentTransaction', [OrderController::class, 'paymentTransaction']);
    });
    Route::prefix('user')->group(function () {
        // User Profile
        Route::controller(ClientController::class)
            ->group(function () {
                Route::get('profile', 'viewProfile');
                Route::post('profile/update', 'updateProfile');
                Route::post('profile/deactivate', 'deactivateProfile');
                Route::post('changePassword', 'changePassword');
            });

        // User Orders
        Route::controller(OrderTrackingController::class)
            ->group(function () {
                Route::get('orders', 'listOrders');
                Route::get('orders/statuses', 'ordersStatuses');
                Route::get('orders/track/{id}', 'trackOrder');
                Route::get('order/paymentDetails/{id}', 'paymentDetails');
            });
    });

    // User order checkout
    Route::post('checkout', [OrderController::class, 'Checkout']);

    // User Reservation
    Route::controller(TableReservationController::class)
        ->prefix('table-reservations')
        ->name('table-reservations.')
        ->group(function () {
            Route::get('reservation/track/{id}', 'trackReservation')->name('track');
            Route::get('reservation/paymentDetails/{id}', 'reservationPaymentDetails')->name('paymentDetails');
            Route::get('reservation/cancel/{id}', 'cancelReservation')->name('cancel');
            Route::post('place-reservation', 'placeReservation')->name('place-reservation');
            Route::post('place_order', 'store')->name('place-order');
        });
    //  address
    Route::controller(ClientAddressApiController::class)
        ->prefix('address')
        ->name('address.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('show/{id}', 'show')->name('show');
            Route::post('store', 'store')->name('store');
            Route::post('update/{id}', 'update')->name('update');
            Route::delete('delete/{id}', 'destroy')->name('destroy');
            Route::post('restore/{id}', 'restore')->name('restore');
            Route::get('default/{id}', 'makeDefault')->name('makeDefault');
        });


    //  Transactions
    Route::prefix('transactions')->group(function () {
        // Store Transactions
        Route::controller(StoreTransactionController::class)->group(function () {
            Route::get('store', 'index')->name('transactions.store.index');
            Route::post('add', 'store')->name('transactions.store.add');
            Route::get('showStore/{id}', 'show')->name('transactions.store.show');
        });

        // Product Transactions
        Route::controller(ProductTransactionController::class)->group(function () {
            Route::get('products', 'index')->name('transactions.products.index');
            Route::get('showProduct/{id}', 'show')->name('transactions.products.show');
        });
    });
    //Rates
    Route::prefix('rates')->controller(RateController::class)->group(function () {
        Route::post('store', 'store');
        Route::put('update/{id}', 'update');
        Route::delete('delete/{id}', 'destroy');
    });
});
/////////////////////////////////////////////////////// End routes for Client App (uses api guard auth) /////////////////////////////////////


Route::post('editInvoice', [GetInvoiceController::class, 'edit']);
Route::post('getInvoice', [GetInvoiceController::class, 'index']);
Route::post('mergeInvoiceDetails', [GetInvoiceController::class, 'mergeInvoiceDetails']);


// E-Receipt routes
// Route::get('/test/{id}', [EReceiptController::class, 'invoiceSubmission']);
