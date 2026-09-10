<?php

use App\Http\Controllers\AmenityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailLogController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LaundryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\RestaurantOrderController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomRequestController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SmsLogController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ---------- Public website ----------
Route::get('/', [PublicSiteController::class, 'home'])->name('site.home');
Route::get('/accommodation', [PublicSiteController::class, 'rooms'])->name('site.rooms');
Route::get('/accommodation/{roomType}', [PublicSiteController::class, 'roomType'])->name('site.room-type');
Route::get('/contact', [PublicSiteController::class, 'contact'])->name('site.contact');
Route::get('/api/site/available-rooms', [PublicSiteController::class, 'availableRooms'])->name('site.api.available-rooms');

Route::get('/book', [PublicBookingController::class, 'create'])->name('site.booking.create');
Route::post('/book', [PublicBookingController::class, 'store'])->name('site.booking.store');
Route::get('/booking/{booking}/confirmation', [PublicBookingController::class, 'confirmation'])->name('site.booking.confirmation');

Route::get('/my-booking', [PublicBookingController::class, 'lookup'])->name('site.booking.lookup');
Route::post('/my-booking', [PublicBookingController::class, 'verify'])->name('site.booking.verify');
Route::get('/my-booking/{booking}', [PublicBookingController::class, 'portal'])->name('site.booking.portal');
Route::post('/my-booking/{booking}/pay', [PublicBookingController::class, 'pay'])->name('site.booking.pay');
Route::post('/my-booking/{booking}/cancel', [PublicBookingController::class, 'cancel'])->name('site.booking.cancel');
Route::post('/my-booking/{booking}/request', [PublicBookingController::class, 'requestStore'])->name('site.booking.request');
Route::post('/my-booking/{booking}/restaurant-order', [PublicBookingController::class, 'restaurantOrder'])->name('site.booking.restaurant-order');
Route::post('/my-booking/{booking}/laundry', [PublicBookingController::class, 'laundryStore'])->name('site.booking.laundry');

// ---------- Admin login ----------
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'module'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/sms/test', [AuthController::class, 'testSms'])->name('sms.test');

    Route::resource('room-types', RoomTypeController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
    Route::put('/rooms/{room}/status', [RoomController::class, 'setStatus'])->name('rooms.status');

    Route::get('/guests', [GuestController::class, 'index'])->name('guests.index');
    Route::post('/guests', [GuestController::class, 'store'])->name('guests.store');
    Route::put('/guests/{guest}', [GuestController::class, 'update'])->name('guests.update');
    Route::delete('/guests/{guest}', [GuestController::class, 'destroy'])->name('guests.destroy');

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
    Route::post('/bookings/{booking}/check-in', [BookingController::class, 'checkIn'])->name('bookings.check-in');
    Route::post('/bookings/{booking}/check-out', [BookingController::class, 'checkOut'])->name('bookings.check-out');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/api/available-rooms', [BookingController::class, 'availableRooms'])->name('api.available-rooms');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{booking}', [InvoiceController::class, 'show'])->name('invoices.show');

    Route::get('/requests', [RoomRequestController::class, 'index'])->name('requests.index');
    Route::post('/requests', [RoomRequestController::class, 'store'])->name('requests.store');
    Route::put('/requests/{roomRequest}', [RoomRequestController::class, 'update'])->name('requests.update');
    Route::put('/requests/{roomRequest}/status', [RoomRequestController::class, 'setStatus'])->name('requests.status');
    Route::delete('/requests/{roomRequest}', [RoomRequestController::class, 'destroy'])->name('requests.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/api/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unread');
    Route::get('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    Route::get('/sms-logs', [SmsLogController::class, 'index'])->name('sms-logs.index');

    Route::get('/email-logs', [EmailLogController::class, 'index'])->name('emails.index');
    Route::get('/email-logs/{log}/preview', [EmailLogController::class, 'preview'])->name('emails.preview');
    Route::post('/email-logs/{log}/resend', [EmailLogController::class, 'resend'])->name('emails.resend');
    Route::post('/emails/test', [EmailLogController::class, 'test'])->name('emails.test');

    Route::get('/amenities', [AmenityController::class, 'index'])->name('amenities.index');
    Route::post('/amenities', [AmenityController::class, 'store'])->name('amenities.store');
    Route::put('/amenities/{amenity}', [AmenityController::class, 'update'])->name('amenities.update');
    Route::delete('/amenities/{amenity}', [AmenityController::class, 'destroy'])->name('amenities.destroy');

    Route::get('/restaurant/menu', [MenuItemController::class, 'index'])->name('restaurant.menu');
    Route::post('/restaurant/menu', [MenuItemController::class, 'store'])->name('restaurant.menu.store');
    Route::put('/restaurant/menu/{menuItem}', [MenuItemController::class, 'update'])->name('restaurant.menu.update');
    Route::delete('/restaurant/menu/{menuItem}', [MenuItemController::class, 'destroy'])->name('restaurant.menu.destroy');
    Route::post('/restaurant/menu/{menuItem}/toggle', [MenuItemController::class, 'toggleAvailability'])->name('restaurant.menu.toggle');

    Route::get('/restaurant/orders', [RestaurantOrderController::class, 'index'])->name('restaurant.orders.index');
    Route::post('/restaurant/orders', [RestaurantOrderController::class, 'store'])->name('restaurant.orders.store');
    Route::post('/restaurant/orders/{order}/status', [RestaurantOrderController::class, 'setStatus'])->name('restaurant.orders.status');
    Route::delete('/restaurant/orders/{order}', [RestaurantOrderController::class, 'destroy'])->name('restaurant.orders.destroy');

    Route::get('/laundry', [LaundryController::class, 'index'])->name('laundry.index');
    Route::post('/laundry', [LaundryController::class, 'store'])->name('laundry.store');
    Route::post('/laundry/{laundry}/status', [LaundryController::class, 'setStatus'])->name('laundry.status');
    Route::delete('/laundry/{laundry}', [LaundryController::class, 'destroy'])->name('laundry.destroy');

    Route::middleware('admin')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
});
