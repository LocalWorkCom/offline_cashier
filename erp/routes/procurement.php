<?php

use App\Http\Controllers\Dashboard\NotificationSendController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HR_APIs\DepartmentController;
use App\Http\Controllers\Api\InventoryAPIs\UnitController;
use App\Http\Controllers\Api\InventoryAPIs\ProductController;
use App\Http\Controllers\Api\ProcurementAPIS\BrandController;
use App\Http\Controllers\Api\InventoryAPIs\CustomFieldController;
use App\Http\Controllers\Api\ProcurementAPIS\DepositRuleController;
use App\Http\Controllers\Api\ProcurementAPIS\DocumentController;
use App\Http\Controllers\Api\ProcurementAPIS\FinancialController;
use App\Http\Controllers\Api\ProcurementAPIS\HighValueRuleController;
use App\Http\Controllers\Api\ProcurementAPIS\PaymentTypeController;
use App\Http\Controllers\Api\ProcurementAPIS\PaymentMethodController;
use App\Http\Controllers\Api\ProcurementAPIS\PaymentIntervalController;
use App\Http\Controllers\Api\ProcurementAPIS\PricingDealController;
use App\Http\Controllers\Api\ProcurementAPIS\ProcurementEmployeeController;
use App\Http\Controllers\Api\ProcurementAPIS\PurchaseOrderController;
use App\Http\Controllers\Api\ProcurementAPIS\PurchaseRequestController;
use App\Http\Controllers\Api\ProcurementAPIS\PurchaseSettingController;
use App\Http\Controllers\Api\ProcurementAPIS\PurchasingBudgetController;
use App\Http\Controllers\Api\ProcurementAPIS\ReasoneReturnController;
use App\Http\Controllers\Api\ProcurementAPIS\ReasonPurchaseRequestController;
use App\Http\Controllers\Api\ProcurementAPIS\RejectPurchaseRequestController;
use App\Http\Controllers\Api\ProcurementAPIS\VendorController;

Route::post('procurement/create/purchaserequest', [PurchaseRequestController::class, 'store']);
Route::get('procurement/departments/view', [DepartmentController::class, 'index']);



