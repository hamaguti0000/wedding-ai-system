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
        'avatar_bg_color',
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
        $options = [];

        foreach (self::avatarEmojiGroups() as $group) {
            foreach ($group['items'] as $emoji => $label) {
                $options[$emoji] = $label;
            }
        }

        return $options;
    }

    public static function avatarEmojiGroups(): array
    {
        return [
            [
                'key' => 'nature',
                'label' => '花・自然',
                'items' => [
                    '🌸' => 'さくら',
                    '🌷' => 'チューリップ',
                    '🌹' => 'ローズ',
                    '🌻' => 'ひまわり',
                    '🌿' => 'リーフ',
                    '🍃' => 'そよかぜ',
                    '☘️' => 'クローバー',
                    '🍀' => 'しあわせ',
                    '🌙' => 'ムーン',
                    '⭐' => 'スター',
                    '✨' => 'きらめき',
                ],
            ],
            [
                'key' => 'animals',
                'label' => 'どうぶつ',
                'items' => [
                    '🐶' => 'いぬ',
                    '🐱' => 'ねこ',
                    '🐻' => 'くま',
                    '🐰' => 'うさぎ',
                    '🦊' => 'きつね',
                    '🐼' => 'ぱんだ',
                    '🦁' => 'らいおん',
                    '🐯' => 'とら',
                    '🦄' => 'ユニコーン',
                    '🐧' => 'ぺんぎん',
                    '🐠' => 'さかな',
                ],
            ],
            [
                'key' => 'food',
                'label' => '食べ物',
                'items' => [
                    '☕' => 'カフェ',
                    '🍰' => 'ケーキ',
                    '🍓' => 'いちご',
                    '🍊' => 'みかん',
                    '🍋' => 'レモン',
                    '🍒' => 'チェリー',
                    '🍩' => 'ドーナツ',
                    '🍯' => 'はちみつ',
                    '🍞' => 'パン',
                    '🍵' => 'おちゃ',
                    '🍨' => 'アイス',
                ],
            ],
            [
                'key' => 'symbols',
                'label' => 'しるし',
                'items' => [
                    '🎀' => 'リボン',
                    '🎈' => 'バルーン',
                    '🎁' => 'ギフト',
                    '🕊️' => 'ピース',
                    '💐' => 'ブーケ',
                    '💎' => 'ジュエル',
                    '💫' => 'スパーク',
                    '💖' => 'ハート',
                    '🫶' => 'ハート手',
                    '🎶' => 'ミュージック',
                    '🪩' => 'ディスコ',
                ],
            ],
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

    public function avatarBackgroundColor(): string
    {
        if ($this->avatarType() === self::AVATAR_EMOJI) {
            if (is_string($this->avatar_bg_color) && preg_match('/^#[0-9A-Fa-f]{6}$/', $this->avatar_bg_color)) {
                return $this->avatar_bg_color;
            }

            return '#ffffff';
        }

        return 'linear-gradient(135deg, #b38b59 0%, #d4a870 100%)';
    }

    public static function avatarColorOptions(): array
    {
        return [
            '#ffffff' => '白',
            '#fff7ed' => 'クリーム',
            '#fef3c7' => 'やわらかい黄',
            '#fde68a' => 'アンバー',
            '#fecaca' => 'ローズ',
            '#e9d5ff' => 'ラベンダー',
            '#dbeafe' => 'ライトブルー',
            '#d1fae5' => 'ミント',
            '#e5e7eb' => 'グレー',
            '#fca5a5' => 'サーモン',
        ];
    }
}
