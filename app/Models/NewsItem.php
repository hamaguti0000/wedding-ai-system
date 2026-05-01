<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsItem extends Model
{
    protected $fillable = ['published_date', 'tag', 'body', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'published_date' => 'date',
            'is_active'      => 'boolean',
        ];
    }
}
