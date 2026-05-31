<?php
namespace App\Interfaces;

use App\Models\Order;

interface PaymentGatewayInterface
{
    /**
     * @return array{success: bool, transaction_id: string, message: string}
     */
    public function process(Order $order): array;
}
