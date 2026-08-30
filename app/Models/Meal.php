<?php

namespace App\Models;

use App\Models\Concerns\LogsAuditActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['menu_id', 'meal_type', 'food_type', 'food_name', 'status'])]
class Meal extends Model
{
    use HasFactory, LogsAuditActivity;

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
