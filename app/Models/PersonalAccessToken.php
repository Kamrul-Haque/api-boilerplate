<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'token',
        'abilities',
        'expires_at',
        'session_id',
        'token_version',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'revoked_at' => 'datetime',
            'token_version' => 'integer',
        ];
    }

    /**
     * Find the token instance matching the given token, preferring the cache.
     */
    public static function findToken($token)
    {
        $parts = explode('|', $token, 2);
        $plain = count($parts) === 2 ? $parts[1] : $token;

        $cacheKey = static::cacheKey($token);

        $cached = Cache::get($cacheKey);

        if (is_array($cached) && hash_equals((string) ($cached['token'] ?? ''), hash('sha256', $plain))) {
            return (new static)->newFromBuilder($cached);
        }

        $instance = parent::findToken($token);

        if ($instance) {
            Cache::put($cacheKey, $instance->getAttributes(), static::cacheTtl($instance));
        }

        return $instance;
    }

    /**
     * Find the token instance matching the given token, bypassing the cache.
     */
    public static function findTokenFresh($token): ?static
    {
        return parent::findToken($token);
    }

    /**
     * Get the cache key for the given plain text token.
     */
    public static function cacheKey(string $token): string
    {
        $parts = explode('|', $token, 2);

        return count($parts) === 2
            ? static::cacheKeyForId($parts[0])
            : config('token.cache_prefix').hash('sha256', $token);
    }

    /**
     * Get the cache key for the given token id.
     */
    public static function cacheKeyForId(int|string $id): string
    {
        return config('token.cache_prefix').$id;
    }

    /**
     * Get the cache key marking the given session as revoked.
     */
    public static function sessionRevokedKey(string $sessionId): string
    {
        return config('token.cache_prefix').'session_revoked:'.$sessionId;
    }

    /**
     * Get the cache ttl for the given token.
     */
    protected static function cacheTtl(self $token): int
    {
        $ttl = (int) config('token.cache_ttl');

        if ($token->expires_at) {
            $ttl = min($ttl, max(1, (int) now()->diffInSeconds($token->expires_at, false)));
        }

        return $ttl;
    }
}
