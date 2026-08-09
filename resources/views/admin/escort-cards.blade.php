@extends('layouts.app')
@section('title', 'エスコートカード | Admin')

@push('styles')
<link rel="stylesheet" href="{{ versioned_asset('css/escort-cards.css') }}">
@endpush

@section('content')
@php
    $guestName = function ($user) {
        $p = $user->guestProfile;
        return $p ? trim($p->last_name . ' ' . $p->first_name) : $user->name;
    };
    $guestFurigana = function ($user) {
        $p = $user->guestProfile;
        return $p ? trim(($p->furigana_sei ?? '') . ' ' . ($p->furigana_mei ?? '')) : '';
    };
    $couple = trim(($setting?->groom_name ?? 'Kakeru') . ' and ' . ($setting?->bride_name ?? 'Mirai'));
@endphp

<div class="ec-page">
    <div class="ec-toolbar">
        <a href="{{ route('admin.seating') }}">&larr; 席次表に戻る</a>
        <span>{{ $guests->count() }}枚 / A4名刺10面・縦デザイン</span>
        <button type="button" onclick="window.print()">印刷する</button>
    </div>

    @if ($guests->isEmpty())
    <section class="ec-empty">
        <h1>エスコートカードを作成できるゲストがいません</h1>
        <p>出席で、席が配置済みのゲストが対象です。</p>
    </section>
    @else
    <section class="ec-sheets" aria-label="エスコートカード一覧">
        @foreach ($guests->chunk(10) as $sheetGuests)
        <div class="ec-print-sheet">
            @foreach ($sheetGuests as $guest)
            @php
                $table = $guest->seatAssignment?->seat?->seatingTable;
                $tableMark = $table ? ($tableMarks[$table->id] ?? '') : '';
                $furigana = $guestFurigana($guest);
            @endphp
            <article class="ec-card">
                <div class="ec-card__inner">
                    <header class="ec-card__header">
                        <p class="ec-card__couple">{{ $couple }}</p>
                        @if ($setting?->ceremony_date)
                        <p class="ec-card__date">{{ \Carbon\Carbon::parse($setting->ceremony_date)->format('M.j.Y') }}</p>
                        @endif
                    </header>

                    <div class="ec-table" aria-label="テーブル {{ $tableMark }}">
                        <span class="ec-table__mark">{{ $tableMark }}</span>
                    </div>

                    <div class="ec-guest">
                        @if ($furigana)
                        <p class="ec-guest__kana">{{ $furigana }}</p>
                        @endif
                        <h2 class="ec-guest__name">{{ $guestName($guest) }}</h2>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
        @endforeach
    </section>
    @endif
</div>
@endsection
