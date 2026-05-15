@extends('layouts.app')
@section('title', $label . ' | メディア管理 | Admin')

@push('styles')
<style>
.ml-header {
    display: flex; align-items: center; gap: 16px; margin-bottom: 28px; flex-wrap: wrap;
}
.ml-header h1 { margin: 0; }
.ml-back {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 0.82rem; color: #b38b59; text-decoration: none;
}
.ml-back:hover { color: #9a7447; }

.ml-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; flex-wrap: wrap; margin-bottom: 20px;
}
.mode-toggle {
    display: flex; align-items: center; gap: 8px;
}
.mode-btn {
    padding: 6px 16px; border-radius: 20px; border: 1px solid #e8d5b7;
    background: #fdfaf6; color: #6b5b4e; font-size: 0.8rem;
    cursor: pointer; font-family: 'Noto Sans JP', sans-serif; transition: all 0.15s;
}
.mode-btn.active { background: #b38b59; color: #fff; border-color: #b38b59; }
.mode-btn:hover:not(.active) { border-color: #b38b59; color: #b38b59; }

.img-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 14px;
    margin-top: 20px;
}
.img-card {
    border: 1px solid #e8d5b7; border-radius: 8px;
    overflow: hidden; background: #fff;
    transition: box-shadow 0.2s;
}
.img-card:hover { box-shadow: 0 4px 16px rgba(179,139,89,0.15); }
.img-card__thumb {
    width: 100%; aspect-ratio: 16/10; object-fit: cover;
    display: block; cursor: grab;
}
.img-card__footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 10px; gap: 8px;
}
.img-card__order {
    font-size: 0.72rem; color: #a89282;
}
.img-card__actions { display: flex; gap: 6px; }
.btn-icon {
    width: 30px; height: 30px; border-radius: 6px;
    border: 1px solid #e8d5b7; background: #fdfaf6;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 0.8rem; color: #7a6555;
    transition: all 0.15s;
}
.btn-icon:hover { background: #b38b59; color: #fff; border-color: #b38b59; }
.btn-icon--danger:hover { background: #e74c3c; border-color: #e74c3c; color: #fff; }
.img-card--inactive { opacity: 0.45; }

.upload-zone {
    border: 2px dashed #e8d5b7; border-radius: 10px;
    padding: 32px 20px; text-align: center;
    background: #fdfaf6; cursor: pointer; transition: border-color 0.2s;
}
.upload-zone:hover { border-color: #b38b59; }
.upload-zone input[type=file] { display: none; }
.upload-zone__label {
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    cursor: pointer;
}
.upload-zone__icon { font-size: 2rem; color: #d4b896; }
.upload-zone__text { font-size: 0.88rem; color: #7a6555; }
.upload-zone__sub  { font-size: 0.76rem; color: #a89282; }
</style>
@endpush

@section('content')
<div class="admin-wrap">

    <div class="ml-header">
        <a href="{{ route('admin.media') }}" class="ml-back">
            <i class="fa-solid fa-chevron-left"></i> メディア一覧
        </a>
        <h1>{{ $label }}</h1>
    </div>

    @if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
    @endif

    {{-- ── 表示モード ── --}}
    @if($location !== 'hero')
    <div class="ml-toolbar">
        <div class="mode-toggle">
            <span style="font-size:0.82rem;color:#7a6555;margin-right:4px;">表示モード：</span>
            <form method="POST" action="{{ route('admin.media.mode', $location) }}" style="display:contents;">
                @csrf
                <button name="mode" value="fixed"
                    class="mode-btn {{ $currentMode === 'fixed' ? 'active' : '' }}">
                    固定（1枚目を常に表示）
                </button>
                <button name="mode" value="random"
                    class="mode-btn {{ $currentMode === 'random' ? 'active' : '' }}">
                    ランダム（毎回ランダム）
                </button>
            </form>
        </div>
        <p style="font-size:0.76rem;color:#a89282;margin:0;">
            複数枚アップロードするとランダムで切り替わります
        </p>
    </div>
    @endif

    {{-- ── アップロード ── --}}
    <form method="POST" action="{{ route('admin.media.store', $location) }}"
          enctype="multipart/form-data" id="uploadForm">
        @csrf
        <div class="upload-zone" onclick="document.getElementById('fileInput').click()">
            <label class="upload-zone__label">
                <span class="upload-zone__icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                <span class="upload-zone__text">クリックして画像を選択</span>
                <span class="upload-zone__sub">JPEG / PNG / WebP・最大10MB</span>
            </label>
            <input type="file" id="fileInput" name="image" accept="image/*"
                onchange="this.form.submit()">
        </div>
        @error('image')<p class="field-error" style="margin-top:8px;">{{ $message }}</p>@enderror
    </form>

    {{-- ── 画像一覧 ── --}}
    @if($images->isEmpty())
    <p style="text-align:center;color:#a89282;font-size:0.9rem;margin-top:32px;">
        まだ画像がありません。上のエリアからアップロードしてください。
    </p>
    @else
    <p style="font-size:0.78rem;color:#a89282;margin-top:16px;">
        <i class="fa-solid fa-grip-dots-vertical"></i> ドラッグで並び替えができます
    </p>
    <div class="img-grid" id="imgGrid">
        @foreach($images as $img)
        <div class="img-card {{ $img->is_active ? '' : 'img-card--inactive' }}" data-id="{{ $img->id }}">
            <img src="{{ $img->url }}" alt="" class="img-card__thumb">
            <div class="img-card__footer">
                <span class="img-card__order">#{{ $loop->iteration }}</span>
                <div class="img-card__actions">
                    {{-- 表示/非表示トグル --}}
                    <form method="POST" action="{{ route('admin.media.toggle', $img->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn-icon" title="{{ $img->is_active ? '非表示にする' : '表示する' }}">
                            <i class="fa-solid {{ $img->is_active ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                        </button>
                    </form>
                    {{-- 削除 --}}
                    <form method="POST" action="{{ route('admin.media.destroy', $img->id) }}"
                          onsubmit="return confirm('この画像を削除しますか？')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-icon btn-icon--danger" title="削除">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
const grid = document.getElementById('imgGrid');
if (grid) {
    Sortable.create(grid, {
        animation: 150,
        handle: '.img-card__thumb',
        ghostClass: 'sortable-ghost',
        onEnd() {
            const order = [...grid.querySelectorAll('.img-card')].map(el => el.dataset.id);
            fetch('{{ route('admin.media.reorder') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ order }),
            });
        },
    });
}
</script>
@endpush
