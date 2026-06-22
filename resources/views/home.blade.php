@extends('layouts.app')
@section('title', 'ホーム | ' . ($setting?->groom_name ?? '') . ' & ' . ($setting?->bride_name ?? '') . ' Wedding')

@push('styles')
<link rel="stylesheet" href="{{ css_asset('css/home.css') }}">
@endpush

@section('content')

{{-- ══ HERO ════════════════════════════════════════════════════════ --}}
@php
    $heroType   = $setting?->hero_type ?? 'slideshow';
    $heroImages = \App\Models\SiteImage::forHero();
@endphp
<section class="home-hero"
    data-hero-type="{{ $heroType }}"
    data-interval="{{ $setting?->hero_interval ?? 5000 }}">

    {{-- 動画 --}}
    @if ($heroType === 'video' && $setting?->hero_video_path)
    <video class="home-hero__video" autoplay muted loop playsinline>
        <source src="{{ asset('storage/' . $setting->hero_video_path) }}" type="video/mp4">
    </video>

    {{-- スライドショー --}}
    @elseif ($heroImages->isNotEmpty())
    @foreach ($heroImages as $i => $img)
    <img src="{{ $img->url }}" alt=""
         class="home-hero__slide{{ $i === 0 ? ' is-active' : '' }}">
    @endforeach

    {{-- フォールバック（未設定時） --}}
    @else
    <img src="{{ asset('img/チャペル.jpg') }}" alt="チャペル" class="home-hero__img">
    @endif

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
            @if ($setting?->ceremony_date)
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

@if ($needsEmailRegistration)
<section class="home-rsvp home-rsvp--pending">
    <div class="home-rsvp__card home-rsvp__card--cta">
        <p class="home-rsvp__notice">メールアドレスを登録してください。</p>
        <p class="home-rsvp__sub">パスワード再設定や連絡に使います。プロフィール画面で登録できます。</p>
        <a href="{{ route('profile.edit') }}" class="home-rsvp__btn">プロフィールを開く</a>
    </div>
</section>
@endif

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

{{-- ══ インフォメーション（返信期日・シャトルバス）══════════════════ --}}
@php
    $showNotice = $setting?->rsvp_deadline || ($setting?->shuttle_bus_enabled && $setting?->shuttle_bus_departure);
