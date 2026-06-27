<?php

namespace App\DTOs\Api;

use App\Http\Requests\Api\UpdatePasswordRequest;

readonly class UpdatePasswordData
{
    /**
     * Create a new UpdatePasswordData instance.
     */
    public function __construct(
        public string $current_password,
        public string $password,
    ) {}

    /**
     * Create a new UpdatePasswordData instance from an UpdatePasswordRequest.
     */
    public static function fromRequest(UpdatePasswordRequest $request): self
    {
        return new self(
            current_password: $request->validated('current_password'),
            password: $request->validated('password'),
        );
    }

    /**
     * Convert the UpdatePasswordData instance to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'current_password' => $this->current_password,
            'password' => $this->password,
        ];
    }
}
