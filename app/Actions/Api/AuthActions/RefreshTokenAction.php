<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\DTOs\Api\RefreshTokenData;
use App\DTOs\Token\TokenPairData;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Auth\AuthenticationException;

class RefreshTokenAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @return array{user: User, tokens: TokenPairData}
     *
     * @throws AuthenticationException
     */
    public function handle(RefreshTokenData $refreshTokenData): array
    {
        return app(TokenService::class)->refresh($refreshTokenData->refresh_token);
    }
}
