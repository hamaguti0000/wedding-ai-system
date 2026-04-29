@extends('layouts.app')
@section('title', '席次表 | ' . ($setting?->groom_name ?? '') . ' & ' . ($setting?->bride_name ?? ''))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/seating-guest.css') }}">
@endpush

@section('content')

@php
    $guestName = function ($user) {
        $p = $user->guestProfile;
        return $p ? $p->last_name . ' ' . $p->first_name : $user->name;
    };
    $guestInit = fn($user) => mb_substr($user->guestProfile?->last_name ?? $user->name, 0, 1, 'UTF-8');
@endphp

<script>window.SEAT_TYPE_CONFIG = @json($typeConfig);</script>
<script>window.MY_SEAT_ID = {{ $mySeat?->id ?? 'null' }};</script>

<div class="gs-page">

    {{-- ── ページヘッダー ── --}}
    <div class="gs-hero">
        <h1 class="gs-hero__title">
            <i class="fa-solid fa-chair" style="font-size:1rem;margin-right:6px;"></i>席次表
        </h1>
        <p class="gs-hero__sub">
            @if ($setting?->ceremony_date)
                {{ \Carbon\Carbon::parse($setting->ceremony_date)->format('Y年n月j日') }} ·
            @endif
            {{ $setting?->venue_name ?? '' }}
        </p>
    </div>

    @if (!$isPublished)

        {{-- ── 未公開状態 ── --}}
        <div class="gs-unpublished">
            <i class="fa-solid fa-hourglass-half gs-unpublished__icon"></i>
            <h2 class="gs-unpublished__title">席次表は準備中です</h2>
            <p class="gs-unpublished__desc">
                席次の確定後にこちらでご確認いただけます。<br>
                もうしばらくお待ちください。
            </p>
        </div>

    @else

        {{-- ── 自席バナー ── --}}
        @if ($mySeat)
        <div class="gs-my-banner">
            <i class="fa-solid fa-location-dot gs-my-banner__icon"></i>
            <p class="gs-my-banner__text">
                あなたの席は
                <strong>
                    {{ $tables->firstWhere('id', $myTableId)?->name ?? '—' }}
                </strong>
                です
                <span id="scrollBtn"
                      style="margin-left:8px;cursor:pointer;color:var(--ds-gold);text-decoration:underline;font-size:0.85em;">
                    席を確認する →
                </span>
            </p>
        </div>
        @else
        <div class="gs-my-banner" style="border-color:var(--ds-gold-lighter);background:var(--ds-cream);">
            <i class="fa-solid fa-circle-info gs-my-banner__icon" style="color:var(--ds-text-muted);"></i>
            <p class="gs-my-banner__text" style="color:var(--ds-text-mid);">
                席はまだ確定していません。確定次第ご案内します。
            </p>
        </div>
        @endif

        {{-- ── キャンバス ── --}}
        <div class="gs-canvas-wrap" id="gsCanvasWrap">
            <div class="gs-canvas" id="gsCanvas">

                @foreach ($tables as $i => $table)
                @php
                    $x = $table->pos_x ?: (24 + ($i % 3) * 280);
                    $y = $table->pos_y ?: (24 + (int)floor($i / 3) * 240);
                @endphp
                <div class="gs-table"
                     id="gst-{{ $table->id }}"
                     style="left:{{ $x }}px; top:{{ $y }}px;">

                    <div class="gs-table__name" title="{{ $table->name }}">
                        {{ $table->name }}
                    </div>

                    <div class="gs-table__body">
                        @foreach ($table->seats as $seat)
                        @php
                            $assignedUser = $seat->assignment?->user;
                            $occupied     = $assignedUser !== null;
                            $isMe         = $mySeat && $seat->id === $mySeat->id;
                            $initial      = $occupied ? $guestInit($assignedUser) : '';
                            $fullName     = $occupied ? $guestName($assignedUser) : '';
                        @endphp
                        <div class="gs-seat {{ $occupied ? 'is-occupied' : '' }} {{ $isMe ? 'gs-seat--mine' : '' }}"
                             id="gs-seat-{{ $seat->id }}"
                             data-seat-id="{{ $seat->id }}"
                             data-type="{{ $seat->type }}"
                             style="left:{{ $seat->pos_x }}px; top:{{ $seat->pos_y }}px;">

                            @if ($isMe)
                            <span class="gs-seat__badge">あなたの席</span>
                            @endif

                            <div class="gs-seat__circle">
                                {{ $initial }}
                            </div>

                            @if ($occupied)
                            <div class="gs-seat__tooltip">{{ $fullName }}</div>
                            @endif
                        </div>
                        @endforeach
                    </div>

                </div>
                @endforeach

            </div>{{-- /.gs-canvas --}}
        </div>{{-- /.gs-canvas-wrap --}}

        {{-- ── 凡例 ── --}}
        <footer class="gs-legend">
            <span class="gs-legend__label">席タイプ:</span>
            @foreach ($typeConfig as $key => $cfg)
            <span class="gs-legend__item">
                <span class="gs-legend__dot" style="background:{{ $cfg['color'] }};"></span>
                {{ $cfg['label'] }}
            </span>
            @endforeach
            @if ($mySeat)
            <span style="margin-left:auto;">
                <span class="gs-legend__mine">
                    <span class="gs-legend__mine-dot"></span>あなたの席
                </span>
            </span>
            @endif
        </footer>

    @endif

</div>{{-- /.gs-page --}}

@endsection

@push('scripts')
<script>
(function () {
    const cfg    = window.SEAT_TYPE_CONFIG || {};
    const mySeatId = window.MY_SEAT_ID;

    // 席タイプ別 CSS カスタムプロパティを適用
    document.querySelectorAll('.gs-seat[data-type]').forEach(function (el) {
        const type = el.dataset.type;
        const c    = cfg[type];
        if (c) {
            el.style.setProperty('--sc', c.color);
            el.style.setProperty('--sb', c.bg);
        }
    });

    // キャンバスの最小サイズを動的に計算
    const canvas = document.getElementById('gsCanvas');
    if (canvas) {
        let maxX = 0, maxY = 0;
        canvas.querySelectorAll('.gs-table').forEach(function (tbl) {
            const l = parseInt(tbl.style.left) || 0;
            const t = parseInt(tbl.style.top)  || 0;
            maxX = Math.max(maxX, l + 220);
            maxY = Math.max(maxY, t + 200);
        });
        canvas.style.minWidth  = (maxX + 40) + 'px';
        canvas.style.minHeight = (maxY + 40) + 'px';
    }

    // 自席へスクロール
    function scrollToMySeat() {
        if (!mySeatId) return;
        const el   = document.getElementById('gs-seat-' + mySeatId);
        const wrap = document.getElementById('gsCanvasWrap');
        if (!el || !wrap) return;
        const elRect   = el.getBoundingClientRect();
        const wrapRect = wrap.getBoundingClientRect();
        wrap.scrollTo({
            left: wrap.scrollLeft + elRect.left - wrapRect.left - (wrapRect.width  / 2) + 22,
            top:  wrap.scrollTop  + elRect.top  - wrapRect.top  - (wrapRect.height / 2) + 22,
            behavior: 'smooth',
        });
    }

    // ページロード時に自動スクロール
    window.addEventListener('load', function () {
        setTimeout(scrollToMySeat, 300);
    });

    // 「席を確認する」ボタン
    const btn = document.getElementById('scrollBtn');
    if (btn) btn.addEventListener('click', scrollToMySeat);
})();
</script>
@endpush
