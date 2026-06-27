<?php

namespace App\DTOs\Api;

use App\Http\Requests\Api\LoginRequest;

readonly class LoginData
{
    /**
     * Create a new LoginData instance.
     */
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    /**
     * Create a new LoginData instance from a LoginRequest.
     */
    public static function fromRequest(LoginRequest $request): self
    {
        return new self(
            email: $request->validated('email'),
            password: $request->validated('password'),
        );
    }

    /**
     * Convert the LoginData instance to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
