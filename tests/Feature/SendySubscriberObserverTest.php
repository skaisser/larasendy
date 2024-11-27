<?php

namespace Skaisser\LaraSendy\Tests\Feature;

use Mockery;
use Skaisser\LaraSendy\Tests\TestCase;
use Skaisser\LaraSendy\Tests\Models\User;
use Skaisser\LaraSendy\Http\Clients\SendyClient;

class SendySubscriberObserverTest extends TestCase
{
    protected $client;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Mockery::mock(SendyClient::class);
        $this->app->instance(SendyClient::class, $this->client);

        $this->user = new User([
            'email' => 'test@example.com',
            'name' => 'Test User',
            'company' => 'Test Company',
            'country' => 'Test Country'
        ]);
    }

    /** @test */
    public function it_subscribes_user_when_created()
    {
        $this->client->shouldReceive('subscribe')
            ->once()
            ->with([
                'email' => 'test@example.com',
                'name' => 'Test User',
                'company' => 'Test Company',
                'country' => 'Test Country'
            ])
            ->andReturn(['status' => true, 'message' => 'Success']);

        $this->user->save();
    }

    /** @test */
    public function it_updates_subscription_when_email_changes()
    {
        $this->client->shouldReceive('subscribe')
            ->once()
            ->with([
                'email' => 'test@example.com',
                'name' => 'Test User',
                'company' => 'Test Company',
                'country' => 'Test Country'
            ])
            ->andReturn(['status' => true, 'message' => 'Success']);

        $this->user->save();

        $this->client->shouldReceive('unsubscribe')
            ->once()
            ->with('test@example.com')
            ->andReturn(['status' => true, 'message' => 'Success']);

        $this->client->shouldReceive('subscribe')
            ->once()
            ->with([
                'email' => 'new@example.com',
                'name' => 'Test User',
                'company' => 'Test Company',
                'country' => 'Test Country'
            ])
            ->andReturn(['status' => true, 'message' => 'Success']);

        $this->user->email = 'new@example.com';
        $this->user->save();
    }

    /** @test */
    public function it_handles_delete_action_properly()
    {
        config()->set('sendy.on_delete_action', 'delete');

        $this->client->shouldReceive('subscribe')
            ->once()
            ->andReturn(['status' => true, 'message' => 'Success']);

        $this->user->save();

        $this->client->shouldReceive('delete')
            ->once()
            ->with('test@example.com')
            ->andReturn(['status' => true, 'message' => 'Success']);

        $this->user->delete();
    }

    /** @test */
    public function it_handles_unsubscribe_action_properly()
    {
        config()->set('sendy.on_delete_action', 'unsubscribe');

        $this->client->shouldReceive('subscribe')
            ->once()
            ->andReturn(['status' => true, 'message' => 'Success']);

        $this->user->save();

        $this->client->shouldReceive('unsubscribe')
            ->once()
            ->with('test@example.com')
            ->andReturn(['status' => true, 'message' => 'Success']);

        $this->user->delete();
    }

    /** @test */
    public function it_does_nothing_when_delete_action_is_none()
    {
        config()->set('sendy.on_delete_action', 'none');

        $this->client->shouldReceive('subscribe')
            ->once()
            ->andReturn(['status' => true, 'message' => 'Success']);

        $this->user->save();

        $this->client->shouldNotReceive('unsubscribe');
        $this->client->shouldNotReceive('delete');

        $this->user->delete();
    }
}
