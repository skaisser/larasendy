<?php

namespace Skaisser\LaraSendy\Http\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Config;

class SendyClient
{
    protected $client;
    protected $url;
    protected $apiKey;
    protected $listId;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->url = Config::get('sendy.url');
        $this->apiKey = Config::get('sendy.api_key');
        $this->listId = Config::get('sendy.list_id');
    }

    public function subscribe(array $data)
    {
        if (empty($data['email'])) {
            throw new \InvalidArgumentException('Email is required for Sendy subscription');
        }

        $params = $this->buildParameters($data);

        try {
            $response = $this->client->post($this->url . '/subscribe', [
                'form_params' => $params
            ]);

            $result = (string) $response->getBody();

            if ($result === 'true' || $result === '1') {
                return true;
            }

            return $result;
        } catch (GuzzleException $e) {
            return 'Failed to connect to Sendy API: ' . $e->getMessage();
        }
    }

    protected function buildParameters(array $data)
    {
        $params = [
            'api_key' => $this->apiKey,
            'list' => $this->listId,
            'email' => $data['email'],
            'boolean' => 'true'
        ];

        // Add standard fields
        foreach (['name', 'country', 'ipaddress', 'referrer'] as $field) {
            if (!empty($data[$field])) {
                $params[$field] = $data[$field];
            }
        }

        // Add global configuration parameters
        foreach (['gdpr', 'silent', 'honeypot', 'referrer'] as $param) {
            $value = Config::get('sendy.' . $param);
            if ($value !== null) {
                if ($param === 'honeypot') {
                    $params['hp'] = '';  // Empty string for honeypot
                } else {
                    $params[$param] = $value ? 'true' : 'false';
                }
            }
        }

        // Add custom fields
        foreach ($data as $key => $value) {
            if (!in_array($key, ['email', 'name', 'country', 'ipaddress', 'referrer'])) {
                $params['custom[' . $key . ']'] = $value;
            }
        }

        return $params;
    }
}
