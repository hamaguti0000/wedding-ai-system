@extends('layouts.app')
@section('title', 'お知らせ一覧 | Wedding')

@push('styles')
<link rel="stylesheet" href="{{ css_asset('css/home.css') }}">
<style>
main { padding: 0; text-align: initial; }

.nw-banner {
    position: relative; height: 28vh; min-height: 180px;
    overflow: hidden; display: flex; align-items: center; justify-content: center; text-align: center;
    padding-top: 60px; box-sizing: border-box;
}
.nw-banner__img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; filter: brightness(0.38) saturate(0.7); }
.nw-banner__overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(20,12,4,0.1), rgba(20,12,4,0.55)); }
.nw-banner__text { position: relative; z-index: 2; color: #fff; padding: 0 20px; }
.nw-banner__eyebrow { display: block; font-size: 0.6rem; letter-spacing: 5px; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 10px; font-family: 'Noto Sans JP', sans-serif; }
.nw-banner__title { font-family: 'Playfair Display', serif; font-size: clamp(1.4rem, 4vw, 2.2rem); font-weight: 400; letter-spacing: 2px; margin: 0; }

.nw-wrap { max-width: 720px; margin: 0 auto; padding: 60px 20px 80px; }
.nw-intro { text-align: center; margin-bottom: 40px; }
.nw-section-en { display: block; font-size: 0.65rem; letter-spacing: 5px; text-transform: uppercase; color: #b38b59; margin-bottom: 6px; font-family: 'Noto Sans JP', sans-serif; }
.nw-section-ja { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 400; color: #3d2f25; margin: 0 0 12px; }
.nw-rule { width: 40px; height: 1px; background: #b38b59; margin: 0 auto; }
.nw-count { text-align: center; font-size: 0.8rem; color: #b0a090; margin-top: 16px; }

.nw-empty { text-align: center; padding: 60px 20px; color: #c0b0a0; }
.nw-empty i { font-size: 2.5rem; opacity: 0.3; display: block; margin-bottom: 12px; }

@media (min-width: 768px) {
    .nw-banner { padding-top: 80px; }
    .nw-wrap { padding: 80px 24px 100px; }
}
</style>
@endpush

@section('content')
<section class="nw-banner">
    @include('partials.rotating-banner', ['class' => 'nw-banner__img'])
    <div class="nw-banner__overlay"></div>
    <div class="nw-banner__text">
        <span class="nw-banner__eyebrow">News · お知らせ</span>
        <h1 class="nw-banner__title">お知らせ一覧</h1>
    </div>
</section>

<div class="nw-wrap">
    <div class="nw-intro">
        <span class="nw-section-en">News</span>
        <h2 class="nw-section-ja">お知らせ</h2>
        <div class="nw-rule"></div>
        @if ($news->isNotEmpty())
        <p class="nw-count">{{ $news->count() }}件</p>
        @endif
    </div>

    @if ($news->isEmpty())
    <div class="nw-empty">
        <i class="fa-regular fa-newspaper"></i>
        <p>お知らせはありません</p>
    </div>
    @else
    <div class="home-news__list">
        @foreach ($news as $item)
        @php $layout = $item->layout ?? 'text'; @endphp

        @if ($layout === 'text' || !$item->image_url)
        <a href="{{ route('news.show', $item->id) }}" class="hn-item hn-text hn-link">
            <div class="hn-meta">
                <time class="hn-date">{{ $item->published_date->format('Y.m.d') }}</time>
                @if ($item->tag)<span class="hn-tag">{{ $item->tag }}</span>@endif
            </div>
            @if ($item->title)<p class="hn-title">{{ $item->title }}</p>@endif
            <p class="hn-body">{{ Str::limit($item->body, 150) }}</p>
            <span class="hn-more">続きを読む <i class="fa-solid fa-arrow-right" style="font-size:0.65rem;"></i></span>
        </a>

        @elseif ($layout === 'top')
        <a href="{{ route('news.show', $item->id) }}" class="hn-item hn-top hn-link">
            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="hn-img hn-img--top">
            <div class="hn-content">
                <div class="hn-meta">
                    <time class="hn-date">{{ $item->published_date->format('Y.m.d') }}</time>
                    @if ($item->tag)<span class="hn-tag">{{ $item->tag }}</span>@endif
                </div>
                @if ($item->title)<p class="hn-title">{{ $item->title }}</p>@endif
                <p class="hn-body">{{ Str::limit($item->body, 120) }}</p>
                <span class="hn-more">続きを読む <i class="fa-solid fa-arrow-right" style="font-size:0.65rem;"></i></span>
            </div>
        </a>

        @elseif ($layout === 'left')
        <a href="{{ route('news.show', $item->id) }}" class="hn-item hn-side hn-side--left hn-link">
            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="hn-img hn-img--side">
            <div class="hn-content">
                <div class="hn-meta">
                    <time class="hn-date">{{ $item->published_date->format('Y.m.d') }}</time>
                    @if ($item->tag)<span class="hn-tag">{{ $item->tag }}</span>@endif
                </div>
                @if ($item->title)<p class="hn-title">{{ $item->title }}</p>@endif
                <p class="hn-body">{{ Str::limit($item->body, 120) }}</p>
                <span class="hn-more">続きを読む <i class="fa-solid fa-arrow-right" style="font-size:0.65rem;"></i></span>
            </div>
        </a>

        @elseif ($layout === 'right')
        <a href="{{ route('news.show', $item->id) }}" class="hn-item hn-side hn-side--right hn-link">
            <div class="hn-content">
                <div class="hn-meta">
                    <time class="hn-date">{{ $item->published_date->format('Y.m.d') }}</time>
                    @if ($item->tag)<span class="hn-tag">{{ $item->tag }}</span>@endif
                </div>
                @if ($item->title)<p class="hn-title">{{ $item->title }}</p>@endif
                <p class="hn-body">{{ Str::limit($item->body, 120) }}</p>
                <span class="hn-more">続きを読む <i class="fa-solid fa-arrow-right" style="font-size:0.65rem;"></i></span>
            </div>
            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="hn-img hn-img--side">
        </a>

        @elseif ($layout === 'hero')
        <a href="{{ route('news.show', $item->id) }}" class="hn-item hn-hero hn-link">
            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="hn-img hn-img--hero">
            <div class="hn-hero__overlay">
                <div class="hn-meta hn-meta--light">
                    <time class="hn-date">{{ $item->published_date->format('Y.m.d') }}</time>
                    @if ($item->tag)<span class="hn-tag hn-tag--light">{{ $item->tag }}</span>@endif
                </div>
                @if ($item->title)<p class="hn-title hn-title--light">{{ $item->title }}</p>@endif
                <span class="hn-more" style="color:rgba(255,255,255,0.85);">続きを読む <i class="fa-solid fa-arrow-right" style="font-size:0.65rem;"></i></span>
            </div>
        </a>

        @elseif ($layout === 'card')
        <a href="{{ route('news.show', $item->id) }}" class="hn-item hn-card hn-link">
            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="hn-img hn-img--card">
            <div class="hn-card__body">
                <div class="hn-meta">
                    <time class="hn-date">{{ $item->published_date->format('Y.m.d') }}</time>
                    @if ($item->tag)<span class="hn-tag">{{ $item->tag }}</span>@endif
                </div>
                @if ($item->title)<p class="hn-title">{{ $item->title }}</p>@endif
                <p class="hn-body">{{ Str::limit($item->body, 120) }}</p>
                <span class="hn-more">続きを読む <i class="fa-solid fa-arrow-right" style="font-size:0.65rem;"></i></span>
            </div>
        </a>
        @endif
        @endforeach
    </div>
    @endif
</div>
@endsection
