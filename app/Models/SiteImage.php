<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class SiteImage extends Model
{
    protected $fillable = ['location', 'image_path', 'caption', 'display_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    const LOCATIONS = [
        'hero'              => 'ヒーロー（ホーム）',
        'login_bg'          => 'ログイン背景',
        'banner_invitation' => '招待状バナー',
        'banner_program'    => 'プログラムバナー',
        'banner_access'     => 'アクセスバナー',
        'banner_faq'        => 'FAQバナー',
        'banner_gallery'    => 'ギャラリーバナー',
        'banner_guestbook'  => 'ゲストブックバナー',
        'banner_news'       => 'お知らせバナー',
        'banner_profile'    => 'プロフィールバナー',
    ];

    public function scopeForLocation($query, string $location)
    {
        return $query->where('location', $location);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }

    /** バナー用：表示モードに従い1枚返す（null = 画像なし） */
    public static function forDisplay(string $location): ?self
    {
        $setting = WeddingSetting::first();
        $modes   = $setting?->image_display_modes ?? [];
        $mode    = $modes[$location] ?? 'random';

        $query = static::forLocation($location)->active()->ordered();

        return $mode === 'random'
            ? $query->inRandomOrder()->first()
            : $query->first();
    }

    /** ヒーロー用：全アクティブ画像を返す */
    public static function forHero(): Collection
    {
        return static::forLocation('hero')->active()->ordered()->get();
    }
}
