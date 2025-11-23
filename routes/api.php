<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/clear-cache', function() {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    Artisan::call('route:clear');
    Artisan::call('optimize:clear');
    Artisan::call('optimize');
    return 'Caches cleared';
});

Route::get('/migrate-db-fresh', function() {
    // Run your app migrations first
    Artisan::call('migrate:fresh', [
        '--force' => true,
    ]);

    // Then run Sanctum migrations
    Artisan::call('migrate', [
        '--path' => 'vendor/laravel/sanctum/database/migrations',
        '--force' => true,
    ]);

    return Artisan::output();
});

Route::get('/migrate-db', function() {
    Artisan::call('migrate', [
        '--force' => true,
    ]);

    return 'Database migrated';
});


Route::post('/auth/register', [LoginController::class, 'register'])->name('register');
Route::post('/auth/login', [LoginController::class, 'login'])->name('login');

Route::get('/rooms/get-rooms', [RoomsController::class, 'getRooms'])->name('rooms.get-all');


Route::middleware('auth:sanctum')->group(function () {



    Route::get('/user/get-users', [UserController::class, 'getUsers'])->name('users.get-all');
    Route::post('/auth/sign-out', [LoginController::class, 'signOut'])->name('sign-out');

    Route::get('/front-end/get-rooms', [FrontEndController::class, 'getRooms'])->name('front-end.get-rooms');
    Route::get('/front-end/get-my-bookings', [FrontEndController::class, 'getMyBookings'])->name('front-end.get-my-bookings');
    Route::post('/front-end/cancel-booking', [FrontEndController::class, 'cancelBooking'])->name('front-end.cancel-booking');
    Route::post('/front-end/check-out-booking', [FrontEndController::class, 'checkOutBooking'])->name('front-end.check-out-booking');
    Route::post('/front-end/book-room', [FrontEndController::class, 'bookRoom'])->name('front-end.book-room');
    Route::post('/front-end/book-amenities', [FrontEndController::class, 'bookAmenities'])->name('booking.book-amenities');



    Route::post('/payment/export-payments', [PaymentController::class, 'exportPayments'])->name('payment.export-payments');

    Route::post('/settings/test-email', [SettingsController::class, 'testEmail'])->name('settings.test-email');


    Route::get('/booking/get-report', [BookingController::class, 'getReport'])->name('booking.get-report');
    Route::post('/booking/export-reports', [BookingController::class, 'exportReports'])->name('booking.export-reports');


    Route::resources([
        'user' => UserController::class,
        'rooms' => RoomsController::class,
        'front-end' => FrontEndController::class,
        'booking' => BookingController::class,
        'payment' => PaymentController::class,
        'dashboard' => DashboardController::class,
        'settings' => SettingsController::class,
    ]);
    
});
