<?php

use App\Http\Controllers\Api\InventoryAPIs\AuditController;
use App\Http\Controllers\Api\InventoryAPIs\AuditTypeController;
use App\Http\Controllers\Api\InventoryAPIs\CustomFieldController;
use App\Http\Controllers\Api\InventoryAPIs\DirectSupplyIssueTypeController;
use App\Http\Controllers\Api\InventoryAPIs\DirectSupplyPermissionController;
use App\Http\Controllers\Api\InventoryAPIs\DirectSupplyPermissionStatusSettingController;
use App\Http\Controllers\Api\InventoryAPIs\DiscrepancyReasonController;
use App\Http\Controllers\Api\InventoryAPIs\InventoryEmployeeController;
use App\Http\Controllers\Api\InventoryAPIs\InventoryLocationController;
use App\Http\Controllers\Api\InventoryAPIs\InventoryPackagingConfigrationController;
use App\Http\Controllers\Api\InventoryAPIs\InventorySettingController;
use App\Http\Controllers\Api\InventoryAPIs\OrderWasteController;
use App\Http\Controllers\Api\InventoryAPIs\ProductController;
use App\Http\Controllers\Api\InventoryAPIs\PurchaseOrderController;
use App\Http\Controllers\Api\InventoryAPIs\PurchaseRequestController;
use App\Http\Controllers\Api\InventoryAPIs\ReasoneReturnDspController;
use App\Http\Controllers\Api\InventoryAPIs\ReasonPurchaseRequestController;
use App\Http\Controllers\Api\InventoryAPIs\RejectPurchaseRequestController;
use App\Http\Controllers\Api\InventoryAPIs\RejectReasonController;
use App\Http\Controllers\Api\InventoryAPIs\ReturnDspController;
use App\Http\Controllers\Api\InventoryAPIs\ShelfController;
use App\Http\Controllers\Api\InventoryAPIs\StatisticsController;
use App\Http\Controllers\Api\InventoryAPIs\StorageLocationController;
use App\Http\Controllers\Api\InventoryAPIs\StoreController;
use App\Http\Controllers\Api\InventoryAPIs\StoreInventoryController;
use App\Http\Controllers\Api\InventoryAPIs\SupplyOrderController;
use App\Http\Controllers\Api\InventoryAPIs\SupplyOrderReasonController;
use App\Http\Controllers\Api\InventoryAPIs\UnitController;
use App\Http\Controllers\Api\InventoryAPIs\WarehouseController;
use App\Http\Controllers\Api\InventoryAPIs\WasteReasonController;
use App\Http\Controllers\Api\InventoryAPIs\WasteReportController;
use App\Http\Controllers\Api\InventoryAPIs\ZoneController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:employee'])->group(function () {

    Route::prefix('inventory')->group(function () {
        // Shelves
        Route::group(['prefix' => 'shelves'], function () {
            Route::get('shelfList', [ShelfController::class, 'index']);
            Route::get('showShelf/{id}', [ShelfController::class, 'show']);
            Route::post('addShelf', [ShelfController::class, 'store']);
            Route::put('updateShelf/{id}', [ShelfController::class, 'update']);
            Route::delete('deleteShelf/{id}', [ShelfController::class, 'destroy']);
            Route::post('restoreShelf/{id}', [ShelfController::class, 'restore']);
        });
        //store
        Route::group(['prefix' => 'stores'], function () {
            //stores handling
            Route::get('storeList', [StoreController::class, 'index'])->middleware('role_or_permission_api:view store');
            Route::get('showStore/{id}', [StoreController::class, 'show'])->middleware('role_or_permission_api:view store');
            Route::post('addStore', [StoreController::class, 'store'])->middleware('role_or_permission_api:create store');
            Route::post('updateStore/{id}', [StoreController::class, 'update'])->middleware('role_or_permission_api:update store');
            Route::delete('deleteStore/{id}', [StoreController::class, 'destroy'])->middleware('role_or_permission_api:delete store');
            Route::post('restoreStore/{id}', [StoreController::class, 'restore'])->middleware('role_or_permission_api:restore store');
            Route::get('{id}/inventory', [StoreInventoryController::class, 'getInventory']);
        });
        // Product
        Route::group(['prefix' => 'product'], function () {
            Route::get('/', [ProductController::class, 'indexV2'])->middleware('role_or_permission_api:view products');
            Route::get('/product_brand', [ProductController::class, 'index'])->middleware('role_or_permission_api:view products');
            Route::post('store', [ProductController::class, 'storeV2'])->middleware('role_or_permission_api:create products');
            Route::get('show/{id}', [ProductController::class, 'showProduct'])->middleware('role_or_permission_api:view products');
            Route::post('update/{id}', [ProductController::class, 'updateV2'])->middleware('role_or_permission_api:update products');
            Route::delete('delete/{id}', [ProductController::class, 'deleteV2'])->middleware('role_or_permission_api:delete products');
            Route::post('/quantity', [ProductController::class, 'getProductQuantity']);
            Route::get('/productBrand/units/{id}', [ProductController::class, 'getProductBrandUnits']);
            Route::post('/stockUpdate', [ProductController::class, 'storeProductTransaction']);
        });

        Route::post('/creategroupofpermissions', [InventoryEmployeeController::class, 'createGroupOfPermissions'])
            ->middleware('role_or_permission_api:create GroupOfPermissions');

        // Employees and there permissions and rolee
        Route::group(['prefix' => 'employees'], function () {
            Route::get('/access/{id}', [InventoryEmployeeController::class, 'getEmployeeRolesAndPermissions']);
            Route::post('/assign-permissions', [InventoryEmployeeController::class, 'assignPermissionsToEmployee'])
                ->middleware('role_or_permission_api:Assign EmpyloyeePermissions');
            Route::get('/list', [InventoryEmployeeController::class, 'getEmployees'])->middleware('role_or_permission_api:view inventory_employees');
            Route::get('/show/{id}', [InventoryEmployeeController::class, 'showEmployee'])->middleware('role_or_permission_api:view inventory_employees');
            Route::post('create', [InventoryEmployeeController::class, 'createEmployee']);
            Route::post('update/{id}', [InventoryEmployeeController::class, 'updateEmployee'])->middleware('role_or_permission_api:update inventory_employees');
            Route::delete('delete/{id}', [InventoryEmployeeController::class, 'destroyEmployee'])->middleware('role_or_permission_api:delete inventory_employees');
            Route::post('/dashboardAccount', [InventoryEmployeeController::class, 'AccessDashboard'])->middleware('role_or_permission_api:changeStatus AccessDashboard');
            Route::get('/listRoles', [InventoryEmployeeController::class, 'getInventoryRoles']);
        });
        Route::group(['prefix' => 'supply_orders'], function () {

            Route::get('/', [SupplyOrderController::class, 'index'])
                ->middleware('role_or_permission_api:view supply_orders');
            Route::get('/autogeneratednumber', [SupplyOrderController::class, 'createAutoGeneratedNumber']);
            Route::post('/store', [SupplyOrderController::class, 'store'])
                ->middleware('role_or_permission_api:create supply_orders');

            Route::get('/show/{id}', [SupplyOrderController::class, 'show'])
                ->middleware('role_or_permission_api:view supply_orders');

            Route::post('/update/{id}', [SupplyOrderController::class, 'update'])
                ->middleware('role_or_permission_api:update supply_orders');

            Route::delete('/delete/{id}', [SupplyOrderController::class, 'destroy'])
                ->middleware('role_or_permission_api:delete supply_orders');

            Route::get('/submit/{id}', [SupplyOrderController::class, 'submit'])
                ->middleware('role_or_permission_api:submit supply_orders');

            Route::get('/approve/{id}', [SupplyOrderController::class, 'approve'])
                ->middleware('role_or_permission_api:approve supply_orders');

            Route::post('/reject/{id}', [SupplyOrderController::class, 'reject'])
                ->middleware('role_or_permission_api:reject supply_orders');
        });

        //audits//

        Route::prefix('audits')->group(function () {
            Route::get('/results/{id}', [AuditController::class, 'getAuditReport'])->middleware('role_or_permission_api:view audits');
            Route::get('/', [AuditController::class, 'index'])->middleware('role_or_permission_api:view audits');
            Route::post('/store', [AuditController::class, 'store'])->middleware('role_or_permission_api:create audits');
            Route::get('/show/{id}', [AuditController::class, 'show'])->middleware('role_or_permission_api:view audits');
            Route::post('/update/{id}', [AuditController::class, 'update'])->middleware('role_or_permission_api:update audits');
            Route::delete('/delete/{id}', [AuditController::class, 'destroy'])->middleware('role_or_permission_api:delete audits');
            Route::post('/add-items/{id}', [AuditController::class, 'addItems'])->middleware('role_or_permission_api:update audits');
            Route::post('/update-items/{id}', [AuditController::class, 'addItems'])->middleware('role_or_permission_api:update audits');
            Route::post('/change-status/{id}', [AuditController::class, 'changeStatus'])->middleware('role_or_permission_api:update audits');
            Route::get('/products/{id}', [AuditController::class, 'getAuditProducts'])->middleware('role_or_permission_api:view audits');
        });



        // ===== Supply Order Reasons CRUD =====
        Route::prefix('supply-order-reasons')->group(function () {
            // List all reasons
            Route::get('/', [SupplyOrderReasonController::class, 'index'])->middleware('role_or_permission_api:view supply-order-reasons');

            // Create a new reason
            Route::post('/store', [SupplyOrderReasonController::class, 'store'])->middleware('role_or_permission_api:create supply-order-reasons');

            // Update existing reason
            Route::post('/update/{id}', [SupplyOrderReasonController::class, 'update'])->middleware('role_or_permission_api:update supply-order-reasons');

            // Show single reason by ID
            Route::get('/show/{id}', [SupplyOrderReasonController::class, 'show'])->middleware('role_or_permission_api:view supply-order-reasons');

            // Soft delete a reason
            Route::delete('/delete/{id}', [SupplyOrderReasonController::class, 'destroy'])->middleware('role_or_permission_api:delete supply-order-reasons');
        });

        Route::group(['prefix' => 'reject-reasons'], function () {
            Route::get('/', [RejectReasonController::class, 'index'])
                ->middleware('role_or_permission_api:view reject_reasons');

            Route::get('/show/{id}', [RejectReasonController::class, 'show'])
                ->middleware('role_or_permission_api:view reject_reasons');

            Route::post('/store', [RejectReasonController::class, 'store'])
                ->middleware('role_or_permission_api:create reject_reasons');

            Route::post('/update/{id}', [RejectReasonController::class, 'update'])
                ->middleware('role_or_permission_api:update reject_reasons');

            Route::delete('/delete/{id}', [RejectReasonController::class, 'destroy'])
                ->middleware('role_or_permission_api:delete reject_reasons');
        });

        // DirectSupplyPermission
        Route::prefix('dsp')->name('dsp.')->group(function () {
            Route::get('/autogeneratednumber', [DirectSupplyPermissionController::class, 'createAutoGeneratedNumber']);
            Route::get('/', [DirectSupplyPermissionController::class, 'index'])->middleware('role_or_permission_api:view DirectSupplyPermission');
            Route::post('/store', [DirectSupplyPermissionController::class, 'store'])->middleware('role_or_permission_api:create DirectSupplyPermission');
            Route::post('/reviewandaction/{id}', [DirectSupplyPermissionController::class, 'update'])->middleware('role_or_permission_api:update DirectSupplyPermission');
            Route::get('/show/{id}', [DirectSupplyPermissionController::class, 'show'])->middleware('role_or_permission_api:view DirectSupplyPermission');
            Route::post('change-status/{id}', [DirectSupplyPermissionController::class, 'updateStatus'])->middleware('role_or_permission_api:changeStatus DirectSupplyPermission');
            Route::get('listStatus', [DirectSupplyPermissionController::class, 'listStatus']);

            Route::prefix('issuetypes')->group(function () {
                Route::get('/', [DirectSupplyIssueTypeController::class, 'index'])->middleware('role_or_permission_api:view direct_supply_issue_type');
                Route::get('/{id}', [DirectSupplyIssueTypeController::class, 'show'])->middleware('role_or_permission_api:view direct_supply_issue_type');
                Route::post('/create', [DirectSupplyIssueTypeController::class, 'store'])->middleware('role_or_permission_api:create direct_supply_issue_type');
                Route::post('/update/{id}', [DirectSupplyIssueTypeController::class, 'update'])->middleware('role_or_permission_api:update direct_supply_issue_type');
                Route::delete('/delete/{id}', [DirectSupplyIssueTypeController::class, 'destroy'])->middleware('role_or_permission_api:delete direct_supply_issue_type');
            });
        });
        //waste report
        Route::prefix('wastreport')->group(function () {
            Route::get('/autogeneratednumber', [WasteReportController::class, 'createAutoGeneratedNumber']);
            Route::get('/', [WasteReportController::class, 'index'])->middleware('role_or_permission_api:view waste_report');
            Route::post('/store', [WasteReportController::class, 'store'])->middleware('role_or_permission_api:create waste_report');
            Route::post('/update/{id}', [WasteReportController::class, 'update'])->middleware('role_or_permission_api:update waste_report');
            Route::delete('/delete/{id}', [WasteReportController::class, 'destroy'])->middleware('role_or_permission_api:delete waste_report');

            Route::post('/review/{id}', [WasteReportController::class, 'review'])->middleware('role_or_permission_api:update waste_report');
            Route::get('/show/{id}', [WasteReportController::class, 'show'])->middleware('role_or_permission_api:view waste_report');
            Route::post('change-status/{id}', [WasteReportController::class, 'changeStatus'])->middleware('role_or_permission_api:changeStatus waste_report');
        });

        // DirectSupplyPermissionStatusSetting
        Route::group(['prefix' => 'direct_supply_permission_status_setting'], function () {
            Route::get('/index', [DirectSupplyPermissionStatusSettingController::class, 'index'])->middleware('role_or_permission_api:view DirectSupplyPermissionStatusSetting');
            Route::post('store', [DirectSupplyPermissionStatusSettingController::class, 'store'])->middleware('role_or_permission_api:create DirectSupplyPermissionStatusSetting');
            Route::post('update/{id}', [DirectSupplyPermissionStatusSettingController::class, 'update'])->middleware('role_or_permission_api:update DirectSupplyPermissionStatusSetting');
            Route::delete('delete/{id}', [DirectSupplyPermissionStatusSettingController::class, 'destroy'])->middleware('role_or_permission_api:delete DirectSupplyPermissionStatusSetting');
            Route::post('reorder', [DirectSupplyPermissionStatusSettingController::class, 'reorder'])->middleware('role_or_permission_api:reorder DirectSupplyPermissionStatusSetting');
        });

        // audit type
        Route::group(['prefix' => 'audit-type'], function () {
            Route::get('/', [AuditTypeController::class, 'index'])->middleware('role_or_permission_api:view audit type');
        });

        // inventory-location
        Route::group(['prefix' => 'inventory-location'], function () {
            Route::get('/', [InventoryLocationController::class, 'index'])->middleware('role_or_permission_api:view inventory-location');
        });
        // discrepancy reason
        Route::group(['prefix' => 'discrepancy-reason'], function () {
            Route::get('/', [DiscrepancyReasonController::class, 'index'])->middleware('role_or_permission_api:view discrepancy reason');
            Route::get('/{id}', [DiscrepancyReasonController::class, 'show'])->middleware('role_or_permission_api:view discrepancy reason');
            Route::post('/', [DiscrepancyReasonController::class, 'store'])->middleware('role_or_permission_api:create discrepancy reason');
            Route::post('/{id}', [DiscrepancyReasonController::class, 'update'])->middleware('role_or_permission_api:update discrepancy reason');
            Route::delete('/{id}', [DiscrepancyReasonController::class, 'delete'])->middleware('role_or_permission_api:delete discrepancy reason');
        });
        //reasone return dsp
        Route::group(['prefix' => 'reason-return-dsp'], function () {
            Route::get('/', [ReasoneReturnDspController::class, 'index'])->middleware('role_or_permission_api:view reason_return_dsp');
            Route::get('/{id}', [ReasoneReturnDspController::class, 'show'])->middleware('role_or_permission_api:view reason_return_dsp');
            Route::post('/', [ReasoneReturnDspController::class, 'store'])->middleware('role_or_permission_api:create reason_return_dsp');
            Route::post('/{id}', [ReasoneReturnDspController::class, 'update'])->middleware('role_or_permission_api:update reason_return_dsp');
            Route::delete('/{id}', [ReasoneReturnDspController::class, 'delete'])->middleware('role_or_permission_api:delete reason_return_dsp');
        });

        // return dsp
        Route::group(['prefix' => 'return-dsp'], function () {
            Route::get('/', [ReturnDspController::class, 'index'])->middleware('role_or_permission_api:view return_dsp');
            Route::get('/{id}', [ReturnDspController::class, 'show'])->middleware('role_or_permission_api:view return_dsp');
            Route::post('/', [ReturnDspController::class, 'store'])->middleware('role_or_permission_api:create return_dsp');
            Route::post('/{id}', [ReturnDspController::class, 'update'])->middleware('role_or_permission_api:update return_dsp');
            Route::post('/approveOrReject/{id}', [ReturnDspController::class, 'approveOrReject'])->middleware('role_or_permission_api:approveOrReject return_dsp');
            Route::delete('/{id}', [ReturnDspController::class, 'delete'])->middleware('role_or_permission_api:delete return_dsp');
        });
        Route::get('activity-logs', [PurchaseRequestController::class, 'getLogs'])->middleware('role_or_permission_api:view activity_logs');

        //purchase request
        Route::group(['prefix' => 'purchase-request'], function () {
            Route::get('/suggestions', [PurchaseRequestController::class, 'getSuggestion'])->middleware('role_or_permission_api:view purchase_requests Suggestion');
            Route::get('/autogeneratednumber', [PurchaseRequestController::class, 'createAutoGeneratedNumber']);
            Route::get('/', [PurchaseRequestController::class, 'index'])->middleware('role_or_permission_api:view purchase_requests');
            Route::get('/activityLog', [PurchaseRequestController::class, 'getLogActions']);
            Route::get('/{id}', [PurchaseRequestController::class, 'show'])->middleware('role_or_permission_api:view purchase_requests');
            Route::post('/', [PurchaseRequestController::class, 'store'])->middleware('role_or_permission_api:create purchase_requests');
            Route::post('/{id}', [PurchaseRequestController::class, 'update'])->middleware('role_or_permission_api:update purchase_requests');
            Route::post('/approveOrReject/{id}', [PurchaseRequestController::class, 'approveOrReject'])->middleware('role_or_permission_api:approveOrReject purchase_requests');
            Route::delete('/{id}', [PurchaseRequestController::class, 'delete'])->middleware('role_or_permission_api:delete purchase_requests');
        });

        //reason purchase request
        Route::group(['prefix' => 'reason-purchase-request'], function () {
            Route::get('/', [ReasonPurchaseRequestController::class, 'index'])->middleware('role_or_permission_api:view reason_purchase_requests');
            Route::get('/{id}', [ReasonPurchaseRequestController::class, 'show'])->middleware('role_or_permission_api:view reason_purchase_requests');
            Route::post('/', [ReasonPurchaseRequestController::class, 'store'])->middleware('role_or_permission_api:create reason_purchase_requests');
            Route::post('/{id}', [ReasonPurchaseRequestController::class, 'update'])->middleware('role_or_permission_api:update reason_purchase_requests');
            Route::delete('/{id}', [ReasonPurchaseRequestController::class, 'delete'])->middleware('role_or_permission_api:delete reason_purchase_requests');
        });

        //reject purchase request
        Route::group(['prefix' => 'reject-purchase-request'], function () {
            Route::get('/', [RejectPurchaseRequestController::class, 'index'])->middleware('role_or_permission_api:view reject_purchase_requests');
            Route::get('/{id}', [RejectPurchaseRequestController::class, 'show'])->middleware('role_or_permission_api:view reject_purchase_requests');
            Route::post('/', [RejectPurchaseRequestController::class, 'store'])->middleware('role_or_permission_api:create reject_purchase_requests');
            Route::post('/{id}', [RejectPurchaseRequestController::class, 'update'])->middleware('role_or_permission_api:update reject_purchase_requests');
            Route::delete('/{id}', [RejectPurchaseRequestController::class, 'delete'])->middleware('role_or_permission_api:delete reject_purchase_requests');
        });


        // order waste
        Route::prefix('order-wastes')->group(function () {
            Route::get('', [OrderWasteController::class, 'index'])->middleware('role_or_permission_api:view order_wastes');
            Route::post('', [OrderWasteController::class, 'store'])->middleware('role_or_permission_api:create order_wastes');
            Route::get('/{id}', [OrderWasteController::class, 'show'])->middleware('role_or_permission_api:view order_wastes');
            Route::post('/{id}', [OrderWasteController::class, 'update'])->middleware('role_or_permission_api:update order_wastes');
            Route::delete('/{id}', [OrderWasteController::class, 'destroy'])->middleware('role_or_permission_api:delete order_wastes');
            Route::get('/{id}/logs', [OrderWasteController::class, 'getLogs'])->middleware('role_or_permission_api:view order_wastes_log');
        });

        // Warehouse routes
        Route::prefix('warehouses')->group(function () {
            Route::get('/', [WarehouseController::class, 'index'])->middleware('role_or_permission_api:view warehouses');
            Route::post('/', [WarehouseController::class, 'store'])->middleware('role_or_permission_api:create warehouses');
            Route::get('/{id}', [WarehouseController::class, 'show'])->middleware('role_or_permission_api:view warehouses');
            Route::put('/{id}', [WarehouseController::class, 'update'])->middleware('role_or_permission_api:update warehouses');
            Route::delete('/{id}', [WarehouseController::class, 'destroy'])->middleware('role_or_permission_api:delete warehouses');
        });
        // Zones
        Route::prefix('zones')->group(function () {
            Route::get('/', [ZoneController::class, 'index'])->middleware('role_or_permission_api:view zones');
            Route::post('/', [ZoneController::class, 'store'])->middleware('role_or_permission_api:create zones');
            Route::get('/all-archive', [ZoneController::class, 'allArchive'])->middleware('role_or_permission_api:restore zones');
            Route::get('/{id}', [ZoneController::class, 'show'])->middleware('role_or_permission_api:view zones');
            Route::post('/{id}', [ZoneController::class, 'update'])->middleware('role_or_permission_api:update zones');
            Route::delete('/{id}', [ZoneController::class, 'archive'])->middleware('role_or_permission_api:archieve zones');
            Route::get('restore/{id}', [ZoneController::class, 'restore'])->middleware('role_or_permission_api:restore zones');
        });
        Route::get('/statistics', [StatisticsController::class, 'index'])->middleware('role_or_permission_api:view statistics');

        Route::get('/dashboard', [StatisticsController::class, 'home'])->middleware('role_or_permission_api:view dashboard');

        Route::get('zones/store/{id}', [ZoneController::class, 'getZonesByStore']);
        Route::get('/shelfs/zone/{id}', [ShelfController::class, 'getRackShelvesByZone']);
        Route::get('/storage-location/zone/{id}', [StorageLocationController::class, 'getStorageLocationByZone']);

        // Storage Location routes
        Route::prefix('storage-locations')->group(function () {
            Route::get('/', [StorageLocationController::class, 'index'])->middleware('role_or_permission_api:view storage location');
            Route::get('/all-archive', [StorageLocationController::class, 'allArchive'])->middleware('role_or_permission_api:restore storage location');
            Route::post('/', [StorageLocationController::class, 'store'])->middleware('role_or_permission_api:create storage location');
            Route::get('/{id}', [StorageLocationController::class, 'show'])->middleware('role_or_permission_api:view storage location');
            Route::post('/{id}', [StorageLocationController::class, 'update'])->middleware('role_or_permission_api:update storage location');
            Route::delete('/{id}', [StorageLocationController::class, 'archieve'])->middleware('role_or_permission_api:delete storage location');
            Route::get('restore/{id}', [StorageLocationController::class, 'restore'])->middleware('role_or_permission_api:restore storage location');
        });

        Route::get('/inventory-employees', [PurchaseOrderController::class, 'listInventoryEmployees']);
        // Route::get('/reason-lists', [InventorySettingController::class, 'indexReason'])->middleware('role_or_permission_api:view reason_list');
        // Route::get('/reason-lists/{id}', [InventorySettingController::class, 'showReason'])->middleware('role_or_permission_api:view reason_list');
        // Route::post('/reason-lists', [InventorySettingController::class, 'storeReason'])->middleware('role_or_permission_api:create reason_list');
        // Route::put('/reason-lists/{id}', [InventorySettingController::class, 'updateReason'])->middleware('role_or_permission_api:update reason_list');

        Route::get('/data-of-dropdown', [CustomFieldController::class, 'showModel']);

        Route::get('/product-configiration', [InventoryPackagingConfigrationController::class, 'getProducts'])->middleware('role_or_permission_api:data of dropdown');
        Route::post('/add-product-configiration', [InventoryPackagingConfigrationController::class, 'store'])->middleware('role_or_permission_api:add product configiration');
        Route::get('/show-product-configiration', [InventoryPackagingConfigrationController::class, 'index'])->middleware('role_or_permission_api:show product configiration');
        Route::put('/update-product-configiration/{id}', [InventoryPackagingConfigrationController::class, 'update'])->middleware('role_or_permission_api:update product configiration');

        Route::get('tables-name', [CustomFieldController::class, 'tablesName']); //->middleware('role_or_permission_api:action on custom fields');
        Route::get('categories-name', [CustomFieldController::class, 'categoriesName']); //->middleware('role_or_permission_api:action on custom fields');

        // custom field
        Route::prefix('custom-field')->group(function () {
            Route::post('store', [CustomFieldController::class, 'storeCustomField'])->middleware('role_or_permission_api:create custom-field');
            Route::get('index', [CustomFieldController::class, 'indexCustomField'])->middleware('role_or_permission_api:view custom-field');
            Route::get('show/{id}', [CustomFieldController::class, 'showCustomField'])->middleware('role_or_permission_api:view custom-field');
            Route::post('update/{id}', [CustomFieldController::class, 'updateCustomField'])->middleware('role_or_permission_api:update custom-field');
            Route::delete('delete/{id}', [CustomFieldController::class, 'destroyCustomField'])->middleware('role_or_permission_api:delete custom-field');
        });
        // category
        Route::prefix('category')->group(function () {
            Route::get('show-categories', [CustomFieldController::class, 'index'])->middleware('role_or_permission_api:view category');
            Route::get('show-category/{id}', [CustomFieldController::class, 'show'])->middleware('role_or_permission_api:view category');
            Route::post('update/{id}', [CustomFieldController::class, 'update'])->middleware('role_or_permission_api:update category');
            Route::post('store', [CustomFieldController::class, 'store'])->middleware('role_or_permission_api:create category');
            Route::delete('delete/{id}', [CustomFieldController::class, 'delete'])->middleware('role_or_permission_api:delete category');
            Route::get('show-custom-field/{id}', [CustomFieldController::class, 'category'])->middleware('role_or_permission_api:list custom fields');
            Route::post('remove-from-product', [CustomFieldController::class, 'removeCategoryFromProduct'])->middleware('role_or_permission_api:update category');
            Route::post('remove-from-custom_field', [CustomFieldController::class, 'removeCategoryFromCustomField'])->middleware('role_or_permission_api:update category');
        });
        //  updateExpirySetting
        Route::group(['prefix' => 'inventory-setting'], function () {
            Route::get('/', [InventorySettingController::class, 'showExpirySetting'])->middleware('role_or_permission_api:view inventory-setting');
            Route::post('/update', [InventorySettingController::class, 'updateExpirySetting'])->middleware('role_or_permission_api:update inventory-setting');
        });

        //  unit
        Route::group(['prefix' => 'unit'], function () {
            Route::get('index', [UnitController::class, 'index'])->middleware('role_or_permission_api:view unit');
            Route::get('show/{id}', [UnitController::class, 'show'])->middleware('role_or_permission_api:view unit');
            Route::post('store', [UnitController::class, 'store'])->middleware('role_or_permission_api:create unit');
            Route::post('update/{id}', [UnitController::class, 'update'])->middleware('role_or_permission_api:update unit');
            Route::delete('delete/{id}', [UnitController::class, 'delete'])->middleware('role_or_permission_api:delete unit');
        });
        Route::prefix('waste-reasons')->group(function () {
            Route::get('/', [WasteReasonController::class, 'index'])->middleware('role_or_permission_api:view waste_reason');
            Route::post('/', [WasteReasonController::class, 'store'])->middleware('role_or_permission_api:create waste_reason');
            Route::get('/{id}', [WasteReasonController::class, 'show'])->middleware('role_or_permission_api:view waste_reason');
            Route::post('/{id}', [WasteReasonController::class, 'update'])->middleware('role_or_permission_api:update waste_reason');
            Route::delete('/{id}', [WasteReasonController::class, 'destroy'])->middleware('role_or_permission_api:delete waste_reason');
        });
        Route::post('change-status/{id}', [OrderWasteController::class, 'changeWasteStatus']);
    });
});
