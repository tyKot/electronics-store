<?php

namespace App\QueryFilters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class TagsFilter implements Filter
{
    /**
     * @param Builder $query
     * @param mixed $value
     * @param string $property
     * @return Builder
     */
    public function __invoke(Builder $query, $value, string $property): void
    {
        // Приводим к массиву, если вдруг пришло одно значение
        $tags = is_array($value) ? $value : [$value];

        $query->where(function (Builder $q) use ($tags) {
            foreach ($tags as $tag) {
                match ($tag) {
                    'new'      => $q->orWhere('is_new', true),
                    'featured' => $q->orWhere('is_featured', true),
                    'discount' => $q->orWhereColumn('old_price', '>', 'price'),
                    default    => null,
                };
            }
        });
    }
}
