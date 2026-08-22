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
    pointer-events: none; -webkit-user-select: none; user-select: none;
}
.gl-item:hover .gl-item__caption { opacity: 1; }

.gl-lightbox {
    position: fixed; inset: 0; z-index: 9000; background: rgba(16,12,9,.95);
    display: flex; align-items: center; justify-content: center; padding: 20px;
    opacity: 0; pointer-events: none; transition: opacity 0.22s;
    -webkit-user-select: none; user-select: none; -webkit-touch-callout: none;
}
.gl-lightbox * { -webkit-user-select: none; user-select: none; -webkit-touch-callout: none; }
.gl-lightbox.is-open { opacity: 1; pointer-events: all; }
.gl-lightbox__inner { position: relative; width: min(1120px, 100%); height: min(90vh, 820px); display: grid; place-items: center; }
.gl-lightbox__img {
    max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 14px;
    box-shadow: 0 18px 58px rgba(0,0,0,.42); transform: scale(.98); transition: transform .22s, opacity .14s; -webkit-user-drag: none;
}
.gl-lightbox.is-open .gl-lightbox__img { transform: scale(1); }
.gl-lightbox__meta {
    position: absolute; left: 16px; right: 16px; bottom: 14px; z-index: 5;
    display: grid; gap: 8px; justify-items: start; pointer-events: auto;
    max-height: 96px; overflow: hidden;
    padding: 10px 12px; border-radius: 16px;
    background: linear-gradient(180deg, rgba(16,12,9,.2), rgba(16,12,9,.58));
    border: 1px solid rgba(255,255,255,.08);
    backdrop-filter: blur(12px);
}
.gl-lightbox__caption { margin: 0; color: rgba(255,255,255,.86); font-size: .82rem; text-align: left; line-height: 1.45; }
.gl-lightbox__caption:empty { display: none; }
.gl-lightbox__tags { display: flex; flex-wrap: nowrap; gap: 7px; justify-content: flex-start; width: 100%; overflow-x: auto; overflow-y: hidden; overscroll-behavior-x: contain; padding-bottom: 2px; scrollbar-width: none; }
.gl-lightbox__tags::-webkit-scrollbar { display: none; }
.gl-lightbox__tag {
    display: inline-flex; align-items: center; justify-content: center; gap: 5px;
    background: rgba(255,255,255,.13); color: #fff; text-decoration: none;
    border: 1px solid rgba(255,255,255,.22); border-radius: 999px;
    padding: 7px 11px; font-size: .74rem; transition: background .15s; pointer-events: auto;
    min-height: 32px; max-width: 170px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    flex: 0 0 auto;
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
.gl-lightbox__hint { position: absolute; left: 50%; bottom: 122px; z-index: 4; transform: translateX(-50%); padding: 4px 9px; border-radius: 999px; background: rgba(255,255,255,.1); color: rgba(255,255,255,.62); font-size: .62rem; letter-spacing: .06em; pointer-events: none; white-space: nowrap; }
.gl-lightbox.is-zoomed .gl-lightbox__hint { background: rgba(255,255,255,.18); color: rgba(255,255,255,.9); }

.gl-empty { text-align: center; padding: 60px 20px; color: #c0b0a0; }
.gl-empty i { font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 16px; }

@media (max-width: 767px) {
    .gl-grid { columns: 2 140px; column-gap: 10px; }
    .gl-item { margin-bottom: 10px; }
    .gl-item__caption { display: none; }
    .gl-lightbox { padding: 0; align-items: stretch; justify-content: stretch; }
    .gl-lightbox__inner { width: 100%; height: 100dvh; max-height: none; padding: calc(env(safe-area-inset-top) + 70px) 12px 126px; box-sizing: border-box; }
    .gl-lightbox__img { max-width: calc(100vw - 24px); max-height: calc(100dvh - env(safe-area-inset-top) - env(safe-area-inset-bottom) - 220px); border-radius: 14px; }
    .gl-lightbox__download { left: 14px; display: inline-flex; }
    .gl-lightbox__nav { display: none; }
    .gl-lightbox__close { top: calc(env(safe-area-inset-top) + 14px); right: 14px; }
    .gl-lightbox__topcount { top: calc(env(safe-area-inset-top) + 23px); }
    .gl-lightbox__meta { left: 12px; right: 12px; bottom: calc(env(safe-area-inset-bottom) + 16px); max-height: 92px; padding: 9px 10px; border-radius: 16px; }
    .gl-lightbox__tags { flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; justify-content: flex-start; }
    .gl-lightbox__tag { background: rgba(255,255,255,.13); flex: 0 0 auto; min-width: auto; }
    .gl-lightbox__hint { bottom: calc(env(safe-area-inset-bottom) + 112px); }
}
@media (min-width: 768px) {
    .gl-banner { padding-top: 80px; }
    .gl-wrap { padding: 80px 24px 100px; }
}
</style>
@endpush

@section('content')
<section class="gl-banner">
    @if ($heroPhoto)
        <img src="{{ $heroPhoto->url }}" alt="" class="gl-banner__img">
    @else
        @include('partials.rotating-banner', ['class' => 'gl-banner__img'])
    @endif
    <div class="gl-banner__overlay"></div>
    <div class="gl-banner__text">
        <span class="gl-banner__eyebrow">People · 参加者一覧</span>
        <h1 class="gl-banner__title">{{ $user->guestProfile?->fullName() ?: $user->name }} さんの写真</h1>
    </div>
</section>

<div class="gl-wrap">
    <a href="{{ $backUrl }}" class="people-back">
        <i class="fa-solid fa-arrow-left"></i> {{ $backLabel }}
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
        <button class="gl-lightbox__close" onclick="event.stopPropagation();closeLightbox()" aria-label="閉じる">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <button class="gl-lightbox__nav gl-lightbox__prev" onclick="event.stopPropagation();prevPhoto(event)" aria-label="前へ">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <img class="gl-lightbox__img" id="glLightboxImg" src="" alt="" onclick="event.stopPropagation()">
        <button class="gl-lightbox__nav gl-lightbox__next" onclick="event.stopPropagation();nextPhoto(event)" aria-label="次へ">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
        <span class="gl-lightbox__hint" id="peopleLightboxHint">左右スワイプで移動・拡大中は固定</span>
        <div class="gl-lightbox__meta" id="peopleLightboxMeta">
            <p class="gl-lightbox__caption" id="glLightboxCaption"></p>
            <div class="gl-lightbox__tags" id="glLightboxTags"></div>
        </div>
    </div>
</div>

@php
    $photosJson = $photos->map(function ($p) use ($user, $backSource) {
        $tags = $p->taggedUsers->where('id', '!=', $user->id)->map(function ($u) use ($backSource) {
            return ['name' => $u->guestProfile?->fullName() ?: $u->name, 'href' => route('people.show-ref', ['token' => $u->publicReferenceToken(), 'from' => $backSource, 'hero' => $p->publicReferenceToken()])];
        })->values();

        return ['url' => $p->url, 'caption' => $p->caption, 'tags' => $tags];
    })->values();
@endphp
<script>
const peopleBackSource = @json($backSource);
const photos = @json($photosJson);
let current = 0;
let lightboxGestureBlockUntil = 0;
let lightboxTouchMoved = false;

function isViewportZoomed() {
    return Boolean(window.visualViewport && window.visualViewport.scale && window.visualViewport.scale > 1.04);
}
function markLightboxGestureBlocked(duration = 650) {
    lightboxGestureBlockUntil = Math.max(lightboxGestureBlockUntil, Date.now() + duration);
}
function updateLightboxZoomState() {
    const lb = document.getElementById('glLightbox');
    const zoomed = isViewportZoomed();
    lb?.classList.toggle('is-zoomed', zoomed);
    const hint = document.getElementById('peopleLightboxHint');
    if (hint) hint.textContent = zoomed ? '拡大中は写真送りを止めています' : '左右スワイプで移動・拡大中は固定';
}
function canNavigatePhoto(event) {
    if (Date.now() < lightboxGestureBlockUntil) return false;
    if (isViewportZoomed()) return false;
    if (event?.type?.startsWith('touch')) return false;
    return true;
}

function openLightbox(index) {
    current = index;
    showPhoto();
    document.getElementById('glLightbox').classList.add('is-open');
    document.body.style.overflow = 'hidden';
    updateLightboxZoomState();
}
function scrollToCurrentItem() {
    const item = document.querySelector(`.gl-item[data-index="${current}"]`);
    if (!item) return;
    window.setTimeout(() => item.scrollIntoView({ behavior: 'smooth', block: 'center' }), 80);
}
function closeLightbox() {
    document.getElementById('glLightbox').classList.remove('is-open');
    document.getElementById('glLightbox')?.classList.remove('is-zoomed');
    document.body.style.overflow = '';
    scrollToCurrentItem();
}
function isLightboxControlTarget(target) {
    return Boolean(target.closest('button, a, img, .gl-lightbox__meta'));
}
function handleLightboxSurfaceTap(event) {
    if (isLightboxControlTarget(event.target)) return;
    if (lightboxTouchMoved || Date.now() < lightboxGestureBlockUntil) return;
    closeLightbox();
}
function closeLightboxOnOverlay(e) {
    if (e.target === document.getElementById('glLightbox')) closeLightbox();
}
function showPhoto() {
    updateLightboxZoomState();
    const p = photos[current];
    document.getElementById('glLightboxImg').src = p.url;
    document.getElementById('glLightboxCaption').textContent = p.caption ?? '';
    const download = document.getElementById('glLightboxDownload');
    download.href = p.url;
    download.download = `wedding-photo-${current + 1}.jpg`;
    document.getElementById('glLightboxTopIndex').textContent = `${current + 1} / ${photos.length}`;
    const tagsEl = document.getElementById('glLightboxTags');
    const escapeHtml = s => s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    tagsEl.innerHTML = (p.tags || []).map(t =>
        `<a class="gl-lightbox__tag" href="${t.href || '#'}" data-people-link="${t.href || '#'}"><i class="fa-solid fa-user"></i> ${escapeHtml(t.name)}</a>`
    ).join('');
}
function nextPhoto(event) {
    event?.preventDefault?.();
    event?.stopPropagation?.();
    if (!canNavigatePhoto(event)) { updateLightboxZoomState(); return; }
    current = (current + 1) % photos.length;
    showPhoto();
}
function prevPhoto(event) {
    event?.preventDefault?.();
    event?.stopPropagation?.();
    if (!canNavigatePhoto(event)) { updateLightboxZoomState(); return; }
    current = (current - 1 + photos.length) % photos.length;
    showPhoto();
}
document.addEventListener('click', event => {
    const link = event.target.closest('.gl-lightbox__tag');
    if (!link) return;
    event.preventDefault();
    event.stopPropagation();
    const href = link.dataset.peopleLink;
    if (href) window.location.assign(href);
});
(function () {
    const stage = document.querySelector('.gl-lightbox__inner');
    if (!stage) return;
    let startX = 0;
    let startY = 0;
    stage.addEventListener('click', handleLightboxSurfaceTap);
    document.getElementById('glLightbox')?.addEventListener('contextmenu', event => {
        event.preventDefault();
    });
    document.getElementById('glLightbox')?.addEventListener('dragstart', event => {
        event.preventDefault();
    });
    stage.addEventListener('touchstart', event => {
        lightboxTouchMoved = false;
        if (event.target.closest('.gl-lightbox__meta')) return;
        if (event.touches.length > 1) {
            markLightboxGestureBlocked(900);
            updateLightboxZoomState();
            return;
        }
        const touch = event.changedTouches[0];
        startX = touch.clientX;
        startY = touch.clientY;
    }, { passive: true });
    stage.addEventListener('touchmove', event => {
        lightboxTouchMoved = true;
        if (event.target.closest('.gl-lightbox__meta')) return;
        if (event.touches.length > 1 || isViewportZoomed()) {
            markLightboxGestureBlocked(900);
            updateLightboxZoomState();
        }
    }, { passive: true });
    stage.addEventListener('touchend', event => {
        if (event.target.closest('.gl-lightbox__meta')) return;
        updateLightboxZoomState();
        if (Date.now() < lightboxGestureBlockUntil || isViewportZoomed()) return;
        const touch = event.changedTouches[0];
        const dx = touch.clientX - startX;
        const dy = touch.clientY - startY;
        if (Math.abs(dx) < 54 || Math.abs(dx) < Math.abs(dy) * 1.35) return;
        markLightboxGestureBlocked(220);
        dx < 0 ? nextPhoto() : prevPhoto();
    }, { passive: true });
})();
window.visualViewport?.addEventListener('resize', updateLightboxZoomState);
window.visualViewport?.addEventListener('scroll', updateLightboxZoomState);
document.addEventListener('keydown', e => {
    const lb = document.getElementById('glLightbox');
    if (!lb.classList.contains('is-open')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowRight') nextPhoto(e);
    if (e.key === 'ArrowLeft')  prevPhoto(e);
});
</script>
@endif
@endsection
