@extends('layouts.app')
@section('title', 'ギャラリー管理 | Admin')

@push('styles')
<style>
.upload-zone {
    border: 2px dashed #e8d5b7; border-radius: 10px;
    padding: 32px 20px; text-align: center; background: #fffdf9;
    cursor: pointer; transition: border-color 0.15s, background 0.15s;
    position: relative;
}
.upload-zone:hover, .upload-zone.drag-over { border-color: #b38b59; background: #fef9f0; }
.upload-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
.upload-zone__icon { font-size: 2rem; color: #c0b0a0; margin-bottom: 8px; }
.upload-zone__text { font-size: 0.88rem; color: #9b8573; }
.upload-zone__sub  { font-size: 0.74rem; color: #b0a090; margin-top: 4px; }

.photo-previews { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.photo-preview { width: 80px; height: 80px; border-radius: 6px; object-fit: cover; border: 1px solid #e8d5b7; }

.gl-admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 14px;
    margin-top: 8px;
}
.official-section {
    margin-top: 22px;
    border: 1px solid #efe3d4;
    border-radius: 16px;
    background: #fffdf9;
    box-shadow: 0 10px 28px rgba(61,47,37,.06);
    overflow: hidden;
}
.official-section[open] { padding-bottom: 14px; }
.official-section__summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 16px 18px;
    cursor: pointer;
    list-style: none;
    user-select: none;
}
.official-section__summary::-webkit-details-marker { display: none; }
.official-section__title {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}
.official-section__icon {
    width: 34px;
    height: 34px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #f6ead8;
    color: #b38b59;
    flex: 0 0 auto;
}
.official-section__title strong {
    display: block;
    color: #3d2f25;
    font-size: .94rem;
}
.official-section__copy {
    display: block;
    margin-top: 2px;
    color: #9b8573;
    font-size: .76rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.official-section__meta {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 0 0 auto;
}
.official-section__pill {
    border: 1px solid #e8d5b7;
    border-radius: 999px;
    padding: 5px 10px;
    color: #7a5b32;
    background: #fff;
    font-size: .76rem;
    font-weight: 700;
}
.official-section__chevron {
    color: #b38b59;
    transition: transform .18s ease;
}
.official-section[open] .official-section__chevron { transform: rotate(180deg); }
.official-section__inner { padding: 0 18px; }
.gl-admin-item {
    background: #fff; border-radius: 10px; overflow: hidden;
    border: 1px solid #f0ebe3; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    display: flex; flex-direction: column; min-width: 0;
}
.gl-admin-item.inactive { opacity: 0.5; }
.gl-admin-item__img { width: 100%; height: 130px; object-fit: cover; display: block; flex: 0 0 auto; }
.gl-admin-item__body { padding: 10px 12px; }
.gl-admin-item__caption { font-size: 0.78rem; color: #7a6a5a; margin: 0 0 8px; line-height: 1.5; min-height: 1.5em; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.gl-admin-item__actions { display: flex; gap: 4px; flex-wrap: wrap; }
.gl-admin-guide {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin: 0 0 20px;
}
.gl-admin-guide__card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border: 1px solid #eadccd;
    border-radius: 14px;
    background: linear-gradient(135deg, #fffdf9 0%, #fff8ee 100%);
    box-shadow: 0 8px 24px rgba(61,47,37,.05);
    color: #5d4635;
    text-decoration: none;
}
.gl-admin-guide__icon {
    width: 42px;
    height: 42px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    background: #f1e3ce;
    color: #9b6d35;
}
.gl-admin-guide__card strong {
    display: block;
    margin-bottom: 3px;
    color: #3d2f25;
    font-size: .9rem;
}
.gl-admin-guide__card span {
    display: block;
    color: #8a7969;
    font-size: .76rem;
    line-height: 1.55;
}
.gl-admin-guide__card:hover { border-color: #c9a56f; }
.gl-admin-item__tag-btn {
    gap: 5px;
    min-width: 82px;
    padding-left: 9px;
    padding-right: 9px;
}

/* ゲスト投稿承認セクション */
.pending-section {
    background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px;
    padding: 20px 24px; margin-bottom: 32px;
}
.pending-section h3 { font-size: 0.95rem; font-weight: 600; color: #92400e; margin-bottom: 4px; }
.pending-section .section-desc { font-size: 0.8rem; color: #a16207; margin-bottom: 16px; }
.pending-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 14px;
}
.pending-item {
    background: #fff; border-radius: 10px; overflow: hidden;
    border: 1px solid #fde68a; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: opacity .18s ease, transform .18s ease;
}
.pending-item.is-removing { opacity: 0; transform: scale(.98); }
.pending-item__img { width: 100%; height: 140px; object-fit: cover; display: block; }
.pending-item__body { padding: 10px 12px; }
.pending-item__uploader { font-size: 0.76rem; color: #9b8573; margin-bottom: 4px; }
.pending-item__caption  { font-size: 0.8rem; color: #7a6a5a; line-height: 1.5; margin-bottom: 8px; min-height: 1.2em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
.pending-item__actions  { display: flex; gap: 6px; flex-wrap: wrap; }
.pending-item__status { flex-basis: 100%; min-height: 18px; color: #a16207; font-size: 0.74rem; }
.pending-item__status.is-ok { color: #15803d; }
.pending-item__status.is-error { color: #dc2626; }
.btn-approve { background: #16a34a; color: #fff; border: none; border-radius: 6px; padding: 6px 14px; font-size: 0.78rem; cursor: pointer; transition: background .15s; }
.btn-approve:hover { background: #15803d; }
.btn-reject  { background: #fff; color: #dc2626; border: 1px solid #fca5a5; border-radius: 6px; padding: 5px 12px; font-size: 0.78rem; cursor: pointer; transition: all .15s; }
.btn-reject:hover { background: #fef2f2; }

/* ── 検索・フィルター ── */
.gl-toolbar {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    padding: 12px 14px; margin-bottom: 10px;
    background: #fff; border-radius: 12px; border: 1px solid #f0ebe3;
}
.gl-search-wrap { position: relative; flex: 1; min-width: 160px; max-width: 260px; }
.gl-search-wrap i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #c0b0a0; font-size: 0.85rem; pointer-events: none; }
.gl-search { width: 100%; padding: 8px 28px 8px 30px; border: 1px solid #e0d0bc; border-radius: 6px; font-size: 0.85rem; background: #fffdf9; box-sizing: border-box; }
.gl-search:focus { border-color: #b38b59; outline: none; }
.gl-clear { display: none; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #c0b0a0; font-size: 1rem; line-height: 1; }
.gl-clear.visible { display: block; }
.gl-filter-btn { min-height: 36px; padding: 7px 14px; border-radius: 20px; font-size: 0.78rem; font-weight: 700; border: 1px solid #e8d5b7; color: #b38b59; background: #fef9f0; cursor: pointer; transition: background 0.15s; white-space: nowrap; }
.gl-filter-btn.active, .gl-filter-btn:hover { background: #b38b59; color: #fff; border-color: #b38b59; }
.gl-result-count { font-size: 0.82rem; color: #999; margin-bottom: 8px; }
.gl-no-results { display: none; text-align: center; padding: 40px 20px; color: #aaa; }
.gl-no-results.visible { display: block; }

/* 承認済み/却下済みゲスト投稿 */
.guest-history-section { margin-bottom: 32px; }
.guest-history-section summary { cursor: pointer; font-size: 0.85rem; font-weight: 600; color: #7a6a5a; padding: 10px 0; user-select: none; }
.gl-admin-item.rejected { opacity: 0.4; }

/* 人物タグ付け */

.gl-tag-panel__head {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    margin-bottom: 8px; color: #5d4635; font-size: 0.82rem;
}
.gl-tag-selected-count { color: #b38b59; margin-left: 6px; font-size: 0.74rem; }
.gl-tag-panel__subhead { margin: 8px 0 5px; color: #7a6a5a; font-size: 0.74rem; font-weight: 700; }
.gl-tag-clear {
    border: 1px solid #e8d5b7; background: #fff; color: #9b8573;
    border-radius: 999px; padding: 3px 9px; font-size: 0.72rem; cursor: pointer;
}
.gl-tag-selected { min-height: 28px; max-height: 82px; overflow-y: auto; margin-bottom: 8px; }
.gl-tag-selected:empty::before { content: 'まだ選択されていません'; color: #c0b0a0; font-size: 0.76rem; }
.gl-tag-panel__list label span { display: grid; gap: 1px; }
.gl-tag-panel__list label strong { font-weight: 600; color: #5d4635; }
.gl-tag-panel__list label small { color: #a99888; font-size: 0.68rem; }
.gl-tag-panel__actions { display: flex; align-items: center; gap: 8px; }
.gl-tag-status { color: #9b8573; font-size: 0.76rem; }
.gl-tag-status.is-ok { color: #15803d; }
.gl-tag-status.is-error { color: #dc2626; }
.gl-admin-item__tags {
    min-height: 24px; max-height: 78px; overflow-y: auto; overscroll-behavior: contain;
    padding: 0 12px 10px; font-size: 0.72rem; color: #9b8573; line-height: 1.6;
    background: #fff; flex: 0 0 auto;
}
.gl-tag-chip--group { background: #eef7ff; border-color: #bfdbfe; color: #2563eb; }
.gl-tag-chip {
    display: inline-flex; align-items: center; max-width: calc(100% - 8px);
    background: #fef9f0; border: 1px solid #e8d5b7; color: #b38b59; border-radius: 20px;
    padding: 1px 8px; margin: 2px 3px 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    vertical-align: top;
}
.gl-tag-panel { display: none; padding: 10px 12px; background: #fef9f0; border-top: 1px solid #e8d5b7; }
.gl-tag-search { width: 100%; box-sizing: border-box; padding: 6px 10px; margin-bottom: 8px; border: 1px solid #e0d0bc; border-radius: 6px; font-size: 0.8rem; background: #fffdf9; }
.gl-tag-search:focus { border-color: #b38b59; outline: none; }
.gl-tag-panel__list { max-height: 160px; overflow-y: auto; border: 1px solid #e0d0bc; border-radius: 6px; padding: 6px 8px; background: #fff; margin-bottom: 8px; }
.gl-tag-panel__list label { display: flex; align-items: center; gap: 6px; font-size: 0.78rem; padding: 3px 0; cursor: pointer; }
.gl-tag-panel__list label.is-hidden { display: none; }
.gl-order-form { margin: 0 0 12px; }
.gl-order-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin: 0 0 12px; }
.gl-order-input { width: 58px; min-height: 34px; padding: 5px 8px; border: 1px solid #e0d0bc; border-radius: 7px; background: #fffdf9; font-size: .82rem; text-align: center; }
.gl-order-status { color: #8a7969; font-size: .78rem; }
.gl-order-status.is-ok { color: #15803d; }
.gl-order-status.is-error { color: #dc2626; }
.gl-admin-item__source { display: inline-flex; align-items: center; gap: 5px; margin-bottom: 6px; color: #9b8573; font-size: .7rem; font-weight: 700; }

@media (max-width: 640px) {
    .upload-zone { padding: 22px 14px; }
    .gl-admin-grid { grid-template-columns: 1fr; gap: 14px; }
    .gl-admin-item { border-radius: 14px; }
    .gl-admin-item__img { height: 188px; }
    .gl-admin-item__body { padding: 10px; }
    .gl-admin-item__actions { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
    .gl-admin-item__actions .btn-sm {
        min-width: 42px;
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .gl-admin-guide { grid-template-columns: 1fr; }
    .gl-admin-guide__card { align-items: flex-start; padding: 13px 14px; }
    .gl-admin-item__tag-btn { grid-column: 1 / -1; justify-content: center; min-width: 100%; }
    .gl-tag-panel { padding: 12px; }
    .gl-tag-panel__list { max-height: 260px; }
    .gl-tag-panel__list label { min-height: 42px; padding: 6px 0; font-size: .86rem; }
    .gl-tag-panel__list input[type="checkbox"] { width: 20px; height: 20px; }
    .official-section { margin-left: -2px; margin-right: -2px; border-radius: 18px; }
    .official-section__summary { padding: 15px 14px; align-items: flex-start; }
    .official-section__copy { white-space: normal; }
    .official-section__meta { flex-direction: column; align-items: flex-end; gap: 6px; }
    .official-section__pill { font-size: .72rem; padding: 4px 9px; }
    .official-section__inner { padding: 0 12px 12px; }
    .gl-toolbar { gap: 8px; padding: 12px; }
    .gl-search-wrap { flex-basis: 100%; max-width: none; }
    .gl-search { min-height: 42px; font-size: 16px; border-radius: 10px; }
    .gl-filter-btn { flex: 1 1 auto; }
    .gl-result-count { margin: 10px 2px; }
}
</style>
@endpush

@section('content')
<div class="admin-wrap">
    <h1><i class="fa-solid fa-images" style="font-size:1.2rem;opacity:0.7;margin-right:8px;"></i>ギャラリー管理</h1>
    <p class="page-desc">ゲストに公開する写真を管理します。複数枚まとめてアップロードできます。</p>

    <div class="gl-admin-guide">
        <div class="gl-admin-guide__card">
            <span class="gl-admin-guide__icon"><i class="fa-solid fa-user-tag"></i></span>
            <span>
                <strong>写真の人物・グループ紐付け</strong>
                <span>各写真カードの「タグ付け」から、写っているゲストやグループを選びます。</span>
            </span>
        </div>
        <a href="{{ route('admin.seating') }}" class="gl-admin-guide__card">
            <span class="gl-admin-guide__icon"><i class="fa-solid fa-chair"></i></span>
            <span>
                <strong>席・テーブルの振り分け</strong>
                <span>席の配置は席次表管理で行います。グループ振り分けも同じ画面にあります。</span>
            </span>
        </a>
    </div>

    @if (session('success'))
    <div class="alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif

    {{-- ゲスト投稿承認待ち --}}
    @if ($pending->isNotEmpty())
    <div class="pending-section" id="pendingSection">
        <h3>📥 ゲスト投稿 — 承認待ち（<span id="pendingCount">{{ $pending->count() }}</span>件）</h3>
        <p class="section-desc">ゲストから届いた写真です。承認するとギャラリーに追加されます。</p>
        <div class="pending-grid" id="pendingGrid">
            @foreach ($pending as $photo)
            <div class="pending-item" data-pending-id="{{ $photo->id }}">
                <img src="{{ $photo->url }}" alt="" class="pending-item__img">
                <div class="pending-item__body">
                    <p class="pending-item__uploader">
                        <i class="fa-solid fa-user" style="font-size:0.7rem;"></i>
                        {{ $photo->uploader?->name ?? '不明' }}
                        <span style="color:#c0b0a0;margin-left:4px;">{{ $photo->created_at->format('m/d') }}</span>
                    </p>
                    <p class="pending-item__caption">{{ $photo->caption ?: '（コメントなし）' }}</p>
                    <div class="pending-item__actions">
                        <form method="POST" action="{{ route('admin.gallery.approve', $photo->id) }}" class="pending-action-form" data-action="approve">
                            @csrf
                            <button type="submit" class="btn-approve" title="承認してギャラリーに追加">
                                <i class="fa-solid fa-check"></i> 承認
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.gallery.reject', $photo->id) }}" class="pending-action-form" data-action="reject" data-confirm="却下しますか？">
                            @csrf
                            <button type="submit" class="btn-reject">却下</button>
                        </form>
                        <span class="pending-item__status" aria-live="polite"></span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- アップロードフォーム --}}
    <div class="add-card">
        <h3>写真をアップロード</h3>
        <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data" id="galleryForm">
            @csrf
            <div class="upload-zone" id="uploadZone">
                <input type="file" name="photos[]" multiple accept="image/*"
                       onchange="previewPhotos(this)">
                <div class="upload-zone__icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                <p class="upload-zone__text">クリックまたはドラッグ＆ドロップ</p>
                <p class="upload-zone__sub">JPG / PNG / WebP・1枚10MB以内・最大20枚</p>
            </div>
            <div class="photo-previews" id="photoPreviews"></div>

            <div id="captionFields" style="display:none;margin-top:14px;">
                <p style="font-size:0.8rem;color:#9b8573;margin-bottom:8px;">キャプション（任意）</p>
                <div id="captionInputs"></div>
            </div>

            <button type="submit" class="btn-primary" style="margin-top:16px;" id="uploadBtn" disabled>
                <i class="fa-solid fa-upload"></i> アップロードする
            </button>
        </form>
    </div>

    {{-- 公式写真一覧 --}}
    <details class="official-section" id="officialPhotosSection" open>
        <summary class="official-section__summary">
            <span class="official-section__title">
                <span class="official-section__icon"><i class="fa-solid fa-image"></i></span>
                <span class="official-section__copy">
                    <strong>公開写真</strong>
                    <span>ゲストに見せる順番・表示切替・人物タグを管理</span>
                </span>
            </span>
            <span class="official-section__meta">
                <span class="official-section__pill" id="officialVisiblePill">{{ $photos->count() }}枚</span>
                <i class="fa-solid fa-chevron-down official-section__chevron" aria-hidden="true"></i>
            </span>
        </summary>
        <div class="official-section__inner">

    @if ($photos->isEmpty())
    <div class="empty-state">
        <div class="empty-state__icon">🖼️</div>
        <p class="empty-state__title">まだ写真がありません</p>
        <p class="empty-state__desc">上のフォームからアップロードしてください</p>
    </div>
    @else
    {{-- ツールバー --}}
    <div class="gl-toolbar">
        <div class="gl-search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" id="glSearch" class="gl-search" placeholder="キャプションで検索" autocomplete="off">
            <button type="button" id="glClear" class="gl-clear" aria-label="クリア">✕</button>
        </div>
        <button class="gl-filter-btn active" data-active="all">すべて</button>
        <button class="gl-filter-btn" data-active="1">表示中</button>
        <button class="gl-filter-btn" data-active="0">非表示</button>
    </div>
    <div class="gl-result-count" id="glCount"><strong>{{ $photos->count() }}</strong>枚</div>
    <div class="gl-order-form" id="galleryOrderControls" data-action="{{ route('admin.gallery.reorder') }}" data-token="{{ csrf_token() }}">
        <div class="gl-order-actions">
            <button type="button" class="btn-primary" style="padding:7px 16px;font-size:.82rem;" id="galleryOrderSave">表示順を保存</button>
            <span class="gl-order-status" id="galleryOrderStatus" aria-live="polite">番号を変えて保存できます</span>
        </div>
    </div>
    <div class="gl-admin-grid" id="galleryGrid">
        @foreach ($photos as $photo)
        <div class="gl-admin-item {{ $photo->is_active ? '' : 'inactive' }}"
             data-caption="{{ strtolower($photo->caption ?? '') }}"
             data-active="{{ $photo->is_active ? '1' : '0' }}"
             data-id="{{ $photo->id }}">
            <img src="{{ $photo->url }}" alt="" class="gl-admin-item__img">
            <div class="gl-admin-item__body">
                <span class="gl-admin-item__source">
                    <i class="fa-solid {{ $photo->is_guest_upload ? 'fa-user' : 'fa-camera' }}"></i>
                    {{ $photo->is_guest_upload ? (($photo->uploader?->guestProfile?->fullName() ?: $photo->uploader?->name ?: 'ゲスト') . ' さんの投稿') : '管理者アップロード' }}
                </span>
                <p class="gl-admin-item__caption" title="{{ $photo->caption }}">{{ $photo->caption ?: '—' }}</p>
                <div class="gl-admin-item__actions">
                    <input type="number" class="gl-order-input" min="1" value="{{ $loop->iteration }}" aria-label="表示順" data-order-input>
                    <form method="POST" action="{{ route('admin.gallery.move-up', $photo->id) }}">@csrf @method('PATCH')<button class="btn-sm btn-sm-pw" title="上へ"><i class="fa-solid fa-chevron-up"></i></button></form>
                    <form method="POST" action="{{ route('admin.gallery.move-down', $photo->id) }}">@csrf @method('PATCH')<button class="btn-sm btn-sm-pw" title="下へ"><i class="fa-solid fa-chevron-down"></i></button></form>
                    <button class="btn-sm btn-sm-pw" onclick="toggleEdit({{ $photo->id }})" title="編集"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-sm btn-sm-pw gl-admin-item__tag-btn" onclick="toggleTag({{ $photo->id }})" title="人物・グループを紐付け"><i class="fa-solid fa-user-tag"></i><span>タグ付け</span></button>
                    <form method="POST" action="{{ route('admin.gallery.destroy', $photo->id) }}" onsubmit="return confirm('削除しますか？')">@csrf @method('DELETE')<button class="btn-sm btn-sm-del"><i class="fa-solid fa-trash"></i></button></form>
                </div>
            </div>
            @if ($photo->taggedUsers->isNotEmpty() || $photo->taggedGroups->isNotEmpty())
            <div class="gl-admin-item__tags">
                @foreach ($photo->taggedGroups->unique(fn($group) => $group->displayName()) as $group)
                <span class="gl-tag-chip gl-tag-chip--group">{{ $group->displayName() }}</span>
                @endforeach
                @foreach ($photo->taggedUsers as $tagged)
                <span class="gl-tag-chip">{{ $tagged->guestProfile?->fullName() ?: $tagged->name }}</span>
                @endforeach
            </div>
            @endif
            <div id="edit-{{ $photo->id }}" style="display:none;padding:10px 12px;background:#fef9f0;border-top:1px solid #e8d5b7;">
                <form method="POST" action="{{ route('admin.gallery.update', $photo->id) }}">
                    @csrf @method('PATCH')
                    <div class="form-group" style="margin-bottom:8px;">
                        <label style="font-size:0.76rem;">キャプション</label>
                        <input type="text" name="caption" value="{{ $photo->caption }}" maxlength="200" placeholder="写真の説明">
                    </div>
                    <label style="display:flex;align-items:center;gap:6px;font-size:0.8rem;margin-bottom:8px;cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ $photo->is_active ? 'checked' : '' }}>
                        表示する
                    </label>
                    <button type="submit" class="btn-primary" style="padding:6px 16px;font-size:0.82rem;">保存</button>
                </form>
            </div>
            @include('admin.partials.gallery-tag-panel', ['photo' => $photo, 'taggableGuests' => $taggableGuests, 'taggableGroups' => $taggableGroups])
        </div>
        @endforeach
    </div>{{-- #galleryGrid --}}
    <div class="gl-no-results" id="glNoResults">
        <div style="font-size:2rem;margin-bottom:8px;">🔍</div>
        <p style="font-weight:600;color:#888;">該当する写真が見つかりません</p>
    </div>
    @endif
        </div>
    </details>

    {{-- 承認済み・却下済みゲスト投稿（折りたたみ） --}}
    @if ($guestApproved->isNotEmpty())
    <details class="guest-history-section" style="margin-top:32px;">
        <summary>ゲスト投稿の承認・却下済み（{{ $guestApproved->count() }}件）</summary>
        <div class="gl-admin-grid" style="margin-top:12px;">
            @foreach ($guestApproved as $photo)
            <div class="gl-admin-item {{ $photo->status === 'rejected' ? 'rejected' : '' }}">
                <img src="{{ $photo->url }}" alt="" class="gl-admin-item__img">
                <div class="gl-admin-item__body">
                    <p class="gl-admin-item__caption" title="{{ $photo->caption }}">
                        {{ $photo->uploader?->name ?? '不明' }} ·
                        <span style="color:{{ $photo->status==='approved' ? '#16a34a' : '#dc2626' }};">
                            {{ $photo->statusLabel() }}
                        </span>
                    </p>
                    <div class="gl-admin-item__actions">
                        @if ($photo->status === 'approved')
                        <button class="btn-sm btn-sm-pw gl-admin-item__tag-btn" onclick="toggleTag({{ $photo->id }})" title="人物・グループを紐付け"><i class="fa-solid fa-user-tag"></i><span>タグ付け</span></button>
                        <form method="POST" action="{{ route('admin.gallery.reject', $photo->id) }}" class="gallery-status-form" data-confirm="却下しますか？">
                            @csrf
                            <button class="btn-sm btn-reject" title="却下">却下</button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('admin.gallery.approve', $photo->id) }}" class="gallery-status-form">
                            @csrf
                            <button class="btn-sm btn-approve" title="承認">承認</button>
                        </form>
                        @endif
                        <span class="pending-item__status" aria-live="polite"></span>
                        <form method="POST" action="{{ route('admin.gallery.destroy', $photo->id) }}" onsubmit="return confirm('削除しますか？')">
                            @csrf @method('DELETE')
                            <button class="btn-sm btn-sm-del"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
                @if ($photo->taggedUsers->isNotEmpty() || $photo->taggedGroups->isNotEmpty())
                <div class="gl-admin-item__tags">
                    @foreach ($photo->taggedGroups->unique(fn($group) => $group->displayName()) as $group)
                    <span class="gl-tag-chip gl-tag-chip--group">{{ $group->displayName() }}</span>
                    @endforeach
                    @foreach ($photo->taggedUsers as $tagged)
                    <span class="gl-tag-chip">{{ $tagged->guestProfile?->fullName() ?: $tagged->name }}</span>
                    @endforeach
                </div>
                @endif
                @if ($photo->status === 'approved')
                @include('admin.partials.gallery-tag-panel', ['photo' => $photo, 'taggableGuests' => $taggableGuests, 'taggableGroups' => $taggableGroups])
                @endif
            </div>
            @endforeach
        </div>
    </details>
    @endif
</div>

<script>
// ── ギャラリー検索・フィルター ────────────────────────────
(function () {
    const state  = { q: '', active: 'all' };
    const grid   = document.getElementById('galleryGrid');
    const srch   = document.getElementById('glSearch');
    const clrBtn = document.getElementById('glClear');
    const countEl= document.getElementById('glCount');
    const noRes  = document.getElementById('glNoResults');
    if (!grid) return;

    const getItems = () => Array.from(grid.querySelectorAll('.gl-admin-item'));

    function applyAll() {
        const items = getItems();
        let visible = 0;
        items.forEach(item => {
            const d = item.dataset;
            let show = true;
            if (state.q && !d.caption.includes(state.q)) show = false;
            if (state.active !== 'all' && d.active !== state.active) show = false;
            item.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (countEl) countEl.innerHTML = `<strong>${visible}</strong>枚`;
        const officialPill = document.getElementById('officialVisiblePill');
        if (officialPill) {
            const total = items.length;
            officialPill.textContent = visible === total ? `${total}枚` : `${visible} / ${total}枚`;
        }
        if (noRes)   noRes.classList.toggle('visible', visible === 0);
    }

    srch?.addEventListener('input', () => {
        state.q = srch.value.toLowerCase().trim();
        clrBtn?.classList.toggle('visible', state.q.length > 0);
        applyAll();
    });
    clrBtn?.addEventListener('click', () => {
        srch.value = ''; state.q = '';
        clrBtn.classList.remove('visible');
        srch.focus(); applyAll();
    });
    document.querySelectorAll('.gl-filter-btn[data-active]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.gl-filter-btn[data-active]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            state.active = btn.dataset.active;
            applyAll();
        });
    });
    applyAll();
})();

(function () {
    const section = document.getElementById('officialPhotosSection');
    if (!section) return;
    const key = 'adminGalleryOfficialPhotosOpen';
    const saved = localStorage.getItem(key);
    if (saved === '0') section.open = false;
    if (saved === '1') section.open = true;
    section.addEventListener('toggle', () => {
        localStorage.setItem(key, section.open ? '1' : '0');
    });
})();


function updatePendingCount(delta) {
    const countEl = document.getElementById('pendingCount');
    if (!countEl) return;
    const next = Math.max(0, Number(countEl.textContent || 0) + delta);
    countEl.textContent = String(next);
    if (next === 0) {
        const section = document.getElementById('pendingSection');
        if (section) section.style.display = 'none';
    }
}

document.querySelectorAll('.pending-action-form, .gallery-status-form').forEach(form => {
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const confirmMessage = form.dataset.confirm;
        if (confirmMessage && !confirm(confirmMessage)) return;

        const card = form.closest('.pending-item, .gl-admin-item');
        const status = card?.querySelector('.pending-item__status');
        const buttons = card ? Array.from(card.querySelectorAll('button')) : [];
        buttons.forEach(button => button.disabled = true);
        if (status) {
            status.textContent = '処理中...';
            status.className = 'pending-item__status';
        }

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: new FormData(form),
            });
            const json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.message || '処理に失敗しました');
            if (status) {
                status.textContent = json.message || '完了しました';
                status.className = 'pending-item__status is-ok';
            }
            card?.classList.add('is-removing');
            setTimeout(() => {
                card?.remove();
                if (card?.classList.contains('pending-item')) updatePendingCount(-1);
            }, 180);
        } catch (error) {
            if (status) {
                status.textContent = error.message || '処理に失敗しました';
                status.className = 'pending-item__status is-error';
            }
            buttons.forEach(button => button.disabled = false);
        }
    });
});

function toggleEdit(id) {
    const el = document.getElementById('edit-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
function toggleTag(id) {
    const el = document.getElementById('tag-' + id);
    if (!el) return;
    el.style.display = el.style.display === 'none' || !el.style.display ? 'block' : 'none';
    el.querySelector('.gl-tag-search')?.focus();
}

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, ch => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[ch]));
}

function galleryTagNames(form) {
    return Array.from(form.querySelectorAll('.gl-tag-panel__list input[type="checkbox"]:checked')).map(input => {
        const label = input.closest('label');
        return { id: input.value, name: label?.dataset.label || label?.textContent.trim() || '', type: input.name === 'group_ids[]' ? 'group' : 'user' };
    });
}

function renderSelectedTags(form) {
    const selected = form.querySelector('.gl-tag-selected');
    const count = form.querySelector('.gl-tag-selected-count');
    const names = galleryTagNames(form);
    if (selected) {
        selected.innerHTML = names.map(tag => `<span class="gl-tag-chip ${tag.type === 'group' ? 'gl-tag-chip--group' : ''}" data-tag-id="${escapeHtml(tag.id)}">${escapeHtml(tag.name)}</span>`).join('');
    }
    if (count) count.textContent = `${names.length}名選択中`;
}

function updateCardTags(photoId, tags) {
    const card = document.querySelector(`.gl-admin-item[data-id="${photoId}"]`) || document.querySelector(`#tag-${photoId}`)?.closest('.gl-admin-item');
    if (!card) return;
    let holder = card.querySelector('.gl-admin-item__tags');
    const panel = card.querySelector(`#tag-${photoId}`);
    if (!holder) {
        holder = document.createElement('div');
        holder.className = 'gl-admin-item__tags';
        card.insertBefore(holder, panel || null);
    }
    const groups = arguments.length > 2 ? arguments[2] : [];
    const chips = groups.map(group => `<span class="gl-tag-chip gl-tag-chip--group">${escapeHtml(group.name)}</span>`)
        .concat(tags.map(tag => `<span class="gl-tag-chip">${escapeHtml(tag.name)}</span>`));
    holder.innerHTML = chips.join('');
}

document.querySelectorAll('.gl-tag-form').forEach(form => {
    const search = form.querySelector('.gl-tag-search');
    const status = form.querySelector('.gl-tag-status');

    search?.addEventListener('input', () => {
        const q = search.value.toLowerCase().trim();
        form.querySelectorAll('.gl-tag-panel__list label[data-name]').forEach(label => {
            label.classList.toggle('is-hidden', q.length > 0 && !label.dataset.name.includes(q));
        });
    });

    form.querySelectorAll('.gl-tag-panel__list input[type="checkbox"]').forEach(input => {
        input.addEventListener('change', () => {
            renderSelectedTags(form);
            if (status) {
                status.textContent = '未保存の変更があります';
                status.className = 'gl-tag-status';
            }
        });
    });

    form.querySelector('.gl-tag-clear')?.addEventListener('click', () => {
        form.querySelectorAll('.gl-tag-panel__list input[type="checkbox"]').forEach(input => input.checked = false);
        renderSelectedTags(form);
        if (status) {
            status.textContent = '未保存の変更があります';
            status.className = 'gl-tag-status';
        }
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = form.querySelector('.gl-tag-save');
        const data = new FormData(form);
        if (status) {
            status.textContent = '保存中...';
            status.className = 'gl-tag-status';
        }
        if (button) button.disabled = true;

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: data,
            });
            const json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.message || '保存に失敗しました');
            updateCardTags(json.photo_id, json.tags || [], json.groups || []);
            if (status) {
                status.textContent = '保存しました';
                status.className = 'gl-tag-status is-ok';
            }
        } catch (error) {
            if (status) {
                status.textContent = error.message || '保存に失敗しました';
                status.className = 'gl-tag-status is-error';
            }
        } finally {
            if (button) button.disabled = false;
        }
    });

    renderSelectedTags(form);
});

function filterTagList(input) {
    const q = input.value.toLowerCase().trim();
    const form = input.closest('form');
    form.querySelectorAll('.gl-tag-panel__list label[data-name]').forEach(label => {
        label.classList.toggle('is-hidden', q.length > 0 && !label.dataset.name.includes(q));
    });
}

(function () {
    const controls = document.getElementById('galleryOrderControls');
    const saveButton = document.getElementById('galleryOrderSave');
    const grid = document.getElementById('galleryGrid');
    const status = document.getElementById('galleryOrderStatus');
    if (!controls || !saveButton || !grid) return;

    function syncOrderFromInputs() {
        const items = Array.from(grid.querySelectorAll('.gl-admin-item'));
        items.sort((a, b) => {
            const av = Number(a.querySelector('[data-order-input]')?.value || 9999);
            const bv = Number(b.querySelector('[data-order-input]')?.value || 9999);
            if (av !== bv) return av - bv;
            return Number(a.dataset.id || 0) - Number(b.dataset.id || 0);
        });
        items.forEach((item, index) => {
            grid.appendChild(item);
            const input = item.querySelector('[data-order-input]');
            if (input) input.value = index + 1;
        });
    }

    grid.querySelectorAll('[data-order-input]').forEach(input => {
        input.addEventListener('change', () => {
            syncOrderFromInputs();
            if (status) {
                status.textContent = '未保存の表示順があります';
                status.className = 'gl-order-status';
            }
        });
    });

    saveButton.addEventListener('click', async () => {
        syncOrderFromInputs();
        if (status) {
            status.textContent = '保存中...';
            status.className = 'gl-order-status';
        }
        try {
            const data = new FormData();
            data.append('_token', controls.dataset.token || '');
            data.append('_method', 'PATCH');
            Array.from(grid.querySelectorAll('.gl-admin-item')).forEach(item => data.append('order[]', item.dataset.id));

            const res = await fetch(controls.dataset.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: data,
            });
            const json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.message || '保存に失敗しました');
            if (status) {
                status.textContent = '表示順を保存しました';
                status.className = 'gl-order-status is-ok';
            }
        } catch (error) {
            if (status) {
                status.textContent = error.message || '保存に失敗しました';
                status.className = 'gl-order-status is-error';
            }
        }
    });
})();
function previewPhotos(input) {
    const previews = document.getElementById('photoPreviews');
    const captionFields = document.getElementById('captionFields');
    const captionInputs = document.getElementById('captionInputs');
    const btn = document.getElementById('uploadBtn');
    previews.innerHTML = '';
    captionInputs.innerHTML = '';
    if (!input.files.length) { btn.disabled = true; captionFields.style.display = 'none'; return; }
    Array.from(input.files).forEach((f, i) => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'photo-preview';
            previews.appendChild(img);
        };
        reader.readAsDataURL(f);
        const div = document.createElement('div');
        div.style.cssText = 'margin-bottom:8px;';
        div.innerHTML = `<label style="font-size:0.76rem;color:#9b8573;display:block;margin-bottom:3px;">${f.name}</label><input type="text" name="captions[]" placeholder="キャプション（任意）" style="width:100%;padding:7px 10px;border:1px solid #e0d0bc;border-radius:5px;font-size:0.85rem;">`;
        captionInputs.appendChild(div);
    });
    btn.disabled = false;
    captionFields.style.display = 'block';
}
</script>
@endsection
