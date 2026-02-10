<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NumDash implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^[\pM\pN-]+$/u', $value)) {
            $fail('The :attribute may contain only numbers and dashes.');
        }
    }
}
