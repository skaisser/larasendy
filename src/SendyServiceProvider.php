<?php

namespace Skaisser\LaraSendy;

use Illuminate\Support\ServiceProvider;
use Skaisser\LaraSendy\Console\Commands\SyncSendySubscribersCommand;
use Skaisser\LaraSendy\Http\Clients\SendyClient;

class SendyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/sendy.php', 'sendy'
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
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncSendySubscribersCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/sendy.php' => config_path('sendy.php'),
            ], 'config');
        }

        $this->setupScheduler();
    }

    /**
     * Setup the scheduler.
     */
    protected function setupScheduler(): void
    {
        if ($this->app->runningInConsole() && config('sendy.schedule_enabled', false)) {
            $this->app->booted(function () {
                $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
                $schedule->command('sendy:sync')
                    ->withoutOverlapping()
                    ->runInBackground()
                    ->cron(config('sendy.schedule_cron', '0 * * * *'));
            });
        }
    }
}
