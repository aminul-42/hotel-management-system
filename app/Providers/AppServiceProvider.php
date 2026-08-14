<?php

namespace App\Providers;
use App\Contracts\PaymentGatewayInterface;
use App\Services\Payment\FakeSslcommerzGateway;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, function () {
        return match (config('payment.driver')) {
            // 'sslcommerz' => new \App\Services\Payment\SslcommerzGateway(), // add later
            default => new FakeSslcommerzGateway(),
        };
    });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
