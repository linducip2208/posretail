<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentGatewayController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\TableController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/categories', [CategoryController::class, 'index']);

        Route::get('/payment-methods', [PaymentMethodController::class, 'index']);

        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
        Route::post('/products/barcode', [ProductController::class, 'byBarcode']);

        Route::get('/customers', [CustomerController::class, 'index']);
        Route::get('/customers/{customer}', [CustomerController::class, 'show']);
        Route::post('/customers', [CustomerController::class, 'store']);

        Route::get('/orders/today', [OrderController::class, 'today']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::post('/orders', [OrderController::class, 'store']);

        Route::get('/tables', [TableController::class, 'index']);

        // Payment Gateway
        Route::post('/payment/create', [PaymentGatewayController::class, 'createTransaction']);
        Route::post('/payment/status', [PaymentGatewayController::class, 'checkStatus']);
        Route::get('/payment/presets', [PaymentGatewayController::class, 'presets']);
    });
});

// Webhooks (no auth - called by payment gateways)
Route::prefix('v1/webhooks')->group(function () {
    Route::post('/{providerCode}', [PaymentGatewayController::class, 'webhook']);
});
