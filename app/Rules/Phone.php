<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class Phone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^[+]?[\d\s().\/-]+(?:\s*(?:ext\.?|x)\s*\d+)?$/i', $value)) {
            $fail(trans('validation.phone', ['attribute' => trans("validation.attributes.{$attribute}")]));
        }
    }
}
