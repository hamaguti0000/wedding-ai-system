@extends('layouts.app')
@section('title', '操作ログ | Admin')

@push('styles')
<style>
.audit-wrap { display: grid; gap: 20px; }
.audit-hero {
    display: flex; justify-content: space-between; gap: 16px; align-items: flex-end;
    background: linear-gradient(180deg, #fffaf3 0%, #fff 100%);
    border: 1px solid #f0e3d2; border-radius: 18px; padding: 24px 26px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.05);
}
.audit-hero h1 { margin: 0; }
.audit-hero p { margin: 8px 0 0; color: #8d7865; font-size: .88rem; line-height: 1.6; }
.audit-links { display: flex; flex-wrap: wrap; gap: 10px; }
.audit-link {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px;
    border-radius: 999px; border: 1px solid #e3d3bf; background: #fffdf9;
    color: #5a4637; text-decoration: none; font-size: .86rem; font-weight: 600;
}
.audit-link--primary { background: #b38b59; color: #fff; border-color: #b38b59; }
.audit-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.audit-card {
    border-radius: 14px; padding: 16px; border: 1px solid #f0e3d2; background: #fff;
    box-shadow: 0 4px 14px rgba(0,0,0,0.04);
}
.audit-card__label { color: #b38b59; font-size: .72rem; letter-spacing: 1.6px; text-transform: uppercase; font-weight: 700; }
.audit-card__value { margin-top: 10px; font-size: 1.9rem; font-weight: 700; line-height: 1; color: #352820; }
.audit-card__sub { margin-top: 6px; color: #7f6a57; font-size: .82rem; }
.audit-toolbar {
    display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
    margin-top: -2px;
}
.audit-pill {
    display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px;
    border-radius: 999px; border: 1px solid #e3d3bf; background: #fffdf9;
    color: #5a4637; font-size: .8rem; font-weight: 700; text-decoration: none;
}
.audit-pill.is-active { background: #b38b59; color: #fff; border-color: #b38b59; }
.audit-panel {
    background: #fff; border: 1px solid #f0e3d2; border-radius: 16px; padding: 20px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.05);
}
.audit-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
}
.audit-table thead th {
    text-align: left;
    color: #8d7865;
    font-size: .74rem;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    padding: 0 12px 6px;
}
.audit-table tbody tr td {
    background: #fcf8f2;
    border-top: 1px solid #f2e7d8;
    border-bottom: 1px solid #f2e7d8;
    padding: 14px 12px;
    vertical-align: top;
}
.audit-table tbody tr td:first-child {
    border-left: 1px solid #f2e7d8;
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
}
.audit-table tbody tr td:last-child {
    border-right: 1px solid #f2e7d8;
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
}
.audit-name { font-weight: 700; color: #36281d; }
.audit-sub { margin-top: 4px; color: #8f7d6f; font-size: .8rem; line-height: 1.5; }
.audit-actions { display: flex; flex-wrap: wrap; gap: 6px; }
.audit-badge {
    display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px;
    border-radius: 999px; font-size: .76rem; font-weight: 700; white-space: nowrap;
}
.audit-badge.is-checkin { background: #e4f4ea; color: #2f6e42; }
.audit-badge.is-cancel { background: #fdecec; color: #b42318; }
.audit-badge.is-manual { background: #eef2ff; color: #4f46e5; }
.audit-badge.is-qr { background: #f3e8ff; color: #7c3aed; }
.audit-empty {
    padding: 28px 18px; text-align: center; border-radius: 12px;
    background: #fcf8f2; border: 1px dashed #e4d6c3; color: #8a7967;
}
.pagination { margin-top: 16px; }
@media (max-width: 920px) {
    .audit-summary { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .audit-hero { align-items: flex-start; flex-direction: column; padding: 16px; }
    .audit-panel { padding: 14px; }
    .audit-table thead { display: none; }
    .audit-table, .audit-table tbody, .audit-table tr, .audit-table td { display: block; width: 100%; }
    .audit-table tbody tr { margin-bottom: 10px; }
    .audit-table tbody tr td {
        border-left: 1px solid #f2e7d8;
        border-right: 1px solid #f2e7d8;
        border-radius: 0;
    }
    .audit-table tbody tr td:first-child {
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }
    .audit-table tbody tr td:last-child {
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
    }
}
</style>
@endpush

@section('content')
<div class="audit-wrap">
    <section class="audit-hero">
        <div>
            <h1>操作ログ</h1>
            <p>受付、取消、出欠回答、プロフィール変更を、誰がいつ何をしたかまで追えるようにした一覧です。</p>
        </div>
        <div class="audit-links">
            <a href="{{ route('admin.ops') }}" class="audit-link audit-link--primary">
                <i class="fa-solid fa-chart-line"></i> 運営ダッシュボード
            </a>
            <a href="{{ route('admin.checkin.guests') }}" class="audit-link">
                <i class="fa-solid fa-clipboard-user"></i> 受付一覧
            </a>
        </div>
    </section>

    <section class="audit-summary">
        <div class="audit-card">
            <div class="audit-card__label">Total</div>
            <div class="audit-card__value">{{ $summary['total'] }}</div>
            <div class="audit-card__sub">記録件数</div>
        </div>
        <div class="audit-card">
            <div class="audit-card__label">Check-in</div>
            <div class="audit-card__value">{{ $summary['check_in'] }}</div>
            <div class="audit-card__sub">受付の記録</div>
        </div>
        <div class="audit-card">
            <div class="audit-card__label">Cancel</div>
            <div class="audit-card__value">{{ $summary['cancel'] }}</div>
            <div class="audit-card__sub">取消の記録</div>
        </div>
        <div class="audit-card">
            <div class="audit-card__label">Update</div>
            <div class="audit-card__value">{{ $summary['update'] }}</div>
            <div class="audit-card__sub">変更・更新の記録</div>
        </div>
    </section>

    <section class="audit-panel">
        <div class="audit-toolbar">
            @foreach ([
                'all' => 'すべて',
                'check_in' => '受付',
                'cancel' => '取消',
                'update' => '更新',
            ] as $key => $label)
                <a href="{{ route('admin.audit.checkin', array_filter(['action' => $key], fn ($v) => $v !== '')) }}"
                   class="audit-pill {{ $action === $key ? 'is-active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($logs->isEmpty())
            <div class="audit-empty">まだ操作ログがありません。</div>
        @else
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>ゲスト</th>
                        <th>内容</th>
                        <th>区分</th>
                        <th>実行者</th>
                        <th>時刻</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td>
                                <div class="audit-name">{{ $log->guestProfile?->fullName() ?: $log->guestProfile?->user?->name ?: '—' }}</div>
                                <div class="audit-sub">
                                    {{ $log->guestProfile?->user?->username ?: '—' }}
                                </div>
                            </td>
                            <td>
                                <div class="audit-name">{{ $log->message }}</div>
                                <div class="audit-sub">
                                    {{ $log->action === 'cancel' ? '受付を取り消しました' : ($log->action === 'check_in' ? '受付を記録しました' : '更新を記録しました') }}
                                </div>
                            </td>
                            <td>
                                <div class="audit-actions">
                                    <span class="audit-badge {{ $log->action === 'cancel' ? 'is-cancel' : ($log->action === 'check_in' ? 'is-checkin' : 'is-manual') }}">
                                        {{ $log->action === 'cancel' ? '取消' : ($log->action === 'check_in' ? '受付' : '更新') }}
                                    </span>
                                    @if ($log->source)
                                        <span class="audit-badge {{ $log->source === 'qr' ? 'is-qr' : 'is-manual' }}">
                                            {{ $log->source === 'qr' ? 'QR' : ($log->source === 'profile' ? 'プロフィール' : ($log->source === 'rsvp' ? 'RSVP' : '管理')) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="audit-name">{{ $log->actor?->name ?: '—' }}</div>
                            </td>
                            <td>
                                <div class="audit-name">{{ $log->created_at?->format('Y/m/d') }}</div>
                                <div class="audit-sub">{{ $log->created_at?->format('H:i') }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination">
                {{ $logs->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
