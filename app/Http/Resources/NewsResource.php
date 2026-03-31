<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'content'      => $this->content,
            'category'     => $this->category,
            'author'       => $this->author,
            'image_url'    => $this->image_url,  // sudah full URL dari Unsplash / storage
            'is_published' => $this->is_published,
            'published_at' => $this->published_at?->toDateString(),    // "2026-03-26"
            'date'         => $this->published_at?->translatedFormat('d F Y'), // "26 Maret 2026"
            'created_at'   => $this->created_at?->toDateTimeString(),
        ];
    }
}