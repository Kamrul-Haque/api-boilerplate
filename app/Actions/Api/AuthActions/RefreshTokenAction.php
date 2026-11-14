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
    public function __construct(private readonly TokenService $tokenService)
    {
        parent::__construct();
    }

    /**
     * Perform the action
     *
     * @return object{user: User, tokens: TokenPairData}
     *
     * @throws AuthenticationException
     */
    public function handle(RefreshTokenData $refreshTokenData): object
    {
        $data = $this->tokenService->refresh($refreshTokenData->refresh_token);

        return (object) [
            'user' => $data['user'],
            'accessToken' => $data['tokens']->accessToken,
            'refreshToken' => $data['tokens']->refreshToken,
            'expiresIn' => $data['tokens']->expiresIn,
        ];
    }
}
