<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AlphaSpace implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^[\pL\s.]+$/u', $value)) {
            $fail(trans('validation.alpha_space', ['attribute' => trans("validation.attributes.{$attribute}")]));
        }
    }
}
