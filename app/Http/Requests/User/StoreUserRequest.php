<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', Rule::in(['admin', 'manager', 'member'])],
            'organization_ids' => ['nullable', 'array'],
            'organization_ids.*' => ['integer', 'exists:organizations,id'],
            'organization_role' => ['nullable', Rule::in(['admin', 'manager', 'member'])],
        ];
    }
}
