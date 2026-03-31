<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'department',
        'position',
        'avatar_url',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Relasi ──────────────────────────────────────────────────────────────
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }

    public function qrScans()
    {
        return $this->hasMany(QrScan::class);
    }
}