<?php

namespace Skaisser\LaraSendy\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Skaisser\LaraSendy\Tests\TestCase;
use Skaisser\LaraSendy\Tests\Models\User;
use Skaisser\LaraSendy\Http\Clients\SendyClient;
use Skaisser\LaraSendy\Events\SendySubscriberSynced;
use Mockery;

class SyncSendyCommandTest extends TestCase
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
    public function it_uses_custom_field_mapping()
    {
        Event::fake([SendySubscriberSynced::class]);

        $user = User::create([
            'email' => 'test@example.com',
            'name' => 'Test User',
            'company' => 'Test Company',
            'country' => 'Test Country'
        ]);

        $this->sendyClient
            ->shouldReceive('subscribe')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['email'] === 'test@example.com' &&
                    $data['name'] === 'Test User' &&
                    $data['company'] === 'Test Company' &&
                    $data['country'] === 'Test Country';
            }))
            ->andReturn(['status' => true]);

        $this->artisan('sendy:sync')
            ->assertSuccessful();

        $this->assertTrue(Cache::has("sendy_sync_status_{$user->id}"));
        Event::assertDispatched(SendySubscriberSynced::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
