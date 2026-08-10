@extends('layouts.app')
@section('title', '席次表管理 | Admin')

@push('styles')
<link rel="stylesheet" href="{{ css_asset('css/seating-table.css') }}">
@endpush

@section('content')

@php
    $pct = $summary['total'] > 0 ? round($summary['assigned'] / $summary['total'] * 100) : 0;

    $guestSide = fn($p) => match($p?->guest_side) { 'groom' => '新郎側', 'bride' => '新婦側', default => '' };
    $guestRel  = fn($p) => match($p?->relationship) {
        'friend' => '友人', 'family' => '親族', 'colleague' => '職場', 'other' => 'その他', default => ''
    };
    $guestName = function ($user) {
        $p = $user->guestProfile;
        return $p ? trim($p->last_name . ' ' . $p->first_name) : $user->name;
    };

    $allGuestsJson = $allGuests->map(function ($u) use ($guestName, $guestSide, $guestRel) {
        return [
            'id'    => $u->id,
            'name'  => $guestName($u),
            'tag'   => trim($guestSide($u->guestProfile) . $guestRel($u->guestProfile)),
            'table' => $u->seatAssignment?->seat?->seatingTable?->name,
        ];
    })->values();
@endphp

<script>
    window.SEAT_TYPE_CONFIG = @json($typeConfig);
    window.ALL_GUESTS = @json($allGuestsJson);
    window.SUMMARY_TOTAL_GUESTS = {{ $summary['total'] }};
</script>

