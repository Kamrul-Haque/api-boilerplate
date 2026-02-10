<?php

namespace App\Http\Requests\Api;

use App\Models\Module;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModuleRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'route_prefix' => ['required', 'alpha_dash', 'max:255', Rule::unique('modules')->ignore($this->module)],
            'priority' => ['required', 'integer'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'priority' => $this->priority ?? (Module::max('priority') + 1),
        ]);
    }
}
