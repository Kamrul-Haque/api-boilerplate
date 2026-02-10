<?php

namespace App\Models;

use App\Traits\HasAuthorizeOwner;
use App\Traits\HasCreatedBy;
use App\Traits\Trashable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasAuthorizeOwner;
    use HasCreatedBy;
    use Trashable;

    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'active_role_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'is_reserved' => 'boolean',
        ];
    }
}
