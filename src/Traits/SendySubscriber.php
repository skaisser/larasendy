<?php

namespace Skaisser\LaraSendy\Traits;

use Skaisser\LaraSendy\Http\Clients\SendyClient;
use Skaisser\LaraSendy\Observers\SendySubscriberObserver;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

trait SendySubscriber
{
    /**
     * Boot the trait
     */
    public static function bootSendySubscriber()
    {
        static::observe(SendySubscriberObserver::class);
    }

    /**
     * Get the cache key for this model's sync status
     */
    protected function getSendySyncCacheKey(): string
    {
        return "sendy_sync_{$this->getTable()}_{$this->getKey()}";
    }

    /**
     * Get the last sync time
     */
    public function getLastSendySyncTime()
    {
        return Cache::get($this->getSendySyncCacheKey());
    }

    /**
     * Set the last sync time
     */
    protected function updateSendySyncTime(): void
    {
        Cache::put($this->getSendySyncCacheKey(), now(), now()->addDays(30));
    }

    /**
     * Get the email field for Sendy
     */
    public function getSendyEmailAttribute(): ?string
    {
        $field = config('sendy.fields_mapping.email', 'email');
        return $this->{$field};
    }

    /**
     * Get the name field for Sendy
     */
    public function getSendyNameAttribute(): ?string
    {
        $field = config('sendy.fields_mapping.name', 'name');
        return $this->{$field};
    }

    /**
     * Get custom fields for Sendy
     */
    public function getSendyCustomFieldsAttribute(): array
    {
        $mapping = config('sendy.fields_mapping', []);
        $customFields = [];

        foreach ($mapping as $sendyField => $modelField) {
            // Skip standard fields
            if (in_array($sendyField, ['email', 'name'])) {
                continue;
            }

            if (isset($this->{$modelField})) {
                $customFields[$sendyField] = $this->{$modelField};
            }
        }

        return $customFields;
    }

    /**
     * Subscribe to Sendy
     */
    public function subscribeToSendy(): bool
    {
        if (!$this->sendy_email) {
            return false;
        }

        $client = App::make(SendyClient::class);
        
        $data = [
            'email' => $this->sendy_email,
            'name' => $this->sendy_name,
        ];

        // Add custom fields
        foreach ($this->sendy_custom_fields as $field => $value) {
            $data[$field] = $value;
        }

        $result = $client->subscribe($data);
        
        if ($result['status']) {
            $this->updateSendySyncTime();
            Cache::forget($this->getSendyErrorCacheKey());
        } else {
            $this->setSendyError($result['message']);
        }

        return $result['status'];
    }

    /**
     * Get the cache key for this model's error status
     */
    protected function getSendyErrorCacheKey(): string
    {
        return "sendy_error_{$this->getTable()}_{$this->getKey()}";
    }

    /**
     * Get the last Sendy error
     */
    public function getSendyError(): ?string
    {
        return Cache::get($this->getSendyErrorCacheKey());
    }

    /**
     * Set a Sendy error
     */
    protected function setSendyError(string $error): void
    {
        Cache::put($this->getSendyErrorCacheKey(), $error, now()->addDays(30));
    }

    /**
     * Unsubscribe from Sendy
     */
    public function unsubscribeFromSendy(): bool
    {
        if (!$this->sendy_email) {
            return false;
        }

        $client = App::make(SendyClient::class);
        $result = $client->unsubscribe($this->sendy_email);

        if ($result['status']) {
            $this->updateSendySyncTime();
            Cache::forget($this->getSendyErrorCacheKey());
        } else {
            $this->setSendyError($result['message']);
        }

        return $result['status'];
    }

    /**
     * Delete from Sendy
     */
    public function deleteFromSendy(): bool
    {
        if (!$this->sendy_email) {
            return false;
        }

        $client = App::make(SendyClient::class);
        $result = $client->delete($this->sendy_email);

        if ($result['status']) {
            $this->updateSendySyncTime();
            Cache::forget($this->getSendyErrorCacheKey());
        } else {
            $this->setSendyError($result['message']);
        }

        return $result['status'];
    }

    /**
     * Check subscription status in Sendy
     */
    public function checkSendyStatus(): array
    {
        if (!$this->sendy_email) {
            return ['status' => false, 'message' => 'No email address'];
        }

        $client = App::make(SendyClient::class);
        $result = $client->isSubscribed($this->sendy_email);

        if ($result['status']) {
            $this->updateSendySyncTime();
            Cache::forget($this->getSendyErrorCacheKey());
        } else {
            $this->setSendyError($result['message']);
        }

        return $result;
    }
}
