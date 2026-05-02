@extends('layouts.app')
@php
    $isGroom  = $person === 'groom';
    $nameEn   = $isGroom
        ? ($setting?->groom_name_en ?: $setting?->groom_name ?? 'Groom')
        : ($setting?->bride_name_en ?: $setting?->bride_name ?? 'Bride');
    $nameJa   = $isGroom ? ($setting?->groom_name ?? '') : ($setting?->bride_name ?? '');
    $roleEn   = $isGroom ? 'Groom' : 'Bride';
    $roleJa   = $isGroom ? '新郎' : '新婦';
    $photo    = $isGroom ? $setting?->groom_photo : $setting?->bride_photo;
    $bio      = $isGroom ? $setting?->groom_bio   : $setting?->bride_bio;
    $photoUrl = $photo ? asset('storage/' . $photo) : asset('img/チャペル.jpg');
    $otherPerson = $isGroom ? 'bride' : 'groom';
@endphp
@section('title', $nameEn . ' | プロフィール')

@push('styles')
<style>
main { padding: 0; text-align: initial; }

/* ── ヒーローバナー ── */
.pfshow-hero {
    position: relative;
    height: 52vh;
    min-height: 300px;
    overflow: hidden;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding-top: 60px;
    box-sizing: border-box;
}
.pfshow-hero__img {
    position: absolute; inset: 0;
    width: 100%; height: 100%;
    object-fit: cover;
    filter: brightness(0.45) saturate(0.8);
    transition: transform 8s ease;
    transform: scale(1.04);
}
.pfshow-hero:hover .pfshow-hero__img { transform: scale(1.0); }
.pfshow-hero__overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to bottom, rgba(20,12,4,0.1) 0%, rgba(20,12,4,0.65) 100%);
}
.pfshow-hero__text {
    position: relative; z-index: 2;
    text-align: center; color: #fff;
    padding: 0 20px 44px;
    width: 100%;
}
.pfshow-hero__role {
    display: block;
    font-size: 0.62rem;
    letter-spacing: 5px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.6);
    margin-bottom: 10px;
    font-family: 'Noto Sans JP', sans-serif;
}
.pfshow-hero__name {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 6vw, 3.2rem);
    font-weight: 400;
    letter-spacing: 3px;
    margin: 0 0 6px;
    line-height: 1.1;
}
.pfshow-hero__name-ja {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.65);
    letter-spacing: 2px;
    font-family: 'Noto Sans JP', sans-serif;
    font-weight: 300;
}

/* ── コンテンツ ── */
.pfshow-wrap { max-width: 820px; margin: 0 auto; padding: 56px 20px 80px; }

.pfshow-body {
    display: grid;
    grid-template-columns: 240px 1fr;
    gap: 48px;
    align-items: flex-start;
}

/* 写真 */
.pfshow-photo-col {}
.pfshow-photo {
    width: 100%;
    aspect-ratio: 3/4;
    object-fit: cover;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
    display: block;
}
.pfshow-photo-placeholder {
    width: 100%;
    aspect-ratio: 3/4;
    border-radius: 12px;
    background: #f5efe8;
    border: 2px dashed #e8d5b7;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4b896;
    font-size: 4rem;
}