Route::middleware(['auth:employee'])->group(function () {

    Route::prefix('procurement')->group(function () {
        Route::get('/notifications', [NotificationSendController::class, 'index']);

        // custom field
        Route::prefix('custom-field')->group(function () {
            Route::post('store', [CustomFieldController::class, 'storeCustomField']); //->middleware('role_or_permission_api:create custom-field');
            Route::get('index', [CustomFieldController::class, 'indexCustomField']); //->middleware('role_or_permission_api:view custom-field');
            Route::get('show/{id}', [CustomFieldController::class, 'showCustomField']); //->middleware('role_or_permission_api:view custom-field');
            Route::post('update/{id}', [CustomFieldController::class, 'updateCustomField']); //->middleware('role_or_permission_api:update custom-field');
            Route::delete('delete/{id?}', [CustomFieldController::class, 'destroyCustomField']); //->middleware('role_or_permission_api:delete custom-field');
        });
        // category
        Route::prefix('category')->group(function () {
            Route::get('show-categories', [CustomFieldController::class, 'index']); //->middleware('role_or_permission_api:view category');
            Route::get('show-categories-child', [CustomFieldController::class, 'index2']); //->middleware('role_or_permission_api:view category');

            Route::get('show-category/{id}', [CustomFieldController::class, 'show']); //->middleware('role_or_permission_api:view category');
            Route::post('update/{id}', [CustomFieldController::class, 'update']); //->middleware('role_or_permission_api:update category');
            Route::post('store', [CustomFieldController::class, 'store']); //->middleware('role_or_permission_api:create category');
            Route::delete('delete/{id?}', [CustomFieldController::class, 'delete']); //->middleware('role_or_permission_api:delete category');
        });
        // vendors
        Route::prefix('vendor')->group(function () {
            Route::get('/', [VendorController::class, 'index']); //->middleware('role_or_permission_api:view vendor');
            Route::get('/{id}', [VendorController::class, 'show']); //->middleware('role_or_permission_api:view vendor');
            Route::post('/', [VendorController::class, 'store']); //->middleware('role_or_permission_api:store vendor');
            Route::post('/{id}', [VendorController::class, 'update']); //->middleware('role_or_permission_api:update vendor');
            Route::delete('/{id?}', [VendorController::class, 'destroy']);

        });
        //brands
        Route::prefix('brands')->group(function () {
            Route::get('/list', [BrandController::class, 'index']); //->middleware('role_or_permission_api:view brand');
            Route::get('/show/{id}', [BrandController::class, 'show']); //->middleware('role_or_permission_api:view brand');
            Route::post('/store', [BrandController::class, 'store']); //->middleware('role_or_permission_api:create brand');
            Route::post('/update/{id}', [BrandController::class, 'update']); //->middleware('role_or_permission_api:update brand');
            Route::delete('/delete/{id?}', [BrandController::class, 'destroy']); //->middleware('role_or_permission_api:delete brand');

        });
        Route::group(['prefix' => 'departments'], function () {

            Route::get('/', [DepartmentController::class, 'index']);
            Route::get('/list', [DepartmentController::class, 'list']); //->middleware('role_or_permission_api:view departments');
            Route::get('/show/{id}', [DepartmentController::class, 'show']); //->middleware('role_or_permission_api:view departments');
            Route::post('/add', [DepartmentController::class, 'store']); //->middleware('role_or_permission_api:create departments');
            Route::post('/update/{id}', [DepartmentController::class, 'update']); //->middleware('role_or_permission_api:update departments');
            Route::delete('/delete/{id?}', [DepartmentController::class, 'destroy']); //->middleware('role_or_permission_api:delete departments');
        });

        Route::group(['prefix' => 'product'], function () {
            Route::get('/', [ProductController::class, 'indexV2']); //->middleware('role_or_permission_api:view products');
            Route::get('/product_brand', [ProductController::class, 'index']); //->middleware('role_or_permission_api:view products');
            Route::post('store', [ProductController::class, 'storeV2']); //->middleware('role_or_permission_api:create products');
            Route::get('show/{id}', [ProductController::class, 'showProduct']); //->middleware('role_or_permission_api:view products');
            Route::post('update/{id}', [ProductController::class, 'updateV2']); //->middleware('role_or_permission_api:update products');
            Route::delete('delete/{id}', [ProductController::class, 'deleteV2']); //->middleware('role_or_permission_api:delete products');
            Route::get('skuGeneration', [ProductController::class, 'skuGeneration']); //->middleware('role_or_permission_api:view products');
            Route::get('showproductsdetails', [ProductController::class, 'showProductdetail']); //->middleware('role_or_permission_api:view products');
            Route::get('/showaverageprice', [ProductController::class, 'getAveragePrice']); //->middleware('role_or_permission_api:view brand');

        });

        //  unit
        Route::group(['prefix' => 'unit'], function () {
            Route::get('index', [UnitController::class, 'index']); //->middleware('role_or_permission_api:view unit');
            Route::get('show/{id}', [UnitController::class, 'show']); //->middleware('role_or_permission_api:view unit');
            Route::post('store', [UnitController::class, 'store']); //->middleware('role_or_permission_api:create unit');
            Route::post('update/{id}', [UnitController::class, 'update']); //->middleware('role_or_permission_api:update unit');
            Route::delete('delete/{id}', [UnitController::class, 'delete']); //->middleware('role_or_permission_api:delete unit');
        });

        //  PaymentInterval
        Route::group(['prefix' => 'payment-interval'], function () {
            Route::get('index', [PaymentIntervalController::class, 'index']); //->middleware('role_or_permission_api:view payment-interval');
            Route::get('show/{id}', [PaymentIntervalController::class, 'show']); //->middleware('role_or_permission_api:view payment-interval');
            Route::post('store', [PaymentIntervalController::class, 'store']); //->middleware('role_or_permission_api:create payment-interval');
            Route::post('update/{id}', [PaymentIntervalController::class, 'update']); //->middleware('role_or_permission_api:update payment-interval');
            Route::delete('delete/{id}', [PaymentIntervalController::class, 'delete']); //->middleware('role_or_permission_api:delete payment-interval');
        });

        //  PaymentMethod
        Route::group(['prefix' => 'payment-method'], function () {
            Route::get('index', [PaymentMethodController::class, 'index']); //->middleware('role_or_permission_api:view payment-method');
            Route::get('show/{id}', [PaymentMethodController::class, 'show']); //->middleware('role_or_permission_api:view payment-method');
            Route::post('store', [PaymentMethodController::class, 'store']); //->middleware('role_or_permission_api:create payment-method');
            Route::post('update/{id}', [PaymentMethodController::class, 'update']); //->middleware('role_or_permission_api:update payment-method');
            Route::delete('delete/{id}', [PaymentMethodController::class, 'delete']); //->middleware('role_or_permission_api:delete payment-method');
        });

        //  PaymentMethod
        Route::group(['prefix' => 'payment-type'], function () {
            Route::get('index', [PaymentTypeController::class, 'index']); //->middleware('role_or_permission_api:view payment-type');
            Route::get('show/{id}', [PaymentTypeController::class, 'show']); //->middleware('role_or_permission_api:view payment-type');
            Route::post('store', [PaymentTypeController::class, 'store']); //->middleware('role_or_permission_api:create payment-type');
            Route::post('update/{id}', [PaymentTypeController::class, 'update']); //->middleware('role_or_permission_api:update payment-type');
            Route::delete('delete/{id}', [PaymentTypeController::class, 'delete']); //->middleware('role_or_permission_api:delete payment-type');
        });

        // Currency
        Route::group(['prefix' => 'currencies'], function () {
            Route::get('/', [FinancialController::class, 'listCurrencies']); //->middleware('role_or_permission_api:view currencies');
            Route::get('show/{id}', [FinancialController::class, 'showCurrencies']); //->middleware('role_or_permission_api:view currencies');
            Route::post('store', [FinancialController::class, 'storeCurrency']); // ->middleware('role_or_permission_api:create currencies');
            Route::post('update/{id}', [FinancialController::class, 'updateCurrency']); //->middleware('role_or_permission_api:update currencies');
            Route::delete('delete/{id}', [FinancialController::class, 'deleteCurrency']); //->middleware('role_or_permission_api:delete currencies');
        });

        // Taxes
        Route::group(['prefix' => 'taxes'], function () {

            Route::get('/', [FinancialController::class, 'listTaxes']); //->middleware('role_or_permission_api:view taxes');
            Route::get('/{id}', [FinancialController::class, 'showTax']); //->middleware('role_or_permission_api:view taxes');

            Route::post('store', [FinancialController::class, 'storeTax']); //->middleware('role_or_permission_api:create taxes');
            Route::post('update/{id}', [FinancialController::class, 'updateTax']); //->middleware('role_or_permission_api:update taxes');
            Route::post('updatedefault/{id}', [FinancialController::class, 'updateTaxDefault']); //->middleware('role_or_permission_api:update taxes');

            Route::delete('delete/{id}', [FinancialController::class, 'deleteTax']); //->middleware('role_or_permission_api:delete taxes');
        });

        //  purchasing budget
        Route::group(['prefix' => 'purchasing-budget'], function () {
            Route::get('/', [PurchasingBudgetController::class, 'index']); //->middleware('role_or_permission_api:view purchasing-budget');
            Route::get('show/{id}', [PurchasingBudgetController::class, 'show']); //->middleware('role_or_permission_api:view purchasing-budget');
            Route::post('store', [PurchasingBudgetController::class, 'store']); //->middleware('role_or_permission_api:create purchasing-budget');
            Route::post('update/{id}', [PurchasingBudgetController::class, 'update']); //->middleware('role_or_permission_api:update purchasing-budget');
            Route::post('increasebudgetamount/{id}', [PurchasingBudgetController::class, 'increaseBudgetAmount']); //->middleware('role_or_permission_api:update purchasing-budget');
            Route::get('historyBudget', [PurchasingBudgetController::class, 'historyBudget']); //->middleware('role_or_permission_api:view purchasing-budget-history');
            Route::get('Budgetpermissions', [PurchasingBudgetController::class, 'budgetPermissionList']); //->middleware('role_or_permission_api:view purchasing-budget-history');
            Route::post('storeNotify/{id}', [PurchasingBudgetController::class, 'storeNotify']); //->middleware('role_or_permission_api:create purchasing-budget');

        });
        //  high value rule
        Route::group(['prefix' => 'deposite-rule'], function () {
            Route::get('/', [DepositRuleController::class, 'index']); //->middleware('role_or_permission_api:view deposit-rule');
            Route::get('show/{id}', [DepositRuleController::class, 'show']); //->middleware('role_or_permission_api:view deposit-rule');
            Route::post('store', [DepositRuleController::class, 'store']); //->middleware('role_or_permission_api:create deposit-rule');
            Route::post('update/{id}', [DepositRuleController::class, 'update']); //->middleware('role_or_permission_api:update deposit-rule');
            Route::delete('delete', [DepositRuleController::class, 'deleteMultiple']); //->middleware('role_or_permission_api:update deposit-rule');

        });
        //  deposite
        Route::group(['prefix' => 'high-value-rule'], function () {
            Route::get('/', [HighValueRuleController::class, 'index']); //->middleware('role_or_permission_api:view high-value-rule');
            Route::get('show/{id}', [HighValueRuleController::class, 'show']); //->middleware('role_or_permission_api:view high-value-rule');
            Route::post('store', [HighValueRuleController::class, 'store']); //->middleware('role_or_permission_api:create high-value-rule');
            Route::post('update/{id}', [HighValueRuleController::class, 'update']); //->middleware('role_or_permission_api:update high-value-rule');
            Route::delete('delete', [HighValueRuleController::class, 'deleteMultiple']); //->middleware('role_or_permission_api:update deposit-rule');

        });
        // Precision
        Route::group(['prefix' => 'precision'], function () {
            Route::get('/', [FinancialController::class, 'getPrecisionSettings']); //->middleware('role_or_permission_api:view precision');
            Route::post('update', [FinancialController::class, 'updatePrecision']); //->middleware('role_or_permission_api:update precision');
        });
        //permissions
        Route::post('/creategroupofpermissions', [ProcurementEmployeeController::class, 'createGroupOfPermissions']);
        //->middleware('role_or_permission_api:create GroupOfPermissions');
        //employees
        Route::group(['prefix' => 'employees'], function () {
            Route::get('/access/{id}', [ProcurementEmployeeController::class, 'getEmployeeRolesAndPermissions']);
            Route::post('/assign-permissions', [ProcurementEmployeeController::class, 'assignPermissionsToEmployee']);
            //->middleware('role_or_permission_api:Assign EmpyloyeePermissions');
            Route::get('/list', [ProcurementEmployeeController::class, 'getEmployees']); //->middleware('role_or_permission_api:view purchase_employees');
            Route::get('/show/{id}', [ProcurementEmployeeController::class, 'showEmployee']); //->middleware('role_or_permission_api:view purchase_employees');
            Route::post('create', [ProcurementEmployeeController::class, 'createEmployee']); //->middleware('role_or_permission_api:update purchase_employees');
            Route::post('update/{id}', [ProcurementEmployeeController::class, 'updateEmployee']); //->middleware('role_or_permission_api:update purchase_employees');
            Route::delete('delete/{id}', [ProcurementEmployeeController::class, 'destroyEmployee']); //->middleware('role_or_permission_api:delete purchase_employees');
            Route::post('/dashboardAccount', [ProcurementEmployeeController::class, 'accessDashboard']); //->middleware('role_or_permission_api:changeStatus AccessDashboard');
            Route::get('/listRoles', [ProcurementEmployeeController::class, 'getPurchaseRoles']);

        });
        Route::group(['prefix' => 'profile'], function () {
            Route::get('/', [ProcurementEmployeeController::class, 'profile']); //->middleware('role_or_permission_api:view purchase_employees');
            Route::post('/update',[ProcurementEmployeeController::class, 'updateProfile'] );
            Route::post('/changePassword', [ProcurementEmployeeController::class, 'changePassword']);
        });


        //reason purchase request
        Route::group(['prefix' => 'reason-purchase-request'], function () {
            Route::get('/', [ReasonPurchaseRequestController::class, 'index']); //->middleware('role_or_permission_api:view reason_purchase_requests');
            Route::get('/{id}', [ReasonPurchaseRequestController::class, 'show']); //->middleware('role_or_permission_api:view reason_purchase_requests');
            Route::post('/', [ReasonPurchaseRequestController::class, 'store']); //->middleware('role_or_permission_api:create reason_purchase_requests');
            Route::post('/{id}', [ReasonPurchaseRequestController::class, 'update']); //->middleware('role_or_permission_api:update reason_purchase_requests');
            Route::delete('/{id}', [ReasonPurchaseRequestController::class, 'delete']); //->middleware('role_or_permission_api:delete reason_purchase_requests');
        });
        //reject purchase request
        Route::group(['prefix' => 'reject-purchase-request'], function () {
            Route::get('/', [RejectPurchaseRequestController::class, 'index']); //->middleware('role_or_permission_api:view reject_purchase_requests');
            Route::get('/{id}', [RejectPurchaseRequestController::class, 'show']); //->middleware('role_or_permission_api:view reject_purchase_requests');
            Route::post('/', [RejectPurchaseRequestController::class, 'store']); //->middleware('role_or_permission_api:create reject_purchase_requests');
            Route::post('/{id}', [RejectPurchaseRequestController::class, 'update']); //->middleware('role_or_permission_api:update reject_purchase_requests');
            Route::delete('/{id}', [RejectPurchaseRequestController::class, 'delete']); //->middleware('role_or_permission_api:delete reject_purchase_requests');
        });
        //reasone return
        Route::group(['prefix' => 'return-reason'], function () {
            Route::get('/', [ReasoneReturnController::class, 'index']); //->middleware('role_or_permission_api:view reason_return');
            Route::get('/{id}', [ReasoneReturnController::class, 'show']); //->middleware('role_or_permission_api:view reason_return');
            Route::post('/', [ReasoneReturnController::class, 'store']); //->middleware('role_or_permission_api:create reason_return');
            Route::post('/{id}', [ReasoneReturnController::class, 'update']); //->middleware('role_or_permission_api:update reason_return');
            Route::delete('/{id}', [ReasoneReturnController::class, 'delete']); //->middleware('role_or_permission_api:delete reason_return');
        });
        //list documnt types
        Route::get('/documnttype', [DocumentController::class, 'listDocumntType']);

        // //documnt
        Route::group(['prefix' => 'documnt'], function () {
            Route::get('/', [DocumentController::class, 'index']); //->middleware('role_or_permission_api:view reason_return');
            Route::get('/{id}', [DocumentController::class, 'show']); //->middleware('role_or_permission_api:view reason_return');
            Route::post('/', [DocumentController::class, 'store']); //->middleware('role_or_permission_api:create reason_return');
            Route::post('/{id}', [DocumentController::class, 'update']); //->middleware('role_or_permission_api:update reason_return');
        });
        // pricing deals
        Route::prefix('pricingdeals')->group(function () {
            Route::get('/', [PricingDealController::class, 'index']); //->middleware('role_or_permission_api:view pricing_deal');
            Route::get('/{id}', [PricingDealController::class, 'show']); //->middleware('role_or_permission_api:view pricing_deal');
            Route::post('/', [PricingDealController::class, 'store']); //->middleware('role_or_permission_api:store pricing_deal');
            Route::post('/{id}', [PricingDealController::class, 'update']); //->middleware('role_or_permission_api:update pricing_deal');
            Route::delete('/', [PricingDealController::class, 'destroy']); //->middleware('role_or_permission_api:delete pricing_deal');

        });
        Route::prefix('purchase-settings')->group(function () {
            Route::get('/', [PurchaseSettingController::class, 'show']);
            Route::post('/update', [PurchaseSettingController::class, 'update']);
        });

        //purchase request
        Route::group(['prefix' => 'purchase-request'], function () {
            Route::get('/autogeneratednumber', [PurchaseRequestController::class, 'createAutoGeneratedNumber']);
            Route::get('/', [PurchaseRequestController::class, 'index']); //->middleware('role_or_permission_api:view purchase_requests');
            Route::get('/{id}', [PurchaseRequestController::class, 'show']); //->middleware('role_or_permission_api:view purchase_requests');
            Route::get('/list/products', [PurchaseRequestController::class, 'showProducts']); //->middleware('role_or_permission_api:view purchase_requests');
            Route::get('/addproduct/{id}', [PurchaseRequestController::class, 'updatedamyProductStatus']); //->middleware('role_or_permission_api:view purchase_requests');
            Route::post('/', [PurchaseRequestController::class, 'store']); //->middleware('role_or_permission_api:create purchase_requests');
            Route::post('/{id}', [PurchaseRequestController::class, 'update']); //->middleware('role_or_permission_api:update purchase_requests');
            Route::post('/approveOrReject/{id}', [PurchaseRequestController::class, 'approveOrReject']); //->middleware('role_or_permission_api:approveOrReject purchase_requests');
            Route::delete('/{id?}', [PurchaseRequestController::class, 'deleteMultiple']); //->middleware('role_or_permission_api:delete purchase_requests');
        });
         //purchase order
        Route::group(['prefix' => 'purchase-order'], function () {
            Route::get('/autogeneratednumber', [PurchaseOrderController::class, 'createAutoGeneratedNumber']);
            Route::get('/', [PurchaseOrderController::class, 'index']); //->middleware('role_or_permission_api:view purchase_orders');
            Route::get('/{id}', [PurchaseOrderController::class, 'show']); //->middleware('role_or_permission_api:view purchase_orders');
            Route::post('/', [PurchaseOrderController::class, 'store']); //->middleware('role_or_permission_api:create purchase_orders');
            Route::post('/{id}', [PurchaseOrderController::class, 'update']); //->middleware('role_or_permission_api:update purchase_orders');
            Route::post('merge/PO', [PurchaseOrderController::class, 'mergePOs']); //->middleware('role_or_permission_api:approveOrReject purchase_orders');
            Route::post('/approvePM/{id}', [PurchaseOrderController::class, 'acceptPM']); //->middleware('role_or_permission_api:approveOrReject purchase_orders');
            Route::post('/approveFM/{id}', [PurchaseOrderController::class, 'acceptFM']); //->middleware('role_or_permission_api:approveOrReject purchase_orders');
            Route::post('/reject/{id}', [PurchaseOrderController::class, 'reject']); //->middleware('role_or_permission_api:approveOrReject purchase_orders');
            Route::delete('/{ids?}', [PurchaseOrderController::class, 'deleteMultiple']); //->middleware('role_or_permission_api:delete purchase_orders');
        });
    });
});
