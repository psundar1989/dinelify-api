<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\LogsAuditActivity;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * No password — customers authenticate via email OTP + Sanctum tokens only.
 * Mobile number is optional profile data and is not used for OTP delivery
 * or verification.
 * Still implements Authenticatable and Authorizable so the Sanctum guard,
 * policy checks (Gate::authorize), and test helpers (actingAs) work
 * correctly with this model — spatie/laravel-permission's global
 * Gate::before hook type-hints Authorizable for every authorization check
 * app-wide, including ones unrelated to admin roles/permissions.
 */
#[Fillable(['name', 'mobile', 'email', 'email_verified_at', 'location_id', 'room_id', 'status'])]
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasApiTokens, HasFactory, LogsAuditActivity, Notifiable;

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'email_verified_at' => 'datetime',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }
}
