@extends('layouts.app')
@section('title', 'Q&A管理 | Admin')

@push('styles')
<style>
.faq-admin-item {
    background: #fff; border-radius: 10px; padding: 16px 20px;
    margin-bottom: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid #f0ebe3;
}
.faq-admin-item.inactive { opacity: 0.55; }
.faq-admin-q { font-size: 0.95rem; font-weight: 600; color: #3d2f25; margin-bottom: 6px; display: flex; align-items: flex-start; gap: 8px; }
.faq-admin-q-badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 0.68rem; font-weight: 700; background: #fef9f0; color: #b38b59; border: 1px solid #e8d5b7; flex-shrink: 0; margin-top: 2px; }
.faq-admin-a { font-size: 0.85rem; color: #7a6a5a; line-height: 1.7; white-space: pre-line; }
.faq-admin-actions { display: flex; gap: 6px; margin-top: 10px; flex-wrap: wrap; align-items: center; }
.add-card { background: #fff; border-radius: 12px; padding: 22px 24px; box-shadow: 0 3px 12px rgba(0,0,0,0.07); margin-bottom: 28px; border: 2px dashed #e8d5b7; }
.add-card h3 { font-size: 0.78rem; font-weight: 700; color: #b38b59; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 16px; }
.inactive-toggle { font-size: 0.78rem; color: #9b8573; display: flex; align-items: center; gap: 6px; }
</style>
@endpush

@section('content')
<div class="admin-wrap">
    <h1><i class="fa-solid fa-circle-question" style="font-size:1.2rem;opacity:0.7;margin-right:8px;"></i>Q&A 管理</h1>

    @if (session('success'))
    <div class="alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif

    {{-- 追加フォーム --}}
    <div class="add-card">
        <h3>新しい Q&amp;A を追加</h3>
        <form method="POST" action="{{ route('admin.faq.store') }}">
            @csrf
            <div class="form-group">
                <label>質問 <span class="req">*</span></label>
                <input type="text" name="question" value="{{ old('question') }}"
                       placeholder="例：駐車場はありますか？" required>
                @error('question')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>回答 <span class="req">*</span></label>
                <textarea name="answer" rows="4" style="width:100%;padding:11px 14px;border:1px solid #e0d0bc;border-radius:6px;font-family:'Noto Sans JP',sans-serif;font-size:0.95rem;box-sizing:border-box;resize:vertical;"
                    placeholder="例：会場には無料駐車場が◯台ございます。" required>{{ old('answer') }}</textarea>
                @error('answer')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-plus"></i> 追加する
            </button>
        </form>
    </div>

    {{-- 一覧 --}}
    <div style="margin-bottom:8px;font-size:0.82rem;color:#999;">
        {{ $faqs->count() }}件
    </div>

    @if ($faqs->isEmpty())
    <div class="empty-state">
        <div class="empty-state__icon">❓</div>
        <p class="empty-state__title">まだ Q&amp;A がありません</p>
    </div>
    @else
    @foreach ($faqs as $faq)
    <div class="faq-admin-item {{ $faq->is_active ? '' : 'inactive' }}">
        <div class="faq-admin-q">
            <span class="faq-admin-q-badge">Q</span>
            {{ $faq->question }}
            @unless ($faq->is_active)
            <span style="margin-left:6px;font-size:0.72rem;color:#e74c3c;font-weight:600;">[非表示]</span>
            @endunless
        </div>
        <div class="faq-admin-a">{{ $faq->answer }}</div>
        <div class="faq-admin-actions">
            {{-- 順序 --}}
            <form method="POST" action="{{ route('admin.faq.move-up', $faq->id) }}">
                @csrf @method('PATCH')
                <button class="btn-sm btn-sm-pw" title="上へ"><i class="fa-solid fa-chevron-up"></i></button>
            </form>
            <form method="POST" action="{{ route('admin.faq.move-down', $faq->id) }}">
                @csrf @method('PATCH')
                <button class="btn-sm btn-sm-pw" title="下へ"><i class="fa-solid fa-chevron-down"></i></button>
            </form>
            <button class="btn-sm btn-sm-pw" onclick="toggleFaqEdit({{ $faq->id }})">
                <i class="fa-solid fa-pen"></i> 編集
            </button>
            <form method="POST" action="{{ route('admin.faq.destroy', $faq->id) }}"
                  onsubmit="return confirm('削除しますか？')">
                @csrf @method('DELETE')
                <button class="btn-sm btn-sm-del"><i class="fa-solid fa-trash"></i></button>
            </form>
        </div>
    </div>
    {{-- 編集フォーム --}}
    <div id="faq-edit-{{ $faq->id }}" style="display:none;background:#fef9f0;border-radius:0 0 10px 10px;padding:16px 20px;margin-top:-10px;margin-bottom:10px;border:1px solid #e8d5b7;border-top:none;">
        <form method="POST" action="{{ route('admin.faq.update', $faq->id) }}">
            @csrf @method('PATCH')
            <div class="form-group">
                <label>質問</label>
                <input type="text" name="question" value="{{ $faq->question }}" required>
            </div>
            <div class="form-group">
                <label>回答</label>
                <textarea name="answer" rows="4" style="width:100%;padding:11px 14px;border:1px solid #e0d0bc;border-radius:6px;font-family:'Noto Sans JP',sans-serif;font-size:0.95rem;box-sizing:border-box;resize:vertical;" required>{{ $faq->answer }}</textarea>
            </div>
            <div class="form-group" style="margin-bottom:14px;">
                <label class="inactive-toggle">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ $faq->is_active ? 'checked' : '' }}>
                    ゲストに表示する
                </label>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn-primary" style="padding:8px 20px;font-size:0.85rem;">保存</button>
                <button type="button" class="btn-secondary" onclick="toggleFaqEdit({{ $faq->id }})" style="padding:8px 14px;font-size:0.85rem;">キャンセル</button>
            </div>
        </form>
    </div>
    @endforeach
    @endif
</div>

<script>
function toggleFaqEdit(id) {
    const el = document.getElementById('faq-edit-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
@endsection
