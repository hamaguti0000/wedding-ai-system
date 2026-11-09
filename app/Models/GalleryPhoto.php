<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Crypt;

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

    public function publicReferenceToken(): string
    {
        $encrypted = Crypt::encryptString((string) $this->id);

        return rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');
    }

    public static function fromPublicReferenceToken(string $token): ?self
    {
        try {
            $encrypted = base64_decode(strtr($token, '-_', '+/'), true);

            if ($encrypted === false) {
                return null;
            }

            $id = (int) Crypt::decryptString($encrypted);

            return static::find($id);
        } catch (\Throwable) {
            return null;
        }
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
            'venue' => '会場',
            'flowers' => '花',
            'scenery' => '風景',
            'food' => '料理',
            'items' => '小物',
            'other' => 'その他',
        ];
    }

    public static function noPeopleTagCategoryKeys(): array
    {
        return ['venue', 'flowers', 'scenery', 'food', 'items'];
    }

    public function needsPeopleTag(): bool
    {
        return ! in_array($this->gallery_category ?: 'other', self::noPeopleTagCategoryKeys(), true);
    }

    public function isUntaggedForManagement(): bool
    {
        return $this->needsPeopleTag()
            && $this->taggedUsers->isEmpty()
            && $this->taggedGroups->isEmpty();
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
