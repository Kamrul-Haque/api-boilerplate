<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\Models\User;
use App\Services\TokenService;

class LogoutAction extends BaseAction
{
    public function __construct(private readonly TokenService $tokenService)
    {
        parent::__construct();
    }

    /**
     * Perform the action
     */
    public function handle(User $user, ?string $rawAccessToken = null): void
    {
        $this->tokenService->revokeCurrent($user, $rawAccessToken);
    }
}
