<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
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
            'module_id' => ['required', 'integer', 'exists:modules,id'],
            'display_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'alpha_dash', 'max:255', Rule::unique('permissions')->ignore($this->permission)],
        ];
    }
}
