<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\Models\User;

class LogoutAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(User $user)
    {
        $user->currentAccessToken()->delete();
    }
}
