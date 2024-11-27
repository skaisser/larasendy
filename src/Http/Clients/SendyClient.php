<?php

namespace Skaisser\LaraSendy\Http\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class SendyClient
{
    protected $client;
    protected $config;
    protected $standardFields = ['email', 'name', 'country', 'ipaddress'];

    public function __construct()
    {
        $this->config = config('sendy');
        $this->client = new Client([
            'base_uri' => rtrim($this->config['url'], '/') . '/',
            'timeout' => 30,
        ]);
    }

    /**
     * Subscribe a user to the Sendy list
     *
     * @param array $userData
     * @return array
     */
    public function subscribe(array $userData): array
    {
        try {
            $params = $this->buildSubscribeParams($userData);
            $response = $this->client->post('subscribe', [
                'form_params' => $params
            ]);

            $body = (string) $response->getBody();
            
            return [
                'success' => $body === '1',
                'message' => $this->parseResponse($body)
            ];
        } catch (GuzzleException $e) {
            Log::error('Sendy API Error', [
                'message' => $e->getMessage(),
                'user' => $userData['email'] ?? 'unknown'
            ]);

            return [
                'success' => false,
                'message' => 'Failed to connect to Sendy API: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Build parameters for the subscribe request
     *
     * @param array $userData
     * @return array
     */
    protected function buildSubscribeParams(array $userData): array
    {
        // Required parameters
        $params = [
            'api_key' => $this->config['api_key'],
            'list' => $this->config['list_id'],
            'boolean' => 'true'
        ];

        // Add email (required)
        if (!isset($userData['email'])) {
            throw new \InvalidArgumentException('Email is required for Sendy subscription');
        }
        $params['email'] = $userData['email'];

        // Add standard fields if present
        foreach ($this->standardFields as $field) {
            if ($field !== 'email' && isset($userData[$field])) {
                $params[$field] = $userData[$field];
            }
        }

        // Add global configuration parameters
        if ($this->config['gdpr']) {
            $params['gdpr'] = 'true';
        }

        if ($this->config['silent']) {
            $params['silent'] = 'true';
        }

        if ($this->config['honeypot']) {
            $params['hp'] = '';
        }

        if ($this->config['referrer']) {
            $params['referrer'] = $this->config['referrer'];
        }

        // Add any custom fields
        foreach ($userData as $field => $value) {
            if (!in_array($field, $this->standardFields) && $value !== null) {
                $params["custom[{$field}]"] = $value;
            }
        }

        return $params;
    }

    /**
     * Parse Sendy API response
     *
     * @param string $response
     * @return string
     */
    protected function parseResponse(string $response): string
    {
        $messages = [
            '1' => 'Subscribed successfully',
            'Already subscribed.' => 'Already subscribed',
            'Invalid API key' => 'Invalid API key',
            'Invalid list ID' => 'Invalid list ID',
            'Invalid email address' => 'Invalid email address',
            'Some fields are missing.' => 'Required fields are missing',
            'Invalid GDPR value.' => 'Invalid GDPR value',
            'API subscription is disabled.' => 'API subscription is disabled',
        ];

        return $messages[$response] ?? $response;
    }
}
