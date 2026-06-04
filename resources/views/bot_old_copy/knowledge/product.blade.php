<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="index, follow">
    <meta name="description" content="{{ $product->short_description }}">
    <title>{{ $product->name }} - {{ $product->brand }}</title>
</head>
<body>
    <article itemscope itemtype="https://schema.org/Product">
        <h1 itemprop="name">{{ $product->name }}</h1>

        <p><strong>Бренд:</strong> <span itemprop="brand">{{ $product->brand }}</span></p>
        <p><strong>Категория:</strong> {{ $product->category->name ?? 'Электроника' }}</p>
        <p><strong>Артикул:</strong> <span itemprop="sku">{{ $product->sku ?? $product->id }}</span></p>

        @if($product->images)
            @foreach($product->images as $image)
                <img src="{{ $image }}" alt="{{ $product->name }}" itemprop="image">
            @endforeach
        @endif

        <div itemprop="offers" itemscope itemtype="https://schema.org/Offer">
            <p><strong>Цена:</strong>
                <span itemprop="price" content="{{ $product->price }}">
                    {{ number_format($product->price, 0, '.', ' ') }} ₽
                </span>
                <meta itemprop="priceCurrency" content="RUB">
                <meta itemprop="availability" content="{{ $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}">
            </p>
        </div>

        <h2>Описание</h2>
        <div itemprop="description">
            {!! nl2br(e($product->description)) !!}
        </div>

        @if($product->specs)
            <h2>Характеристики</h2>
            <table>
                @foreach($product->specs as $key => $value)
                    <tr>
                        <th>{{ $key }}</th>
                        <td>{{ $value }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        <h2>Наличие</h2>
        <p>{{ $product->stock > 0 ? "В наличии: {$product->stock} шт." : 'Нет в наличии' }}</p>
    </article>

    <p><a href="{{ route('bot.knowledge.index') }}">← Вернуться в каталог</a></p>
</body>
</html>
