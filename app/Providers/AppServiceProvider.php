<?php

namespace App\Providers;

use App\Interfaces\PaymentGatewayInterface;
use App\Services\MockPaymentService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, MockPaymentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        // RateLimiter::for('bot-api', function (Request $request) {
        //     return Limit::perMinute(60)->by($request->ip());
        // });
        Vite::prefetch(concurrency: 3);
    }
}
