<?php

namespace App\DTOs\Api;

use App\Http\Requests\Api\TwoFactorVerifyRequest;

readonly class TwoFactorVerifyData
{
    /**
     * Create a new TwoFactorVerifyData instance.
     */
    public function __construct(
        public string $verification_token,
        public string $verification_code,
    ) {}

    /**
     * Create a new TwoFactorVerifyData instance from a TwoFactorVerifyRequest.
     */
    public static function fromRequest(TwoFactorVerifyRequest $request): self
    {
        return new self(
            verification_token: $request->validated('verification_token'),
            verification_code: $request->validated('verification_code'),
        );
    }

    /**
     * Convert the TwoFactorVerifyData instance to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'verification_token' => $this->verification_token,
            'verification_code' => $this->verification_code,
        ];
    }
}