<div class="sx-app" id="sxApp">

    <header class="sx-topbar">
        <div class="sx-topbar__left">
            <a href="{{ route('admin.dashboard') }}" class="sx-back"><i class="fa-solid fa-chevron-left"></i>管理</a>
            <span class="sx-topbar__divider"></span>
            <span class="sx-topbar__title"><i class="fa-solid fa-table"></i>席次表</span>
            <div class="sx-view-tabs" id="viewTabs">
                <button type="button" class="sx-view-tab active" data-view="table"><i class="fa-solid fa-table-list"></i>表</button>
                <button type="button" class="sx-view-tab" data-view="floorplan"><i class="fa-solid fa-map"></i>配置図</button>
            </div>
        </div>
        <div class="sx-topbar__center">
            <div class="sx-progress">
                <div class="sx-progress__bar"><div class="sx-progress__fill" id="progressFill" style="width:{{ $pct }}%"></div></div>
                <span class="sx-progress__label" id="progressLabel">{{ $summary['assigned'] }} / {{ $summary['total'] }} 名配置済み</span>
            </div>
        </div>
        <div class="sx-topbar__right">
            <span class="sx-save-status is-saved" id="saveStatus"><i class="fa-solid fa-check"></i>自動保存済み</span>
            <a href="{{ route('admin.seating.guest-preview') }}" target="_blank" class="sx-btn-print"><i class="fa-solid fa-eye"></i>ゲスト表示プレビュー</a>
            <a href="{{ route('admin.seating.escort-cards') }}" target="_blank" class="sx-btn-print"><i class="fa-solid fa-address-card"></i>エスコートカード</a>
            <a href="{{ route('admin.seating.print') }}" target="_blank" class="sx-btn-print"><i class="fa-solid fa-print"></i>印刷用ページ</a>
            <nav class="sx-topbar-nav">
                <a href="{{ route('admin.dashboard') }}">RSVP管理</a>
                <a href="{{ route('admin.seating') }}" class="active">席次表</a>
                <a href="{{ route('admin.settings') }}">設定</a>
            </nav>
        </div>
    </header>

    <div class="sx-body" id="sxViewTable">

        {{-- ── 左サイドバー：未配置ゲスト ── --}}
        <aside class="sx-sidebar">
            <div class="sx-sidebar__search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input id="guestSearch" type="text" placeholder="名前で検索..." autocomplete="off">
            </div>
            <div class="sx-sidebar__head">
                <span>未配置ゲスト</span>
                <span class="sx-sidebar__count" id="unassignedCount">{{ $summary['unassigned'] }}名</span>
            </div>
            <ul class="sx-unassigned-list" id="unassignedList">
                @forelse ($unassignedGuests as $guest)
                @php $p = $guest->guestProfile; @endphp
                <li data-user-id="{{ $guest->id }}" data-name="{{ $guestName($guest) }}">
                    <span class="sx-unassigned-list__name">{{ $guestName($guest) }}</span>
                    <span class="sx-unassigned-list__tag">{{ trim($guestSide($p).$guestRel($p)) }}</span>
                </li>
                @empty
                <li class="sx-unassigned-list__empty" id="unassignedEmpty">全員配置済みです</li>
                @endforelse
            </ul>
        </aside>

        {{-- ── メイン：表形式エディタ ── --}}
        <main class="sx-main">
            <table class="sx-table" id="sxTable">
                <thead>
                    <tr>
                        <th class="sx-col-table">テーブル</th>
                        <th class="sx-col-num">#</th>
                        <th class="sx-col-type">タイプ</th>
                        <th class="sx-col-guest">ゲスト</th>
                        <th class="sx-col-action"></th>
                    </tr>
                </thead>
                <tbody id="sxBody">
                    @foreach ($tables as $table)
                        @php $seatCount = $table->seats->count(); @endphp
                        @forelse ($table->seats as $i => $seat)
                        <tr data-seat-id="{{ $seat->id }}" data-table-id="{{ $table->id }}" class="sx-row {{ $loop->first ? 'sx-row--first' : '' }}">
                            @if ($loop->first)
                            <td class="sx-cell-table" rowspan="{{ $seatCount }}" data-table-id="{{ $table->id }}">
                                @include('admin.partials.seating-table-cell', ['table' => $table])
                            </td>
                            @endif
                            <td class="sx-cell-num">{{ $i + 1 }}</td>
                            <td class="sx-cell-type">
                                <select class="sx-type" data-seat-id="{{ $seat->id }}">
                                    @foreach ($typeConfig as $key => $cfg)
                                    <option value="{{ $key }}" @selected($seat->type === $key)>{{ $cfg['label'] }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="sx-cell-guest">
                                <div class="sx-guest-wrap">
                                    <select class="sx-guest" data-seat-id="{{ $seat->id }}" data-current-user="{{ $seat->assignment?->user_id }}">
                                        <option value="">— 未配置 —</option>
                                        @foreach ($allGuests as $g)
                                        <option value="{{ $g->id }}" @selected($seat->assignment?->user_id === $g->id)>
                                            {{ $guestName($g) }}@if($g->seatAssignment && $g->seatAssignment->seat->seating_table_id !== $table->id) （{{ $g->seatAssignment->seat->seatingTable->name }}に配置中）@endif
                                        </option>
                                        @endforeach
                                    </select>
                                    @if ($seat->assignment)
                                    @php $p2 = $seat->assignment->user->guestProfile; @endphp
                                    <button type="button" class="sx-guest-detail{{ $p2?->has_allergy ? ' has-allergy' : '' }}"
                                        title="詳細情報を見る"
                                        data-guest-name="{{ $guestName($seat->assignment->user) }}"
                                        data-guest-side="{{ $guestSide($p2) }}"
                                        data-guest-rel="{{ $guestRel($p2) }}"
                                        data-guest-rel-detail="{{ $p2?->relationship_detail ?? '' }}"
                                        data-guest-phone="{{ $p2?->phone ?? '' }}"
                                        data-guest-postal="{{ $p2?->postal_code ?? '' }}"
                                        data-guest-address="{{ $p2?->address ?? '' }}"
                                        data-guest-allergy="{{ $p2?->has_allergy ? 'あり' : ($p2 ? 'なし' : '') }}"
                                        data-guest-allergy-notes="{{ $p2?->allergy_notes ?? '' }}"
                                        data-guest-count="{{ $p2?->attending_count ?? '' }}"
                                        data-guest-children="{{ $p2?->children_count ?? '' }}"
                                        data-guest-notes="{{ $p2?->notes ?? '' }}"
                                        data-table-name="{{ $table->name }}">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                            <td class="sx-cell-action">
                                <button type="button" class="sx-del-seat" data-seat-id="{{ $seat->id }}" title="この席を削除">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr data-table-id="{{ $table->id }}" class="sx-row sx-row--first">
                            <td class="sx-cell-table" rowspan="1" data-table-id="{{ $table->id }}">
                                @include('admin.partials.seating-table-cell', ['table' => $table])
                            </td>
                            <td class="sx-cell-empty" colspan="4">席がありません。「+席」で追加してください</td>
                        </tr>
                        @endforelse
                    @endforeach
                </tbody>
            </table>

            @if ($tables->isEmpty())
            <p class="sx-no-tables">テーブルがまだありません。下のフォームから追加してください</p>
            @endif

            <div class="sx-add-table">
                <input type="text" id="newTableName" placeholder="新しいテーブル名（例: 友人1）" maxlength="50">
                <input type="number" id="newTableSeats" value="4" min="0" max="8" title="席数">
                <button type="button" id="addTableBtn"><i class="fa-solid fa-plus"></i>テーブルを追加</button>
            </div>
        </main>

    </div>

    {{-- ── 配置図ビュー（会場内のテーブル配置。ドラッグで移動）── --}}
    <div class="sx-fp-wrap" id="sxViewFloorplan" hidden>
        <p class="sx-fp-hint"><i class="fa-solid fa-hand-pointer"></i>テーブルをドラッグして会場内の位置を調整、ゲストをテーブルにドラッグして配置できます</p>
        <div class="sx-fp-body">
            <aside class="sx-fp-sidebar">
                <div class="sx-sidebar__head">
                    <span>未配置ゲスト</span>
                    <span class="sx-sidebar__count" id="fpUnassignedCount">{{ $summary['unassigned'] }}名</span>
                </div>
                <ul class="sx-unassigned-list sx-fp-unassigned-list" id="fpUnassignedList">
                    @forelse ($unassignedGuests as $guest)
                    @php $p = $guest->guestProfile; @endphp
                    <li data-user-id="{{ $guest->id }}" data-name="{{ $guestName($guest) }}" draggable="true">
                        <span class="sx-unassigned-list__name">{{ $guestName($guest) }}</span>
                        <span class="sx-unassigned-list__tag">{{ trim($guestSide($p).$guestRel($p)) }}</span>
                    </li>
                    @empty
                    <li class="sx-unassigned-list__empty">全員配置済みです</li>
                    @endforelse
                </ul>
            </aside>
            <div class="sx-fp-main">
                <div class="sx-fp-palette">
                    <div class="sx-fp-new-table" id="sxFpNewTable" draggable="true">
                        <i class="fa-solid fa-plus"></i>ここをドラッグして新しいテーブルを配置
                    </div>
                </div>
                <div class="sx-fp-scroll">
                    <div class="sx-fp-room" id="sxFpRoom" style="width:1700px; height:760px;">
                        @foreach ($tables as $table)
                        @php $occupied = $table->seats->filter(fn($s) => $s->assignment !== null)->count(); @endphp
                        <div class="sx-fp-table" data-table-id="{{ $table->id }}"
                             style="left:{{ $table->pos_x }}px; top:{{ $table->pos_y }}px;">
                            <div class="sx-fp-table__head">
                                <span class="sx-fp-table__name">{{ $table->name }}</span>
                                <span class="sx-fp-table__count">{{ $occupied }}/{{ $table->seats->count() }}</span>
                            </div>
                            <ul class="sx-fp-seats">
                                @foreach ($table->seats as $seat)
                                <li class="sx-fp-seat {{ $seat->assignment ? 'is-occupied' : 'is-empty' }}"
                                    data-seat-id="{{ $seat->id }}" data-table-id="{{ $table->id }}">
                                    {{ $seat->assignment ? $guestName($seat->assignment->user) : '空席' }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 凡例 ── --}}
    <footer class="sx-legend">
        <span class="sx-legend__label">席タイプ:</span>
        @foreach ($typeConfig as $key => $cfg)
        <span class="sx-legend__item">
            <span class="sx-legend__dot" style="background:{{ $cfg['color'] }};"></span>{{ $cfg['label'] }}
        </span>
        @endforeach
    </footer>

