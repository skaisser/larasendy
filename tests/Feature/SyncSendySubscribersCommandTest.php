<?php

namespace Skaisser\LaraSendy\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Skaisser\LaraSendy\Tests\TestCase;
use Skaisser\LaraSendy\Tests\Models\User;
use Skaisser\LaraSendy\Http\Clients\SendyClient;
use Skaisser\LaraSendy\Events\SendySubscriberSynced;
use Mockery;

class SyncSendySubscribersCommandTest extends TestCase
{
    protected $sendyClient;

    protected function setUp(): void
    {
        parent::setUp();

        // Prevent observers from firing during test setup
        User::unsetEventDispatcher();

        // Mock the Sendy client
        $this->sendyClient = Mockery::mock(SendyClient::class);
        $this->app->instance(SendyClient::class, $this->sendyClient);

        // Clear the cache before each test
        Cache::flush();
    }

    /** @test */
    public function it_syncs_subscribers_to_sendy()
    {
        Event::fake([SendySubscriberSynced::class]);

        $users = User::factory()->count(3)->create();

        $this->sendyClient
            ->shouldReceive('subscribe')
            ->times(3)
            ->andReturn(['status' => true]);

        $this->artisan('sendy:sync')
            ->assertSuccessful();

        foreach ($users as $user) {
            $this->assertTrue(Cache::has("sendy_sync_status_{$user->id}"));
        }

        Event::assertDispatched(SendySubscriberSynced::class, 3);
    }

    /** @test */
    public function it_handles_api_errors_during_sync()
    {
        Event::fake([SendySubscriberSynced::class]);

        $users = User::factory()->count(2)->create();

        $this->sendyClient
            ->shouldReceive('subscribe')
            ->times(2)
            ->andReturn(
                ['status' => false, 'message' => 'API Error'],
                ['status' => true]
            );

        $this->artisan('sendy:sync')
            ->assertSuccessful();

        $this->assertTrue(Cache::has("sendy_sync_error_{$users[0]->id}"));
        $this->assertTrue(Cache::has("sendy_sync_status_{$users[1]->id}"));

        Event::assertDispatched(SendySubscriberSynced::class, 1);
    }

    /** @test */
    public function it_respects_chunk_size_configuration()
    {
        Event::fake([SendySubscriberSynced::class]);

        config(['sendy.sync_chunk_size' => 2]);

        $users = User::factory()->count(5)->create();

        $this->sendyClient
            ->shouldReceive('subscribe')
            ->times(5)
            ->andReturn(['status' => true]);

        $this->artisan('sendy:sync')
            ->assertSuccessful();

        foreach ($users as $user) {
            $this->assertTrue(Cache::has("sendy_sync_status_{$user->id}"));
        }

        Event::assertDispatched(SendySubscriberSynced::class, 5);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
