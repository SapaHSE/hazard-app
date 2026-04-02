<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasUuids;

    protected $fillable = [
        'nik',
        'employee_id',
        'full_name',
        'email',
        'phone_number',
        'position',
        'department',
        'password_hash',
        'profile_photo',
        'is_active',
        'role',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    // Map Laravel Auth ke kolom password_hash
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'user_id');
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class, 'user_id');
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    public function news()
    {
        return $this->hasMany(News::class, 'created_by');
    }

    public function readStatuses()
    {
        return $this->hasMany(ReadStatus::class, 'user_id');
    }
}