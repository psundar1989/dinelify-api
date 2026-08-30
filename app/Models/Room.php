<?php

namespace App\Models;

use App\Models\Concerns\LogsAuditActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['location_id', 'room_number', 'status'])]
class Room extends Model
{
    use HasFactory, LogsAuditActivity;

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
