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
</style>
@endpush

@section('content')
<div class="admin-wrap">
    <h1><i class="fa-solid fa-images" style="font-size:1.2rem;opacity:0.7;margin-right:8px;"></i>ギャラリー管理</h1>
    <p class="page-desc">ゲストに公開する写真を管理します。複数枚まとめてアップロードできます。</p>

    @if (session('success'))
    <div class="alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
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

    {{-- 一覧 --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
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
