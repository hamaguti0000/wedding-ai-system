@extends('layouts.app')
@section('title', '席次表（印刷用） | Admin')

@push('styles')
<link rel="stylesheet" href="{{ versioned_asset('css/seating-print.css') }}">
<style>
    .sxp-toolbar {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
        padding: 14px;
        background: #fff;
        border-bottom: 1px solid #e6dcc8;
        /* テーブル数が多いと縦に長くなり、印刷ボタンがスクロールで見えなくなって
           「印刷ボタンが無い」と誤解される事故があったため、常に見える位置に固定する。
           body { overflow-y: auto } がサイト全体に効いているため、
           position: sticky だと基準がbodyの内部スクロール(実際には発生しない)に
           なってしまい機能しない。position: fixed にする。 */
        position: fixed;
        top: 60px;
        left: 0;
        right: 0;
        z-index: 50;
    }
    @media (min-width: 768px) {
        body.has-admin-sidebar .sxp-toolbar {
            left: 220px;
        }
    }
    /* position: fixedで浮くようになった分、本文が隠れないよう余白を空ける */
    #sxpPage {
        padding-top: 140px;
    }
    .sxp-toolbar a, .sxp-toolbar button {
        padding: 8px 18px;
        border: 1px solid #b38b59;
        border-radius: 4px;
        background: #b38b59;
        color: #fff;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: background-color .15s ease, color .15s ease;
    }
    .sxp-toolbar a.ghost {
        background: transparent;
        color: #8f6a3f;
    }
    .sxp-toolbar a:hover, .sxp-toolbar button:hover {
        background: #966d3e;
    }
    .sxp-toolbar a.ghost:hover {
        background: rgba(179,139,89,.1);
    }
    @media print {
        .header, .header-drawer, .header-overlay, .sxp-toolbar,
        .admin-sidebar, footer { display: none !important; }
        main { padding-top: 0 !important; }
        #sxpPage { padding-top: 30px !important; }
    }
</style>
@endpush

@section('content')

@php
    $guestName = function ($user) {
        $p = $user->guestProfile;
        return $p ? trim($p->last_name . ' ' . $p->first_name) : $user->name;
    };
    $tableMark = function ($table) {
        $name = trim($table->name ?? '');
        return $name !== '' ? mb_substr($name, 0, 1) : 'T';
    };
@endphp

<div class="sxp-toolbar">
    <a href="{{ route('admin.seating') }}" class="ghost">&larr; 編集画面に戻る</a>
    <label class="sxp-toolbar__search">
        <span>検索</span>
        <input type="search" id="sxpSearch" placeholder="名前・卓名" autocomplete="off">
        <em id="sxpSearchCount"></em>
    </label>
    <label class="sxp-toolbar__toggle">
        <input type="checkbox" id="sxpHideEmpty">
        <span>空席を隠す</span>
    </label>
    <div class="sxp-toolbar__zoom" aria-label="表示倍率">
        <button type="button" data-sxp-zoom="0.45">全体</button>
        <button type="button" data-sxp-zoom="0.7">70%</button>
        <button type="button" data-sxp-zoom="0.85">85%</button>
        <button type="button" data-sxp-zoom="1">100%</button>
        <button type="button" data-sxp-zoom="1.2">120%</button>
    </div>
    <div class="sxp-toolbar__papersize" aria-label="用紙サイズ">
        <button type="button" data-sxp-page="a3">A3・1枚</button>
        <button type="button" data-sxp-page="a4">A4・2枚</button>
    </div>
    <button type="button" onclick="window.print()">印刷 / PDF保存</button>
    <span class="sxp-toolbar__hint">※印刷ダイアログの「送信先」で「PDFに保存」を選ぶとPDF保存できます</span>
</div>

