<?php

declare(strict_types=1);

namespace Skaisser\LaraSendy;

use Illuminate\Support\ServiceProvider;
use Skaisser\LaraSendy\Http\Clients\SendyClient;

class LaraSendyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/sendy.php',
            'sendy'
        );

        $this->app->singleton(SendyClient::class, function ($app) {
            return new SendyClient(
                config('sendy.installation_url'),
                config('sendy.api_key'),
                config('sendy.list_id')
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/sendy.php' => config_path('sendy.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'migrations');

            $this->commands([
                Console\Commands\SyncSendyCommand::class,
            ]);
        }
    }
}
