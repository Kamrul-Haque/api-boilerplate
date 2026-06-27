<?php

namespace App\DTOs\Api;

use App\Http\Requests\Api\RoleRequest;

readonly class RoleData
{
    /**
     * Create a new RoleData instance.
     */
    public function __construct(
        public string $display_name,
        public string $name,
        public array $permissions,
        public bool $is_reserved,
        public ?string $description = null,
    ) {}

    /**
     * Create a new RoleData instance from a RoleRequest.
     */
    public static function fromRequest(RoleRequest $request): self
    {
        return new self(
            display_name: $request->validated('display_name'),
            name: $request->validated('name'),
            permissions: $request->validated('permissions'),
            is_reserved: (bool) $request->validated('is_reserved'),
            description: $request->validated('description'),
        );
    }

    /**
     * Convert the RoleData instance to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'display_name' => $this->display_name,
            'name' => $this->name,
            'permissions' => $this->permissions,
            'is_reserved' => $this->is_reserved,
            'description' => $this->description,
        ];
    }
}
