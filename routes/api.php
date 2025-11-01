<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

Route::post('/auth/register', [LoginController::class, 'register'])->name('register');
Route::post('/auth/login', [LoginController::class, 'login'])->name('login');


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user/get-users', [UserController::class, 'getUsers'])->name('users.get-all');

    Route::get('/front-end/get-rooms', [FrontEndController::class, 'getRooms'])->name('front-end.get-rooms');
    Route::get('/front-end/get-my-bookings', [FrontEndController::class, 'getMyBookings'])->name('front-end.get-my-bookings');
    Route::post('/front-end/cancel-booking', [FrontEndController::class, 'cancelBooking'])->name('front-end.cancel-booking');
    Route::post('/front-end/book-room', [FrontEndController::class, 'bookRoom'])->name('front-end.book-room');


    Route::post('/payment/export-payments', [PaymentController::class, 'exportPayments'])->name('payment.export-payments');

    Route::resources([
        'user' => UserController::class,
        'rooms' => RoomsController::class,
        'front-end' => FrontEndController::class,
        'booking' => BookingController::class,
        'payment' => PaymentController::class,
        'dashboard' => DashboardController::class,
    ]);
    
});
