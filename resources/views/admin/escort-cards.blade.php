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
    </div>

    <section class="ec-selector" aria-label="印刷対象者選択">
        <div class="ec-selector__head">
            <div>
                <p class="ec-selector__eyebrow">Print Target</p>
                <h1>印刷するゲストを選択</h1>
                <p>出席者・欠席者・未回答者をすべて表示しています。チェックしたゲストだけ下のプレビューに出ます。</p>
            </div>
            <div class="ec-selector__actions">
                <button type="button" class="ec-secondary" id="ecSelectAll">全員チェック</button>
                <button type="button" class="ec-secondary" id="ecSelectDefault">席あり出席者</button>
                <button type="button" class="ec-secondary" id="ecClearAll">全解除</button>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.seating.escort-cards') }}" id="escortTargetForm">
            <input type="hidden" name="selection_submitted" value="1">
            <div class="ec-selector__summary">
                <span><strong id="ecSelectedCount">{{ $selectedIds->count() }}</strong>名を印刷対象に選択中</span>
                <button type="submit">選択した人でプレビューを作成</button>
            </div>

            <div class="ec-guest-list">
                @foreach ($allGuests as $candidate)
                @php
                    $profile = $candidate->guestProfile;
                    $candidateName = $profile ? trim(($profile->last_name ?? '') . ' ' . ($profile->first_name ?? '')) : $candidate->name;
                    $candidateKana = $profile?->furigana() ?? '';
                    $participation = $profile?->participation ?? 'pending';
                    $participationLabel = $profile?->participationLabel() ?? '未回答';
                    $table = $candidate->seatAssignment?->seat?->seatingTable;
                    $tableMark = $table ? ($tableMarks[$table->id] ?? '') : '';
                    $isDefaultTarget = $participation === 'attending' && $table;
                @endphp
                <label class="ec-guest-option ec-guest-option--{{ $participation }}">
                    <input type="checkbox"
                           name="print_user_ids[]"
                           value="{{ $candidate->id }}"
                           class="ec-target-check"
                           data-default="{{ $isDefaultTarget ? '1' : '0' }}"
                           {{ $selectedIds->contains($candidate->id) ? 'checked' : '' }}>
                    <span class="ec-guest-option__body">
                        <span class="ec-guest-option__name">{{ $candidateName ?: $candidate->username }}</span>
                        @if ($candidateKana)
                        <span class="ec-guest-option__kana">{{ $candidateKana }}</span>
                        @endif
                    </span>
                    <span class="ec-guest-option__meta">
                        <span class="ec-status ec-status--{{ $participation }}">{{ $participationLabel }}</span>
                        <span class="ec-table-chip">{{ $tableMark ? 'TABLE ' . $tableMark : '席未配置' }}</span>
                    </span>
                </label>
                @endforeach
            </div>
        </form>
    </section>

    <section class="ec-print-note">
        <strong>印刷前の確認</strong>
        <span>A4、倍率100%、余白なし、背景グラフィックONで印刷してください。最初は普通紙で1枚テストして、名刺用紙に重ねてズレを確認してください。</span>
    </section>

    @if ($guests->isNotEmpty())
    <section class="ec-preview-action" aria-label="印刷プレビュー">
        <div>
            <p class="ec-selector__eyebrow">Preview First</p>
            <h2>まず下のプレビューを確認してください</h2>
            <p>{{ $guests->count() }}枚をA4名刺10面に配置しています。問題なければPDF印刷プレビューを開いて、用紙がA4・倍率100%になっているか確認してください。</p>
        </div>
        <button type="submit" form="escortTargetForm" formmethod="GET" formaction="{{ route('admin.seating.escort-cards.pdf') }}" formtarget="_blank">PDF印刷プレビューを開く</button>
    </section>
    @endif

    @if ($guests->isEmpty())
    <section class="ec-empty">
        <h1>印刷対象者が選択されていません</h1>
        <p>上の一覧で印刷したいゲストにチェックを入れてください。</p>
    </section>
    @else
    <section class="ec-sheets" aria-label="エスコートカードプレビュー">
        @foreach ($guests->chunk(10) as $sheetIndex => $sheetGuests)
        <svg class="ec-print-sheet" viewBox="0 0 210 297" width="210mm" height="297mm" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="エスコートカード {{ $sheetIndex + 1 }}ページ目">
            <defs>
                <clipPath id="ec-card-clip-{{ $sheetIndex }}">
                    <rect x="0" y="0" width="91" height="55"></rect>
                </clipPath>
            </defs>
            <rect x="0" y="0" width="210" height="297" fill="#fff"></rect>
            @foreach ($sheetGuests as $guestIndex => $guest)
            @php
                $table = $guest->seatAssignment?->seat?->seatingTable;
                $tableMark = $table ? ($tableMarks[$table->id] ?? '') : '';
                $furigana = $guestFurigana($guest);
                $name = $guestName($guest);
                $x = ($guestIndex % 2) === 0 ? 14 : 105;
                $y = 11 + intdiv($guestIndex, 2) * 55;
            @endphp
            <g transform="translate({{ $x }} {{ $y }})" clip-path="url(#ec-card-clip-{{ $sheetIndex }})">
                <rect x="0" y="0" width="91" height="55" fill="#fff"></rect>
                <g transform="translate(45.5 27.5) rotate(90) translate(-27.5 -45.5)">
                    <image href="{{ asset('images/escort-template.png') }}" x="0" y="0" width="55" height="91" preserveAspectRatio="none"></image>
                    <rect x="4.7" y="4.6" width="25" height="11" fill="#f7f4ed" opacity="0.98"></rect>
                    <text x="5.2" y="8.1" fill="#a98348" font-family="Segoe Script, Brush Script MT, cursive" font-size="3.1">{{ $couple }}</text>
                    @if ($setting?->ceremony_date)
                    <text x="5.2" y="13.0" fill="#a98348" font-family="Georgia, serif" font-size="2.65" letter-spacing="0.08">{{ \Carbon\Carbon::parse($setting->ceremony_date)->format('M.j.Y') }}</text>
                    @endif
                    <text x="44.7" y="38.2" fill="#253a5c" font-family="Georgia, serif" font-size="17.2" text-anchor="middle">{{ $tableMark }}</text>
                    @if ($furigana)
                    <text x="6" y="48.0" fill="#837767" font-family="Noto Sans JP, Yu Gothic, sans-serif" font-size="1.9" letter-spacing="0.18">{{ $furigana }}</text>
                    @endif
                    <text x="6" y="{{ $furigana ? '56.2' : '53.4' }}" fill="#253a5c" font-family="Noto Serif JP, Yu Mincho, serif" font-size="5.9" letter-spacing="0.05">{{ $name }}</text>
                </g>
            </g>
            @endforeach
        </svg>
        @endforeach
    </section>
    @endif
</div>

<script>
(() => {
    const checks = Array.from(document.querySelectorAll('.ec-target-check'));
    const count = document.getElementById('ecSelectedCount');
    const update = () => {
        if (count) count.textContent = checks.filter((check) => check.checked).length;
    };

    document.getElementById('ecSelectAll')?.addEventListener('click', () => {
        checks.forEach((check) => check.checked = true);
        update();
    });
    document.getElementById('ecClearAll')?.addEventListener('click', () => {
        checks.forEach((check) => check.checked = false);
        update();
    });
    document.getElementById('ecSelectDefault')?.addEventListener('click', () => {
        checks.forEach((check) => check.checked = check.dataset.default === '1');
        update();
    });
    checks.forEach((check) => check.addEventListener('change', update));
    update();
})();
</script>
@endsection
