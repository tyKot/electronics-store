<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => (float) $this->price,
            'brand' => $this->brand,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'image' => $this->images[0] ?? '/images/placeholder.webp',
            'images' => $this->images ?? [],
            'specs' => $this->specs ?? [],
            'is_new' => $this->is_new,
            'stock' => $this->stock,
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'created_at' => $this->created_at->format('d.m.Y'),
            'old_price' => $this->old_price ? (float) $this->old_price : null,
            'is_featured' => $this->is_featured,
        ];
    }
}
