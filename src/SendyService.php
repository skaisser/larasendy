<?php

namespace Kpg\LaraSendy;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendyService
{
    /** @var string */
    protected $url;
    
    /** @var string */
    protected $apiKey;
    
    /** @var string */
    protected $listId;

    public function __construct()
    {
        $this->url = config('sendy.url');
        $this->apiKey = config('sendy.api_key');
        $this->listId = config('sendy.list_id');
    }

    /**
     * Subscribe a user to the list
     *
     * @param string $email
     * @param string|null $name
     * @return bool
     */
    public function subscribe(string $email, ?string $name = null): bool
    {
        $data = [
            'api_key' => $this->apiKey,
            'email' => $email,
            'list' => $this->listId,
            'boolean' => 'true'
        ];

        if ($name) {
            $data['name'] = $name;
        }

        try {
            $response = Http::asForm()
                ->post($this->url . '/subscribe', $data);

            $result = $response->body();

            // Sendy returns '1' for success
            if ($result === '1') {
                Log::info("Successfully subscribed {$email} to Sendy list");
                return true;
            }

            Log::warning("Failed to subscribe {$email} to Sendy list. Response: {$result}");
            return false;
        } catch (\Exception $e) {
            Log::error("Error subscribing {$email} to Sendy list: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the number of active subscribers in the list
     *
     * @return int|null Returns null if the request fails
     */
    public function getActiveSubscriberCount(): ?int
    {
        try {
            $response = Http::asForm()
                ->post($this->url . '/api/subscribers/active-subscriber-count.php', [
                    'api_key' => $this->apiKey,
                    'list_id' => $this->listId
                ]);

            $result = $response->body();

            if (is_numeric($result)) {
                return (int) $result;
            }

            Log::warning("Invalid response from Sendy subscriber count. Response: {$result}");
            return null;
        } catch (\Exception $e) {
            Log::error("Error getting Sendy subscriber count: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if an email is subscribed to the list
     *
     * @param string $email
     * @return bool|null Returns null if the request fails
     */
    public function isSubscribed(string $email): ?bool
    {
        try {
            $response = Http::asForm()
                ->post($this->url . '/api/subscribers/subscription-status.php', [
                    'api_key' => $this->apiKey,
                    'email' => $email,
                    'list_id' => $this->listId
                ]);

            $result = $response->body();

            if ($result === 'Subscribed') {
                return true;
            } elseif ($result === 'Not Subscribed') {
                return false;
            }

            Log::warning("Invalid subscription status response for {$email}. Response: {$result}");
            return null;
        } catch (\Exception $e) {
            Log::error("Error checking subscription status for {$email}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Unsubscribe an email from the list
     *
     * @param string $email
     * @return bool
     */
    public function unsubscribe(string $email): bool
    {
        try {
            $response = Http::asForm()
                ->post($this->url . '/api/subscribers/unsubscribe.php', [
                    'api_key' => $this->apiKey,
                    'email' => $email,
                    'list_id' => $this->listId,
                    'boolean' => 'true'
                ]);

            $result = $response->body();

            if ($result === '1') {
                Log::info("Successfully unsubscribed {$email} from Sendy list");
                return true;
            }

            Log::warning("Failed to unsubscribe {$email} from Sendy list. Response: {$result}");
            return false;
        } catch (\Exception $e) {
            Log::error("Error unsubscribing {$email} from Sendy list: " . $e->getMessage());
            return false;
        }
    }
}
