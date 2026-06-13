<?php

namespace App\DTOs;

use App\Http\Requests\Api\UserRequest;
use Illuminate\Http\UploadedFile;

readonly class UserData
{
    /**
     * Create a new UserData instance.
     */
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone = null,
        public ?UploadedFile $avatar = null,
        public ?array $roles = [],
        public ?int $parent_id = null,
    ) {}

    /**
     * Create a new UserData instance from a UserRequest.
     */
    public static function fromRequest(UserRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            phone: $request->validated('phone'),
            avatar: $request->validated('avatar'),
            roles: $request->validated('roles'),
            parent_id: $request->validated('parent_id'),
        );
    }

    /**
     * Convert the UserData instance to an array.
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
            'roles' => $this->roles,
            'parent_id' => $this->parent_id,
        ];
    }
}
