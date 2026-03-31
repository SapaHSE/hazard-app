<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'category',
        'author',
        'image_url',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    // ── Scope: hanya tampilkan yang sudah published ──────────────────────────
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    // ── Relasi ───────────────────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}