<div class="gs-page" id="sxpPage">
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

    <div class="sxp-viewport">
        <div class="sxp-zoom-shell" id="sxpZoomShell">
            <section class="sxp-layout" aria-label="席とテーブルを統合した席次表">
        <div class="sxp-main-table">
            <span class="sxp-main-table__sub">Main Table</span>
            <span class="sxp-main-table__title">{{ trim(($setting?->groom_name ?? '') . '　' . ($setting?->bride_name ?? '')) }}</span>
        </div>

        @php
            $printRows = [
                ['type' => 'eight', 'tables' => $tables->slice(0, 8)->values()],
                ['type' => 'eight', 'tables' => $tables->slice(8, 8)->values()],
                ['type' => 'seven', 'tables' => $tables->slice(16, 7)->values()],
            ];
            $edgeRows = $tables->slice(23)->values()->chunk(4);
        @endphp

        <div class="sxp-board">
            @foreach ($printRows as $printRow)
            @php $rowTables = $printRow['tables']; @endphp
            @if ($rowTables->isNotEmpty())
            <div class="sxp-row sxp-row--{{ $printRow['type'] }}">
            @foreach ($rowTables as $table)
            @include('admin.partials.seating-print-table-card', [
                'table' => $table,
                'guestName' => $guestName,
                'tableMark' => $tableMark,
            ])
            @endforeach
            </div>
            @endif
            @endforeach

            @foreach ($edgeRows as $rowTables)
            <div class="sxp-row sxp-row--edge">
                <div class="sxp-edge-group">
                    @foreach ($rowTables->slice(0, 2) as $table)
                    @include('admin.partials.seating-print-table-card', [
                        'table' => $table,
                        'guestName' => $guestName,
                        'tableMark' => $tableMark,
                    ])
                    @endforeach
                </div>
                <div class="sxp-edge-gap" aria-hidden="true"></div>
                <div class="sxp-edge-group">
                    @foreach ($rowTables->slice(2, 2) as $table)
                    @include('admin.partials.seating-print-table-card', [
                        'table' => $table,
                        'guestName' => $guestName,
                        'tableMark' => $tableMark,
                    ])
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
            </section>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const page = document.getElementById('sxpPage');
    const shell = document.getElementById('sxpZoomShell');
    const buttons = document.querySelectorAll('[data-sxp-zoom]');
    const search = document.getElementById('sxpSearch');
    const count = document.getElementById('sxpSearchCount');
    const hideEmpty = document.getElementById('sxpHideEmpty');
    const cards = Array.from(document.querySelectorAll('.sxp-table-card'));
    if (!shell || buttons.length === 0) return;

    const fallback = window.matchMedia('(max-width: 767px)').matches ? '0.45' : '0.85';
    const saved = localStorage.getItem('seatingPrintZoom') || fallback;

    function normalize(value) {
        return String(value || '').trim().toLowerCase();
    }

    function setZoom(value) {
        shell.style.setProperty('--sxp-zoom', value);
        localStorage.setItem('seatingPrintZoom', value);
        buttons.forEach((button) => {
            button.classList.toggle('is-active', button.dataset.sxpZoom === value);
        });
    }

    function applySearch() {
        const q = normalize(search?.value);
        let matchedCards = 0;
        let matchedSeats = 0;

        cards.forEach((card) => {
            const tableName = normalize(card.querySelector('.sxp-table-card__name')?.textContent);
            let cardHasMatch = q && tableName.includes(q);

            card.querySelectorAll('.sxp-seat').forEach((seat) => {
                const name = normalize(seat.textContent);
                const hit = q && name.includes(q);
                seat.classList.toggle('is-search-hit', Boolean(hit));
                if (hit) {
                    cardHasMatch = true;
                    matchedSeats += 1;
                }
            });

            card.classList.toggle('is-search-muted', Boolean(q && !cardHasMatch));
            card.classList.toggle('is-search-card-hit', Boolean(q && cardHasMatch));
            if (cardHasMatch) matchedCards += 1;
        });

        if (count) {
            count.textContent = q ? `${matchedCards}卓 / ${matchedSeats}席` : '';
        }
    }

    buttons.forEach((button) => {
        button.addEventListener('click', () => setZoom(button.dataset.sxpZoom));
    });

    search?.addEventListener('input', applySearch);
    hideEmpty?.addEventListener('change', () => {
        page?.classList.toggle('sxp-hide-empty', hideEmpty.checked);
    });

    setZoom(saved);
    applySearch();
})();

