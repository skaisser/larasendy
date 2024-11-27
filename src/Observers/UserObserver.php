<?php

namespace Skaisser\LaraSendy\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Skaisser\LaraSendy\Http\Clients\SendyClient;

class UserObserver
{
    protected $sendyClient;

    public function __construct(SendyClient $sendyClient)
    {
        $this->sendyClient = $sendyClient;
    }

    public function deleted(Model $user)
    {
        // Check if it's a soft delete
        $isSoftDelete = method_exists($user, 'trashed') && $user->trashed();
        
        // Get the appropriate action based on delete type
        $action = $isSoftDelete 
            ? Config::get('sendy.on_soft_delete_action', 'none')
            : Config::get('sendy.on_delete_action', 'none');

        // If no action is configured, return early
        if ($action === 'none') {
            return;
        }

        // Get the email field from the mapping configuration
        $emailField = Config::get('sendy.fields_mapping.email', 'email');
        
        if ($email = $user->{$emailField}) {
            if ($action === 'unsubscribe') {
                $this->sendyClient->unsubscribe($email);
            } elseif ($action === 'delete') {
                $this->sendyClient->deleteSubscriber($email);
            }
        }
    }
}
