@extends('layouts.app')
@section('title', '席次表（印刷用） | Admin')

@push('styles')
<link rel="stylesheet" href="{{ versioned_asset('css/seating-print.css') }}">
<style>
    .sxp-toolbar {
        display: flex;
        justify-content: center;
        gap: 10px;
        padding: 14px;
        background: #fff;
        border-bottom: 1px solid #e6dcc8;
    }
    .sxp-toolbar a, .sxp-toolbar button {
        padding: 8px 18px;
        border: 1px solid #b38b59;
        background: #b38b59;
        color: #fff;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
    }
    .sxp-toolbar a.ghost {
        background: transparent;
        color: #8f6a3f;
    }
    @media print {
        .header, .header-drawer, .header-overlay, .sxp-toolbar { display: none !important; }
        main { padding-top: 0 !important; }
        @page { size: A3 landscape; margin: 14mm; }
    }
</style>
@endpush

@section('content')

@php
    $guestName = function ($user) {
        $p = $user->guestProfile;
        return $p ? trim($p->last_name . ' ' . $p->first_name) : $user->name;
    };
@endphp

<div class="sxp-toolbar">
    <a href="{{ route('admin.seating') }}" class="ghost">&larr; 編集画面に戻る</a>
    <button type="button" onclick="window.print()">印刷する</button>
</div>

<div class="gs-page">
    <header class="gs-hero">
        <div class="gs-hero__sparkle" aria-hidden="true"></div>
        <p class="gs-hero__eyebrow">Seating Chart</p>
        <div class="gs-hero__frame">
            <div class="gs-hero__frame-inner">
                @if ($setting?->groom_name || $setting?->bride_name)
                <p class="gs-hero__role">新郎　　　　新婦</p>
                @endif
                <h1 class="gs-hero__title">
                    <span>{{ $setting?->groom_name ?? '　' }}</span>
                    <span class="gs-hero__amp">&amp;</span>
                    <span>{{ $setting?->bride_name ?? '　' }}</span>
                </h1>
            </div>
        </div>
        <p class="gs-hero__meta">
            @if ($setting?->ceremony_date)
                {{ \Carbon\Carbon::parse($setting->ceremony_date)->format('Y年n月j日') }}
                @if ($setting?->venue_name) ・{{ $setting->venue_name }}@endif
            @elseif ($setting?->venue_name)
                {{ $setting->venue_name }}
            @endif
        </p>
    </header>

    <section class="sxp-layout" aria-label="席とテーブルを統合した席次表">
        <div class="sxp-main-table">
            <span class="sxp-main-table__sub">Main Table</span>
            <span class="sxp-main-table__title">高砂</span>
        </div>

        <div class="sxp-grid">
            @foreach ($tables as $table)
            @php
                $seats = $table->seats->values();
                $occupiedSeats = $seats->filter(fn($s) => $s->assignment !== null)->values();
                $seatTotal = max($seats->count(), $occupiedSeats->count());
                $leftSeats = $seats->slice(0, (int) ceil(max($seats->count(), 1) / 2))->values();
                $rightSeats = $seats->slice($leftSeats->count())->values();
            @endphp
            <article class="sxp-table-card">
                <header class="sxp-table-card__head">
                    <span class="sxp-table-card__name">{{ $table->name }}</span>
                    <span class="sxp-table-card__count">{{ $occupiedSeats->count() }} / {{ $seatTotal }}名</span>
                </header>

                <div class="sxp-seat-map">
                    <div class="sxp-seat-rail sxp-seat-rail--left">
                        @forelse ($leftSeats as $seat)
                        @php $assignedUser = $seat->assignment?->user; @endphp
                        <div class="sxp-seat {{ $assignedUser ? '' : 'is-empty' }}">
                            <span class="sxp-seat__name">{{ $assignedUser ? $guestName($assignedUser) : '空席' }}</span>
                        </div>
                        @empty
                        <div class="sxp-seat is-empty">
                            <span class="sxp-seat__name">席未設定</span>
                        </div>
                        @endforelse
                    </div>

                    <div class="sxp-table-core">
                        <span class="sxp-table-core__label">TABLE</span>
                        <span class="sxp-table-core__name">{{ $table->name }}</span>
                    </div>

                    <div class="sxp-seat-rail sxp-seat-rail--right">
                        @foreach ($rightSeats as $seat)
                        @php $assignedUser = $seat->assignment?->user; @endphp
                        <div class="sxp-seat {{ $assignedUser ? '' : 'is-empty' }}">
                            <span class="sxp-seat__name">{{ $assignedUser ? $guestName($assignedUser) : '空席' }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </article>
            @endforeach
        </div>

        <div class="sxp-entrance">
            <span>受付・入口</span>
        </div>
    </section>
</div>

@endsection
