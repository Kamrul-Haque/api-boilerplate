<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *  Adds Created By Attribute to Model
 */
trait HasCreatedBy
{
    /**
     * initializes the trait
     */
    public static function bootHasCreatedBy(): void
    {
        static::creating(function ($model) {
            if (! isset($model->getAttributes()['created_by_id'])) {
                $model->created_by_id = auth()->check() ? auth()->user()->id : null;
            }
        });

        $class = static::class;
        $method = 'initializeHasCreatedBy';

        static::$traitInitializers[$class][] = $method;

        static::$traitInitializers[$class] = array_unique(
            static::$traitInitializers[$class]
        );
    }

    /**
     * Appends 'created_by_name' attribute to parent class
     */
    protected function initializeHasCreatedBy(): array
    {
        $this->appends = array_unique(array_merge($this->appends, ['created_by_name']));

        return parent::getArrayableAppends();
    }

    /**
     * appends a 'created_by_name' attribute to the model
     */
    protected function getArrayableAppends(): array
    {
        $this->appends = array_unique(array_merge($this->appends, ['created_by_name']));

        return parent::getArrayableAppends();
    }

    /**
     * gets the 'name' of the 'user' who created the record
     */
    public function getCreatedByNameAttribute(): string
    {
        if ($this->created_by_id) {
            return $this->created_by->name;
        }

        return 'System';
    }

    /**
     * defines a belongs to relationship with the 'user' model
     */
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
