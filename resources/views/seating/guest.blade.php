@extends('layouts.app')
@section('title', '席次表 | ' . ($setting?->groom_name ?? '') . ' & ' . ($setting?->bride_name ?? ''))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/seating-guest.css') }}">
@endpush

@section('content')

@php
    $guestName = function ($user) {
        $p = $user->guestProfile;
        return $p ? trim($p->last_name . ' ' . $p->first_name) : $user->name;
    };
    $guestInit = fn($user) => mb_substr($user->guestProfile?->last_name ?? $user->name, 0, 1, 'UTF-8');
@endphp

<script>window.SEAT_TYPE_CONFIG = @json($typeConfig);</script>

<div class="gs-page">

    {{-- ── ヘッダー ── --}}
    <header class="gs-header">
        <h1 class="gs-header__title">席 次 表</h1>
        <p class="gs-header__meta">
            @if ($setting?->ceremony_date)
                {{ \Carbon\Carbon::parse($setting->ceremony_date)->format('Y年n月j日') }}
                @if ($setting?->venue_name) &nbsp;·&nbsp; {{ $setting->venue_name }} @endif
            @elseif ($setting?->venue_name)
                {{ $setting->venue_name }}
            @endif
        </p>
    </header>

    @if (!$isPublished)

        {{-- ── 未公開 ── --}}
        <div class="gs-empty">
            <div class="gs-empty__icon"><i class="fa-regular fa-clock"></i></div>
            <h2 class="gs-empty__title">席次表は準備中です</h2>
            <p class="gs-empty__desc">席次が確定次第こちらでご確認いただけます。</p>
        </div>

    @else

        {{-- ── 自席バナー ── --}}
        @php
            $myTable = $myTableId ? $tables->firstWhere('id', $myTableId) : null;
        @endphp

        @if ($myTable)
        <div class="gs-banner gs-banner--found" id="gsBanner">
            <div class="gs-banner__left">
                <span class="gs-banner__icon"><i class="fa-solid fa-location-dot"></i></span>
                <div>
                    <p class="gs-banner__label">あなたの席</p>
                    <p class="gs-banner__value">{{ $myTable->name }}</p>
                </div>
            </div>
            <button class="gs-banner__btn" id="scrollToMyTable">確認する</button>
        </div>
        @else
        <div class="gs-banner gs-banner--pending">
            <span class="gs-banner__icon"><i class="fa-solid fa-circle-info"></i></span>
            <p class="gs-banner__text">席はまだ確定していません。確定次第ご案内します。</p>
        </div>
        @endif

        {{-- ── テーブルグリッド ── --}}
        <main class="gs-grid" id="gsGrid">
            @foreach ($tables as $table)
            @php
                $isMyTable  = $myTableId && $table->id === $myTableId;
                $totalSeats = $table->seats->count();
                $occupied   = $table->seats->filter(fn($s) => $s->assignment !== null)->count();
            @endphp
            <div class="gs-card {{ $isMyTable ? 'gs-card--mine' : '' }}"
                 id="gst-{{ $table->id }}"
                 style="animation-delay: {{ $loop->index * 40 }}ms">

                {{-- カードヘッダー --}}
                <div class="gs-card__header">
                    <span class="gs-card__name">{{ $table->name }}</span>
                    <span class="gs-card__badge">{{ $occupied }}<span class="gs-card__badge-sep">/</span>{{ $totalSeats }}</span>
                </div>

                {{-- 席グリッド --}}
                <div class="gs-card__seats">
                    @forelse ($table->seats as $seat)
                    @php
                        $assignedUser = $seat->assignment?->user;
                        $isOccupied   = $assignedUser !== null;
                        $isMe         = $mySeat && $seat->id === $mySeat->id;
                        $initial      = $isOccupied ? $guestInit($assignedUser) : '';
                        $fullName     = $isOccupied ? $guestName($assignedUser) : '';
                    @endphp
                    <div class="gs-seat {{ $isOccupied ? 'is-occupied' : '' }} {{ $isMe ? 'is-mine' : '' }}"
                         data-type="{{ $seat->type }}">
                        <div class="gs-seat__circle">
                            @if ($isMe)<span class="gs-seat__badge">YOU</span>@endif
                            {{ $initial }}
                        </div>
                        <p class="gs-seat__name">{{ $isOccupied ? $fullName : '　' }}</p>
                    </div>
                    @empty
                    <p class="gs-card__empty">席が登録されていません</p>
                    @endforelse
                </div>

            </div>
            @endforeach
        </main>

        {{-- ── 凡例 ── --}}
        <footer class="gs-legend">
            <div class="gs-legend__inner">
                @if ($myTable)
                <div class="gs-legend__mine">
                    <span class="gs-legend__mine-dot"></span>あなたの席
                </div>
                <span class="gs-legend__sep"></span>
                @endif
                <span class="gs-legend__label">席タイプ:</span>
                @foreach ($typeConfig as $key => $cfg)
                <div class="gs-legend__item">
                    <span class="gs-legend__dot" style="background:{{ $cfg['color'] }};"></span>
                    <span>{{ $cfg['label'] }}</span>
                </div>
                @endforeach
            </div>
        </footer>

    @endif

</div>

@endsection

@push('scripts')
<script>
(function () {
    const cfg      = window.SEAT_TYPE_CONFIG || {};
    const myTable  = document.querySelector('.gs-card--mine');

    // 席タイプ別カラーを CSS カスタムプロパティで適用
    document.querySelectorAll('.gs-seat[data-type]').forEach(function (el) {
        const c = cfg[el.dataset.type];
        if (c) {
            el.style.setProperty('--sc', c.color);
            el.style.setProperty('--sb', c.bg);
        }
    });

    // 「確認する」ボタン → 自席テーブルカードにスクロール
    document.getElementById('scrollToMyTable')?.addEventListener('click', function () {
        myTable?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    // ページロード時に自席カードへ自動スクロール
    if (myTable) {
        setTimeout(function () {
            myTable.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 500);
    }
})();
</script>
@endpush
