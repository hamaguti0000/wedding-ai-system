@extends('layouts.app')
@section('title', '席次表 | ' . ($setting?->groom_name ?? '') . ' & ' . ($setting?->bride_name ?? ''))

@push('styles')
<link rel="stylesheet" href="{{ css_asset('css/seating-guest.css') }}">
<style>
    /*
      挙式までのカウントダウン(position:fixedで右下に固定表示)が、席次表の
      ご芳名に重なって読めなくなっていた(2026-08-13、公開後に実機で発覚)。
      席次表は名前を一件ずつ読む画面で、重なりは誤読につながるため、この
      ページでは表示しない。他のページでは従来どおり表示される。
    */
    .countdown { display: none !important; }
</style>
@endpush

@section('content')

@php
    $guestName = function ($user) {
        $p = $user->guestProfile;
        return $p ? trim($p->last_name . ' ' . $p->first_name) : $user->name;
    };
    $coupleNames = trim(($setting?->groom_name ?? '') . ' & ' . ($setting?->bride_name ?? ''));
    $tableMarkForIndex = function (int $index) {
        if ($index < 26) {
            return chr(65 + $index);
        }

        $index -= 26;
        $letter = '';

        do {
            $letter = chr(97 + ($index % 26)) . $letter;
            $index = intdiv($index, 26) - 1;
        } while ($index >= 0);

        return $letter;
    };
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
                ['type' => 'eight', 'offset' => 0, 'tables' => $tables->slice(0, 8)->values()],
                ['type' => 'eight', 'offset' => 8, 'tables' => $tables->slice(8, 8)->values()],
                ['type' => 'seven', 'offset' => 16, 'tables' => $tables->slice(16, 7)->values()],
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

                <div class="gs-view-tools" aria-label="表示切替">
                    <button type="button" class="is-active" data-gs-view="fit">全体表示</button>
                    <button type="button" data-gs-view="read">拡大して読む</button>
                </div>

                {{--
                  卓の並びは会場の配置そのものなので組み替えられない。狭い画面で
                  読みづらい場合は、配置を保ったまま倍率だけを上げ下げしてもらう。
                --}}
                <div class="gs-zoom-tools" aria-label="表示倍率">
                    <button type="button" id="gsZoomOut" aria-label="縮小">−</button>
                    <span class="gs-zoom-tools__field">
                        <input type="number" id="gsZoomLevel" inputmode="numeric"
                               min="100" max="600" step="10" value="100" aria-label="表示倍率(%)">
                        <span aria-hidden="true">%</span>
                    </span>
                    <button type="button" id="gsZoomIn" aria-label="拡大">＋</button>
                </div>

                {{--
                  「拡大して読む」は実寸表示になる代わりに全体を横スクロールで見る仕様だが、
                  スクロールできること自体が画面上で示されておらず、最初に見えている
                  卓(一番左の列)だけが全部だと誤解される恐れがあった(2026-08-12、
                  スマホ実機相当のスクリーンショットで確認)。スクロールが必要な時だけ
                  JS側で.is-visibleを付けて表示するヒント。
                --}}
                <p class="gs-scroll-hint" id="gsScrollHint">→ 横にスクロールすると他の卓もご覧いただけます</p>

                <div class="gs-board-scroll" id="gsGrid">
                    <div class="gs-board-scale" id="gsBoardScale">
                        <div class="gs-board" id="gsBoard">
                            {{--
                              高砂は新郎新婦の席なので、「高砂」という役割名ではなく
                              お二人の名前を出す。設定が未入力の場合だけ従来の「高砂」に戻す。
                            --}}
                            @php
                                /*
                                  trim($s, ' ＆') のように全角記号を文字リストに渡すと、trimは
                                  バイト単位で削るため「礼」(E7 A4 BC)の末尾BCが＆(EF BC 86)の
                                  BCと一致して削られ、名前が文字化けする。連結ではなく
                                  空でない値だけを集めて結合する。
                                */
                                $stageNames = collect([$setting?->groom_name, $setting?->bride_name])
                                    ->filter(fn ($v) => filled($v))
                                    ->implode(' ＆ ');
                            @endphp
                            <div class="gs-stage gs-stage--head">
                                <span>{{ $stageNames !== '' ? $stageNames : '高砂' }}</span>
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
                                    'tableMark' => $tableMarkForIndex($printRow['offset'] + $loop->index),
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
                                        'tableMark' => $tableMarkForIndex(23 + (($loop->parent->index * 4) + $loop->index)),
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
                                        'tableMark' => $tableMarkForIndex(23 + (($loop->parent->index * 4) + 2 + $loop->index)),
                                    ])
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

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
    const scrollHint = document.getElementById('gsScrollHint');

    const zoomIn = document.getElementById('gsZoomIn');
    const zoomOut = document.getElementById('gsZoomOut');
    const zoomLevel = document.getElementById('gsZoomLevel');

    // 卓の並びは会場の配置そのものなので組み替えられない。狭い画面では倍率で対応する。
    // zoomFactor は「そのモードの基準倍率」に対する掛け率。
    let currentMode = 'fit';
    let zoomFactor = 1;
    const ZOOM_MIN = 1;
    const ZOOM_MAX = 6;
    const ZOOM_STEP = 1.4;

    function baseScale(mode) {
        return mode === 'read' ? 1 : Math.min(1, (scroller.clientWidth - 8) / board.offsetWidth);
    }

    function applyScale() {
        const scale = baseScale(currentMode) * zoomFactor;
        scaleShell.style.setProperty('--gs-board-scale', scale.toFixed(3));
        scaleShell.style.height = `${board.offsetHeight * scale}px`;
        scaleShell.classList.toggle('is-fit', currentMode !== 'read');
        // 拡大すると横幅が画面を超えるため、その時はスクロールできるようにする。
        const overflows = board.offsetWidth * scale > scroller.clientWidth + 1;
        scroller.classList.toggle('is-reading', overflows);
        scrollHint?.classList.toggle('is-visible', overflows);
        // 入力中の値を打ち消さないよう、フォーカス中は書き換えない。
        if (zoomLevel && document.activeElement !== zoomLevel) {
            zoomLevel.value = Math.round(zoomFactor * 100);
        }
        if (zoomOut) zoomOut.disabled = zoomFactor <= ZOOM_MIN + 0.001;
        if (zoomIn) zoomIn.disabled = zoomFactor >= ZOOM_MAX - 0.001;
    }

    function setBoardView(mode) {
        if (!scroller || !scaleShell || !board) return;
        currentMode = mode;
        zoomFactor = 1;
        applyScale();
        scroller.scrollTo({ left: 0, top: 0, behavior: 'smooth' });
        viewButtons.forEach((button) => {
            button.classList.toggle('is-active', button.dataset.gsView === mode);
        });
    }

    function changeZoom(factor) {
        if (!scroller || !scaleShell || !board) return;
        // 拡大前に見えていた中心を、拡大後も同じ位置に保つ。
        const prevScale = baseScale(currentMode) * zoomFactor;
        const centerX = (scroller.scrollLeft + scroller.clientWidth / 2) / prevScale;
        const centerY = (scroller.scrollTop + scroller.clientHeight / 2) / prevScale;

        zoomFactor = Math.min(ZOOM_MAX, Math.max(ZOOM_MIN, zoomFactor * factor));
        applyScale();

        const nextScale = baseScale(currentMode) * zoomFactor;
        scroller.scrollLeft = centerX * nextScale - scroller.clientWidth / 2;
        scroller.scrollTop = centerY * nextScale - scroller.clientHeight / 2;
    }

    zoomIn?.addEventListener('click', () => changeZoom(ZOOM_STEP));
    zoomOut?.addEventListener('click', () => changeZoom(1 / ZOOM_STEP));

    // 倍率の直接入力。空欄や範囲外は現在値へ戻す。
    function applyTypedZoom() {
        if (!zoomLevel) return;
        const typed = parseFloat(zoomLevel.value);
        if (!isFinite(typed)) {
            zoomLevel.value = Math.round(zoomFactor * 100);
            return;
        }
        const target = Math.min(ZOOM_MAX, Math.max(ZOOM_MIN, typed / 100));
        changeZoom(target / zoomFactor);
        zoomLevel.value = Math.round(zoomFactor * 100);
    }
    zoomLevel?.addEventListener('change', applyTypedZoom);
    zoomLevel?.addEventListener('blur', applyTypedZoom);
    zoomLevel?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); zoomLevel.blur(); }
    });

    viewButtons.forEach((button) => {
        button.addEventListener('click', () => setBoardView(button.dataset.gsView));
    });

    document.getElementById('scrollToMyTable')?.addEventListener('click', function () {
        setBoardView('read');
        setTimeout(() => myTable?.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' }), 80);
    });

    /*
      スマホでは画面を上下にスクロールするとアドレスバーが出入りして高さが変わり、
      その都度resizeが発火する。ここでsetBoardViewを呼ぶと倍率が100%に戻ってしまう
      ため（2026-08-13に指摘）、幅が実際に変わった時だけ基準倍率を計算し直し、
      ユーザーが設定した倍率(zoomFactor)は保持する。
    */
    let lastWidth = window.innerWidth;
    window.addEventListener('resize', () => {
        if (window.innerWidth === lastWidth) return;
        lastWidth = window.innerWidth;
        applyScale();
    });
    // 画像やフォントの読み込み後に卓の大きさが変わることがあるので測り直すが、
    // ここでも倍率はリセットしない。
    window.addEventListener('load', () => applyScale());
    setBoardView('fit');
})();
</script>
@endpush
