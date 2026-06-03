<?php

Route::prefix('bot')->group(function () {
    Route::post('/order-status', [\App\Http\Controllers\Api\BotController::class, 'getOrderStatus']);
    Route::post('/search-products', [\App\Http\Controllers\Api\BotController::class, 'searchProducts']);
    Route::post('/get-product-details', [\App\Http\Controllers\Api\BotController::class, 'getProductDetails']);
    Route::post('/get-popular-categories', [\App\Http\Controllers\Api\BotController::class, 'getPopularCategories']);
});
