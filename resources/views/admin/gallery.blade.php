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
.gl-admin-item {
    background: #fff; border-radius: 10px; overflow: hidden;
    border: 1px solid #f0ebe3; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.gl-admin-item.inactive { opacity: 0.5; }
.gl-admin-item__img { width: 100%; height: 130px; object-fit: cover; display: block; }
.gl-admin-item__body { padding: 10px 12px; }
.gl-admin-item__caption { font-size: 0.78rem; color: #7a6a5a; margin: 0 0 8px; line-height: 1.5; min-height: 1.5em; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.gl-admin-item__actions { display: flex; gap: 4px; flex-wrap: wrap; }

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
}
.pending-item__img { width: 100%; height: 140px; object-fit: cover; display: block; }
.pending-item__body { padding: 10px 12px; }
.pending-item__uploader { font-size: 0.76rem; color: #9b8573; margin-bottom: 4px; }
.pending-item__caption  { font-size: 0.8rem; color: #7a6a5a; line-height: 1.5; margin-bottom: 8px; min-height: 1.2em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
.pending-item__actions  { display: flex; gap: 6px; }
.btn-approve { background: #16a34a; color: #fff; border: none; border-radius: 6px; padding: 6px 14px; font-size: 0.78rem; cursor: pointer; transition: background .15s; }
.btn-approve:hover { background: #15803d; }
.btn-reject  { background: #fff; color: #dc2626; border: 1px solid #fca5a5; border-radius: 6px; padding: 5px 12px; font-size: 0.78rem; cursor: pointer; transition: all .15s; }
.btn-reject:hover { background: #fef2f2; }

/* 承認済み/却下済みゲスト投稿 */
.guest-history-section { margin-bottom: 32px; }
.guest-history-section summary { cursor: pointer; font-size: 0.85rem; font-weight: 600; color: #7a6a5a; padding: 10px 0; user-select: none; }
.gl-admin-item.rejected { opacity: 0.4; }
</style>
@endpush

@section('content')
<div class="admin-wrap">
    <h1><i class="fa-solid fa-images" style="font-size:1.2rem;opacity:0.7;margin-right:8px;"></i>ギャラリー管理</h1>
    <p class="page-desc">ゲストに公開する写真を管理します。複数枚まとめてアップロードできます。</p>

    @if (session('success'))
    <div class="alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif

    {{-- ゲスト投稿承認待ち --}}
    @if ($pending->isNotEmpty())
    <div class="pending-section">
        <h3>📥 ゲスト投稿 — 承認待ち（{{ $pending->count() }}件）</h3>
        <p class="section-desc">ゲストから届いた写真です。承認するとギャラリーに追加されます。</p>
        <div class="pending-grid">
            @foreach ($pending as $photo)
            <div class="pending-item">
                <img src="{{ $photo->url }}" alt="" class="pending-item__img">
                <div class="pending-item__body">
                    <p class="pending-item__uploader">
                        <i class="fa-solid fa-user" style="font-size:0.7rem;"></i>
                        {{ $photo->uploader?->name ?? '不明' }}
                        <span style="color:#c0b0a0;margin-left:4px;">{{ $photo->created_at->format('m/d') }}</span>
                    </p>
                    <p class="pending-item__caption">{{ $photo->caption ?: '（コメントなし）' }}</p>
                    <div class="pending-item__actions">
                        <form method="POST" action="{{ route('admin.gallery.approve', $photo->id) }}">
                            @csrf
                            <button type="submit" class="btn-approve" title="承認してギャラリーに追加">
                                <i class="fa-solid fa-check"></i> 承認
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.gallery.reject', $photo->id) }}"
                              onsubmit="return confirm('却下しますか？')">
                            @csrf
                            <button type="submit" class="btn-reject">却下</button>
                        </form>
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
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <span style="font-size:0.88rem;font-weight:600;color:#3d2f25;">公式写真</span>
        <span style="font-size:0.82rem;color:#999;">{{ $photos->count() }}枚</span>
    </div>

    @if ($photos->isEmpty())
    <div class="empty-state">
        <div class="empty-state__icon">🖼️</div>
        <p class="empty-state__title">まだ写真がありません</p>
        <p class="empty-state__desc">上のフォームからアップロードしてください</p>
    </div>
    @else
    <div class="gl-admin-grid">
        @foreach ($photos as $photo)
        <div class="gl-admin-item {{ $photo->is_active ? '' : 'inactive' }}">
            <img src="{{ $photo->url }}" alt="" class="gl-admin-item__img">
            <div class="gl-admin-item__body">
                <p class="gl-admin-item__caption" title="{{ $photo->caption }}">{{ $photo->caption ?: '—' }}</p>
                <div class="gl-admin-item__actions">
                    <form method="POST" action="{{ route('admin.gallery.move-up', $photo->id) }}">@csrf @method('PATCH')<button class="btn-sm btn-sm-pw" title="上へ"><i class="fa-solid fa-chevron-up"></i></button></form>
                    <form method="POST" action="{{ route('admin.gallery.move-down', $photo->id) }}">@csrf @method('PATCH')<button class="btn-sm btn-sm-pw" title="下へ"><i class="fa-solid fa-chevron-down"></i></button></form>
                    <button class="btn-sm btn-sm-pw" onclick="toggleEdit({{ $photo->id }})"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" action="{{ route('admin.gallery.destroy', $photo->id) }}" onsubmit="return confirm('削除しますか？')">@csrf @method('DELETE')<button class="btn-sm btn-sm-del"><i class="fa-solid fa-trash"></i></button></form>
                </div>
            </div>
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
        </div>
        @endforeach
    </div>
    @endif

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
                        <form method="POST" action="{{ route('admin.gallery.destroy', $photo->id) }}" onsubmit="return confirm('削除しますか？')">
                            @csrf @method('DELETE')
                            <button class="btn-sm btn-sm-del"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </details>
    @endif
</div>

<script>
function toggleEdit(id) {
    const el = document.getElementById('edit-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
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
