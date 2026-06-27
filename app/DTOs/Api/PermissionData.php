<?php

namespace App\DTOs\Api;

use App\Http\Requests\Api\PermissionRequest;

readonly class PermissionData
{
    /**
     * Create a new PermissionData instance.
     */
    public function __construct(
        public int $module_id,
        public string $display_name,
        public string $name,
    ) {}

    /**
     * Create a new PermissionData instance from a PermissionRequest.
     */
    public static function fromRequest(PermissionRequest $request): self
    {
        return new self(
            module_id: (int) $request->validated('module_id'),
            display_name: $request->validated('display_name'),
            name: $request->validated('name'),
        );
    }

    /**
     * Convert the PermissionData instance to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'module_id' => $this->module_id,
            'display_name' => $this->display_name,
            'name' => $this->name,
        ];
    }
}
