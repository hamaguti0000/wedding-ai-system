@extends('layouts.app')
@section('title', 'プロフィールブック管理 | Admin')

@push('styles')
<style>
.pb-admin-current {
    display: flex; flex-wrap: wrap; gap: 10px;
    margin: 16px 0 24px;
}
.pb-admin-thumb {
    width: 90px; height: 127px; object-fit: cover;
    border-radius: 6px; border: 1px solid #e8d5b7;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
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

    <p class="field-note">Canva等で作成したPDFをアップロードすると、ゲスト向けページで見開きページめくり形式のプロフィールブックとして表示されます。</p>

    @if ($pages->isEmpty())
    <p class="pb-admin-empty">まだアップロードされていません。</p>
    @else
    <div class="pb-admin-current">
        @foreach ($pages as $page)
        <img src="{{ $page->url }}" alt="{{ $page->page_number }}ページ" class="pb-admin-thumb" loading="lazy">
        @endforeach
    </div>

    <form method="POST" action="{{ route('admin.profile-book.destroy') }}" onsubmit="return confirm('プロフィールブックを削除しますか？');" style="margin-bottom:28px;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-sm" style="background:#fdf2f2;color:#c0392b;border:1px solid #f5c6c6;">
            <i class="fa-solid fa-trash"></i> 削除する
        </button>
    </form>
    @endif

    <form method="POST" action="{{ route('admin.profile-book.upload') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="pdf">PDFファイル（{{ $pages->isEmpty() ? '新規アップロード' : '差し替え' }}）</label>
            <input type="file" name="pdf" id="pdf" accept="application/pdf" required>
            <p class="field-note">1ファイル20MBまで。アップロードすると既存のページはすべて置き換わります。</p>
            @error('pdf')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <button type="submit" class="btn-save">アップロードして変換する</button>
    </form>
</div>

@endsection
