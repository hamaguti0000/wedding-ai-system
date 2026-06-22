@extends('layouts.app')
@section('title', '席次表管理 | Admin')

@push('styles')
<link rel="stylesheet" href="{{ css_asset('css/seating.css') }}">
@endpush

@section('content')

@php
    // $summary['assigned'] / $summary['total'] は int のみ（Collection ではない）
    $pct = $summary['total'] > 0
        ? round($summary['assigned'] / $summary['total'] * 100)
        : 0;

    // グローバル関数宣言を避けるためクロージャを使用
    // （同一リクエスト内で同じ view が複数回レンダリングされても再宣言エラーが起きない）
    $guestSide  = fn($p) => match($p?->guest_side) { 'groom' => '新郎側', 'bride' => '新婦側', default => '' };
    $guestRel   = fn($p) => match($p?->relationship) {
        'friend' => '友人', 'family' => '親族', 'colleague' => '職場', 'other' => 'その他', default => ''
    };
    $guestInit  = fn($user) => mb_substr($user->guestProfile?->last_name ?? $user->name, 0, 1, 'UTF-8');
    $guestName  = function ($user) {
        $p = $user->guestProfile;
        return $p ? $p->last_name . ' ' . $p->first_name : $user->name;
    };
@endphp

<script>window.SEAT_TYPE_CONFIG = @json($typeConfig);</script>

