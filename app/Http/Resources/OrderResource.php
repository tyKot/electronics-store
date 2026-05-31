<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->getStatusColor(),
            ],
            'total_amount' => number_format($this->total_amount, 0, '.', ' '),
            'payment' => [
                'transaction_id' => $this->transaction_id,
                'method' => $this->payment_method,
                'paid_at' => $this->paid_at?->format('d.m.Y H:i'),
            ],
            'created_at' => $this->created_at->format('d.m.Y H:i'),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
