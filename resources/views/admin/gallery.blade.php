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

/* やることバー（未タグの案内） */
.gl-todo {
    display: flex; align-items: center; gap: 13px; margin: 0 0 18px;
    padding: 15px 17px; border-radius: 14px; text-decoration: none;
    border: 1px solid #e8d0a8; background: linear-gradient(135deg, #fffaf0 0%, #fdf2df 100%);
    box-shadow: 0 8px 24px rgba(61,47,37,.06);
}
.gl-todo:hover { border-color: #d0a86a; }
.gl-todo--done { border-color: #cfe6d3; background: linear-gradient(135deg, #f6fbf7 0%, #eef7f0 100%); }
.gl-todo__icon {
    width: 42px; height: 42px; border-radius: 999px; flex: 0 0 auto;
    display: inline-flex; align-items: center; justify-content: center;
    background: #f1e0c2; color: #9b6d35;
}
.gl-todo--done .gl-todo__icon { background: #d9edde; color: #3f7d51; }
.gl-todo__body { flex: 1; min-width: 0; }
.gl-todo__body strong { display: block; color: #3d2f25; font-size: .92rem; margin-bottom: 2px; }
.gl-todo__body span { display: block; color: #8a7969; font-size: .76rem; line-height: 1.5; }
.gl-todo__go {
    flex: 0 0 auto; display: inline-flex; align-items: center; gap: 6px;
    background: #b38b59; color: #fff; border-radius: 999px;
    padding: 10px 16px; font-size: .82rem; font-weight: 800;
}
.gl-upload-section { margin-bottom: 22px; border: 1px solid #efe3d4; border-radius: 16px; background: #fffdf9; box-shadow: 0 10px 28px rgba(61,47,37,.06); overflow: hidden; }
.gl-upload-section__summary { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 15px 18px; cursor: pointer; list-style: none; user-select: none; }
.gl-upload-section__summary::-webkit-details-marker { display: none; }
.gl-upload-section__summary strong { display: block; color: #3d2f25; font-size: .95rem; }
.gl-upload-section__summary span { display: block; margin-top: 2px; color: #9b8573; font-size: .76rem; line-height: 1.55; }
.gl-upload-section__summary i { color: #b38b59; transition: transform .18s ease; }
.gl-upload-section[open] .gl-upload-section__summary i { transform: rotate(180deg); }
.gl-upload-section__inner { padding: 0 18px 18px; }
.upload-zone { min-height: 150px; display: grid; place-items: center; }
.upload-zone__summary { display: none; margin-top: 10px; color: #7a6048; font-size: .82rem; font-weight: 800; }
.photo-previews { align-items: center; }
.photo-preview-more { width: 80px; height: 80px; border-radius: 8px; border: 1px dashed #d9c6ad; display: inline-flex; align-items: center; justify-content: center; color: #9b8573; background: #fffaf2; font-size: .82rem; font-weight: 800; }

.gl-admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 18px;
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
    background: #fff; border-radius: 16px; overflow: hidden;
    border: 1px solid #eee4d8; box-shadow: 0 12px 30px rgba(61,47,37,0.08);
    display: flex; flex-direction: column; min-width: 0;
}
.gl-admin-item.inactive { opacity: 0.56; }
.gl-admin-item__photo { position: relative; background: #f2ece4; }
.gl-admin-item__img { width: 100%; height: 172px; object-fit: cover; display: block; flex: 0 0 auto; }
.gl-order-badge {
    position: absolute; left: 10px; top: 10px; min-width: 32px; height: 32px; padding: 0 9px;
    display: inline-flex; align-items: center; justify-content: center; border-radius: 999px;
    background: rgba(255,255,255,.92); color: #7a5b32; font-size: .8rem; font-weight: 800;
    box-shadow: 0 8px 22px rgba(0,0,0,.14);
}
.gl-untagged-badge {
    position: absolute; right: 10px; top: 10px; padding: 5px 11px;
    border-radius: 999px; background: #b8791f; color: #fff;
    font-size: .74rem; font-weight: 800; letter-spacing: .5px;
    box-shadow: 0 8px 22px rgba(0,0,0,.16);
}
.gl-admin-item__body { padding: 12px 14px 14px; }
.gl-admin-item__caption { font-size: 0.86rem; color: #5d4635; margin: 0 0 12px; line-height: 1.55; min-height: 1.55em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
.gl-admin-item__caption.is-empty { color: #b7a897; font-weight: 500; }
.gl-admin-item__actions { display: grid; grid-template-columns: 1fr auto; gap: 8px; align-items: stretch; }
.gl-admin-item__primary { min-height: 42px; justify-content: center; font-weight: 800; }
.gl-admin-more { position: relative; }
.gl-admin-more[open] { grid-column: 1 / -1; }
.gl-admin-more[open] summary { width: 100%; box-sizing: border-box; }
.gl-admin-more summary {
    min-height: 42px; padding: 0 12px; border: 1px solid #e8d5b7; border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center; gap: 7px;
    background: #fffdf9; color: #8a642e; font-size: .8rem; font-weight: 800; cursor: pointer; list-style: none;
}
.gl-admin-more summary::-webkit-details-marker { display: none; }
.gl-admin-more__panel {
    display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px;
    margin-top: 9px; padding: 10px; border: 1px solid #eadccd; border-radius: 12px; background: #fffaf2;
}
.gl-order-compact { grid-column: 1 / -1; display: grid; grid-template-columns: auto 1fr; gap: 8px; align-items: center; color: #806a55; font-size: .76rem; font-weight: 700; }
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

/* 人物タグのチップ表示（タグ付け操作は専用画面 admin/gallery-tag に分離） */
.gl-admin-item__tags {
    min-height: 24px; max-height: 38px; overflow: hidden; overscroll-behavior: contain;
    padding: 0 14px 12px; font-size: 0.72rem; color: #9b8573; line-height: 1.6;
    background: #fff; flex: 0 0 auto;
}
.gl-tag-chip--group { background: #eef7ff; border-color: #bfdbfe; color: #2563eb; }
.gl-tag-chip {
    display: inline-flex; align-items: center; max-width: calc(100% - 8px);
    background: #fef9f0; border: 1px solid #e8d5b7; color: #b38b59; border-radius: 20px;
    padding: 1px 8px; margin: 2px 3px 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    vertical-align: top;
}
.gl-order-form { margin: 0 0 12px; }
.gl-order-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin: 0 0 12px; }
.gl-order-presets { display: flex; gap: 7px; overflow-x: auto; padding: 0 0 10px; scrollbar-width: none; }
.gl-order-presets::-webkit-scrollbar { display: none; }
.gl-order-preset { min-height: 36px; padding: 0 12px; border: 1px solid #e8d5b7; border-radius: 999px; background: #fffdf9; color: #7a5b32; font-size: .78rem; font-weight: 800; white-space: nowrap; cursor: pointer; }
.gl-order-preset:hover { background: #f6ead8; }
.gl-order-input { width: 58px; min-height: 34px; padding: 5px 8px; border: 1px solid #e0d0bc; border-radius: 7px; background: #fffdf9; font-size: .82rem; text-align: center; }
.gl-order-status { color: #8a7969; font-size: .78rem; }
.gl-order-status.is-ok { color: #15803d; }
.gl-order-status.is-error { color: #dc2626; }
.gl-admin-item__source { display: inline-flex; align-items: center; gap: 5px; margin-bottom: 7px; color: #9b8573; font-size: .72rem; font-weight: 800; }

.gl-admin-item__meta { display: flex; flex-wrap: wrap; gap: 5px; margin: 0 0 9px; }
.gl-photo-chip { display: inline-flex; align-items: center; gap: 5px; padding: 3px 8px; border-radius: 999px; background: #f7f1e9; border: 1px solid #eadccd; color: #806a55; font-size: .68rem; font-weight: 800; }
.gl-photo-chip--source { background: #eef7ff; border-color: #bfdbfe; color: #2563eb; }
.gl-upload-options { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin: 0 0 12px; }
.gl-upload-options label, .gl-edit-options label { display: grid; gap: 4px; color: #8a7969; font-size: .74rem; font-weight: 800; }
.gl-upload-options select, .gl-edit-options select { width: 100%; min-height: 40px; padding: 7px 10px; border: 1px solid #e0d0bc; border-radius: 8px; background: #fff; color: #3d2f25; font-size: .86rem; }
.gl-edit-options { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; margin-bottom: 8px; }

@media (max-width: 640px) {
    .upload-zone { min-height: 118px; padding: 18px 14px; }
    .upload-zone__icon { font-size: 1.65rem; margin-bottom: 4px; }
    .upload-zone__text { margin: 0; font-size: .84rem; }
    .upload-zone__sub { margin-top: 3px; font-size: .68rem; }
    .gl-upload-section__summary { padding: 14px; }
    .gl-upload-section__inner { padding: 0 14px 14px; }
    .gl-admin-grid { grid-template-columns: 1fr; gap: 18px; }
    .gl-admin-item { border-radius: 18px; }
    .gl-admin-item__img { height: 220px; }
    .gl-admin-item__body { padding: 13px 14px 15px; }
    .gl-admin-item__actions .btn-sm,
    .gl-admin-more summary {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .gl-admin-more__panel .btn-sm { width: 100%; }
    .gl-todo { padding: 13px 14px; gap: 11px; flex-wrap: wrap; }
    .gl-todo__go { width: 100%; justify-content: center; }
    .gl-admin-item__tag-btn { justify-content: center; min-width: 100%; }
    .official-section { margin-left: -2px; margin-right: -2px; border-radius: 18px; }
    .official-section__summary { padding: 15px 14px; align-items: flex-start; }
    .official-section__copy { white-space: normal; }
    .official-section__meta { flex-direction: column; align-items: flex-end; gap: 6px; }
    .official-section__pill { font-size: .72rem; padding: 4px 9px; }
    .official-section__inner { padding: 0 12px 12px; }
    .gl-toolbar { position: sticky; top: 72px; z-index: 4; gap: 8px; padding: 12px; box-shadow: 0 8px 24px rgba(61,47,37,.08); }
    .gl-search-wrap { flex-basis: 100%; max-width: none; }
    .gl-search { min-height: 42px; font-size: 16px; border-radius: 10px; }
    .gl-filter-btn { flex: 1 1 auto; }
    .gl-upload-options, .gl-edit-options { grid-template-columns: 1fr; }
    .gl-result-count { margin: 10px 2px; }
}
</style>
@endpush

@section('content')
<div class="admin-wrap">
    <h1><i class="fa-solid fa-images" style="font-size:1.2rem;opacity:0.7;margin-right:8px;"></i>ギャラリー管理</h1>
    <p class="page-desc">ゲストに公開する写真を管理します。複数枚まとめてアップロードできます。</p>

    @if ($untaggedCount > 0)
    <a href="{{ route('admin.gallery.tag.edit', $firstUntaggedId) }}" class="gl-todo">
        <span class="gl-todo__icon"><i class="fa-solid fa-user-tag"></i></span>
        <span class="gl-todo__body">
            <strong>タグが未設定の写真が {{ $untaggedCount }}枚 あります</strong>
            <span>1枚ずつ表示して、写っている人を選んでいきます</span>
        </span>
        <span class="gl-todo__go">始める <i class="fa-solid fa-arrow-right"></i></span>
    </a>
    @else
    <div class="gl-todo gl-todo--done">
        <span class="gl-todo__icon"><i class="fa-solid fa-check"></i></span>
        <span class="gl-todo__body">
            <strong>すべての写真にタグが付いています</strong>
            <span>各写真の「タグ付け」から内容を見直せます</span>
        </span>
    </div>
    @endif

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
    <details class="gl-upload-section" id="uploadPhotosSection">
        <summary class="gl-upload-section__summary">
            <span>
                <strong>写真を追加</strong>
                <span>必要な時だけ開いてアップロードします</span>
            </span>
            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
        </summary>
        <div class="gl-upload-section__inner">
            <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data" id="galleryForm">
                @csrf
                <div class="gl-upload-options">
                    <label>シーン
                        <select name="gallery_category">
                            @foreach ($categoryOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>撮影者
                        <select name="photo_source">
                            <option value="photographer">カメラマン撮影</option>
                            <option value="admin">管理者アップロード</option>
                        </select>
                    </label>
                </div>
                <div class="upload-zone" id="uploadZone">
                    <input type="file" name="photos[]" multiple accept="image/*"
                           onchange="previewPhotos(this)">
                    <div>
                        <div class="upload-zone__icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                        <p class="upload-zone__text">写真を選択</p>
                        <p class="upload-zone__sub">JPG / PNG / WebP・1枚10MB以内・最大20枚</p>
                        <p class="upload-zone__summary" id="uploadSummary"></p>
                    </div>
                </div>
                <div class="photo-previews" id="photoPreviews"></div>

                <div id="captionFields" style="display:none;margin-top:14px;">
                    <p style="font-size:0.8rem;color:#9b8573;margin-bottom:8px;">キャプション（任意）</p>
                    <div id="captionInputs"></div>
                </div>

                <button type="submit" class="btn-primary" style="margin-top:16px;width:100%;min-height:46px;" id="uploadBtn" disabled>
                    <i class="fa-solid fa-upload"></i> アップロードする
                </button>
            </form>
        </div>
    </details>

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
        <button class="gl-filter-btn active" data-filter-kind="active" data-active="all">すべて</button>
        <button class="gl-filter-btn" data-filter-kind="tagged" data-tagged="0">未タグ</button>
        <button class="gl-filter-btn" data-filter-kind="active" data-active="1">表示中</button>
        <button class="gl-filter-btn" data-filter-kind="active" data-active="0">非表示</button>
        <button class="gl-filter-btn" data-filter-kind="category" data-category="ceremony">挙式</button>
        <button class="gl-filter-btn" data-filter-kind="category" data-category="reception">披露宴</button>
        <button class="gl-filter-btn" data-filter-kind="source" data-source="photographer">カメラマン</button>
    </div>
    <div class="gl-result-count" id="glCount"><strong>{{ $photos->count() }}</strong>枚</div>
    <div class="gl-order-form" id="galleryOrderControls" data-action="{{ route('admin.gallery.reorder') }}" data-token="{{ csrf_token() }}">
        <div class="gl-order-presets" aria-label="表示順の一括変更">
            <button type="button" class="gl-order-preset" data-order-preset="newest"><i class="fa-solid fa-arrow-down-short-wide"></i> 新しい順</button>
            <button type="button" class="gl-order-preset" data-order-preset="oldest"><i class="fa-solid fa-arrow-up-wide-short"></i> 古い順</button>
            <button type="button" class="gl-order-preset" data-order-preset="official-first"><i class="fa-solid fa-camera"></i> 管理者→ゲスト</button>
            <button type="button" class="gl-order-preset" data-order-preset="guest-first"><i class="fa-solid fa-user"></i> ゲスト→管理者</button>
        </div>
        <div class="gl-order-actions">
            <button type="button" class="btn-primary" style="padding:7px 16px;font-size:.82rem;" id="galleryOrderSave">表示順を保存</button>
            <span class="gl-order-status" id="galleryOrderStatus" aria-live="polite">一括で並べて、必要な写真だけ番号調整できます</span>
        </div>
    </div>
    <div class="gl-admin-grid" id="galleryGrid">
        @foreach ($photos as $photo)
        @php $isUntagged = $photo->taggedUsers->isEmpty() && $photo->taggedGroups->isEmpty(); @endphp
        <div class="gl-admin-item {{ $photo->is_active ? '' : 'inactive' }}"
             data-caption="{{ strtolower($photo->caption ?? '') }}"
             data-active="{{ $photo->is_active ? '1' : '0' }}"
             data-source="{{ $photo->photo_source ?: ($photo->is_guest_upload ? 'guest' : 'admin') }}"
             data-upload-kind="{{ $photo->is_guest_upload ? 'guest' : 'official' }}"
             data-category="{{ $photo->gallery_category ?: 'other' }}"
             data-tagged="{{ $isUntagged ? '0' : '1' }}"
             data-created="{{ optional($photo->created_at)->timestamp ?? 0 }}"
             data-id="{{ $photo->id }}">
            <div class="gl-admin-item__photo">
                <img src="{{ $photo->url }}" alt="" class="gl-admin-item__img">
                <span class="gl-order-badge">{{ $loop->iteration }}</span>
                @if ($isUntagged)
                <span class="gl-untagged-badge">未タグ</span>
                @endif
            </div>
            <div class="gl-admin-item__body">
                <span class="gl-admin-item__source">
                    <i class="fa-solid {{ $photo->is_guest_upload ? 'fa-user' : 'fa-camera' }}"></i>
                    {{ $photo->is_guest_upload ? (($photo->uploader?->guestProfile?->fullName() ?: $photo->uploader?->name ?: 'ゲスト') . ' さんの投稿') : $photo->sourceLabel() }}
                </span>
                <div class="gl-admin-item__meta">
                    <span class="gl-photo-chip"><i class="fa-solid fa-layer-group"></i>{{ $photo->categoryLabel() }}</span>
                    <span class="gl-photo-chip gl-photo-chip--source"><i class="fa-solid {{ $photo->is_guest_upload ? 'fa-user' : ($photo->photo_source === 'photographer' ? 'fa-camera-retro' : 'fa-camera') }}"></i>{{ $photo->sourceLabel() }}</span>
                </div>
                <p class="gl-admin-item__caption {{ $photo->caption ? '' : 'is-empty' }}" title="{{ $photo->caption }}">{{ $photo->caption ?: 'キャプションなし' }}</p>
                <div class="gl-admin-item__actions">
                    <a href="{{ route('admin.gallery.tag.edit', $photo->id) }}" class="btn-sm btn-sm-pw gl-admin-item__tag-btn gl-admin-item__primary" title="人物・グループを紐付け"><i class="fa-solid fa-user-tag"></i><span>タグ付け</span></a>
                    <details class="gl-admin-more">
                        <summary><i class="fa-solid fa-ellipsis"></i>操作</summary>
                        <div class="gl-admin-more__panel">
                            <label class="gl-order-compact">表示順<input type="number" class="gl-order-input" min="1" value="{{ $loop->iteration }}" aria-label="表示順" data-order-input></label>
                            <form method="POST" action="{{ route('admin.gallery.move-up', $photo->id) }}">@csrf @method('PATCH')<button class="btn-sm btn-sm-pw" title="上へ"><i class="fa-solid fa-chevron-up"></i></button></form>
                            <form method="POST" action="{{ route('admin.gallery.move-down', $photo->id) }}">@csrf @method('PATCH')<button class="btn-sm btn-sm-pw" title="下へ"><i class="fa-solid fa-chevron-down"></i></button></form>
                            <button type="button" class="btn-sm btn-sm-pw" onclick="toggleEdit({{ $photo->id }})" title="編集"><i class="fa-solid fa-pen"></i></button>
                            <form method="POST" action="{{ route('admin.gallery.destroy', $photo->id) }}" onsubmit="return confirm('削除しますか？')">@csrf @method('DELETE')<button class="btn-sm btn-sm-del" title="削除"><i class="fa-solid fa-trash"></i></button></form>
                        </div>
                    </details>
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
                    <div class="gl-edit-options">
                        <label>シーン
                            <select name="gallery_category">
                                @foreach ($categoryOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($photo->gallery_category ?: 'other') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>撮影者
                            @if ($photo->is_guest_upload)
                            <select name="photo_source" disabled><option value="guest">ゲスト投稿</option></select>
                            <input type="hidden" name="photo_source" value="guest">
                            @else
                            <select name="photo_source">
                                <option value="photographer" @selected($photo->photo_source === 'photographer')>カメラマン撮影</option>
                                <option value="admin" @selected(($photo->photo_source ?: 'admin') === 'admin')>管理者アップロード</option>
                            </select>
                            @endif
                        </label>
                    </div>
                    <label style="display:flex;align-items:center;gap:6px;font-size:0.8rem;margin-bottom:8px;cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ $photo->is_active ? 'checked' : '' }}>
                        表示する
                    </label>
                    <button type="submit" class="btn-primary" style="padding:6px 16px;font-size:0.82rem;">保存</button>
                </form>
            </div>
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
                        <a href="{{ route('admin.gallery.tag.edit', $photo->id) }}" class="btn-sm btn-sm-pw gl-admin-item__tag-btn" title="人物・グループを紐付け"><i class="fa-solid fa-user-tag"></i><span>タグ付け</span></a>
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
            </div>
            @endforeach
        </div>
    </details>
    @endif
</div>

<script>
// ── ギャラリー検索・フィルター ────────────────────────────
(function () {
    const state  = { q: '', active: 'all', tagged: 'all', category: 'all', source: 'all' };
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
            if (state.tagged !== 'all' && d.tagged !== state.tagged) show = false;
            if (state.category !== 'all' && d.category !== state.category) show = false;
            if (state.source !== 'all' && d.source !== state.source) show = false;
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
    document.querySelectorAll('.gl-filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const kind = btn.dataset.filterKind || 'active';
            if (kind === 'active') {
                document.querySelectorAll('.gl-filter-btn[data-filter-kind="active"]').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                state.active = btn.dataset.active || 'all';
            }
            if (kind === 'category') {
                const next = state.category === btn.dataset.category ? 'all' : btn.dataset.category;
                state.category = next;
                document.querySelectorAll('.gl-filter-btn[data-filter-kind="category"]').forEach(b => b.classList.toggle('active', next !== 'all' && b.dataset.category === next));
            }
            if (kind === 'source') {
                const next = state.source === btn.dataset.source ? 'all' : btn.dataset.source;
                state.source = next;
                document.querySelectorAll('.gl-filter-btn[data-filter-kind="source"]').forEach(b => b.classList.toggle('active', next !== 'all' && b.dataset.source === next));
            }
            if (kind === 'tagged') {
                const next = state.tagged === btn.dataset.tagged ? 'all' : btn.dataset.tagged;
                state.tagged = next;
                document.querySelectorAll('.gl-filter-btn[data-filter-kind="tagged"]').forEach(b => b.classList.toggle('active', next !== 'all' && b.dataset.tagged === next));
            }
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
(function () {
    const controls = document.getElementById('galleryOrderControls');
    const saveButton = document.getElementById('galleryOrderSave');
    const grid = document.getElementById('galleryGrid');
    const status = document.getElementById('galleryOrderStatus');
    if (!controls || !saveButton || !grid) return;

    function renumberVisibleOrder(items = Array.from(grid.querySelectorAll('.gl-admin-item'))) {
        items.forEach((item, index) => {
            grid.appendChild(item);
            const input = item.querySelector('[data-order-input]');
            const badge = item.querySelector('.gl-order-badge');
            if (input) input.value = index + 1;
            if (badge) badge.textContent = index + 1;
        });
    }

    function markOrderDirty(message = '未保存の表示順があります') {
        if (status) {
            status.textContent = message;
            status.className = 'gl-order-status';
        }
    }

    function syncOrderFromInputs() {
        const items = Array.from(grid.querySelectorAll('.gl-admin-item'));
        items.sort((a, b) => {
            const av = Number(a.querySelector('[data-order-input]')?.value || 9999);
            const bv = Number(b.querySelector('[data-order-input]')?.value || 9999);
            if (av !== bv) return av - bv;
            return Number(a.dataset.id || 0) - Number(b.dataset.id || 0);
        });
        renumberVisibleOrder(items);
    }

    function applyPreset(type) {
        const items = Array.from(grid.querySelectorAll('.gl-admin-item'));
        items.sort((a, b) => {
            if (type === 'newest') return Number(b.dataset.created || 0) - Number(a.dataset.created || 0);
            if (type === 'oldest') return Number(a.dataset.created || 0) - Number(b.dataset.created || 0);
            if (type === 'official-first' && a.dataset.uploadKind !== b.dataset.uploadKind) return a.dataset.uploadKind === 'official' ? -1 : 1;
            if (type === 'guest-first' && a.dataset.uploadKind !== b.dataset.uploadKind) return a.dataset.uploadKind === 'guest' ? -1 : 1;
            const av = Number(a.querySelector('[data-order-input]')?.value || 9999);
            const bv = Number(b.querySelector('[data-order-input]')?.value || 9999);
            if (av !== bv) return av - bv;
            return Number(a.dataset.id || 0) - Number(b.dataset.id || 0);
        });
        renumberVisibleOrder(items);
        markOrderDirty('一括並び替えを適用しました。保存してください');
    }

    controls.querySelectorAll('[data-order-preset]').forEach(button => {
        button.addEventListener('click', () => applyPreset(button.dataset.orderPreset));
    });

    grid.querySelectorAll('[data-order-input]').forEach(input => {
        input.addEventListener('change', () => {
            syncOrderFromInputs();
            markOrderDirty();
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
    const summary = document.getElementById('uploadSummary');
    const files = Array.from(input.files || []);
    previews.innerHTML = '';
    captionInputs.innerHTML = '';
    if (!files.length) {
        btn.disabled = true;
        captionFields.style.display = 'none';
        if (summary) { summary.textContent = ''; summary.style.display = 'none'; }
        return;
    }

    if (summary) {
        summary.textContent = `${files.length}枚選択中`;
        summary.style.display = 'block';
    }

    files.slice(0, 6).forEach(f => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'photo-preview';
            previews.appendChild(img);
        };
        reader.readAsDataURL(f);
    });
    if (files.length > 6) {
        const more = document.createElement('span');
        more.className = 'photo-preview-more';
        more.textContent = `+${files.length - 6}枚`;
        previews.appendChild(more);
    }

    const div = document.createElement('div');
    div.style.cssText = 'margin-bottom:8px;';
    div.innerHTML = `<label style="font-size:0.76rem;color:#9b8573;display:block;margin-bottom:3px;">全写真に同じキャプションを付ける</label><input type="text" name="captions[]" placeholder="キャプション（任意）" style="width:100%;padding:9px 10px;border:1px solid #e0d0bc;border-radius:8px;font-size:16px;">`;
    captionInputs.appendChild(div);
    const sharedCaptionInput = div.querySelector('input');
    const hiddenCaptionInputs = [];
    for (let i = 1; i < files.length; i++) {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'captions[]';
        hiddenCaptionInputs.push(hidden);
        captionInputs.appendChild(hidden);
    }
    sharedCaptionInput?.addEventListener('input', () => {
        hiddenCaptionInputs.forEach(hidden => hidden.value = sharedCaptionInput.value);
    });
    btn.disabled = false;
    captionFields.style.display = 'block';
}
</script>
@endsection
