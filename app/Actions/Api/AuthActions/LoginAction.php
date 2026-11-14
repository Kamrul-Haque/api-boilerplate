<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\DTOs\Api\LoginData;
use App\Models\User;
use App\Services\TokenService;
use Exception;
use Hash;
use Illuminate\Auth\AuthenticationException;

class LoginAction extends BaseAction
{
    public function __construct(
        private readonly TokenService $tokenService,
        private readonly StartTwoFactorChallengeAction $startTwoFactorChallengeAction
    ) {
        parent::__construct();
    }

    /**
     * Perform the action
     *
     *
     * @throws AuthenticationException
     * @throws Exception
     */
    public function handle(LoginData $loginData): object
    {
        $user = User::where('email', $loginData->email)->first();

        if (! $user || ! Hash::check($loginData->password, $user->password)) {
            throw new AuthenticationException(trans('common.unauthorized'));
        }

        $user->load(['active_role', 'active_role.permissions', 'roles']);

        if ($user->requiresTwoFactorAuthentication()) {
            $verificationCode = $this->startTwoFactorChallengeAction->handle($user);

            return (object) [
                'user' => $user,
                'verificationToken' => $verificationCode->token,
                'expiresIn' => $verificationCode->expiresIn,
                'twoFactorRequired' => true,
            ];
        }

        $tokenPair = $this->tokenService->issueTokenPair($user);

        return (object) [
            'user' => $user,
            'accessToken' => $tokenPair->accessToken,
            'refreshToken' => $tokenPair->refreshToken,
            'expiresIn' => $tokenPair->expiresIn,
            'twoFactorRequired' => false,
        ];
    }
}
