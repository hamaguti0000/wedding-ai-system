@extends('layouts.app')
@section('title', '回答状況 | Admin')

@push('styles')
<style>
.rsvp-wrap {
    max-width: 1000px;
    margin: 24px auto 80px;
    padding: 0 14px;
    font-family: 'Noto Sans JP', sans-serif;
}
.rsvp-wrap h1 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    color: #b38b59;
    margin-bottom: 6px;
}

/* ── 管理ナビ ── */
.admin-nav {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.admin-nav a {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 14px;
    border-radius: 6px;
    font-size: 0.82rem;
    font-weight: 500;
    text-decoration: none;
    background: #fef9f0;
    color: #b38b59;
    border: 1px solid #e8d5b7;
    transition: background 0.2s;
    white-space: nowrap;
}
.admin-nav a.active,
.admin-nav a:hover { background: #b38b59; color: #fff; }

/* ── サマリー ── */
.summary-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}
.summary-card {
    background: #fff;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    box-shadow: 0 3px 12px rgba(0,0,0,0.07);
}
.summary-card .count { font-size: 2rem; font-weight: 700; line-height: 1; }
.summary-card .label { margin-top: 5px; font-size: 0.82rem; color: #777; }
.summary-card.attending .count { color: #27ae60; }
.summary-card.declining  .count { color: #e74c3c; }
.summary-card.pending    .count { color: #f39c12; }
.summary-card.total      .count { color: #b38b59; }

/* ── 出席合計バナー ── */
.attend-banner {
    background: #f0faf4;
    border: 1px solid #a8d8b9;
    border-radius: 10px;
    padding: 14px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    font-size: 0.9rem;
    color: #2d6a4f;
}
.attend-banner strong { font-size: 1.25rem; }

/* ── フィルタータブ ── */
.filter-tabs {
    display: flex;
    gap: 6px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.filter-tab {
    padding: 7px 16px;
    border-radius: 20px;
    font-size: 0.82rem;
    font-weight: 500;
    text-decoration: none;
    border: 1px solid #e8d5b7;
    color: #b38b59;
    background: #fef9f0;
    transition: background 0.15s;
    white-space: nowrap;
}
.filter-tab.active,
.filter-tab:hover { background: #b38b59; color: #fff; border-color: #b38b59; }

/* ── テーブルラッパー ── */
.rsvp-table-wrap {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.07);
    overflow: hidden;
}
.rsvp-table-wrap .table-head {
    padding: 18px 20px 12px;
    font-size: 0.82rem;
    color: #999;
}
.table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.87rem;
    min-width: 640px;
}
th {
    background: #fdf6ee;
    color: #b38b59;
    font-weight: 700;
    padding: 10px 14px;
    text-align: left;
    border-bottom: 2px solid #eedfc4;
    white-space: nowrap;
}
td {
    padding: 10px 14px;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: top;
    color: #333;
}
tr:last-child td { border-bottom: none; }
tr:hover td { background: #fffdf9; }

.badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    white-space: nowrap;
}
.badge.attending { background: #eafaf1; color: #27ae60; }
.badge.declining  { background: #fdf2f2; color: #e74c3c; }
.badge.pending    { background: #fef9ee; color: #f39c12; }

.allergy-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.72rem;
    font-weight: 600;
}
.allergy-yes { background: #fff3cd; color: #856404; }
.allergy-no  { background: #f0f0f0; color: #888; }

.text-muted { color: #aaa; font-size: 0.8rem; }
.text-sm    { font-size: 0.8rem; color: #666; }

.empty-state { text-align: center; padding: 48px; color: #aaa; }

/* ── レスポンシブ ── */
@media (min-width: 768px) {
    .rsvp-wrap { margin-top: 40px; padding: 0 20px; }
    .rsvp-wrap h1 { font-size: 1.8rem; }
    .summary-row { grid-template-columns: repeat(4, 1fr); }
}
@media (max-width: 767px) {
    .attend-banner { flex-direction: column; align-items: flex-start; gap: 8px; }
    .rsvp-table-wrap .table-head { padding: 14px 14px 8px; }
    th, td { padding: 8px 10px; font-size: 0.8rem; }
}
</style>
@endpush

@section('content')
<div class="rsvp-wrap">

    <h1>回答状況</h1>

    {{-- サマリーカード --}}
    <div class="summary-row">
        <div class="summary-card total">
            <div class="count">{{ $summary['total'] }}</div>
            <div class="label">招待総数</div>
        </div>
        <div class="summary-card attending">
            <div class="count">{{ $summary['attending'] }}</div>
            <div class="label">出席</div>
        </div>
        <div class="summary-card declining">
            <div class="count">{{ $summary['declining'] }}</div>
            <div class="label">欠席</div>
        </div>
        <div class="summary-card pending">
            <div class="count">{{ $summary['pending'] }}</div>
            <div class="label">未回答</div>
        </div>
    </div>

    {{-- 出席者合計 --}}
    @if ($summary['attending'] > 0)
    <div class="attend-banner">
        <div>
            <span>出席予定 合計：</span>
            <strong>{{ $totalAttending }}名</strong>
            @if ($totalChildren > 0)
            <span class="text-sm">（うちお子様 {{ $totalChildren }}名）</span>
            @endif
        </div>
        <div>
            <span>大人：</span>
            <strong>{{ $totalAttending - $totalChildren }}名</strong>
            @if ($totalChildren > 0)
            &nbsp;＋&nbsp;<span>お子様：</span><strong>{{ $totalChildren }}名</strong>
            @endif
        </div>
    </div>
    @endif

    {{-- フィルタータブ --}}
    <div class="filter-tabs">
        <a href="{{ route('admin.rsvp') }}"
           class="filter-tab {{ $filter === 'all' ? 'active' : '' }}">
            全員（{{ $summary['total'] }}）
        </a>
        <a href="{{ route('admin.rsvp', ['filter' => 'attending']) }}"
           class="filter-tab {{ $filter === 'attending' ? 'active' : '' }}">
            出席（{{ $summary['attending'] }}）
        </a>
        <a href="{{ route('admin.rsvp', ['filter' => 'declining']) }}"
           class="filter-tab {{ $filter === 'declining' ? 'active' : '' }}">
            欠席（{{ $summary['declining'] }}）
        </a>
        <a href="{{ route('admin.rsvp', ['filter' => 'pending']) }}"
           class="filter-tab {{ $filter === 'pending' ? 'active' : '' }}">
            未回答（{{ $summary['pending'] }}）
        </a>
    </div>

    {{-- 詳細テーブル --}}
    <div class="rsvp-table-wrap">
        <div class="table-head">{{ $filtered->count() }}件 表示中</div>

        @if ($filtered->isEmpty())
        <div class="empty-state">該当するゲストがいません</div>
        @else
        <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>氏名</th>
                    <th>お立場 / ご関係</th>
                    <th>出欠</th>
                    <th>人数</th>
                    <th>アレルギー</th>
                    <th>連絡先</th>
                    <th>回答日時</th>
                    <th>メモ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($filtered as $guest)
                @php $p = $guest->guestProfile; @endphp
                <tr>
                    {{-- 氏名 --}}
                    <td>
                        @if ($p && ($p->last_name || $p->first_name))
                            <strong>{{ trim($p->last_name . ' ' . $p->first_name) }}</strong>
                            @if ($p->furigana())
                            <br><span class="text-muted">{{ $p->furigana() }}</span>
                            @endif
                        @else
                            <span class="text-muted">{{ $guest->username }}</span>
                        @endif
                    </td>

                    {{-- お立場 / ご関係 --}}
                    <td>
                        @if ($p?->guest_side)
                            <span>{{ $p->guestSideLabel() }}</span>
                            @if ($p->relationship)
                            <br><span class="text-muted">{{ $p->relationshipLabel() }}</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- 出欠 --}}
                    <td>
                        @php $status = $p?->participation ?? 'pending'; @endphp
                        <span class="badge {{ $status }}">
                            {{ ['attending' => '出席', 'declining' => '欠席', 'pending' => '未回答'][$status] }}
                        </span>
                    </td>

                    {{-- 人数 --}}
                    <td>
                        @if ($p?->isAttending())
                            {{ $p->attending_count }}名
                            @if ($p->children_count > 0)
                            <br><span class="text-muted">子{{ $p->children_count }}名</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- アレルギー --}}
                    <td>
                        @if ($p?->has_allergy)
                            <span class="allergy-badge allergy-yes">あり</span>
                            @if ($p->allergy_notes)
                            <br><span class="text-sm">{{ $p->allergy_notes }}</span>
                            @endif
                        @elseif ($p)
                            <span class="allergy-badge allergy-no">なし</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- 連絡先 --}}
                    <td>
                        @if ($p?->phone)
                            {{ $p->phone }}
                        @endif
                        @if ($p?->address)
                            @if ($p->phone)<br>@endif
                            <span class="text-sm">{{ $p->address }}</span>
                        @endif
                        @if (!$p?->phone && !$p?->address)
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- 回答日時 --}}
                    <td>
                        @if ($p?->responded_at)
                            {{ $p->responded_at->format('m/d') }}
                            <br><span class="text-muted">{{ $p->responded_at->format('H:i') }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- メモ --}}
                    <td>
                        @if ($p?->notes)
                            <span class="text-sm">{{ Str::limit($p->notes, 40) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>

</div>
@endsection
