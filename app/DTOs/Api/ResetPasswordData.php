<?php

namespace App\DTOs\Api;

use App\Http\Requests\Api\ResetPasswordRequest;

readonly class ResetPasswordData
{
    /**
     * Create a new ResetPasswordData instance.
     */
    public function __construct(
        public string $password_reset_token,
        public string $verification_code,
        public string $password,
    ) {}

    /**
     * Create a new ResetPasswordData instance from a ResetPasswordRequest.
     */
    public static function fromRequest(ResetPasswordRequest $request): self
    {
        return new self(
            password_reset_token: $request->validated('password_reset_token'),
            verification_code: $request->validated('verification_code'),
            password: $request->validated('password'),
        );
    }

    /**
     * Convert the ResetPasswordData instance to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'password_reset_token' => $this->password_reset_token,
            'verification_code' => $this->verification_code,
            'password' => $this->password,
        ];
    }
}
