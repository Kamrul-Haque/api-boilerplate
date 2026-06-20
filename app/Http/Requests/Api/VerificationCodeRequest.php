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
        return [
            'purpose' => ['required', 'string', Rule::in(VerificationCodePurpose::values())],
            'identifier_key' => ['required', 'string', Rule::in(VerificationCodeIdentifierKey::values())],
            'identifier_value' => ['required', 'string', Rule::exists('users', $this->identifier_key)],
        ];
    }
}
