<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\DTOs\Api\LoginData;
use App\DTOs\Token\TokenPairData;
use App\Models\User;
use App\Models\VerificationCode;
use App\Services\TokenService;
use Hash;
use Illuminate\Auth\AuthenticationException;

class LoginAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @return array{user: User, tokens: ?TokenPairData, two_factor: ?VerificationCode}
     *
     * @throws AuthenticationException
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
                'two_factor' => app(StartTwoFactorChallengeAction::class)->handle($user),
            ];
        }

        return [
            'user' => $user,
            'tokens' => app(TokenService::class)->issueTokenPair($user),
            'two_factor' => null,
        ];
    }
}
