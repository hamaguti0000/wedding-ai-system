@extends('layouts.app')
@section('title', 'プロフィールブック管理 | Admin')

@push('styles')
<style>
.pb-admin-current {
    display: flex; flex-wrap: wrap; gap: 14px;
    margin: 16px 0 24px;
}
.pb-admin-item {
    width: 96px;
    display: flex; flex-direction: column; align-items: center; gap: 6px;
}
.pb-admin-thumb {
    width: 90px; height: 127px; object-fit: cover;
    border-radius: 6px; border: 1px solid #e8d5b7;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
.pb-admin-num { font-size: 0.72rem; color: #9b8573; }
.pb-admin-actions { display: flex; gap: 3px; }
.pb-admin-actions button {
    width: 24px; height: 24px; border-radius: 5px; border: 1px solid #e8d5b7;
    background: #fffdf9; color: #7a6555; cursor: pointer; font-size: 0.7rem;
    display: flex; align-items: center; justify-content: center;
}
.pb-admin-actions button:hover { background: #f5eadc; }
.pb-admin-actions button.pb-admin-del { color: #c0392b; border-color: #f5c6c6; }
.pb-admin-actions button.pb-admin-del:hover { background: #fdf2f2; }
.pb-admin-empty { color: #9b8573; font-size: 0.85rem; margin: 16px 0 24px; }
</style>
@endpush

@section('content')

<div class="settings-section">
    <h2>プロフィールブック</h2>

    @if (session('success'))
    <div class="alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="alert-error" style="margin-bottom:20px;">{{ session('error') }}</div>
    @endif

    <p class="field-note">Canva等で作成したPDFをアップロードすると、ゲスト向けページで見開きページめくり形式のプロフィールブックとして表示されます。複数ファイルをまとめてアップロードでき、既存のページの後ろに追加されます。矢印ボタンでページ順を入れ替えられます。</p>

    @if ($pages->isEmpty())
    <p class="pb-admin-empty">まだアップロードされていません。</p>
    @else
    <div class="pb-admin-current">
        @foreach ($pages as $page)
        <div class="pb-admin-item">
            <img src="{{ $page->url }}" alt="{{ $page->page_number }}ページ" class="pb-admin-thumb" loading="lazy">
            <span class="pb-admin-num">{{ $page->page_number }}</span>
            <div class="pb-admin-actions">
                <form method="POST" action="{{ route('admin.profile-book.move-up', $page->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit" title="前へ"><i class="fa-solid fa-arrow-left"></i></button>
                </form>
                <form method="POST" action="{{ route('admin.profile-book.move-down', $page->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit" title="次へ"><i class="fa-solid fa-arrow-right"></i></button>
                </form>
                <form method="POST" action="{{ route('admin.profile-book.destroy-page', $page->id) }}" onsubmit="return confirm('このページを削除しますか？');">
                    @csrf @method('DELETE')
                    <button type="submit" class="pb-admin-del" title="削除"><i class="fa-solid fa-trash"></i></button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <div style="display:flex; gap:10px; margin-bottom:28px; flex-wrap:wrap;">
        <a href="{{ route('profile-book') }}" target="_blank" rel="noopener" class="btn-sm" style="background:#f3e6cf;color:#8a6a3a;border:1px solid #e2c99a; text-decoration:none;">
            <i class="fa-solid fa-book-open"></i> ページめくりをプレビュー
        </a>
        <form method="POST" action="{{ route('admin.profile-book.destroy') }}" onsubmit="return confirm('プロフィールブックを全ページ削除しますか？');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-sm" style="background:#fdf2f2;color:#c0392b;border:1px solid #f5c6c6;">
                <i class="fa-solid fa-trash"></i> 全ページ削除
            </button>
        </form>
    </div>
    <p class="field-note" style="margin-top:-18px; margin-bottom:28px;">「設定」ページの公開トグルがオフの間でも、管理者はこのプレビューでゲストと同じ見た目を確認できます。</p>
    @endif

    <form method="POST" action="{{ route('admin.profile-book.upload') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="pdfs">PDFファイル（複数選択可）</label>
            <input type="file" name="pdfs[]" id="pdfs" accept="application/pdf" multiple required>
            <p class="field-note">1ファイル20MBまで、一度に最大10ファイル。既存のページの後ろに追加されます。</p>
            @error('pdfs')<span class="field-error">{{ $message }}</span>@enderror
            @error('pdfs.*')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <button type="submit" class="btn-save">アップロードして変換する</button>
    </form>
</div>

@endsection
