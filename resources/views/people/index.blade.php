@extends('layouts.app')
@section('title', '参加者一覧 | Wedding')

@push('styles')
<style>
main { padding: 0; text-align: initial; }

.gl-banner {
    position: relative; height: 32vh; min-height: 200px;
    overflow: hidden; display: flex; align-items: center; justify-content: center; text-align: center;
    padding-top: 60px; box-sizing: border-box;
}
.gl-banner__img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; filter: brightness(0.38) saturate(0.7); }
.gl-banner__overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(20,12,4,0.1), rgba(20,12,4,0.55)); }
.gl-banner__text { position: relative; z-index: 2; color: #fff; padding: 0 20px; }
.gl-banner__eyebrow { display: block; font-size: 0.6rem; letter-spacing: 5px; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 10px; font-family: 'Noto Sans JP', sans-serif; }
.gl-banner__title { font-family: 'Playfair Display', serif; font-size: clamp(1.6rem, 5vw, 2.6rem); font-weight: 400; letter-spacing: 2px; margin: 0; }

.gl-wrap { max-width: 1100px; margin: 0 auto; padding: 60px 20px 80px; }
.gl-intro { text-align: center; margin-bottom: 48px; }
.gl-section-en { display: block; font-size: 0.65rem; letter-spacing: 5px; text-transform: uppercase; color: #b38b59; margin-bottom: 6px; font-family: 'Noto Sans JP', sans-serif; }
.gl-section-ja { font-family: 'Playfair Display', serif; font-size: 1.7rem; font-weight: 400; color: #3d2f25; margin: 0 0 14px; }
.gl-rule { width: 40px; height: 1px; background: #b38b59; margin: 0 auto; }

.people-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 14px;
}
.people-card {
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    padding: 18px 12px; background: #fff; border: 1px solid #f0ebe3; border-radius: 12px;
    text-decoration: none; transition: box-shadow 0.15s, transform 0.15s;
}
.people-card:hover { box-shadow: 0 6px 18px rgba(0,0,0,0.08); transform: translateY(-2px); }
.people-avatar {
    width: 64px; height: 64px; border-radius: 50%; overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Playfair Display', serif; font-size: 1.4rem; color: #b38b59;
    background: linear-gradient(135deg, #b38b59 0%, #d4a870 100%);
    border-style: solid;
    flex-shrink: 0;
}
.people-avatar img { width: 100%; height: 100%; object-fit: cover; }
.people-name { font-size: 0.86rem; color: #3d2f25; text-align: center; line-height: 1.4; }

.gl-empty { text-align: center; padding: 60px 20px; color: #c0b0a0; }
.gl-empty i { font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 16px; }

@media (min-width: 768px) {
    .gl-banner { padding-top: 80px; }
    .gl-wrap { padding: 80px 24px 100px; }
}
</style>
@endpush

@section('content')
<section class="gl-banner">
    <img src="{{ ($bannerImage?->url ?? asset('img/チャペル.jpg')) }}" alt="" class="gl-banner__img">
    <div class="gl-banner__overlay"></div>
    <div class="gl-banner__text">
        <span class="gl-banner__eyebrow">People · 参加者一覧</span>
        <h1 class="gl-banner__title">参加者一覧</h1>
    </div>
</section>

<div class="gl-wrap">
    <div class="gl-intro">
        <span class="gl-section-en">People</span>
        <h2 class="gl-section-ja">みんなの写真を見る</h2>
        <div class="gl-rule"></div>
        <p style="font-size:0.85rem;color:#9b8573;margin-top:14px;">気になる人をタップすると、その人が写っている写真だけを見返せます</p>
    </div>

    @if ($people->isEmpty())
    <div class="gl-empty">
        <i class="fa-regular fa-user"></i>
        <p>参加者情報はまだありません</p>
    </div>
    @else
    <div class="people-grid">
        @foreach ($people as $person)
        <a href="{{ route('people.show', $person) }}" class="people-card">
            <div class="people-avatar"
                 style="border-width:{{ $person->avatarBorderWidth() }}px; border-color:{{ $person->avatarBorderColor() }}; @if ($person->avatarType() === 'emoji') background: {{ $person->avatarBackgroundColor() }}; @endif">
                @if ($person->avatarType() === 'photo' && $person->avatarImageUrl())
                    <img src="{{ $person->avatarImageUrl() }}" alt="">
                @elseif ($person->avatarType() === 'emoji' && $person->avatar_emoji)
                    <span>{{ $person->avatar_emoji }}</span>
                @else
                    {{ $person->avatarInitial() }}
                @endif
            </div>
            <span class="people-name">{{ $person->guestProfile?->fullName() ?: $person->name }}</span>
        </a>
        @endforeach
    </div>
    @endif
</div>
@endsection
