<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Str;

trait EnumToArray
{
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function array(): array
    {
        $array = [];

        foreach (self::cases() as $case) {
            $array[] = (object) ['name' => Str::lower($case->name), 'value' => $case->value];
        }

        return $array;
    }

    /**
     * @throws Exception
     */
    public static function getValueFromCaseName(string $caseName): ?int
    {
        $case = Str::upper($caseName);

        if (! in_array($case, self::names(), true)) {
            throw new Exception('Invalid Case Name: '.$caseName);
        }

        return self::{$case}->value;
    }
}
