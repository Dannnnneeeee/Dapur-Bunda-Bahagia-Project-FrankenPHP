<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Order;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;
    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'tempat_lahir',
        'tgl_lahir',
        'no_telp',
        'gender',
        'foto',
        'alamat',
        'kota',
        'provinsi',
        'kode_pos',
        'status',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'tgl_lahir' => 'date',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
     public function getUmurAttribute()
    {
        return $this->tgl_lahir ? $this->tgl_lahir->age : null;
    }

    /**
     * Accessor untuk foto URL
     */
    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return asset('storage/' . $this->foto);
        }

        // Default avatar berdasarkan gender
        return $this->gender === 'Perempuan'
            ? asset('images/avatar-female.png')
            : asset('images/avatar-male.png');
    }

    /**
     * Scope untuk filter user active
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk filter user inactive
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope untuk filter user suspended
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
