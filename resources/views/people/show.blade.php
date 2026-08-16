@extends('layouts.app')
@section('title', ($user->guestProfile?->fullName() ?: $user->name) . 'さんの写真 | Wedding')

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

.gl-wrap { max-width: 1100px; margin: 0 auto; padding: 60px 20px 80px; }
.gl-intro { text-align: center; margin-bottom: 48px; }
.gl-section-en { display: block; font-size: 0.65rem; letter-spacing: 5px; text-transform: uppercase; color: #b38b59; margin-bottom: 6px; font-family: 'Noto Sans JP', sans-serif; }
.gl-section-ja { font-family: 'Playfair Display', serif; font-size: 1.7rem; font-weight: 400; color: #3d2f25; margin: 0 0 14px; }
.gl-rule { width: 40px; height: 1px; background: #b38b59; margin: 0 auto; }
.people-back { display: inline-flex; align-items: center; gap: 6px; font-size: 0.82rem; color: #b38b59; text-decoration: none; margin-bottom: 24px; }
.people-back:hover { text-decoration: underline; }

.gl-grid { columns: 3 280px; column-gap: 16px; }
.gl-item {
    break-inside: avoid; margin-bottom: 16px; border-radius: 10px; overflow: hidden;
    cursor: pointer; position: relative; animation: gl-in 0.4s ease backwards;
}
@keyframes gl-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.gl-item img { width: 100%; display: block; transition: transform 0.35s ease; }
.gl-item:hover img { transform: scale(1.04); }
.gl-item__caption {
    position: absolute; bottom: 0; left: 0; right: 0; padding: 10px 14px;
    background: linear-gradient(to top, rgba(20,10,2,0.65), transparent);
    color: #fff; font-size: 0.8rem; opacity: 0; transition: opacity 0.25s; line-height: 1.5;
}
.gl-item:hover .gl-item__caption { opacity: 1; }

.gl-lightbox {
    position: fixed; inset: 0; z-index: 9000; background: rgba(10,5,0,0.92);
    display: flex; align-items: center; justify-content: center; padding: 20px;
    opacity: 0; pointer-events: none; transition: opacity 0.25s;
}
.gl-lightbox.is-open { opacity: 1; pointer-events: all; }
.gl-lightbox__inner { position: relative; max-width: 90vw; max-height: 90vh; display: flex; align-items: center; justify-content: center; }
.gl-lightbox__img {
    max-width: 90vw; max-height: 85vh; object-fit: contain; border-radius: 6px;
    box-shadow: 0 16px 64px rgba(0,0,0,0.5); transform: scale(0.95); transition: transform 0.25s;
}
.gl-lightbox.is-open .gl-lightbox__img { transform: scale(1); }
.gl-lightbox__caption { position: absolute; bottom: -32px; left: 0; right: 0; text-align: center; color: rgba(255,255,255,0.7); font-size: 0.85rem; }
.gl-lightbox__close { position: absolute; top: -44px; right: 0; background: none; border: none; color: #fff; font-size: 1.5rem; cursor: pointer; opacity: 0.7; transition: opacity 0.15s; }
.gl-lightbox__close:hover { opacity: 1; }
.gl-lightbox__nav {
    position: absolute; top: 50%; transform: translateY(-50%);
    background: rgba(255,255,255,0.15); border: none; color: #fff;
    width: 44px; height: 44px; border-radius: 50%; cursor: pointer;
    font-size: 1rem; display: flex; align-items: center; justify-content: center;
    transition: background 0.15s; backdrop-filter: blur(4px);
}
.gl-lightbox__nav:hover { background: rgba(255,255,255,0.25); }
.gl-lightbox__prev { left: -56px; }
.gl-lightbox__next { right: -56px; }

.gl-empty { text-align: center; padding: 60px 20px; color: #c0b0a0; }
.gl-empty i { font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 16px; }

@media (max-width: 767px) {
    .gl-grid { columns: 2 140px; column-gap: 10px; }
    .gl-item { margin-bottom: 10px; }
    .gl-lightbox__prev { left: -10px; }
    .gl-lightbox__next { right: -10px; }
    .gl-lightbox__nav { width: 36px; height: 36px; }
}
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
        <span class="gl-banner__eyebrow">People · 参加者一覧</span>
        <h1 class="gl-banner__title">{{ $user->guestProfile?->fullName() ?: $user->name }} さんの写真</h1>
    </div>
</section>

<div class="gl-wrap">
    <a href="{{ route('people.index') }}" class="people-back">
        <i class="fa-solid fa-arrow-left"></i> 参加者一覧に戻る
    </a>

    @if ($photos->isEmpty())
    <div class="gl-empty">
        <i class="fa-regular fa-images"></i>
        <p>まだこの人が写っている写真はありません</p>
    </div>
    @else
    <div class="gl-grid" id="glGrid">
        @foreach ($photos as $i => $photo)
        <div class="gl-item" data-index="{{ $i }}" onclick="openLightbox({{ $i }})">
            <img src="{{ $photo->url }}" alt="{{ $photo->caption ?? '写真' }}" loading="lazy">
            @if ($photo->caption)
            <div class="gl-item__caption">{{ $photo->caption }}</div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>

@if ($photos->isNotEmpty())
<div class="gl-lightbox" id="glLightbox" onclick="closeLightboxOnOverlay(event)">
    <div class="gl-lightbox__inner">
        <button class="gl-lightbox__close" onclick="closeLightbox()" aria-label="閉じる">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <button class="gl-lightbox__nav gl-lightbox__prev" onclick="event.stopPropagation();prevPhoto()" aria-label="前へ">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <img class="gl-lightbox__img" id="glLightboxImg" src="" alt="">
        <button class="gl-lightbox__nav gl-lightbox__next" onclick="event.stopPropagation();nextPhoto()" aria-label="次へ">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
        <p class="gl-lightbox__caption" id="glLightboxCaption"></p>
    </div>
</div>

<script>
const photos = @json($photos->map(fn($p) => ['url' => $p->url, 'caption' => $p->caption])->values());
let current = 0;

function openLightbox(index) {
    current = index;
    showPhoto();
    document.getElementById('glLightbox').classList.add('is-open');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    document.getElementById('glLightbox').classList.remove('is-open');
    document.body.style.overflow = '';
}
function closeLightboxOnOverlay(e) {
    if (e.target === document.getElementById('glLightbox')) closeLightbox();
}
function showPhoto() {
    const p = photos[current];
    document.getElementById('glLightboxImg').src = p.url;
    document.getElementById('glLightboxCaption').textContent = p.caption ?? '';
}
function nextPhoto() { current = (current + 1) % photos.length; showPhoto(); }
function prevPhoto() { current = (current - 1 + photos.length) % photos.length; showPhoto(); }
document.addEventListener('keydown', e => {
    const lb = document.getElementById('glLightbox');
    if (!lb.classList.contains('is-open')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowRight') nextPhoto();
    if (e.key === 'ArrowLeft')  prevPhoto();
});
</script>
@endif
@endsection
