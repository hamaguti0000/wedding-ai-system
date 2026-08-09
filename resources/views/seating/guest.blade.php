@extends('layouts.app')
@section('title', '席次表 | ' . ($setting?->groom_name ?? '') . ' & ' . ($setting?->bride_name ?? ''))

@push('styles')
<link rel="stylesheet" href="{{ css_asset('css/seating-guest.css') }}">
@endpush

@section('content')

@php
    $guestName = function ($user) {
        $p = $user->guestProfile;
        return $p ? trim($p->last_name . ' ' . $p->first_name) : $user->name;
    };
    $coupleNames = trim(($setting?->groom_name ?? '') . ' & ' . ($setting?->bride_name ?? ''));
@endphp

<div class="gs-page">

    @if (!$isPublished)

        <div class="gs-empty">
            <div class="gs-empty__panel">
                <div class="gs-empty__icon"><i class="fa-regular fa-clock"></i></div>
                <h2 class="gs-empty__title">席次表は準備中です</h2>
                <p class="gs-empty__desc">席次が確定次第、ここにテーブル名とお名前が表示されます。</p>
            </div>
        </div>

    @else

        @php
            $myTable = $myTableId ? $tables->firstWhere('id', $myTableId) : null;
            $printRows = [
                ['type' => 'eight', 'tables' => $tables->slice(0, 8)->values()],
                ['type' => 'eight', 'tables' => $tables->slice(8, 8)->values()],
                ['type' => 'seven', 'tables' => $tables->slice(16, 7)->values()],
            ];
            $edgeRows = $tables->slice(23)->values()->chunk(4);
        @endphp

        <section class="gs-paper-shell" aria-label="結婚式席次表">
            <div class="gs-paper">
                <span class="gs-corner gs-corner--tl" aria-hidden="true"></span>
                <span class="gs-corner gs-corner--tr" aria-hidden="true"></span>
                <span class="gs-corner gs-corner--bl" aria-hidden="true"></span>
                <span class="gs-corner gs-corner--br" aria-hidden="true"></span>

                <header class="gs-hero">
                    <div class="gs-hero__family">
                        <span>{{ $setting?->groom_name ? mb_substr($setting->groom_name, 0, 1) : 'K' }}</span>
                        <span>{{ $setting?->bride_name ? mb_substr($setting->bride_name, 0, 1) : 'M' }}</span>
                        <small>Wedding Reception Seating Chart</small>
                    </div>

                    <div class="gs-hero__center">
                        <p class="gs-hero__eyebrow">Seating Chart</p>
                        <h1 class="gs-hero__title">
                            <span>{{ $setting?->groom_name ?? '新郎' }}</span>
                            <span class="gs-hero__amp">&amp;</span>
                            <span>{{ $setting?->bride_name ?? '新婦' }}</span>
                        </h1>
                        @if ($coupleNames)
                        <p class="gs-hero__script">{{ $coupleNames }}</p>
                        @endif
                    </div>

                    <p class="gs-hero__meta">
                        @if ($setting?->ceremony_date)
                            {{ \Carbon\Carbon::parse($setting->ceremony_date)->format('Y.n.j') }}
                        @endif
                        @if ($setting?->venue_name)
                            <span>於 {{ $setting->venue_name }}</span>
                        @endif
                    </p>
                </header>

                @if ($myTable)
                <section class="gs-focus" id="gsBanner">
                    <div>
                        <p class="gs-focus__label">Your Seat</p>
                        <p class="gs-focus__value">{{ $myTable->name }}</p>
                    </div>
                    <button class="gs-focus__btn" id="scrollToMyTable" type="button">自分の席へ</button>
                </section>
                @else
                <section class="gs-focus gs-focus--pending">
                    <div>
                        <p class="gs-focus__label">Your Seat</p>
                        <p class="gs-focus__value">未配置</p>
                    </div>
                    <p class="gs-focus__note">席はまだ確定していません。</p>
                </section>
                @endif

                <div class="gs-view-tools" aria-label="表示切替">
                    <button type="button" class="is-active" data-gs-view="fit">全体表示</button>
                    <button type="button" data-gs-view="read">拡大して読む</button>
                </div>

                <div class="gs-board-scroll" id="gsGrid">
                    <div class="gs-board-scale" id="gsBoardScale">
                        <div class="gs-board" id="gsBoard">
                            <div class="gs-stage gs-stage--head">
                                <span>高砂</span>
                            </div>

                            @foreach ($printRows as $printRow)
                            @if ($printRow['tables']->isNotEmpty())
                            <div class="gs-table-row gs-table-row--{{ $printRow['type'] }}">
                                @foreach ($printRow['tables'] as $table)
                                @include('seating.partials.guest-table', [
                                    'table' => $table,
                                    'guestName' => $guestName,
                                    'myTableId' => $myTableId,
                                    'mySeat' => $mySeat,
                                ])
                                @endforeach
                            </div>
                            @endif
                            @endforeach

                            @foreach ($edgeRows as $rowTables)
                            <div class="gs-table-row gs-table-row--edge">
                                <div class="gs-edge-group">
                                    @foreach ($rowTables->slice(0, 2) as $table)
                                    @include('seating.partials.guest-table', [
                                        'table' => $table,
                                        'guestName' => $guestName,
                                        'myTableId' => $myTableId,
                                        'mySeat' => $mySeat,
                                    ])
                                    @endforeach
                                </div>
                                <div class="gs-edge-gap" aria-hidden="true"></div>
                                <div class="gs-edge-group">
                                    @foreach ($rowTables->slice(2, 2) as $table)
                                    @include('seating.partials.guest-table', [
                                        'table' => $table,
                                        'guestName' => $guestName,
                                        'myTableId' => $myTableId,
                                        'mySeat' => $mySeat,
                                    ])
                                    @endforeach
                                </div>
                            </div>
                            @endforeach

                            <div class="gs-stage gs-stage--entrance">
                                <span>受付・入口</span>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="gs-footnote">御席の不順、ご芳名に誤字がございましたら深くお詫び申し上げます</p>
            </div>
        </section>

    @endif

