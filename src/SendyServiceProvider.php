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

            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'migrations');
        }

        $this->setupScheduler();
    }

    /**
     * Set up the command scheduler
     */
    protected function setupScheduler(): void
    {
        $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
        $frequency = config('sendy.sync_schedule', 'hourly');

        $command = $schedule->command('sendy:sync');

        switch ($frequency) {
            case 'hourly':
                $command->hourly();
                break;
            case 'daily':
                $command->daily();
                break;
            case 'custom':
                // Custom schedule can be defined in the app's scheduler
                break;
            default:
                $command->hourly();
        }
    }
}
