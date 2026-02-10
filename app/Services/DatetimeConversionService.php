<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class DatetimeConversionService
{
    /**
     * Converts 'UTC' timestamp to local timestamp or format
     *
     * @param  Carbon|string  $dateTime  datetime string or timestamp
     * @param  string  $localTimezone  PHP timezone
     * @param  string|null  $dateFormat  date format
     * @param  string|null  $timeFormat  time format
     */
    public static function convertToLocal(
        Carbon|string $dateTime,
        string $localTimezone,
        ?string $dateFormat = null,
        ?string $timeFormat = null
    ): array|Carbon {
        $carbonDateTime = Carbon::parse($dateTime, 'UTC')->setTimezone($localTimezone);

        return self::format($dateFormat, $timeFormat, $carbonDateTime);
    }

    /**
     * Converts local timestamp to 'UTC' timestamp or format
     *
     * @param  Carbon|string  $dateTime  datetime string or timestamp
     * @param  string|null  $dateFormat  date format
     * @param  string|null  $timeFormat  time format
     */
    public static function convertToUTC(
        Carbon|string $dateTime,
        string $localTimeZone,
        ?string $dateFormat = null,
        ?string $timeFormat = null
    ): array|Carbon {
        $carbonDateTime = Carbon::create($dateTime, $localTimeZone)->setTimezone('UTC');

        return self::format($dateFormat, $timeFormat, $carbonDateTime);
    }

    /**
     * Converts minutes to hours and minutes
     */
    public static function convertMinutesToDiffHuman(int $minutes): string
    {
        $options = [
            'join' => ' ',
            'parts' => 2,
            'syntax' => CarbonInterface::DIFF_ABSOLUTE,
            'short' => true,
        ];

        $start = now();

        return now()->addMinutes($minutes)->diffForHumans($start, $options);
    }

    /**
     * Create desired formats to return
     *
     * @param  string|null  $dateFormat  given date format
     * @param  string|null  $timeFormat  given time format
     * @param  Carbon  $carbonDateTime  given carbon datetime instance
     * @return Carbon|array formatted datetime
     */
    public static function format(?string $dateFormat, ?string $timeFormat, Carbon $carbonDateTime): Carbon|array
    {
        if ($dateFormat && $timeFormat) {
            return [
                'raw_time' => $carbonDateTime->toTimeString(),
                'raw_date' => $carbonDateTime->toDateString(),
                'raw_date_time' => $carbonDateTime->toDateTimeString(),
                'diff' => $carbonDateTime->diffForHumans(now(), CarbonInterface::DIFF_ABSOLUTE),
                'time' => $carbonDateTime->format($timeFormat),
                'date' => $carbonDateTime->format($dateFormat),
                'date_time' => $carbonDateTime->format($dateFormat).', '.$carbonDateTime->format($timeFormat),
            ];
        }

        if ($dateFormat) {
            $carbonDateTime->format($dateFormat);
        }

        if ($timeFormat) {
            $carbonDateTime->format($timeFormat);
        }

        return $carbonDateTime;
    }
}
