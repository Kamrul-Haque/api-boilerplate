<?php

namespace App\Http\Requests\Api;

use App\Enums\VerificationCodeIdentifierKey;
use App\Enums\VerificationCodePurpose;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerificationCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $isTwoFactor = $this->input('purpose') === VerificationCodePurpose::TWO_FACTOR->value;

        $rules = [
            'purpose' => ['required', 'string', Rule::in(VerificationCodePurpose::values())],
            'verification_token' => [
                Rule::requiredIf($isTwoFactor),
                'nullable',
                'uuid',
            ],
            'identifier_key' => [
                Rule::requiredIf(! $isTwoFactor),
                'nullable',
                'string',
                Rule::in(VerificationCodeIdentifierKey::values()),
            ],
            'identifier_value' => [
                Rule::requiredIf(! $isTwoFactor),
                'nullable',
                'string',
            ],
        ];

        if (! $isTwoFactor) {
            $rules['identifier_value'][] = Rule::exists('users', (string) $this->input('identifier_key'));
        }

        return $rules;
    }
}
