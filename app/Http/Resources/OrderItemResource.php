<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'price' => (float) $this->price_at_moment,
            'subtotal' => (float) ($this->price_at_moment * $this->quantity),
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'image' => $this->product->images[0] ?? null,
            ],
        ];
    }
}
