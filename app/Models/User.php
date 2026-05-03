<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const AVATAR_INITIAL = 'initial';
    public const AVATAR_EMOJI = 'emoji';
    public const AVATAR_PHOTO = 'photo';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'avatar_type',
        'avatar_emoji',
        'avatar_image_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function guestProfile(): HasOne
    {
        return $this->hasOne(GuestProfile::class);
    }

    public function seatAssignment(): HasOne
    {
        return $this->hasOne(SeatAssignment::class);
    }

    public function taskAssignments()
    {
        return $this->hasMany(GuestTaskAssignment::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public static function avatarTypeOptions(): array
    {
        return [
            self::AVATAR_INITIAL => 'イニシャル',
            self::AVATAR_EMOJI => '絵文字',
            self::AVATAR_PHOTO => '写真',
        ];
    }

    public static function avatarEmojiOptions(): array
    {
        return [
            '🌸' => 'さくら',
            '✨' => 'きらめき',
            '🌿' => 'リーフ',
            '🕊️' => 'ピース',
            '🎀' => 'リボン',
            '☕' => 'カフェ',
            '🐶' => 'いぬ',
            '🐱' => 'ねこ',
            '🐻' => 'くま',
            '🐰' => 'うさぎ',
            '🦊' => 'きつね',
            '🐼' => 'ぱんだ',
        ];
    }

    public function avatarType(): string
    {
        return $this->avatar_type ?: self::AVATAR_INITIAL;
    }

    public function avatarInitial(): string
    {
        return mb_substr($this->name ?? '?', 0, 1, 'UTF-8') ?: '?';
    }

    public function avatarImageUrl(): ?string
    {
        return $this->avatar_image_path ? asset('storage/' . $this->avatar_image_path) : null;
    }
}
