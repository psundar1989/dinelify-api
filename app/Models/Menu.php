<?php

namespace App\Models;

use App\Models\Concerns\LogsAuditActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['menu_date', 'status'])]
class Menu extends Model
{
    use HasFactory, LogsAuditActivity;

    protected function casts(): array
    {
        return [
            'menu_date' => 'date',
        ];
    }

    public function meals(): HasMany
    {
        return $this->hasMany(Meal::class);
    }
}