<div class="st-app" id="stApp" data-preview="false">

    {{-- ── トップバー ── --}}
    <header class="st-topbar">
        <div class="st-topbar__left">
            <a href="{{ route('admin.dashboard') }}" class="st-back">
                <i class="fa-solid fa-chevron-left"></i>管理
            </a>
            <span class="st-topbar__divider"></span>
            <span class="st-topbar__title">
                <i class="fa-solid fa-chair"></i>席次表
            </span>
        </div>

        <div class="st-topbar__center">
            <div class="st-progress">
                <div class="st-progress__bar">
                    <div class="st-progress__fill" id="progressFill" style="width:{{ $pct }}%"></div>
                </div>
                <span class="st-progress__label" id="progressLabel">
                    {{ $summary['assigned'] }} / {{ $summary['total'] }} 名配置済み
                </span>
            </div>
        </div>

        <div class="st-topbar__right">
            <span class="st-save-status is-saved" id="saveStatus">
                <i class="fa-solid fa-check"></i>自動保存済み
            </span>
            <span class="st-topbar__divider"></span>
            <button class="st-btn-preview" id="previewToggle" title="印刷プレビューを表示">
                <i class="fa-solid fa-eye" aria-hidden="true"></i>プレビュー
            </button>
            <button class="st-btn-print" id="printBtn" title="印刷">
                <i class="fa-solid fa-print" aria-hidden="true"></i>印刷
            </button>
            <span class="st-topbar__divider" id="previewDivider"></span>
            <nav class="st-topbar-nav">
                <a href="{{ route('admin.dashboard') }}">RSVP管理</a>
                <a href="{{ route('admin.seating') }}" class="active">席次表</a>
                <a href="{{ route('admin.settings') }}">設定</a>
            </nav>
        </div>
    </header>

    {{-- ── メインエリア ── --}}
    <div class="st-body">

        {{-- ── 左サイドバー：ゲストリスト ── --}}
        <aside class="st-sidebar">

            {{-- 検索・フィルター --}}
            <div class="st-sidebar__search-wrap">
                <div class="st-search">
                    <i class="fa-solid fa-magnifying-glass st-search__icon"></i>
                    <input class="st-search__input" id="guestSearch"
                           type="text" placeholder="名前で検索..." autocomplete="off">
                </div>
                <div class="st-filter-tabs" id="sideFilterSide">
                    <button class="st-filter-tab active" data-filter="all">全員</button>
                    <button class="st-filter-tab" data-filter="groom">新郎側</button>
                    <button class="st-filter-tab" data-filter="bride">新婦側</button>
                </div>
            </div>

            {{-- 未配置ゲスト --}}
            <div class="st-sidebar__section" style="flex:1;overflow:hidden;display:flex;flex-direction:column;">
                <div class="st-sidebar__section-head">
                    <span class="st-sidebar__section-title">未配置</span>
                    <span class="st-sidebar__section-count" id="unassignedCount">{{ $summary['unassigned'] }}名</span>
                </div>
                <div class="st-guest-list" id="unassignedPool">
                    @forelse ($unassignedGuests as $guest)
                    @php $p = $guest->guestProfile; @endphp
                    <div class="st-guest-card"
                         data-user-id="{{ $guest->id }}"
                         data-name="{{ $guestName($guest) }}"
                         data-side="{{ $p?->guest_side ?? '' }}"
                         data-rel="{{ $p?->relationship ?? '' }}"
                         draggable="true">
                        <div class="st-guest-card__avatar st-guest-card__avatar--{{ $p?->guest_side ?? 'none' }}">
                            {{ $guestInit($guest) }}
                        </div>
                        <div class="st-guest-card__info">
                            <p class="st-guest-card__name">{{ $guestName($guest) }}</p>
                            <div class="st-guest-card__tags">
                                @if ($p?->guest_side)
                                <span class="st-tag st-tag--{{ $p->guest_side }}">{{ $guestSide($p) }}</span>
                                @endif
                                @if ($p?->relationship)
                                <span class="st-tag st-tag--rel">{{ $guestRel($p) }}</span>
                                @endif
                            </div>
                        </div>
                        <i class="fa-solid fa-grip-dots st-guest-card__grip"></i>
                    </div>
                    @empty
                    <div class="st-guest-list__empty" id="poolEmpty">
                        <i class="fa-solid fa-circle-check" style="color:var(--ds-attending-text);font-size:1.4rem;"></i>
                        <p>全員配置済みです</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- 配置済みゲスト（コンパクト）--}}
            <div class="st-sidebar__section st-sidebar__section--assigned">
                <div class="st-sidebar__section-head">
                    <span class="st-sidebar__section-title">配置済み</span>
                    <span class="st-sidebar__section-count" id="assignedCount">{{ $summary['assigned'] }}名</span>
                </div>
                <div class="st-assigned-list" id="assignedList">
                    @foreach ($assignedGuests as $guest)
                    @php
                        $p   = $guest->guestProfile;
                        $tbl = $guest->seatAssignment?->seat?->seatingTable?->name ?? '—';
                    @endphp
                    <div class="st-assigned-item"
                         data-user-id="{{ $guest->id }}"
                         data-table="{{ $tbl }}">
                        <div class="st-assigned-item__dot st-guest-card__avatar--{{ $p?->guest_side ?? 'none' }}"></div>
                        <span class="st-assigned-item__name">{{ $guestName($guest) }}</span>
                        <span class="st-assigned-item__table">{{ $tbl }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

        </aside>

        {{-- ── キャンバス ── --}}
        <div class="st-canvas-area" id="canvasArea">

            {{-- 印刷プレビューヘッダー（プレビュー・印刷時のみ表示）--}}
            <div class="st-print-header" id="stPrintHeader">
                <p class="st-print-header__title">席 次 表</p>
                @if($setting?->groom_name || $setting?->bride_name)
                <p class="st-print-header__couple">{{ $setting?->groom_name ?? '' }} ＆ {{ $setting?->bride_name ?? '' }}</p>
                @endif
                @if($setting?->ceremony_date)
                <p class="st-print-header__meta">
                    {{ \Carbon\Carbon::parse($setting->ceremony_date)->format('Y年n月j日') }}
                    @if($setting?->venue_name) ・{{ $setting->venue_name }}@endif
                </p>
                @endif
            </div>

            {{-- 1800×1100 仮想キャンバス（JS が transform:scale でスケール）--}}
            <div class="st-canvas-scaler" id="canvasScaler">
            <div class="st-canvas" id="seatingCanvas">

                @if ($tables->isEmpty())
                <div class="st-canvas__empty" id="canvasEmpty">
                    <i class="fa-solid fa-chair" style="font-size:2.5rem;opacity:0.3;"></i>
                    <p>テーブルがありません</p>
                </div>
                @endif

                @foreach ($tables as $i => $table)
                @php
                    $x = $table->pos_x ?: (24 + ($i % 8) * 220);
                    $y = $table->pos_y ?: (24 + (int)floor($i / 8) * 230);
                    $seatCount     = $table->seats->count();
                    $assignedCount = $table->seats->filter(fn($s) => $s->assignment !== null)->count();
                    $maxPosX = $table->seats->max('pos_x') ?? 0;
                    $maxPosY = $table->seats->max('pos_y') ?? 0;
                    $bodyMinW = max(180, intval($maxPosX) + 54);
                    $bodyMinH = max(126, intval($maxPosY) + 76);
                @endphp
                <div class="canvas-table"
                     id="ct-{{ $table->id }}"
                     data-table-id="{{ $table->id }}"
                     style="left:{{ $x }}px; top:{{ $y }}px;">

                    <div class="canvas-table__handle" data-drag-handle>
                        <i class="fa-solid fa-grip-dots ct-grip"></i>
                        <span class="ct-name" title="{{ $table->name }}">{{ $table->name }}</span>
                        <span class="ct-count" id="ct-meta-{{ $table->id }}">{{ $assignedCount }}/{{ $seatCount }}</span>
                    </div>

                    <div class="canvas-table__body"
                         id="ct-body-{{ $table->id }}"
                         data-table-id="{{ $table->id }}"
                         style="min-width:{{ $bodyMinW }}px; min-height:{{ $bodyMinH }}px;">

                        @foreach ($table->seats as $seat)
                        @php
                            $user     = $seat->assignment?->user;
                            $occupied = $user !== null;
                            $initial  = $occupied ? $guestInit($user) : '';
                            $fullName = $occupied ? $guestName($user) : '';
                            $userId   = $occupied ? $user->id : '';
                        @endphp
                        <div class="canvas-seat {{ $occupied ? 'is-occupied' : '' }}"
                             id="seat-{{ $seat->id }}"
                             data-seat-id="{{ $seat->id }}"
                             data-type="{{ $seat->type }}"
                             data-user-id="{{ $userId }}"
                             style="left:{{ $seat->pos_x }}px; top:{{ $seat->pos_y }}px;">
                            <div class="canvas-seat__circle">
                                <span class="canvas-seat__initial">{{ $initial }}</span>
                                <span class="canvas-seat__plus" aria-hidden="true">+</span>
                            </div>
                            @if ($occupied)
                            <div class="canvas-seat__tooltip">{{ $fullName }}</div>
                            @endif
                        </div>
                        @endforeach

                        @if ($seatCount === 0)
                        <p class="ct-hint" id="ct-hint-{{ $table->id }}">
                            ＋ を押して席を追加
                        </p>
                        @endif
                    </div>
                </div>
                @endforeach

            </div>{{-- /.st-canvas --}}
            </div>{{-- /.st-canvas-scaler --}}

        </div>{{-- /.st-canvas-area --}}

    </div>{{-- /.st-body --}}

    {{-- ── 凡例バー ── --}}
    <footer class="st-legend">
        <span class="st-legend__label">席タイプ:</span>
        @foreach ($typeConfig as $key => $cfg)
        <span class="st-legend__item">
            <span class="st-legend__dot" style="background:{{ $cfg['color'] }};"></span>
            {{ $cfg['label'] }}
        </span>
        @endforeach
    </footer>

</div>{{-- /.st-app --}}

{{-- ── 右プロパティパネル（席選択時に表示）── --}}
<div class="st-props" id="seatProps" aria-hidden="true">
    <div class="st-props__header">
        <span class="st-props__title">席のプロパティ</span>
        <button class="st-props__close" id="propsClose" aria-label="閉じる">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <div class="st-props__body">

        {{-- 席タイプ選択 --}}
        <div class="sp-section">
            <p class="sp-section__title">席タイプ</p>
            <div class="sp-type-grid" id="propsTypeGrid">
                @foreach ($typeConfig as $key => $cfg)
                <label class="sp-type-option" data-type="{{ $key }}">
                    <input type="radio" name="seatType" value="{{ $key }}" class="sp-type-radio">
                    <span class="sp-type-dot" style="background:{{ $cfg['color'] }};"></span>
                    <span class="sp-type-label">{{ $cfg['label'] }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- 配置ゲスト情報 --}}
        <div class="sp-section" id="propsGuestSection" style="display:none;">
            <p class="sp-section__title">配置ゲスト</p>
            <div class="sp-guest-info" id="propsGuestInfo">
                <div class="sp-guest-avatar" id="propsGuestAvatar"></div>
                <div>
                    <p class="sp-guest-name" id="propsGuestName"></p>
                    <p class="sp-guest-tags" id="propsGuestTags"></p>
                </div>
            </div>
            <button class="sp-btn sp-btn--ghost" id="propsUnassign">
                <i class="fa-solid fa-rotate-left"></i>配置を解除
            </button>
        </div>

        {{-- 空席メッセージ --}}
        <div class="sp-section" id="propsEmptySection">
            <p class="sp-empty-hint">ゲストリストからドラッグして<br>この席に配置できます</p>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/seating.js') }}"></script>
@endpush
