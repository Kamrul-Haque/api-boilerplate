<?php

namespace App\Models;

use App\Casts\LocalDateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trash extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'data' => 'json',
            'created_at' => LocalDateTime::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
