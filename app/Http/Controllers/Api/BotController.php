<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BotController extends Controller
{
    /**
     * Получить информацию о заказе по номеру
     */
    public function getOrderStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => 'required|string',
            'user_email' => 'required|email',
        ]);

        $order = Order::where('order_number', $validated['order_number'])
            ->whereHas('user', fn($q) => $q->where('email', $validated['user_email']))
            ->with(['items.product'])
            ->first();

        if (!$order) {
            return response()->json([
                'found' => false,
                'message' => 'Заказ не найден. Проверьте номер и email.'
            ], 404);
        }

        return response()->json([
            'found' => true,
            'order' => [
                'number' => $order->order_number,
                'status' => $order->status->label(),
                'status_value' => $order->status->value,
                'total' => number_format($order->total_amount, 0, '.', ' '),
                'items_count' => $order->items->count(),
                'created_at' => $order->created_at->format('d.m.Y H:i'),
                'shipping_address' => $order->shipping_address,
                'items' => $order->items->map(fn($item) => [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => number_format($item->price_at_moment, 0, '.', ' '),
                ]),
            ]
        ]);
    }

    /**
     * Поиск товаров по категории
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'nullable|string',
            'brand' => 'nullable|string',
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric',
            'limit' => 'nullable|integer|max:10',
        ]);

        $query = Product::where('is_active', true);

        if (!empty($validated['category'])) {
            $query->whereHas('category', fn($q) =>
                $q->where('slug', $validated['category'])
            );
        }

        if (!empty($validated['brand'])) {
            $query->where('brand', $validated['brand']);
        }

        if (!empty($validated['min_price'])) {
            $query->where('price', '>=', $validated['min_price']);
        }

        if (!empty($validated['max_price'])) {
            $query->where('price', '<=', $validated['max_price']);
        }

        $products = $query->limit($validated['limit'] ?? 5)
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'found' => $products->count(),
            'products' => $products->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'brand' => $p->brand,
                'price' => number_format($p->price, 0, '.', ' '),
                'old_price' => $p->old_price ? number_format($p->old_price, 0, '.', ' ') : null,
                'short_description' => $p->short_description,
                'in_stock' => $p->stock > 0,
                'stock_count' => $p->stock,
                'url' => route('products.show', $p->slug),
            ]),
        ]);
    }

    /**
     * Получить информацию о товаре по ID
     */
    public function getProductDetails(Request $request, int $productId): JsonResponse
    {
        $product = Product::with('category')->findOrFail($productId);

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'category' => $product->category->name,
                'price' => $product->price,
                'old_price' => $product->old_price ? $product->old_price : null,
                'description' => $product->description,
                'specs' => $product->specs,
                'in_stock' => $product->stock > 0,
                'stock_count' => $product->stock,
                'images' => $product->images,
                'url' => route('products.show', $product->slug),
            ]
        ]);
    }

    /**
     * Получить популярные категории
     */
    public function getPopularCategories(): JsonResponse
    {
        $categories = \App\Models\Category::where('is_active', true)
            ->withCount('products')
            ->orderBy('products_count', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'categories' => $categories->map(fn($c) => [
                'name' => $c->name,
                'slug' => $c->slug,
                'products_count' => $c->products_count,
                'url' => route('catalog.index', ['filter[category]' => $c->slug]),
            ])
        ]);
    }
}
