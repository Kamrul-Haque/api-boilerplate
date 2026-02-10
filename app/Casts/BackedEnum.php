<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BackedEnum implements CastsAttributes
{
    protected string $enumClass;

    public function __construct(string $enumClass)
    {
        $enumClass = 'App\\Enums\\'.$enumClass;

        if (! enum_exists($enumClass)) {
            throw new InvalidArgumentException("The given class {$enumClass} is not a valid backed enum.");
        }

        $this->enumClass = $enumClass;
    }

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?object
    {
        if (is_null($value)) {
            return null;
        }

        $enum = $this->enumClass::tryFrom($value);

        if (! $enum) {
            return null;
        }

        return (object) [
            'case' => $enum->name,
            'slug' => Str::lower($enum->name),
            'name' => Str::lower(str_replace('_', ' ', $enum->name)),
            'value' => $enum->value,
        ];
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return $model->exists
                ? $attributes[$key]
                : null;
        }

        if (is_int($value)) {
            return $value;
        }

        if ($value instanceof $this->enumClass) {
            return $value->value;
        }

        if (is_object($value) && property_exists($value, 'value')) {
            return $value->value;
        }

        return $attributes[$key] ?? null;
    }
}
