<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selections' => ['required', 'array', 'min:1', 'max:3'],
            'selections.*.meal_type' => ['required', 'in:breakfast,lunch,dinner', 'distinct'],
            'selections.*.food_type' => ['required', 'in:veg,non_veg,skip'],
        ];
    }
}
