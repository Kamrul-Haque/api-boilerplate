<?php

namespace App\Casts;

use App\Services\DatetimeConversionService;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class LocalDateTime implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?object
    {
        $timezone = (auth()->check() && auth()->user()->timezone)
            ? auth()->user()->timezone
            : config('app.timezone');

        if (! is_null($value)) {
            return (object) DatetimeConversionService::convertToLocal(
                $value,
                $timezone,
                config('app.date_format'),
                config('app.time_format')
            );
        }

        return null;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $timezone = (auth()->check() && auth()->user()->timezone)
            ? auth()->user()->timezone
            : config('app.timezone');

        if (is_string($value)) {
            $carbonValue = Carbon::parse($value, $timezone);

            if ($carbonValue->isStartOfDay(true)) {
                $carbonValue->setTimeFrom(Carbon::now($timezone));
            }

            $value = $carbonValue;
        } elseif (! ($value instanceof Carbon)) {
            return $attributes[$key] ?? null;
        }

        return DatetimeConversionService::convertToUTC($value, $timezone);
    }
}
