<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsItem extends Model
{
    protected $fillable = [
        'published_date', 'tag', 'title', 'body',
        'image_path', 'layout', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'published_date' => 'date',
            'is_active'      => 'boolean',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path
            ? asset('storage/' . $this->image_path)
            : null;
    }
}
