<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileBookPage extends Model
{
    protected $fillable = [
        'page_number',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'page_number' => 'integer',
        ];
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }
}
