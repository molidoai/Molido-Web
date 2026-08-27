<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - MOLIDO CORE
|--------------------------------------------------------------------------
|
| Versioned API: /api/v1/...
|
*/

Route::prefix('v1')->group(function () {

    // Public Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });

        // Example permission-protected routes (will expand in later phases)
        // Route::get('/customers', ...)->middleware('permission:crm.customer.read');
    });
});
