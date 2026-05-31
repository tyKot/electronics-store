<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function show(Product $product): Response
    {
        // Подгружаем связанные данные для страницы товара
        $product->load(['category']);

        // Получаем похожие товары из той же категории
        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->take(4)
            ->get();


        return Inertia::render('Products/Show', [
            'product' => new ProductResource($product)->toArray(request()),
            'related' => ProductResource::collection($related)->toArray(request()),
        ]);
    }
}
