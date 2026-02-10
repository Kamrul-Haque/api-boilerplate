<?php

namespace App\Models;

use App\Traits\HasCreatedBy;
use App\Traits\Trashable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasCreatedBy;
    use Trashable;

    protected $guarded = ['id'];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }
}
