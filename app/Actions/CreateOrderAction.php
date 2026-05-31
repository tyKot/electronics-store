<?php
namespace App\Actions;

use App\DTOs\CheckoutData;
use App\Enums\OrderStatus;
use App\Interfaces\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Illuminate\Support\Carbon;

class CreateOrderAction
{
    public function __construct(
        protected PaymentGatewayInterface $paymentGateway
    ) {
    }

    public function execute(CheckoutData $data): Order
    {
        return DB::transaction(function () use ($data) {
            // 1. Создаём заказ
            $order = Order::create([
                'user_id' => $data->userId,
                'order_number' => 'ORD-' . uniqid(),
                'first_name' => $data->firstName,
                'last_name' => $data->lastName,
                'email' => 'test@test.ru',
                'phone' => $data->phone,
                'city' => 'test',
                'postal_code' => 'test',
                'notes' => 'test',
                'status' => OrderStatus::PENDING,
                'shipping_address' => $data->address,
                'total_amount' => 0,
            ]);

            $total = 0;
            // Проверяем наличие на складе и создаём позиции заказа
            foreach ($data->cartItems as $item) {
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();
                if (!$product) {
                    $order->update(['status' => OrderStatus::CANCELLED]);
                    throw new RuntimeException('Товар не найден: ' . $item['product_id']);
                }

                if ((int) $product->stock < (int) $item['quantity']) {
                    $order->update(['status' => OrderStatus::CANCELLED]);
                    throw new RuntimeException('Недостаточно товара на складе: ' . $product->name);
                }

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'price_at_moment' => $item['price'],
                ]);

                $total += $orderItem->price_at_moment * $orderItem->quantity;
            }

            $order->update(['total_amount' => $total]);

            // 2. Пробуем оплатить
            $paymentResult = $this->paymentGateway->process($order);

            if (!$paymentResult['success']) {
                $order->update(['status' => OrderStatus::CANCELLED]);
                throw new RuntimeException($paymentResult['message']);
            }

            // 3. Успех
            // Отмечаем оплату и сохраняем транзакцию
            $order->update([
                'status' => OrderStatus::PROCESSING,
                'transaction_id' => $paymentResult['transaction_id'],
                'paid_at' => Carbon::now(),
                'payment_method' => 'mock',
            ]);

            // Уменьшаем остатки на складе для каждой позиции
            foreach ($order->items as $item) {
                Product::where('id', $item->product_id)->decrement('stock', $item->quantity);
            }

            return $order->fresh(['items', 'user']);
        });
    }
}
