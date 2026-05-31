<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        // Статистика по заказам за последние 7 дней (группировка средствами PostgreSQL)
        $salesData = Order::where('created_at', '>=', now()->subDays(7))
            ->select(
                DB::raw("DATE(created_at) as date"),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Формируем данные для Chart.js
        $chartData = [
            'labels' => $salesData->map(fn ($d) => \Carbon\Carbon::parse($d->date)->format('d.m'))->toArray(),
            'datasets' => [
                [
                    'label' => 'Продажи (руб.)',
                    'data' => $salesData->pluck('revenue')->toArray(),
                ],
            ],
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'orders_today' => Order::whereDate('created_at', today())->count(),
                'revenue_week' => (float) $salesData->sum('revenue'),
                'new_users_week' => User::where('created_at', '>=', now()->subDays(7))->count(),
                'total_orders' => Order::count(),
            ],
            'chartData' => $chartData,
            'recentOrders' => Order::with('user')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($o) => [
                    'id' => $o->id,
                    'user' => $o->user->name,
                    'total' => (float) $o->total_amount,
                    'status' => [
                        'value' => $o->status->value,
                        'label' => $o->status->label(),
                    ],
                    'created_at' => $o->created_at->diffForHumans(),
                ]),
        ]);
    }
}
