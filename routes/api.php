<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FoodController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RestaurantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');



// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});



// Restaurant routes
Route::get('/restaurants', [RestaurantController::class, 'index']);
Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'show']);
// Category routes
Route::get(
    '/restaurants/{restaurant}/categories',
    [CategoryController::class, 'index']
);
Route::get(
    '/categories/{category}',
    [CategoryController::class, 'show']
);
// Food routes
Route::get(
    '/restaurants/{restaurant}/foods',
    [FoodController::class, 'index']
);
Route::get(
    '/foods/{food}',
    [FoodController::class, 'show']
);

Route::middleware('auth:sanctum')->group(function () {

    // Restaurant routes
    Route::post('/restaurants', [RestaurantController::class, 'store']);
    Route::put('/restaurants/{restaurant}', [RestaurantController::class, 'update']);
    Route::delete('/restaurants/{restaurant}', [RestaurantController::class, 'destroy']);

    // Category routes
    Route::post(
        '/restaurants/{restaurant}/categories',
        [CategoryController::class, 'store']
    );
    Route::put(
        '/categories/{category}',
        [CategoryController::class, 'update']
    );
    Route::delete(
        '/categories/{category}',
        [CategoryController::class, 'destroy']
    );

    // Food routes
    Route::post(
        '/restaurants/{restaurant}/foods',
        [FoodController::class, 'store']
    );
    Route::put(
        '/foods/{food}',
        [FoodController::class, 'update']
    );
    Route::delete(
        '/foods/{food}',
        [FoodController::class, 'destroy']
    );

    // Cart routes
    Route::get('/cart', [CartController::class, 'show']);

    Route::post('/cart/items', [CartController::class, 'addItem']);

    Route::patch(
        '/cart/items/{cartItem}',
        [CartController::class, 'updateItem']
    );

    Route::delete(
        '/cart/items/{cartItem}',
        [CartController::class, 'removeItem']
    );

    Route::delete(
        '/cart',
        [CartController::class, 'clear']
    );

    // Order routes
    Route::post('/checkout', [OrderController::class, 'checkout']);

    Route::get('/orders', [OrderController::class, 'index']);

    Route::get('/orders/{order}', [OrderController::class, 'show']);

    Route::patch(
        '/orders/{order}/status',
        [OrderController::class, 'updateStatus']
    );

    Route::patch(
        '/orders/{order}/cancel',
        [OrderController::class, 'cancel']
    );

    Route::patch(
        '/orders/{order}/cancel-by-restaurant',
        [OrderController::class, 'cancelByRestaurant']
    );
});
