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
    position: fixed; inset: 0; z-index: 9000; background: rgba(16,12,9,.95);
    display: flex; align-items: center; justify-content: center; padding: 20px;
    opacity: 0; pointer-events: none; transition: opacity 0.22s;
}
.gl-lightbox.is-open { opacity: 1; pointer-events: all; }
.gl-lightbox__inner { position: relative; width: min(1120px, 100%); height: min(90vh, 820px); display: grid; place-items: center; }
.gl-lightbox__img {
    max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 14px;
    box-shadow: 0 18px 58px rgba(0,0,0,.42); transform: scale(.98); transition: transform .22s, opacity .14s;
}
.gl-lightbox.is-open .gl-lightbox__img { transform: scale(1); }
.gl-lightbox__meta {
    position: absolute; left: 16px; right: 16px; bottom: 14px; z-index: 3;
    display: grid; gap: 8px; justify-items: center; pointer-events: none;
}
.gl-lightbox__caption { margin: 0; color: rgba(255,255,255,.82); font-size: .85rem; text-align: center; }
.gl-lightbox__caption:empty { display: none; }
.gl-lightbox__tags { display: flex; flex-wrap: wrap; gap: 7px; justify-content: center; }
.gl-lightbox__tag {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(255,255,255,.14); color: #fff; text-decoration: none;
    border: 1px solid rgba(255,255,255,.34); border-radius: 999px;
    padding: 6px 12px; font-size: .76rem; transition: background .15s; pointer-events: auto;
}
.gl-lightbox__tag:hover { background: rgba(255,255,255,.24); }
.gl-lightbox__download {
    position: absolute; top: 14px; left: 14px; z-index: 4;
    width: 44px; height: 44px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.14); color: #fff;
    font-size: 1rem; cursor: pointer; text-decoration: none; backdrop-filter: blur(12px);
}
.gl-lightbox__close { position: absolute; top: 14px; right: 14px; z-index: 4; width: 44px; height: 44px; border-radius: 999px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.14); color: #fff; font-size: 1.15rem; cursor: pointer; backdrop-filter: blur(12px); }
.gl-lightbox__nav {
    position: absolute; top: 50%; transform: translateY(-50%); z-index: 4;
    background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.14); color: #fff;
    width: 44px; height: 44px; border-radius: 999px; cursor: pointer;
    font-size: 1rem; display: flex; align-items: center; justify-content: center;
    backdrop-filter: blur(12px);
}
.gl-lightbox__prev { left: 14px; }
.gl-lightbox__next { right: 14px; }
.gl-lightbox__topcount { position: absolute; top: 23px; left: 72px; right: 72px; z-index: 4; text-align: center; color: rgba(255,255,255,.86); font-size: .76rem; font-weight: 800; letter-spacing: 2px; }

