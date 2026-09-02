<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

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
            'mobile' => ['required', 'string', 'regex:/^[0-9]{7,15}$/', 'unique:users,mobile'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
