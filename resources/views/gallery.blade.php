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
.gl-toolbar__actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
.gl-select-toggle { min-height: 40px; padding: 0 14px; border-radius: 999px; border: 1px solid #e7d6c1; background: #fff; color: #7a6048; display: inline-flex; align-items: center; justify-content: center; gap: 7px; font-size: .82rem; font-weight: 800; white-space: nowrap; cursor: pointer; }
.gl-select-toggle.is-active { background: #3d2f25; border-color: #3d2f25; color: #fff; }

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
.gl-card.is-selected { outline: 3px solid #b38b59; outline-offset: 2px; }
.gl-card__select { position: absolute; right: 10px; top: 10px; z-index: 2; width: 34px; height: 34px; border: 1px solid rgba(255,255,255,.78); border-radius: 999px; background: rgba(255,255,255,.9); display: none; align-items: center; justify-content: center; box-shadow: 0 8px 22px rgba(0,0,0,.12); }
.gl-card__select input { width: 20px; height: 20px; margin: 0; accent-color: #b38b59; }
body.gl-selecting .gl-card__select { display: inline-flex; }
body.gl-selecting .gl-card:hover { transform: none; }
body.gl-selecting .gl-card:hover .gl-card__photo img { transform: none; }
.gl-card__badge {
    position: absolute; left: 12px; top: 12px; display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 10px; border-radius: 999px; background: rgba(255,255,255,.9); color: #b42318; font-size: .75rem; font-weight: 700;
    box-shadow: 0 8px 22px rgba(0,0,0,.12);
}
.gl-card__body { padding: 12px 12px 14px; }
.gl-card__caption { margin: 0 0 10px; color: #3d2f25; font-size: .9rem; font-weight: 700; line-height: 1.55; }
.gl-card__uploader { display: inline-flex; align-items: center; gap: 6px; margin: 0 0 9px; color: #8c7965; font-size: .74rem; font-weight: 700; }
.gl-card__uploader i { color: #b38b59; font-size: .68rem; }
.gl-card__labels { display: flex; flex-wrap: wrap; gap: 5px; margin: 0 0 9px; }
.gl-card__label { display: inline-flex; align-items: center; gap: 5px; padding: 3px 8px; border-radius: 999px; background: #f7f1e9; color: #755f48; border: 1px solid #eadccd; font-size: .66rem; font-weight: 800; }
.gl-card__label--source { background: #eef7ff; border-color: #bfdbfe; color: #2563eb; }
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
.gl-selection-bar { position: fixed; left: 50%; bottom: calc(env(safe-area-inset-bottom) + 16px); z-index: 8990; width: min(640px, calc(100% - 24px)); transform: translate(-50%, 140%); opacity: 0; pointer-events: none; transition: transform .18s ease, opacity .18s ease; padding: 10px; border: 1px solid rgba(234,220,205,.82); border-radius: 18px; background: rgba(255,252,247,.97); box-shadow: 0 18px 48px rgba(61,47,37,.2); backdrop-filter: blur(18px); }
.gl-selection-bar.is-open { transform: translate(-50%, 0); opacity: 1; pointer-events: auto; }
.gl-selection-bar__main { display: grid; grid-template-columns: 1fr; gap: 9px; }
.gl-selection-bar__count { color: #3d2f25; font-size: .84rem; font-weight: 900; }
.gl-selection-bar__actions { display: grid; grid-template-columns: 1fr 1fr auto; gap: 8px; }
.gl-selection-action { min-height: 42px; border: 1px solid #e2d0bb; border-radius: 12px; background: #fff; color: #755f48; display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 0 10px; font-size: .78rem; font-weight: 900; white-space: nowrap; cursor: pointer; }
.gl-selection-action--primary { background: #3d2f25; border-color: #3d2f25; color: #fff; }
.gl-selection-action:disabled { opacity: .45; cursor: not-allowed; }
.gl-selection-status { min-height: 17px; color: #9b8573; font-size: .72rem; line-height: 1.35; }

.gl-lightbox {
    position: fixed; inset: 0; z-index: 9000; display: grid; place-items: center; padding: 22px;
    background: radial-gradient(circle at center, rgba(46,34,25,.94), rgba(12,9,7,.96));
    opacity: 0; pointer-events: none; transition: opacity .2s ease;
}
.gl-lightbox.is-open { opacity: 1; pointer-events: all; }
.gl-lightbox__shell {
    position: relative; width: min(1120px, 100%); max-height: 90vh;
    display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 0;
    border-radius: 22px; overflow: hidden; background: #0b0806;
    box-shadow: 0 26px 90px rgba(0,0,0,.48);
}
.gl-lightbox__stage {
    display: grid; place-items: center; min-height: 420px;
    background:
        linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,0)),
        #090705;
}
.gl-lightbox__img { max-width: 100%; max-height: 90vh; object-fit: contain; display: block; }
.gl-lightbox__info { padding: 24px; background: #fffaf3; color: #3d2f25; overflow-y: auto; }
.gl-lightbox__label { color: #b38b59; font-size: .68rem; letter-spacing: 3px; text-transform: uppercase; }
.gl-lightbox__caption { margin: 12px 0 12px; font-size: 1rem; font-weight: 700; line-height: 1.7; }
.gl-lightbox__uploader { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 12px; color: #806a55; font-size: .82rem; font-weight: 700; }
.gl-lightbox__uploader i { color: #b38b59; }
.gl-lightbox__labels { display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 14px; }
.gl-lightbox__info-head { display: block; }
.gl-lightbox__save-inline { display: none; }
.gl-lightbox__tags { display: flex; flex-wrap: wrap; gap: 8px; }
.gl-lightbox__tag { display: inline-flex; align-items: center; gap: 6px; max-width: 100%; padding: 7px 11px; border-radius: 999px; background: #fff; border: 1px solid #eadccd; color: #755f48; text-decoration: none; font-size: .8rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gl-lightbox__tag.is-current { color: #b42318; border-color: #ffd0d0; background: #fff1f1; font-weight: 700; }
.gl-lightbox__close, .gl-lightbox__nav, .gl-lightbox__download {
    position: absolute; z-index: 4; border: 0; color: #fff; background: rgba(29,22,17,.56); backdrop-filter: blur(14px); cursor: pointer;
    width: 44px; height: 44px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;
    box-shadow: 0 12px 28px rgba(0,0,0,.24);
}
.gl-lightbox__close { right: 14px; top: 14px; }
.gl-lightbox__download { left: 14px; top: 14px; }
.gl-lightbox__topcount { display: none; }
.gl-save-sheet { position: absolute; inset: 0; z-index: 8; display: grid; place-items: end center; padding: 18px; pointer-events: none; opacity: 0; transition: opacity .16s ease; }
.gl-save-sheet.is-open { pointer-events: auto; opacity: 1; }
.gl-save-sheet__backdrop { position: absolute; inset: 0; background: rgba(8,6,5,.46); }
.gl-save-sheet__panel { position: relative; width: min(360px, 100%); padding: 10px; border-radius: 18px; background: rgba(255,252,247,.98); border: 1px solid rgba(234,220,205,.8); box-shadow: 0 22px 58px rgba(0,0,0,.32); }
.gl-save-sheet__title { margin: 4px 8px 10px; color: #806a55; font-size: .72rem; font-weight: 800; letter-spacing: 1.8px; text-transform: uppercase; }
.gl-save-option { width: 100%; min-height: 48px; border: 0; border-radius: 12px; background: transparent; color: #3d2f25; display: flex; align-items: center; gap: 11px; padding: 8px 10px; font-size: .9rem; font-weight: 800; text-align: left; cursor: pointer; }
.gl-save-option i { width: 34px; height: 34px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; background: #f3e6d5; color: #a77942; }
.gl-save-option span { display: grid; gap: 2px; }
.gl-save-option small { color: #9b8573; font-size: .7rem; font-weight: 600; line-height: 1.35; }
.gl-save-option:hover { background: #fff6ea; }
.gl-save-sheet__status { min-height: 18px; margin: 6px 8px 2px; color: #9b8573; font-size: .72rem; line-height: 1.45; }
.gl-lightbox__nav { top: 50%; transform: translateY(-50%); }
.gl-lightbox__prev { left: 14px; }
.gl-lightbox__next { right: 344px; }
.gl-mobile-upload { display: none; }

@media (max-width: 767px) {
    .gl-hero { min-height: 300px; padding: 102px 18px 24px; }
    .gl-hero__lead { font-size: .86rem; line-height: 1.75; }
    .gl-hero__actions { margin-top: 18px; }
    .gl-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
    .gl-stat { min-width: 0; padding: 8px 10px; border-radius: 12px; }
    .gl-wrap { width: min(100% - 20px, 1120px); padding-top: 18px; padding-bottom: 44px; }
    .gl-toolbar { grid-template-columns: 1fr; align-items: stretch; border-radius: 14px; margin-bottom: 14px; }
    .gl-toolbar__actions { width: 100%; display: grid; grid-template-columns: 1fr 1fr; }
    .gl-toolbar__upload, .gl-select-toggle { min-height: 42px; width: 100%; box-sizing: border-box; }
    .gl-count { padding-left: 4px; }
    .gl-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .gl-card { border-radius: 14px; box-shadow: 0 8px 22px rgba(61,47,37,.07); }
    .gl-card__photo { aspect-ratio: 1 / 1; }
    .gl-card__body { padding: 9px; }
    .gl-card__caption { font-size: .78rem; margin-bottom: 7px; }
    .gl-card__uploader { font-size: .68rem; margin-bottom: 7px; }
    .gl-card__tags { gap: 4px; max-height: 48px; }
    .gl-person-chip { padding: 3px 7px; font-size: .66rem; }
    .gl-lightbox {
        display: block;
        padding: 0;
        background: #100c09;
    }
    .gl-lightbox__shell {
        width: 100%;
        height: 100dvh;
        max-height: none;
        display: grid;
        grid-template-columns: 1fr;
        grid-template-rows: 1fr auto;
        border-radius: 0;
        overflow: hidden;
        background: #100c09;
        box-shadow: none;
    }
    .gl-lightbox__shell::before {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        height: 104px;
        z-index: 3;
        pointer-events: none;
        background: linear-gradient(180deg, rgba(16,12,9,.84), rgba(16,12,9,0));
    }
    .gl-lightbox__stage {
        position: relative;
        min-height: 0;
        height: 100%;
        padding: calc(env(safe-area-inset-top) + 66px) 12px 188px;
        box-sizing: border-box;
        background:
            radial-gradient(circle at 50% 38%, rgba(255,255,255,.055), transparent 42%),
            #100c09;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .gl-lightbox__stage::after {
        content: '読み込み中';
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        color: rgba(255,255,255,.58);
        font-size: .78rem;
        letter-spacing: 2px;
        opacity: 0;
        transition: opacity .12s ease;
        pointer-events: none;
    }
    .gl-lightbox__stage.is-loading::after { opacity: 1; }
    .gl-lightbox__stage.is-error::after {
        content: '写真を読み込めませんでした';
        opacity: 1;
        color: rgba(255,255,255,.76);
    }
    .gl-lightbox__img {
        width: auto;
        height: auto;
        max-width: calc(100vw - 20px);
        max-height: calc(100dvh - env(safe-area-inset-top) - env(safe-area-inset-bottom) - 254px);
        object-fit: contain;
        border-radius: 14px;
        box-shadow: 0 18px 54px rgba(0,0,0,.32);
        background: transparent;
        opacity: 1;
        transition: opacity .14s ease;
    }
    .gl-lightbox__img.is-loading,
    .gl-lightbox__img.is-error { opacity: 0; }
    .gl-lightbox__info {
        position: absolute;
        left: 12px;
        right: 12px;
        bottom: calc(env(safe-area-inset-bottom) + 18px);
        z-index: 4;
        max-height: 152px;
        padding: 13px 14px 14px;
        border: 1px solid rgba(234,220,205,.78);
        border-radius: 16px;
        background: rgba(255,252,247,.97);
        color: #3d2f25;
        overflow-y: auto;
        box-shadow: 0 18px 42px rgba(0,0,0,.24);
        backdrop-filter: blur(18px);
    }
    .gl-lightbox__label {
        display: block;
        margin-bottom: 0;
        color: #9a7445;
        font-size: .64rem;
        letter-spacing: 2.5px;
    }
    .gl-lightbox__info-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }
    .gl-lightbox__save-inline {
        border: 1px solid #e2d0bb;
        border-radius: 999px;
        background: #3d2f25;
        color: #fff;
        min-height: 34px;
        padding: 0 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: .76rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .gl-lightbox__caption {
        margin: 0 0 10px;
        font-size: .9rem;
        line-height: 1.55;
    }
    .gl-lightbox__caption:empty { display: none; }
    .gl-lightbox__uploader { margin-bottom: 10px; font-size: .76rem; }
    .gl-lightbox__labels { gap: 6px; margin-bottom: 10px; }
    .gl-lightbox__labels .gl-card__label { max-width: 100%; }
    .gl-lightbox__tags {
        flex-wrap: wrap;
        overflow: visible;
        gap: 7px;
        padding-bottom: 1px;
    }
    .gl-lightbox__tag,
    .gl-lightbox__tags .gl-person-chip {
        min-width: 0;
        max-width: 100%;
        padding: 8px 11px;
        font-size: .78rem;
        background: rgba(255,255,255,.9);
    }
    .gl-lightbox__close,
    .gl-lightbox__download {
        top: calc(env(safe-area-inset-top) + 14px);
        background: rgba(255,255,255,.16);
        border: 1px solid rgba(255,255,255,.14);
    }
    .gl-lightbox__close { right: 14px; }
    .gl-lightbox__download { display: none; }
    .gl-lightbox__topcount {
        display: inline-flex;
        position: absolute;
        left: 74px;
        right: 74px;
        top: calc(env(safe-area-inset-top) + 17px);
        z-index: 5;
        min-height: 38px;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,.9);
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        pointer-events: none;
    }
    .gl-lightbox__nav { display: none; }

}
</style>
@endpush

@section('content')
@php
    $currentUserId = auth()->id();
    $currentUserGroupIds = isset($currentUserGroupIds) ? collect($currentUserGroupIds) : collect();
    $taggedPhotoCount = $photos->filter(fn($photo) => $photo->taggedUsers->isNotEmpty() || $photo->taggedGroups->isNotEmpty())->count();
    $myPhotoCount = $currentUserId ? $photos->filter(fn($photo) => $photo->taggedUsers->contains('id', $currentUserId))->count() : 0;
    $relatedPhotoCount = $currentUserId ? $photos->filter(fn($photo) => $photo->taggedUsers->contains('id', $currentUserId) || $photo->taggedGroups->pluck('id')->intersect($currentUserGroupIds)->isNotEmpty())->count() : 0;
    $defaultFilter = $relatedPhotoCount > 0 ? 'related' : 'all';
@endphp

<section class="gl-hero">
    <img src="{{ ($bannerImage?->url ?? asset('img/チャペル.jpg')) }}" alt="" class="gl-hero__img">
    <div class="gl-hero__shade"></div>
    <div class="gl-hero__inner">
        <span class="gl-hero__eyebrow">Wedding Gallery</span>
        <h1 class="gl-hero__title">Photo Gallery</h1>
        <p class="gl-hero__lead">自分が写っている写真や、同じグループの写真を見つけやすくしました。</p>
        <div class="gl-hero__actions">
            <a href="{{ route('gallery.upload') }}" class="gl-btn gl-btn--gold"><i class="fa-solid fa-cloud-arrow-up"></i> 写真を投稿する</a>
            @if ($relatedPhotoCount > 0)
            <button type="button" class="gl-btn" data-filter-trigger="related"><i class="fa-solid fa-user-check"></i> あなた関連を見る</button>
            @endif
        </div>
        <div class="gl-stats">
            <div class="gl-stat"><strong>{{ $photos->count() }}</strong><span>公開写真</span></div>
            <div class="gl-stat"><strong>{{ $taggedPhotoCount }}</strong><span>人物タグあり</span></div>
            @if ($currentUserId)
            <div class="gl-stat"><strong>{{ $relatedPhotoCount }}</strong><span>あなた関連</span></div>
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
                @if ($currentUserId)
                <button type="button" class="{{ $defaultFilter === 'related' ? 'is-active' : '' }}" data-filter="related">あなた関連</button>
                <button type="button" data-filter="mine">自分が写ってる</button>
                @endif
                <button type="button" class="{{ $defaultFilter === 'all' ? 'is-active' : '' }}" data-filter="all">すべて</button>
                <button type="button" data-filter="ceremony">挙式</button>
                <button type="button" data-filter="reception">披露宴</button>
                <button type="button" data-filter="photographer">カメラマン</button>
                <button type="button" data-filter="tagged">人物タグあり</button>
            </div>
            <div class="gl-count" id="glCount"><strong>{{ $photos->count() }}</strong>枚表示</div>
        </div>
        <div class="gl-toolbar__actions">
            <button type="button" class="gl-select-toggle" id="gallerySelectToggle"><i class="fa-regular fa-square-check"></i> 選択</button>
            <a href="{{ route('gallery.upload') }}" class="gl-toolbar__upload"><i class="fa-solid fa-cloud-arrow-up"></i> 投稿</a>
        </div>
    </div>

    <div class="gl-grid" id="glGrid">
        @foreach ($photos as $i => $photo)
        @php
            $groupTags = $photo->taggedGroups->map(fn($g) => ['id' => $g->id, 'name' => $g->galleryDisplayName(), 'type' => 'group'])->unique('name')->values();
            $userTags = $photo->taggedUsers->map(fn($u) => ['id' => $u->id, 'name' => $u->guestProfile?->fullName() ?: $u->name, 'type' => 'user'])->values();
            $tagNames = $groupTags->concat($userTags)->values();
            $isMine = $currentUserId && $photo->taggedUsers->contains('id', $currentUserId);
            $isRelated = $isMine || ($currentUserId && $photo->taggedGroups->pluck('id')->intersect($currentUserGroupIds)->isNotEmpty());
            $uploaderName = $photo->uploader?->guestProfile?->fullName() ?: $photo->uploader?->name;
        @endphp
        <article class="gl-card" data-index="{{ $i }}" data-photo-id="{{ $photo->id }}" data-tagged="{{ ($photo->taggedUsers->isNotEmpty() || $photo->taggedGroups->isNotEmpty()) ? '1' : '0' }}" data-mine="{{ $isMine ? '1' : '0' }}" data-related="{{ $isRelated ? '1' : '0' }}" data-category="{{ $photo->gallery_category ?: 'other' }}" data-source="{{ $photo->photo_source ?: ($photo->is_guest_upload ? 'guest' : 'admin') }}" onclick="handleCardClick(event, {{ $i }})">
            <div class="gl-card__photo">
                <label class="gl-card__select" aria-label="写真を選択" onclick="event.stopPropagation()"><input type="checkbox" data-photo-select value="{{ $photo->id }}"></label>
                <img src="{{ $photo->url }}" alt="{{ $photo->caption ?? '写真' }}" loading="lazy">
                @if ($isMine)
                <span class="gl-card__badge"><i class="fa-solid fa-heart"></i> あなた</span>
                @endif
            </div>
            @if ($photo->caption || $uploaderName || $tagNames->isNotEmpty())
            <div class="gl-card__body">
                <div class="gl-card__labels">
                    <span class="gl-card__label"><i class="fa-solid fa-layer-group"></i>{{ $photo->categoryLabel() }}</span>
                    <span class="gl-card__label gl-card__label--source"><i class="fa-solid {{ $photo->photo_source === 'photographer' ? 'fa-camera-retro' : ($photo->is_guest_upload ? 'fa-user' : 'fa-camera') }}"></i>{{ $photo->sourceLabel() }}</span>
                </div>
                @if ($uploaderName)
                <p class="gl-card__uploader"><i class="fa-solid fa-camera"></i>{{ $uploaderName }} さんが投稿</p>
                @endif
                @if ($photo->caption)
                <p class="gl-card__caption">{{ $photo->caption }}</p>
                @endif
                @if ($tagNames->isNotEmpty())
                <div class="gl-card__tags">
                    @foreach ($tagNames->take(3) as $tag)
                    <span class="gl-person-chip {{ ($tag['type'] ?? 'user') === 'user' && $currentUserId === $tag['id'] ? 'is-current' : '' }}">{{ $tag['name'] }}</span>
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
<div class="gl-selection-bar" id="gallerySelectionBar" aria-live="polite">
    <div class="gl-selection-bar__main">
        <div class="gl-selection-bar__count"><span id="gallerySelectedCount">0</span>枚を選択中</div>
        <div class="gl-selection-bar__actions">
            <button type="button" class="gl-selection-action gl-selection-action--primary" id="bulkSavePhotos"><i class="fa-solid fa-images"></i>写真へ保存</button>
            <button type="button" class="gl-selection-action" id="bulkSaveFiles"><i class="fa-solid fa-file-zipper"></i>ZIP保存</button>
            <button type="button" class="gl-selection-action" id="bulkClearSelection" aria-label="選択解除"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="gl-selection-status" id="bulkSaveStatus"></div>
    </div>
</div>
<form method="POST" action="{{ route('gallery.download') }}" id="bulkDownloadForm" hidden>
    @csrf
</form>

<div class="gl-lightbox" id="glLightbox" onclick="closeLightboxOnOverlay(event)">
    <div class="gl-lightbox__shell" onclick="event.stopPropagation()">
        <button class="gl-lightbox__download" id="glLightboxDownload" type="button" aria-label="ダウンロード"><i class="fa-solid fa-download"></i></button>
        <span class="gl-lightbox__topcount" id="glLightboxTopIndex">Photo</span>
        <button class="gl-lightbox__close" type="button" onclick="closeLightbox()" aria-label="閉じる"><i class="fa-solid fa-xmark"></i></button>
        <button class="gl-lightbox__nav gl-lightbox__prev" type="button" onclick="prevPhoto()" aria-label="前へ"><i class="fa-solid fa-chevron-left"></i></button>
        <button class="gl-lightbox__nav gl-lightbox__next" type="button" onclick="nextPhoto()" aria-label="次へ"><i class="fa-solid fa-chevron-right"></i></button>
        <div class="gl-lightbox__stage"><img class="gl-lightbox__img" id="glLightboxImg" src="" alt=""></div>
        <aside class="gl-lightbox__info">
            <div class="gl-lightbox__info-head">
                <span class="gl-lightbox__label" id="glLightboxIndex">Photo</span>
                <button type="button" class="gl-lightbox__save-inline" id="glLightboxSaveInline"><i class="fa-solid fa-download"></i>保存</button>
            </div>
            <p class="gl-lightbox__caption" id="glLightboxCaption"></p>
            <div class="gl-lightbox__labels" id="glLightboxLabels"></div>
            <div class="gl-lightbox__uploader" id="glLightboxUploader"></div>
            <div class="gl-lightbox__tags" id="glLightboxTags"></div>
        </aside>
        <div class="gl-save-sheet" id="glSaveSheet" aria-hidden="true">
            <div class="gl-save-sheet__backdrop" data-save-close></div>
            <div class="gl-save-sheet__panel" role="dialog" aria-modal="true" aria-label="保存方法を選択">
                <p class="gl-save-sheet__title">保存方法を選択</p>
                <button type="button" class="gl-save-option" id="saveToPhotos">
                    <i class="fa-solid fa-images"></i>
                    <span>写真アプリへ保存<small>共有シートから「画像を保存」を選びます</small></span>
                </button>
                <button type="button" class="gl-save-option" id="saveToFiles">
                    <i class="fa-solid fa-folder-arrow-down"></i>
                    <span>ファイルに保存<small>ブラウザのダウンロードとして保存します</small></span>
                </button>
                <button type="button" class="gl-save-option" data-save-close>
                    <i class="fa-solid fa-xmark"></i>
                    <span>閉じる<small>写真を見る画面に戻ります</small></span>
                </button>
                <p class="gl-save-sheet__status" id="saveSheetStatus" aria-live="polite"></p>
            </div>
        </div>
    </div>
</div>

@php
    $photosJson = $photos->map(function ($p) use ($currentUserId, $currentUserGroupIds) {
        $tags = $p->taggedGroups->map(function ($g) use ($currentUserGroupIds) {
            return [
                'id' => $g->id,
                'name' => $g->galleryDisplayName(),
                'is_current' => $currentUserGroupIds->contains($g->id),
                'type' => 'group',
            ];
        })->unique('name')->concat($p->taggedUsers->map(function ($u) use ($currentUserId) {
            return [
                'id' => $u->id,
                'name' => $u->guestProfile?->fullName() ?: $u->name,
                'is_current' => $currentUserId === $u->id,
                'type' => 'user',
            ];
        }))->values();
        $uploaderName = $p->uploader?->guestProfile?->fullName() ?: $p->uploader?->name;

        return [
            'id' => $p->id,
            'url' => $p->url,
            'caption' => $p->caption,
            'tags' => $tags,
            'uploader' => $uploaderName,
            'category' => $p->gallery_category ?: 'other',
            'category_label' => $p->categoryLabel(),
            'source' => $p->photo_source ?: ($p->is_guest_upload ? 'guest' : 'admin'),
            'source_label' => $p->sourceLabel(),
            'download_name' => 'wedding-photo-' . $p->id . '.jpg',
        ];
    })->values();
@endphp
<script>
const peopleBaseUrl = "{{ url('/people') }}";
const photos = @json($photosJson);
const defaultGalleryFilter = @json($defaultFilter);
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
function scrollToCurrentCard() {
    const card = document.querySelector(`.gl-card[data-index="${current}"]`);
    if (!card || card.classList.contains('is-hidden')) return;
    window.setTimeout(() => {
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 80);
}

function closeLightbox(shouldScroll = true) {
    closeSaveSheet();
    document.getElementById('glLightbox').classList.remove('is-open');
    document.body.style.overflow = '';
    if (shouldScroll) scrollToCurrentCard();
}
function closeLightboxOnOverlay(e) {
    if (e.target === document.getElementById('glLightbox')) closeLightbox();
}
function handleCardClick(event, index) {
    const card = event.currentTarget;
    if (document.body.classList.contains('gl-selecting')) {
        const checkbox = card.querySelector('[data-photo-select]');
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            updateSelectionState();
        }
        return;
    }
    openLightbox(index);
}
function showPhoto() {
    const p = photos[current];
    const img = document.getElementById('glLightboxImg');
    const stage = img.closest('.gl-lightbox__stage');
    stage?.classList.remove('is-error');
    stage?.classList.add('is-loading');
    img.classList.remove('is-error');
    img.classList.add('is-loading');
    img.onload = () => {
        img.classList.remove('is-loading');
        stage?.classList.remove('is-loading');
    };
    img.onerror = () => {
        img.classList.remove('is-loading');
        img.classList.add('is-error');
        stage?.classList.remove('is-loading');
        stage?.classList.add('is-error');
    };
    img.src = p.url;
    document.getElementById('glLightboxCaption').textContent = p.caption || '';
    document.getElementById('glLightboxDownload').dataset.url = p.url;
    document.getElementById('glLightboxDownload').dataset.filename = p.download_name || `wedding-photo-${current + 1}.jpg`;
    const labelsEl = document.getElementById('glLightboxLabels');
    labelsEl.innerHTML = `<span class="gl-card__label"><i class="fa-solid fa-layer-group"></i>${escapeHtml(p.category_label || 'その他')}</span><span class="gl-card__label gl-card__label--source"><i class="fa-solid ${p.source === 'photographer' ? 'fa-camera-retro' : (p.source === 'guest' ? 'fa-user' : 'fa-camera')}"></i>${escapeHtml(p.source_label || '')}</span>`;
    const uploaderEl = document.getElementById('glLightboxUploader');
    uploaderEl.innerHTML = p.uploader ? `<i class="fa-solid fa-camera"></i>${escapeHtml(p.uploader)} さんが投稿` : '';
    document.getElementById('glLightboxIndex').textContent = `Photo ${current + 1} / ${photos.length}`;
    document.getElementById('glLightboxTopIndex').textContent = `${current + 1} / ${photos.length}`;
    const tagsEl = document.getElementById('glLightboxTags');
    tagsEl.innerHTML = (p.tags || []).length
        ? p.tags.map(t => `<a class="gl-lightbox__tag ${t.is_current ? 'is-current' : ''}" href="${t.type === 'group' ? '#' : peopleBaseUrl + '/' + t.id}"><i class="fa-solid fa-user"></i> ${escapeHtml(t.name)}</a>`).join('')
        : '<span class="gl-person-chip">人物タグはまだありません</span>';
}
function nextPhoto() { current = (current + 1) % photos.length; showPhoto(); }
function prevPhoto() { current = (current - 1 + photos.length) % photos.length; showPhoto(); }

function openSaveSheet() {
    const sheet = document.getElementById('glSaveSheet');
    const status = document.getElementById('saveSheetStatus');
    if (status) status.textContent = '';
    sheet?.classList.add('is-open');
    sheet?.setAttribute('aria-hidden', 'false');
}

function closeSaveSheet() {
    const sheet = document.getElementById('glSaveSheet');
    sheet?.classList.remove('is-open');
    sheet?.setAttribute('aria-hidden', 'true');
}

function currentDownloadMeta() {
    const btn = document.getElementById('glLightboxDownload');
    return {
        url: btn?.dataset.url || photos[current]?.url || '',
        filename: btn?.dataset.filename || `wedding-photo-${current + 1}.jpg`,
    };
}

async function fetchPhotoFile(url, filename) {
    const response = await fetch(url, { cache: 'no-store' });
    if (!response.ok) throw new Error('写真を取得できませんでした');
    const blob = await response.blob();
    const type = blob.type || 'image/jpeg';
    return new File([blob], filename, { type });
}

function downloadToFiles() {
    const { url, filename } = currentDownloadMeta();
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    closeSaveSheet();
}

async function saveToPhotos() {
    const status = document.getElementById('saveSheetStatus');
    const { url, filename } = currentDownloadMeta();
    if (status) status.textContent = '共有シートを準備しています...';

    try {
        const file = await fetchPhotoFile(url, filename);
        if (navigator.canShare && navigator.canShare({ files: [file] }) && navigator.share) {
            await navigator.share({ files: [file], title: 'Wedding photo' });
            closeSaveSheet();
            return;
        }
        if (navigator.share) {
            await navigator.share({ title: 'Wedding photo', url });
            closeSaveSheet();
            return;
        }
        if (status) status.textContent = 'このブラウザでは写真アプリ保存を直接開けません。ファイル保存を使ってください。';
    } catch (error) {
        if (status) status.textContent = error.message || '共有シートを開けませんでした。';
    }
}

function selectedPhotoIds() {
    return Array.from(document.querySelectorAll('[data-photo-select]:checked')).map(input => Number(input.value));
}

function selectedPhotos() {
    const ids = new Set(selectedPhotoIds());
    return photos.filter(photo => ids.has(Number(photo.id)));
}

function setSelectionMode(enabled) {
    document.body.classList.toggle('gl-selecting', enabled);
    document.getElementById('gallerySelectToggle')?.classList.toggle('is-active', enabled);
    if (!enabled) {
        document.querySelectorAll('[data-photo-select]').forEach(input => input.checked = false);
    }
    updateSelectionState();
}

function updateSelectionState() {
    const count = selectedPhotoIds().length;
    document.querySelectorAll('.gl-card').forEach(card => {
        const input = card.querySelector('[data-photo-select]');
        card.classList.toggle('is-selected', Boolean(input?.checked));
    });
    const bar = document.getElementById('gallerySelectionBar');
    const countEl = document.getElementById('gallerySelectedCount');
    if (countEl) countEl.textContent = count;
    bar?.classList.toggle('is-open', count > 0);
    document.getElementById('bulkSavePhotos')?.toggleAttribute('disabled', count === 0);
    document.getElementById('bulkSaveFiles')?.toggleAttribute('disabled', count === 0);
    if (count === 0) document.getElementById('bulkSaveStatus').textContent = '';
}

async function saveSelectedToPhotos() {
    const chosen = selectedPhotos();
    const status = document.getElementById('bulkSaveStatus');
    if (!chosen.length) return;
    if (status) status.textContent = '共有シートを準備しています...';

    try {
        const files = [];
        for (const photo of chosen.slice(0, 30)) {
            files.push(await fetchPhotoFile(photo.url, photo.download_name || `wedding-photo-${photo.id}.jpg`));
        }
        if (navigator.canShare && navigator.canShare({ files }) && navigator.share) {
            await navigator.share({ files, title: 'Wedding photos' });
            if (status) status.textContent = '共有シートを開きました';
            return;
        }
        if (status) status.textContent = 'このブラウザでは複数写真の写真アプリ保存に対応していません。ZIP保存を使ってください。';
    } catch (error) {
        if (status) status.textContent = error.message || '複数写真の保存を準備できませんでした。';
    }
}

function saveSelectedToFiles() {
    const ids = selectedPhotoIds();
    if (!ids.length) return;
    const form = document.getElementById('bulkDownloadForm');
    form.querySelectorAll('input[name="photo_ids[]"]').forEach(input => input.remove());
    ids.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'photo_ids[]';
        input.value = id;
        form.appendChild(input);
    });
    form.submit();
}

function applyGalleryFilter(filter, shouldScroll = true) {
    const cards = Array.from(document.querySelectorAll('.gl-card'));
    let visible = 0;
    cards.forEach(card => {
        const show = filter === 'all' || (filter === 'tagged' && card.dataset.tagged === '1') || (filter === 'mine' && card.dataset.mine === '1') || (filter === 'related' && card.dataset.related === '1') || (filter === 'ceremony' && card.dataset.category === 'ceremony') || (filter === 'reception' && card.dataset.category === 'reception') || (filter === 'photographer' && card.dataset.source === 'photographer');
        card.classList.toggle('is-hidden', !show);
        if (show) visible++;
    });
    document.getElementById('glCount').innerHTML = `<strong>${visible}</strong>枚表示`;
    document.querySelectorAll('[data-filter]').forEach(btn => btn.classList.toggle('is-active', btn.dataset.filter === filter));
    if (shouldScroll) {
        document.getElementById('glGrid')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

document.querySelectorAll('[data-filter]').forEach(btn => btn.addEventListener('click', () => applyGalleryFilter(btn.dataset.filter)));
document.querySelectorAll('[data-filter-trigger]').forEach(btn => btn.addEventListener('click', () => applyGalleryFilter(btn.dataset.filterTrigger)));
if (defaultGalleryFilter !== 'all') applyGalleryFilter(defaultGalleryFilter, false);
document.getElementById('glLightboxDownload')?.addEventListener('click', openSaveSheet);
document.getElementById('glLightboxSaveInline')?.addEventListener('click', openSaveSheet);
document.getElementById('saveToFiles')?.addEventListener('click', downloadToFiles);
document.getElementById('saveToPhotos')?.addEventListener('click', saveToPhotos);
document.querySelectorAll('[data-save-close]').forEach(el => el.addEventListener('click', closeSaveSheet));
document.getElementById('gallerySelectToggle')?.addEventListener('click', () => setSelectionMode(!document.body.classList.contains('gl-selecting')));
document.querySelectorAll('[data-photo-select]').forEach(input => input.addEventListener('change', updateSelectionState));
document.getElementById('bulkClearSelection')?.addEventListener('click', () => setSelectionMode(false));
document.getElementById('bulkSavePhotos')?.addEventListener('click', saveSelectedToPhotos);
document.getElementById('bulkSaveFiles')?.addEventListener('click', saveSelectedToFiles);

(function () {
    const lightbox = document.getElementById('glLightbox');
    const stage = document.querySelector('.gl-lightbox__stage');
    if (!lightbox || !stage) return;

    let startX = 0;
    let startY = 0;
    stage.addEventListener('click', event => {
        if (event.target === stage) closeLightbox();
    });
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
    if (!lb || !lb.classList.contains('is-open')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowRight') nextPhoto();
    if (e.key === 'ArrowLeft') prevPhoto();
});
</script>
@endif

@endsection
