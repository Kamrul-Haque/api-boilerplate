<?php

namespace App\Services;

use App\DTOs\GenerateOneClickLoginTokenRequestData;
use App\DTOs\ParsedTokenData;
use App\DTOs\TokenPayloadData;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TokenService
{
    public const string JWT = 'jwt';

    public const string OPAQUE = 'opaque';

    /**
     * The token format.
     */
    private string $format;

    /**
     * The signing key.
     */
    private ?string $key = null;

    /**
     * The key ID.
     */
    private ?string $kid = null;

    /**
     * The algorithm used for signing.
     */
    private ?string $alg = null;

    /**
     * Token time to live
     */
    private ?int $ttl = null;

    /**
     * Create a new JwtService instance.
     */
    public function __construct(string $format = self::OPAQUE)
    {
        $this->format = $format;

        if ($this->format === self::OPAQUE) {
            $this->key = null;
        } elseif (config('token.secret') === 'app_key') {
            $appKey = config('app.key');

            if (str_starts_with($appKey, 'base64:')) {
                $this->key = base64_decode(substr($appKey, 7));
            } else {
                $this->key = $appKey;
            }
        } else {
            $this->key = config('token.secret');
        }

        $this->kid = config('token.kid');
        $this->alg = config('token.alg');
        $this->ttl = config('token.ttl');

        if ($this->format === self::OPAQUE) {
            if (is_null($this->kid) || is_null($this->ttl)) {
                throw new HttpException(500, trans('common.configuration_missing'));
            }
        } elseif (is_null($this->key) || is_null($this->kid) || is_null($this->alg) || is_null($this->ttl)) {
            throw new HttpException(500, trans('common.configuration_missing'));
        }
    }

    /**
     * Generate an encrypted JWT token.
     */
    public function generate(GenerateOneClickLoginTokenRequestData $data): TokenPayloadData
    {
        $now = Carbon::now();

        $header = [
            'alg' => $this->alg,
            'typ' => $this->format === self::OPAQUE ? self::OPAQUE : self::JWT,
            'kid' => $this->kid,
        ];

        $payload = [
            'jti' => $data->jti ?? (string) Str::uuid(),
            'site_id' => $data->siteId,
            'session_role' => $data->sessionRole,
            'user_id' => $data->userId,
            'user_name' => $data->userName,
            'iat' => $now->timestamp,
            'exp' => $now->copy()->addSeconds($this->ttl)->timestamp,
            'nbf' => $now->timestamp,
            'ip' => $data->ip,
            'user_agent' => $data->userAgent,
        ];

        $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));

        if ($this->format === self::JWT) {
            $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $this->key, true);
            $base64UrlSignature = $this->base64UrlEncode($signature);
        } else {
            $base64UrlSignature = null;
        }

        if ($this->format === self::JWT) {
            $token = "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
        } else {
            $token = Crypt::encryptString($base64UrlPayload);
        }

        return $this->parsePayloadData($payload, $token);
    }

    /**
     * Parse payload data to DTO
     */
    private function parsePayloadData(array $payload, ?string $token = null): TokenPayloadData
    {
        return new TokenPayloadData(
            token: $token,
            jti: $payload['jti'],
            sessionRole: $payload['session_role'],
            siteId: $payload['site_id'],
            iat: $payload['iat'],
            exp: $payload['exp'],
            nbf: $payload['nbf'],
            userId: $payload['user_id'],
            userName: $payload['user_name'],
            clientIp: $payload['ip'],
            clientUserAgent: $payload['user_agent'],
        );
    }

    /**
     * Decrypt the token and parse the parts
     *
     * @throws AuthenticationException
     */
    public function decryptAndParse(string $rawToken): ParsedTokenData
    {
        if ($this->format === self::OPAQUE) {
            try {
                $token = Crypt::decryptString($rawToken);
            } catch (DecryptException $exception) {
                throw new AuthenticationException(trans('common.invalid_token'));
            }

            $partsCount = 1;
        } else {
            $token = $rawToken;
            $partsCount = 3;
        }

        $parts = explode('.', $token);

        if (count($parts) !== $partsCount) {
            throw new AuthenticationException(trans('common.invalid_token'));
        }

        if ($this->format === self::OPAQUE) {
            [$base64UrlPayload] = $parts;
        } else {
            [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $parts;
        }

        $payload = json_decode($this->base64UrlDecode($base64UrlPayload), true);

        if (! is_array($payload) || ! isset($payload['jti'])) {
            throw new AuthenticationException(trans('common.invalid_token'));
        }

        return new ParsedTokenData(
            base64UrlHeader: $base64UrlHeader ?? null,
            base64UrlPayload: $base64UrlPayload,
            base64UrlSignature: $base64UrlSignature ?? null,
            payload: $this->parsePayloadData($payload),
            jti: $payload['jti'],
            siteId: $payload['site_id'],
            clientIp: $payload['ip'],
            clientUserAgent: $payload['user_agent'],
        );
    }

    /**
     * Validate and decode an encrypted JWT token.
     *
     *
     * @throws AuthenticationException
     */
    public function validate(ParsedTokenData $parsedTokenData): TokenPayloadData
    {
        $payload = $parsedTokenData->payload;

        $now = Carbon::now()->timestamp;
        $leeway = 5;

        if ($now > ($payload->exp + $leeway)) {
            throw new AuthenticationException(trans('common.invalid_token'));
        }

        if ($now < ($payload->nbf - $leeway)) {
            throw new AuthenticationException(trans('common.invalid_token'));
        }

        if ($this->format === self::JWT) {
            $signature = hash_hmac(
                'sha256',
                "$parsedTokenData->base64UrlHeader.$parsedTokenData->base64UrlPayload",
                $this->key,
                true);

            if (! hash_equals($this->base64UrlEncode($signature), $parsedTokenData->base64UrlSignature)) {
                throw new AuthenticationException(trans('common.invalid_token'));
            }
        }

        return $payload;
    }

    /**
     * Base64Url encode string.
     */
    private function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    /**
     * Base64Url decode string.
     */
    private function base64UrlDecode(string $data): string
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}
