<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class BotKnowledgeController extends Controller
{
    /**
     * Главная страница знаний для бота
     * Чистый HTML без JS - бот легко прочитает
     */
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->withCount('products')
            ->get();

        $featuredProducts = Product::where('is_active', true)
            ->where('is_featured', true)
            ->limit(20)
            ->get();

        return view('bot.knowledge.index', compact('categories', 'featuredProducts'));
    }

    /**
     * Страница категории с товарами
     */
    public function category(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $products = Product::where('is_active', true)
            ->where('category_id', $category->id)
            ->get();

        return view('bot.knowledge.category', compact('category', 'products'));
    }

    /**
     * Детальная страница товара
     */
    public function product(string $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        return view('bot.knowledge.product', compact('product'));
    }

    /**
     * FAQ страница
     */
    public function faq()
    {
        $faqs = [
            [
                'q' => 'Какие способы доставки доступны?',
                'a' => 'Мы предлагаем курьерскую доставку по Москве и Санкт-Петербургу (1-2 дня), доставку по России через СДЭК и Почту России (3-7 дней), а также самовывоз из наших пунктов выдачи.'
            ],
            [
                'q' => 'Какая гарантия на товары?',
                'a' => 'На всю электронику действует официальная гарантия производителя от 1 до 3 лет. Дополнительно мы предоставляем 14 дней на возврат товара надлежащего качества.'
            ],
            [
                'q' => 'Как оплатить заказ?',
                'a' => 'Доступны следующие способы оплаты: банковская карта (Visa, MasterCard, МИР), СБП, электронные кошельки (ЮMoney, QIWI), наличные при получении, безналичный расчёт для юридических лиц.'
            ],
            [
                'q' => 'Можно ли оформить рассрочку?',
                'a' => 'Да, мы предлагаем рассрочку 0-0-12 от банков-партнёров: Тинькофф, Альфа-Банк, Сбербанк. Оформление занимает 5 минут онлайн.'
            ],
        ];

        return view('bot.knowledge.faq', compact('faqs'));
    }
}
