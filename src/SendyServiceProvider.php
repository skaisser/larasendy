<?php

namespace Skaisser\LaraSendy;

use Illuminate\Support\ServiceProvider;
use Skaisser\LaraSendy\Console\Commands\SyncSendy;
use Skaisser\LaraSendy\Http\Clients\SendyClient;

class SendyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/config/sendy.php' => config_path('sendy.php'),
        ], 'config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
        ], 'migrations');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncSendy::class,
            ]);
        }

        // Schedule the sync command if interval is set
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            $interval = config('sendy.sync_interval', 60);
            
            $schedule->command('sendy:sync')
                ->cron("*/{$interval} * * * *")
                ->withoutOverlapping();
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/config/sendy.php',
            'sendy'
        );

        // Register SendyClient as a singleton
        $this->app->singleton(SendyClient::class, function ($app) {
            return new SendyClient();
        });
    }
}
