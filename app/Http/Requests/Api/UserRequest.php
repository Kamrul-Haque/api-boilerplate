<?php

namespace App\Http\Requests\Api;

use App\Rules\AlphaSpace;
use App\Rules\Phone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $dynamicRule = $this->user ? 'nullable' : 'required';

        return [
            'name' => ['required', new AlphaSpace, 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->user)],
            'phone' => ['nullable', new Phone, 'max:255'],
            'avatar' => ['nullable', 'image', 'max:5120'],
            'roles' => [$dynamicRule, 'array'],
            'roles.*' => [$dynamicRule, 'integer', 'exists:roles,id'],
        ];
    }
}
