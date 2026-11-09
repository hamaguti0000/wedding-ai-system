@extends('layouts.app')
@section('title', '受付一覧 | Admin')

@push('styles')
<style>
.cg-wrap { display: grid; gap: 20px; }
.cg-hero {
    display: flex; justify-content: space-between; gap: 16px; align-items: flex-end;
    background: linear-gradient(180deg, #fffaf3 0%, #fff 100%);
    border: 1px solid #f0e3d2; border-radius: 18px; padding: 24px 26px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.05);
}
.cg-hero h1 { margin: 0; }
.cg-hero p { margin: 8px 0 0; color: #8d7865; font-size: .88rem; line-height: 1.6; }
.cg-links { display: flex; flex-wrap: wrap; gap: 10px; }
.cg-link {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px;
    border-radius: 999px; border: 1px solid #e3d3bf; background: #fffdf9;
    color: #5a4637; text-decoration: none; font-size: .86rem; font-weight: 600;
}
.cg-link--primary { background: #b38b59; color: #fff; border-color: #b38b59; }
.cg-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.cg-metric {
    border-radius: 14px; padding: 16px; border: 1px solid #f0e3d2; background: #fff;
    box-shadow: 0 4px 14px rgba(0,0,0,0.04);
}
.cg-metric__label { color: #b38b59; font-size: .72rem; letter-spacing: 1.6px; text-transform: uppercase; font-weight: 700; }
.cg-metric__value { margin-top: 10px; font-size: 1.9rem; font-weight: 700; line-height: 1; color: #352820; }
.cg-metric__sub { margin-top: 6px; color: #7f6a57; font-size: .82rem; }
.cg-panel {
    background: #fff; border: 1px solid #f0e3d2; border-radius: 16px; padding: 20px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.05);
}
.cg-toolbar {
    display: grid; grid-template-columns: minmax(0, 1fr) minmax(280px, 340px); gap: 12px; align-items: end;
    margin-bottom: 16px;
}
.cg-toolbar__filters { display: flex; flex-wrap: wrap; gap: 8px; min-width: 0; }
.cg-pill {
    display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px;
    border-radius: 999px; border: 1px solid #e3d3bf; background: #fffdf9;
    color: #5a4637; font-size: .8rem; font-weight: 700; text-decoration: none;
}
.cg-pill.is-active { background: #b38b59; color: #fff; border-color: #b38b59; }
.cg-search {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 8px;
    align-items: center;
    min-width: 0;
    justify-self: end;
    width: 100%;
}
.cg-search input {
    width: 100%; padding: 12px 14px; border-radius: 12px; border: 1px solid #e4d6c3;
    background: #fff; font-size: .92rem; min-width: 0;
}
.cg-search button {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    min-height: 44px; padding: 0 14px; border-radius: 12px; border: 0;
    background: #b38b59; color: #fff; font-weight: 700; cursor: pointer;
}
.cg-list { display: grid; gap: 10px; }
.cg-item {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(132px, 152px);
    gap: 12px;
    padding: 14px 14px 13px;
    border-radius: 14px;
    background: #fcf8f2;
    border: 1px solid #f2e7d8;
    min-width: 0;
    align-items: start;
}
.cg-item__content {
    display: block;
    min-width: 0;
    color: inherit;
    text-decoration: none;
    border-radius: 12px;
    padding: 2px 0;
}
.cg-item__content:hover .cg-item__name { color: #b38b59; }
.cg-item__name { font-weight: 700; color: #36281d; font-size: .98rem; }
.cg-item__sub { margin-top: 4px; color: #8f7d6f; font-size: .8rem; line-height: 1.5; }
.cg-item__meta {
    display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px;
}
.cg-tag {
    display: inline-flex; align-items: center; gap: 6px; padding: 5px 9px; border-radius: 999px;
    background: #efe2d1; color: #725b47; font-size: .74rem; font-weight: 700;
}
.cg-tag.is-live { background: #e4f4ea; color: #2f6e42; }
.cg-tag.is-warn { background: #fbf0da; color: #8e632a; }
.cg-actions {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0;
    align-self: center;
    min-width: 0;
    width: 100%;
}
.cg-actions form { margin: 0; }
.cg-actions form,
.cg-actions a { min-width: 0; width: 100%; }
.cg-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    padding: 11px 12px; border-radius: 12px; border: 0; background: #b38b59;
    color: #fff; font-weight: 700; cursor: pointer; text-decoration: none; min-height: 44px;
    box-shadow: 0 4px 12px rgba(179,139,89,0.14);
    transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    white-space: nowrap;
    width: 100%;
}
.cg-btn:hover { transform: translateY(-1px); }
.cg-btn--ghost {
    background: #fff;
    color: #5a4637;
    border: 1px solid #e5d7c5;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.cg-btn--ghost:hover {
    background: #fffaf3;
    border-color: #d9c3a9;
    color: #433226;
}
.cg-btn--danger {
    background: #fff5f5;
    color: #b42318;
    border: 1px solid #f1c7c1;
    box-shadow: 0 2px 8px rgba(180,35,24,0.08);
}
.cg-btn--danger:hover {
    background: #ffecec;
    border-color: #ea9b92;
    color: #991b1b;
}
.cg-btn--primary {
    background: #b38b59;
    color: #fff;
    border: 1px solid #b38b59;
}
.cg-btn--primary:hover {
    background: #a67c4c;
    border-color: #a67c4c;
}
.cg-empty {
    padding: 28px 18px; text-align: center; border-radius: 12px;
    background: #fcf8f2; border: 1px dashed #e4d6c3; color: #8a7967;
}
@media (max-width: 1100px) {
    .cg-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 720px) {
    .cg-wrap {
        gap: 12px;
        padding: 0 14px 16px;
    }
    .cg-hero {
        flex-direction: column;
        align-items: flex-start;
        padding: 14px 14px 12px;
        border-radius: 16px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.05);
    }
    .cg-hero h1 { font-size: 1.05rem; }
    .cg-hero p { font-size: .8rem; }
    .cg-toolbar { grid-template-columns: 1fr; }
    .cg-toolbar__filters {
        width: 100%;
        display: grid;
        grid-template-columns: 1fr;
        grid-auto-rows: minmax(44px, auto);
        gap: 10px;
    }
    .cg-toolbar__filters > * {
        width: 100%;
        min-width: 0;
        max-width: 100%;
    }
    .cg-pill {
        display: flex;
        align-items: center;
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
        text-align: left;
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
        min-height: 42px;
        padding: 12px 14px;
        line-height: 1.45;
        flex: none;
        float: none;
        position: static;
    }
    .cg-search {
        width: 100%;
        justify-self: stretch;
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .cg-search input,
    .cg-search button {
        width: 100%;
    }
    .cg-search button {
        min-width: 0;
        white-space: nowrap;
    }
    .cg-summary { grid-template-columns: 1fr; }
    .cg-panel {
        padding: 14px;
        border-radius: 16px;
    }
    .cg-item {
        grid-template-columns: 1fr;
        gap: 10px;
        padding: 12px;
        border-radius: 14px;
    }
    .cg-item__content { padding: 0; }
    .cg-item__meta { gap: 6px; }
    .cg-actions {
        width: 100%;
        grid-template-columns: 1fr;
        gap: 0;
    }
    .cg-actions form,
    .cg-actions a { width: 100%; }
    .cg-actions .cg-btn { width: 100%; min-height: 44px; }
    .cg-empty {
        padding: 16px 14px;
        text-align: center;
    }
}
</style>
@endpush

@section('content')
@php
    $status = $status ?? 'checked_in';
    $statusLabels = $statusLabels ?? [];
    $query = $q ?? '';
@endphp

<div class="cg-wrap">
    <section class="cg-hero">
        <div>
            <h1>受付一覧</h1>
            <p>受付済み・未受付のゲストを一覧で切り替えながら、手動で受付や取り消しを行えます。</p>
        </div>
        <div class="cg-links">
            <a href="{{ route('admin.checkin.index') }}" class="cg-link cg-link--primary">
                <i class="fa-solid fa-qrcode"></i> 受付スキャン
            </a>
            <a href="{{ route('admin.ops') }}" class="cg-link">
                <i class="fa-solid fa-chart-line"></i> 運営ダッシュボード
            </a>
            <a href="{{ route('admin.users') }}" class="cg-link">
                <i class="fa-solid fa-user-group"></i> ゲスト詳細
            </a>
        </div>
    </section>

    <section class="cg-summary">
        <div class="cg-metric">
            <div class="cg-metric__label">Guests</div>
            <div class="cg-metric__value">{{ $summary['total'] }}</div>
            <div class="cg-metric__sub">登録済みゲスト</div>
        </div>
        <div class="cg-metric">
            <div class="cg-metric__label">Checked in</div>
            <div class="cg-metric__value">{{ $summary['checked_in'] }}</div>
            <div class="cg-metric__sub">受付済み</div>
        </div>
        <div class="cg-metric">
            <div class="cg-metric__label">Pending</div>
            <div class="cg-metric__value">{{ $summary['pending'] }}</div>
            <div class="cg-metric__sub">受付待ち</div>
        </div>
        <div class="cg-metric">
            <div class="cg-metric__label">Attending</div>
            <div class="cg-metric__value">{{ $summary['attending'] }}</div>
            <div class="cg-metric__sub">出席回答</div>
        </div>
    </section>

    <section class="cg-panel">
        <div class="cg-toolbar">
            <div class="cg-toolbar__filters">
                @foreach ($statusLabels as $key => $label)
                    <a href="{{ route('admin.checkin.guests', array_filter(['status' => $key, 'q' => $query], fn ($v) => $v !== '')) }}"
                       class="cg-pill {{ $status === $key ? 'is-active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <form method="GET" action="{{ route('admin.checkin.guests') }}" class="cg-search" data-live-search-form data-live-search-delay="250" data-live-search-target=".cg-list">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="search" name="q" value="{{ $query }}" placeholder="氏名、ふりがな、ユーザー名で検索">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i>検索</button>
            </form>
        </div>

        <div class="cg-list">
            @forelse ($guests as $guest)
                @php
                    $profile = $guest;
                    $user = $guest->user;
                    $seat = $user?->seatAssignment?->seat;
                    $table = $seat?->seatingTable;
                @endphp
                <div class="cg-item">
                    <a href="{{ route('admin.users.show', $user->id) }}" class="cg-item__content">
                        <div class="cg-item__name">{{ $profile->fullName() }}</div>
                        <div class="cg-item__sub">
                            {{ $user?->username }}
                            @if ($table)
                                ・{{ $table->name }}@if ($seat?->label) / {{ $seat->label }}@endif
                            @endif
                            @if ($profile->checked_in_at)
                                ・{{ $profile->checked_in_at->format('Y/m/d H:i') }}
                            @endif
                        </div>
                        <div class="cg-item__meta">
                            <span class="cg-tag {{ $profile->isCheckedIn() ? 'is-live' : 'is-warn' }}">
                                {{ $profile->checkInStatusLabel() }}
                            </span>
                            <span class="cg-tag">{{ $profile->participationLabel() }}</span>
                            @if ($profile->checkedInBy)
                                <span class="cg-tag">受付者: {{ $profile->checkedInBy->name }}</span>
                            @endif
                        </div>
                    </a>
                    <div class="cg-actions">
                        @if ($profile->isCheckedIn())
                            <form method="POST" action="{{ route('admin.checkin.guests.cancel', $profile) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="cg-btn cg-btn--danger">
                                    <i class="fa-solid fa-rotate-left"></i> 受付取消
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.checkin.guests.check-in', $profile) }}">
                                @csrf
                                <button type="submit" class="cg-btn cg-btn--primary">
                                    <i class="fa-solid fa-check"></i> 受付する
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="cg-empty">該当するゲストがいません。</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
