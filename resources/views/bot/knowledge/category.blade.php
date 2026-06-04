# Категория: {{ $category->name }}

@if($category->description)
{{ $category->description }}
@endif

**Всего товаров в категории:** {{ $products->count() }}

---

## Товары категории

@foreach($products as $product)
### {{ $product->name }}

- **Бренд:** {{ $product->brand }}
- **Цена:** {{ number_format($product->price, 0, '.', ' ') }} ₽
@if($product->old_price)
- **Старая цена:** {{ number_format($product->old_price, 0, '.', ' ') }} ₽
@endif
- **Наличие:** {{ $product->stock > 0 ? 'В наличии (' . $product->stock . ' шт.)' : 'Нет в наличии' }}
@if($product->short_description)
- **Описание:** {{ $product->short_description }}
@endif
@if($product->specs)
- **Основные характеристики:**
@foreach($product->specs as $key => $value)
  - {{ $key }}: {{ $value }}
@endforeach
@endif
- **Подробнее:** {{ route('bot.knowledge.product', $product->slug) }}

@endforeach

---

- [← Все категории]({{ route('bot.knowledge.index') }})
- [FAQ]({{ route('bot.knowledge.faq') }})
