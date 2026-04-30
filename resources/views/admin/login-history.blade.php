@extends('layouts.app')
@section('title', 'ログイン履歴 | Admin')

@push('styles')
<style>
.lh-wrap {
    max-width: 1000px;
    margin: 24px auto 80px;
    padding: 0 14px;
    font-family: 'Noto Sans JP', sans-serif;
}
.lh-wrap h1 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    color: #b38b59;
    margin-bottom: 6px;
}
/* 管理ナビ（共通） */
.admin-nav {
    display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap;
}
.admin-nav a {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 14px; border-radius: 6px; font-size: 0.82rem; font-weight: 500;
    text-decoration: none; background: #fef9f0; color: #b38b59;
    border: 1px solid #e8d5b7; transition: background 0.2s; white-space: nowrap;
}
.admin-nav a.active, .admin-nav a:hover { background: #b38b59; color: #fff; }

/* サマリー */
.lh-summary {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 20px;
}
.lh-card {
    background: #fff;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    box-shadow: 0 3px 12px rgba(0,0,0,0.07);
}
.lh-card .count { font-size: 2rem; font-weight: 700; line-height: 1; }
.lh-card .label { margin-top: 4px; font-size: 0.82rem; color: #777; }
.lh-card.success .count { color: #27ae60; }
.lh-card.failed  .count { color: #e74c3c; }

/* フィルタータブ */
.filter-tabs {
    display: flex; gap: 6px; margin-bottom: 16px; flex-wrap: wrap;
}
.filter-tab {
    padding: 6px 16px; border-radius: 20px; font-size: 0.82rem; font-weight: 500;
    text-decoration: none; border: 1px solid #e8d5b7; color: #b38b59;
    background: #fef9f0; transition: background 0.15s; white-space: nowrap;
}
.filter-tab.active, .filter-tab:hover { background: #b38b59; color: #fff; border-color: #b38b59; }

/* テーブル */
.lh-table-wrap {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.07);
    overflow: hidden;
}
.table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
table { width: 100%; border-collapse: collapse; font-size: 0.86rem; min-width: 600px; }
th {
    background: #fdf6ee; color: #b38b59; font-weight: 700;
    padding: 10px 14px; text-align: left; border-bottom: 2px solid #eedfc4; white-space: nowrap;
}
td {
    padding: 10px 14px; border-bottom: 1px solid #f0f0f0;
    vertical-align: middle; color: #333;
}
tr:last-child td { border-bottom: none; }
tr:hover td { background: #fffdf9; }

.badge {
    display: inline-block; padding: 3px 10px; border-radius: 20px;
    font-size: 0.73rem; font-weight: 700; white-space: nowrap;
}
.badge.success { background: #eafaf1; color: #27ae60; }
.badge.failed  { background: #fdf2f2; color: #e74c3c; }
.badge.admin-role { background: #f0edff; color: #6c3fd9; }
.badge.guest-role { background: #fef9f0; color: #b38b59; }

.text-muted { color: #aaa; font-size: 0.78rem; }
.ua-text { font-size: 0.75rem; color: #888; word-break: break-all; max-width: 260px; }
.empty-state { text-align: center; padding: 48px; color: #aaa; }

/* ページネーション */
.pagination-wrap {
    display: flex;
    justify-content: center;
    padding: 20px;
    gap: 4px;
    flex-wrap: wrap;
}
.pagination-wrap a,
.pagination-wrap span {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 36px; height: 36px; padding: 0 10px;
    border-radius: 6px; font-size: 0.85rem; text-decoration: none;
    border: 1px solid #e8d5b7; color: #b38b59; background: #fef9f0;
    transition: background 0.15s;
}
.pagination-wrap a:hover { background: #b38b59; color: #fff; }
.pagination-wrap .active-page { background: #b38b59; color: #fff; border-color: #b38b59; }
.pagination-wrap .disabled { color: #ddd; border-color: #eee; background: #fafafa; cursor: default; }

/* レスポンシブ */
@media (min-width: 768px) {
    .lh-wrap { margin-top: 40px; padding: 0 20px; }
    .lh-wrap h1 { font-size: 1.8rem; }
    .lh-summary { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 767px) {
    th, td { padding: 8px 10px; font-size: 0.78rem; }
    .ua-text { max-width: 160px; }
}
</style>
@endpush

@section('content')
<div class="lh-wrap">

    <h1>ログイン履歴</h1>

    {{-- サマリー --}}
    <div class="lh-summary">
        <div class="lh-card success">
            <div class="count">{{ $totalSuccess }}</div>
            <div class="label">ログイン成功</div>
        </div>
        <div class="lh-card failed">
            <div class="count">{{ $totalFailed }}</div>
            <div class="label">ログイン失敗</div>
        </div>
        <div class="lh-card" style="grid-column: span 2;">
            <div class="count" style="color:#b38b59;">{{ $totalSuccess + $totalFailed }}</div>
            <div class="label">合計ログイン試行</div>
        </div>
    </div>

    {{-- フィルタータブ --}}
    <div class="filter-tabs">
        <a href="{{ route('admin.login-history') }}"
           class="filter-tab {{ $filter === 'all' ? 'active' : '' }}">
            全て（{{ $totalSuccess + $totalFailed }}）
        </a>
        <a href="{{ route('admin.login-history', ['filter' => 'success']) }}"
           class="filter-tab {{ $filter === 'success' ? 'active' : '' }}">
            成功（{{ $totalSuccess }}）
        </a>
        <a href="{{ route('admin.login-history', ['filter' => 'failed']) }}"
           class="filter-tab {{ $filter === 'failed' ? 'active' : '' }}">
            失敗（{{ $totalFailed }}）
        </a>
    </div>

    {{-- テーブル --}}
    <div class="lh-table-wrap">
        @if ($histories->isEmpty())
        <div class="empty-state">ログイン履歴がありません</div>
        @else
        <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>日時</th>
                    <th>ユーザー名</th>
                    <th>ロール</th>
                    <th>状態</th>
                    <th>IPアドレス</th>
                    <th>ブラウザ / デバイス</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($histories as $h)
                <tr>
                    <td style="white-space:nowrap;">
                        {{ $h->created_at->format('Y/m/d') }}
                        <br><span class="text-muted">{{ $h->created_at->format('H:i:s') }}</span>
                    </td>
                    <td><strong>{{ $h->username }}</strong></td>
                    <td>
                        @if ($h->user)
                            <span class="badge {{ $h->user->role }}-role">
                                {{ $h->user->isAdmin() ? '管理者' : 'ゲスト' }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $h->status }}">
                            {{ $h->status === 'success' ? '成功' : '失敗' }}
                        </span>
                    </td>
                    <td>{{ $h->ip_address ?? '—' }}</td>
                    <td>
                        @if ($h->user_agent)
                        <span class="ua-text" title="{{ $h->user_agent }}">
                            {{ Str::limit($h->user_agent, 60) }}
                        </span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        {{-- ページネーション --}}
        @if ($histories->hasPages())
        <div class="pagination-wrap">
            {{-- 前へ --}}
            @if ($histories->onFirstPage())
                <span class="disabled">‹</span>
            @else
                <a href="{{ $histories->previousPageUrl() }}">‹</a>
            @endif

            {{-- ページ番号 --}}
            @foreach ($histories->getUrlRange(max(1, $histories->currentPage()-2), min($histories->lastPage(), $histories->currentPage()+2)) as $page => $url)
                @if ($page == $histories->currentPage())
                    <span class="active-page">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            {{-- 次へ --}}
            @if ($histories->hasMorePages())
                <a href="{{ $histories->nextPageUrl() }}">›</a>
            @else
                <span class="disabled">›</span>
            @endif
        </div>
        @endif
        @endif
    </div>

</div>
@endsection
