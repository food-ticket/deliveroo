<?php

namespace Foodticket\Deliveroo;

use Foodticket\Deliveroo\Controllers\WebhookController;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;

class DeliverooServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishConfig();
        $this->registerMacros();
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        if ($this->app instanceof Application && $this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/deliveroo.php' => config_path('deliveroo.php'),
            ], 'deliveroo-config');
        }
    }

    protected function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/deliveroo.php' => config_path('deliveroo.php'),
            ], 'deliveroo-config');
        }

        $this->mergeConfigFrom(__DIR__.'/../config/deliveroo.php', 'deliveroo');
    }

    protected function registerMacros(): void
    {
        Route::macro('deliverooWebhooks', function (string $uri = 'deliveroo/webhooks') {
            return $this->post($uri, [WebhookController::class, 'handle'])
                ->name('deliveroo.webhooks');
        });
    }
}