.gl-empty { text-align: center; padding: 60px 20px; color: #c0b0a0; }
.gl-empty i { font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 16px; }

@media (max-width: 767px) {
    .gl-grid { columns: 2 140px; column-gap: 10px; }
    .gl-item { margin-bottom: 10px; }
    .gl-lightbox { padding: 0; align-items: stretch; justify-content: stretch; }
    .gl-lightbox__inner { width: 100%; height: 100dvh; max-height: none; padding: calc(env(safe-area-inset-top) + 66px) 12px 128px; box-sizing: border-box; }
    .gl-lightbox__img { max-width: calc(100vw - 24px); max-height: calc(100dvh - env(safe-area-inset-top) - env(safe-area-inset-bottom) - 210px); border-radius: 14px; }
    .gl-lightbox__download { display: none; }
    .gl-lightbox__nav { display: none; }
    .gl-lightbox__close { top: calc(env(safe-area-inset-top) + 14px); right: 14px; }
    .gl-lightbox__topcount { top: calc(env(safe-area-inset-top) + 23px); }
    .gl-lightbox__meta { left: 12px; right: 12px; bottom: calc(env(safe-area-inset-bottom) + 18px); padding: 12px; border: 1px solid rgba(255,255,255,.13); border-radius: 16px; background: rgba(16,12,9,.72); backdrop-filter: blur(14px); }
    .gl-lightbox__tags { max-height: 84px; overflow-y: auto; }
    .gl-lightbox__tag { background: rgba(255,255,255,.12); }
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
        @php $others = $photo->taggedUsers->where('id', '!=', $user->id); @endphp
        <div class="gl-item" data-index="{{ $i }}" onclick="openLightbox({{ $i }})">
            <img src="{{ $photo->url }}" alt="{{ $photo->caption ?? '写真' }}" loading="lazy">
            @if ($photo->caption || $others->isNotEmpty())
            <div class="gl-item__caption">
                {{ $photo->caption }}
                @if ($others->isNotEmpty())
                <br><i class="fa-solid fa-user-group" style="font-size:0.7rem;opacity:0.8;"></i>
                {{ $others->map(fn($u) => $u->guestProfile?->fullName() ?: $u->name)->implode('、') }}
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>

@if ($photos->isNotEmpty())
<div class="gl-lightbox" id="glLightbox" onclick="closeLightboxOnOverlay(event)">
    <div class="gl-lightbox__inner">
        <a class="gl-lightbox__download" id="glLightboxDownload" href="" download onclick="event.stopPropagation()" aria-label="ダウンロード">
            <i class="fa-solid fa-download"></i>
        </a>
        <span class="gl-lightbox__topcount" id="glLightboxTopIndex">Photo</span>
        <button class="gl-lightbox__close" onclick="closeLightbox()" aria-label="閉じる">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <button class="gl-lightbox__nav gl-lightbox__prev" onclick="event.stopPropagation();prevPhoto()" aria-label="前へ">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <img class="gl-lightbox__img" id="glLightboxImg" src="" alt="" onclick="event.stopPropagation()">
        <button class="gl-lightbox__nav gl-lightbox__next" onclick="event.stopPropagation();nextPhoto()" aria-label="次へ">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
        <div class="gl-lightbox__meta">
            <p class="gl-lightbox__caption" id="glLightboxCaption"></p>
            <div class="gl-lightbox__tags" id="glLightboxTags"></div>
        </div>
    </div>
</div>

@php
    $photosJson = $photos->map(function ($p) use ($user) {
        $tags = $p->taggedUsers->where('id', '!=', $user->id)->map(function ($u) {
            return ['id' => $u->id, 'name' => $u->guestProfile?->fullName() ?: $u->name];
        })->values();

        return ['url' => $p->url, 'caption' => $p->caption, 'tags' => $tags, 'download_name' => 'wedding-photo-' . $p->id . '.jpg'];
    })->values();
@endphp
<script>
const peopleBaseUrl = "{{ url('/people') }}";
const photos = @json($photosJson);
let current = 0;

function openLightbox(index) {
    current = index;
    showPhoto();
    document.getElementById('glLightbox').classList.add('is-open');
    document.body.style.overflow = 'hidden';
}
function scrollToCurrentItem() {
    const item = document.querySelector(`.gl-item[data-index="${current}"]`);
    if (!item) return;
    window.setTimeout(() => item.scrollIntoView({ behavior: 'smooth', block: 'center' }), 80);
}
function closeLightbox() {
    document.getElementById('glLightbox').classList.remove('is-open');
    document.body.style.overflow = '';
    scrollToCurrentItem();
}
function isLightboxControlTarget(target) {
    return Boolean(target.closest('button, a, img, .gl-lightbox__meta'));
}
function handleLightboxSurfaceTap(event) {
    if (isLightboxControlTarget(event.target)) return;
    const edgeWidth = Math.min(104, window.innerWidth * 0.28);
    if (event.clientX <= edgeWidth) { prevPhoto(); return; }
    if (event.clientX >= window.innerWidth - edgeWidth) { nextPhoto(); return; }
    closeLightbox();
}
function closeLightboxOnOverlay(e) {
    if (e.target === document.getElementById('glLightbox')) handleLightboxSurfaceTap(e);
}
function showPhoto() {
    const p = photos[current];
    document.getElementById('glLightboxImg').src = p.url;
    document.getElementById('glLightboxCaption').textContent = p.caption ?? '';
    const download = document.getElementById('glLightboxDownload');
    download.href = p.url;
    download.download = p.download_name || `wedding-photo-${current + 1}.jpg`;
    document.getElementById('glLightboxTopIndex').textContent = `${current + 1} / ${photos.length}`;
    const tagsEl = document.getElementById('glLightboxTags');
    const escapeHtml = s => s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    tagsEl.innerHTML = (p.tags || []).map(t =>
        `<a class="gl-lightbox__tag" href="${peopleBaseUrl}/${t.id}" onclick="event.stopPropagation()"><i class="fa-solid fa-user"></i> ${escapeHtml(t.name)}</a>`
    ).join('');
}
function nextPhoto() { current = (current + 1) % photos.length; showPhoto(); }
function prevPhoto() { current = (current - 1 + photos.length) % photos.length; showPhoto(); }
(function () {
    const stage = document.querySelector('.gl-lightbox__inner');
    if (!stage) return;
    let startX = 0;
    let startY = 0;
    stage.addEventListener('click', handleLightboxSurfaceTap);
    stage.addEventListener('touchstart', event => {
        const touch = event.changedTouches[0];
        startX = touch.clientX;
        startY = touch.clientY;
    }, { passive: true });
    stage.addEventListener('touchend', event => {
        const touch = event.changedTouches[0];
        const dx = touch.clientX - startX;
        const dy = touch.clientY - startY;
        if (Math.abs(dx) < 44 || Math.abs(dx) < Math.abs(dy) * 1.2) return;
        dx < 0 ? nextPhoto() : prevPhoto();
    }, { passive: true });
})();
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
