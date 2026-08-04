<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}/{slug}', [ProductController::class, 'show']);

Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    Route::apiResource('products', ProductController::class)
        ->except(['index', 'show']);

    Route::apiResource('categories', CategoryController::class)
        ->except(['index', 'show']);

    Route::patch('orders/{order}/order-status', [OrderController::class, 'order_status']);

    Route::patch('orders/{order}/payment-status', [OrderController::class, 'payment_status']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });
    
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('cart', CartController::class)
        ->only(['index', 'store', 'show']);
    Route::get('/cart-count', [CartController::class, 'getCartCount']);

    Route::apiResource('cart-items', CartItemController::class);
    Route::apiResource('orders', OrderController::class);

    Route::post('payment/create-order', [PaymentController::class, 'createOrder']);
    Route::post('payment/verify', [PaymentController::class, 'verify']);
});
