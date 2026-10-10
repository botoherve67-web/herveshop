<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HerveShop Mobile API Routes
|--------------------------------------------------------------------------
*/

// Configuration & Paramètres
Route::get('/settings', [ConfigController::class, 'settings']);
Route::post('/contact', [ConfigController::class, 'contact']);

// Catalogue & Produits
Route::get('/home', [CatalogController::class, 'home']);
Route::get('/categories', [CatalogController::class, 'categories']);
Route::get('/products', [CatalogController::class, 'products']);
Route::get('/products/{idOrSlug}', [CatalogController::class, 'product']);
Route::post('/promo-code/verify', [OrderController::class, 'verifyPromo']);

// Authentification Mobile
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/firebase', [AuthController::class, 'firebaseAuth']);

// Routes protégées par jeton Mobile
Route::middleware('auth.api')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::patch('/auth/profile', [AuthController::class, 'updateProfile']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/payment-proof', [OrderController::class, 'submitPaymentProof']);

    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);

    Route::post('/products/{product}/reviews', [ReviewController::class, 'store']);
});
