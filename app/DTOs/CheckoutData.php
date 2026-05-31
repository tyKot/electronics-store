<?php
namespace App\DTOs;

readonly class CheckoutData
{
    public function __construct(
        public int $userId,
        public array $cartItems, // [['product_id' => 1, 'quantity' => 2, 'price' => 59990]]
        public string $firstName,
        public string $lastName,
        public string $address,
        public string $paymentMethod,
        public ?string $phone = null,
    ) {
    }

    // Удобный фабричный метод из валидированных данных
    public static function fromValidated(array $data, int $userId): self
    {
        return new self(
            userId: $userId,
            cartItems: $data['cart_items'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            address: $data['address'],
            paymentMethod: $data['payment_method'] ?? 'mock',
            phone: $data['phone'] ?? '',
        );
    }
}
