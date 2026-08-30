<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\PublicSiteController;
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

// ---------- Admin login ----------
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
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

    Route::middleware('admin')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
