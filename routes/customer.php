<?php

use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\BookingFlowController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\RegisterController;
use App\Http\Controllers\Customer\RoomBrowseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest-accessible booking flow — top-level URLs, no 'customer' prefix.
| Only the review/confirm steps require login (guest can browse & customize
| freely; the 'auth' middleware there sends them to /login and, once your
| AuthController calls redirect()->intended(), brings them right back).
|--------------------------------------------------------------------------
*/
Route::name('customer.')->group(function () {

    Route::get('/rooms', [RoomBrowseController::class, 'index'])->name('rooms.index');
    Route::get('/rooms/{roomType:slug}', [RoomBrowseController::class, 'show'])->name('rooms.show');

    Route::post('/register', [RegisterController::class, 'register'])->name('register');

    Route::prefix('booking')->name('booking.')->group(function () {
        Route::post('/start', [BookingFlowController::class, 'start'])->name('start');
        Route::get('/customize', [BookingFlowController::class, 'customize'])->name('customize');
        Route::post('/coupon/apply', [BookingFlowController::class, 'applyCoupon'])->name('coupon.apply');
        Route::post('/customize', [BookingFlowController::class, 'saveCustomize'])->name('customize.save');
        Route::get('/review', [BookingFlowController::class, 'review'])->middleware('auth')->name('review');
        Route::post('/confirm', [BookingFlowController::class, 'confirm'])->middleware('auth')->name('confirm');
    });

    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/fake/checkout/{tranId}', [PaymentController::class, 'fakeCheckout'])->name('fake.checkout');
        Route::post('/fake/callback/{tranId}', [PaymentController::class, 'fakeCallback'])->name('fake.callback');
        Route::get('/success/{tranId}', [PaymentController::class, 'success'])->name('success');
        Route::get('/failed/{tranId}', [PaymentController::class, 'failed'])->name('failed');
    });
});

/*
|--------------------------------------------------------------------------
| Authenticated customer dashboard
|--------------------------------------------------------------------------
*/
Route::prefix('customer')
    ->name('customer.')
    ->middleware(['auth', 'customer'])
    ->group(function () {
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    });