<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Courier;
use App\Http\Controllers\DeliveryAreaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\Partner;
use App\Http\Controllers\RedemptionController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/delivery-area', [DeliveryAreaController::class, 'update'])->name('delivery-area.update');
Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');
Route::get('/restaurants', [RestaurantController::class, 'index'])->name('restaurants.index');
Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'show'])->name('restaurants.show');
Route::get('/memberships', [MembershipController::class, 'index'])->name('memberships.index');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/{item}', [CartController::class, 'store'])->name('cart.add');
Route::patch('/cart', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart', [CartController::class, 'destroy'])->name('cart.clear');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/partners/register', [Partner\RegisterController::class, 'create'])->name('partner.register');
    Route::post('/partners/register', [Partner\RegisterController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::post('/memberships/{membership}/subscribe', [MembershipController::class, 'subscribe'])->name('memberships.subscribe');
    Route::post('/memberships/card', [MembershipController::class, 'requestCard'])->name('memberships.card');

    Route::get('/redeem', [RedemptionController::class, 'create'])->name('redeem.create');
    Route::post('/redeem', [RedemptionController::class, 'store'])->name('redeem.store');

    Route::get('/account', [AccountController::class, 'show'])->name('account.show');
    Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
    Route::get('/account/orders/{order}', [AccountController::class, 'showOrder'])->name('account.orders.show');
    Route::post('/account/orders/{order}/cancel', [AccountController::class, 'cancelOrder'])->name('account.orders.cancel');
    Route::get('/account/points', [AccountController::class, 'points'])->name('account.points');
    Route::get('/account/addresses', [AccountController::class, 'addresses'])->name('account.addresses');
    Route::post('/account/addresses', [AccountController::class, 'storeAddress'])->name('account.addresses.store');
    Route::delete('/account/addresses/{address}', [AccountController::class, 'destroyAddress'])->name('account.addresses.destroy');
    Route::get('/account/notifications', [AccountController::class, 'notifications'])->name('account.notifications');
    Route::get('/account/notifications/{notification}', [AccountController::class, 'openNotification'])->name('account.notifications.open');
    Route::post('/account/notifications/read', [AccountController::class, 'markNotifications'])->name('account.notifications.read');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('search/suggest', [Admin\SearchController::class, 'suggest'])->name('search.suggest');
    Route::resource('restaurants', Admin\RestaurantController::class)->except(['show']);
    Route::get('restaurants/{restaurant}', [Admin\RestaurantController::class, 'show'])->name('restaurants.show');
    Route::post('restaurants/{restaurant}/approve', [Admin\RestaurantController::class, 'approve'])->name('restaurants.approve');
    Route::post('restaurants/{restaurant}/reject', [Admin\RestaurantController::class, 'reject'])->name('restaurants.reject');
    Route::resource('restaurants.menu-items', Admin\MenuItemController::class)->except(['show']);
    Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/receipt', [Admin\OrderController::class, 'receipt'])->name('orders.receipt');
    Route::patch('orders/{order}', [Admin\OrderController::class, 'update'])->name('orders.update');
    Route::resource('memberships', Admin\MembershipController::class)->except(['show']);
    Route::get('subscriptions', [Admin\SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('subscriptions/{subscription}', [Admin\SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::get('subscriptions/{subscription}/receipt', [Admin\SubscriptionController::class, 'receipt'])->name('subscriptions.receipt');
    Route::post('subscriptions/{subscription}/approve', [Admin\SubscriptionController::class, 'approve'])->name('subscriptions.approve');
    Route::post('subscriptions/{subscription}/reject', [Admin\SubscriptionController::class, 'reject'])->name('subscriptions.reject');
    Route::post('subscriptions/{subscription}/card', [Admin\SubscriptionController::class, 'markCard'])->name('subscriptions.card');
    Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [Admin\UserController::class, 'show'])->name('users.show');
    Route::post('users/{user}/points', [Admin\UserController::class, 'adjustPoints'])->name('users.points');
    Route::get('settings', [Admin\SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
    Route::get('notifications', [Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{notification}', [Admin\NotificationController::class, 'open'])->name('notifications.open');
    Route::post('notifications/read', [Admin\NotificationController::class, 'markRead'])->name('notifications.read');
});

Route::prefix('partner')->name('partner.')->middleware(['auth', 'partner'])->group(function () {
    Route::get('/', [Partner\DashboardController::class, 'index'])->name('dashboard');
    Route::get('restaurant', [Partner\RestaurantController::class, 'edit'])->name('restaurant.edit');
    Route::put('restaurant', [Partner\RestaurantController::class, 'update'])->name('restaurant.update');
    Route::post('restaurant/resubmit', [Partner\RestaurantController::class, 'resubmit'])->name('restaurant.resubmit');
    Route::resource('menu-items', Partner\MenuItemController::class)->except(['show']);
    Route::get('orders', [Partner\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [Partner\OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/receipt', [Partner\OrderController::class, 'receipt'])->name('orders.receipt');
    Route::patch('orders/{order}', [Partner\OrderController::class, 'update'])->name('orders.update');
    Route::get('card', [Partner\CardController::class, 'show'])->name('cards.show');
    Route::get('notifications', [Partner\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{notification}', [Partner\NotificationController::class, 'open'])->name('notifications.open');
    Route::post('notifications/read', [Partner\NotificationController::class, 'markRead'])->name('notifications.read');
});

Route::prefix('courier')->name('courier.')->middleware(['auth', 'courier'])->group(function () {
    Route::get('/', [Courier\DashboardController::class, 'index'])->name('dashboard');
    Route::get('orders/{order}', [Courier\OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/claim', [Courier\OrderController::class, 'claim'])->name('orders.claim');
    Route::post('orders/{order}/complete', [Courier\OrderController::class, 'complete'])->name('orders.complete');
    Route::get('notifications', [Courier\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{notification}', [Courier\NotificationController::class, 'open'])->name('notifications.open');
    Route::post('notifications/read', [Courier\NotificationController::class, 'markRead'])->name('notifications.read');
});
