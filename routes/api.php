<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::apiResource('/category', CategoryController::class);
Route::apiResource('/products', ProductController::class);
Route::get('/products/{id}/{slug}', [ProductController::class,'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('/cart', CartController::class);
    Route::apiResource('cart-items', CartItemController::class);
    Route::apiResource('orders', OrderController::class);
    Route::patch('orders/{order}/order-status', [OrderController::class, 'order_status']);
    Route::patch('orders/{order}/payment-status', [OrderController::class, 'payment_status']);
});
