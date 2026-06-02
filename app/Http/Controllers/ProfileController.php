<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Models\Favorite;
use App\Models\Favorites;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Заказы с пагинацией
        $orders = Order::with('items')
            ->where('user_id', $user->id)
            ->withCount('items')
            ->latest()
            ->paginate(5);

        // Избранные товары (если храните в БД)
        // Если используете только Pinia/localStorage, передайте пустой массив
        $favoriteIds = Favorite::where('user_id', $user->id)->pluck('product_id'); // Или получите из БД: Favorite::where('user_id', $user->id)->pluck('product_id')
        $favorites = Product::whereIn('id', $favoriteIds)
            ->when($favoriteIds, fn($q) => $q->get(), fn($q) => collect())
            ->values();

        // Статистика пользователя
        $stats = [
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'completed_orders' => Order::where('user_id', $user->id)->where('status', 'completed')->count(),
            'total_spent' => (float) Order::where('user_id', $user->id)->where('status', 'completed')->sum('total_amount'),
            'member_since' => $user->created_at->format('d.m.Y'),
        ];

        return Inertia::render('Profile/Index', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
            ],
            'orders' => OrderResource::collection($orders),
            'favorites' => ProductResource::collection($favorites),
            'stats' => $stats,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
            'phone' => ['nullable', 'string', 'regex:/^\+7\d{10}$/'],
        ]);

        $request->user()->update($validated);

        return back()->with('success', 'Данные обновлены');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', 'Пароль изменён');
    }
}
