<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class BotKnowledgeController extends Controller
{
    /**
     * Главная страница знаний (отдаёт Markdown)
     */
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->select('categories.*')
            ->selectSub(
                Product::where('is_active', true)
                    ->whereColumn('category_id', 'categories.id')
                    ->selectRaw('COUNT(*)'),
                'products_count'
            )
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('products')
                    ->whereColumn('products.category_id', 'categories.id')
                    ->where('products.is_active', true);
            })
            ->orderByDesc('products_count')
            ->get();


        $featuredProducts = Product::where('is_active', true)
            ->where('is_featured', true)
            ->where('stock', '>', 0)
            ->with('category')
            ->limit(20)
            ->get();

        return response()
            ->view('bot.knowledge.index', compact('categories', 'featuredProducts'))
            ->header('Content-Type', 'text/markdown; charset=utf-8');
    }

    /**
     * FAQ страница (Markdown)
     */
    public function faq()
    {
        return response()
            ->view('bot.knowledge.faq')
            ->header('Content-Type', 'text/markdown; charset=utf-8');
    }

    /**
     * Страница категории (Markdown)
     */
    public function category(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $products = Product::where('is_active', true)
            ->where('category_id', $category->id)
            ->get();

        return response()
            ->view('bot.knowledge.category', compact('category', 'products'))
            ->header('Content-Type', 'text/markdown; charset=utf-8');
    }

    /**
     * Страница товара (Markdown)
     */
    public function product(string $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        return response()
            ->view('bot.knowledge.product', compact('product'))
            ->header('Content-Type', 'text/markdown; charset=utf-8');
    }
}
