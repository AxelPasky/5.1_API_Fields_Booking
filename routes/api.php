<?php

use App\Http\Controllers\Api\Admin\AdminFieldController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\FieldController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rotte pubbliche
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Rotte protette per tutti gli utenti autenticati
Route::middleware('auth:api')->group(function () {
    // User
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/fields', [FieldController::class, 'index']);
    Route::get('/fields/{field}', [FieldController::class, 'show']);

    // Bookings (User)
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);
});

// Rotte protette solo per amministratori
Route::middleware(['auth:api', 'role:Admin'])->prefix('admin')->group(function () {
    // Fields (Admin)
    Route::post('/fields', [AdminFieldController::class, 'store']);
    Route::put('/fields/{field}', [AdminFieldController::class, 'update']);
    Route::delete('/fields/{field}', [AdminFieldController::class, 'destroy']);
});