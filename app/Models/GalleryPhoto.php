<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GalleryPhoto extends Model
{
    protected $fillable = [
        'file_path',
        'caption',
        'sort_order',
        'is_active',
        'uploaded_by_user_id',
        'status',
        'is_guest_upload',
    ];

    protected function casts(): array
    {
        return [
            'is_active'        => 'boolean',
            'is_guest_upload'  => 'boolean',
        ];
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function taggedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'gallery_photo_taggings');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'approved' => '公開中',
            'pending'  => '承認待ち',
            'rejected' => '却下',
            default    => '—',
        };
    }
}
