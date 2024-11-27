<?php

namespace Skaisser\LaraSendy\Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Skaisser\LaraSendy\Http\Clients\SendyClient;
use Skaisser\LaraSendy\Tests\TestCase;

class SendyClientTest extends TestCase
{
    protected $client;
    protected $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $httpClient = new Client(['handler' => $handlerStack]);

        $this->client = new SendyClient($httpClient);
    }

    public function test_it_can_subscribe_a_user_successfully()
    {
        $this->mockHandler->append(new Response(200, [], 'true'));

        $result = $this->client->subscribe([
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);

        $this->assertTrue($result);
    }

    public function test_it_can_handle_custom_fields()
    {
        $this->mockHandler->append(new Response(200, [], 'true'));

        $result = $this->client->subscribe([
            'email' => 'test@example.com',
            'CustomField' => 'Custom Value'
        ]);

        $this->assertTrue($result);
    }

    public function test_it_handles_already_subscribed_response()
    {
        $this->mockHandler->append(new Response(200, [], 'Already subscribed'));

        $result = $this->client->subscribe([
            'email' => 'test@example.com'
        ]);

        $this->assertEquals('Already subscribed', $result);
    }

    public function test_it_handles_invalid_api_key_response()
    {
        $this->mockHandler->append(new Response(200, [], 'Invalid API key'));

        $result = $this->client->subscribe([
            'email' => 'test@example.com'
        ]);

        $this->assertEquals('Invalid API key', $result);
    }

    public function test_it_includes_standard_fields_when_provided()
    {
        $this->mockHandler->append(new Response(200, [], 'true'));

        $data = [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'company' => 'Test Company',
            'country' => 'US'
        ];

        $result = $this->client->subscribe($data);

        $this->assertTrue($result);
    }

    public function test_it_includes_global_configuration_parameters()
    {
        $this->mockHandler->append(new Response(200, [], 'true'));

        config([
            'sendy.gdpr' => true,
            'sendy.silent' => true,
            'sendy.referrer' => 'http://test.com',
            'sendy.honeypot' => true
        ]);

        $result = $this->client->subscribe([
            'email' => 'test@example.com'
        ]);

        $this->assertTrue($result);
    }

    public function test_it_handles_gdpr_error_response()
    {
        $this->mockHandler->append(new Response(200, [], 'Invalid GDPR value'));

        config(['sendy.gdpr' => 'invalid']);

        $result = $this->client->subscribe([
            'email' => 'test@example.com'
        ]);

        $this->assertEquals('Invalid GDPR value', $result);
    }

    public function test_it_handles_disabled_api_response()
    {
        $this->mockHandler->append(new Response(200, [], 'API subscription is disabled'));

        $result = $this->client->subscribe([
            'email' => 'test@example.com'
        ]);

        $this->assertEquals('API subscription is disabled', $result);
    }
}
