@extends('layouts.app')
@section('title', '式次第管理 | Admin')

@push('styles')
<style>
/* 式次第管理固有スタイル */
.prog-item {
    background: #fff;
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid #f0ebe3;
    display: flex;
    align-items: flex-start;
    gap: 14px;
}
.prog-item__order { display: flex; flex-direction: column; gap: 3px; }
.prog-item__order form { margin: 0; }
.prog-item__order button {
    width: 26px; height: 26px; border-radius: 4px;
    border: 1px solid #e0d0bc; background: #fffdf9;
    color: #9b8573; font-size: 0.7rem; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.15s;
}
.prog-item__order button:hover { background: #fef9f0; color: #b38b59; border-color: #b38b59; }
.prog-item__icon { font-size: 1.1rem; color: #b38b59; padding-top: 2px; width: 20px; text-align: center; flex-shrink: 0; }
.prog-item__body { flex: 1; min-width: 0; }
.prog-item__time { font-size: 0.75rem; color: #b38b59; font-weight: 600; letter-spacing: 0.5px; }
.prog-item__title { font-size: 0.95rem; font-weight: 500; color: #3d2f25; margin: 2px 0 4px; }
.prog-item__desc { font-size: 0.82rem; color: #9b8573; line-height: 1.6; }
.prog-item__actions { display: flex; gap: 6px; flex-shrink: 0; }

/* 追加フォームカード */
.add-card { background: #fff; border-radius: 12px; padding: 22px 24px; box-shadow: 0 3px 12px rgba(0,0,0,0.07); margin-bottom: 28px; border: 2px dashed #e8d5b7; }
.add-card h3 { font-size: 0.78rem; font-weight: 700; color: #b38b59; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 16px; }
.add-row { display: grid; gap: 12px; margin-bottom: 12px; }
.add-row-2 { grid-template-columns: 120px 1fr; }
.add-row-icon { grid-template-columns: 200px 1fr; }

/* アイコン候補 */
.icon-presets { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
.icon-preset { padding: 6px 10px; border: 1px solid #e0d0bc; border-radius: 5px; cursor: pointer; background: #fffdf9; font-size: 0.78rem; color: #9b8573; transition: all 0.15s; display: flex; align-items: center; gap: 5px; }
.icon-preset:hover, .icon-preset.sel { background: #fef9f0; border-color: #b38b59; color: #b38b59; }

@media (max-width: 767px) {
    .add-row-2, .add-row-icon { grid-template-columns: 1fr; }
    .prog-item { flex-wrap: wrap; }
}
</style>
@endpush

@section('content')
<div class="admin-wrap">
    <h1><i class="fa-solid fa-list-ol" style="font-size:1.2rem;opacity:0.7;margin-right:8px;"></i>式次第管理</h1>

    @if (session('success'))
    <div class="alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif

    {{-- 追加フォーム --}}
    <div class="add-card">
        <h3>新しい項目を追加</h3>
        <form method="POST" action="{{ route('admin.program.store') }}">
            @csrf
            <div class="add-row add-row-2">
                <div class="form-group">
                    <label>時刻</label>
                    <input type="text" name="start_time" value="{{ old('start_time') }}" placeholder="14:00" maxlength="10">
                </div>
                <div class="form-group">
                    <label>タイトル <span class="req">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="例：挙式" required>
                    @error('title')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label>説明</label>
                <input type="text" name="description" value="{{ old('description') }}" placeholder="例：チャペルにてお二人の誓いを立てます">
            </div>
            <div class="form-group" style="margin-bottom:8px;">
                <label>アイコン（Font Awesome クラス名）</label>
                <input type="text" name="icon" id="iconInput" value="{{ old('icon', 'fa-circle') }}" placeholder="fa-church">
            </div>
            <div class="icon-presets">
                @foreach([
                    'fa-church'       => 'チャペル',
                    'fa-rings-wedding'=> '誓い',
                    'fa-champagne-glasses' => '乾杯',
                    'fa-utensils'     => '食事',
                    'fa-music'        => '余興',
                    'fa-camera'       => '撮影',
                    'fa-gift'         => '贈り物',
                    'fa-star'         => 'その他',
                    'fa-heart'        => 'ハート',
                    'fa-car'          => '移動',
                ] as $icon => $label)
                <span class="icon-preset" onclick="document.getElementById('iconInput').value='{{ $icon }}';this.closest('.icon-presets').querySelectorAll('.icon-preset').forEach(e=>e.classList.remove('sel'));this.classList.add('sel')">
                    <i class="fa-solid {{ $icon }}"></i> {{ $label }}
                </span>
                @endforeach
            </div>
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-plus"></i> 追加する
            </button>
        </form>
    </div>

    {{-- 項目一覧 --}}
    <div style="margin-bottom:8px;font-size:0.82rem;color:#999;">
        {{ $items->count() }}件 ／ ↑↓ で順番を変更できます
    </div>

    @if ($items->isEmpty())
    <div class="empty-state">
        <div class="empty-state__icon">📋</div>
        <p class="empty-state__title">まだ項目がありません</p>
        <p class="empty-state__desc">上のフォームから追加してください</p>
    </div>
    @else
    <div id="programList">
        @foreach ($items as $item)
        <div class="prog-item">
            {{-- 順序変更 --}}
            <div class="prog-item__order">
                <form method="POST" action="{{ route('admin.program.move-up', $item->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit" title="上へ"><i class="fa-solid fa-chevron-up"></i></button>
                </form>
                <form method="POST" action="{{ route('admin.program.move-down', $item->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit" title="下へ"><i class="fa-solid fa-chevron-down"></i></button>
                </form>
            </div>
            <div class="prog-item__icon"><i class="fa-solid {{ $item->icon }}"></i></div>
            <div class="prog-item__body">
                <p class="prog-item__time">{{ $item->start_time ?? '—' }}</p>
                <p class="prog-item__title">{{ $item->title }}</p>
                @if ($item->description)
                <p class="prog-item__desc">{{ $item->description }}</p>
                @endif
            </div>
            <div class="prog-item__actions">
                {{-- インライン編集ボタン --}}
                <button class="btn-sm btn-sm-pw" onclick="toggleEdit({{ $item->id }})">
                    <i class="fa-solid fa-pen"></i> 編集
                </button>
                <form method="POST" action="{{ route('admin.program.destroy', $item->id) }}"
                      onsubmit="return confirm('削除しますか？')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-sm btn-sm-del"><i class="fa-solid fa-trash"></i></button>
                </form>
            </div>
        </div>
        {{-- 編集フォーム（折り畳み）--}}
        <div id="edit-{{ $item->id }}" style="display:none;background:#fef9f0;border-radius:0 0 10px 10px;padding:16px 20px;margin-top:-10px;margin-bottom:10px;border:1px solid #e8d5b7;border-top:none;">
            <form method="POST" action="{{ route('admin.program.update', $item->id) }}">
                @csrf @method('PATCH')
                <div style="display:grid;grid-template-columns:100px 1fr;gap:10px;margin-bottom:10px;">
                    <div class="form-group"><label>時刻</label><input type="text" name="start_time" value="{{ $item->start_time }}" placeholder="14:00"></div>
                    <div class="form-group"><label>タイトル</label><input type="text" name="title" value="{{ $item->title }}" required></div>
                </div>
                <div class="form-group" style="margin-bottom:10px;"><label>説明</label><input type="text" name="description" value="{{ $item->description }}"></div>
                <div class="form-group" style="margin-bottom:12px;"><label>アイコン</label><input type="text" name="icon" value="{{ $item->icon }}"></div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="btn-primary" style="padding:8px 20px;font-size:0.85rem;">保存</button>
                    <button type="button" class="btn-secondary" onclick="toggleEdit({{ $item->id }})" style="padding:8px 14px;font-size:0.85rem;">キャンセル</button>
                </div>
            </form>
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
</script>
@endsection
