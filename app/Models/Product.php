<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'brand',
        'specs',       // JSONB колонка
        'images',      // JSONB массив изображений
        'is_new',
        'stock',
        'category_id',
        'old_price',  // Старая цена для отображения скидки
        'is_featured', // Хит продаж
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_new' => 'boolean',
        'specs' => 'array',    // Автоматическая конвертация JSON в массив
        'images' => 'array',
        'old_price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // Scope для фильтрации по диапазону цен (используется в QueryBuilder)
    public function scopePriceRange($query, $range)
    {
        if (is_string($range) && str_contains($range, '-')) {
            [$min, $max] = explode('-', $range);
            return $query->whereBetween('price', [(int) $min, (int) $max]);
        }
        return $query;
    }

    /**
     * Фильтрация по минимальной цене
     */
    public function scopePriceMin(Builder $query, mixed $value): void
    {
        if ($value !== null && $value !== '') {
            $query->where('price', '>=', (float) $value);
        }
    }

    /**
     * Фильтрация по максимальной цене
     */
    public function scopePriceMax(Builder $query, mixed $value): void
    {
        if ($value !== null && $value !== '') {
            $query->where('price', '<=', (float) $value);
        }
    }

    // Scope для поиска по JSONB specs (PostgreSQL)
    public function scopeHasSpec($query, string $key, mixed $value)
    {
        return $query->whereRaw("specs->>? = ?", [$key, $value]);
    }

    // Product.php
    public function scopeHasTag(Builder $query, string $tag): void
    {
        match ($tag) {
            'new' => $query->where('is_new', true),
            'featured' => $query->where('is_featured', true),
            'discount' => $query->whereColumn('old_price', '>', 'price'),
            default => null,
        };
    }
}
