@extends('layouts.app')
@section('title', 'ホーム | ' . ($setting?->groom_name ?? '') . ' & ' . ($setting?->bride_name ?? '') . ' Wedding')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endpush

@section('content')

{{-- ══ HERO ════════════════════════════════════════════════════════ --}}
<section class="home-hero">
    <img src="{{ asset('img/チャペル.jpg') }}" alt="チャペル" class="home-hero__img">
    <div class="home-hero__overlay"></div>

    <div class="home-hero__text">
        <span class="home-hero__eyebrow">Wedding Invitation</span>
        <h1 class="home-hero__names">
            @if ($setting)
                <span>{{ $setting->groom_name_en ?: $setting->groom_name }}</span>
                <em>&amp;</em>
                <span>{{ $setting->bride_name_en ?: $setting->bride_name }}</span>
            @else
                <span>Kakeru</span><em>&amp;</em><span>Mirai</span>
            @endif
        </h1>
        <div class="home-hero__rule"></div>
        <p class="home-hero__date">
            @if ($setting)
                {{ $setting->ceremony_date->format('F j, Y') }}
                &nbsp;|&nbsp;
                {{ $setting->ceremonyDayOfWeek() }}曜日
            @else
                July 19, 2026 &nbsp;|&nbsp; Sunday
            @endif
        </p>
    </div>

    <div class="home-hero__scroll">
        <span>scroll</span>
        <div class="home-hero__arrow"></div>
    </div>
</section>

{{-- ══ RSVP ステータス ══════════════════════════════════════════════ --}}
@if ($profile && $profile->participation === 'attending')
<section class="home-rsvp home-rsvp--attending">
    <div class="home-rsvp__card">
        <span class="home-rsvp__icon">✦</span>
        <div class="home-rsvp__body">
            <p><strong>{{ $profile->fullName() }} 様</strong>、ご出席のご登録ありがとうございます。</p>
            @if (!$deadlinePassed)
            <p class="home-rsvp__sub">内容を変更したい場合は招待状ページからいつでも更新できます。
                @if ($setting?->rsvp_deadline)（締め切り：{{ $setting->rsvp_deadline->format('Y年n月j日') }}）@endif
            </p>
            @else
            <p class="home-rsvp__sub">受付は終了しました。変更がある場合は直接ご連絡ください。</p>
            @endif
        </div>
        <a href="{{ route('invitation') }}" class="home-rsvp__btn home-rsvp__btn--outline">招待状を確認</a>
    </div>
</section>

@elseif ($profile && $profile->participation === 'declining')
<section class="home-rsvp home-rsvp--declining">
    <div class="home-rsvp__card">
        <span class="home-rsvp__icon">✦</span>
        <div class="home-rsvp__body">
            <p><strong>{{ $profile->fullName() }} 様</strong>、欠席のご連絡ありがとうございます。</p>
            @if (!$deadlinePassed)
            <p class="home-rsvp__sub">変更がある場合はいつでも回答を更新できます。
                @if ($setting?->rsvp_deadline)（締め切り：{{ $setting->rsvp_deadline->format('Y年n月j日') }}）@endif
            </p>
            @else
            <p class="home-rsvp__sub">受付は終了しました。変更がある場合は直接ご連絡ください。</p>
            @endif
        </div>
        <a href="{{ route('invitation') }}" class="home-rsvp__btn home-rsvp__btn--outline">回答を確認</a>
    </div>
</section>

@else
<section class="home-rsvp home-rsvp--pending">
    <div class="home-rsvp__card home-rsvp__card--cta">
        @if ($deadlinePassed)
        <p class="home-rsvp__notice">出欠回答の受付は終了しました。</p>
        <p class="home-rsvp__sub">
            @if ($setting?->rsvp_deadline)締め切り：{{ $setting->rsvp_deadline->format('Y年n月j日') }}@endif
        </p>
        @else
        <p class="home-rsvp__notice">まだご出欠のご回答が届いておりません。</p>
        <p class="home-rsvp__sub">ご都合のほど、下記よりお知らせください。
            @if ($setting?->rsvp_deadline)（締め切り：{{ $setting->rsvp_deadline->format('Y年n月j日') }}）@endif
        </p>
        <a href="{{ route('invitation') }}" class="home-rsvp__btn">出欠を回答する</a>
        @endif
    </div>
</section>
@endif

