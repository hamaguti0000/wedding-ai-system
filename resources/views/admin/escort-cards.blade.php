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
                <button type="submit">選択した人でプレビュー更新</button>
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

    @if ($guests->isEmpty())
    <section class="ec-empty">
        <h1>印刷対象者が選択されていません</h1>
        <p>上の一覧で印刷したいゲストにチェックを入れてください。</p>
    </section>
    @else
    <section class="ec-sheets" aria-label="エスコートカードプレビュー">
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
