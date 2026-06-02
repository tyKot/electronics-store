<?php // app/Http/Controllers/CheckoutController.php
namespace App\Http\Controllers;

use App\Actions\CreateOrderAction;
use App\DTOs\CheckoutData;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Checkout/Index', [
            'cart' => session('cart', []),
        ]);
    }

    public function store(Request $request, CreateOrderAction $createOrder)
    {

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'phone' => ['required', 'string', 'max:12'],
            'cart_items' => ['required', 'array', 'min:1'],
            'cart_items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'cart_items.*.quantity' => ['required', 'integer', 'min:1'],
            'cart_items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        $checkoutData = CheckoutData::fromValidated($validated, $request->user()->id);

        try {
            $order = $createOrder->execute($checkoutData);
            session()->forget('cart');

            return Redirect::route('home')->with('success', 'Заказ успешно создан! Номер заказа: ' . $order->order_number);
        } catch (\RuntimeException $e) {
            return Redirect::back()->withInput()->withErrors([
                'payment' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            report($e);
            return Redirect::back()->withInput()->withErrors([
                'server' => 'Произошла ошибка при обработке заказа. Пожалуйста, попробуйте позже.',
            ]);
        }
    }
}
