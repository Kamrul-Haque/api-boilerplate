<?php

namespace App\DTOs\Api;

use App\Http\Requests\Api\RegisterRequest;
use Illuminate\Http\UploadedFile;

readonly class RegisterData
{
    /**
     * Create a new RegisterData instance.
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $phone = null,
        public ?UploadedFile $avatar = null,
    ) {}

    /**
     * Create a new RegisterData instance from a RegisterRequest.
     */
    public static function fromRequest(RegisterRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            phone: $request->validated('phone'),
            avatar: $request->validated('avatar'),
        );
    }

    /**
     * Convert the RegisterData instance to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
        ];
    }
}
