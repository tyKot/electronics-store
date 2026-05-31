<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Получить все избранные товары текущего пользователя
     */
    public function index(Request $request): JsonResponse
    {
        $favorites = $request->user()
            ->favorites()
            ->paginate(20);

        return response()->json([
            'data' => ProductResource::collection($favorites),
            'meta' => [
                'total' => $favorites->total(),
                'current_page' => $favorites->currentPage(),
                'last_page' => $favorites->lastPage(),
            ],
        ]);
    }

    /**
     * Переключить статус избранного (toggle)
     */
    public function toggle(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();
        $exists = $user->favorites()->where('product_id', $productId)->exists();

        if ($exists) {
            $user->favorites()->detach($productId);
            return response()->json(['is_favorite' => false]);
        }

        $user->favorites()->attach($productId);
        return response()->json(['is_favorite' => true]);
    }

    /**
     * Проверить статусы для списка товаров (массовая проверка)
     * Полезно при загрузке каталога, чтобы не делать N запросов
     */
    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_ids' => ['required', 'array'],
            'product_ids.*' => ['integer'],
        ]);

        $favoriteIds = $request->user()
            ->favorites()
            ->whereIn('products.id', $validated['product_ids'])
            ->pluck('products.id')
            ->toArray();

        return response()->json(['favorite_ids' => $favoriteIds]);
    }
}
