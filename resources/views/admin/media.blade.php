@extends('layouts.app')
@section('title', 'メディア管理 | Admin')

@push('styles')
<style>
.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 16px;
    margin-top: 24px;
}
.media-card {
    background: #fff;
    border: 1px solid #e8d5b7;
    border-radius: 10px;
    overflow: hidden;
    transition: box-shadow 0.2s;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
}
.media-card:hover { box-shadow: 0 6px 24px rgba(179,139,89,0.15); }
.media-card__preview {
    height: 120px;
    background: #fdfaf6;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}
.media-card__preview img {
    width: 100%; height: 100%; object-fit: cover;
}
.media-card__preview-empty {
    font-size: 2rem; opacity: 0.3;
}
.media-card__body {
    padding: 14px 16px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.media-card__label {
    font-size: 0.88rem; font-weight: 600; color: #3d2f25;
}
.media-card__count {
    font-size: 0.78rem; color: #7a6555;
}
.media-card__badge {
    display: inline-block;
    font-size: 0.68rem;
    padding: 2px 8px;
    border-radius: 10px;
    background: #fef9f0;
    color: #b38b59;
    border: 1px solid #e8d5b7;
    width: fit-content;
    margin-top: 4px;
}
.hero-setting-card {
    background: #fff;
    border: 1px solid #e8d5b7;
    border-radius: 10px;
    padding: 24px 28px;
    margin-bottom: 28px;
}
.hero-setting-card h2 {
    font-size: 0.8rem; letter-spacing: 3px; text-transform: uppercase;
    color: #b38b59; font-weight: 700; margin-bottom: 16px;
}
.hero-type-row {
    display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 12px;
}
.hero-type-btn {
    padding: 8px 20px;
    border-radius: 6px;
    border: 1px solid #e8d5b7;
    background: #fdfaf6;
    color: #6b5b4e;
    font-size: 0.84rem;
    cursor: pointer;
    font-family: 'Noto Sans JP', sans-serif;
    transition: all 0.15s;
}
.hero-type-btn.active {
    background: #b38b59; color: #fff; border-color: #b38b59;
}
.hero-type-btn:hover:not(.active) { border-color: #b38b59; color: #b38b59; }
</style>
@endpush

@section('content')
<div class="admin-wrap">
    <h1>メディア管理</h1>
    <p class="page-desc">各ページで表示する画像・動画を管理します。</p>

    @if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
    @endif

    {{-- ── ヒーロー設定 ── --}}
    <div class="hero-setting-card">
        <h2>ヒーロー（ホームトップ）表示方法</h2>
        <form method="POST" action="{{ route('admin.media.hero-type') }}">
            @csrf
            <div class="hero-type-row">
                <button type="submit" name="hero_type" value="slideshow"
                    class="hero-type-btn {{ ($setting?->hero_type ?? 'slideshow') === 'slideshow' ? 'active' : '' }}">
                    <i class="fa-solid fa-images"></i> スライドショー
                </button>
                <button type="submit" name="hero_type" value="video"
                    class="hero-type-btn {{ ($setting?->hero_type) === 'video' ? 'active' : '' }}">
                    <i class="fa-solid fa-video"></i> 背景動画
                </button>
            </div>
            <p style="font-size:0.8rem;color:#7a6555;margin:0;">
                スライドショー：「ヒーロー」にアップした画像を自動切り替え表示します。<br>
                背景動画：下記でアップロードした動画をループ再生します（音声なし）。
            </p>
        </form>

        {{-- 動画アップロード --}}
        <div style="margin-top:20px;padding-top:20px;border-top:1px solid #f0ebe3;">
            <p style="font-size:0.82rem;font-weight:600;color:#3d2f25;margin-bottom:10px;">背景動画ファイル（MP4 / WebM・最大200MB）</p>
            @if($setting?->hero_video_path)
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                <video src="{{ asset('storage/'.$setting->hero_video_path) }}"
                    style="height:60px;border-radius:6px;" muted></video>
                <span style="font-size:0.8rem;color:#7a6555;">動画がアップロード済みです</span>
                <form method="POST" action="{{ route('admin.media.video-delete') }}"
                    onsubmit="return confirm('動画を削除しますか？')">
                    @csrf @method('DELETE')
                    <button class="btn-sm btn-danger">削除</button>
                </form>
            </div>
            @endif
            <form method="POST" action="{{ route('admin.media.video') }}" enctype="multipart/form-data">
                @csrf
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <input type="file" name="video" accept="video/mp4,video/webm"
                        style="font-size:0.84rem;" required>
                    <button type="submit" class="btn-save" style="padding:8px 20px;font-size:0.82rem;">
                        アップロード
                    </button>
                </div>
                @error('video')<p class="field-error">{{ $message }}</p>@enderror
            </form>
        </div>
    </div>

    {{-- ── 各ロケーション ── --}}
    <div class="media-grid">
        @foreach(SiteImage::LOCATIONS as $key => $label)
        @php
            $cnt  = $counts->get($key);
            $total  = $cnt?->total ?? 0;
            $active = $cnt?->active_count ?? 0;
            $preview = \App\Models\SiteImage::forLocation($key)->active()->ordered()->first();
        @endphp
        <a href="{{ route('admin.media.show', $key) }}" class="media-card">
            <div class="media-card__preview">
                @if($preview)
                <img src="{{ $preview->url }}" alt="">
                @else
                <span class="media-card__preview-empty"><i class="fa-regular fa-image"></i></span>
                @endif
            </div>
            <div class="media-card__body">
                <p class="media-card__label">{{ $label }}</p>
                <p class="media-card__count">
                    {{ $total }}枚（表示中：{{ $active }}枚）
                </p>
                @if($total === 0)
                <span class="media-card__badge">未設定</span>
                @elseif($active === 0)
                <span class="media-card__badge" style="color:#c0392b;border-color:#e8b4b4;background:#fdf6f6;">すべて非表示</span>
                @endif
            </div>
        </a>
        @endforeach
    </div>
</div>
@endsection
