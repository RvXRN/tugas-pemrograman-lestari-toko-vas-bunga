<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Customer\CatalogController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\PaymentController;

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    // Public Auth Routes
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:forgot-password');
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

    // Public Catalog Routes
    Route::get('/catalog/categories', [CatalogController::class, 'categories']);
    Route::get('/catalog/products', [CatalogController::class, 'products'])->middleware('throttle:search');
    Route::get('/catalog/products/{slug}', [CatalogController::class, 'showProduct']);

    // Public / Guest Cart Routes
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::delete('/cart/{itemId}', [CartController::class, 'destroy']);

    // Payment Webhook
    Route::post('/payments/webhook', [PaymentController::class, 'webhook']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/checkout', [CheckoutController::class, 'process']);
        
        // Admin Routes
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
            
            Route::apiResource('categories', CategoryController::class);
            Route::apiResource('products', ProductController::class);
        });
    });
});
