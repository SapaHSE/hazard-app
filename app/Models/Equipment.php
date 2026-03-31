<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'qr_code',
        'location',
        'status',
        'last_checked_at',
        'next_check_at',
    ];

    protected function casts(): array
    {
        return [
            'last_checked_at' => 'date',
            'next_check_at'   => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function qrScans()
    {
        return $this->hasMany(QrScan::class);
    }
}