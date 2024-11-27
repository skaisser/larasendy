<?php

declare(strict_types=1);

namespace Skaisser\LaraSendy\Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use Skaisser\LaraSendy\Http\Clients\SendyClient;
use Skaisser\LaraSendy\Tests\TestCase;

class SendyClientTest extends TestCase
{
    /** @var SendyClient */
    protected $client;

    /** @var MockHandler */
    protected $mockHandler;

    /** @var string */
    protected $apiKey = 'test-api-key';

    /** @var string */
    protected $listId = 'test-list-id';

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $httpClient = new Client(['handler' => $handlerStack]);

        $this->client = new SendyClient(
            'http://sendy.test',
            $this->apiKey,
            $this->listId,
            $httpClient
        );
    }

    protected function getRequestBody(Request $request): array
    {
        $body = (string) $request->getBody();
        parse_str($body, $data);
        return $data;
    }

    /** @test */
    public function it_validates_constructor_parameters(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required parameter [url] is not set or empty');
        new SendyClient('', 'api-key', 'list-id');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required parameter [apiKey] is not set or empty');
        new SendyClient('http://sendy.test', '', 'list-id');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required parameter [listId] is not set or empty');
        new SendyClient('http://sendy.test', 'api-key', '');
    }

    /** @test */
    public function it_can_set_and_get_list_id(): void
    {
        $newListId = 'new-list-id';
        $this->client->setListId($newListId);
        $this->assertEquals($newListId, $this->client->getListId());
    }

    /** @test */
    public function it_validates_list_id_when_setting(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required parameter [listId] is not set or empty');
        $this->client->setListId('');
    }

    /** @test */
    public function it_can_subscribe_a_user(): void
    {
        $this->mockHandler->append(new Response(200, [], '1'));

        $result = $this->client->subscribe([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $this->assertTrue($result['status']);
        $this->assertEquals('Subscribed', $result['message']);
    }

    /** @test */
    public function it_handles_already_subscribed_user(): void
    {
        $this->mockHandler->append(new Response(200, [], 'Already subscribed.'));

        $result = $this->client->subscribe([
            'email' => 'john@example.com'
        ]);

        $this->assertTrue($result['status']);
        $this->assertEquals('Already subscribed.', $result['message']);
    }

    /** @test */
    public function it_handles_subscription_failure(): void
    {
        $this->mockHandler->append(new Response(200, [], 'Some error occurred'));

        $result = $this->client->subscribe([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $this->assertFalse($result['status']);
        $this->assertEquals('Some error occurred', $result['message']);
    }

    /** @test */
    public function it_requires_email_for_subscription(): void
    {
        $result = $this->client->subscribe([
            'name' => 'John Doe'
        ]);

        $this->assertFalse($result['status']);
        $this->assertEquals('Email is required', $result['message']);
    }

    /** @test */
    public function it_can_get_active_subscriber_count(): void
    {
        $this->mockHandler->append(new Response(200, [], '42'));

        $result = $this->client->getActiveSubscriberCount();

        $this->assertTrue($result['status']);
        $this->assertEquals('42', $result['message']);
    }

    /** @test */
    public function it_can_get_active_subscriber_count_for_specific_list(): void
    {
        $this->mockHandler->append(new Response(200, [], '24'));

        $result = $this->client->getActiveSubscriberCount('other-list-id');

        $this->assertTrue($result['status']);
        $this->assertEquals('24', $result['message']);
    }

    /** @test */
    public function it_handles_invalid_subscriber_count_response(): void
    {
        $this->mockHandler->append(new Response(200, [], 'Invalid response'));

        $result = $this->client->getActiveSubscriberCount();

        $this->assertFalse($result['status']);
        $this->assertEquals('Invalid response', $result['message']);
    }

    /** @test */
    public function it_can_check_subscription_status(): void
    {
        $this->mockHandler->append(new Response(200, [], 'Subscribed'));

        $result = $this->client->isSubscribed('john@example.com');

        $this->assertTrue($result['status']);
        $this->assertEquals('Subscribed', $result['message']);
    }

    /** @test */
    public function it_handles_various_subscription_statuses(): void
    {
        $statuses = ['Unsubscribed', 'Unconfirmed', 'Bounced', 'Soft bounced', 'Complained'];

        foreach ($statuses as $status) {
            $this->mockHandler->append(new Response(200, [], $status));

            $result = $this->client->isSubscribed('john@example.com');

            $this->assertTrue($result['status']);
            $this->assertEquals($status, $result['message']);
        }
    }

    /** @test */
    public function it_handles_invalid_subscription_status(): void
    {
        $this->mockHandler->append(new Response(200, [], 'Invalid status'));

        $result = $this->client->isSubscribed('john@example.com');

        $this->assertFalse($result['status']);
        $this->assertEquals('Invalid status', $result['message']);
    }

    /** @test */
    public function it_can_unsubscribe_a_user(): void
    {
        $this->mockHandler->append(new Response(200, [], '1'));

        $result = $this->client->unsubscribe('john@example.com');

        $this->assertTrue($result['status']);
        $this->assertEquals('Unsubscribed', $result['message']);
    }

    /** @test */
    public function it_handles_unsubscribe_failure(): void
    {
        $this->mockHandler->append(new Response(200, [], 'Some error occurred'));

        $result = $this->client->unsubscribe('john@example.com');

        $this->assertFalse($result['status']);
        $this->assertEquals('Some error occurred', $result['message']);
    }

    /** @test */
    public function it_can_delete_a_subscriber()
    {
        $email = 'test@example.com';

        $this->mockHandler->append(
            new Response(200, [], 'true')
        );

        $result = $this->client->delete($email);

        $this->assertTrue($result['status']);
        $this->assertEquals('Successfully deleted', $result['message']);

        $request = $this->mockHandler->getLastRequest();
        $this->assertEquals('POST', $request->getMethod());
        $this->assertStringEndsWith('/api/subscribers/delete.php', $request->getUri()->getPath());

        $body = $this->getRequestBody($request);
        $this->assertEquals($this->apiKey, $body['api_key']);
        $this->assertEquals($this->listId, $body['list_id']);
        $this->assertEquals($email, $body['email']);
    }

    /** @test */
    public function it_handles_delete_errors_properly()
    {
        $email = 'test@example.com';

        $this->mockHandler->append(
            new Response(200, [], 'Subscriber does not exist')
        );

        $result = $this->client->delete($email);

        $this->assertFalse($result['status']);
        $this->assertEquals('Subscriber not found', $result['message']);
    }
}
