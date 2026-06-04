@php
    // Отключаем layout, чтобы отдавать чистый Markdown
    header('Content-Type: text/markdown; charset=utf-8');
@endphp
# Интернет-магазин электроники

Мы предлагаем более 10 000 товаров от ведущих мировых брендов: Apple, Samsung, Xiaomi, Sony, Huawei.

---

## Категории товаров

@foreach($categories as $category)
### {{ $category->name }}

- **Количество товаров:** {{ $category->products_count }}
- **Ссылка на категорию:** {{ route('catalog.index', $category->id) }}
@if($category->description)
- **Описание:** {{ $category->description }}
@endif

@endforeach
---

## Популярные товары

@foreach($featuredProducts as $product)
### {{ $product->name }}

- **Бренд:** {{ $product->brand }}
- **Цена:** {{ number_format($product->price, 0, '.', ' ') }} ₽
@if($product->old_price)
- **Старая цена:** {{ number_format($product->old_price, 0, '.', ' ') }} ₽ (скидка {{ round((1 - $product->price / $product->old_price) * 100) }}%)
@endif
- **Наличие:** {{ $product->stock > 0 ? 'В наличии (' . $product->stock . ' шт.)' : 'Нет в наличии' }}
@if($product->short_description)
- **Описание:** {{ $product->short_description }}
@endif
- **Подробнее:** {{ route('product.show', $product->slug) }}

@endforeach
---

## Полезные ссылки

- [Часто задаваемые вопросы]({{ route('bot.knowledge.faq') }})
- [Карта сайта]({{ url('/sitemap.xml') }})
- [Главная страница]({{ url('/') }})

---

## О магазине

- **Бренды:** Apple, Samsung, Xiaomi, Sony, Huawei
- **Гарантия:** от 1 до 3 лет (официальная)
- **Доставка:** по всей России (Москва, Санкт-Петербург и регионы)
- **Оплата:** картой, СБП, наличными, рассрочка 0-0-12
- **Возврат:** 14 дней на товар надлежащего качества
