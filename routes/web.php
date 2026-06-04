<?php

use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\BotKnowledgeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/dd', function () {
    dd(route('catalog.index', ['category_id' => 1]));
});

Route::get('/dashboard', function () {
    return redirect(route('home'));
})->name('dashboard');

// Главная
Route::get('/', [CatalogController::class, 'index'])->name('home');

// Каталог и товары
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Корзина и Чекаут
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});

// Админ-панель (Spatie Permission: middleware role:admin)
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Управление товарами
        Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
        Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
        Route::post('/products/{product}/generate-description', [AdminProductController::class, 'generateDescription'])
            ->name('products.generate-description');
    });


Route::middleware('auth:sanctum')->prefix('api/favorites')->group(function () {
    Route::get('/', [FavoriteController::class, 'index']);
    Route::post('/toggle/{productId}', [FavoriteController::class, 'toggle']);
    Route::post('/check', [FavoriteController::class, 'check']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});


// Публичные страницы для краулеров и SEO
Route::prefix('knowledge')->name('bot.knowledge.')->group(function () {
    Route::get('/', [BotKnowledgeController::class, 'index'])->name('index');
    Route::get('/category/{slug}', [BotKnowledgeController::class, 'category'])->name('category');
    Route::get('/product/{slug}', [BotKnowledgeController::class, 'product'])->name('product');
    Route::get('/faq', [BotKnowledgeController::class, 'faq'])->name('faq');
});
require __DIR__ . '/auth.php'; // Laravel Breeze
