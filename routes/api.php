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

    Route::resources([
        'user' => UserController::class,
    ]);
    
});
