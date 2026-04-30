@extends('layouts.app')
@section('title', 'ログイン履歴 | Admin')

@push('styles')
<style>
/* ── ラッパー ── */
.lh-wrap {
    max-width: 1100px;
    margin: 24px auto 80px;
    padding: 0 14px;
    font-family: 'Noto Sans JP', sans-serif;
}
.lh-wrap h1 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    color: #b38b59;
    margin-bottom: 20px;
}

/* ── サマリー ── */
.lh-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
.lh-card {
    background: #fff;
    border-radius: 12px;
    padding: 16px 12px;
    text-align: center;
    box-shadow: 0 3px 12px rgba(0,0,0,0.07);
}
.lh-card .num  { font-size: 1.9rem; font-weight: 700; line-height: 1; }
.lh-card .lbl  { margin-top: 4px; font-size: 0.78rem; color: #888; }
.lh-card.success .num { color: #27ae60; }
.lh-card.failed  .num { color: #e74c3c; }
.lh-card.total   .num { color: #b38b59; }

/* ── 検索パネル ── */
.search-panel {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e8d5b7;
    padding: 20px 20px 16px;
    margin-bottom: 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.search-panel-title {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #b38b59;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* 検索フィールドグリッド */
.search-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px 20px;
    margin-bottom: 14px;
}
.search-field label {
    display: block;
    font-size: 0.74rem;
    font-weight: 600;
    color: #7a6a5a;
    margin-bottom: 6px;
    letter-spacing: 0.3px;
}
.search-field input[type="text"],
.search-field input[type="search"] {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #e0d0bc;
    border-radius: 7px;
    font-size: 0.9rem;
    font-family: 'Noto Sans JP', sans-serif;
    color: #3d2f25;
    background: #fffdf9;
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.search-field input:focus {
    border-color: #b38b59;
    outline: none;
    box-shadow: 0 0 0 3px rgba(179,139,89,0.12);
}

/* 日付範囲 */
.date-range {
    display: flex;
    align-items: center;
    gap: 8px;
}
.date-range input[type="date"] {
    flex: 1;
    padding: 9px 10px;
    border: 1px solid #e0d0bc;
    border-radius: 7px;
    font-size: 0.88rem;
    font-family: 'Noto Sans JP', sans-serif;
    color: #3d2f25;
    background: #fffdf9;
    box-sizing: border-box;
    transition: border-color 0.2s;
    min-width: 0;
}
.date-range input[type="date"]:focus {
    border-color: #b38b59;
    outline: none;
    box-shadow: 0 0 0 3px rgba(179,139,89,0.12);
}
.date-sep { font-size: 0.8rem; color: #b0a090; flex-shrink: 0; }

/* 期間ショートカット */
.date-shortcuts {
    display: flex;
    gap: 5px;
    margin-top: 7px;
    flex-wrap: wrap;
}
.date-shortcut {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 500;
    border: 1px solid #e0d0bc;
    color: #9b8573;
    background: #fffdf9;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s;
    white-space: nowrap;
}
.date-shortcut:hover { background: #fef9f0; border-color: #b38b59; color: #b38b59; }

/* トグルボタングループ（状態・お立場） */
.toggle-group {
    display: flex;
    gap: 0;
    border: 1px solid #e0d0bc;
    border-radius: 7px;
    overflow: hidden;
}
.toggle-group input[type="radio"] { display: none; }
.toggle-group label {
    flex: 1;
    text-align: center;
    padding: 9px 6px;
    font-size: 0.83rem;
    font-weight: 500;
    color: #9b8573;
    background: #fffdf9;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
    border: none;
    white-space: nowrap;
    line-height: 1.2;
}
.toggle-group label:not(:last-child) { border-right: 1px solid #e0d0bc; }
.toggle-group input:checked + label { background: #b38b59; color: #fff; }

/* 検索ボタンエリア */
.search-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    padding-top: 4px;
    border-top: 1px solid #f0ebe3;
    flex-wrap: wrap;
}
.btn-search {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 24px;
    background: #b38b59;
    color: #fff;
    border: none;
    border-radius: 7px;
    font-size: 0.9rem;
    font-family: 'Noto Sans JP', sans-serif;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
    min-height: 42px;
}
.btn-search:hover { background: #9a7447; }
.btn-reset {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 10px 16px;
    background: transparent;
    border: 1px solid #e0d0bc;
    border-radius: 7px;
    font-size: 0.86rem;
    font-family: 'Noto Sans JP', sans-serif;
    color: #9b8573;
    text-decoration: none;
    cursor: pointer;
    transition: border-color 0.2s, color 0.2s;
    min-height: 42px;
}
.btn-reset:hover { border-color: #b38b59; color: #b38b59; }

/* アクティブフィルターバッジ */
.active-filters {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    margin-bottom: 14px;
}
.active-filters-label {
    font-size: 0.76rem;
    color: #9b8573;
    font-weight: 500;
}
.filter-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    background: #fef9f0;
    border: 1px solid #e8d5b7;
    border-radius: 20px;
    font-size: 0.76rem;
    color: #b38b59;
    font-weight: 500;
}

/* ── テーブル ── */
.lh-table-wrap {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.07);
    overflow: hidden;
}
.lh-table-head {
    padding: 14px 20px;
    font-size: 0.8rem;
    color: #999;
    border-bottom: 1px solid #f5f0ea;
}
.lh-table-head strong { color: #3d2f25; }
.table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
table { width: 100%; border-collapse: collapse; font-size: 0.86rem; min-width: 620px; }
th {
    background: #fdf6ee; color: #b38b59; font-weight: 700;
    padding: 10px 14px; text-align: left;
    border-bottom: 2px solid #eedfc4; white-space: nowrap;
}
td { padding: 10px 14px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; color: #333; }
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
.badge.groom { background: #e8f4ff; color: #2980b9; }
.badge.bride { background: #ffe8f0; color: #c0392b; }

.text-muted { color: #aaa; font-size: 0.78rem; }
.ua-text { font-size: 0.74rem; color: #888; word-break: break-all; max-width: 240px; }
.empty-state { text-align: center; padding: 56px; color: #aaa; font-size: 0.9rem; }
.empty-icon { font-size: 2rem; margin-bottom: 10px; opacity: 0.35; }

/* ページネーション */
.pagination-wrap {
    display: flex; justify-content: center;
    padding: 20px; gap: 4px; flex-wrap: wrap;
}
.pagination-wrap a, .pagination-wrap span {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 36px; height: 36px; padding: 0 10px;
    border-radius: 6px; font-size: 0.85rem; text-decoration: none;
    border: 1px solid #e8d5b7; color: #b38b59; background: #fef9f0;
    transition: background 0.15s;
}
.pagination-wrap a:hover { background: #b38b59; color: #fff; }
.pagination-wrap .pg-active { background: #b38b59; color: #fff; border-color: #b38b59; }
.pagination-wrap .pg-disabled { color: #ddd; border-color: #eee; background: #fafafa; }

/* ── レスポンシブ ── */
@media (min-width: 768px) {
    .lh-wrap { margin-top: 40px; padding: 0 20px; }
    .lh-wrap h1 { font-size: 1.8rem; }
    .search-grid { grid-template-columns: 2fr 2fr 1fr 1fr; }
}
@media (max-width: 767px) {
    .lh-summary { grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .lh-card .num { font-size: 1.5rem; }
    .search-grid { grid-template-columns: 1fr; }
    .search-panel { padding: 16px 14px 12px; }
    th, td { padding: 8px 10px; font-size: 0.78rem; }
    .ua-text { max-width: 140px; }
    .search-actions { flex-direction: column; align-items: stretch; }
    .btn-search, .btn-reset { justify-content: center; }
}
</style>
@endpush

@section('content')
<div class="lh-wrap">

    <h1><i class="fa-solid fa-clock-rotate-left" style="font-size:1.2rem;opacity:0.7;margin-right:6px;"></i>ログイン履歴</h1>

    {{-- サマリー --}}
    <div class="lh-summary">
        <div class="lh-card success">
            <div class="num">{{ $totalSuccess }}</div>
            <div class="lbl">ログイン成功</div>
        </div>
        <div class="lh-card failed">
            <div class="num">{{ $totalFailed }}</div>
            <div class="lbl">ログイン失敗</div>
        </div>
        <div class="lh-card total">
            <div class="num">{{ $totalSuccess + $totalFailed }}</div>
            <div class="lbl">合計試行回数</div>
        </div>
    </div>

    {{-- 検索パネル --}}
    <div class="search-panel">
        <div class="search-panel-title">
            <i class="fa-solid fa-magnifying-glass"></i> 絞り込み検索
        </div>

        <form method="GET" action="{{ route('admin.login-history') }}" id="searchForm">

            <div class="search-grid">

                {{-- ユーザー名 --}}
                <div class="search-field">
                    <label>ユーザー名</label>
                    <input type="search" name="q" value="{{ $q }}"
                           placeholder="例：yamada_taro"
                           autocomplete="off">
                </div>

                {{-- 期間 --}}
                <div class="search-field">
                    <label>期間</label>
                    <div class="date-range">
                        <input type="date" name="from" id="fromDate" value="{{ $from }}" max="{{ date('Y-m-d') }}">
                        <span class="date-sep">〜</span>
                        <input type="date" name="to"   id="toDate"   value="{{ $to }}"   max="{{ date('Y-m-d') }}">
                    </div>
                    <div class="date-shortcuts">
                        <button type="button" class="date-shortcut" data-range="today">今日</button>
                        <button type="button" class="date-shortcut" data-range="yesterday">昨日</button>
                        <button type="button" class="date-shortcut" data-range="week">過去7日</button>
                        <button type="button" class="date-shortcut" data-range="month">過去30日</button>
                    </div>
                </div>

                {{-- 状態 --}}
                <div class="search-field">
                    <label>ログイン状態</label>
                    <div class="toggle-group">
                        <input type="radio" name="status" id="st_all"     value="all"     {{ $status === 'all'     ? 'checked' : '' }}>
                        <label for="st_all">全て</label>
                        <input type="radio" name="status" id="st_success" value="success" {{ $status === 'success' ? 'checked' : '' }}>
                        <label for="st_success">成功</label>
                        <input type="radio" name="status" id="st_failed"  value="failed"  {{ $status === 'failed'  ? 'checked' : '' }}>
                        <label for="st_failed">失敗</label>
                    </div>
                </div>

                {{-- お立場 --}}
                <div class="search-field">
                    <label>お立場</label>
                    <div class="toggle-group">
                        <input type="radio" name="side" id="sd_all"   value="all"   {{ $side === 'all'   ? 'checked' : '' }}>
                        <label for="sd_all">全て</label>
                        <input type="radio" name="side" id="sd_groom" value="groom" {{ $side === 'groom' ? 'checked' : '' }}>
                        <label for="sd_groom">新郎側</label>
                        <input type="radio" name="side" id="sd_bride" value="bride" {{ $side === 'bride' ? 'checked' : '' }}>
                        <label for="sd_bride">新婦側</label>
                    </div>
                </div>

            </div>{{-- /.search-grid --}}

            <div class="search-actions">
                <button type="submit" class="btn-search">
                    <i class="fa-solid fa-magnifying-glass"></i> 検索する
                </button>
                @if ($hasFilter)
                <a href="{{ route('admin.login-history') }}" class="btn-reset">
                    <i class="fa-solid fa-rotate-left"></i> リセット
                </a>
                @endif
            </div>

        </form>
    </div>

    {{-- アクティブフィルターバッジ --}}
    @if ($hasFilter)
    <div class="active-filters">
        <span class="active-filters-label"><i class="fa-solid fa-filter"></i> 絞り込み中：</span>
        @if ($q !== '')
            <span class="filter-badge"><i class="fa-solid fa-user"></i> 「{{ $q }}」</span>
        @endif
        @if ($from !== '' || $to !== '')
            <span class="filter-badge">
                <i class="fa-solid fa-calendar"></i>
                {{ $from ?: '…' }} 〜 {{ $to ?: '…' }}
            </span>
        @endif
        @if ($status !== 'all')
            <span class="filter-badge">
                <i class="fa-solid fa-circle-dot"></i>
                {{ $status === 'success' ? 'ログイン成功' : 'ログイン失敗' }}
            </span>
        @endif
        @if ($side !== 'all')
            <span class="filter-badge">
                <i class="fa-solid fa-users"></i>
                {{ $side === 'groom' ? '新郎側' : '新婦側' }}
            </span>
        @endif
    </div>
    @endif

    {{-- 結果テーブル --}}
    <div class="lh-table-wrap">
        <div class="lh-table-head">
            @if ($hasFilter)
                絞り込み結果：<strong>{{ $histories->total() }}件</strong>
            @else
                全 <strong>{{ $histories->total() }}件</strong>
            @endif
            （{{ $histories->firstItem() ?? 0 }}〜{{ $histories->lastItem() ?? 0 }}件 表示中）
        </div>

        @if ($histories->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
            該当するログイン履歴が見つかりませんでした
        </div>
        @else
        <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>日時</th>
                    <th>ユーザー名</th>
                    <th>状態</th>
                    <th>ロール / お立場</th>
                    <th>IPアドレス</th>
                    <th>ブラウザ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($histories as $h)
                <tr>
                    <td style="white-space:nowrap;">
                        <span style="font-size:0.82rem;">{{ $h->created_at->format('Y/m/d') }}</span>
                        <br><strong style="font-size:0.88rem;">{{ $h->created_at->format('H:i:s') }}</strong>
                    </td>
                    <td><strong>{{ $h->username }}</strong></td>
                    <td>
                        <span class="badge {{ $h->status }}">
                            {{ $h->status === 'success' ? '✓ 成功' : '✕ 失敗' }}
                        </span>
                    </td>
                    <td>
                        @if ($h->user)
                            <span class="badge {{ $h->user->role }}-role">
                                {{ $h->user->isAdmin() ? '管理者' : 'ゲスト' }}
                            </span>
                            @if ($h->user->guestProfile?->guest_side)
                            <br><span class="badge {{ $h->user->guestProfile->guest_side }}" style="margin-top:4px;">
                                {{ $h->user->guestProfile->guest_side === 'groom' ? '新郎側' : '新婦側' }}
                            </span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-size:0.84rem;">{{ $h->ip_address ?? '—' }}</span>
                    </td>
                    <td>
                        @if ($h->user_agent)
                        <span class="ua-text" title="{{ $h->user_agent }}">
                            {{ Str::limit($h->user_agent, 55) }}
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
            @if ($histories->onFirstPage())
                <span class="pg-disabled">‹</span>
            @else
                <a href="{{ $histories->previousPageUrl() }}">‹</a>
            @endif

            @foreach ($histories->getUrlRange(max(1, $histories->currentPage()-2), min($histories->lastPage(), $histories->currentPage()+2)) as $page => $url)
                @if ($page == $histories->currentPage())
                    <span class="pg-active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            @if ($histories->hasMorePages())
                <a href="{{ $histories->nextPageUrl() }}">›</a>
            @else
                <span class="pg-disabled">›</span>
            @endif
        </div>
        @endif
        @endif
    </div>

</div>

<script>
// 期間ショートカットボタン
(function() {
    const from = document.getElementById('fromDate');
    const to   = document.getElementById('toDate');
    const fmt  = d => d.toISOString().slice(0, 10);

    document.querySelectorAll('.date-shortcut').forEach(btn => {
        btn.addEventListener('click', () => {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            switch (btn.dataset.range) {
                case 'today':
                    from.value = fmt(today);
                    to.value   = fmt(today);
                    break;
                case 'yesterday': {
                    const y = new Date(today);
                    y.setDate(y.getDate() - 1);
                    from.value = fmt(y);
                    to.value   = fmt(y);
                    break;
                }
                case 'week': {
                    const w = new Date(today);
                    w.setDate(w.getDate() - 6);
                    from.value = fmt(w);
                    to.value   = fmt(today);
                    break;
                }
                case 'month': {
                    const m = new Date(today);
                    m.setDate(m.getDate() - 29);
                    from.value = fmt(m);
                    to.value   = fmt(today);
                    break;
                }
            }
        });
    });
})();
</script>
@endsection
