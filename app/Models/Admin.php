<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;
    protected $guard_name = 'admin';
    protected $fillable = [
        'name',
        'email',
        'tempat_lahir',
        'tgl_lahir',
        'no_telp',
        'gender',
        'foto',
        'password',
        'email_verified_at',
        'is_active',
    ];
        protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tgl_lahir' => 'date',
            'is_active' => 'boolean',
        ];
    }
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->hasAnyRole(['super-admin', 'admin', 'staff']);
    }
    public function getFotoUrlAttribute(): ?string
    {
    return $this->foto
        ? asset('storage/' . ltrim($this->foto, '/'))
        : null;
    }
}
