<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
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
            'display_name' => ['required', 'string', 'max:255'],
            'name' => ['required', Rule::unique('roles')->ignore($this->role)],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['required', 'integer', 'exists:permissions,id'],
            'is_reserved' => ['required', 'boolean'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'name' => Str::slug($this->display_name, '-', 'ja'),
            'is_reserved' => filter_var($this->is_reserved, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
        ]);
    }
}
