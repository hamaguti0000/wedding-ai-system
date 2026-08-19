<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GalleryPhoto extends Model
{
    protected $fillable = [
        'file_path',
        'display_file_path',
        'caption',
        'gallery_category',
        'photo_source',
        'sort_order',
        'is_active',
        'uploaded_by_user_id',
        'status',
        'is_guest_upload',
        'file_hash',
        'phash',
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
        return asset('storage/' . ($this->display_file_path ?: $this->file_path));
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function taggedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'gallery_photo_taggings');
    }

    public function taggedGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            GuestGroup::class,
            'gallery_photo_group_taggings',
            'gallery_photo_id',
            'guest_group_id',
            'id',
            'id'
        );
    }


    public static function categoryOptions(): array
    {
        return [
            'ceremony' => '挙式',
            'reception' => '披露宴',
            'other' => 'その他',
        ];
    }

    public static function sourceOptions(): array
    {
        return [
            'photographer' => 'カメラマン撮影',
            'admin' => '管理者アップロード',
            'guest' => 'ゲスト投稿',
        ];
    }

    public function categoryLabel(): string
    {
        return self::categoryOptions()[$this->gallery_category ?: 'other'] ?? 'その他';
    }

    public function sourceLabel(): string
    {
        if ($this->is_guest_upload) {
            return 'ゲスト投稿';
        }

        return self::sourceOptions()[$this->photo_source ?: 'admin'] ?? '管理者アップロード';
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
