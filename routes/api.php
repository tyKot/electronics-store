<?php

use App\Http\Controllers\Api\BotController;

Route::prefix('bot')->middleware(['bot.token','throttle:bot-api'])->group(function () {
    Route::post('/order-status', [BotController::class, 'getOrderStatus']);
    Route::post('/search-products', [BotController::class, 'searchProducts']);
    Route::post('/get-product-details', [BotController::class, 'getProductDetails']);
    Route::post('/get-popular-categories', [BotController::class, 'getPopularCategories']);
});


Route::get('/bot/debug', [BotController::class, 'debug']);
