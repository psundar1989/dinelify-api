<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'meal_type' => $this->meal_type,
            'food_type' => $this->food_type,
            'food_name' => $this->food_name,
            'status' => $this->status,
        ];
    }
}
