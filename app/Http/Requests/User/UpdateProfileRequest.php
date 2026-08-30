<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'location_id' => ['sometimes', 'required', 'integer', 'exists:locations,id'],
            'room_id' => ['sometimes', 'required', 'integer', 'exists:rooms,id'],
        ];
    }
}
