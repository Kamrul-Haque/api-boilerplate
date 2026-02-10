<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AlphaNumericSpace implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^[\pL\pM\pN\s.]+$/u', $value)) {
            $fail(trans('validation.alpha_numeric_space', ['attribute' => "validation.attributes.{$attribute}"]));
        }
    }
}
