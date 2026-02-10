<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Generates a unique id
 */
class GenerateUniqueIdService
{
    /**
     * Generates a unique id based on year & time
     *
     * @param  string|null  $prefix  prepended on the unique id if passed
     * @param  string|null  $suffix  appends on the unique id if passed
     */
    public static function generate(?string $prefix = null, ?string $suffix = null, bool $upper = true): string
    {
        $uniqueId = uniqid($prefix.now()->year, true).$suffix;

        return $upper ? Str::upper($uniqueId) : $uniqueId;
    }
}