</div>

@endsection

@push('scripts')
<script>
(function () {
    const myTable = document.querySelector('.gs-table--mine');
    const scroller = document.getElementById('gsGrid');
    const scaleShell = document.getElementById('gsBoardScale');
    const board = document.getElementById('gsBoard');
    const viewButtons = document.querySelectorAll('[data-gs-view]');

    function setBoardView(mode) {
        if (!scroller || !scaleShell || !board) return;
        const fitScale = Math.min(1, (scroller.clientWidth - 8) / board.offsetWidth);
        const scale = mode === 'read' ? 1 : fitScale;
        scaleShell.style.setProperty('--gs-board-scale', scale.toFixed(3));
        scaleShell.style.height = `${board.offsetHeight * scale}px`;
        scaleShell.classList.toggle('is-fit', mode !== 'read');
        scroller.classList.toggle('is-reading', mode === 'read');
        if (mode !== 'read') scroller.scrollTo({ left: 0, top: 0, behavior: 'smooth' });
        viewButtons.forEach((button) => {
            button.classList.toggle('is-active', button.dataset.gsView === mode);
        });
    }

    viewButtons.forEach((button) => {
        button.addEventListener('click', () => setBoardView(button.dataset.gsView));
    });

    document.getElementById('scrollToMyTable')?.addEventListener('click', function () {
        setBoardView('read');
        setTimeout(() => myTable?.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' }), 80);
    });

    window.addEventListener('resize', () => setBoardView(document.querySelector('[data-gs-view].is-active')?.dataset.gsView || 'fit'));
    window.addEventListener('load', () => setBoardView('fit'));
    setBoardView('fit');
})();
</script>
@endpush
