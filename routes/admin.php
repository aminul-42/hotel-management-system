<?php
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\RoomTypeController;
use App\Http\Controllers\Admin\RoomController;
 use App\Http\Controllers\Admin\RoomRateController;
 use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\CustomerController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        ///Room Types
    
        Route::prefix('room-types')->name('room-types.')->group(function () {
            Route::get('/', [RoomTypeController::class, 'index'])->name('index');
            Route::get('/data', [RoomTypeController::class, 'data'])->name('data');
            Route::post('/', [RoomTypeController::class, 'store'])->name('store');
            Route::post('/{roomType}', [RoomTypeController::class, 'update'])->name('update'); // multipart -> POST + _method=PUT
            Route::delete('/{roomType}', [RoomTypeController::class, 'destroy'])->name('destroy');
            Route::patch('/{roomType}/toggle', [RoomTypeController::class, 'toggleActive'])->name('toggle');
        });

        ///Rooms
    
        Route::prefix('rooms')->name('rooms.')->group(function () {
            Route::get('/', [RoomController::class, 'index'])->name('index');
            Route::get('/data', [RoomController::class, 'data'])->name('data');
            Route::post('/', [RoomController::class, 'store'])->name('store');
            Route::post('/{room}', [RoomController::class, 'update'])->name('update'); // multipart -> POST + _method=PUT
            Route::delete('/{room}', [RoomController::class, 'destroy'])->name('destroy');
            Route::patch('/{room}/status', [RoomController::class, 'updateStatus'])->name('status');
        });


        ///Room Rates

        Route::name('room-rates.')->prefix('room-rates')->group(function () {
            Route::get('/', [RoomRateController::class, 'index'])->name('index');
            Route::post('/', [RoomRateController::class, 'store'])->name('store');
            Route::get('/{roomRate}/edit', [RoomRateController::class, 'edit'])->name('edit');
            Route::post('/{roomRate}', [RoomRateController::class, 'update'])->name('update');
            Route::patch('/{roomRate}/toggle', [RoomRateController::class, 'toggle'])->name('toggle');
            Route::delete('/{roomRate}', [RoomRateController::class, 'destroy'])->name('destroy');
        });

        ///Room Bookings
    
        Route::name('bookings.')->prefix('bookings')->group(function () {
            Route::get('/', [BookingController::class, 'index'])->name('index');
            Route::post('/', [BookingController::class, 'store'])->name('store');

            // Fixed-path routes MUST come before the '/{booking}' wildcard ones
            Route::post('/check-availability', [BookingController::class, 'checkAvailability'])->name('check-availability');
            Route::post('/calculate-rate', [BookingController::class, 'calculateRate'])->name('calculate-rate');
            Route::get('/search-customers', [BookingController::class, 'searchCustomers'])->name('search-customers');
            Route::post('/apply-coupon', [BookingController::class, 'applyCoupon'])->name('apply-coupon');

            Route::get('/{booking}/edit', [BookingController::class, 'edit'])->name('edit');
            Route::post('/{booking}', [BookingController::class, 'update'])->name('update');
            Route::patch('/{booking}/status', [BookingController::class, 'updateStatus'])->name('status');
            Route::delete('/{booking}', [BookingController::class, 'destroy'])->name('destroy');
        });
        ///Coupons
    
        Route::name('coupons.')->prefix('coupons')->group(function () {
            Route::get('/', [CouponController::class, 'index'])->name('index');
            Route::post('/', [CouponController::class, 'store'])->name('store');
            Route::get('/{coupon}/edit', [CouponController::class, 'edit'])->name('edit');
            Route::post('/{coupon}', [CouponController::class, 'update'])->name('update');
            Route::patch('/{coupon}/toggle', [CouponController::class, 'toggle'])->name('toggle');
            Route::delete('/{coupon}', [CouponController::class, 'destroy'])->name('destroy');
        });

        ///Facilites

        Route::prefix('facilities')->name('facilities.')->group(function () {
            Route::get('/', [FacilityController::class, 'index'])->name('index');
            Route::post('/', [FacilityController::class, 'store'])->name('store');
            Route::post('/{facility}', [FacilityController::class, 'update'])->name('update');
            Route::patch('/{facility}/toggle', [FacilityController::class, 'toggleActive'])->name('toggle');
            Route::delete('/{facility}', [FacilityController::class, 'destroy'])->name('destroy');
        });


        ///Settings
    
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('/', [SettingController::class, 'update'])->name('update');
        });


        ///Custoemr Management

        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
            Route::patch('/{customer}/toggle', [CustomerController::class, 'toggleActive'])->name('toggle');
        });

    });