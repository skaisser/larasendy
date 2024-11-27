<?php

declare(strict_types=1);

namespace Skaisser\LaraSendy\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Skaisser\LaraSendy\Http\Clients\SendyClient;
use Skaisser\LaraSendy\Events\SendySubscriberSynced;

class SyncSendyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendy:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync subscribers to Sendy';

    /**
     * The Sendy client instance.
     *
     * @var \Skaisser\LaraSendy\Http\Clients\SendyClient
     */
    protected $client;

    /**
     * Create a new command instance.
     *
     * @param  \Skaisser\LaraSendy\Http\Clients\SendyClient  $client
     * @return void
     */
    public function __construct(SendyClient $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $model = config('sendy.default_model');
        $chunkSize = config('sendy.sync_chunk_size', 100);

        $query = $model::query();

        $total = $query->count();
        $bar = $this->output->createProgressBar($total);

        $query->chunk($chunkSize, function ($subscribers) use ($bar) {
            foreach ($subscribers as $subscriber) {
                $this->syncSubscriber($subscriber);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        return Command::SUCCESS;
    }

    /**
     * Sync a single subscriber to Sendy.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $subscriber
     * @return void
     */
    protected function syncSubscriber($subscriber)
    {
        $mapping = config('sendy.fields_mapping', []);
        $data = [];

        foreach ($mapping as $sendyField => $modelField) {
            $data[$sendyField] = $subscriber->{$modelField};
        }

        try {
            $response = $this->client->subscribe($data);

            if ($response['status']) {
                Cache::put(
                    "sendy_sync_status_{$subscriber->id}",
                    true,
                    now()->addDay()
                );

                event(new SendySubscriberSynced($subscriber, $response));
            } else {
                Cache::put(
                    "sendy_sync_error_{$subscriber->id}",
                    $response['message'] ?? 'Unknown error',
                    now()->addDay()
                );
            }
        } catch (\Exception $e) {
            Cache::put(
                "sendy_sync_error_{$subscriber->id}",
                $e->getMessage(),
                now()->addDay()
            );
        }
    }
}
