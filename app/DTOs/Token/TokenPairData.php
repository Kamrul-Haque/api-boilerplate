<?php

namespace App\DTOs\Token;

readonly class TokenPairData
{
    /**
     * Create a new TokenPairData instance.
     */
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public string $tokenType,
        public int $expiresIn,
    ) {}

    /**
     * Convert the TokenPairData instance to an array.
     *
     * @return array<string, string|int>
     */
    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
        ];
    }
}
