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
    $tableCount = $tables->count();
    $seatCount = $tables->sum(fn($table) => $table->seats->count());
    $assignedCount = $tables->sum(fn($table) => $table->seats->filter(fn($seat) => $seat->assignment !== null)->count());
    $tableSlots = collect(range(1, 32))->map(function ($slot) use ($tables) {
        return [
            'slot' => $slot,
            'table' => $tables->firstWhere('display_order', $slot),
        ];
    });
@endphp

<script>window.SEAT_TYPE_CONFIG = @json($typeConfig);</script>

<div class="gs-page">

    @if (!$isPublished)

        {{-- ── 未公開 ── --}}
        <div class="gs-empty">
            <div class="gs-empty__panel">
                <div class="gs-empty__icon"><i class="fa-regular fa-clock"></i></div>
                <h2 class="gs-empty__title">席次表は準備中です</h2>
                <p class="gs-empty__desc">席次が確定次第、ここにテーブル名とお名前が表示されます。</p>
            </div>
        </div>

    @else

        {{-- ── 自席バナー ── --}}
        @php
            $myTable = $myTableId ? $tables->firstWhere('id', $myTableId) : null;
        @endphp

        <header class="gs-hero">
            <div class="gs-hero__copy">
                <p class="gs-hero__eyebrow">Seating Chart</p>
                <h1 class="gs-hero__title">席次表</h1>
                <p class="gs-hero__meta">
                    @if ($setting?->ceremony_date)
                        {{ \Carbon\Carbon::parse($setting->ceremony_date)->format('Y年n月j日') }}
                        @if ($setting?->venue_name) ・{{ $setting->venue_name }}@endif
                    @elseif ($setting?->venue_name)
                        {{ $setting->venue_name }}
                    @endif
                </p>
            </div>
        </header>

        @if ($myTable)
        <section class="gs-focus" id="gsBanner">
            <div class="gs-focus__inner">
                <div class="gs-focus__badge"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></div>
                <div class="gs-focus__copy">
                    <p class="gs-focus__label">あなたの席</p>
                    <p class="gs-focus__value">{{ $myTable->name }}</p>
                </div>
            </div>
            <button class="gs-focus__btn" id="scrollToMyTable" type="button">席を見る</button>
        </section>
        @else
        <section class="gs-focus gs-focus--pending">
            <div class="gs-focus__inner">
                <div class="gs-focus__badge"><i class="fa-solid fa-circle-info" aria-hidden="true"></i></div>
                <div class="gs-focus__copy">
                    <p class="gs-focus__label">あなたの席</p>
                    <p class="gs-focus__value">未配置</p>
                </div>
            </div>
            <p class="gs-focus__note">席はまだ確定していません。確定次第ご案内します。</p>
        </section>
        @endif

        {{-- ── テーブルグリッド ── --}}
        <section class="gs-sheet">
            <main class="gs-grid" id="gsGrid">
                @foreach ($tableSlots as $slotInfo)
                @php
                    $table = $slotInfo['table'];
                    $isEmpty = $table === null;
                    $isMyTable  = $table && $myTableId && $table->id === $myTableId;
                    $totalSeats = $table?->seats->count() ?? 0;
                    $occupied   = $table ? $table->seats->filter(fn($s) => $s->assignment !== null)->count() : 0;
                @endphp
                <article class="gs-table {{ $isMyTable ? 'gs-table--mine' : '' }} {{ $isEmpty ? 'gs-table--empty' : '' }}"
                     id="{{ $table ? 'gst-' . $table->id : 'gst-empty-' . $slotInfo['slot'] }}"
                     style="animation-delay: {{ ($slotInfo['slot'] - 1) * 24 }}ms">
                    @if ($table)
                    <div class="gs-table__head">
                        <h2 class="gs-table__name">{{ $table->name }}</h2>
                        <div class="gs-table__count">{{ $occupied }} / {{ $totalSeats }}</div>
                    </div>

                    <div class="gs-table__surface">
                        <div class="gs-table__seats">
                            @forelse ($table->seats as $seat)
                            @php
                                $assignedUser = $seat->assignment?->user;
                                $isOccupied   = $assignedUser !== null;
                                $isMe         = $mySeat && $seat->id === $mySeat->id;
                                $fullName     = $isOccupied ? $guestName($assignedUser) : '';
                                $seatClass    = $isMe ? 'is-mine' : ($isOccupied ? 'is-occupied' : 'is-empty');
                            @endphp
                            <div class="gs-seat {{ $seatClass }}"
                                 data-type="{{ $seat->type }}">
                                <p class="gs-seat__name">{{ $isOccupied ? $fullName : '' }}</p>
                            </div>
                            @empty
                            <div class="gs-table__empty">席が登録されていません</div>
                            @endforelse
                        </div>
                    </div>
                    @else
                    <div class="gs-table__ghost">
                        <span class="gs-table__ghost-slot">-</span>
                    </div>
                    @endif

                </article>
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
        </section>

    @endif

</div>

@endsection

@push('scripts')
<script>
(function () {
    const cfg      = window.SEAT_TYPE_CONFIG || {};
    const myTable  = document.querySelector('.gs-table--mine');

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
