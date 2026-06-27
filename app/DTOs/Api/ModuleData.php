<?php

namespace App\DTOs\Api;

use App\Http\Requests\Api\ModuleRequest;

readonly class ModuleData
{
    /**
     * Create a new ModuleData instance.
     */
    public function __construct(
        public string $name,
        public string $route_prefix,
        public int $priority,
        public ?string $description = null,
    ) {}

    /**
     * Create a new ModuleData instance from a ModuleRequest.
     */
    public static function fromRequest(ModuleRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            route_prefix: $request->validated('route_prefix'),
            priority: (int) $request->validated('priority'),
            description: $request->validated('description'),
        );
    }

    /**
     * Convert the ModuleData instance to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'route_prefix' => $this->route_prefix,
            'priority' => $this->priority,
            'description' => $this->description,
        ];
    }
}