/* プロフィール情報 */
.pfshow-info {}
.pfshow-section-en {
    display: block;
    font-size: 0.62rem;
    letter-spacing: 5px;
    text-transform: uppercase;
    color: #b38b59;
    margin-bottom: 6px;
    font-family: 'Noto Sans JP', sans-serif;
}
.pfshow-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.9rem;
    font-weight: 400;
    color: #3d2f25;
    letter-spacing: 2px;
    margin: 0 0 4px;
}
.pfshow-name-ja {
    font-size: 0.9rem;
    color: #9b8573;
    margin: 0 0 20px;
}
.pfshow-rule { width: 36px; height: 1px; background: #b38b59; margin: 0 0 24px; }

.pfshow-bio {
    font-size: 1rem;
    line-height: 2.2;
    color: #6b5b4e;
    font-weight: 300;
    white-space: pre-line;
}
.pfshow-bio-empty {
    font-size: 0.9rem;
    color: #c0b0a0;
    font-style: italic;
}

/* ナビゲーション */
.pfshow-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 48px;
    padding-top: 32px;
    border-top: 1px solid #f0ebe3;
    flex-wrap: wrap;
    gap: 12px;
}
.pfshow-nav__back,
.pfshow-nav__other {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    border-radius: 30px;
    font-size: 0.82rem;
    font-family: 'Noto Sans JP', sans-serif;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.2s, color 0.2s;
    letter-spacing: 0.5px;
}
.pfshow-nav__back {
    background: transparent;
    border: 1px solid #e0d0bc;
    color: #9b8573;
}
.pfshow-nav__back:hover { background: #fef9f0; border-color: #b38b59; color: #b38b59; }
.pfshow-nav__other {
    background: #b38b59;
    color: #fff;
}
.pfshow-nav__other:hover { background: #9a7447; color: #fff; }

@media (max-width: 640px) {
    .pfshow-body { grid-template-columns: 1fr; gap: 28px; }
    .pfshow-photo { max-width: 220px; margin: 0 auto; }
    .pfshow-photo-placeholder { max-width: 220px; margin: 0 auto; }
    .pfshow-info { text-align: center; }
    .pfshow-rule { margin: 0 auto 24px; }
    .pfshow-wrap { padding: 40px 16px 64px; }
    .pfshow-hero { height: 44vh; }
    .pfshow-hero__name { font-size: clamp(1.8rem, 8vw, 2.6rem); }
}
</style>
@endpush

@section('content')

{{-- ヒーローバナー --}}
<section class="pfshow-hero">
    <img src="{{ $photoUrl }}" alt="{{ $nameEn }}" class="pfshow-hero__img">
    <div class="pfshow-hero__overlay"></div>
    <div class="pfshow-hero__text">
        <span class="pfshow-hero__role">{{ $roleEn }} · {{ $roleJa }}</span>
        <h1 class="pfshow-hero__name">{{ $nameEn }}</h1>
        @if ($nameJa)
        <p class="pfshow-hero__name-ja">{{ $nameJa }}</p>
        @endif
    </div>
</section>

<div class="pfshow-wrap">
    <div class="pfshow-body">
        {{-- 写真 --}}
        <div class="pfshow-photo-col">
            @if ($photo)
                <img src="{{ asset('storage/' . $photo) }}" alt="{{ $nameEn }}" class="pfshow-photo">
            @else
                <div class="pfshow-photo-placeholder">
                    <i class="fa-solid fa-user"></i>
                </div>
            @endif
        </div>

        {{-- プロフィール情報 --}}
        <div class="pfshow-info">
            <span class="pfshow-section-en">{{ $roleEn }} Profile</span>
            <h2 class="pfshow-name">{{ $nameEn }}</h2>
            @if ($nameJa)
            <p class="pfshow-name-ja">{{ $nameJa }}</p>
            @endif
            <div class="pfshow-rule"></div>

            @if ($bio)
                <p class="pfshow-bio">{{ $bio }}</p>
            @else
                <p class="pfshow-bio-empty">プロフィールは準備中です。</p>
            @endif
        </div>
    </div>

    {{-- ページナビゲーション --}}
    <div class="pfshow-nav">
        <a href="{{ route('profiles.index') }}" class="pfshow-nav__back">
            <i class="fa-solid fa-chevron-left" style="font-size:0.7rem;"></i>おふたり一覧へ戻る
        </a>
        <a href="{{ route('profiles.show', $otherPerson) }}" class="pfshow-nav__other">
            {{ $isGroom ? '新婦のプロフィールを見る' : '新郎のプロフィールを見る' }}
            <i class="fa-solid fa-chevron-right" style="font-size:0.7rem;"></i>
        </a>
    </div>
</div>
@endsection