</div>

{{-- ── ゲスト詳細情報モーダル ── --}}
<div class="detail-modal-overlay" id="guestDetailOverlay" aria-hidden="true">
    <div class="detail-modal">
        <div class="dm-header">
            <div>
                <p class="dm-title" id="gdName">—</p>
                <p class="dm-subtitle" id="gdTable">—</p>
            </div>
            <button type="button" class="dm-close" id="guestDetailClose" aria-label="閉じる">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="dm-section">
            <p class="dm-section-title">出席人数</p>
            <div class="dm-grid">
                <dl class="dm-item"><dt>出席人数（合計）</dt><dd id="gdCount">—</dd></dl>
                <dl class="dm-item"><dt>うちお子様</dt><dd id="gdChildren">—</dd></dl>
            </div>
        </div>

        <div class="dm-section">
            <p class="dm-section-title">お立場・ご関係</p>
            <div class="dm-grid">
                <dl class="dm-item"><dt>お立場</dt><dd id="gdSide">—</dd></dl>
                <dl class="dm-item"><dt>ご関係</dt><dd id="gdRel">—</dd></dl>
                <dl class="dm-item dm-full"><dt>ご関係の詳細</dt><dd id="gdRelDetail">—</dd></dl>
            </div>
        </div>

        <div class="dm-section">
            <p class="dm-section-title">食物アレルギー</p>
            <div class="dm-grid">
                <dl class="dm-item"><dt>アレルギー</dt><dd id="gdAllergy">—</dd></dl>
                <dl class="dm-item dm-full"><dt>アレルギー詳細</dt><dd id="gdAllergyNotes">—</dd></dl>
            </div>
        </div>

        <div class="dm-section">
            <p class="dm-section-title">連絡先</p>
            <div class="dm-grid">
                <dl class="dm-item"><dt>電話番号</dt><dd id="gdPhone">—</dd></dl>
                <dl class="dm-item"><dt>郵便番号</dt><dd id="gdPostal">—</dd></dl>
                <dl class="dm-item dm-full"><dt>住所</dt><dd id="gdAddress">—</dd></dl>
            </div>
        </div>

        <div class="dm-section">
            <p class="dm-section-title">メッセージ</p>
            <dl class="dm-item"><dd id="gdNotes" class="dm-pre">—</dd></dl>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ versioned_asset('js/seating-table.js') }}"></script>
@endpush
