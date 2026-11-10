<?php

namespace App\Support;

use Illuminate\Support\Str;

class HashHelper
{
    /**
     * Generate a new key tuple (prefix, secret, raw_key, secret_hash).
     *
     * @return array{key: string, secret: string, raw_key: string, secret_hash: string}
     */
    public static function generate(string $prefix = 'ab_live'): array
    {
        $key = $prefix.'_'.Str::random(16);
        $secret = Str::random(32);

        return [
            'key' => $key,
            'secret' => $secret,
            'raw_key' => "{$key}.{$secret}",
            'secret_hash' => static::hashSecret($secret),
        ];
    }

    /**
     * Parse and split a raw composite API key into prefix and secret.
     *
     * @return array{0: string, 1: string}|null
     */
    public static function parse(?string $rawKey): ?array
    {
        if (! $rawKey) {
            return null;
        }

        $clean = trim($rawKey, " \t\n\r\0\x0B\"'");

        if (! str_contains($clean, '.')) {
            return null;
        }

        [$prefix, $secret] = explode('.', $clean, 2);

        return [$prefix, $secret];
    }

    /**
     * Hash a secret using SHA-256.
     */
    public static function hashSecret(string $secret): string
    {
        return hash('sha256', $secret);
    }

    /**
     * Verify a secret against its stored SHA-256 hash using constant-time comparison.
     */
    public static function verifySecret(string $knownHash, string $secret): bool
    {
        return hash_equals($knownHash, static::hashSecret($secret));
    }
}
