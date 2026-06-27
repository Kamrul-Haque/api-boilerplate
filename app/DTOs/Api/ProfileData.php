<?php

namespace App\DTOs\Api;

use App\Http\Requests\Api\ProfileRequest;
use Illuminate\Http\UploadedFile;

readonly class ProfileData
{
    /**
     * Create a new ProfileData instance.
     */
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone = null,
        public ?UploadedFile $avatar = null,
    ) {}

    /**
     * Create a new ProfileData instance from a ProfileRequest.
     */
    public static function fromRequest(ProfileRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            phone: $request->validated('phone'),
            avatar: $request->validated('avatar'),
        );
    }

    /**
     * Convert the ProfileData instance to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
        ];
    }
}
