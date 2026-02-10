<?php

namespace App\Http\Requests\Api;

use App\Rules\AlphaSpace;
use App\Rules\Phone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'name' => ['required', new AlphaSpace, 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', new Phone, 'max:255'],
            'avatar' => ['nullable', 'image', 'max:5120'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }
}
