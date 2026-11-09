@extends('layouts.app')
@section('title', 'プログラム | ' . ($setting?->groom_name_en ?: $setting?->groom_name ?? 'Wedding'))

@push('styles')
<style>
main { padding: 0; text-align: initial; }

/* ── バナー ── */
.pg-banner {
    position: relative; height: 32vh; min-height: 200px;
    overflow: hidden; display: flex; align-items: center; justify-content: center; text-align: center;
    padding-top: 60px; box-sizing: border-box;
}
.pg-banner__img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; filter: brightness(0.38) saturate(0.7); }
.pg-banner__overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(20,12,4,0.1), rgba(20,12,4,0.55)); }
.pg-banner__text { position: relative; z-index: 2; color: #fff; padding: 0 20px; }
.pg-banner__eyebrow { display: block; font-size: 0.6rem; letter-spacing: 5px; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 10px; font-family: 'Noto Sans JP', sans-serif; }
.pg-banner__title { font-family: 'Playfair Display', serif; font-size: clamp(1.6rem, 5vw, 2.6rem); font-weight: 400; letter-spacing: 2px; margin: 0; }

/* ── コンテンツ ── */
.pg-wrap { max-width: 680px; margin: 0 auto; padding: 60px 20px 80px; }
.pg-intro { text-align: center; margin-bottom: 48px; }
.pg-section-en { display: block; font-size: 0.65rem; letter-spacing: 5px; text-transform: uppercase; color: #b38b59; margin-bottom: 6px; font-family: 'Noto Sans JP', sans-serif; }
.pg-section-ja { font-family: 'Playfair Display', serif; font-size: 1.7rem; font-weight: 400; color: #3d2f25; margin: 0 0 14px; }
.pg-rule { width: 40px; height: 1px; background: #b38b59; margin: 0 auto 24px; }
.pg-date { font-size: 0.88rem; color: #9b8573; letter-spacing: 1px; }

/* ── タイムライン ── */
.timeline { position: relative; padding-left: 0; }
.timeline::before {
    content: '';
    position: absolute;
    left: 44px;
    top: 20px;
    bottom: 20px;
    width: 2px;
    background: linear-gradient(to bottom, #e8d5b7, #d4b896, #e8d5b7);
}

.tl-item {
    display: flex;
    align-items: flex-start;
    gap: 0;
    margin-bottom: 36px;
    animation: tl-in 0.5s ease backwards;
}
.tl-item:nth-child(1) { animation-delay: 0.05s; }
.tl-item:nth-child(2) { animation-delay: 0.1s; }
.tl-item:nth-child(3) { animation-delay: 0.15s; }
.tl-item:nth-child(4) { animation-delay: 0.2s; }
.tl-item:nth-child(5) { animation-delay: 0.25s; }
.tl-item:nth-child(6) { animation-delay: 0.3s; }
.tl-item:nth-child(7) { animation-delay: 0.35s; }
.tl-item:nth-child(8) { animation-delay: 0.4s; }

@keyframes tl-in {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

.tl-time {
    width: 72px;
    flex-shrink: 0;
    text-align: right;
    padding-right: 16px;
    padding-top: 2px;
    font-size: 0.78rem;
    color: #b38b59;
    font-family: 'Noto Sans JP', sans-serif;
    font-weight: 500;
    letter-spacing: 0.5px;
    line-height: 1.4;
}

.tl-dot {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #b38b59;
    flex-shrink: 0;
    margin-top: 3px;
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}
.tl-dot i { font-size: 0.5rem; color: #b38b59; }

.tl-body {
    flex: 1;
    padding-left: 16px;
    padding-bottom: 4px;
    border-bottom: 1px solid #f5efe8;
}
.tl-item:last-child .tl-body { border-bottom: none; }

.tl-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.05rem;
    font-weight: 400;
    color: #3d2f25;
    margin: 0 0 5px;
}
.tl-desc {
    font-size: 0.85rem;
    color: #9b8573;
    line-height: 1.75;
    margin: 0;
}

/* 空状態 */
.pg-empty {
    text-align: center;
    padding: 60px 20px;
    color: #c0b0a0;
}
.pg-empty i { font-size: 2.5rem; opacity: 0.3; display: block; margin-bottom: 16px; }
.pg-empty p { font-size: 0.9rem; }

@media (min-width: 768px) {
    .pg-banner { padding-top: 80px; }
    .pg-wrap { padding: 80px 24px 100px; }
    .timeline::before { left: 80px; }
    .tl-time { width: 108px; font-size: 0.85rem; }
}
</style>
@endpush

@section('content')
<section class="pg-banner">
    @include('partials.rotating-banner', ['class' => 'pg-banner__img'])
    <div class="pg-banner__overlay"></div>
    <div class="pg-banner__text">
        <span class="pg-banner__eyebrow">Program · プログラム</span>
        <h1 class="pg-banner__title">プログラム</h1>
    </div>
</section>

<div class="pg-wrap">
    <div class="pg-intro">
        <span class="pg-section-en">Program</span>
        <h2 class="pg-section-ja">当日の流れ</h2>
        <div class="pg-rule"></div>
        @if ($setting?->ceremony_date)
        <p class="pg-date">
            {{ $setting->ceremonyDateJa() }}（{{ $setting->ceremonyDayOfWeek() }}）
            &nbsp;|&nbsp; {{ $setting->ceremonyTimeFormatted() }} 挙式
        </p>
        @endif

        {{-- 役割専用プログラム表示中バッジ --}}
        @if ($isRoleProgram)
        <div style="margin-top:18px;display:inline-flex;align-items:center;gap:8px;background:#fef9f0;border:1px solid #e8d5b7;border-radius:20px;padding:6px 16px;">
            <i class="fa-solid fa-clipboard-check" style="color:#b38b59;font-size:0.8rem;"></i>
            <span style="font-size:0.78rem;color:#b38b59;font-family:'Noto Sans JP',sans-serif;font-weight:600;letter-spacing:1px;">
                {{ $roleNames->implode('・') }} のプログラム
            </span>
        </div>
        @endif
    </div>

    @if ($items->isEmpty())
    <div class="pg-empty">
        <i class="fa-regular fa-calendar"></i>
        <p>プログラムは準備中です</p>
    </div>
    @else
    <div class="timeline">
        @foreach ($items as $item)
        <div class="tl-item">
            <div class="tl-time">{{ $item->start_time ?? '' }}</div>
            <div class="tl-dot">
                <i class="fa-solid {{ $item->icon ?? 'fa-circle' }}"></i>
            </div>
            <div class="tl-body">
                <p class="tl-title">{{ $item->title }}</p>
                @if ($item->description)
                <p class="tl-desc">{{ $item->description }}</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
