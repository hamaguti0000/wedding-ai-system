@extends('layouts.app')
@section('title', 'ゲストブック | Wedding')

@push('styles')
<style>
main { padding: 0; text-align: initial; }

/* ── バナー ── */
.gb-banner {
    position: relative; height: 28vh; min-height: 180px;
    overflow: hidden; display: flex; align-items: center; justify-content: center; text-align: center;
    padding-top: 60px; box-sizing: border-box;
}
.gb-banner__img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; filter: brightness(0.38) saturate(0.7); }
.gb-banner__overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(20,12,4,0.1), rgba(20,12,4,0.55)); }
.gb-banner__text { position: relative; z-index: 2; color: #fff; padding: 0 20px; }
.gb-banner__eyebrow { display: block; font-size: 0.6rem; letter-spacing: 5px; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 10px; font-family: 'Noto Sans JP', sans-serif; }
.gb-banner__title { font-family: 'Playfair Display', serif; font-size: clamp(1.4rem, 4vw, 2.2rem); font-weight: 400; letter-spacing: 2px; margin: 0; }

/* ── ページ全体 ── */
.gb-page { background: linear-gradient(160deg, #f5f0e8, #ece4d8); min-height: 60vh; }

/* ── 投稿フォーム ── */
.gb-form-wrap {
    max-width: 680px; margin: 0 auto; padding: 48px 20px 0;
}
.gb-form-card {
    background: #fff;
    border-radius: 16px;
    padding: 28px 28px 24px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.07), 0 0 0 1px rgba(232,213,183,0.3);
}
.gb-form-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.2rem; font-weight: 400; color: #3d2f25;
    margin: 0 0 4px;
}
.gb-form-sub { font-size: 0.82rem; color: #9b8573; margin: 0 0 20px; }

.gb-form-textarea {
    width: 100%; padding: 12px 14px; border: 1px solid #e0d0bc;
    border-radius: 8px; font-size: 0.95rem; font-family: 'Noto Sans JP', sans-serif;
    color: #3d2f25; background: #fffdf9; box-sizing: border-box;
    resize: vertical; min-height: 100px; line-height: 1.8;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.gb-form-textarea:focus { border-color: #b38b59; outline: none; box-shadow: 0 0 0 3px rgba(179,139,89,0.12); }

/* スタンプ選択 */
.gb-sticker-label { font-size: 0.8rem; color: #9b8573; margin: 14px 0 8px; display: block; }
.gb-stickers { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px; }
.gb-sticker-opt { display: none; }
.gb-sticker-lbl {
    font-size: 1.4rem; line-height: 1; padding: 6px 8px;
    border: 2px solid transparent; border-radius: 8px; cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
    user-select: none;
}
.gb-sticker-lbl:hover { background: #fef9f0; }
.gb-sticker-opt:checked + .gb-sticker-lbl { border-color: #b38b59; background: #fef4e4; }

.gb-form-actions { display: flex; gap: 10px; align-items: center; }
.gb-submit-btn {
    padding: 11px 28px; background: #b38b59; color: #fff; border: none;
    border-radius: 6px; font-size: 0.9rem; font-family: 'Noto Sans JP', sans-serif;
    font-weight: 500; cursor: pointer; transition: background 0.2s;
    display: inline-flex; align-items: center; gap: 7px;
}
.gb-submit-btn:hover { background: #9a7447; }
.gb-char-count { font-size: 0.78rem; color: #b0a090; margin-left: auto; }
.gb-char-count.warn { color: #e67e22; }

/* 既存メッセージ表示 */
.gb-existing {
    margin-top: 16px; padding: 14px 16px;
    background: linear-gradient(135deg, #fef9f0, #fdfbf5);
    border: 1px solid #e8d5b7; border-radius: 10px;
    font-size: 0.88rem;
}
.gb-existing__label { font-size: 0.72rem; color: #b38b59; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 6px; }
.gb-existing__text { color: #3d2f25; white-space: pre-wrap; }
.gb-existing__delete { font-size: 0.76rem; color: #c0392b; background: none; border: none; cursor: pointer; text-decoration: underline; margin-top: 8px; padding: 0; }

/* ── メッセージウォール ── */
.gb-wall-wrap { max-width: 1100px; margin: 0 auto; padding: 40px 20px 80px; }
.gb-wall-head { text-align: center; margin-bottom: 36px; }
.gb-wall-en { display: block; font-size: 0.65rem; letter-spacing: 5px; text-transform: uppercase; color: #b38b59; margin-bottom: 6px; font-family: 'Noto Sans JP', sans-serif; }
.gb-wall-ja { font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 400; color: #3d2f25; margin: 0 0 12px; }
.gb-wall-rule { width: 40px; height: 1px; background: #b38b59; margin: 0 auto 6px; }
.gb-wall-count { font-size: 0.8rem; color: #9b8573; }

/* メッセージグリッド */
.gb-grid {
    columns: 3 260px;
    column-gap: 16px;
}
.gb-card {
    break-inside: avoid;
    margin-bottom: 16px;
    border-radius: 16px;
    padding: 20px 20px 18px;
    background: #fff;
    box-shadow: 0 3px 14px rgba(0,0,0,0.06), 0 0 0 1px rgba(232,213,183,0.2);
    animation: gb-card-in 0.4s ease backwards;
    position: relative;
}
.gb-card:nth-child(1)  { animation-delay: 0.04s; }
.gb-card:nth-child(2)  { animation-delay: 0.08s; }
.gb-card:nth-child(3)  { animation-delay: 0.12s; }
.gb-card:nth-child(4)  { animation-delay: 0.16s; }
.gb-card:nth-child(5)  { animation-delay: 0.20s; }
.gb-card:nth-child(6)  { animation-delay: 0.24s; }
@keyframes gb-card-in {
    from { opacity: 0; transform: translateY(10px) scale(0.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* ステッカーデコ */
.gb-card__sticker {
    position: absolute; top: -14px; right: 16px;
    font-size: 2rem; line-height: 1; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

/* 投稿者情報 */
.gb-card__author {
    display: flex; align-items: center; gap: 10px; margin-bottom: 12px;
}
.gb-card__avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, #b38b59, #d4a870);
    color: #fff; font-family: 'Playfair Display', serif;
    font-size: 0.95rem; display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; overflow: hidden;
    border: 2px solid #f0e4d0;
}
.gb-card__avatar img { width: 100%; height: 100%; object-fit: cover; }
.gb-card__name { font-size: 0.88rem; font-weight: 600; color: #3d2f25; }
.gb-card__date { font-size: 0.72rem; color: #b0a090; margin-top: 1px; }

/* メッセージ本文 */
.gb-card__message {
    font-size: 0.9rem; color: #4a3a2e; line-height: 1.85;
    white-space: pre-wrap; word-break: break-word;
}

/* 空状態 */
.gb-empty {
    text-align: center; padding: 60px 20px; color: #c0b0a0;
}
.gb-empty i { font-size: 2.5rem; opacity: 0.3; display: block; margin-bottom: 12px; }

@media (max-width: 767px) {
    .gb-grid { columns: 1; }
    .gb-form-card { padding: 20px 16px; }
    .gb-wall-wrap { padding: 32px 14px 60px; }
}
</style>
@endpush

@section('content')
<section class="gb-banner">
    @include('partials.rotating-banner', ['class' => 'gb-banner__img'])
    <div class="gb-banner__overlay"></div>
    <div class="gb-banner__text">
        <span class="gb-banner__eyebrow">Guestbook · ゲストブック</span>
        <h1 class="gb-banner__title">みんなのメッセージ</h1>
    </div>
</section>

<div class="gb-page">

    {{-- ── 投稿フォーム ── --}}
    <div class="gb-form-wrap">
        @if (session('success'))
        <div class="alert-success" style="margin-bottom:16px;border-radius:10px;">{{ session('success') }}</div>
        @endif

        <div class="gb-form-card">
            <h2 class="gb-form-title">✉️ メッセージを残す</h2>
            <p class="gb-form-sub">お二人へのお祝いの言葉や、みんなへのひとこと。なんでも歓迎です！</p>

            @if ($myMessage)
            {{-- 投稿済み --}}
            <div class="gb-existing">
                <p class="gb-existing__label">あなたの投稿</p>
                @if ($myMessage->sticker)
                <span style="font-size:1.6rem;margin-bottom:4px;display:block;">{{ $myMessage->sticker }}</span>
                @endif
                <p class="gb-existing__text">{{ $myMessage->message }}</p>
                <form method="POST" action="{{ route('guestbook.destroy') }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="gb-existing__delete"
                            onclick="return confirm('メッセージを削除しますか？')">削除する</button>
                </form>
            </div>
            {{-- 編集フォーム --}}
            <details style="margin-top:12px;">
                <summary style="font-size:0.82rem;color:#b38b59;cursor:pointer;user-select:none;">メッセージを書き直す</summary>
                <div style="margin-top:12px;">
                    @include('partials.guestbook-form', ['myMessage' => $myMessage])
                </div>
            </details>
            @else
            @include('partials.guestbook-form', ['myMessage' => null])
            @endif
        </div>
    </div>

    {{-- ── メッセージウォール ── --}}
    <div class="gb-wall-wrap">
        <div class="gb-wall-head">
            <span class="gb-wall-en">Messages</span>
            <h2 class="gb-wall-ja">みんなからのメッセージ</h2>
            <div class="gb-wall-rule"></div>
            @if ($messages->isNotEmpty())
            <p class="gb-wall-count">{{ $messages->count() }}件のメッセージ 💌</p>
            @endif
        </div>

        @if ($messages->isEmpty())
        <div class="gb-empty">
            <i class="fa-regular fa-comment-dots"></i>
            <p>まだメッセージがありません。<br>最初の一言を残してみてください！</p>
        </div>
        @else
        <div class="gb-grid">
            @foreach ($messages as $msg)
            @php
                $user = $msg->user;
                $avatarType = $user?->avatarType() ?? 'initial';
                $initial = $user?->avatarInitial() ?? '?';
                $imgUrl  = $user?->avatarImageUrl();
                $emoji   = $user?->avatar_emoji;
                $bgColor = $user?->avatarBackgroundColor() ?? 'linear-gradient(135deg,#b38b59,#d4a870)';
                $borderColor = $user?->avatarBorderColor() ?? '#f0e4d0';
            @endphp
            <div class="gb-card">
                @if ($msg->sticker)
                <div class="gb-card__sticker">{{ $msg->sticker }}</div>
                @endif
                <div class="gb-card__author">
                    <div class="gb-card__avatar"
                         style="border-color: {{ $borderColor }}; {{ $avatarType !== 'emoji' ? "background: {$bgColor};" : '' }}">
                        @if ($avatarType === 'photo' && $imgUrl)
                            <img src="{{ $imgUrl }}" alt="">
                        @elseif ($avatarType === 'emoji' && $emoji)
                            <span style="font-size:1rem;line-height:1;background:{{ $bgColor }};width:100%;height:100%;display:flex;align-items:center;justify-content:center;">{{ $emoji }}</span>
                        @else
                            {{ $initial }}
                        @endif
                    </div>
                    <div>
                        <p class="gb-card__name">{{ $user?->name ?? '—' }}</p>
                        <p class="gb-card__date">{{ $msg->created_at->format('Y.m.d') }}</p>
                    </div>
                </div>
                <p class="gb-card__message">{{ $msg->message }}</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
