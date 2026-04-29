@extends('layouts.app')
@section('title', 'プロフィール | Kakeru & Mirai Wedding')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endpush

@section('content')

@php
    $initial = mb_substr($user->name ?? '?', 0, 1, 'UTF-8');
@endphp

<div class="pf-wrap">

    {{-- サクセスメッセージ --}}
    @if (session('success'))
    <div class="pf-alert-success">{{ session('success') }}</div>
    @endif

    {{-- ══ CARD 1: ユーザー情報 ══════════════════════════ --}}
    <div class="pf-card">
        <div class="pf-user">
            <div class="pf-avatar" aria-hidden="true">{{ $initial }}</div>
            <div class="pf-user-info">
                <p class="pf-user-name">{{ $user->name }}</p>
                <p class="pf-user-email">{{ $user->email }}</p>
                @if ($user->isAdmin())
                <span class="pf-badge pf-badge--admin">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>管理者
                </span>
                @else
                <span class="pf-badge pf-badge--guest">
                    <i class="fa-solid fa-user" aria-hidden="true"></i>ゲスト
                </span>
                @endif
            </div>
        </div>

        @if ($user->isAdmin())
        <div class="pf-divider"></div>
        <div class="pf-actions">
            <a href="{{ route('admin.dashboard') }}" class="pf-btn pf-btn--primary">
                <i class="fa-solid fa-list-check" aria-hidden="true"></i>管理ダッシュボードへ
            </a>
        </div>
        @endif
    </div>

    {{-- ゲストの場合のみ表示 --}}
    @if (!$user->isAdmin())

    {{-- ══ CARD 2: 回答状況 ══════════════════════════════ --}}
    <div class="pf-card">
        <span class="pf-section-en">RSVP Status</span>
        <h2 class="pf-section-ja">回答状況</h2>
        <div class="pf-section-rule"></div>

        @php
            $participation = $profile?->participation ?? 'pending';
            $rsvpLabels = ['attending' => '出席', 'declining' => '欠席', 'pending' => '未回答'];
            $rsvpIcons  = ['attending' => '✦', 'declining' => '✧', 'pending' => '○'];
        @endphp

        <div class="pf-rsvp-banner pf-rsvp-banner--{{ $participation }}">
            <span class="pf-rsvp-icon">{{ $rsvpIcons[$participation] }}</span>
            <div>
                <p class="pf-rsvp-label">{{ $rsvpLabels[$participation] }}</p>
                @if ($profile?->responded_at)
                <p class="pf-rsvp-date">{{ $profile->responded_at->format('Y年m月d日 H:i') }} 回答</p>
                @else
                <p class="pf-rsvp-date">まだ回答が届いていません</p>
                @endif
            </div>
        </div>

        @if ($profile && $profile->participation !== 'pending')

        {{-- ゲスト情報 --}}
        @if ($profile->guest_side || $profile->relationship)
        <dl class="pf-grid" style="margin-bottom: 18px;">
            @if ($profile->guest_side)
            <div class="pf-item">
                <dt>お立場</dt>
                <dd>
                    <div class="pf-tags">
                        <span class="pf-tag">{{ $profile->guestSideLabel() }}</span>
                    </div>
                </dd>
            </div>
            @endif
            @if ($profile->relationship)
            <div class="pf-item">
                <dt>ご関係</dt>
                <dd>
                    <div class="pf-tags">
                        <span class="pf-tag">{{ $profile->relationshipLabel() }}</span>
                    </div>
                </dd>
            </div>
            @endif
        </dl>
        <div class="pf-divider"></div>
        @endif

        {{-- 基本情報 --}}
        <dl class="pf-grid">
            <div class="pf-item">
                <dt>氏名</dt>
                <dd>
                    {{ $profile->fullName() }}
                    @if ($profile->furigana())
                    <br><small style="color:#b0a090;">{{ $profile->furigana() }}</small>
                    @endif
                </dd>
            </div>

            @if ($profile->phone)
            <div class="pf-item">
                <dt>電話番号</dt>
                <dd>{{ $profile->phone }}</dd>
            </div>
            @endif

            @if ($profile->postal_code || $profile->address)
            <div class="pf-item" style="grid-column: 1 / -1;">
                <dt>ご住所</dt>
                <dd>
                    @if ($profile->postal_code)〒{{ $profile->postal_code }}&nbsp;@endif
                    {{ $profile->address }}
                </dd>
            </div>
            @endif

            @if ($profile->isAttending())
            <div class="pf-item">
                <dt>ご出席人数</dt>
                <dd>合計 {{ $profile->attending_count }}名
                    @if ($profile->children_count > 0)
                    （うちお子様 {{ $profile->children_count }}名）
                    @endif
                </dd>
            </div>

            <div class="pf-item">
                <dt>食物アレルギー</dt>
                <dd>
                    @if ($profile->has_allergy)
                        <span style="color:#c0392b; font-weight:600;">あり</span>
                        @if ($profile->allergy_notes)
                        <br><small style="color:#666;">{{ $profile->allergy_notes }}</small>
                        @endif
                    @else
                        なし
                    @endif
                </dd>
            </div>
            @endif

            @if ($profile->notes)
            <div class="pf-item" style="grid-column: 1 / -1;">
                <dt>おふたりへのメッセージ</dt>
                <dd>{{ $profile->notes }}</dd>
            </div>
            @endif
        </dl>

        @endif {{-- profile exists and not pending --}}

        <div class="pf-divider" style="margin-top: 20px;"></div>
        <div class="pf-actions">
            @if ($profile && $profile->participation !== 'pending')
            <a href="{{ route('invitation') }}" class="pf-btn pf-btn--outline">
                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>回答を変更する
            </a>
            @else
            <a href="{{ route('invitation') }}" class="pf-btn pf-btn--primary">
                <i class="fa-solid fa-envelope-open-text" aria-hidden="true"></i>招待状に回答する
            </a>
            @endif
        </div>
    </div>

    {{-- ══ CARD 3: 式のご案内 ════════════════════════════ --}}
    @if ($setting)
    <div class="pf-card">
        <span class="pf-section-en">Wedding Info</span>
        <h2 class="pf-section-ja">式のご案内</h2>
        <div class="pf-section-rule"></div>

        <dl class="pf-wedding-grid">
            <div>
                <p class="pf-wedding-label">Date</p>
                <p class="pf-wedding-value">
                    {{ $setting->ceremonyDateJa() }}（{{ $setting->ceremonyDayOfWeek() }}）
                </p>
                <p class="pf-wedding-sub">
                    挙式 {{ $setting->ceremonyTimeFormatted() }} ／ 披露宴 {{ $setting->receptionTimeFormatted() }}〜
                </p>
            </div>
            <div>
                <p class="pf-wedding-label">Venue</p>
                <p class="pf-wedding-value">{{ $setting->venue_name }}</p>
                <p class="pf-wedding-sub">{{ $setting->venue_address }}</p>
                @if ($setting->venue_url)
                <a href="{{ $setting->venue_url }}" target="_blank" rel="noopener" class="pf-wedding-map">
                    Google Map →
                </a>
                @endif
            </div>
        </dl>
    </div>
    @endif

    @endif {{-- !isAdmin() --}}

</div>

@endsection
