<?php

namespace Skaisser\LaraSendy\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Skaisser\LaraSendy\Http\Clients\SendyClient;

class SyncSendyCommand extends Command
{
    protected $signature = 'sendy:sync {--force : Force sync all users regardless of sent_to_sendy status}';
    protected $description = 'Sync users to Sendy mailing list';

    protected $sendyClient;

    public function __construct(SendyClient $sendyClient)
    {
        parent::__construct();
        $this->sendyClient = $sendyClient;
    }

    public function handle()
    {
        $this->info('Starting Sendy sync...');

        $query = DB::table(config('sendy.target_table', 'users'));

        if (!$this->option('force')) {
            $query->where('sent_to_sendy', false);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->info('No users found to sync.');
            return 0;
        }

        $failedSync = false;
        $syncedCount = 0;

        foreach ($users as $user) {
            $response = $this->sendyClient->subscribe([
                'name' => $user->name,
                'email' => $user->email,
                'company_name' => $user->company_name
            ]);

            if ($response === true) {
                DB::table(config('sendy.target_table', 'users'))
                    ->where('id', $user->id)
                    ->update(['sent_to_sendy' => true]);
                $syncedCount++;
            } else {
                $failedSync = true;
            }
        }

        if ($failedSync) {
            $this->error('Failed to sync some users to Sendy.');
            return 1;
        }

        $this->info("Successfully synced {$syncedCount} users to Sendy.");
        return 0;
    }
}
