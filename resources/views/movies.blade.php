@extends('layouts.app')
@section('title', 'ムービー | Wedding')

@push('styles')
<style>
main { padding: 0; text-align: initial; }

.gl-banner {
    position: relative; height: 32vh; min-height: 200px;
    overflow: hidden; display: flex; align-items: center; justify-content: center; text-align: center;
    padding-top: 60px; box-sizing: border-box;
}
.gl-banner__img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; filter: brightness(0.38) saturate(0.7); }
.gl-banner__overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(20,12,4,0.1), rgba(20,12,4,0.55)); }
.gl-banner__text { position: relative; z-index: 2; color: #fff; padding: 0 20px; }
.gl-banner__eyebrow { display: block; font-size: 0.6rem; letter-spacing: 5px; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 10px; font-family: 'Noto Sans JP', sans-serif; }
.gl-banner__title { font-family: 'Playfair Display', serif; font-size: clamp(1.6rem, 5vw, 2.6rem); font-weight: 400; letter-spacing: 2px; margin: 0; }

.gl-wrap { max-width: 900px; margin: 0 auto; padding: 60px 20px 80px; }
.gl-intro { text-align: center; margin-bottom: 40px; }
.gl-section-en { display: block; font-size: 0.65rem; letter-spacing: 5px; text-transform: uppercase; color: #b38b59; margin-bottom: 6px; font-family: 'Noto Sans JP', sans-serif; }
.gl-section-ja { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 400; color: #3d2f25; margin: 0 0 10px; }
.gl-rule { width: 40px; height: 1px; background: #b38b59; margin: 0 auto; }

.mv-section { margin-bottom: 56px; }
.mv-section:last-child { margin-bottom: 0; }
.mv-heading { display: flex; align-items: baseline; gap: 10px; margin-bottom: 14px; }
.mv-heading__ja { font-family: 'Playfair Display', serif; font-size: 1.15rem; color: #3d2f25; }
.mv-heading__en { font-size: 0.65rem; letter-spacing: 3px; text-transform: uppercase; color: #b38b59; }

.mv-player-wrap {
    background: #000; border-radius: 14px; overflow: hidden;
    box-shadow: 0 12px 36px rgba(0,0,0,0.18);
}
.mv-player { width: 100%; display: block; aspect-ratio: 16 / 9; background: #000; }

.mv-download {
    display: inline-flex; align-items: center; gap: 6px; margin-top: 12px;
    font-size: 0.82rem; color: #b38b59; text-decoration: none;
}
.mv-download:hover { text-decoration: underline; }

.mv-empty {
    text-align: center; padding: 40px 20px; color: #c0b0a0;
    background: #fff; border: 1px dashed #e8d5b7; border-radius: 14px;
}
.mv-empty i { font-size: 1.8rem; opacity: 0.4; display: block; margin-bottom: 8px; }

@media (min-width: 768px) {
    .gl-banner { padding-top: 80px; }
    .gl-wrap { padding: 80px 24px 100px; }
}
</style>
@endpush

@section('content')
<section class="gl-banner">
    <img src="{{ ($bannerImage?->url ?? asset('img/チャペル.jpg')) }}" alt="" class="gl-banner__img">
    <div class="gl-banner__overlay"></div>
    <div class="gl-banner__text">
        <span class="gl-banner__eyebrow">Movies · ムービー</span>
        <h1 class="gl-banner__title">ムービー</h1>
    </div>
</section>

<div class="gl-wrap">
    <div class="gl-intro">
        <span class="gl-section-en">Movie</span>
        <h2 class="gl-section-ja">もう一度、あの日を</h2>
        <div class="gl-rule"></div>
    </div>

    @php
        $movies = [
            'opening' => ['label' => 'オープニングムービー', 'en' => 'Opening'],
            'profile' => ['label' => 'プロフィールムービー', 'en' => 'Profile'],
            'ending'  => ['label' => 'エンディングムービー', 'en' => 'Ending'],
        ];
    @endphp

    @foreach ($movies as $type => $meta)
    @php $path = $setting?->{"{$type}_movie_path"}; @endphp
    <div class="mv-section">
        <div class="mv-heading">
            <span class="mv-heading__ja">{{ $meta['label'] }}</span>
            <span class="mv-heading__en">{{ $meta['en'] }}</span>
        </div>

        @if ($path)
        <div class="mv-player-wrap">
            <video class="mv-player" controls playsinline preload="metadata">
                <source src="{{ asset('storage/' . $path) }}" type="video/mp4">
            </video>
        </div>
        <a class="mv-download" href="{{ asset('storage/' . $path) }}" download>
            <i class="fa-solid fa-download"></i> 動画をダウンロード
        </a>
        @else
        <div class="mv-empty">
            <i class="fa-regular fa-circle-play"></i>
            <p>準備中です</p>
        </div>
        @endif
    </div>
    @endforeach
</div>
@endsection
