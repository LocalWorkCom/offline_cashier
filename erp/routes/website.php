<?php

use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Dashboard\testController;
use App\Http\Controllers\Website\AuthController;
use App\Http\Controllers\Website\CartController;
use App\Http\Controllers\Website\HomeController;
use App\Http\Controllers\Website\LocationController;
use App\Http\Controllers\Website\MyFatoorahController;
use App\Http\Controllers\Website\OrderController;
use App\Http\Controllers\Website\RateController;
use App\Http\Controllers\Website\ReservationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| website  Routes
|--------------------------------------------------------------------------
|
*/
Route::get('testphp',[testController::class,'testController']);

Route::get('/chat/messages/{channel_id}', [chatController::class, 'getChatMessages'])
    ->name('chat.messages');
Route::post('/site/register', [AuthController::class, 'Register'])->name('website.register');
Route::post('/site/login', [AuthController::class, 'login'])->name('website.login');
Route::post('/site/check-phone', [AuthController::class, 'checkPhone'])->name('check.phone');
Route::post('/site/reset-password', [AuthController::class, 'resetPassword'])->name('reset.password');
Route::post('/forget-session', function () {
    session()->forget('new_address_id');
    return response()->json(['success' => true]);
});
Route::get('/get-cities/{countryId}', [LocationController::class, 'getCities']);
Route::get('/get-areas/{cityId}', [LocationController::class, 'getAreas']);
Route::get('/check-area-branch/{Id}', [LocationController::class, 'checkAreasBranch']);

Route::post('/search-branches', [LocationController::class, 'searchBranches'])->name('search.branches');
Route::post('/checkAvailability', [LocationController::class, 'checkAvailability'])->name('search.branches.checkAvailability');
Route::post('/checkOrderCapacity', [LocationController::class, 'checkOrderCapacity'])->name('check.order.capacity');

Route::post('/set-new-address-session', [LocationController::class, 'setNewAddressSession']);

