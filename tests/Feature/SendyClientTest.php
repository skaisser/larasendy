<?php

namespace Skaisser\LaraSendy\Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;
use Skaisser\LaraSendy\Http\Clients\SendyClient;
use Skaisser\LaraSendy\Tests\TestCase;

class SendyClientTest extends TestCase
{
    protected $container = [];
    protected $mockHandler;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        
        // Add history middleware
        $history = Middleware::history($this->container);
        $handlerStack->push($history);

        $this->client = new Client(['handler' => $handlerStack]);
        $this->app->instance(Client::class, $this->client);
    }

    /** @test */
    public function it_can_subscribe_a_user_successfully()
    {
        $this->mockHandler->append(new Response(200, [], '1'));

        $sendyClient = $this->app->make(SendyClient::class);
        $result = $sendyClient->subscribe([
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('Subscribed successfully', $result['message']);

        // Check the request
        $request = $this->container[0]['request'];
        $formParams = [];
        parse_str((string) $request->getBody(), $formParams);

        $this->assertEquals('test@example.com', $formParams['email']);
        $this->assertEquals('Test User', $formParams['name']);
        $this->assertEquals('test-api-key', $formParams['api_key']);
        $this->assertEquals('test-list-id', $formParams['list']);
        $this->assertEquals('true', $formParams['boolean']);
    }

    /** @test */
    public function it_can_handle_custom_fields()
    {
        $this->mockHandler->append(new Response(200, [], '1'));

        $sendyClient = $this->app->make(SendyClient::class);
        $result = $sendyClient->subscribe([
            'email' => 'test@example.com',
            'name' => 'Test User',
            'company' => 'Test Company',
            'phone' => '1234567890'
        ]);

        $this->assertTrue($result['success']);

        // Check the request
        $request = $this->container[0]['request'];
        $formParams = [];
        parse_str((string) $request->getBody(), $formParams);

        $this->assertEquals('Test Company', $formParams['custom[company]']);
        $this->assertEquals('1234567890', $formParams['custom[phone]']);
    }

    /** @test */
    public function it_handles_already_subscribed_response()
    {
        $this->mockHandler->append(new Response(200, [], 'Already subscribed.'));

        $sendyClient = $this->app->make(SendyClient::class);
        $result = $sendyClient->subscribe([
            'email' => 'test@example.com'
        ]);

        $this->assertFalse($result['success']);
        $this->assertEquals('Already subscribed', $result['message']);
    }

    /** @test */
    public function it_handles_invalid_api_key_response()
    {
        $this->mockHandler->append(new Response(200, [], 'Invalid API key'));

        $sendyClient = $this->app->make(SendyClient::class);
        $result = $sendyClient->subscribe([
            'email' => 'test@example.com'
        ]);

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid API key', $result['message']);
    }

    /** @test */
    public function it_handles_api_errors()
    {
        $this->mockHandler->append(new Response(500, [], 'Server Error'));

        $sendyClient = $this->app->make(SendyClient::class);
        $result = $sendyClient->subscribe([
            'email' => 'test@example.com'
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Failed to connect to Sendy API', $result['message']);
    }

    /** @test */
    public function it_includes_standard_fields_when_provided()
    {
        $this->mockHandler->append(new Response(200, [], '1'));

        $sendyClient = $this->app->make(SendyClient::class);
        $result = $sendyClient->subscribe([
            'email' => 'test@example.com',
            'name' => 'Test User',
            'country' => 'US',
            'ipaddress' => '127.0.0.1'
        ]);

        $this->assertTrue($result['success']);

        // Check the request
        $request = $this->container[0]['request'];
        $formParams = [];
        parse_str((string) $request->getBody(), $formParams);

        $this->assertEquals('test@example.com', $formParams['email']);
        $this->assertEquals('Test User', $formParams['name']);
        $this->assertEquals('US', $formParams['country']);
        $this->assertEquals('127.0.0.1', $formParams['ipaddress']);
    }

    /** @test */
    public function it_includes_global_configuration_parameters()
    {
        config(['sendy.gdpr' => true]);
        config(['sendy.silent' => true]);
        config(['sendy.honeypot' => true]);
        config(['sendy.referrer' => 'https://example.com']);

        $this->mockHandler->append(new Response(200, [], '1'));

        $sendyClient = $this->app->make(SendyClient::class);
        $result = $sendyClient->subscribe([
            'email' => 'test@example.com'
        ]);

        // Check the request
        $request = $this->container[0]['request'];
        $formParams = [];
        parse_str((string) $request->getBody(), $formParams);

        $this->assertEquals('true', $formParams['gdpr']);
        $this->assertEquals('true', $formParams['silent']);
        $this->assertEquals('', $formParams['hp']);
        $this->assertEquals('https://example.com', $formParams['referrer']);
    }

    /** @test */
    public function it_throws_exception_when_email_is_missing()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email is required for Sendy subscription');

        $sendyClient = $this->app->make(SendyClient::class);
        $sendyClient->subscribe([
            'name' => 'Test User'
        ]);
    }

    /** @test */
    public function it_handles_gdpr_error_response()
    {
        $this->mockHandler->append(new Response(200, [], 'Invalid GDPR value.'));

        $sendyClient = $this->app->make(SendyClient::class);
        $result = $sendyClient->subscribe([
            'email' => 'test@example.com'
        ]);

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid GDPR value', $result['message']);
    }

    /** @test */
    public function it_handles_disabled_api_response()
    {
        $this->mockHandler->append(new Response(200, [], 'API subscription is disabled.'));

        $sendyClient = $this->app->make(SendyClient::class);
        $result = $sendyClient->subscribe([
            'email' => 'test@example.com'
        ]);

        $this->assertFalse($result['success']);
        $this->assertEquals('API subscription is disabled', $result['message']);
    }
}
