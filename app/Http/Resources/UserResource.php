<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar ? route('users.avatar', $this) : null,
            'active_role' => new ActiveRoleResource($this->whenLoaded('active_role')),
            'roles' => RoleIndexResource::collection($this->whenLoaded('roles')),
            'permissions' => $this->whenLoaded('active_role', function () {
                return $this->permissions();
            }),
        ];
    }
}
