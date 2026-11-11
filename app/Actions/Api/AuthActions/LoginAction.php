<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\DTOs\Api\LoginData;
use App\DTOs\Token\TokenPairData;
use App\Models\User;
use App\Models\VerificationCode;
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
     * @return array{user: User, tokens: ?TokenPairData, two_factor: ?VerificationCode}
     *
     * @throws AuthenticationException
     * @throws Exception
     */
    public function handle(LoginData $loginData): array
    {
        $user = User::where('email', $loginData->email)->first();

        if (! $user || ! Hash::check($loginData->password, $user->password)) {
            throw new AuthenticationException(trans('common.unauthorized'));
        }

        $user->load(['active_role', 'active_role.permissions', 'roles']);

        if ($user->requiresTwoFactorAuthentication()) {
            return [
                'user' => $user,
                'tokens' => null,
                'two_factor' => $this->startTwoFactorChallengeAction->handle($user),
            ];
        }

        return [
            'user' => $user,
            'tokens' => $this->tokenService->issueTokenPair($user),
            'two_factor' => null,
        ];
    }
}