(function () {
    const pageEl = document.getElementById('sxpPage');
    const zoomShell = document.getElementById('sxpZoomShell');
    const board = document.querySelector('.sxp-board');
    const pageSizeButtons = document.querySelectorAll('[data-sxp-page]');
    if (!pageEl || !zoomShell || !board || pageSizeButtons.length === 0) return;

    const MM_TO_PX = 96 / 25.4;
    const SPECS = {
        a3: { wMm: 420, hMm: 297, marginMm: 10 },
        a4: { wMm: 297, hMm: 210, marginMm: 10 },
    };
    const SAFETY_PX = 3 * MM_TO_PX;
    const PAGE2_TOP_BUFFER_PX = 5 * MM_TO_PX;

    const originalRows = Array.from(board.children);
    let currentPageSize = localStorage.getItem('seatingPrintPageSize') || 'a3';

    const pageStyleEl = document.createElement('style');
    pageStyleEl.id = 'sxpPageSizeStyle';
    document.head.appendChild(pageStyleEl);

    function usableAreaPx(spec) {
        return {
            w: (spec.wMm - spec.marginMm * 2) * MM_TO_PX,
            h: (spec.hMm - spec.marginMm * 2) * MM_TO_PX,
        };
    }

    function updatePageSizeButtons() {
        pageSizeButtons.forEach((button) => {
            button.classList.toggle('is-active', button.dataset.sxpPage === currentPageSize);
        });
    }

    function setPageSize(size) {
        currentPageSize = size;
        localStorage.setItem('seatingPrintPageSize', size);
        updatePageSizeButtons();
    }

    function resetBoardStructure() {
        zoomShell.style.removeProperty('--sxp-print-fit-scale');
        zoomShell.style.removeProperty('--sxp-print-fit-height');
        board.querySelectorAll(':scope > .sxp-print-half').forEach((wrap) => wrap.remove());
        originalRows.forEach((row) => board.appendChild(row));
    }

    function tableCountOf(row) {
        return row.querySelectorAll('.sxp-table-card').length;
    }

    function splitRowsByCount(rows) {
        const total = rows.reduce((sum, row) => sum + tableCountOf(row), 0);
        const half = total / 2;
        let running = 0;
        let cut = rows.length;
        for (let i = 0; i < rows.length; i += 1) {
            running += tableCountOf(rows[i]);
            if (running >= half) {
                cut = i + 1;
                break;
            }
        }
        return [rows.slice(0, cut), rows.slice(cut)];
    }

    function fitElement(el, availWpx, availHpx) {
        el.style.setProperty('--sxp-print-fit-scale', '1');
        el.style.setProperty('--sxp-print-fit-height', 'auto');
        const naturalW = el.scrollWidth;
        const naturalH = el.scrollHeight;
        const scale = Math.min(1, availHpx / naturalH, availWpx / naturalW);
        el.style.setProperty('--sxp-print-fit-scale', String(scale));
        el.style.setProperty('--sxp-print-fit-height', (naturalH * scale) + 'px');
        return scale;
    }

    function applyPrintFit() {
        resetBoardStructure();
        const spec = SPECS[currentPageSize] || SPECS.a3;
        pageStyleEl.textContent = `@media print { @page { size: ${spec.wMm}mm ${spec.hMm}mm; margin: ${spec.marginMm}mm; } }`;

        const usable = usableAreaPx(spec);

        // ブラウザの実際のウィンドウ幅と印刷用紙の幅は一致しないため、
        // 印刷時に近い横幅を#sxpPageへ強制指定してから高さを測る。
        // (measureせずに測ると、実際の印刷幅と異なる幅で計算した誤った縮小率になる)
        const prevWidth = pageEl.style.width;
        pageEl.style.width = usable.w + 'px';
        void pageEl.offsetHeight;

        if (currentPageSize === 'a4') {
            const [rowsA, rowsB] = splitRowsByCount(originalRows);
            const wrapA = document.createElement('div');
            wrapA.className = 'sxp-print-half';
            rowsA.forEach((row) => wrapA.appendChild(row));
            const wrapB = document.createElement('div');
            wrapB.className = 'sxp-print-half sxp-print-half--break';
            rowsB.forEach((row) => wrapB.appendChild(row));
            board.appendChild(wrapA);
            board.appendChild(wrapB);

            const consumedTop = board.getBoundingClientRect().top - pageEl.getBoundingClientRect().top;
            fitElement(wrapA, usable.w, Math.max(usable.h - consumedTop - SAFETY_PX, usable.h * 0.3));
            fitElement(wrapB, usable.w, Math.max(usable.h - PAGE2_TOP_BUFFER_PX - SAFETY_PX, usable.h * 0.3));
        } else {
            const consumedTop = zoomShell.getBoundingClientRect().top - pageEl.getBoundingClientRect().top;
            fitElement(zoomShell, usable.w, Math.max(usable.h - consumedTop - SAFETY_PX, usable.h * 0.3));
        }

        pageEl.style.width = prevWidth;
    }

    function resetAfterPrint() {
        resetBoardStructure();
    }

    pageSizeButtons.forEach((button) => {
        button.addEventListener('click', () => setPageSize(button.dataset.sxpPage));
    });

    window.addEventListener('beforeprint', applyPrintFit);
    window.addEventListener('afterprint', resetAfterPrint);

    // テスト自動化用フック(Playwrightのpage.pdf()はbeforeprint/afterprintを発火しないため)
    window.__sxpTestSetPageSize = setPageSize;
    window.__sxpTestApplyPrintFit = applyPrintFit;

    updatePageSizeButtons();
})();
</script>
@endpush
