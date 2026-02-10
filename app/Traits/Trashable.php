<?php

namespace App\Traits;

use App\Models\Trash;

trait Trashable
{
    public static function bootTrashable(): void
    {
        static::deleted(function ($model) {
            Trash::create([
                'trashable_type' => get_class($model),
                'trashable_id' => $model->id,
                'user_id' => auth()->check() ? auth()->id() : null,
                'data' => $model->getAttributes(),
            ]);
        });
    }
}
