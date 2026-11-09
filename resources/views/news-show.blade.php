@extends('layouts.app')
@section('title', ($item->title ?: Str::limit($item->body, 30)) . ' | お知らせ')

@push('styles')
<link rel="stylesheet" href="{{ css_asset('css/home.css') }}">
<style>
main { padding: 0; text-align: initial; }

.ns-banner {
    position: relative; height: 28vh; min-height: 180px;
    overflow: hidden; display: flex; align-items: center; justify-content: center; text-align: center;
    padding-top: 60px; box-sizing: border-box;
}
.ns-banner__img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; filter: brightness(0.38) saturate(0.7); }
.ns-banner__img--article { filter: brightness(0.45) saturate(0.75); }
.ns-banner__overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(20,12,4,0.1), rgba(20,12,4,0.6)); }
.ns-banner__text { position: relative; z-index: 2; color: #fff; padding: 0 20px; max-width: 700px; }
.ns-banner__eyebrow { display: block; font-size: 0.6rem; letter-spacing: 5px; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 10px; font-family: 'Noto Sans JP', sans-serif; }
.ns-banner__tag { display: inline-block; font-size: 0.68rem; padding: 2px 10px; border-radius: 2px; background: rgba(179,139,89,0.35); color: #f0d8a8; border: 1px solid rgba(232,213,183,0.4); letter-spacing: 1px; margin-bottom: 10px; }
.ns-banner__title { font-family: 'Playfair Display', serif; font-size: clamp(1.2rem, 4vw, 2rem); font-weight: 400; letter-spacing: 1px; margin: 0; line-height: 1.35; }

/* ── コンテンツ ── */
.ns-wrap { max-width: 720px; margin: 0 auto; padding: 56px 20px 80px; }

/* 記事メタ */
.ns-meta { display: flex; align-items: center; gap: 12px; margin-bottom: 32px; flex-wrap: wrap; }
.ns-meta__date { font-size: 0.82rem; color: #a89282; letter-spacing: 0.5px; }
.ns-meta__tag {
    font-size: 0.7rem; padding: 2px 10px; border-radius: 2px;
    background: #fef9f0; color: #b38b59; border: 1px solid #e8d5b7; letter-spacing: 1px;
}
.ns-meta__back {
    margin-left: auto; display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.78rem; color: #b38b59; text-decoration: none; transition: color 0.15s;
}
.ns-meta__back:hover { color: #9a7447; }

/* 記事タイトル */
.ns-article-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.4rem, 4vw, 2rem);
    font-weight: 400; color: #3d2f25; line-height: 1.4;
    margin: 0 0 28px; letter-spacing: 0.02em;
}

/* 記事画像 */
.ns-article-img {
    width: 100%; border-radius: 12px; display: block; margin-bottom: 28px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}
.ns-article-img--side {
    float: left; width: 45%; margin: 0 24px 16px 0;
    border-radius: 8px;
}
.ns-article-img--side-right {
    float: right; width: 45%; margin: 0 0 16px 24px;
    border-radius: 8px;
}

/* ヒーロー画像ラップ */
.ns-hero-wrap {
    position: relative; border-radius: 12px; overflow: hidden;
    margin-bottom: 28px;
}
.ns-hero-wrap img { width: 100%; display: block; max-height: 500px; object-fit: cover; }
.ns-hero-wrap__overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(20,10,2,0.65), transparent 55%);
}

