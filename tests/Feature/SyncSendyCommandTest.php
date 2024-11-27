<?php

namespace Skaisser\LaraSendy\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Mockery;
use Skaisser\LaraSendy\Http\Clients\SendyClient;
use Skaisser\LaraSendy\Tests\TestCase;

class SyncSendyCommandTest extends TestCase
{
    protected $mockSendyClient;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the SendyClient
        $this->mockSendyClient = Mockery::mock(SendyClient::class);
        $this->app->instance(SendyClient::class, $this->mockSendyClient);
    }

    /** @test */
    public function it_can_sync_users_to_sendy()
    {
        // Create test users
        DB::table('users')->insert([
            ['email' => 'user1@example.com', 'name' => 'User 1', 'company_name' => 'Company 1'],
            ['email' => 'user2@example.com', 'name' => 'User 2', 'company_name' => 'Company 2'],
        ]);

        // Set up expectations for the mock
        $this->mockSendyClient->shouldReceive('subscribe')
            ->twice()
            ->andReturn(['success' => true, 'message' => 'Subscribed successfully']);

        // Run the command
        $this->artisan('sendy:sync')
            ->expectsOutput('Found 2 users to sync with Sendy.')
            ->assertExitCode(0);

        // Check that users were marked as synced
        $this->assertEquals(2, DB::table('users')->where('sent_to_sendy', true)->count());
    }

    /** @test */
    public function it_only_syncs_unsynced_users()
    {
        // Create test users
        DB::table('users')->insert([
            [
                'email' => 'user1@example.com',
                'name' => 'User 1',
                'company_name' => 'Company 1',
                'sent_to_sendy' => true
            ],
            [
                'email' => 'user2@example.com',
                'name' => 'User 2',
                'company_name' => 'Company 2',
                'sent_to_sendy' => false
            ],
        ]);

        // Set up expectations for the mock
        $this->mockSendyClient->shouldReceive('subscribe')
            ->once()
            ->andReturn(['success' => true, 'message' => 'Subscribed successfully']);

        // Run the command
        $this->artisan('sendy:sync')
            ->expectsOutput('Found 1 users to sync with Sendy.')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_handles_failed_subscriptions()
    {
        // Create test user
        DB::table('users')->insert([
            'email' => 'user1@example.com',
            'name' => 'User 1',
            'company_name' => 'Company 1'
        ]);

        // Set up expectations for the mock
        $this->mockSendyClient->shouldReceive('subscribe')
            ->once()
            ->andReturn(['success' => false, 'message' => 'API Error']);

        // Run the command
        $this->artisan('sendy:sync')
            ->expectsOutput('Found 1 users to sync with Sendy.')
            ->assertExitCode(0);

        // Check that user was not marked as synced
        $this->assertEquals(0, DB::table('users')->where('sent_to_sendy', true)->count());
    }

    /** @test */
    public function it_can_force_sync_all_users()
    {
        // Create test users
        DB::table('users')->insert([
            [
                'email' => 'user1@example.com',
                'name' => 'User 1',
                'company_name' => 'Company 1',
                'sent_to_sendy' => true
            ],
            [
                'email' => 'user2@example.com',
                'name' => 'User 2',
                'company_name' => 'Company 2',
                'sent_to_sendy' => true
            ],
        ]);

        // Set up expectations for the mock
        $this->mockSendyClient->shouldReceive('subscribe')
            ->twice()
            ->andReturn(['success' => true, 'message' => 'Subscribed successfully']);

        // Run the command with --force option
        $this->artisan('sendy:sync --force')
            ->expectsOutput('Found 2 users to sync with Sendy.')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_handles_empty_users_table()
    {
        $this->artisan('sendy:sync')
            ->expectsOutput('No users to sync.')
            ->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
