@extends('layouts.app')
@section('title', 'ゲスト一覧 | Admin')

@push('styles')
<style>
/* ── 列の表示制御 ──
   サイドバー分で実際の表示幅が縮むため、ビューポート幅ではなく
   .guest-table-wrap の実際の幅(コンテナクエリ)で切り替える */
@container (max-width: 950px) {
    .col-side, .col-date, .col-email { display: none; }
}

/* ── 返答率バー ── */
.progress-wrap { margin: 0 0 24px; background: #fff; border-radius: 14px; padding: 18px 22px; box-shadow: 0 4px 14px rgba(0,0,0,0.07); }
.progress-label { display: flex; justify-content: space-between; font-size: 0.8rem; color: #777; margin-bottom: 8px; }
.progress-bar { height: 8px; background: #f0ebe3; border-radius: 4px; overflow: hidden; }
.progress-bar__fill { height: 100%; background: linear-gradient(90deg, #27ae60, #52d68a); border-radius: 4px; transition: width 0.8s ease; }

/* ── サマリー ── */
.summary-row { grid-template-columns: repeat(4, 1fr); }
.summary-card .sub  { font-size: 0.75rem; color: #b0a090; margin-top: 3px; }
.summary-card .icon-top { font-size: 1.2rem; margin-bottom: 8px; opacity: 0.55; }
@media (max-width: 640px) { .summary-row { grid-template-columns: repeat(2, 1fr); } }

/* ── 検索・フィルターバー ── */
.list-controls {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 14px;
}
.search-row {
    display: flex;
    gap: 8px;
    align-items: center;
}
.search-input-wrap {
    position: relative;
    flex: 1;
    max-width: 340px;
}
.search-input-wrap i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #b38b59;
    font-size: 0.85rem;
    pointer-events: none;
}
.search-input {
    width: 100%;
    padding: 9px 12px 9px 34px;
    border: 1px solid #e0d0bc;
    border-radius: 8px;
    font-size: 0.88rem;
    font-family: 'Noto Sans JP', sans-serif;
    color: #3d2f25;
    background: #fffdf9;
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.search-input:focus {
    outline: none;
    border-color: #b38b59;
    box-shadow: 0 0 0 3px rgba(179,139,89,0.12);
}
.clear-search {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #bbb;
    cursor: pointer;
    font-size: 0.85rem;
    padding: 2px;
    line-height: 1;
    display: none;
}
.clear-search.visible { display: block; }
.clear-search:hover { color: #888; }

.result-count {
    font-size: 0.8rem;
    color: #999;
    white-space: nowrap;
    align-self: center;
}
.result-count strong { color: #3d2f25; }

/* フィルタータブ行 */
.filter-rows { display: flex; flex-direction: column; gap: 6px; }
.filter-row { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.filter-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: #b38b59;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    min-width: 56px;
    flex-shrink: 0;
}
.filter-btn {
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 500;
    border: 1px solid #e8d5b7;
    color: #b38b59;
    background: #fef9f0;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
    white-space: nowrap;
    font-family: 'Noto Sans JP', sans-serif;
}
.filter-btn:hover { background: #f5edd8; border-color: #b38b59; }
.filter-btn.active { background: #b38b59; color: #fff; border-color: #b38b59; }

@media (max-width: 767px) {
    .search-input-wrap { max-width: 100%; }
    .search-row { flex-wrap: wrap; }
    .result-count { width: 100%; }
}

/* ── ソート可能ヘッダー ── */
th.sortable {
    cursor: pointer;
    user-select: none;
    white-space: nowrap;
}
th.sortable:hover { background: #f5ede0; }
.sort-icon {
    display: inline-block;
    margin-left: 4px;
    font-size: 0.7rem;
    color: #ccc;
    vertical-align: middle;
}
th.sort-asc  .sort-icon { color: #b38b59; }
th.sort-desc .sort-icon { color: #b38b59; }

/* ── 空状態（フィルター結果なし）── */
.no-results {
    text-align: center;
    padding: 40px 20px;
    color: #aaa;
    display: none;
}
.no-results.visible { display: block; }

/* ── バッジ ── */
.badge-login-ok  { background: #e8f5fe; color: #1a6fa8; }
.badge-login-no  { background: #f5f5f5; color: #aaa; }
.badge-email-ok  { background: #eafaf1; color: #1e8449; }
.badge-email-no  { background: #fdf2f2; color: #c0392b; }

.allergy-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 600; }
.allergy-yes { background: #fff3cd; color: #856404; }
.allergy-no  { background: #f0f0f0; color: #888; }

/* ── CSV出力 ── */
.btn-csv {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 16px; border-radius: 8px; font-size: 0.82rem; font-weight: 500;
    background: #27ae60; color: #fff; text-decoration: none; white-space: nowrap;
    border: none; transition: background 0.2s; flex-shrink: 0;
}
.btn-csv:hover { background: #1e8449; color: #fff; }

@container (max-width: 950px) {
    .col-allergy { display: none; }
}

/* ── ログイン履歴展開行 ── */
.lh-row { display: none; background: #fafaf7; }
.lh-row.open { display: table-row; }
.lh-inner { padding: 10px 16px; }
.lh-inner-title { font-size: 0.74rem; font-weight: 700; color: #b38b59; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px; }
.lh-mini { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
.lh-mini th { padding: 5px 10px; background: #f5f0ea; color: #9b8573; font-weight: 700; text-align: left; white-space: nowrap; }
.lh-mini td { padding: 5px 10px; border-bottom: 1px solid #f0ece6; color: #555; white-space: nowrap; }
.lh-mini tr:last-child td { border-bottom: none; }
.lh-empty { color: #bbb; font-size: 0.82rem; padding: 8px 0; }

/* 履歴トグルボタン */
.btn-lh { background: #f0edff; color: #6c3fd9; border: 1px solid #d9cef5; font-size: 0.75rem; }
.btn-lh:hover { background: #e4deff; border-color: #6c3fd9; color: #5530c8; }
@media (max-width: 767px) { .btn-lh-label { display: none; } }

/* ── 行クリックで回答内容を表示 ── */
#guestTbody tr[data-id] { cursor: pointer; }

/* ── 回答内容 詳細モーダル ── */
.detail-modal-overlay {
    position: fixed; inset: 0; background: rgba(30,18,6,0.45);
    z-index: 500; display: flex; align-items: center; justify-content: center;
    opacity: 0; pointer-events: none; transition: opacity 0.2s;
    padding: 16px;
}
.detail-modal-overlay.open { opacity: 1; pointer-events: all; }

.detail-modal {
    background: #fff; border-radius: 16px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.28);
    width: 100%; max-width: 560px; max-height: 90vh;
    overflow-y: auto; padding: 28px;
    transform: translateY(16px); transition: transform 0.2s;
}
.detail-modal-overlay.open .detail-modal { transform: translateY(0); }

.dm-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 12px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #f0ebe3;
}
.dm-title { font-family: 'Playfair Display', serif; font-size: 1.3rem; color: #3d2f25; font-weight: 400; }
.dm-subtitle { font-size: 0.8rem; color: #9b8573; margin-top: 2px; }
.dm-close {
    background: none; border: none; font-size: 1.2rem; cursor: pointer;
    color: #9b8573; padding: 4px; line-height: 1; flex-shrink: 0;
    transition: color 0.15s;
}
.dm-close:hover { color: #3d2f25; }

.dm-section { margin-bottom: 18px; }
.dm-section-title {
    font-size: 0.7rem; font-weight: 700; color: #b38b59;
    letter-spacing: 2px; text-transform: uppercase; margin-bottom: 10px;
}
.dm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.dm-item dt { font-size: 0.72rem; color: #b0a090; margin-bottom: 2px; }
.dm-item dd { font-size: 0.9rem; color: #3d2f25; margin: 0; line-height: 1.5; }
.dm-full { grid-column: 1 / -1; }
.dm-item dd.dm-pre { white-space: pre-wrap; background: #fafaf8; border-radius: 6px; padding: 8px 10px; font-size: 0.86rem; }

@media (max-width: 767px) {
    .detail-modal { padding: 20px 16px; }
    .dm-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="admin-wrap">
    <h1>ゲスト一覧</h1>

    @if ($needsEmailRegistration)
    <div style="margin:0 0 16px;padding:16px 18px;background:#fff7ef;border:1px solid #e8c8a5;border-radius:14px;color:#6b4b2d;display:flex;gap:12px;justify-content:space-between;align-items:center;flex-wrap:wrap;">
        <div>
            <div style="font-weight:700;">メールアドレスを登録してください</div>
            <div style="font-size:0.86rem;margin-top:4px;">パスワード再設定や連絡に使います。プロフィール画面から登録できます。</div>
        </div>
        <a href="{{ route('profile.edit') }}" class="btn-primary" style="text-decoration:none;">プロフィールを開く</a>
    </div>
    @endif

    {{-- 返答率プログレスバー --}}
    <div class="progress-wrap">
        <div class="progress-label">
            <span>返答率 <strong style="color:#3d2f25;">{{ $summary['response_rate'] }}%</strong></span>
            <span>{{ $summary['total'] - $summary['pending'] }} / {{ $summary['total'] }}名 回答済み</span>
        </div>
        <div class="progress-bar">
            <div class="progress-bar__fill" style="width: {{ $summary['response_rate'] }}%"></div>
        </div>
    </div>

    {{-- サマリー 4カード --}}
    <div class="summary-cards summary-row" style="margin-bottom:14px;">
        <div class="summary-card total">
            <div class="icon-top"><i class="fa-solid fa-users"></i></div>
            <div class="count">{{ $summary['total'] }}</div>
            <div class="label">ゲスト合計</div>
        </div>
        <div class="summary-card attending">
            <div class="icon-top"><i class="fa-solid fa-circle-check"></i></div>
            <div class="count">{{ $summary['attending'] }}</div>
            <div class="label">出席</div>
            <div class="sub">当日 {{ $summary['people_count'] }}名参加予定</div>
        </div>
        <div class="summary-card declining">
            <div class="icon-top"><i class="fa-solid fa-circle-xmark"></i></div>
            <div class="count">{{ $summary['declining'] }}</div>
            <div class="label">欠席</div>
        </div>
        <div class="summary-card pending">
            <div class="icon-top"><i class="fa-regular fa-clock"></i></div>
            <div class="count">{{ $summary['pending'] }}</div>
            <div class="label">未回答</div>
            @if ($summary['allergy_count'] > 0)
            <div class="sub" style="color:#e67e22;">アレルギー {{ $summary['allergy_count'] }}名</div>
            @endif
        </div>
    </div>

    {{-- ゲスト一覧テーブル --}}
    <div class="guest-table-wrap">
        <h2>ゲスト一覧</h2>

        @if ($guests->isEmpty())
        <div class="empty-state">
            <div class="empty-state__icon">👥</div>
            <p class="empty-state__title">まだゲストが登録されていません</p>
            <p class="empty-state__desc">ゲストが登録ページからアカウントを作成すると、ここに表示されます。</p>
        </div>
        @else

        {{-- 検索・フィルターバー --}}
        <div class="list-controls" style="padding: 0 0 12px;">
            <div class="search-row">
                <div class="search-input-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" id="guestSearch" class="search-input"
                           placeholder="名前・フリガナ・ユーザー名で検索…"
                           autocomplete="off">
                    <button type="button" id="clearSearch" class="clear-search" aria-label="クリア">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <span class="result-count" id="resultCount">
                    <strong>{{ $guests->count() }}</strong> 名表示中
                </span>
                <a href="{{ route('admin.rsvp.export') }}" class="btn-csv">
                    <i class="fa-solid fa-file-csv"></i> CSV出力
                </a>
            </div>

            <div class="filter-rows">
                {{-- 出欠フィルター --}}
                <div class="filter-row">
                    <span class="filter-label">出欠</span>
                    <button class="filter-btn active" data-filter="rsvp" data-value="all">すべて</button>
                    <button class="filter-btn" data-filter="rsvp" data-value="attending">出席</button>
                    <button class="filter-btn" data-filter="rsvp" data-value="declining">欠席</button>
                    <button class="filter-btn" data-filter="rsvp" data-value="pending">未回答</button>
                </div>
                {{-- メールフィルター --}}
                <div class="filter-row">
                    <span class="filter-label">メール</span>
                    <button class="filter-btn active" data-filter="email" data-value="all">すべて</button>
                    <button class="filter-btn" data-filter="email" data-value="1">登録済</button>
                    <button class="filter-btn" data-filter="email" data-value="0">未登録</button>
                </div>
                {{-- ログインフィルター --}}
                <div class="filter-row">
                    <span class="filter-label">ログイン</span>
                    <button class="filter-btn active" data-filter="login" data-value="all">すべて</button>
                    <button class="filter-btn" data-filter="login" data-value="1">ログイン済</button>
                    <button class="filter-btn" data-filter="login" data-value="0">未ログイン</button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
        <table id="guestTable">
            <thead>
                <tr>
                    <th class="sortable" data-col="name">
                        氏名 <span class="sort-icon"><i class="fa-solid fa-sort"></i></span>
                    </th>
                    <th class="sortable col-side" data-col="side">
                        お立場 <span class="sort-icon"><i class="fa-solid fa-sort"></i></span>
                    </th>
                    <th class="sortable" data-col="rsvp">
                        出欠 <span class="sort-icon"><i class="fa-solid fa-sort"></i></span>
                    </th>
                    <th class="sortable" data-col="count">
                        人数 <span class="sort-icon"><i class="fa-solid fa-sort"></i></span>
                    </th>
                    <th class="col-allergy">アレルギー</th>
                    <th class="sortable col-email" data-col="email">
                        メール <span class="sort-icon"><i class="fa-solid fa-sort"></i></span>
                    </th>
                    <th class="sortable" data-col="login">
                        ログイン <span class="sort-icon"><i class="fa-solid fa-sort"></i></span>
                    </th>
                    <th class="sortable col-date" data-col="responded">
                        回答日時 <span class="sort-icon"><i class="fa-solid fa-sort"></i></span>
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="guestTbody">
                @foreach ($guests as $guest)
                @php
                    $p        = $guest->guestProfile;
                    $logins   = $loginHistoryMap->get($guest->id, collect());
                    $lastOk   = $logins->where('status', 'success')->first();
                    $hasLogin = $lastOk !== null;
                    $fullName = $p ? trim(($p->last_name ?? '') . ' ' . ($p->first_name ?? '')) : '';
                    $furigana = $p?->furigana() ?? '';
                    $rsvp     = $p?->participation ?? 'pending';
                @endphp
                <tr data-id="{{ $guest->id }}"
                    data-name="{{ strtolower($fullName ?: $guest->username) }}"
                    data-username="{{ strtolower($guest->username) }}"
                    data-furigana="{{ $furigana }}"
                    data-side="{{ $p?->guest_side ?? '' }}"
                    data-rsvp="{{ $rsvp }}"
                    data-count="{{ $p?->isAttending() ? ($p->attending_count ?? 0) : 0 }}"
                    data-email="{{ $guest->email ? '1' : '0' }}"
                    data-login="{{ $hasLogin ? $lastOk->created_at->timestamp : '0' }}"
                    data-responded="{{ $p?->responded_at?->timestamp ?? '0' }}"
                    data-display-name="{{ $fullName ?: $guest->username }}"
                    data-status-label="{{ ['attending'=>'出席','declining'=>'欠席','pending'=>'未回答'][$rsvp] }}"
                    data-children="{{ $p?->children_count ?? 0 }}"
                    data-side-label="{{ $p?->guestSideLabel() ?? '—' }}"
                    data-rel="{{ $p?->relationshipLabel() ?? '—' }}"
                    data-rel-detail="{{ $p?->relationship_detail ?? '' }}"
                    data-allergy="{{ $p?->has_allergy ? 'あり' : ($p ? 'なし' : '') }}"
                    data-allergy-notes="{{ $p?->allergy_notes ?? '' }}"
                    data-phone="{{ $p?->phone ?? '' }}"
                    data-postal="{{ $p?->postal_code ?? '' }}"
                    data-address="{{ $p?->address ?? '' }}"
                    data-notes="{{ $p?->notes ?? '' }}"
                    onclick="openDetail(this)">

                    {{-- 氏名 --}}
                    <td>
                        @if ($fullName)
                            <strong>{{ $fullName }}</strong>
                            @if ($furigana)
                            <br><span class="text-muted">{{ $furigana }}</span>
                            @endif
                        @else
                            <span class="text-muted">{{ $guest->username }}</span>
                        @endif
                        <br><span class="text-muted" style="font-size:0.76rem;">{{ $guest->username }}</span>
                    </td>

                    {{-- お立場 --}}
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

                    {{-- 出欠 --}}
                    <td>
                        @if ($rsvp === 'attending')
                            <span class="badge attending">出席</span>
                        @elseif ($rsvp === 'declining')
                            <span class="badge declining">欠席</span>
                        @else
                            <span class="badge pending">未回答</span>
                        @endif
                    </td>

                    {{-- 人数 --}}
                    <td>
                        @if ($p?->isAttending())
                            {{ $p->attending_count }}名
                            @if ($p->children_count > 0)
                            <br><span class="text-muted" style="font-size:0.76rem;">子{{ $p->children_count }}名</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- アレルギー --}}
                    <td class="col-allergy">
                        @if ($p?->has_allergy)
                            <span class="allergy-badge allergy-yes">あり</span>
                        @elseif ($p)
                            <span class="allergy-badge allergy-no">なし</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- メール --}}
                    <td class="col-email">
                        @if ($guest->email)
                            <span class="badge badge-email-ok">
                                <i class="fa-solid fa-envelope-circle-check" style="font-size:0.7rem;"></i> 登録済
                            </span>
                            @if (!$guest->hasVerifiedEmail())
                            <br><span class="text-muted" style="font-size:0.72rem;">未認証</span>
                            @endif
                        @else
                            <span class="badge badge-email-no">
                                <i class="fa-solid fa-envelope" style="font-size:0.7rem;opacity:0.6;"></i> 未登録
                            </span>
                        @endif
                    </td>

                    {{-- ログイン状態 --}}
                    <td>
                        @if ($hasLogin)
                            <span class="badge badge-login-ok">
                                <i class="fa-solid fa-right-to-bracket" style="font-size:0.7rem;"></i> 済
                            </span>
                            <br><span class="text-muted" style="font-size:0.74rem;">{{ $lastOk->created_at->format('m/d H:i') }}</span>
                        @else
                            <span class="badge badge-login-no">未</span>
                        @endif
                    </td>

                    {{-- 回答日時 --}}
                    <td class="col-date">
                        @if ($p?->responded_at)
                            {{ $p->responded_at->format('m/d') }}<br>
                            <span class="text-muted" style="font-size:0.76rem;">{{ $p->responded_at->format('H:i') }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- 履歴ボタン --}}
                    <td style="text-align:right;white-space:nowrap;">
                        <button type="button"
                            class="btn-sm btn-lh"
                            onclick="event.stopPropagation(); toggleLh({{ $guest->id }}, this)"
                            aria-expanded="false"
                            title="{{ $guest->username }} のログイン履歴">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <span class="btn-lh-label">履歴</span>
                        </button>
                    </td>
                </tr>

                {{-- ログイン履歴 展開行 --}}
                <tr class="lh-row" id="lh-row-{{ $guest->id }}">
                    <td colspan="9">
                        <div class="lh-inner">
                            <div class="lh-inner-title">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                                {{ $guest->username }} — 最近のログイン履歴（最新5件）
                            </div>
                            @if ($logins->isEmpty())
                                <p class="lh-empty">ログイン記録がありません</p>
                            @else
                            <table class="lh-mini">
                                <thead>
                                    <tr>
                                        <th>日時</th>
                                        <th>状態</th>
                                        <th>IPアドレス</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($logins as $lh)
                                    <tr>
                                        <td>{{ $lh->created_at->format('Y/m/d H:i') }}</td>
                                        <td>
                                            <span class="badge {{ $lh->status === 'success' ? 'success' : 'declining' }}" style="font-size:0.7rem;">
                                                {{ $lh->status === 'success' ? '✓ 成功' : '✕ 失敗' }}
                                            </span>
                                        </td>
                                        <td>{{ $lh->ip_address ?? '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        {{-- フィルター結果なし --}}
        <div class="no-results" id="noResults">
            <div style="font-size:2rem;margin-bottom:8px;">🔍</div>
            <p style="font-weight:600;color:#888;">該当するゲストが見つかりません</p>
            <p style="font-size:0.84rem;color:#aaa;margin-top:4px;">検索条件やフィルターを変更してみてください</p>
        </div>

        @endif
    </div>
</div>

{{-- 回答内容 詳細モーダル --}}
<div class="detail-modal-overlay" id="detailOverlay" onclick="if(event.target===this)closeDetail()">
    <div class="detail-modal" id="detailModal">
        <div class="dm-header">
            <div>
                <p class="dm-title" id="dmName">—</p>
                <p class="dm-subtitle" id="dmUsername">—</p>
            </div>
            <button class="dm-close" onclick="closeDetail()" aria-label="閉じる">✕</button>
        </div>

        <div class="dm-section">
            <p class="dm-section-title">出欠・参加情報</p>
            <div class="dm-grid">
                <dl class="dm-item">
                    <dt>出欠</dt>
                    <dd id="dmStatus">—</dd>
                </dl>
                <dl class="dm-item">
                    <dt>出席人数（合計）</dt>
                    <dd id="dmCount">—</dd>
                </dl>
                <dl class="dm-item">
                    <dt>うちお子様</dt>
                    <dd id="dmChildren">—</dd>
                </dl>
                <dl class="dm-item">
                    <dt>回答日時</dt>
                    <dd id="dmResponded">—</dd>
                </dl>
            </div>
        </div>

        <div class="dm-section">
            <p class="dm-section-title">お立場・ご関係</p>
            <div class="dm-grid">
                <dl class="dm-item">
                    <dt>お立場</dt>
                    <dd id="dmSide">—</dd>
                </dl>
                <dl class="dm-item">
                    <dt>ご関係</dt>
                    <dd id="dmRel">—</dd>
                </dl>
                <dl class="dm-item dm-full">
                    <dt>ご関係の詳細</dt>
                    <dd id="dmRelDetail">—</dd>
                </dl>
            </div>
        </div>

        <div class="dm-section">
            <p class="dm-section-title">食物アレルギー</p>
            <div class="dm-grid">
                <dl class="dm-item">
                    <dt>アレルギー</dt>
                    <dd id="dmAllergy">—</dd>
                </dl>
                <dl class="dm-item dm-full">
                    <dt>アレルギー詳細</dt>
                    <dd id="dmAllergyNotes">—</dd>
                </dl>
            </div>
        </div>

        <div class="dm-section">
            <p class="dm-section-title">連絡先</p>
            <div class="dm-grid">
                <dl class="dm-item">
                    <dt>電話番号</dt>
                    <dd id="dmPhone">—</dd>
                </dl>
                <dl class="dm-item">
                    <dt>郵便番号</dt>
                    <dd id="dmPostal">—</dd>
                </dl>
                <dl class="dm-item dm-full">
                    <dt>住所</dt>
                    <dd id="dmAddress">—</dd>
                </dl>
            </div>
        </div>

        <div class="dm-section">
            <p class="dm-section-title">メッセージ</p>
            <dl class="dm-item">
                <dd id="dmNotes" class="dm-pre">—</dd>
            </dl>
        </div>
    </div>
</div>

<script>
(function () {
    // ── 状態管理 ──────────────────────────────────────
    const state = { q: '', rsvp: 'all', email: 'all', login: 'all', col: null, dir: 'asc' };

    const tbody      = document.getElementById('guestTbody');
    const searchEl   = document.getElementById('guestSearch');
    const clearBtn   = document.getElementById('clearSearch');
    const countEl    = document.getElementById('resultCount');
    const noResults  = document.getElementById('noResults');

    // ── ゲスト行（lh-row 以外）を取得 ──────────────────
    function getRows() {
        return Array.from(tbody.querySelectorAll('tr[data-id]'));
    }

    // ── 1行がフィルター条件にマッチするか ──────────────
    function matches(row) {
        const d = row.dataset;
        // 検索
        if (state.q) {
            const q = state.q;
            if (!d.name.includes(q) && !d.username.includes(q) && !d.furigana.includes(q)) return false;
        }
        // 出欠
        if (state.rsvp !== 'all' && d.rsvp !== state.rsvp) return false;
        // メール
        if (state.email !== 'all' && d.email !== state.email) return false;
        // ログイン
        if (state.login === '1' && d.login === '0') return false;
        if (state.login === '0' && d.login !== '0') return false;
        return true;
    }

    // ── フィルター＆ソートを適用 ────────────────────────
    function applyAll() {
        const rows = getRows();
        let visible = 0;

        rows.forEach(row => {
            const show = matches(row);
            row.style.display = show ? '' : 'none';
            const lh = document.getElementById('lh-row-' + row.dataset.id);
            if (lh) {
                // 行が非表示なら展開行も閉じる
                if (!show) { lh.classList.remove('open'); lh.style.display = 'none'; }
                else        { lh.style.display = lh.classList.contains('open') ? 'table-row' : 'none'; }
            }
            if (show) visible++;
        });

        // ソート（表示中の行だけ並び替え）
        if (state.col) {
            const visRows = rows.filter(r => r.style.display !== 'none');
            visRows.sort((a, b) => compareRows(a, b));
            visRows.forEach(row => {
                tbody.appendChild(row);
                const lh = document.getElementById('lh-row-' + row.dataset.id);
                if (lh) tbody.appendChild(lh);
            });
        }

        // 件数表示
        if (countEl) countEl.innerHTML = `<strong>${visible}</strong> 名表示中`;
        if (noResults) noResults.classList.toggle('visible', visible === 0);
    }

    // ── 列ごとの比較ロジック ────────────────────────────
    const sideOrder = { groom: 0, bride: 1, '': 2 };
    const rsvpOrder = { attending: 0, declining: 1, pending: 2 };

    function compareRows(a, b) {
        const da = a.dataset, db = b.dataset;
        let va, vb;
        switch (state.col) {
            case 'name':      va = da.name;    vb = db.name;    break;
            case 'side':      va = sideOrder[da.side] ?? 2; vb = sideOrder[db.side] ?? 2; break;
            case 'rsvp':      va = rsvpOrder[da.rsvp]  ?? 2; vb = rsvpOrder[db.rsvp]  ?? 2; break;
            case 'count':     va = parseInt(da.count)  || 0; vb = parseInt(db.count)  || 0; break;
            case 'email':     va = parseInt(da.email)  || 0; vb = parseInt(db.email)  || 0; break;
            case 'login':     va = parseInt(da.login)  || 0; vb = parseInt(db.login)  || 0; break;
            case 'responded': va = parseInt(da.responded) || 0; vb = parseInt(db.responded) || 0; break;
            default: return 0;
        }
        if (va < vb) return state.dir === 'asc' ? -1 :  1;
        if (va > vb) return state.dir === 'asc' ?  1 : -1;
        return 0;
    }

    // ── ソートアイコン更新 ──────────────────────────────
    function updateSortIcons() {
        document.querySelectorAll('#guestTable th.sortable').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
            const icon = th.querySelector('.sort-icon i');
            if (icon) { icon.className = 'fa-solid fa-sort'; }
            if (th.dataset.col === state.col) {
                th.classList.add('sort-' + state.dir);
                if (icon) icon.className = state.dir === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down';
            }
        });
    }

    // ── ヘッダークリック（ソート）──────────────────────
    document.querySelectorAll('#guestTable th.sortable').forEach(th => {
        th.addEventListener('click', () => {
            const col = th.dataset.col;
            if (state.col === col) {
                state.dir = state.dir === 'asc' ? 'desc' : 'asc';
            } else {
                state.col = col;
                state.dir = 'asc';
            }
            updateSortIcons();
            applyAll();
        });
    });

    // ── 検索 ──────────────────────────────────────────
    if (searchEl) {
        searchEl.addEventListener('input', () => {
            state.q = searchEl.value.toLowerCase().trim();
            clearBtn?.classList.toggle('visible', state.q.length > 0);
            applyAll();
        });
        clearBtn?.addEventListener('click', () => {
            searchEl.value = '';
            state.q = '';
            clearBtn.classList.remove('visible');
            searchEl.focus();
            applyAll();
        });
    }

    // ── フィルタータブ ──────────────────────────────────
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const filter = btn.dataset.filter;
            const value  = btn.dataset.value;
            // 同グループのボタンを非アクティブに
            btn.closest('.filter-row').querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            state[filter] = value;
            applyAll();
        });
    });

    // 初期表示
    applyAll();
})();

// ── 履歴行トグル ────────────────────────────────────────
function toggleLh(id, btn) {
    const row = document.getElementById('lh-row-' + id);
    const open = row.classList.toggle('open');
    row.style.display = open ? 'table-row' : 'none';
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    btn.style.background = open ? '#d6ccff' : '';
}

// ── 回答内容 詳細モーダル ────────────────────────────────
function openDetail(row) {
    const d = row.dataset;
    document.getElementById('dmName').textContent       = d.displayName || '—';
    document.getElementById('dmUsername').textContent   = '@' + (d.username || '—');
    document.getElementById('dmStatus').innerHTML       = statusBadge(d.rsvp, d.statusLabel);
    document.getElementById('dmCount').textContent      = d.count > 0 ? d.count + '名' : '—';
    document.getElementById('dmChildren').textContent   = d.children > 0 ? d.children + '名' : '—';
    document.getElementById('dmResponded').textContent  = d.responded && d.responded !== '0' ? new Date(parseInt(d.responded) * 1000).toLocaleString('ja-JP') : '—';
    document.getElementById('dmSide').textContent       = d.sideLabel || '—';
    document.getElementById('dmRel').textContent        = d.rel || '—';
    document.getElementById('dmRelDetail').textContent  = d.relDetail || '—';
    document.getElementById('dmAllergy').textContent    = d.allergy || '—';
    document.getElementById('dmAllergyNotes').textContent = d.allergyNotes || '—';
    document.getElementById('dmPhone').textContent      = d.phone || '—';
    document.getElementById('dmPostal').textContent     = d.postal || '—';
    document.getElementById('dmAddress').textContent    = d.address || '—';
    document.getElementById('dmNotes').textContent      = d.notes || '—';
    document.getElementById('detailOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeDetail() {
    document.getElementById('detailOverlay').classList.remove('open');
    document.body.style.overflow = '';
}
function statusBadge(status, label) {
    const cls = { attending: 'attending', declining: 'declining', pending: 'pending' }[status] || 'pending';
    return `<span class="badge ${cls}">${label}</span>`;
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDetail(); });
</script>
@endsection
