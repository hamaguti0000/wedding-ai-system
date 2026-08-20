@extends('layouts.app')
@section('title', 'プロフィール | ' . ($setting?->groom_name_en ?: $setting?->groom_name ?? 'Wedding'))

@push('styles')
<style>
main { padding: 0; text-align: initial; }

/* ── バナー ── */
.pf-banner {
    position: relative; height: 32vh; min-height: 200px;
    overflow: hidden; display: flex; align-items: center; justify-content: center; text-align: center;
    padding-top: 60px; box-sizing: border-box;
}
.pf-banner__img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; filter: brightness(0.38) saturate(0.7); }
.pf-banner__overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(20,12,4,0.1), rgba(20,12,4,0.55)); }
.pf-banner__text { position: relative; z-index: 2; color: #fff; padding: 0 20px; }
.pf-banner__eyebrow { display: block; font-size: 0.6rem; letter-spacing: 5px; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 10px; font-family: 'Noto Sans JP', sans-serif; }
.pf-banner__title { font-family: 'Playfair Display', serif; font-size: clamp(1.6rem, 5vw, 2.6rem); font-weight: 400; letter-spacing: 2px; margin: 0; }

/* ── コンテンツ ── */
.pf-wrap { max-width: 860px; margin: 0 auto; padding: 64px 20px 80px; }
.pf-intro { text-align: center; margin-bottom: 52px; }
.pf-section-en { display: block; font-size: 0.65rem; letter-spacing: 5px; text-transform: uppercase; color: #b38b59; margin-bottom: 6px; font-family: 'Noto Sans JP', sans-serif; }
.pf-section-ja { font-family: 'Playfair Display', serif; font-size: 1.7rem; font-weight: 400; color: #3d2f25; margin: 0 0 14px; }
.pf-rule { width: 40px; height: 1px; background: #b38b59; margin: 0 auto; }

/* ── 選択カード ── */
.pf-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 28px;
}
.pf-card {
    background: #fff;
    border: 1px solid #e8d5b7;
    border-radius: 16px;
    padding: 40px 28px 36px;
    text-align: center;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0;
    box-shadow: 0 4px 24px rgba(179,139,89,0.08);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    position: relative;
    overflow: hidden;
}
.pf-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #e8d5b7, #b38b59, #e8d5b7);
    opacity: 0;
    transition: opacity 0.25s;
}
.pf-card:hover { transform: translateY(-4px); box-shadow: 0 10px 36px rgba(179,139,89,0.16); }
.pf-card:hover::before { opacity: 1; }

.pf-card__photo-wrap {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #e8d5b7;
    margin-bottom: 24px;
    flex-shrink: 0;
    background: #fdfaf6;
    display: flex;
    align-items: center;
    justify-content: center;
}
.pf-card__photo-wrap img {
    width: 100%; height: 100%;
    object-fit: cover;
}
.pf-card__photo-placeholder {
    font-size: 3.5rem;
    color: #d4b896;
}

.pf-card__role {
    font-size: 0.65rem;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #b38b59;
    font-family: 'Noto Sans JP', sans-serif;
    font-weight: 700;
    margin-bottom: 8px;
}
.pf-card__name-en {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem;
    font-weight: 400;
    color: #3d2f25;
    letter-spacing: 2px;
    line-height: 1.2;
    margin-bottom: 4px;
}
.pf-card__name-ja {
    font-size: 0.88rem;
    color: #9b8573;
    margin-bottom: 24px;
    font-weight: 400;
}
.pf-card__btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 10px 24px;
    background: #b38b59;
    color: #fff;
    border-radius: 30px;
    font-size: 0.8rem;
    letter-spacing: 1.5px;
    font-family: 'Noto Sans JP', sans-serif;
    font-weight: 500;
    margin-top: auto;
    transition: background 0.2s;
    text-decoration: none;
}
.pf-card:hover .pf-card__btn { background: #9a7447; }

/* ─ アンパサンド区切り ─ */
.pf-ampersand {
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    font-style: italic;
    color: #d4b896;
    padding: 0 4px;
    align-self: center;
}

@media (max-width: 639px) {
    .pf-cards { grid-template-columns: 1fr; gap: 20px; }
    .pf-ampersand { display: none; }
    .pf-card { padding: 32px 20px 28px; }
    .pf-card__photo-wrap { width: 110px; height: 110px; }
    .pf-wrap { padding: 48px 16px 64px; }
}
@media (min-width: 768px) {
    .pf-banner { padding-top: 80px; }
    .pf-wrap { padding: 80px 24px 100px; }
}
</style>
@endpush

@section('content')
<section class="pf-banner">
    @include('partials.rotating-banner', ['class' => 'pf-banner__img'])
    <div class="pf-banner__overlay"></div>
    <div class="pf-banner__text">
        <span class="pf-banner__eyebrow">Profile · おふたりのご紹介</span>
        <h1 class="pf-banner__title">プロフィール</h1>
    </div>
</section>

<div class="pf-wrap">
    <div class="pf-intro">
        <span class="pf-section-en">Our Story</span>
        <h2 class="pf-section-ja">おふたりのご紹介</h2>
        <div class="pf-rule"></div>
    </div>

    <div class="pf-cards">
        {{-- 新郎カード --}}
        <a href="{{ route('profiles.show', 'groom') }}" class="pf-card">
            <div class="pf-card__photo-wrap">
                @if ($setting?->groom_photo)
                    <img src="{{ asset('storage/' . $setting->groom_photo) }}?v={{ $setting->updated_at?->timestamp }}" alt="新郎">
                @else
                    <span class="pf-card__photo-placeholder">
                        <i class="fa-solid fa-user"></i>
                    </span>
                @endif
            </div>
            <span class="pf-card__role">Groom · 新郎</span>
            <p class="pf-card__name-en">{{ $setting?->groom_name_en ?: ($setting?->groom_name ?? 'Groom') }}</p>
            <p class="pf-card__name-ja">{{ $setting?->groom_name }}</p>
            <span class="pf-card__btn">
                プロフィールを見る <i class="fa-solid fa-arrow-right" style="font-size:0.7rem;"></i>
            </span>
        </a>

        {{-- 新婦カード --}}
        <a href="{{ route('profiles.show', 'bride') }}" class="pf-card">
            <div class="pf-card__photo-wrap">
                @if ($setting?->bride_photo)
                    <img src="{{ asset('storage/' . $setting->bride_photo) }}?v={{ $setting->updated_at?->timestamp }}" alt="新婦">
                @else
                    <span class="pf-card__photo-placeholder">
                        <i class="fa-solid fa-user"></i>
                    </span>
                @endif
            </div>
            <span class="pf-card__role">Bride · 新婦</span>
            <p class="pf-card__name-en">{{ $setting?->bride_name_en ?: ($setting?->bride_name ?? 'Bride') }}</p>
            <p class="pf-card__name-ja">{{ $setting?->bride_name }}</p>
            <span class="pf-card__btn">
                プロフィールを見る <i class="fa-solid fa-arrow-right" style="font-size:0.7rem;"></i>
            </span>
        </a>
    </div>
</div>
@endsection
