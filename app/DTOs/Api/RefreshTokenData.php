<?php

namespace App\DTOs\Api;

use App\Http\Requests\Api\RefreshTokenRequest;

readonly class RefreshTokenData
{
    /**
     * Create a new RefreshTokenData instance.
     */
    public function __construct(
        public string $refresh_token,
    ) {}

    /**
     * Create a new RefreshTokenData instance from a RefreshTokenRequest.
     */
    public static function fromRequest(RefreshTokenRequest $request): self
    {
        return new self(
            refresh_token: $request->validated('refresh_token'),
        );
    }

    /**
     * Convert the RefreshTokenData instance to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'refresh_token' => $this->refresh_token,
        ];
    }
}
