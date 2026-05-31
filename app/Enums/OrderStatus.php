<?php
namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Ожидает оплаты',
            self::PROCESSING => 'В обработке',
            self::COMPLETED => 'Завершен',
            self::CANCELLED => 'Отменен',
        };
    }

    public function getStatusColor(): string
    {
        return match($this) {
            self::PENDING => 'text-yellow-500',
            self::PROCESSING => 'text-blue-500',
            self::COMPLETED => 'text-green-500',
            self::CANCELLED => 'text-red-500',
        };
    }
}
