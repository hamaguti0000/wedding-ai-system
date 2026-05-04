@extends('layouts.app')
@section('title', 'ゲストブック管理 | Admin')

@push('styles')
<style>
.gb-msg-item {
    background: #fff; border-radius: 10px; padding: 16px 20px;
    margin-bottom: 10px; border: 1px solid #f0ebe3;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    display: flex; gap: 14px; align-items: flex-start;
}
.gb-msg-item.hidden-msg { opacity: 0.55; }
.gb-msg-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: linear-gradient(135deg, #b38b59, #d4a870);
    color: #fff; font-size: 1rem; font-family: 'Playfair Display', serif;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; overflow: hidden; border: 2px solid #f0e4d0;
}
.gb-msg-avatar img { width: 100%; height: 100%; object-fit: cover; }
.gb-msg-body { flex: 1; min-width: 0; }
.gb-msg-header { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; flex-wrap: wrap; }
.gb-msg-name { font-size: 0.9rem; font-weight: 600; color: #3d2f25; }
.gb-msg-date { font-size: 0.74rem; color: #b0a090; }
.gb-msg-sticker { font-size: 1.2rem; }
.gb-msg-text { font-size: 0.88rem; color: #6b5b4e; white-space: pre-wrap; line-height: 1.7; }
.gb-msg-actions { display: flex; gap: 6px; margin-top: 8px; align-items: center; }
</style>
@endpush

@section('content')
<div class="admin-wrap">
    <h1><i class="fa-solid fa-comment-dots" style="font-size:1.2rem;opacity:0.7;margin-right:8px;"></i>ゲストブック管理</h1>
    <p class="page-desc">ゲストが投稿したメッセージを管理します。非公開にしたり削除できます。</p>

    @if (session('success'))
    <div class="alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif

    <div style="margin-bottom:8px;font-size:0.82rem;color:#999;">
        {{ $messages->count() }}件（公開: {{ $messages->where('is_public', true)->count() }}件）
    </div>

    @if ($messages->isEmpty())
    <div class="empty-state">
        <div class="empty-state__icon">💌</div>
        <p class="empty-state__title">まだメッセージがありません</p>
    </div>
    @else
    @foreach ($messages as $msg)
    @php
        $user = $msg->user;
        $avatarType = $user?->avatarType() ?? 'initial';
        $imgUrl  = $user?->avatarImageUrl();
        $emoji   = $user?->avatar_emoji;
        $bgColor = $user?->avatarBackgroundColor() ?? 'linear-gradient(135deg,#b38b59,#d4a870)';
        $borderColor = $user?->avatarBorderColor() ?? '#f0e4d0';
    @endphp
    <div class="gb-msg-item {{ $msg->is_public ? '' : 'hidden-msg' }}">
        <div class="gb-msg-avatar" style="border-color:{{ $borderColor }};{{ $avatarType !== 'emoji' ? "background:{$bgColor};" : '' }}">
            @if ($avatarType === 'photo' && $imgUrl)
                <img src="{{ $imgUrl }}" alt="">
            @elseif ($avatarType === 'emoji' && $emoji)
                <span style="font-size:1rem;line-height:1;background:{{ $bgColor }};width:100%;height:100%;display:flex;align-items:center;justify-content:center;">{{ $emoji }}</span>
            @else
                {{ $user?->avatarInitial() ?? '?' }}
            @endif
        </div>
        <div class="gb-msg-body">
            <div class="gb-msg-header">
                <span class="gb-msg-name">{{ $user?->name ?? '削除済みユーザー' }}</span>
                <span class="gb-msg-date">{{ $msg->created_at->format('Y.m.d H:i') }}</span>
                @if ($msg->sticker)<span class="gb-msg-sticker">{{ $msg->sticker }}</span>@endif
                @if (!$msg->is_public)
                <span style="font-size:0.7rem;color:#aaa;border:1px solid #ddd;padding:1px 6px;border-radius:3px;">非公開</span>
                @endif
            </div>
            <p class="gb-msg-text">{{ $msg->message }}</p>
            <div class="gb-msg-actions">
                <form method="POST" action="{{ route('admin.guestbook.update', $msg->id) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="is_public" value="{{ $msg->is_public ? '0' : '1' }}">
                    <button class="btn-sm btn-sm-pw">
                        {{ $msg->is_public ? '非公開にする' : '公開する' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.guestbook.destroy', $msg->id) }}" onsubmit="return confirm('削除しますか？')">
                    @csrf @method('DELETE')
                    <button class="btn-sm btn-sm-del"><i class="fa-solid fa-trash"></i></button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
    @endif
</div>
@endsection
