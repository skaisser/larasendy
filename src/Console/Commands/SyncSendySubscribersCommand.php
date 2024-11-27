<?php

declare(strict_types=1);

namespace Skaisser\LaraSendy\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Skaisser\LaraSendy\Traits\SendySubscriber;

class SyncSendySubscribersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendy:sync 
                         {--model= : The model class to sync (default from config)}
                         {--chunk=100 : Number of records to process at once}
                         {--force : Force sync all records regardless of last sync time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize models with Sendy subscribers list';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $modelClass = $this->option('model') ?? config('sendy.default_model');
        $chunkSize = (int) $this->option('chunk');
        $force = $this->option('force');

        if (!class_exists($modelClass)) {
            $this->error("Model class {$modelClass} not found");
            return 1;
        }

        // Check if model uses SendySubscriber trait
        $traits = class_uses_recursive($modelClass);
        if (!isset($traits[SendySubscriber::class])) {
            $this->error("Model {$modelClass} must use SendySubscriber trait");
            return 1;
        }

        $query = $modelClass::query();

        // If not forcing, only sync records that haven't been synced recently
        if (!$force) {
            $syncInterval = config('sendy.sync_interval', 60);
            $query->where(function ($q) use ($modelClass, $syncInterval) {
                $q->whereRaw('1=1')->where(function ($subQ) use ($modelClass, $syncInterval) {
                    $subQ->whereNotExists(function ($exists) use ($modelClass, $syncInterval) {
                        $exists->from('cache')
                            ->whereRaw("cache.key LIKE 'sendy_sync_" . $modelClass::getTable() . "_%'")
                            ->whereRaw("cache.expiration > ?", [now()->subMinutes($syncInterval)->getTimestamp()]);
                    });
                });
            });
        }

        $total = $query->count();
        if ($total === 0) {
            $this->info('No records to sync');
            return 0;
        }

        $this->info("Starting sync for {$total} records from {$modelClass}");
        $bar = $this->output->createProgressBar($total);

        $success = 0;
        $failed = 0;

        $query->chunk($chunkSize, function ($models) use (&$success, &$failed, $bar) {
            foreach ($models as $model) {
                try {
                    if ($model->subscribeToSendy()) {
                        $success++;
                    } else {
                        $failed++;
                        if ($error = $model->getSendyError()) {
                            Log::warning("Failed to sync {$model->sendy_email}: {$error}");
                        }
                    }
                } catch (\Exception $e) {
                    $failed++;
                    Log::error("Error syncing {$model->sendy_email}: {$e->getMessage()}");
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Sync completed: {$success} succeeded, {$failed} failed");

        return $failed === 0 ? 0 : 1;
    }
}
