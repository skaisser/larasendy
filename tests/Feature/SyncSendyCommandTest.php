<?php

namespace Skaisser\LaraSendy\Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\DB;
use Skaisser\LaraSendy\Tests\TestCase;

class SyncSendyCommandTest extends TestCase
{
    protected $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $client = new Client(['handler' => $handlerStack]);
        $this->app->instance(Client::class, $client);
    }

    public function test_it_can_sync_users_to_sendy()
    {
        $this->mockHandler->append(
            new Response(200, [], 'true'),
            new Response(200, [], 'true')
        );

        DB::table('users')->insert([
            ['email' => 'user1@example.com', 'name' => 'User 1', 'company_name' => 'Company 1'],
            ['email' => 'user2@example.com', 'name' => 'User 2', 'company_name' => 'Company 2']
        ]);

        $this->artisan('sendy:sync')
            ->expectsOutput('Starting Sendy sync...')
            ->expectsOutput('Successfully synced 2 users to Sendy.')
            ->assertExitCode(0);

        $this->assertEquals(2, DB::table('users')->where('sent_to_sendy', true)->count());
    }

    public function test_it_only_syncs_unsynced_users()
    {
        $this->mockHandler->append(new Response(200, [], 'true'));

        DB::table('users')->insert([
            ['email' => 'user1@example.com', 'name' => 'User 1', 'company_name' => 'Company 1', 'sent_to_sendy' => true],
            ['email' => 'user2@example.com', 'name' => 'User 2', 'company_name' => 'Company 2', 'sent_to_sendy' => false]
        ]);

        $this->artisan('sendy:sync')
            ->expectsOutput('Starting Sendy sync...')
            ->expectsOutput('Successfully synced 1 users to Sendy.')
            ->assertExitCode(0);

        $this->assertEquals(2, DB::table('users')->where('sent_to_sendy', true)->count());
    }

    public function test_it_handles_failed_subscriptions()
    {
        $this->mockHandler->append(new Response(200, [], 'Invalid API key'));

        DB::table('users')->insert([
            'email' => 'user1@example.com',
            'name' => 'User 1',
            'company_name' => 'Company 1'
        ]);

        $this->artisan('sendy:sync')
            ->expectsOutput('Starting Sendy sync...')
            ->expectsOutput('Failed to sync some users to Sendy.')
            ->assertExitCode(1);

        $this->assertEquals(0, DB::table('users')->where('sent_to_sendy', true)->count());
    }

    public function test_it_can_force_sync_all_users()
    {
        $this->mockHandler->append(
            new Response(200, [], 'true'),
            new Response(200, [], 'true')
        );

        DB::table('users')->insert([
            ['email' => 'user1@example.com', 'name' => 'User 1', 'company_name' => 'Company 1', 'sent_to_sendy' => true],
            ['email' => 'user2@example.com', 'name' => 'User 2', 'company_name' => 'Company 2', 'sent_to_sendy' => true]
        ]);

        $this->artisan('sendy:sync --force')
            ->expectsOutput('Starting Sendy sync...')
            ->expectsOutput('Successfully synced 2 users to Sendy.')
            ->assertExitCode(0);
    }

    public function test_it_handles_empty_users_table()
    {
        $this->artisan('sendy:sync')
            ->expectsOutput('Starting Sendy sync...')
            ->expectsOutput('No users found to sync.')
            ->assertExitCode(0);
    }
}
