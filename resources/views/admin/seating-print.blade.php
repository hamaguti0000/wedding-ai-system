@extends('layouts.app')
@section('title', '席次表（印刷用） | Admin')

@push('styles')
<link rel="stylesheet" href="{{ css_asset('css/seating-print.css') }}">
@endpush

@section('content')

@php
    $guestName = function ($user) {
        $p = $user->guestProfile;
        return $p ? trim($p->last_name . ' ' . $p->first_name) : $user->name;
    };

    $tableGroups = $tables->map(function ($table) {
        return [
            'table' => $table,
            'seats' => $table->seats->filter(fn($s) => $s->assignment !== null)->values(),
        ];
    })->filter(fn($g) => $g['seats']->isNotEmpty())->values();

    $totalGuests = $tableGroups->sum(fn($g) => $g['seats']->count());
    $totalTables = $tableGroups->count();
@endphp

<div class="spc-toolbar">
    <a href="{{ route('admin.seating') }}" class="ghost">&larr; 編集画面に戻る</a>
    <button type="button" onclick="window.print()">印刷する（A3）</button>
</div>

<div class="spc-page">
    <div class="spc-sheet">
        <header class="spc-header">
            <p class="spc-header__eyebrow">Seating Chart</p>
            <h1 class="spc-header__title">
                <span>{{ $setting?->groom_name ?? '　' }}</span>
                <span class="spc-header__amp">&amp;</span>
                <span>{{ $setting?->bride_name ?? '　' }}</span>
            </h1>
            <p class="spc-header__meta">
                @if ($setting?->ceremony_date)
                    {{ \Carbon\Carbon::parse($setting->ceremony_date)->format('Y年n月j日') }}
                    @if ($setting?->venue_name) ・{{ $setting->venue_name }}@endif
                @elseif ($setting?->venue_name)
                    {{ $setting->venue_name }}
                @endif
            </p>
            @if ($totalTables > 0)
            <p class="spc-header__summary">
                <strong>{{ $totalTables }}</strong>卓 ／ 合計 <strong>{{ $totalGuests }}</strong>名
            </p>
            @endif
        </header>

        @if ($tables->isNotEmpty())
        <div class="spc-floorplan">
            <p class="spc-floorplan__label">Floor Plan</p>
            @include('partials.seating-floor-plan', ['tables' => $tables])
        </div>
        @endif

        @if ($tableGroups->isEmpty())
        <p class="spc-empty">配置済みのゲストがいません。編集画面で席の配置を行ってください。</p>
        @else
        <main class="spc-grid">
            @foreach ($tableGroups as $group)
            <article class="spc-card">
                <div class="spc-card__head">
                    <span class="spc-card__name">{{ $group['table']->name }}</span>
                    <span class="spc-card__count">{{ $group['seats']->count() }}名</span>
                </div>
                <div class="spc-card__seats">
                    @foreach ($group['seats'] as $seat)
                    <div class="spc-seat">{{ $guestName($seat->assignment->user) }}</div>
                    @endforeach
                </div>
            </article>
            @endforeach
        </main>
        @endif

        <p class="spc-footnote">Thank you for celebrating with us</p>
    </div>
</div>

@endsection
