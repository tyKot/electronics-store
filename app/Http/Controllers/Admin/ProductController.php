<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use OpenAI\Laravel\Facades\OpenAI;

class ProductController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Products/Index', [
            'products' => ProductResource::collection(
                Product::latest()->paginate(20)
            ),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'brand' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'specs' => ['nullable', 'array'],
        ]);

        $product = Product::create($validated);

        return back()->with('success', 'Товар создан');
    }

    /**
     * Генерация SEO-описания через OpenAI
     */
    public function generateDescription(Request $request, Product $product)
    {
        $request->validate([
            'keywords' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'Ты — опытный копирайтер интернет-магазина электроники. Создавай продающие описания на русском языке.'],
                    [
                        'role' => 'user',
                        'content' => sprintf(
                            'Составь продающее SEO-описание для товара "%s" (бренд: %s, цена: %s руб). %sКлючевые слова: %s. Ответ дай только текстом описания, без пояснений.',
                            $product->name,
                            $product->brand,
                            number_format($product->price, 0, '.', ' '),
                            $product->specs ? 'Характеристики: ' . json_encode($product->specs) . '. ' : '',
                            $request->input('keywords', 'купить, лучший, современный')
                        )
                    ],
                ],
                'max_tokens' => 400,
                'temperature' => 0.7,
            ]);

            $description = $response->choices[0]->message->content;

            $product->update(['description' => trim($description)]);

            return back()->with('success', 'Описание сгенерировано');
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['ai' => 'Ошибка генерации: ' . $e->getMessage()]);
        }
    }
}
