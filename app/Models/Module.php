<?php

namespace App\Models;

use App\Traits\HasCreatedBy;
use App\Traits\Trashable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasCreatedBy;
    use HasFactory;
    use Trashable;

    protected $guarded = ['id'];

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }
}
