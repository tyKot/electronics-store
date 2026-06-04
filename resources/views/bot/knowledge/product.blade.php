# {{ $product->name }}

**Бренд:** {{ $product->brand }}
**Категория:** {{ $product->category->name ?? 'Электроника' }}
**Артикул:** {{ $product->sku ?? $product->id }}

---

## Цена и наличие

- **Цена:** {{ number_format($product->price, 0, '.', ' ') }} ₽
@if($product->old_price)
- **Старая цена:** {{ number_format($product->old_price, 0, '.', ' ') }} ₽
- **Скидка:** {{ round((1 - $product->price / $product->old_price) * 100) }}%
@endif
- **Наличие:** {{ $product->stock > 0 ? 'В наличии (' . $product->stock . ' шт.)' : 'Нет в наличии' }}

---

## Описание

{{ $product->description ?: $product->short_description ?: 'Описание будет добавлено позже.' }}

@if($product->specs)
---

## Характеристики

| Параметр | Значение |
|----------|----------|
@foreach($product->specs as $key => $value)
| {{ $key }} | {{ $value }} |
@endforeach
@endif

@if($product->images)
---

## Изображения

@foreach($product->images as $image)
- {{ $image }}
@endforeach
@endif

---

- [← Вернуться в категорию]({{ route('catalog.index', ['category_id' => $product->category->id]) }})
- [Все категории]({{ route('catalog.index') }})
- [FAQ]({{ route('bot.knowledge.faq') }})
