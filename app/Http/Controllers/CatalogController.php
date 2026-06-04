<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\QueryFilters\TagsFilter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CatalogController extends Controller
{
    public function index(Request $request): Response
    {
        // Построитель запросов с фильтрами, сортировкой и связями
        $products = QueryBuilder::for(Product::class)
            ->allowedFilters(
                AllowedFilter::exact('brand'),
                // AllowedFilter::exact('category'), // 👈 НОВОЕ: фильтр по slug категории
                AllowedFilter::exact('category_id'),
                AllowedFilter::custom('tags', new TagsFilter()), // Если используете кастомный фильтр
                AllowedFilter::scope('price_min'), // Или через scope/where
                AllowedFilter::scope('price_max'),
                AllowedFilter::scope('price_range'),  // scopePriceRange в модели
                AllowedFilter::partial('name'),
                AllowedFilter::callback('specs', function ($query, $value) {
                    // Фильтрация по JSONB: ?filter[specs][RAM]=16GB
                    if (is_array($value)) {
                        foreach ($value as $key => $val) {
                            $query->hasSpec($key, $val);
                        }
                    }
                }),
            )
            ->allowedSorts('price', 'created_at', 'name')
            ->allowedIncludes('category')
            ->defaultSort('-created_at')
            ->paginate($request->integer('per_page', 12))
            ->withQueryString(); // Сохраняем параметры в URL при пагинации

        // Получаем список брендов для фильтра
        $brands = Brand::orderBy('name')->get(['id', 'name', 'slug']);

        $categories = Category::where('is_active', true)
            // 👇 Фильтруем категории, у которых есть хотя бы один активный товар
            ->whereHas('products', fn($q) => $q->where('is_active', true))
            // 👇 Считаем количество активных товаров (алиас products_count)
            ->withCount(['products' => fn($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Catalog/Index', [
            'products' => ProductResource::collection($products),
            'brands' => $brands,
            'categories' => $categories,
            'filters' => $request->only(['sort', 'filter', 'page']),
        ]);
    }
}
