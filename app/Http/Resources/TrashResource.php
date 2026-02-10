<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrashResource extends JsonResource
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
            'trashable_type' => $this->trashable_type,
            'trashable_id' => $this->trashable_id,
            'data' => $this->data,
            'deleted_by' => $this->deleted_by,
            'deleted_at' => $this->created_at,
        ];
    }
}
