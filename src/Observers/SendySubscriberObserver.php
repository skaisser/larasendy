<?php

declare(strict_types=1);

namespace Skaisser\LaraSendy\Observers;

use Illuminate\Database\Eloquent\Model;
use Skaisser\LaraSendy\Http\Clients\SendyClient;
use Illuminate\Support\Facades\{Cache, Log};

class SendySubscriberObserver
{
    /** @var SendyClient */
    protected $client;

    /** @var string */
    protected $emailField;

    /** @var string */
    protected $onDeleteAction;

    /** @var string */
    protected $onSoftDeleteAction;

    public function __construct(SendyClient $client)
    {
        $this->client = $client;
        $this->emailField = config('sendy.fields_mapping.email', 'email');
        $this->onDeleteAction = config('sendy.on_delete_action', 'none');
        $this->onSoftDeleteAction = config('sendy.on_soft_delete_action', 'none');
    }

    /**
     * Handle the model's "created" event.
     *
     * @param Model $model
     * @return void
     */
    public function created(Model $model): void
    {
        if (!$this->shouldHandleModel($model)) {
            return;
        }

        $data = $this->prepareSubscriptionData($model);
        $result = $this->client->subscribe($data);

        if ($result === true) {
            Cache::put("sendy_sync_users_{$model->id}", now(), now()->addDays(30));
            Cache::forget("sendy_error_users_{$model->id}");
        } else {
            Cache::put("sendy_error_users_{$model->id}", 
                is_string($result) ? $result : json_encode($result), 
                now()->addDays(30)
            );
            Log::warning("Failed to subscribe user {$data['email']} to Sendy. Error: " . (is_string($result) ? $result : json_encode($result)));
        }
    }

    /**
     * Handle the model's "updated" event.
     *
     * @param Model $model
     * @return void
     */
    public function updated(Model $model): void
    {
        if (!$this->shouldHandleModel($model)) {
            return;
        }

        // If email has changed, unsubscribe old email first
        if ($model->isDirty($this->emailField)) {
            $oldEmail = $model->getOriginal($this->emailField);
            if ($oldEmail) {
                $this->client->unsubscribe($oldEmail);
            }
        }

        $data = $this->prepareSubscriptionData($model);
        $result = $this->client->subscribe($data);

        if ($result === true) {
            Cache::put("sendy_sync_users_{$model->id}", now(), now()->addDays(30));
            Cache::forget("sendy_error_users_{$model->id}");
        } else {
            Cache::put("sendy_error_users_{$model->id}", 
                is_string($result) ? $result : json_encode($result), 
                now()->addDays(30)
            );
            Log::warning("Failed to update user {$data['email']} in Sendy. Error: " . (is_string($result) ? $result : json_encode($result)));
        }
    }

    /**
     * Handle the model's "deleted" event.
     *
     * @param Model $model
     * @return void
     */
    public function deleted(Model $model): void
    {
        // Check if this is a soft delete
        if (method_exists($model, 'trashed') && $model->trashed()) {
            $this->handleSoftDelete($model);
            return;
        }

        $this->handleDelete($model);
    }

    /**
     * Handle the model's "restored" event.
     *
     * @param Model $model
     * @return void
     */
    public function restored(Model $model): void
    {
        if (!$this->shouldHandleModel($model)) {
            return;
        }

        $data = $this->prepareSubscriptionData($model);
        $result = $this->client->subscribe($data);

        if ($result === true) {
            Cache::put("sendy_sync_users_{$model->id}", now(), now()->addDays(30));
            Cache::forget("sendy_error_users_{$model->id}");
        } else {
            Cache::put("sendy_error_users_{$model->id}", 
                is_string($result) ? $result : json_encode($result), 
                now()->addDays(30)
            );
            Log::warning("Failed to resubscribe user {$data['email']} after restore. Error: " . (is_string($result) ? $result : json_encode($result)));
        }
    }

    /**
     * Handle permanent deletion
     *
     * @param Model $model
     * @return void
     */
    protected function handleDelete(Model $model): void
    {
        if (!$this->shouldHandleModel($model)) {
            return;
        }

        $email = $model->{$this->emailField};

        switch ($this->onDeleteAction) {
            case 'unsubscribe':
                $result = $this->client->unsubscribe($email);
                if (!$result['status']) {
                    Log::warning("Failed to unsubscribe {$email} on delete. Error: {$result['message']}");
                }
                break;

            case 'delete':
                $result = $this->client->delete($email);
                if (!$result['status']) {
                    Log::warning("Failed to delete {$email} on delete. Error: {$result['message']}");
                }
                break;

            case 'none':
            default:
                // Do nothing
                break;
        }

        // Clean up cache
        Cache::forget("sendy_sync_users_{$model->id}");
        Cache::forget("sendy_error_users_{$model->id}");
    }

    /**
     * Handle soft deletion
     *
     * @param Model $model
     * @return void
     */
    protected function handleSoftDelete(Model $model): void
    {
        if (!$this->shouldHandleModel($model)) {
            return;
        }

        $email = $model->{$this->emailField};

        switch ($this->onSoftDeleteAction) {
            case 'unsubscribe':
                $result = $this->client->unsubscribe($email);
                if (!$result['status']) {
                    Log::warning("Failed to unsubscribe {$email} on soft delete. Error: {$result['message']}");
                }
                break;

            case 'delete':
                $result = $this->client->delete($email);
                if (!$result['status']) {
                    Log::warning("Failed to delete {$email} on soft delete. Error: {$result['message']}");
                }
                break;

            case 'none':
            default:
                // Do nothing
                break;
        }

        // Clean up cache
        Cache::forget("sendy_sync_users_{$model->id}");
        Cache::forget("sendy_error_users_{$model->id}");
    }

    /**
     * Check if we should handle events for this model
     *
     * @param Model $model
     * @return bool
     */
    protected function shouldHandleModel(Model $model): bool
    {
        return !empty($model->{$this->emailField});
    }

    /**
     * Prepare subscription data from model
     *
     * @param Model $model
     * @return array
     */
    protected function prepareSubscriptionData(Model $model): array
    {
        $fieldMapping = config('sendy.fields_mapping', []);
        $data = [];

        foreach ($fieldMapping as $sendyField => $modelField) {
            if (isset($model->{$modelField})) {
                $data[$sendyField] = $model->{$modelField};
            }
        }

        return $data;
    }
}
