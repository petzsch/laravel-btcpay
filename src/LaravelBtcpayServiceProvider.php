<?php

namespace Petzsch\LaravelBtcpay;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Petzsch\LaravelBtcpay\Http\Controllers\WebhookController;


class LaravelBtcpayServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laravel-btcpay.php' => config_path('laravel-btcpay.php'),
            ], 'config');
        }

        $this->registerRoutes();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-btcpay.php', 'laravel-btcpay');
    }

    protected function registerRoutes()
    {
        Route::macro('btcPayWebhook',
            function (string $uri = 'laravel-btcpay/webhook') {
                Route::post($uri, [WebhookController::class, 'handleWebhook'])
                    ->name('laravel-btcpay.webhook.capture');
            });
    }
}
