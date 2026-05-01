@extends('layouts.app')
@section('title', 'アクセス | ' . ($setting?->groom_name_en ?: $setting?->groom_name ?? 'Wedding'))

@push('styles')
<style>
main { padding: 0; text-align: initial; }

/* ── バナー ── */
.ac-banner {
    position: relative; height: 32vh; min-height: 200px;
    overflow: hidden; display: flex; align-items: center; justify-content: center; text-align: center;
}
.ac-banner__img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; filter: brightness(0.38) saturate(0.7); }
.ac-banner__overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(20,12,4,0.1), rgba(20,12,4,0.55)); }
.ac-banner__text { position: relative; z-index: 2; color: #fff; padding: 0 20px; }
.ac-banner__eyebrow { display: block; font-size: 0.6rem; letter-spacing: 5px; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 10px; font-family: 'Noto Sans JP', sans-serif; }
.ac-banner__title { font-family: 'Playfair Display', serif; font-size: clamp(1.6rem, 5vw, 2.6rem); font-weight: 400; letter-spacing: 2px; margin: 0; }

/* ── コンテンツ ── */
.ac-wrap { max-width: 800px; margin: 0 auto; padding: 60px 20px 80px; }
.ac-section-en { display: block; font-size: 0.65rem; letter-spacing: 5px; text-transform: uppercase; color: #b38b59; margin-bottom: 6px; font-family: 'Noto Sans JP', sans-serif; }
.ac-section-ja { font-family: 'Playfair Display', serif; font-size: 1.7rem; font-weight: 400; color: #3d2f25; margin: 0 0 14px; }
.ac-rule { width: 40px; height: 1px; background: #b38b59; margin: 0 auto 40px; }

/* ── 会場情報 ── */
.ac-venue {
    background: #fff;
    border-radius: 14px;
    padding: 28px 32px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.07);
    margin-bottom: 32px;
    display: flex;
    gap: 24px;
    align-items: flex-start;
    flex-wrap: wrap;
}
.ac-venue__info { flex: 1; min-width: 200px; }
.ac-venue__name { font-family: 'Playfair Display', serif; font-size: 1.3rem; color: #3d2f25; margin: 0 0 6px; }
.ac-venue__address { font-size: 0.88rem; color: #7a6a5a; margin: 0 0 16px; line-height: 1.7; }
.ac-venue__station { font-size: 0.82rem; color: #9b8573; margin: 0 0 16px; }
.ac-venue__station i { color: #b38b59; margin-right: 5px; }
.ac-map-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 10px 22px;
    background: #b38b59;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.88rem;
    font-family: 'Noto Sans JP', sans-serif;
    font-weight: 500;
    transition: background 0.2s;
}
.ac-map-btn:hover { background: #9a7447; color: #fff; }
.ac-map-btn i { font-size: 1rem; }

/* ── 地図埋め込み ── */
.ac-map-embed {
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 32px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.1);
    background: #f0ebe3;
    min-height: 320px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ac-map-embed iframe { width: 100%; height: 400px; display: block; border: none; }
.ac-map-placeholder { text-align: center; padding: 60px 20px; color: #c0b0a0; }
.ac-map-placeholder i { font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 12px; }
.ac-map-placeholder p { font-size: 0.85rem; }

/* ── アクセス情報カード ── */
.ac-info-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    margin-bottom: 32px;
}
.ac-info-card {
    background: #fff;
    border-radius: 12px;
    padding: 22px 24px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.06);
    border-left: 4px solid #b38b59;
}
.ac-info-card__head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}
.ac-info-card__icon {
    width: 36px; height: 36px; border-radius: 50%;
    background: #fef9f0; color: #b38b59;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.95rem; flex-shrink: 0;
}
.ac-info-card__title { font-size: 0.82rem; font-weight: 700; color: #b38b59; letter-spacing: 1px; text-transform: uppercase; }
.ac-info-card__body { font-size: 0.9rem; color: #6b5b4e; line-height: 1.8; white-space: pre-line; }

@media (min-width: 600px) {
    .ac-info-grid { grid-template-columns: 1fr 1fr; }
}
@media (min-width: 768px) {
    .ac-wrap { padding: 80px 24px 100px; }
    .ac-map-embed iframe { height: 450px; }
}
</style>
@endpush

@section('content')
<section class="ac-banner">
    <img src="{{ asset('img/チャペル.jpg') }}" alt="" class="ac-banner__img">
    <div class="ac-banner__overlay"></div>
    <div class="ac-banner__text">
        <span class="ac-banner__eyebrow">Access · アクセス</span>
        <h1 class="ac-banner__title">会場へのアクセス</h1>
    </div>
</section>

<div class="ac-wrap">
    <div style="text-align:center;margin-bottom:40px;">
        <span class="ac-section-en">Access</span>
        <h2 class="ac-section-ja">アクセス</h2>
        <div class="ac-rule"></div>
    </div>

    {{-- 会場情報 --}}
    <div class="ac-venue">
        <div class="ac-venue__info">
            <p class="ac-venue__name">{{ $setting?->venue_name ?? '会場名未設定' }}</p>
            <p class="ac-venue__address">{{ $setting?->venue_address ?? '' }}</p>
            @if ($setting?->venue_nearest_station)
            <p class="ac-venue__station">
                <i class="fa-solid fa-train"></i>{{ $setting->venue_nearest_station }}
            </p>
            @endif
            @if ($setting?->venue_url)
            <a href="{{ $setting->venue_url }}" target="_blank" rel="noopener" class="ac-map-btn">
                <i class="fa-brands fa-google"></i> Google Maps で開く
            </a>
            @endif
        </div>
    </div>

    {{-- 地図 --}}
    <div class="ac-map-embed">
        @if ($setting?->venue_map_embed)
            {!! $setting->venue_map_embed !!}
        @elseif ($setting?->venue_url)
            {{-- URLからembed URLへ変換（共有URLのみ対応） --}}
            <div class="ac-map-placeholder">
                <i class="fa-solid fa-map-location-dot"></i>
                <p>管理画面の「式の情報設定」→「地図埋め込みコード」を入力すると地図が表示されます。</p>
                <a href="{{ $setting->venue_url }}" target="_blank" rel="noopener" class="ac-map-btn" style="margin-top:16px;">
                    <i class="fa-brands fa-google"></i> Google Maps で確認
                </a>
            </div>
        @else
            <div class="ac-map-placeholder">
                <i class="fa-solid fa-map-location-dot"></i>
                <p>地図情報は設定中です</p>
            </div>
        @endif
    </div>

    {{-- アクセス方法カード --}}
    @if ($setting?->access_train || $setting?->access_car || $setting?->access_parking)
    <div class="ac-info-grid">
        @if ($setting?->access_train)
        <div class="ac-info-card">
            <div class="ac-info-card__head">
                <div class="ac-info-card__icon"><i class="fa-solid fa-train"></i></div>
                <span class="ac-info-card__title">電車でお越しの方</span>
            </div>
            <p class="ac-info-card__body">{{ $setting->access_train }}</p>
        </div>
        @endif
        @if ($setting?->access_car)
        <div class="ac-info-card">
            <div class="ac-info-card__head">
                <div class="ac-info-card__icon"><i class="fa-solid fa-car"></i></div>
                <span class="ac-info-card__title">お車でお越しの方</span>
            </div>
            <p class="ac-info-card__body">{{ $setting->access_car }}</p>
        </div>
        @endif
        @if ($setting?->access_parking)
        <div class="ac-info-card" style="grid-column: 1 / -1;">
            <div class="ac-info-card__head">
                <div class="ac-info-card__icon"><i class="fa-solid fa-square-parking"></i></div>
                <span class="ac-info-card__title">駐車場</span>
            </div>
            <p class="ac-info-card__body">{{ $setting->access_parking }}</p>
        </div>
        @endif
    </div>
    @endif

</div>
@endsection
