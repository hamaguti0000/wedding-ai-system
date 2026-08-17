@extends('layouts.app')
@section('title', 'ギャラリー | Wedding')

@push('styles')
<style>
main { padding: 0; text-align: initial; background: #fbfaf7; }

.gl-hero {
    position: relative; min-height: 360px; overflow: hidden;
    display: flex; align-items: flex-end; padding: 120px 20px 38px; box-sizing: border-box;
}
.gl-hero__img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; filter: brightness(0.48) saturate(0.86); }
.gl-hero__shade { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(21,17,13,.18), rgba(21,17,13,.72)); }
.gl-hero__inner { position: relative; z-index: 1; width: min(1080px, 100%); margin: 0 auto; color: #fff; }
.gl-hero__eyebrow { display: block; margin-bottom: 10px; color: rgba(255,255,255,.72); font-size: .7rem; letter-spacing: 5px; text-transform: uppercase; }
.gl-hero__title { margin: 0; font-family: 'Playfair Display', serif; font-size: clamp(2.1rem, 7vw, 4.4rem); font-weight: 400; letter-spacing: 1px; line-height: 1.05; }
.gl-hero__lead { max-width: 620px; margin: 16px 0 0; color: rgba(255,255,255,.86); font-size: .95rem; line-height: 1.9; }
.gl-hero__actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 24px; }
.gl-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px;
    padding: 0 18px; border-radius: 999px; text-decoration: none; border: 1px solid rgba(255,255,255,.38);
    color: #fff; background: rgba(255,255,255,.14); backdrop-filter: blur(12px); font-size: .88rem; font-weight: 600;
}
.gl-btn--gold { background: #b38b59; border-color: #b38b59; color: #fff; }
.gl-stats { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 20px; }
.gl-stat { min-width: 94px; padding: 10px 14px; border: 1px solid rgba(255,255,255,.28); border-radius: 12px; background: rgba(255,255,255,.1); backdrop-filter: blur(10px); }
.gl-stat strong { display: block; font-size: 1.25rem; line-height: 1; }
.gl-stat span { display: block; margin-top: 4px; color: rgba(255,255,255,.72); font-size: .7rem; }

.gl-wrap { width: min(1120px, calc(100% - 32px)); margin: 0 auto; padding: 34px 0 92px; }
.gl-toolbar {
    display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 12px;
    padding: 10px 12px; margin-bottom: 18px; border: 1px solid #eee5da; border-radius: 16px; background: rgba(255,255,255,.92);
    box-shadow: 0 10px 30px rgba(61,47,37,.07); backdrop-filter: blur(16px);
}
.gl-toolbar__main { min-width: 0; }
.gl-filter { display: flex; gap: 6px; overflow-x: auto; scrollbar-width: none; }
.gl-filter::-webkit-scrollbar { display: none; }
.gl-filter button {
    border: 1px solid #e7d6c1; background: #fff; color: #7a6048; border-radius: 999px; padding: 9px 13px;
    font-size: .82rem; font-weight: 700; white-space: nowrap; cursor: pointer;
}
.gl-filter button.is-active { background: #3d2f25; border-color: #3d2f25; color: #fff; }
.gl-count { margin-top: 9px; color: #8a7a68; font-size: .8rem; white-space: nowrap; }
.gl-toolbar__upload { min-height: 40px; padding: 0 14px; border-radius: 999px; background: #b38b59; color: #fff; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 7px; font-size: .82rem; font-weight: 800; white-space: nowrap; }

.gl-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 18px; align-items: start; }
.gl-card {
    overflow: hidden; border-radius: 18px; background: #fff; border: 1px solid #eee6dc;
    box-shadow: 0 12px 34px rgba(61,47,37,.08); cursor: pointer; transition: transform .2s ease, box-shadow .2s ease;
}
.gl-card:hover { transform: translateY(-3px); box-shadow: 0 18px 44px rgba(61,47,37,.13); }
.gl-card.is-hidden { display: none; }
.gl-card__photo { position: relative; aspect-ratio: 4 / 3; overflow: hidden; background: #efe9df; }
.gl-card__photo img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .35s ease; }
.gl-card:hover .gl-card__photo img { transform: scale(1.035); }
.gl-card__badge {
    position: absolute; left: 12px; top: 12px; display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 10px; border-radius: 999px; background: rgba(255,255,255,.9); color: #b42318; font-size: .75rem; font-weight: 700;
    box-shadow: 0 8px 22px rgba(0,0,0,.12);
}
.gl-card__body { padding: 12px 12px 14px; }
.gl-card__caption { margin: 0 0 10px; color: #3d2f25; font-size: .9rem; font-weight: 700; line-height: 1.55; }
.gl-card__caption.is-empty { color: #b0a090; font-weight: 500; }
.gl-card__tags { display: flex; flex-wrap: wrap; gap: 6px; max-height: 58px; overflow: hidden; }
.gl-person-chip {
    display: inline-flex; align-items: center; max-width: 100%; padding: 4px 9px; border-radius: 999px;
    background: #f7f1e9; color: #755f48; font-size: .72rem; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.gl-person-chip.is-current { background: #fff1f1; color: #b42318; border: 1px solid #ffd0d0; font-weight: 700; }
.gl-more { color: #aa9278; font-size: .72rem; align-self: center; }

.gl-empty { text-align: center; padding: 70px 20px; color: #a69583; background: #fff; border: 1px solid #eee6dc; border-radius: 18px; }
.gl-empty i { display: block; margin-bottom: 14px; font-size: 2.4rem; color: #d6c7b7; }

.gl-lightbox {
    position: fixed; inset: 0; z-index: 9000; display: grid; place-items: center; padding: 22px;
    background: rgba(17,12,8,.92); opacity: 0; pointer-events: none; transition: opacity .2s ease;
}
.gl-lightbox.is-open { opacity: 1; pointer-events: all; }
.gl-lightbox__shell { position: relative; width: min(1120px, 100%); max-height: 90vh; display: grid; grid-template-columns: minmax(0, 1fr) 310px; gap: 0; border-radius: 18px; overflow: hidden; background: #111; box-shadow: 0 26px 90px rgba(0,0,0,.48); }
.gl-lightbox__stage { display: grid; place-items: center; min-height: 420px; background: #050403; }
.gl-lightbox__img { max-width: 100%; max-height: 90vh; object-fit: contain; display: block; }
.gl-lightbox__info { padding: 24px; background: #fffaf3; color: #3d2f25; overflow-y: auto; }
.gl-lightbox__label { color: #b38b59; font-size: .68rem; letter-spacing: 3px; text-transform: uppercase; }
.gl-lightbox__caption { margin: 12px 0 18px; font-size: 1rem; font-weight: 700; line-height: 1.7; }
.gl-lightbox__tags { display: flex; flex-wrap: wrap; gap: 8px; }
.gl-lightbox__tag { display: inline-flex; align-items: center; gap: 6px; max-width: 100%; padding: 7px 11px; border-radius: 999px; background: #fff; border: 1px solid #eadccd; color: #755f48; text-decoration: none; font-size: .8rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gl-lightbox__tag.is-current { color: #b42318; border-color: #ffd0d0; background: #fff1f1; font-weight: 700; }
.gl-lightbox__close, .gl-lightbox__nav, .gl-lightbox__download {
    position: absolute; z-index: 2; border: 0; color: #fff; background: rgba(255,255,255,.15); backdrop-filter: blur(10px); cursor: pointer;
    width: 42px; height: 42px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;
}
.gl-lightbox__close { right: 14px; top: 14px; }
.gl-lightbox__download { left: 14px; top: 14px; }
.gl-lightbox__nav { top: 50%; transform: translateY(-50%); }
.gl-lightbox__prev { left: 14px; }
.gl-lightbox__next { right: 334px; }
.gl-mobile-upload { display: none; }

@media (max-width: 767px) {
    .gl-hero { min-height: 300px; padding: 102px 18px 24px; }
    .gl-hero__lead { font-size: .86rem; line-height: 1.75; }
    .gl-hero__actions { margin-top: 18px; }
    .gl-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
    .gl-stat { min-width: 0; padding: 8px 10px; border-radius: 12px; }
    .gl-wrap { width: min(100% - 20px, 1120px); padding-top: 18px; padding-bottom: 44px; }
    .gl-toolbar { grid-template-columns: 1fr; align-items: stretch; border-radius: 14px; margin-bottom: 14px; }
    .gl-toolbar__upload { min-height: 42px; width: 100%; box-sizing: border-box; }
    .gl-count { padding-left: 4px; }
    .gl-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .gl-card { border-radius: 14px; box-shadow: 0 8px 22px rgba(61,47,37,.07); }
    .gl-card__photo { aspect-ratio: 1 / 1; }
    .gl-card__body { padding: 9px; }
    .gl-card__caption { font-size: .78rem; margin-bottom: 7px; }
    .gl-card__tags { gap: 4px; max-height: 48px; }
    .gl-person-chip { padding: 3px 7px; font-size: .66rem; }
    .gl-lightbox { padding: 10px; }
    .gl-lightbox__shell { grid-template-columns: 1fr; max-height: 92vh; border-radius: 16px; }
    .gl-lightbox__stage { min-height: 58vh; }
    .gl-lightbox__img { max-height: 62vh; }
    .gl-lightbox__info { max-height: 30vh; padding: 18px; }
    .gl-lightbox__next { right: 14px; }

}
</style>
@endpush

@section('content')
@php
    $currentUserId = auth()->id();
    $taggedPhotoCount = $photos->filter(fn($photo) => $photo->taggedUsers->isNotEmpty())->count();
    $myPhotoCount = $currentUserId ? $photos->filter(fn($photo) => $photo->taggedUsers->contains('id', $currentUserId))->count() : 0;
@endphp

<section class="gl-hero">
    <img src="{{ ($bannerImage?->url ?? asset('img/チャペル.jpg')) }}" alt="" class="gl-hero__img">
    <div class="gl-hero__shade"></div>
    <div class="gl-hero__inner">
        <span class="gl-hero__eyebrow">Wedding Gallery</span>
        <h1 class="gl-hero__title">Photo Gallery</h1>
        <p class="gl-hero__lead">当日の写真を一覧で見られます。名前が紐付いている写真は、ゲストごとの思い出としても確認できます。</p>
        <div class="gl-hero__actions">
            <a href="{{ route('gallery.upload') }}" class="gl-btn gl-btn--gold"><i class="fa-solid fa-cloud-arrow-up"></i> 写真を投稿する</a>
            @if ($myPhotoCount > 0)
            <button type="button" class="gl-btn" data-filter-trigger="mine"><i class="fa-solid fa-user-check"></i> 自分の写真を見る</button>
            @endif
        </div>
        <div class="gl-stats">
            <div class="gl-stat"><strong>{{ $photos->count() }}</strong><span>公開写真</span></div>
            <div class="gl-stat"><strong>{{ $taggedPhotoCount }}</strong><span>人物タグあり</span></div>
            @if ($currentUserId)
            <div class="gl-stat"><strong>{{ $myPhotoCount }}</strong><span>あなたの写真</span></div>
            @endif
        </div>
    </div>
</section>

<div class="gl-wrap">
    @if ($photos->isEmpty())
    <div class="gl-empty">
        <i class="fa-regular fa-images"></i>
        <p>写真は準備中です</p>
    </div>
    @else
    <div class="gl-toolbar" aria-label="ギャラリーの絞り込み">
        <div class="gl-toolbar__main">
            <div class="gl-filter">
                <button type="button" class="is-active" data-filter="all">すべて</button>
                <button type="button" data-filter="tagged">人物タグあり</button>
                @if ($currentUserId)
                <button type="button" data-filter="mine">あなたの写真</button>
                @endif
            </div>
            <div class="gl-count" id="glCount"><strong>{{ $photos->count() }}</strong>枚表示</div>
        </div>
        <a href="{{ route('gallery.upload') }}" class="gl-toolbar__upload"><i class="fa-solid fa-cloud-arrow-up"></i> 投稿</a>
    </div>

    <div class="gl-grid" id="glGrid">
        @foreach ($photos as $i => $photo)
        @php
            $tagNames = $photo->taggedUsers->map(fn($u) => ['id' => $u->id, 'name' => $u->guestProfile?->fullName() ?: $u->name])->values();
            $isMine = $currentUserId && $photo->taggedUsers->contains('id', $currentUserId);
        @endphp
        <article class="gl-card" data-index="{{ $i }}" data-tagged="{{ $photo->taggedUsers->isNotEmpty() ? '1' : '0' }}" data-mine="{{ $isMine ? '1' : '0' }}" onclick="openLightbox({{ $i }})">
            <div class="gl-card__photo">
                <img src="{{ $photo->url }}" alt="{{ $photo->caption ?? '写真' }}" loading="lazy">
                @if ($isMine)
                <span class="gl-card__badge"><i class="fa-solid fa-heart"></i> あなた</span>
                @endif
            </div>
            @if ($photo->caption || $tagNames->isNotEmpty())
            <div class="gl-card__body">
                @if ($photo->caption)
                <p class="gl-card__caption">{{ $photo->caption }}</p>
                @endif
                @if ($tagNames->isNotEmpty())
                <div class="gl-card__tags">
                    @foreach ($tagNames->take(3) as $tag)
                    <span class="gl-person-chip {{ $currentUserId === $tag['id'] ? 'is-current' : '' }}">{{ $tag['name'] }}</span>
                    @endforeach
                    @if ($tagNames->count() > 3)
                    <span class="gl-more">+{{ $tagNames->count() - 3 }}名</span>
                    @endif
                </div>
                @endif
            </div>
            @endif
        </article>
        @endforeach
    </div>
    @endif
</div>

@if ($photos->isNotEmpty())
<div class="gl-lightbox" id="glLightbox" onclick="closeLightboxOnOverlay(event)">
    <div class="gl-lightbox__shell" onclick="event.stopPropagation()">
        <a class="gl-lightbox__download" id="glLightboxDownload" href="" download aria-label="ダウンロード"><i class="fa-solid fa-download"></i></a>
        <button class="gl-lightbox__close" type="button" onclick="closeLightbox()" aria-label="閉じる"><i class="fa-solid fa-xmark"></i></button>
        <button class="gl-lightbox__nav gl-lightbox__prev" type="button" onclick="prevPhoto()" aria-label="前へ"><i class="fa-solid fa-chevron-left"></i></button>
        <button class="gl-lightbox__nav gl-lightbox__next" type="button" onclick="nextPhoto()" aria-label="次へ"><i class="fa-solid fa-chevron-right"></i></button>
        <div class="gl-lightbox__stage"><img class="gl-lightbox__img" id="glLightboxImg" src="" alt=""></div>
        <aside class="gl-lightbox__info">
            <span class="gl-lightbox__label" id="glLightboxIndex">Photo</span>
            <p class="gl-lightbox__caption" id="glLightboxCaption"></p>
            <div class="gl-lightbox__tags" id="glLightboxTags"></div>
        </aside>
    </div>
</div>

@php
    $photosJson = $photos->map(function ($p) use ($currentUserId) {
        $tags = $p->taggedUsers->map(function ($u) use ($currentUserId) {
            return [
                'id' => $u->id,
                'name' => $u->guestProfile?->fullName() ?: $u->name,
                'is_current' => $currentUserId === $u->id,
            ];
        })->values();

        return ['url' => $p->url, 'caption' => $p->caption, 'tags' => $tags];
    })->values();
@endphp
<script>
const peopleBaseUrl = "{{ url('/people') }}";
const photos = @json($photosJson);
let current = 0;

function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
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
    document.getElementById('glLightboxCaption').textContent = p.caption || '';
    document.getElementById('glLightboxDownload').href = p.url;
    document.getElementById('glLightboxIndex').textContent = `Photo ${current + 1} / ${photos.length}`;
    const tagsEl = document.getElementById('glLightboxTags');
    tagsEl.innerHTML = (p.tags || []).length
        ? p.tags.map(t => `<a class="gl-lightbox__tag ${t.is_current ? 'is-current' : ''}" href="${peopleBaseUrl}/${t.id}"><i class="fa-solid fa-user"></i> ${escapeHtml(t.name)}</a>`).join('')
        : '<span class="gl-person-chip">人物タグはまだありません</span>';
}
function nextPhoto() { current = (current + 1) % photos.length; showPhoto(); }
function prevPhoto() { current = (current - 1 + photos.length) % photos.length; showPhoto(); }

function applyGalleryFilter(filter) {
    const cards = Array.from(document.querySelectorAll('.gl-card'));
    let visible = 0;
    cards.forEach(card => {
        const show = filter === 'all' || (filter === 'tagged' && card.dataset.tagged === '1') || (filter === 'mine' && card.dataset.mine === '1');
        card.classList.toggle('is-hidden', !show);
        if (show) visible++;
    });
    document.getElementById('glCount').innerHTML = `<strong>${visible}</strong>枚表示`;
    document.querySelectorAll('[data-filter]').forEach(btn => btn.classList.toggle('is-active', btn.dataset.filter === filter));
    document.getElementById('glGrid')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

document.querySelectorAll('[data-filter]').forEach(btn => btn.addEventListener('click', () => applyGalleryFilter(btn.dataset.filter)));
document.querySelectorAll('[data-filter-trigger]').forEach(btn => btn.addEventListener('click', () => applyGalleryFilter(btn.dataset.filterTrigger)));
document.addEventListener('keydown', e => {
    const lb = document.getElementById('glLightbox');
    if (!lb || !lb.classList.contains('is-open')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowRight') nextPhoto();
    if (e.key === 'ArrowLeft') prevPhoto();
});
</script>
@endif

@endsection
