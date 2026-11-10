<?php

namespace App\Traits;

use App\Support\DatetimeConversionHelper;

/**
 *  Formats duration attribute of the model
 */
trait HasDuration
{
    /**
     * Formats the duration attribute
     */
    public function getDurationAttribute($value): ?string
    {
        if ($value) {
            return DatetimeConversionHelper::convertMinutesToDiffHuman($value);
        }

        return null;
    }
}
