<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'regex:/^[0-9]{7,15}$/', Rule::unique('users', 'mobile')->ignore($this->route('user'))],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
