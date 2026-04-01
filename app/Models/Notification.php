<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type',
        'is_read',
        'notifiable_id',
        'notifiable_type',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    // Relasi ke user penerima
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Polymorphic — bisa link ke Report, News, dll
    public function notifiable()
    {
        return $this->morphTo();
    }

    // Scope: belum dibaca
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}