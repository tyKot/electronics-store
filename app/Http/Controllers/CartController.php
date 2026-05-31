<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function index(): Response
    {
        // Товары приходят с фронта через Inertia POST, когда пользователь оформляет заказ
        return Inertia::render('Cart/Index', [
            // Можно передать серверные настройки (валюта, стоимость доставки и т.д.)
            'settings' => [
                'free_shipping_from' => 50000,
                'delivery_cost' => 500,
                'currency' => 'RUB',
            ],
        ]);
    }
}
