<?php

use App\Http\Controllers\Api\Admin\AdminFieldController;
use App\Http\Controllers\Api\Admin\StatisticsController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\User\BookingController;
use App\Http\Controllers\Api\Public\FieldController;
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

Route::get('/health', function () {
    try {
        $dbConnection = DB::connection()->getPdo();
        $tables = DB::select('SHOW TABLES');
        
        return response()->json([
            'status' => 'ok',
            'database' => 'connected',
            'tables_count' => count($tables),
            'tables' => $tables
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'database' => 'not connected',
            'error' => $e->getMessage()
        ], 500);
    }
});


// PUBLIC
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// AUTH USER
Route::middleware('auth:api')->group(function () {
    // User
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/fields', [FieldController::class, 'index']);
    Route::get('/fields/{field}', [FieldController::class, 'show']);
    Route::get('/fields/{field}/availability' , [FieldController::class, 'getAvailability']);

    // Bookings (User)
    Route::post('/bookings/calculate-price', [BookingController::class, 'calculatePrice']); 
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::put('/bookings/{booking}', [BookingController::class, 'update']); 
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);
});

// AUTH ADMIN
Route::middleware(['auth:api', 'role:Admin'])->prefix('admin')->group(function () {
    // Statistics (Admin)
    Route::get('/statistics/revenue', [StatisticsController::class, 'revenue']);
    Route::get('/statistics/field-performance', [StatisticsController::class, 'fieldPerformance']); 

    // Fields (Admin)
    Route::post('/fields', [AdminFieldController::class, 'store']);
    Route::put('/fields/{field}', [AdminFieldController::class, 'update']);
    Route::delete('/fields/{field}', [AdminFieldController::class, 'destroy']);
});

Route::get('/debug-oauth', function () {
    $clients = \DB::table('oauth_clients')->get();
    return response()->json([
        'clients' => $clients,
        'personal_client_id' => env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID'),
        'personal_client_secret' => env('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET'),
        'password_client_id' => env('PASSPORT_PASSWORD_GRANT_CLIENT_ID'),
        'password_client_secret' => env('PASSPORT_PASSWORD_GRANT_CLIENT_SECRET'),
    ]);
});