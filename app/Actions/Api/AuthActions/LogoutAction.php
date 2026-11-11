<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\Models\User;
use App\Services\TokenService;

class LogoutAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(User $user, ?string $rawAccessToken = null): void
    {
        app(TokenService::class)->revokeCurrent($user, $rawAccessToken);
    }
}
