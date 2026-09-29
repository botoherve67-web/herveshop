<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ErrorLogController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\PromoCodeController as AdminPromoCodeController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryPageController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/produits', [ProductController::class, 'index'])->name('products.index');
Route::get('/categories', [CategoryPageController::class, 'index'])->name('categories.index');
Route::get('/a-propos', [AboutController::class, 'index'])->name('about.index');
Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,10')->name('contact.store');
Route::get('/produits/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/comparer', [CompareController::class, 'index'])->name('compare.index');
Route::post('/comparer/{product}', [CompareController::class, 'store'])->name('compare.store');
Route::delete('/comparer/{product}', [CompareController::class, 'destroy'])->name('compare.destroy');
Route::delete('/comparer', [CompareController::class, 'clear'])->name('compare.clear');

Route::get('/panier', [CartController::class, 'index'])->name('cart.index');
Route::post('/panier/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/panier/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/panier/{product}', [CartController::class, 'remove'])->name('cart.remove');

Route::middleware('guest')->group(function () {
    Route::get('/connexion', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/connexion', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/inscription', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/inscription', [AuthController::class, 'register'])->middleware('throttle:5,10');
    Route::get('/mot-de-passe-oublie', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:5,10')->name('password.email');
    Route::get('/nouveau-mot-de-passe/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/nouveau-mot-de-passe', [PasswordResetController::class, 'reset'])->name('password.update');
});
Route::post('/deconnexion', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/commande', [OrderController::class, 'checkout'])->name('checkout.index');
    Route::post('/commande', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/commandes', [OrderController::class, 'myOrders'])->name('orders.index');
    Route::get('/commandes/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/commandes/{order}/facture', [DocumentController::class, 'invoice'])->name('orders.invoice');
    Route::get('/commandes/{order}/recu', [DocumentController::class, 'receipt'])->name('orders.receipt');
    Route::post('/commandes/{order}/preuve-paiement', [OrderController::class, 'submitPaymentProof'])->name('orders.payment-proof');

    Route::get('/mon-compte', [AccountController::class, 'dashboard'])->name('account.dashboard');
    Route::patch('/mon-compte', [AccountController::class, 'update'])->name('account.update');

    Route::post('/produits/{product}/avis', [ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/produits/{product}/avis/{review}/signaler', [ReviewController::class, 'report'])->name('reviews.report');
    Route::get('/mes-listes', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/produits/{product}/liste', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::patch('/mes-listes/{wishlistItem}', [WishlistController::class, 'update'])->name('wishlist.update');
    Route::delete('/mes-listes/{wishlistItem}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
});

Route::middleware(['auth', 'admin', 'admin.activity'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::middleware('admin:products')->group(function () {
        Route::get('/produits', [AdminProductController::class, 'index'])->name('products.index');
        Route::get('/produits/creer', [AdminProductController::class, 'create'])->name('products.create');
        Route::post('/produits', [AdminProductController::class, 'store'])->name('products.store');
        Route::get('/produits/{product}/modifier', [AdminProductController::class, 'edit'])->name('products.edit');
        Route::put('/produits/{product}', [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('/produits/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
        Route::patch('/produits/{product}/images/{image}/principale', [AdminProductController::class, 'setPrimaryImage'])->name('products.images.primary');
        Route::delete('/produits/{product}/images/{image}', [AdminProductController::class, 'destroyImage'])->name('products.images.destroy');
    });

    Route::middleware('admin:products')->group(function () {
        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    });

    Route::middleware('admin:orders')->group(function () {
        Route::get('/commandes', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/commandes/export', [AdminOrderController::class, 'export'])->name('orders.export');
        Route::get('/commandes/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::patch('/commandes/{order}/statut', [AdminOrderController::class, 'updateStatut'])->name('orders.statut');
        Route::patch('/commandes/{order}/paiement', [AdminOrderController::class, 'confirmerPaiement'])->name('orders.paiement');
        Route::get('/commandes/{order}/preuve-paiement', [AdminOrderController::class, 'paymentProof'])->name('orders.payment-proof');
        Route::get('/commandes/{order}/facture', [DocumentController::class, 'invoice'])->name('orders.invoice');
        Route::get('/commandes/{order}/recu', [DocumentController::class, 'receipt'])->name('orders.receipt');
    });

    Route::middleware('admin:super_admin')->group(function () {
        Route::get('/clients', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/clients/export', [AdminUserController::class, 'export'])->name('users.export');
        Route::get('/clients/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::patch('/clients/{user}/statut', [AdminUserController::class, 'toggleStatus'])->name('users.status');
        Route::patch('/clients/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');

        Route::get('/codes-promo', [AdminPromoCodeController::class, 'index'])->name('promo-codes.index');
        Route::post('/codes-promo', [AdminPromoCodeController::class, 'store'])->name('promo-codes.store');
        Route::delete('/codes-promo/{promoCode}', [AdminPromoCodeController::class, 'destroy'])->name('promo-codes.destroy');

        Route::get('/avis', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::patch('/avis/{review}/approuver', [AdminReviewController::class, 'approve'])->name('reviews.approve');
        Route::patch('/avis/{review}/classer-signalement', [AdminReviewController::class, 'dismissReport'])->name('reviews.dismiss-report');
        Route::delete('/avis/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::get('/historique-actions', [ActivityLogController::class, 'index'])->name('activity.index');
        Route::get('/journal-erreurs', [ErrorLogController::class, 'index'])->name('errors.index');
        Route::get('/maintenance', [MaintenanceController::class, 'edit'])->name('maintenance.edit');
        Route::patch('/maintenance', [MaintenanceController::class, 'update'])->name('maintenance.update');
    });
});
