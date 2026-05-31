<?php
namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\Order;
use Illuminate\Support\Str;

class MockPaymentService implements PaymentGatewayInterface
{
    public function process(Order $order): array
    {
        // Имитация задержки сети
        usleep(500000); // 0.5 сек

        $isSuccess = rand(1, 100) <= 95; // 95% успех

        return [
            'success' => $isSuccess,
            'transaction_id' => Str::uuid()->toString(),
            'message' => $isSuccess ? 'Оплата прошла успешно' : 'Ошибка банка',
        ];
    }
}