{{-- ══ 当日のお願い（タスクが割り当てられている場合のみ表示）══════════ --}}
@if ($homeTasks->isNotEmpty())
<section class="home-tasks">
    <div class="home-tasks__inner">
        <span class="home-section__en">Your Role</span>
        <h2 class="home-section__ja">当日のお願い</h2>
        <div class="home-section__rule"></div>

        @foreach ($homeTasks as $assignment)
        <div class="home-tasks__card">
            <div class="home-tasks__card-head">
                <span class="home-tasks__icon"><i class="fa-solid fa-clipboard-check"></i></span>
                <div>
                    <p class="home-tasks__name">{{ $assignment->task->name }}</p>
                    @if ($assignment->custom_time)
                    <p class="home-tasks__time">
                        <i class="fa-regular fa-clock" style="font-size:0.75rem;"></i>
                        {{ $assignment->custom_time }}
                    </p>
                    @endif
                </div>
            </div>
            @if ($assignment->task->description)
            <p class="home-tasks__desc">{{ $assignment->task->description }}</p>
            @endif
            @if ($assignment->custom_note)
            <p class="home-tasks__note">{{ $assignment->custom_note }}</p>
            @endif
        </div>
        @endforeach

        <div style="text-align:center;margin-top:20px;">
            <a href="{{ route('profile.edit') }}" class="home-tasks__link">
                詳細をプロフィールで確認する <i class="fa-solid fa-chevron-right" style="font-size:0.7rem;"></i>
            </a>
        </div>
    </div>
</section>
@endif

{{-- ══ MESSAGE ══════════════════════════════════════════════════════ --}}
<section class="home-message">
    <div class="home-message__inner">
        <span class="home-section__en">Message</span>
        <h2 class="home-section__ja">ご挨拶</h2>
        <div class="home-section__rule"></div>
        <p class="home-message__body">
            @if ($setting?->message)
                {!! nl2br(e($setting->message)) !!}
            @else
                これまで支えてくださった皆さまへ<br>
                感謝の気持ちを込めて<br>
                ささやかな祝宴を催します。
            @endif
        </p>
        @if ($setting)
        <p class="home-message__sign">
            {{ $setting->groom_name }} &nbsp;／&nbsp; {{ $setting->bride_name }}
        </p>
        @endif
    </div>
</section>

{{-- ══ DETAILS（日時・会場） ══════════════════════════════════════ --}}
<section class="home-details">
    <div class="home-details__inner">
        <div class="home-details__card">
            <p class="home-details__label">Date</p>
            @if ($setting)
            <p class="home-details__value">
                {{ $setting->ceremonyDateJa() }}（{{ $setting->ceremonyDayOfWeek() }}）
            </p>
            <p class="home-details__sub">
                挙式 {{ $setting->ceremonyTimeFormatted() }}
                ／ 披露宴 {{ $setting->receptionTimeFormatted() }}〜
            </p>
            @else
            <p class="home-details__value">2026年7月19日（日）</p>
            <p class="home-details__sub">挙式 14:00 ／ 披露宴 15:30〜</p>
            @endif
        </div>
        <div class="home-details__card">
            <p class="home-details__label">Venue</p>
            @if ($setting)
            <p class="home-details__value">{{ $setting->venue_name }}</p>
            <p class="home-details__sub">{{ $setting->venue_address }}</p>
            <div class="home-details__links">
                @if ($setting->venue_url)
                <a href="{{ $setting->venue_url }}" target="_blank" rel="noopener"
                   class="home-details__map-btn">
                    <i class="fa-brands fa-google"></i> Google Maps
                </a>
                @endif
                <a href="{{ route('access') }}" class="home-details__access-btn">
                    <i class="fa-solid fa-map-location-dot"></i> アクセス詳細
                </a>
            </div>
            @else
            <p class="home-details__value">◯◯チャペル</p>
            <p class="home-details__sub">◯◯県◯◯市◯◯町</p>
            @endif
        </div>
    </div>
</section>

{{-- ══ NEWS ═════════════════════════════════════════════════════════ --}}
<section class="home-news">
    <div class="home-news__inner">
        <span class="home-section__en">News</span>
        <h2 class="home-section__ja">お知らせ</h2>
        <div class="home-section__rule"></div>

        <ul class="home-news__list">
            <li class="home-news__item">
                <time class="home-news__date">2025.12.24</time>
                <span class="home-news__tag">New</span>
                <p class="home-news__text">当日のご案内</p>
            </li>
            <li class="home-news__item">
                <time class="home-news__date">2025.10.16</time>
                <span class="home-news__tag">Info</span>
                <p class="home-news__text">WEB招待状の記入のお願い</p>
            </li>
        </ul>
    </div>
</section>

@endsection

@push('scripts')
<script>
// ヒーローと #top_info のアニメーションは main.js が担当
// ここではホームページ固有のスクロールフェードのみ
(function () {
    const fadeEls = document.querySelectorAll(
        '.home-message__inner, .home-details__inner, .home-news__inner, .home-rsvp__card'
    );
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('is-visible');
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.15 });
    fadeEls.forEach(el => obs.observe(el));
})();
</script>
@endpush
