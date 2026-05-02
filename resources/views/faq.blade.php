@extends('layouts.app')
@section('title', 'よくある質問 | ' . ($setting?->groom_name_en ?: $setting?->groom_name ?? 'Wedding'))

@push('styles')
<style>
main { padding: 0; text-align: initial; }

.fq-banner {
    position: relative; height: 32vh; min-height: 200px;
    overflow: hidden; display: flex; align-items: center; justify-content: center; text-align: center;
    padding-top: 60px; box-sizing: border-box;
}
.fq-banner__img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; filter: brightness(0.38) saturate(0.7); }
.fq-banner__overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(20,12,4,0.1), rgba(20,12,4,0.55)); }
.fq-banner__text { position: relative; z-index: 2; color: #fff; padding: 0 20px; }
.fq-banner__eyebrow { display: block; font-size: 0.6rem; letter-spacing: 5px; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 10px; font-family: 'Noto Sans JP', sans-serif; }
.fq-banner__title { font-family: 'Playfair Display', serif; font-size: clamp(1.6rem, 5vw, 2.6rem); font-weight: 400; letter-spacing: 2px; margin: 0; }

.fq-wrap { max-width: 720px; margin: 0 auto; padding: 60px 20px 80px; }
.fq-intro { text-align: center; margin-bottom: 48px; }
.fq-section-en { display: block; font-size: 0.65rem; letter-spacing: 5px; text-transform: uppercase; color: #b38b59; margin-bottom: 6px; font-family: 'Noto Sans JP', sans-serif; }
.fq-section-ja { font-family: 'Playfair Display', serif; font-size: 1.7rem; font-weight: 400; color: #3d2f25; margin: 0 0 14px; }
.fq-rule { width: 40px; height: 1px; background: #b38b59; margin: 0 auto; }

/* アコーディオン */
.faq-list { list-style: none; padding: 0; margin: 0; }

.faq-item {
    background: #fff;
    border-radius: 10px;
    margin-bottom: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid #f0ebe3;
    overflow: hidden;
    animation: fq-in 0.4s ease backwards;
}
.faq-item:nth-child(1) { animation-delay: 0.04s; }
.faq-item:nth-child(2) { animation-delay: 0.08s; }
.faq-item:nth-child(3) { animation-delay: 0.12s; }
.faq-item:nth-child(4) { animation-delay: 0.16s; }
.faq-item:nth-child(5) { animation-delay: 0.20s; }
.faq-item:nth-child(6) { animation-delay: 0.24s; }
@keyframes fq-in {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

.faq-q {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 22px;
    cursor: pointer;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    transition: background 0.15s;
}
.faq-q:hover { background: #fefcf8; }
.faq-q-icon {
    width: 28px; height: 28px; border-radius: 50%;
    background: #fef9f0; color: #b38b59; border: 1px solid #e8d5b7;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem; font-weight: 700; flex-shrink: 0; font-family: serif;
}
.faq-q-text { flex: 1; font-size: 0.96rem; font-weight: 500; color: #3d2f25; line-height: 1.5; }
.faq-q-arrow {
    color: #b38b59; font-size: 0.75rem; flex-shrink: 0;
    transition: transform 0.25s cubic-bezier(0.4,0,0.2,1);
}
.faq-item.open .faq-q-arrow { transform: rotate(180deg); }
.faq-item.open .faq-q { background: #fef9f0; }

.faq-a {
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows 0.3s cubic-bezier(0.4,0,0.2,1);
}
.faq-item.open .faq-a { grid-template-rows: 1fr; }
.faq-a-inner {
    min-height: 0;
    overflow: hidden;
}
.faq-a-body {
    padding: 4px 22px 20px 64px;
    font-size: 0.9rem;
    color: #6b5b4e;
    line-height: 1.85;
    border-top: 1px solid #f0ebe3;
    padding-top: 16px;
    white-space: pre-line;
}

/* 空状態 */
.fq-empty { text-align: center; padding: 60px 20px; color: #c0b0a0; }
.fq-empty i { font-size: 2.5rem; opacity: 0.3; display: block; margin-bottom: 16px; }

@media (min-width: 768px) {
    .fq-wrap { padding: 80px 24px 100px; }
}
</style>
@endpush

@section('content')
<section class="fq-banner">
    <img src="{{ asset('img/チャペル.jpg') }}" alt="" class="fq-banner__img">
    <div class="fq-banner__overlay"></div>
    <div class="fq-banner__text">
        <span class="fq-banner__eyebrow">FAQ · よくある質問</span>
        <h1 class="fq-banner__title">よくある質問</h1>
    </div>
</section>

<div class="fq-wrap">
    <div class="fq-intro">
        <span class="fq-section-en">FAQ</span>
        <h2 class="fq-section-ja">よくある質問</h2>
        <div class="fq-rule"></div>
    </div>

    @if ($faqs->isEmpty())
    <div class="fq-empty">
        <i class="fa-regular fa-circle-question"></i>
        <p>Q&amp;Aは準備中です</p>
    </div>
    @else
    <ul class="faq-list">
        @foreach ($faqs as $faq)
        <li class="faq-item">
            <div class="faq-q" onclick="this.closest('.faq-item').classList.toggle('open')">
                <div class="faq-q-icon">Q</div>
                <span class="faq-q-text">{{ $faq->question }}</span>
                <i class="fa-solid fa-chevron-down faq-q-arrow"></i>
            </div>
            <div class="faq-a">
                <div class="faq-a-inner">
                    <div class="faq-a-body">{{ $faq->answer }}</div>
                </div>
            </div>
        </li>
        @endforeach
    </ul>
    @endif
</div>
@endsection
