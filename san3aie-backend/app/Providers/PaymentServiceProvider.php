<?php

namespace App\Providers;

use App\Services\Payments\MockPaymentGateway;
use App\Services\Payments\PaymentGatewayInterface;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PaymentGatewayInterface::class,
            MockPaymentGateway::class
        );
    }

    public function boot(): void
    {
    }
}