/* 本文 */
.ns-article-body {
    font-size: 1rem; color: #4a3a2e; line-height: 2;
    white-space: pre-wrap; word-break: break-word;
}
.ns-divider { height: 1px; background: #f0ebe3; margin: 40px 0; clear: both; }

/* 前後ナビ */
.ns-nav {
    display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
}
.ns-nav__link {
    display: flex; flex-direction: column; gap: 4px;
    padding: 16px 18px; border-radius: 10px;
    border: 1px solid #f0ebe3; background: #fff;
    text-decoration: none; transition: box-shadow 0.15s, border-color 0.15s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.ns-nav__link:hover { border-color: rgba(179,139,89,0.4); box-shadow: 0 4px 14px rgba(0,0,0,0.08); }
.ns-nav__link--next { text-align: right; }
.ns-nav__dir { font-size: 0.66rem; color: #b38b59; letter-spacing: 2px; text-transform: uppercase; font-weight: 700; }
.ns-nav__title { font-size: 0.86rem; color: #3d2f25; line-height: 1.4; }
.ns-nav__empty { }

.ns-list-btn {
    display: inline-flex; align-items: center; gap: 7px;
    margin: 28px 0 0; padding: 11px 24px;
    background: transparent; border: 1px solid #e0d0bc;
    border-radius: 6px; color: #9b8573; font-size: 0.88rem;
    font-family: 'Noto Sans JP', sans-serif; text-decoration: none;
    transition: border-color 0.15s, color 0.15s;
}
.ns-list-btn:hover { border-color: #b38b59; color: #b38b59; }

@media (max-width: 640px) {
    .ns-wrap { padding: 40px 16px 64px; }
    .ns-article-img--side, .ns-article-img--side-right { float: none; width: 100%; margin: 0 0 20px; }
    .ns-nav { grid-template-columns: 1fr; }
}
@media (min-width: 768px) {
    .ns-banner { padding-top: 80px; height: 32vh; }
}
</style>
@endpush

@section('content')

{{-- バナー：画像ありならその画像、なければチャペル --}}
@php $layout = $item->layout ?? 'text'; @endphp
<section class="ns-banner">
    @if ($item->image_url)
        <img src="{{ $item->image_url }}" alt="" class="ns-banner__img ns-banner__img--article">
    @else
        @include('partials.rotating-banner', ['class' => 'ns-banner__img'])
    @endif
    <div class="ns-banner__overlay"></div>
    <div class="ns-banner__text">
        <span class="ns-banner__eyebrow">News · お知らせ</span>
        @if ($item->tag)<span class="ns-banner__tag">{{ $item->tag }}</span>@endif
        @if ($item->title)
        <h1 class="ns-banner__title">{{ $item->title }}</h1>
        @endif
    </div>
</section>

<div class="ns-wrap">

    {{-- メタ情報 --}}
    <div class="ns-meta">
        <time class="ns-meta__date">{{ $item->published_date->format('Y年n月j日') }}</time>
        @if ($item->tag)<span class="ns-meta__tag">{{ $item->tag }}</span>@endif
        <a href="{{ route('news.index') }}" class="ns-meta__back">
            <i class="fa-solid fa-chevron-left" style="font-size:0.65rem;"></i>一覧へ
        </a>
    </div>

    {{-- タイトル（バナーなしの場合も表示）--}}
    @if ($item->title && !$item->image_url)
    <h1 class="ns-article-title">{{ $item->title }}</h1>
    @elseif ($item->title && $item->image_url)
    <h1 class="ns-article-title">{{ $item->title }}</h1>
    @endif

    {{-- 本文・画像（レイアウト別）--}}
    @if ($layout === 'top' && $item->image_url)
        <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="ns-article-img">
        <p class="ns-article-body">{{ $item->body }}</p>

    @elseif ($layout === 'left' && $item->image_url)
        <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="ns-article-img--side">
        <p class="ns-article-body">{{ $item->body }}</p>
        <div class="ns-divider"></div>

    @elseif ($layout === 'right' && $item->image_url)
        <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="ns-article-img--side-right">
        <p class="ns-article-body">{{ $item->body }}</p>
        <div class="ns-divider"></div>

    @elseif ($layout === 'hero' && $item->image_url)
        <div class="ns-hero-wrap">
            <img src="{{ $item->image_url }}" alt="{{ $item->title }}">
            <div class="ns-hero-wrap__overlay"></div>
        </div>
        <p class="ns-article-body">{{ $item->body }}</p>

    @elseif ($layout === 'card' && $item->image_url)
        <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="ns-article-img">
        <p class="ns-article-body">{{ $item->body }}</p>

    @else
        {{-- テキストのみ or 画像なし --}}
        @if ($item->image_url)
        <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="ns-article-img">
        @endif
        <p class="ns-article-body">{{ $item->body }}</p>
    @endif

    <div class="ns-divider"></div>

    {{-- 前後ナビ --}}
    <nav class="ns-nav">
        @if ($prev)
        <a href="{{ route('news.show', $prev->id) }}" class="ns-nav__link ns-nav__link--prev">
            <span class="ns-nav__dir"><i class="fa-solid fa-chevron-left" style="font-size:0.6rem;"></i> 前の記事</span>
            <span class="ns-nav__title">{{ $prev->title ?: Str::limit($prev->body, 30) }}</span>
        </a>
        @else
        <div class="ns-nav__empty"></div>
        @endif

        @if ($next)
        <a href="{{ route('news.show', $next->id) }}" class="ns-nav__link ns-nav__link--next">
            <span class="ns-nav__dir">次の記事 <i class="fa-solid fa-chevron-right" style="font-size:0.6rem;"></i></span>
            <span class="ns-nav__title">{{ $next->title ?: Str::limit($next->body, 30) }}</span>
        </a>
        @else
        <div class="ns-nav__empty"></div>
        @endif
    </nav>

    <a href="{{ route('news.index') }}" class="ns-list-btn">
        <i class="fa-solid fa-list"></i> お知らせ一覧へ戻る
    </a>

</div>
@endsection
