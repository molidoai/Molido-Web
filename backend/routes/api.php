<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Customer\CustomerController;
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

        // Customer Center (central identity)
        Route::prefix('customers')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])
                ->middleware('permission:crm.customer.read');

            Route::post('/', [CustomerController::class, 'store'])
                ->middleware('permission:crm.customer.create');

            Route::get('/{id}', [CustomerController::class, 'show'])
                ->middleware('permission:crm.customer.read');

            Route::put('/{id}', [CustomerController::class, 'update'])
                ->middleware('permission:crm.customer.update');

            Route::delete('/{id}', [CustomerController::class, 'destroy'])
                ->middleware('permission:crm.customer.delete');
        });
    });
});
