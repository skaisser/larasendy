<?php

declare(strict_types=1);

namespace Skaisser\LaraSendy\Http\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class SendyClient
{
    /** @var Client */
    protected $client;
    
    /** @var string */
    protected $url;
    
    /** @var string */
    protected $apiKey;
    
    /** @var string */
    protected $listId;

    /**
     * Create a new SendyClient instance.
     * 
     * @param string $url
     * @param string $apiKey
     * @param string $listId
     * @param Client|null $client
     * @throws \Exception
     */
    public function __construct(string $url, string $apiKey, string $listId, ?Client $client = null)
    {
        if (empty($url)) {
            throw new \Exception("Required parameter [url] is not set or empty");
        }
        
        if (empty($apiKey)) {
            throw new \Exception("Required parameter [apiKey] is not set or empty");
        }
        
        if (empty($listId)) {
            throw new \Exception("Required parameter [listId] is not set or empty");
        }

        $this->url = rtrim($url, '/');
        $this->apiKey = $apiKey;
        $this->listId = $listId;
        $this->client = $client ?? new Client();
    }

    /**
     * Set a new list ID
     * 
     * @param string $listId
     * @throws \Exception
     */
    public function setListId(string $listId): void
    {
        if (empty($listId)) {
            throw new \Exception("Required parameter [listId] is not set or empty");
        }

        $this->listId = $listId;
    }

    /**
     * Get the current list ID
     * 
     * @return string
     */
    public function getListId(): string
    {
        return $this->listId;
    }

    /**
     * Subscribe a user to the Sendy list.
     *
     * @param array $data User data including required email field
     * @return array
     */
    public function subscribe(array $data): array
    {
        $email = $data['email'] ?? null;
        if (empty($email)) {
            Log::error('Email is required for Sendy subscription');
            return [
                'status' => false,
                'message' => 'Email is required'
            ];
        }

        $payload = array_merge($data, [
            'api_key' => $this->apiKey,
            'list' => $this->listId,
            'boolean' => 'true'
        ]);

        try {
            $response = $this->client->post($this->url . '/subscribe', [
                'form_params' => $payload
            ]);

            $result = (string) $response->getBody();

            switch ($result) {
                case '1':
                    Log::info("Successfully subscribed {$email} to Sendy list");
                    return [
                        'status' => true,
                        'message' => 'Subscribed'
                    ];

                case 'Already subscribed.':
                    return [
                        'status' => true,
                        'message' => 'Already subscribed.'
                    ];

                default:
                    Log::warning("Failed to subscribe {$email} to Sendy list. Response: {$result}");
                    return [
                        'status' => false,
                        'message' => $result
                    ];
            }
        } catch (GuzzleException $e) {
            Log::error("Error subscribing {$email} to Sendy list: " . $e->getMessage());
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get the number of active subscribers in the list
     *
     * @param string|null $listId Optional specific list ID to check
     * @return array
     */
    public function getActiveSubscriberCount(?string $listId = null): array
    {
        $targetList = $listId ?? $this->listId;
        
        if (empty($targetList)) {
            return [
                'status' => false,
                'message' => 'List ID is required'
            ];
        }

        try {
            $response = $this->client->post($this->url . '/api/subscribers/active-subscriber-count.php', [
                'form_params' => [
                    'api_key' => $this->apiKey,
                    'list_id' => $targetList
                ]
            ]);

            $result = (string) $response->getBody();

            if (is_numeric($result)) {
                return [
                    'status' => true,
                    'message' => $result
                ];
            }

            Log::warning("Invalid response from Sendy subscriber count. Response: {$result}");
            return [
                'status' => false,
                'message' => $result
            ];
        } catch (GuzzleException $e) {
            Log::error("Error getting Sendy subscriber count: " . $e->getMessage());
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if an email is subscribed to the list
     *
     * @param string $email
     * @return array
     */
    public function isSubscribed(string $email): array
    {
        try {
            $response = $this->client->post($this->url . '/api/subscribers/subscription-status.php', [
                'form_params' => [
                    'api_key' => $this->apiKey,
                    'email' => $email,
                    'list_id' => $this->listId
                ]
            ]);

            $result = (string) $response->getBody();

            switch ($result) {
                case 'Subscribed':
                case 'Unsubscribed':
                case 'Unconfirmed':
                case 'Bounced':
                case 'Soft bounced':
                case 'Complained':
                    return [
                        'status' => true,
                        'message' => $result
                    ];

                default:
                    Log::warning("Invalid subscription status response for {$email}. Response: {$result}");
                    return [
                        'status' => false,
                        'message' => $result
                    ];
            }
        } catch (GuzzleException $e) {
            Log::error("Error checking subscription status for {$email}: " . $e->getMessage());
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Unsubscribe an email from the list
     *
     * @param string $email
     * @return array
     */
    public function unsubscribe(string $email): array
    {
        try {
            $response = $this->client->post($this->url . '/api/subscribers/unsubscribe.php', [
                'form_params' => [
                    'api_key' => $this->apiKey,
                    'email' => $email,
                    'list_id' => $this->listId,
                    'boolean' => 'true'
                ]
            ]);

            $result = (string) $response->getBody();

            switch ($result) {
                case '1':
                    Log::info("Successfully unsubscribed {$email} from Sendy list");
                    return [
                        'status' => true,
                        'message' => 'Unsubscribed'
                    ];

                default:
                    Log::warning("Failed to unsubscribe {$email} from Sendy list. Response: {$result}");
                    return [
                        'status' => false,
                        'message' => $result
                    ];
            }
        } catch (GuzzleException $e) {
            Log::error("Error unsubscribing {$email} from Sendy list: " . $e->getMessage());
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete a subscriber from the list
     *
     * @param string $email
     * @return array{status: bool, message: string}
     */
    public function delete(string $email): array
    {
        try {
            $response = $this->client->post($this->url . '/api/subscribers/delete.php', [
                'form_params' => [
                    'api_key' => $this->apiKey,
                    'list_id' => $this->listId,
                    'email' => $email
                ]
            ]);

            $result = trim($response->getBody()->getContents());

            if ($result === 'true') {
                return ['status' => true, 'message' => 'Successfully deleted'];
            }

            // Handle known error responses
            $errorMessages = [
                'No data passed' => 'No data provided',
                'API key not passed' => 'API key is required',
                'Invalid API key' => 'Invalid API key',
                'List ID not passed' => 'List ID is required',
                'List does not exist' => 'List does not exist',
                'Email address not passed' => 'Email address is required',
                'Subscriber does not exist' => 'Subscriber not found'
            ];

            $message = $errorMessages[$result] ?? $result;
            return ['status' => false, 'message' => $message];

        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
