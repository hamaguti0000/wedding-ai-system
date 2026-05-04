@extends('layouts.app')
@section('title', 'RSVP管理 | Admin')

@push('styles')
<style>
/* モバイルで不要列を非表示 */
@media (max-width: 767px) {
    .col-side, .col-date { display: none; }
    .col-count { white-space: nowrap; }
}
.summary-row { grid-template-columns: repeat(4, 1fr); }
.summary-card .sub { font-size: 0.75rem; color: #b0a090; margin-top: 3px; }
.summary-card .icon-top { font-size: 1.2rem; margin-bottom: 8px; opacity: 0.55; }
.progress-wrap { margin: 0 0 24px; background: #fff; border-radius: 14px; padding: 18px 22px; box-shadow: 0 4px 14px rgba(0,0,0,0.07); }
.progress-label { display: flex; justify-content: space-between; font-size: 0.8rem; color: #777; margin-bottom: 8px; }
.progress-bar { height: 8px; background: #f0ebe3; border-radius: 4px; overflow: hidden; }
.progress-bar__fill { height: 100%; background: linear-gradient(90deg, #27ae60, #52d68a); border-radius: 4px; transition: width 0.8s ease; }
@media (max-width: 640px) {
    .summary-row { grid-template-columns: repeat(2, 1fr); }
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
                        <br><span class="text-muted" style="font-size:0.76rem;">{{ $guest->username }}</span>
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