Route::middleware(['auth:client'])->group(function () {
    Route::get('/chat-channel/{orderId}', [chatController::class, 'getChannel']);
    Route::post('/site/update-password', [AuthController::class, 'changePassword'])->name('reset.password.auth');

    Route::post('/chat/mark-as-read', [chatController::class, 'markAsRead'])
        ->name('chat.markAsRead');
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.view');
    Route::post('/chat/sender', [ChatController::class, 'store'])
        ->name('chat.sender');
    Route::get('/chat/messages', [ChatController::class, 'getChatMessages']);
    Route::post('/notification/mark-as-read/{id}', [HomeController::class, 'markNotificationAsRead'])->name('notification.markAsRead');
    Route::get('/site/profile', [AuthController::class, 'viewProfile'])->name('website.profile.view');
    Route::post('/site/profile/update', [AuthController::class, 'updateProfile'])->name('website.profile.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('website.logout');
    Route::get('/favorites', [HomeController::class, 'showFavorites'])->name('show.favorites');
    Route::get('/myaddress', [LocationController::class, 'showAddress'])->name('showAddress');
    Route::get('/myaddress/add', [LocationController::class, 'createAddress'])->name('create.Address');
    Route::get('/myaddress/edit/{id}', [LocationController::class, 'createAddress'])->name('edit.Address');
    Route::post('/myaddress/handle', [LocationController::class, 'createOrUpdateAddress'])->name('handle.Address');
    Route::post('/address/delete/{id}', [LocationController::class, 'destroyAddress'])->name('address.delete');
    Route::post('/myaddress/add', [LocationController::class, 'storeAddress'])->name('store.Address');
    Route::post('/myaddress/active/{id}', [LocationController::class, 'defaultAddress'])->name('default.Address');
    Route::get('/orders', [OrderController::class, 'pastOrders'])->name('orders.show');
    Route::get('/reorder', [OrderController::class, 'reOrder'])->name('reorder');
    Route::get('/orders/track', [OrderController::class, 'trackOrder'])->name('orders.tracking');
    Route::get('/order/payment/{id}/details', [OrderController::class, 'paymentDetails'])->name('order.paymentdetails');
    Route::post('/order-store', [CartController::class, 'store'])->name('web.order.add');
    Route::get('cart/checkout', [CartController::class, 'Checkout'])->name('cart.checkout');
    Route::post('/get-delivery-fees', [CartController::class, 'getDeliveryFeesAjax']);


    //    Route::get('/rate', [HomeController::class, 'showRate'])->name('show.rating');
    //    Route::post('/rate', [HomeController::class, 'addRate'])->name('store.rating');


    Route::post('store', [RateController::class, 'store'])->name('rate.store');

    Route::get('/coupons', [HomeController::class, 'showCoupons'])->name('show.coupons');


    Route::get('cart/checkDish/{dishId}/{branchId}', [CartController::class, 'checkDish'])->name('cart.checkDish');
    Route::get('cart/checkDishBranch/{branchId}', [CartController::class, 'checkDishBranch'])->name('cart.checkDishBranch');
    Route::get('cart/checkDishSize/{dishId}/{dishSizeId}/{branchId}', [CartController::class, 'checkDishSize'])->name('cart.checkDishSize');
    Route::get('cart/checkDishAddon/{dishId}/{dishAddonId}/{branchId}', [CartController::class, 'checkDishAddon'])->name('cart.checkDishAddon');
});
Route::get('/get-hotels-by-area/{area_id}', [LocationController::class, 'getByArea']);

Route::get('/get-nearest-branch', [LocationController::class, 'getNearestBranchl']);
Route::get('/branch-details', [LocationController::class, 'getBranchDetails'])->name('get-branch-details');

Route::get('/check-branch-allow-delivery', [LocationController::class, 'checkallowdelivery']);
Route::get('/get-address-detail', [LocationController::class, 'getAddressDetail'])->name('get-address-detail');
Route::get('/get-bracnh-info', [HomeController::class, 'getBranchInfo'])->name('get-bracnh-info');

Route::post('/order/cancel', [OrderController::class, 'cancelOrder'])->name('order.cancel');
Route::post('/order/validate-cancellation', [OrderController::class, 'validateCancellation'])->name('order.validateCancellation');

Route::post('/order/cancelReservation', [ReservationController::class, 'cancelReservation'])->name('order.cancelReservation');
Route::post('/order/validate-reservation-cancellation', [ReservationController::class, 'validateReservationCancellation'])->name('order.validateReservationCancellation');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/faqs', [HomeController::class, 'getfaqs'])->name('shoe.faq');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/return', [HomeController::class, 'return'])->name('return');
Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
Route::get('/menu', [HomeController::class, 'showMenu'])->name('menu');
Route::get('/menu/details/{id}', [HomeController::class, 'showMenu'])->name('menu.details');
Route::get('/offers', [HomeController::class, 'showOffers'])->name('offers.website');
Route::get('/contact-us', [HomeController::class, 'contactUs'])->name('contactUs');
Route::post('/favorite-dish', [HomeController::class, 'addFavorite'])->name('add.favorite');

Route::get('cart', [CartController::class, 'Cart'])->name('cart');
Route::post('cart/coupon-check', [CartController::class, 'isCouponValid'])->name('cart.coupon-check');
Route::post('cart/coupon-validate', [CartController::class, 'validateCoupon'])->name('cart.coupon-validate');


Route::get('cart/dish-detail', [CartController::class, 'getDishDetail'])->name('cart.dish-detail');
Route::get('/favorites', [HomeController::class, 'showFavorites'])->name('show.favorites');

//myfatoorah
Route::get('/myfatoorah-payment/{order_id}/{reserve}', [MyFatoorahController::class, 'index'])->name('myfatoorah-payment');
Route::get('/payment-transaction/{payment_reference}/{reserve}', [CartController::class, 'payment_transaction'])->name('payment-transaction');
Route::get('/myfatoorah/callback', [MyFatoorahController::class, 'callback'])->name('myfatoorah.callback');
// Route::get('/myfatoorah/webhook', [MyFatoorahController::class, 'webhook'])->name('myfatoorah.webhook');
// Route::get('/myfatoorah/checkout', [MyFatoorahController::class, 'checkout'])->name('myfatoorah.cardView');

Route::get('/nearest-branch', [HomeController::class, 'nearestBranch'])->name('get.nearestBranch');

Route::get('/getSession/{count}/{branch_id}/{reservation_date}/{floor_partition}/{type}', [ReservationController::class, 'getSession'])->name('getSession');
Route::get('/table-reservation/confirmation', [ReservationController::class, 'tableConfirmation'])->name('table-reservation.confirmation');
Route::get('/table-reservation/checkout', [ReservationController::class, 'tableCheckout'])->name('table-reservation.checkout');
Route::post('/table-reservation/store', [ReservationController::class, 'storeTableReservation'])->name('table-reservation.store');
