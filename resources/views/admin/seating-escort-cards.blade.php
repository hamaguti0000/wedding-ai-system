@extends('layouts.app')
@section('title', 'エスコートカード | Admin')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Mrs+Saint+Delafield&family=Noto+Serif+JP:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ css_asset('css/seating-escort-cards.css') }}">
@endpush

@section('content')

@php
    $groomEn = $setting?->groom_name_en ?: $setting?->groom_name;
    $brideEn = $setting?->bride_name_en ?: $setting?->bride_name;
    $coupleEn = trim(($groomEn ?? '') . ' and ' . ($brideEn ?? ''), ' and ');
    $dateEn = $setting?->ceremony_date ? \Carbon\Carbon::parse($setting->ceremony_date)->format('M.j.Y') : '';

    $lenKey = function (string $name) {
        $len = mb_strlen($name, 'UTF-8');
        return $len >= 4 ? 'ge4' : (string) max($len, 1);
    };
@endphp

<div class="ec-page">
    <div class="ec-toolbar">
        <a href="{{ route('admin.seating') }}" class="ghost"><i class="fa-solid fa-chevron-left"></i> 編集画面に戻る</a>
        <button type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> 印刷する</button>
    </div>

    @if ($unassignedNames->isNotEmpty())
    <div class="ec-warning">
        <strong>席が未割り当てのため、カードを作成できないゲストが{{ $unassignedNames->count() }}名います：</strong>
        {{ $unassignedNames->implode('、') }}
        <br>先に<a href="{{ route('admin.seating') }}">席次表</a>で配置してください。
    </div>
    @endif

    <p class="ec-meta">
        {{ $cards->count() }}枚のエスコートカード（氏名順・1シート2枚 / A6サイズ）<br>
        印刷時は「背景のグラフィック」を有効にしてください（花柄が印刷されます）
    </p>

    <div class="ec-grid">
        @foreach ($cards as $card)
        <article class="ec-card">
            <div class="ec-card__corner-tr" aria-hidden="true"></div>
            <div class="ec-card__corner-bl" aria-hidden="true"></div>

            <div class="ec-card__head">
                @if ($coupleEn)<p class="ec-card__couple">{{ $coupleEn }}</p>@endif
                @if ($dateEn)<p class="ec-card__date">{{ $dateEn }}</p>@endif
            </div>

            <div class="ec-card__table-name" data-len="{{ $lenKey($card['table_name']) }}">{{ $card['table_name'] }}</div>

            <div class="ec-card__divider" aria-hidden="true"></div>

            <div class="ec-card__guest">
                <p class="ec-card__guest-family">{{ $card['family'] }}</p>
                <p class="ec-card__guest-given">{{ $card['given'] }}</p>
            </div>

            <div class="ec-card__footer">
                <p class="ec-card__footer-en">Welcome to our wedding!</p>
                <p class="ec-card__footer-jp">本日はごゆっくりお過ごしください</p>
            </div>
        </article>
        @endforeach
    </div>
</div>

@endsection
