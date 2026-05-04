<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestbookMessage extends Model
{
    protected $fillable = ['user_id', 'message', 'sticker', 'is_public'];

    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
