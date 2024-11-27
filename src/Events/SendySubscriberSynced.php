<?php

namespace Skaisser\LaraSendy\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendySubscriberSynced
{
    use Dispatchable, SerializesModels;

    /**
     * The model instance that was synced.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $model;

    /**
     * The sync response from Sendy.
     *
     * @var array
     */
    public $response;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $response
     * @return void
     */
    public function __construct(Model $model, array $response)
    {
        $this->model = $model;
        $this->response = $response;
    }
}
