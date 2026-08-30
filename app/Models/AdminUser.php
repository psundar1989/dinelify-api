<?php

namespace App\Models;

use App\Models\Concerns\LogsAuditActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'status'])]
#[Hidden(['password', 'remember_token'])]
class AdminUser extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, LogsAuditActivity, Notifiable;

    protected string $guard_name = 'admin';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'location_admin_user');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function isTeamLead(): bool
    {
        return $this->hasRole('team-lead');
    }
}
