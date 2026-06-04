<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="index, follow">
    <meta name="description" content="Интернет-магазин электроники: смартфоны, ноутбуки, наушники, планшеты, умные часы">
    <title>Каталог электроники - Internet Store</title>
</head>
<body>
    <h1>Интернет-магазин электроники</h1>
    <p>Мы предлагаем более 10 000 товаров от ведущих мировых брендов: Apple, Samsung, Xiaomi, Sony, Huawei.</p>

    <h2>Категории товаров</h2>
    <ul>
        @foreach($categories as $category)
            <li>
                <a href="{{ route('bot.knowledge.category', $category->slug) }}">
                    {{ $category->name }} ({{ $category->products_count }} товаров)
                </a>
                @if($category->description)
                    <p>{{ $category->description }}</p>
                @endif
            </li>
        @endforeach
    </ul>

    <h2>Популярные товары</h2>
    @foreach($featuredProducts as $product)
        <article>
            <h3>
                <a href="{{ route('bot.knowledge.product', $product->slug) }}">
                    {{ $product->name }}
                </a>
            </h3>
            <p><strong>Бренд:</strong> {{ $product->brand }}</p>
            <p><strong>Цена:</strong> {{ number_format($product->price, 0, '.', ' ') }} ₽</p>
            @if($product->old_price)
                <p><strong>Старая цена:</strong> {{ number_format($product->old_price, 0, '.', ' ') }} ₽</p>
            @endif
            <p>{{ $product->short_description }}</p>
            <p><strong>Наличие:</strong> {{ $product->stock > 0 ? 'В наличии' : 'Нет в наличии' }}</p>
        </article>
    @endforeach

    <h2>Полезные ссылки</h2>
    <ul>
        <li><a href="{{ route('bot.knowledge.faq') }}">Часто задаваемые вопросы</a></li>
        <li><a href="/sitemap.xml">Карта сайта</a></li>
    </ul>
</body>
</html>
