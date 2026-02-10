<?php

namespace App\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 *  Adds Slug Functionalities to the Model
 */
trait HasSlug
{
    /**
     * Sets 'slug' attribute of the model
     */
    public static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            self::slugify($model);
        });

        static::updating(function ($model) {
            self::slugify($model);
        });
    }

    /**
     * Generates 'slug' from 'name' or 'title' or 'display_name'
     */
    public static function slugify($model): void
    {
        $model->slug = Schema::hasColumn($model->getTable(), 'title')
            ? Str::slug($model->title)
            : Str::slug($model->name);
    }

    /**
     * Finds the model by the given 'slug' attribute
     */
    public static function findBySlug($slug): object
    {
        return static::where('slug', $slug)->first();
    }
}
