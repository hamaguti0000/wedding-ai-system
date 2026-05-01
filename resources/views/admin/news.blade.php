@extends('layouts.app')
@section('title', 'お知らせ管理 | Admin')

@push('styles')
<style>
.news-item {
    background: #fff; border-radius: 10px; padding: 16px 20px;
    margin-bottom: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid #f0ebe3;
}
.news-item.inactive { opacity: 0.55; }
.news-item__head { display: flex; align-items: center; gap: 12px; margin-bottom: 4px; flex-wrap: wrap; }
.news-item__date { font-size: 0.8rem; color: #a89282; font-family: 'Noto Sans JP', sans-serif; white-space: nowrap; }
.news-item__tag {
    display: inline-block; padding: 2px 10px; font-size: 0.7rem; border-radius: 2px;
    background: #fef9f0; color: #b38b59; border: 1px solid #e8d5b7; letter-spacing: 1px;
}
.news-item__body { font-size: 0.92rem; color: #3d2f25; }
.news-item__actions { display: flex; gap: 6px; margin-top: 10px; flex-wrap: wrap; align-items: center; }
.add-card { background: #fff; border-radius: 12px; padding: 22px 24px; box-shadow: 0 3px 12px rgba(0,0,0,0.07); margin-bottom: 28px; border: 2px dashed #e8d5b7; }
.add-card h3 { font-size: 0.78rem; font-weight: 700; color: #b38b59; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 16px; }
.tag-presets { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
.tag-preset { padding: 4px 12px; border: 1px solid #e0d0bc; border-radius: 20px; cursor: pointer; background: #fffdf9; font-size: 0.78rem; color: #9b8573; transition: all 0.15s; }
.tag-preset:hover, .tag-preset.sel { background: #fef9f0; border-color: #b38b59; color: #b38b59; }
</style>
@endpush

@section('content')
<div class="admin-wrap">
    <h1><i class="fa-solid fa-bullhorn" style="font-size:1.2rem;opacity:0.7;margin-right:8px;"></i>お知らせ管理</h1>
    <p class="page-desc">ゲストのホーム画面に表示するお知らせを管理します。</p>

    @if (session('success'))
    <div class="alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif

    {{-- 追加フォーム --}}
    <div class="add-card">
        <h3>新しいお知らせを追加</h3>
        <form method="POST" action="{{ route('admin.news.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label>日付 <span class="req">*</span></label>
                    <input type="date" name="published_date"
                           value="{{ old('published_date', now()->format('Y-m-d')) }}" required>
                    @error('published_date')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>タグ</label>
                    <input type="text" name="tag" id="tagInput"
                           value="{{ old('tag') }}" placeholder="New / Info / 重要 など" maxlength="30">
                    <div class="tag-presets">
                        @foreach(['New','Info','重要','更新'] as $t)
                        <span class="tag-preset" onclick="document.getElementById('tagInput').value='{{ $t }}';this.closest('.tag-presets').querySelectorAll('.tag-preset').forEach(e=>e.classList.remove('sel'));this.classList.add('sel')">{{ $t }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:14px;">
                <label>内容 <span class="req">*</span></label>
                <input type="text" name="body" value="{{ old('body') }}"
                       placeholder="例：当日のご案内を更新しました。" maxlength="500" required>
                @error('body')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-plus"></i> 追加する
            </button>
        </form>
    </div>

    {{-- 一覧 --}}
    <div style="margin-bottom:8px;font-size:0.82rem;color:#999;">{{ $news->count() }}件</div>

    @if ($news->isEmpty())
    <div class="empty-state">
        <div class="empty-state__icon">📢</div>
        <p class="empty-state__title">まだお知らせがありません</p>
        <p class="empty-state__desc">上のフォームか���追加してください</p>
    </div>
    @else
    @foreach ($news as $item)
    <div class="news-item {{ $item->is_active ? '' : 'inactive' }}">
        <div class="news-item__head">
            <span class="news-item__date">{{ $item->published_date->format('Y.m.d') }}</span>
            @if ($item->tag)
            <span class="news-item__tag">{{ $item->tag }}</span>
            @endif
            @unless ($item->is_active)
            <span style="font-size:0.72rem;color:#e74c3c;font-weight:600;">[非表示]</span>
            @endunless
        </div>
        <p class="news-item__body">{{ $item->body }}</p>
        <div class="news-item__actions">
            <form method="POST" action="{{ route('admin.news.move-up', $item->id) }}">
                @csrf @method('PATCH')
                <button class="btn-sm btn-sm-pw" title="上へ"><i class="fa-solid fa-chevron-up"></i></button>
            </form>
            <form method="POST" action="{{ route('admin.news.move-down', $item->id) }}">
                @csrf @method('PATCH')
                <button class="btn-sm btn-sm-pw" title="下へ"><i class="fa-solid fa-chevron-down"></i></button>
            </form>
            <button class="btn-sm btn-sm-pw" onclick="toggleEdit({{ $item->id }})">
                <i class="fa-solid fa-pen"></i> 編集
            </button>
            <form method="POST" action="{{ route('admin.news.destroy', $item->id) }}"
                  onsubmit="return confirm('削除しますか？')">
                @csrf @method('DELETE')
                <button class="btn-sm btn-sm-del"><i class="fa-solid fa-trash"></i></button>
            </form>
        </div>
    </div>

    {{-- 編集フォーム --}}
    <div id="news-edit-{{ $item->id }}" style="display:none;background:#fef9f0;border-radius:0 0 10px 10px;padding:16px 20px;margin-top:-10px;margin-bottom:10px;border:1px solid #e8d5b7;border-top:none;">
        <form method="POST" action="{{ route('admin.news.update', $item->id) }}">
            @csrf @method('PATCH')
            <div class="form-row">
                <div class="form-group" style="margin-bottom:10px;">
                    <label>日付</label>
                    <input type="date" name="published_date" value="{{ $item->published_date->format('Y-m-d') }}" required>
                </div>
                <div class="form-group" style="margin-bottom:10px;">
                    <label>タグ</label>
                    <input type="text" name="tag" value="{{ $item->tag }}" placeholder="New / Info / 重要" maxlength="30">
                </div>
            </div>
            <div class="form-group" style="margin-bottom:10px;">
                <label>内容</label>
                <input type="text" name="body" value="{{ $item->body }}" required maxlength="500">
            </div>
            <div class="form-group" style="margin-bottom:14px;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }}>
                    ゲストに表示する
                </label>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn-primary" style="padding:8px 20px;font-size:0.85rem;">保存</button>
                <button type="button" class="btn-secondary" onclick="toggleEdit({{ $item->id }})" style="padding:8px 14px;font-size:0.85rem;">キャンセル</button>
            </div>
        </form>
    </div>
    @endforeach
    @endif
</div>

<script>
function toggleEdit(id) {
    const el = document.getElementById('news-edit-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
@endsection
