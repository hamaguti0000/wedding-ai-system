@extends('layouts.app')
@section('title', 'RSVP管理 | Admin')

@push('styles')
<style>
.admin-wrap {
    max-width: 900px;
    margin: 24px auto 80px;
    padding: 0 14px;
    font-family: 'Noto Sans JP', sans-serif;
}
.admin-wrap h1 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    color: #b38b59;
    margin-bottom: 6px;
}
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
    transition: background 0.2s;
    white-space: nowrap;
}
.admin-nav a.active,
.admin-nav a:hover { background: #b38b59; color: #fff; }
.admin-nav a {
    background: #fef9f0;
    color: #b38b59;
    border: 1px solid #e8d5b7;
}
@media (min-width: 768px) {
    .admin-wrap { margin-top: 40px; padding: 0 20px; }
    .admin-wrap h1 { font-size: 1.8rem; }
    .admin-nav { gap: 12px; margin-bottom: 32px; }
    .admin-nav a { padding: 8px 18px; font-size: 0.85rem; }
}

/* サマリーカード */
.summary-cards {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
    margin-bottom: 40px;
}
@media (min-width: 480px) {
    .summary-cards { grid-template-columns: repeat(2, 1fr); gap: 14px; }
}
@media (min-width: 768px) {
    .summary-cards { grid-template-columns: repeat(3, 1fr); gap: 16px; }
}

.summary-card {
    background: #fff;
    border-radius: 14px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 4px 14px rgba(0,0,0,0.07);
}

.summary-card .count {
    font-size: 2.4rem;
    font-weight: 700;
    line-height: 1;
}

.summary-card .label {
    margin-top: 6px;
    font-size: 0.85rem;
    color: #777;
}

.summary-card.attending .count { color: #27ae60; }
.summary-card.declining  .count { color: #e74c3c; }
.summary-card.pending    .count { color: #f39c12; }

/* ゲストテーブル */
.guest-table-wrap {
    background: #fff;
    border-radius: 14px;
    padding: 24px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.07);
}

.guest-table-wrap h2 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #444;
    margin-bottom: 16px;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.92rem;
}

th {
    background: #fdf6ee;
    color: #b38b59;
    font-weight: 700;
    padding: 10px 14px;
    text-align: left;
    border-bottom: 2px solid #eedfc4;
}

td {
    padding: 10px 14px;
    border-bottom: 1px solid #f0f0f0;
    color: #333;
    vertical-align: middle;
}

tr:last-child td { border-bottom: none; }

.badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 700;
    white-space: nowrap;
}

.badge.attending { background: #eafaf1; color: #27ae60; }
.badge.declining  { background: #fdf2f2; color: #e74c3c; }
.badge.pending    { background: #fef9ee; color: #f39c12; }

.text-muted { color: #aaa; }

/* テーブルラッパー */
.table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }

/* 空状態 */
.empty-state {
    text-align: center;
    padding: 48px 20px;
    color: #aaa;
}
.empty-state__icon  { font-size: 2.5rem; margin-bottom: 12px; }
.empty-state__title { font-size: 1rem; font-weight: 600; color: #888; margin: 0 0 8px; }
.empty-state__desc  { font-size: 0.84rem; line-height: 1.6; margin: 0; }

/* モバイルで不要列を非表示 */
@media (max-width: 767px) {
    .col-side, .col-date { display: none; }
    .col-count { white-space: nowrap; }
    th, td { padding: 8px 10px; font-size: 0.82rem; }
    .guest-table-wrap { padding: 16px; }
    .guest-table-wrap h2 { font-size: 0.95rem; }
    .summary-card .count { font-size: 2rem; }
}

.text-muted { color: #aaa; font-size: 0.85rem; }
</style>
@endpush

@section('content')
<div class="admin-wrap">
    <h1>ゲスト一覧</h1>

    {{-- サマリー --}}
    <div class="summary-cards">
        <div class="summary-card attending">
            <div class="count">{{ $summary['attending'] }}</div>
            <div class="label">出席 / {{ $summary['total'] }}名</div>
        </div>
        <div class="summary-card declining">
            <div class="count">{{ $summary['declining'] }}</div>
            <div class="label">欠席 / {{ $summary['total'] }}名</div>
        </div>
        <div class="summary-card pending">
            <div class="count">{{ $summary['pending'] }}</div>
            <div class="label">未回答 / {{ $summary['total'] }}名</div>
        </div>
    </div>

    {{-- ゲスト一覧 --}}
    <div class="guest-table-wrap">
        <h2>ゲスト一覧</h2>

        @if ($guests->isEmpty())
        <div class="empty-state">
            <div class="empty-state__icon">👥</div>
            <p class="empty-state__title">まだゲストが登録されていません</p>
            <p class="empty-state__desc">ゲストが登録ページからアカウントを作成すると、ここに表示されます。</p>
        </div>
        @else
        <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>氏名</th>
                    <th class="col-side">お立場</th>
                    <th>出欠</th>
                    <th class="col-count">人数</th>
                    <th class="col-date">回答日時</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($guests as $guest)
                @php $p = $guest->guestProfile; @endphp
                <tr>
                    <td>
                        @if ($p && ($p->last_name || $p->first_name))
                            <strong>{{ trim($p->last_name . ' ' . $p->first_name) }}</strong>
                            @if ($p->furigana())
                            <br><span class="text-muted">{{ $p->furigana() }}</span>
                            @endif
                        @else
                            <span class="text-muted">{{ $guest->username }}</span>
                        @endif
                        <br><span class="text-muted" style="font-size:0.76rem;">@{{ $guest->username }}</span>
                    </td>
                    <td class="col-side">
                        @if ($p?->guest_side)
                        <span style="font-size:0.82rem;">{{ $p->guestSideLabel() }}</span>
                        @if ($p->relationship)
                        <br><span class="text-muted" style="font-size:0.76rem;">{{ $p->relationshipLabel() }}</span>
                        @endif
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if (!$p || $p->participation === 'pending')
                        <span class="badge pending">未回答</span>
                        @elseif ($p->participation === 'attending')
                        <span class="badge attending">出席</span>
                        @else
                        <span class="badge declining">欠席</span>
                        @endif
                    </td>
                    <td class="col-count">
                        @if ($p?->isAttending())
                        {{ $p->attending_count }}名
                        @if ($p->children_count > 0)
                        <br><span class="text-muted" style="font-size:0.76rem;">子{{ $p->children_count }}名</span>
                        @endif
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="col-date">
                        @if ($p?->responded_at)
                        {{ $p->responded_at->format('m/d') }}<br>
                        <span class="text-muted" style="font-size:0.76rem;">{{ $p->responded_at->format('H:i') }}</span>
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
