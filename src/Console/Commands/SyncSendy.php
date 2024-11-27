<?php

namespace Skaisser\LaraSendy\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Skaisser\LaraSendy\Http\Clients\SendyClient;

class SyncSendy extends Command
{
    protected $signature = 'sendy:sync {--force : Force sync all users regardless of sent_to_sendy status}';
    protected $description = 'Sync users to Sendy mailing list';

    protected $sendyClient;
    protected $config;

    public function __construct(SendyClient $sendyClient)
    {
        parent::__construct();
        $this->sendyClient = $sendyClient;
        $this->config = config('sendy');
    }

    public function handle()
    {
        $table = $this->config['target_table'];
        $query = DB::table($table);

        if (!$this->option('force')) {
            $query->where('sent_to_sendy', false);
        }

        $total = $query->count();
        
        if ($total === 0) {
            $this->info('No users to sync.');
            return 0;
        }

        $this->info("Found {$total} users to sync with Sendy.");
        $bar = $this->output->createProgressBar($total);

        $query->chunk(100, function ($users) use ($bar) {
            foreach ($users as $user) {
                $this->syncUser($user);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('Sync completed!');

        return 0;
    }

    protected function syncUser($user)
    {
        $fieldsMapping = $this->config['fields_mapping'];
        $userData = [];

        foreach ($fieldsMapping as $sendyField => $userField) {
            $userData[$sendyField] = $user->$userField ?? null;
        }

        $result = $this->sendyClient->subscribe($userData);

        if ($result['success']) {
            DB::table($this->config['target_table'])
                ->where('id', $user->id)
                ->update(['sent_to_sendy' => true]);
            
            Log::info('User synced to Sendy', [
                'email' => $userData['email'],
                'message' => $result['message']
            ]);
        } else {
            Log::error('Failed to sync user to Sendy', [
                'email' => $userData['email'],
                'message' => $result['message']
            ]);
        }
    }
}
