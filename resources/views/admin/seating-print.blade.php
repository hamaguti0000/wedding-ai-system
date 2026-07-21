@extends('layouts.app')
@section('title', '席次表（印刷用） | Admin')

@push('styles')
<link rel="stylesheet" href="{{ css_asset('css/seating-guest.css') }}">
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

    @include('partials.seating-floor-plan', ['tables' => $tables])

    <section class="gs-sheet">
        <main class="gs-columns">
            @foreach ($tables as $table)
            @php $occupiedSeats = $table->seats->filter(fn($s) => $s->assignment !== null)->values(); @endphp
            @if ($occupiedSeats->isNotEmpty())
            <article class="gs-col">
                <div class="gs-col__badge">
                    <span class="gs-col__badge-moon">&#9789;</span>
                    <span class="gs-col__badge-name">{{ $table->name }}</span>
                </div>
                <div class="gs-col__list">
                    @foreach ($occupiedSeats as $seat)
                    <div class="gs-guest">
                        <p class="gs-guest__name">{{ $guestName($seat->assignment->user) }}</p>
                    </div>
                    @endforeach
                </div>
            </article>
            @endif
            @endforeach
        </main>
    </section>
</div>

@endsection
