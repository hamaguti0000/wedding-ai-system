@extends('layouts.app')
@section('title', 'ゲスト一覧 | Admin')

@push('styles')
<style>
/* モバイルで非表示にする列 */
@media (max-width: 767px) {
    .col-side, .col-date, .col-email { display: none; }
}
/* ログイン状態バッジ */
.badge-login-ok  { background: #e8f5fe; color: #1a6fa8; }
.badge-login-no  { background: #f5f5f5; color: #aaa; }
.badge-email-ok  { background: #eafaf1; color: #1e8449; }
.badge-email-no  { background: #fdf2f2; color: #c0392b; }

/* 返答率バー */
.progress-wrap { margin: 0 0 24px; background: #fff; border-radius: 14px; padding: 18px 22px; box-shadow: 0 4px 14px rgba(0,0,0,0.07); }
.progress-label { display: flex; justify-content: space-between; font-size: 0.8rem; color: #777; margin-bottom: 8px; }
.progress-bar { height: 8px; background: #f0ebe3; border-radius: 4px; overflow: hidden; }
.progress-bar__fill { height: 100%; background: linear-gradient(90deg, #27ae60, #52d68a); border-radius: 4px; transition: width 0.8s ease; }

/* サマリー */
.summary-row { grid-template-columns: repeat(4, 1fr); }
.summary-card .sub  { font-size: 0.75rem; color: #b0a090; margin-top: 3px; }
.summary-card .icon-top { font-size: 1.2rem; margin-bottom: 8px; opacity: 0.55; }
@media (max-width: 640px) {
    .summary-row { grid-template-columns: repeat(2, 1fr); }
}

/* ログイン履歴展開行 */
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

@media (max-width: 767px) {
    .btn-lh-label { display: none; }
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
        <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>氏名</th>
                    <th class="col-side">お立場</th>
                    <th>出欠</th>
                    <th>人数</th>
                    <th class="col-email">メール</th>
                    <th>ログイン</th>
                    <th class="col-date">回答日時</th>
                    <th></th>{{-- 履歴ボタン列 --}}
                </tr>
            </thead>
            <tbody>
                @foreach ($guests as $guest)
                @php
                    $p       = $guest->guestProfile;
                    $logins  = $loginHistoryMap->get($guest->id, collect());
                    $lastOk  = $logins->where('status', 'success')->first();
                    $hasLogin = $lastOk !== null;
                @endphp
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
                        <br><span class="text-muted" style="font-size:0.76rem;">{{ $guest->username }}</span>
                    </td>

                    {{-- お立場（スマホ非表示）--}}
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

                    {{-- 出欠バッジ --}}
                    <td>
                        @if (!$p || $p->participation === 'pending')
                        <span class="badge pending">未回答</span>
                        @elseif ($p->participation === 'attending')
                        <span class="badge attending">出席</span>
                        @else
                        <span class="badge declining">欠席</span>
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

                    {{-- メール登録状態（スマホ非表示）--}}
                    <td class="col-email">
                        @if ($guest->email)
                            <span class="badge badge-email-ok">
                                <i class="fa-solid fa-envelope-circle-check" style="font-size:0.7rem;"></i>
                                登録済
                            </span>
                            @if (!$guest->hasVerifiedEmail())
                            <br><span class="text-muted" style="font-size:0.72rem;">未認証</span>
                            @endif
                        @else
                            <span class="badge badge-email-no">
                                <i class="fa-solid fa-envelope" style="font-size:0.7rem;opacity:0.6;"></i>
                                未登録
                            </span>
                        @endif
                    </td>

                    {{-- ログイン状態 --}}
                    <td>
                        @if ($hasLogin)
                            <span class="badge badge-login-ok">
                                <i class="fa-solid fa-right-to-bracket" style="font-size:0.7rem;"></i>
                                済
                            </span>
                            <br><span class="text-muted" style="font-size:0.74rem;">{{ $lastOk->created_at->format('m/d H:i') }}</span>
                        @else
                            <span class="badge badge-login-no">未</span>
                        @endif
                    </td>

                    {{-- 回答日時（スマホ非表示）--}}
                    <td class="col-date">
                        @if ($p?->responded_at)
                        {{ $p->responded_at->format('m/d') }}<br>
                        <span class="text-muted" style="font-size:0.76rem;">{{ $p->responded_at->format('H:i') }}</span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- 履歴トグルボタン --}}
                    <td style="text-align:right;white-space:nowrap;">
                        <button type="button"
                            class="btn-sm btn-lh"
                            onclick="toggleLh({{ $guest->id }}, this)"
                            aria-expanded="false"
                            title="{{ $guest->username }} のログイン履歴を表示">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <span class="btn-lh-label">履歴</span>
                        </button>
                    </td>
                </tr>

                {{-- ログイン履歴 展開行 --}}
                <tr class="lh-row" id="lh-row-{{ $guest->id }}">
                    <td colspan="8">
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
        @endif
    </div>
</div>

<script>
function toggleLh(id, btn) {
    const row = document.getElementById('lh-row-' + id);
    const open = row.classList.toggle('open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    btn.style.background = open ? '#d6ccff' : '';
}
</script>
@endsection
