<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\AuthController;
use \App\Http\Controllers\OrderController;

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

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::middleware('jwt')->delete('/destroy', [AuthController::class, 'destroy'])->name('account_destroy');

Route::middleware('jwt')->group(function () {
    Route::get('/order/', [OrderController::class, 'index'])->name('list');
    Route::get('/order/show/{id}', [OrderController::class, 'show'])->name('detail');
    Route::post('/order/create', [OrderController::class, 'create']);
    Route::put('/order/update/{id}', [OrderController::class, 'update']);
    Route::delete('/order/destroy/{id}', [OrderController::class, 'destroy']);
});
