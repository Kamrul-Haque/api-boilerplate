<?php

namespace App\Services;

use App\DTOs\Token\TokenPairData;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;

class TokenService
{
    /**
     * Issue an access token and a refresh token for the same session.
     */
    public function issueTokenPair(User $user, ?string $sessionId = null): TokenPairData
    {
        $sessionId ??= (string) Str::uuid();

        $accessToken = $this->createToken(
            $user,
            config('token.access_name'),
            ['*'],
            (int) config('token.access_ttl'),
            $sessionId
        );

        $refreshToken = $this->createToken(
            $user,
            config('token.refresh_name'),
            ['refresh'],
            (int) config('token.refresh_ttl'),
            $sessionId
        );

        return new TokenPairData(
            accessToken: $accessToken->plainTextToken,
            refreshToken: $refreshToken->plainTextToken,
            tokenType: 'Bearer',
            expiresIn: (int) config('token.access_ttl') * 60,
        );
    }

    /**
     * Validate the given refresh token and rotate it into a new token pair.
     *
     * @return array{user: User, tokens: TokenPairData}
     *
     * @throws AuthenticationException
     */
    public function refresh(string $rawRefreshToken): array
    {
        $token = PersonalAccessToken::findTokenFresh($rawRefreshToken);

        if (! $token || $token->name !== config('token.refresh_name')) {
            throw new AuthenticationException(trans('common.unauthorized'));
        }

        if ($token->revoked_at !== null) {
            if ($token->session_id) {
                $this->revokeSession($token->session_id);
            }

            throw new AuthenticationException(trans('common.unauthorized'));
        }

        if ($token->expires_at?->isPast()) {
            throw new AuthenticationException(trans('common.unauthorized'));
        }

        $user = $token->tokenable;

        if (! $user || (int) $token->token_version !== (int) $user->token_version) {
            throw new AuthenticationException(trans('common.unauthorized'));
        }

        $token->forceFill(['revoked_at' => now()])->save();

        if ($token->session_id) {
            $revokedIds = PersonalAccessToken::where('session_id', $token->session_id)
                ->where('name', config('token.access_name'))
                ->pluck('id');

            PersonalAccessToken::where('session_id', $token->session_id)
                ->where('name', config('token.access_name'))
                ->delete();

            $revokedIds->each(fn ($id) => Cache::forget(PersonalAccessToken::cacheKeyForId($id)));
        }

        $tokens = $this->issueTokenPair($user, $token->session_id);

        $user->load(['active_role', 'active_role.permissions', 'roles']);

        return [
            'user' => $user,
            'tokens' => $tokens,
        ];
    }

    /**
     * Revoke the current session tokens.
     */
    public function revokeCurrent(User $user, ?string $rawAccessToken = null): void
    {
        $currentToken = $user->currentAccessToken();

        if ($currentToken instanceof PersonalAccessToken) {
            if ($currentToken->session_id) {
                $this->revokeSession($currentToken->session_id);
            } else {
                $currentToken->delete();
            }
        }

        if ($rawAccessToken) {
            Cache::forget(PersonalAccessToken::cacheKey($rawAccessToken));
        }
    }

    /**
     * Revoke all tokens belonging to the given session.
     */
    public function revokeSession(string $sessionId): void
    {
        $revokedIds = PersonalAccessToken::where('session_id', $sessionId)->pluck('id');

        PersonalAccessToken::where('session_id', $sessionId)->delete();

        $revokedIds->each(fn ($id) => Cache::forget(PersonalAccessToken::cacheKeyForId($id)));

        Cache::put(
            PersonalAccessToken::sessionRevokedKey($sessionId),
            true,
            (int) config('token.access_ttl') * 60
        );
    }

    /**
     * Revoke all tokens for the user across all devices.
     */
    public function revokeAll(User $user): void
    {
        $revokedIds = $user->tokens()->pluck('id');

        $user->tokens()->delete();

        $revokedIds->each(fn ($id) => Cache::forget(PersonalAccessToken::cacheKeyForId($id)));

        $user->forceFill([
            'token_version' => ((int) $user->token_version) + 1,
        ])->save();
    }

    /**
     * Create a token and attach its session metadata.
     */
    private function createToken(
        User $user,
        string $name,
        array $abilities,
        int $ttlMinutes,
        string $sessionId
    ): NewAccessToken {
        $newAccessToken = $user->createToken($name, $abilities, now()->addMinutes($ttlMinutes));

        $newAccessToken->accessToken->forceFill([
            'session_id' => $sessionId,
            'token_version' => (int) ($user->token_version ?? 0),
        ])->save();

        return $newAccessToken;
    }
}
