<?php

namespace App\Models;

use App\Mail\EmailVerificationMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
        'password_change_required',
        'password_changed_at',
        'role',
        'avatar_type',
        'avatar_emoji',
        'avatar_image_path',
        'avatar_bg_color',
        'avatar_border_color',
        'avatar_border_width',
        'email_verification_token',
        'email_verification_sent_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'          => 'datetime',
            'email_verification_sent_at' => 'datetime',
            'password_change_required'   => 'boolean',
            'password_changed_at'        => 'datetime',
            'password'                   => 'hashed',
        ];
    }

    public function hasVerifiedEmail(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return !blank($this->email) && !is_null($this->email_verified_at);
    }

    public function isEmailUnverified(): bool
    {
        if ($this->isAdmin()) {
            return false;
        }

        return !blank($this->email) && is_null($this->email_verified_at);
    }

    /** トークンを生成してメールを送信する。60分以内の再送は拒否。 */
    public function sendEmailVerification(bool $force = false): bool
    {
        if ($this->isAdmin() || blank($this->email)) {
            return false;
        }

        // 60分以内に送信済みなら再送しない（force=true で強制送信）
        if (!$force && $this->email_verification_sent_at
            && $this->email_verification_sent_at->diffInMinutes(now()) < 60) {
            return false;
        }

        $token = Str::random(64);

        $this->update([
            'email_verified_at'          => null,
            'email_verification_token'   => hash('sha256', $token),
            'email_verification_sent_at' => now(),
        ]);

        Mail::to($this->email)->send(new EmailVerificationMail($this, $token));

        return true;
    }

    /** トークン照合して認証済みにする */
    public static function verifyEmailToken(string $token): ?self
    {
        $hashed = hash('sha256', $token);

        $user = static::where('email_verification_token', $hashed)->first();

        if (!$user) {
            return null;
        }

        // 24時間以内のトークンのみ有効
        if ($user->email_verification_sent_at
            && $user->email_verification_sent_at->addHours(24)->isPast()) {
            return null;
        }

        $user->update([
            'email_verified_at'          => now(),
            'email_verification_token'   => null,
            'email_verification_sent_at' => null,
        ]);

        return $user;
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

    public function avatarBorderColor(): string
    {
        if (is_string($this->avatar_border_color) && preg_match('/^#[0-9A-Fa-f]{6}$/', $this->avatar_border_color)) {
            return $this->avatar_border_color;
        }

        return '#f0e4d0';
    }

    public function avatarBorderWidth(): int
    {
        if (is_numeric($this->avatar_border_width)) {
            return max(0, min(10, (int) $this->avatar_border_width));
        }

        return 3;
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

    public static function avatarBorderColorOptions(): array
    {
        return [
            '#f0e4d0' => 'やわらかい金',
            '#b38b59' => 'ゴールド',
            '#d9c2a1' => 'サンド',
            '#9b8573' => 'トープ',
            '#3d2f25' => 'ダークブラウン',
            '#e8b8c8' => 'ローズ',
            '#b8c8e8' => 'ブルー',
            '#a8d8b9' => 'グリーン',
            '#cfc7f5' => 'ラベンダー',
            '#ffffff' => 'ホワイト',
        ];
    }
}