@endphp
@if ($showNotice)
<section class="home-notice">
    <div class="home-notice__inner">

        {{-- 返信期日 --}}
        @if ($setting?->rsvp_deadline)
        <div class="home-notice__item">
            <div class="home-notice__icon-wrap">
                <i class="fa-regular fa-calendar-check"></i>
            </div>
            <div class="home-notice__body">
                <p class="home-notice__label">ご返信期日</p>
                <p class="home-notice__text">
                    {{ $setting->rsvp_deadline->format('Y年n月j日') }}までにご回答くださいますようお願い申し上げます。
                </p>
            </div>
        </div>
        @if ($setting?->shuttle_bus_enabled && $setting?->shuttle_bus_departure)
        <div class="home-notice__divider"></div>
        @endif
        @endif

        {{-- シャトルバス --}}
        @if ($setting?->shuttle_bus_enabled && $setting?->shuttle_bus_departure)
        <div class="home-notice__item">
            <div class="home-notice__icon-wrap">
                <i class="fa-solid fa-bus-simple"></i>
            </div>
            <div class="home-notice__body">
                <p class="home-notice__label">シャトルバスのご案内</p>
                <p class="home-notice__text">
                    当方にてシャトルバスをご準備しております<br>
                    ご利用の方は{{ $setting->shuttle_bus_departure }}まで<br>
                    お越しくださいますようお願い申し上げます
                </p>
                @if ($setting->shuttle_bus_times)
                <p class="home-notice__time">
                    <i class="fa-regular fa-clock"></i>&ensp;出発時間：{{ $setting->shuttle_bus_times }}
                </p>
                @endif
                @if ($setting->shuttle_bus_note)
                <p class="home-notice__note">{{ $setting->shuttle_bus_note }}</p>
                @endif
                @if ($setting->shuttle_bus_map_url)
                <a href="{{ $setting->shuttle_bus_map_url }}" target="_blank" rel="noopener"
                   class="home-notice__map-btn">
                    <i class="fa-brands fa-google"></i> 乗り場の地図を見る
                </a>
                @endif
            </div>
        </div>
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
            {{-- カードヘッダー --}}
            <div class="home-tasks__card-top">
                <span class="home-tasks__icon"><i class="fa-solid fa-clipboard-check"></i></span>
                <div class="home-tasks__header-text">
                    <p class="home-tasks__name">{{ $assignment->task->name }}</p>
                    @if ($assignment->custom_time)
                    <p class="home-tasks__time">
                        <i class="fa-regular fa-clock"></i>{{ $assignment->custom_time }}
                    </p>
                    @endif
                </div>
            </div>

            {{-- カードボディ --}}
            <div class="home-tasks__body">
                {{-- 役割別プログラム --}}
                @if ($assignment->task->programItems->isNotEmpty())
                <div class="home-tasks__program">
                    <p class="home-tasks__program-label">Program</p>
                    @foreach ($assignment->task->programItems as $pi)
                    <div class="home-tasks__program-item">
                        <span class="home-tasks__program-time">{{ $pi->start_time ?? '—' }}</span>
                        <span class="home-tasks__program-dot"></span>
                        <div class="home-tasks__program-info">
                            <p class="home-tasks__program-title">{{ $pi->title }}</p>
                            @if ($pi->description)
                            <p class="home-tasks__program-desc">{{ $pi->description }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                @if ($assignment->task->description)
                <p class="home-tasks__desc">{{ $assignment->task->description }}</p>
                @endif
                @if ($assignment->custom_note)
                <p class="home-tasks__note">{{ $assignment->custom_note }}</p>
                @endif
            </div>

            <div class="home-tasks__footer">
                <a href="{{ route('profile.edit') }}" class="home-tasks__link">
                    プロフィールで詳細を確認 <i class="fa-solid fa-chevron-right" style="font-size:0.65rem;"></i>
                </a>
            </div>
        </div>
        @endforeach
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
            @if ($setting?->ceremony_date)
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

        @if ($news->isEmpty())
        <p style="text-align:center;color:#c0b0a0;font-size:0.9rem;padding:20px 0;">お知らせはありません</p>
        @else
        <div class="home-news__list">
            @foreach ($news as $item)
            @php $layout = $item->layout ?? 'text'; @endphp

            {{-- ── テキストのみ ── --}}
            @if ($layout === 'text' || !$item->image_url)
            <a href="{{ route('news.show', $item->id) }}" class="hn-item hn-text hn-link">
                <div class="hn-meta">
                    <time class="hn-date">{{ $item->published_date->format('Y.m.d') }}</time>
                    @if ($item->tag)<span class="hn-tag">{{ $item->tag }}</span>@endif
                </div>
                @if ($item->title)<p class="hn-title">{{ $item->title }}</p>@endif
                <p class="hn-body">{{ Str::limit($item->body, 120) }}</p>
                <span class="hn-more">続きを読む <i class="fa-solid fa-arrow-right" style="font-size:0.65rem;"></i></span>
            </a>

            {{-- ── 画像上 + テキスト下 ── --}}
            @elseif ($layout === 'top')
            <a href="{{ route('news.show', $item->id) }}" class="hn-item hn-top hn-link">
                <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="hn-img hn-img--top">
                <div class="hn-content">
                    <div class="hn-meta">
                        <time class="hn-date">{{ $item->published_date->format('Y.m.d') }}</time>
                        @if ($item->tag)<span class="hn-tag">{{ $item->tag }}</span>@endif
                    </div>
                    @if ($item->title)<p class="hn-title">{{ $item->title }}</p>@endif
                    <p class="hn-body">{{ Str::limit($item->body, 100) }}</p>
                    <span class="hn-more">続きを読む <i class="fa-solid fa-arrow-right" style="font-size:0.65rem;"></i></span>
                </div>
            </a>

            {{-- ── 画像左 + テキスト右 ── --}}
            @elseif ($layout === 'left')
            <a href="{{ route('news.show', $item->id) }}" class="hn-item hn-side hn-side--left hn-link">
                <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="hn-img hn-img--side">
                <div class="hn-content">
                    <div class="hn-meta">
                        <time class="hn-date">{{ $item->published_date->format('Y.m.d') }}</time>
                        @if ($item->tag)<span class="hn-tag">{{ $item->tag }}</span>@endif
                    </div>
                    @if ($item->title)<p class="hn-title">{{ $item->title }}</p>@endif
                    <p class="hn-body">{{ Str::limit($item->body, 100) }}</p>
                    <span class="hn-more">続きを読む <i class="fa-solid fa-arrow-right" style="font-size:0.65rem;"></i></span>
                </div>
            </a>

            {{-- ── 画像右 + テキスト左 ── --}}
            @elseif ($layout === 'right')
            <a href="{{ route('news.show', $item->id) }}" class="hn-item hn-side hn-side--right hn-link">
                <div class="hn-content">
                    <div class="hn-meta">
                        <time class="hn-date">{{ $item->published_date->format('Y.m.d') }}</time>
                        @if ($item->tag)<span class="hn-tag">{{ $item->tag }}</span>@endif
                    </div>
                    @if ($item->title)<p class="hn-title">{{ $item->title }}</p>@endif
                    <p class="hn-body">{{ Str::limit($item->body, 100) }}</p>
                    <span class="hn-more">続きを読む <i class="fa-solid fa-arrow-right" style="font-size:0.65rem;"></i></span>
                </div>
                <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="hn-img hn-img--side">
            </a>

            {{-- ── フルイメージ ── --}}
            @elseif ($layout === 'hero')
            <a href="{{ route('news.show', $item->id) }}" class="hn-item hn-hero hn-link">
                <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="hn-img hn-img--hero">
                <div class="hn-hero__overlay">
                    <div class="hn-meta hn-meta--light">
                        <time class="hn-date">{{ $item->published_date->format('Y.m.d') }}</time>
                        @if ($item->tag)<span class="hn-tag hn-tag--light">{{ $item->tag }}</span>@endif
                    </div>
                    @if ($item->title)<p class="hn-title hn-title--light">{{ $item->title }}</p>@endif
                    <span class="hn-more" style="color:rgba(255,255,255,0.8);">続きを読む <i class="fa-solid fa-arrow-right" style="font-size:0.65rem;"></i></span>
                </div>
            </a>

            {{-- ── カード型 ── --}}
            @elseif ($layout === 'card')
            <a href="{{ route('news.show', $item->id) }}" class="hn-item hn-card hn-link">
                <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="hn-img hn-img--card">
                <div class="hn-card__body">
                    <div class="hn-meta">
                        <time class="hn-date">{{ $item->published_date->format('Y.m.d') }}</time>
                        @if ($item->tag)<span class="hn-tag">{{ $item->tag }}</span>@endif
                    </div>
                    @if ($item->title)<p class="hn-title">{{ $item->title }}</p>@endif
                    <p class="hn-body">{{ Str::limit($item->body, 100) }}</p>
                    <span class="hn-more">続きを読む <i class="fa-solid fa-arrow-right" style="font-size:0.65rem;"></i></span>
                </div>
            </a>
            @endif

            @endforeach
        </div>
        @endif
    </div>
</section>

@endsection

@push('scripts')
<script>
// ── ヒーロースライドショー ──────────────────────────
(function () {
    const hero = document.querySelector('.home-hero[data-hero-type="slideshow"]');
    if (!hero) return;
    const slides = hero.querySelectorAll('.home-hero__slide');
    if (slides.length < 2) return;
    let current = 0;
    const interval = parseInt(hero.dataset.interval || '5000', 10);
    setInterval(() => {
        slides[current].classList.remove('is-active');
        current = (current + 1) % slides.length;
        slides[current].classList.add('is-active');
    }, interval);
})();
// ── スクロールフェード ─────────────────────────────
(function () {
    const fadeEls = document.querySelectorAll(
        '.home-message__inner, .home-details__inner, .home-news__inner, .home-rsvp__card, .home-tasks__inner, .home-notice__inner'
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
