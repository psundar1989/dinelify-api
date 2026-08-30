<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'regex:/^[0-9]{7,15}$/', 'unique:users,mobile'],
            'email' => ['nullable', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.unique' => 'This mobile number is already registered.',
            'email.unique' => 'This email address is already registered.',
        ];
    }
}
