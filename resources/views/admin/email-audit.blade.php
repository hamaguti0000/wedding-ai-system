@extends('layouts.app')
@section('title', 'メール操作ログ | Admin')

@push('styles')
<style>
.mail-audit { display: grid; gap: 20px; }
.mail-hero {
    display: flex; justify-content: space-between; gap: 16px; align-items: flex-end;
    background: #fff; border: 1px solid #efe4d6; border-radius: 14px; padding: 22px 24px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.05);
}
.mail-hero h1 { margin: 0; }
.mail-hero p { margin: 8px 0 0; color: #7f6f62; font-size: .88rem; line-height: 1.6; }
.mail-links { display: flex; flex-wrap: wrap; gap: 10px; }
.mail-link {
    display: inline-flex; align-items: center; gap: 8px; padding: 9px 12px;
    border-radius: 8px; border: 1px solid #e2d4c4; background: #fffdf9;
    color: #4f4036; text-decoration: none; font-size: .84rem; font-weight: 700;
}
.mail-link--primary { background: #b38b59; color: #fff; border-color: #b38b59; }
.mail-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.mail-card {
    background: #fff; border: 1px solid #efe4d6; border-radius: 12px; padding: 15px 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
}
.mail-card__label { color: #9a7a55; font-size: .72rem; letter-spacing: 1.3px; text-transform: uppercase; font-weight: 800; }
.mail-card__value { margin-top: 8px; color: #332820; font-size: 1.8rem; line-height: 1; font-weight: 800; }
.mail-card__sub { margin-top: 6px; color: #7f6f62; font-size: .8rem; }
.mail-panel {
    background: #fff; border: 1px solid #efe4d6; border-radius: 14px; padding: 18px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.05);
}
.mail-table { width: 100%; border-collapse: separate; border-spacing: 0 10px; min-width: 980px; }
.mail-table thead th {
    text-align: left; padding: 0 12px 5px; color: #877566;
    font-size: .73rem; letter-spacing: 1.1px; text-transform: uppercase;
}
.mail-table tbody td {
    background: #fcf9f5; border-top: 1px solid #f0e5d8; border-bottom: 1px solid #f0e5d8;
    padding: 13px 12px; vertical-align: top;
}
.mail-table tbody td:first-child {
    border-left: 1px solid #f0e5d8; border-top-left-radius: 10px; border-bottom-left-radius: 10px;
}
.mail-table tbody td:last-child {
    border-right: 1px solid #f0e5d8; border-top-right-radius: 10px; border-bottom-right-radius: 10px;
}
.mail-main { color: #332820; font-weight: 800; }
.mail-sub { margin-top: 4px; color: #867668; font-size: .8rem; line-height: 1.5; }
.mail-address {
    display: inline-flex; max-width: 260px; overflow-wrap: anywhere;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: .8rem; color: #41362f;
}
.mail-muted { color: #9a8a7c; }
.mail-change { display: grid; gap: 5px; min-width: 220px; }
.mail-arrow { color: #9a7a55; font-size: .78rem; }
.mail-badge {
    display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px;
    border-radius: 999px; font-size: .75rem; font-weight: 800; white-space: nowrap;
}
.mail-badge.is-set { background: #e8f5ec; color: #27723f; }
.mail-badge.is-update { background: #eef2ff; color: #4f46e5; }
.mail-badge.is-delete { background: #fff1f2; color: #be123c; }
.mail-badge.is-system { background: #f3efe8; color: #6f5d4d; }
.mail-empty {
    padding: 30px 18px; text-align: center; border-radius: 12px;
    background: #fcf9f5; border: 1px dashed #e4d6c5; color: #867668;
}
.pagination { margin-top: 16px; }
@media (max-width: 960px) {
    .mail-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 640px) {
    .mail-hero { display: block; padding: 16px; }
    .mail-links { margin-top: 12px; }
    .mail-summary { grid-template-columns: 1fr; }
    .mail-panel { padding: 12px; }
}
</style>
@endpush

@section('content')
@php
    $actionLabels = [
        'admin_set' => '管理者登録',
        'admin_update' => '管理者変更',
        'admin_delete' => '管理者削除',
        'user_set' => '本人登録',
        'user_update' => '本人変更',
    ];

    $actionClass = function (?string $action) {
        return match ($action) {
            'admin_delete' => 'is-delete',
            'admin_update', 'user_update' => 'is-update',
            'admin_set', 'user_set' => 'is-set',
            default => 'is-system',
        };
    };
@endphp

<div class="mail-audit">
    <section class="mail-hero">
        <div>
            <h1>メール操作ログ</h1>
            <p>メールアドレスの登録、変更、削除を、対象者・操作者・変更内容で確認できます。</p>
        </div>
        <div class="mail-links">
            <a href="{{ route('admin.users') }}" class="mail-link mail-link--primary">
                <i class="fa-solid fa-users"></i> ユーザー管理
            </a>
            <a href="{{ route('admin.ops') }}" class="mail-link">
                <i class="fa-solid fa-chart-line"></i> 運営ダッシュボード
            </a>
        </div>
    </section>

    <section class="mail-summary">
        <div class="mail-card">
            <div class="mail-card__label">Total</div>
            <div class="mail-card__value">{{ $summary['total'] }}</div>
            <div class="mail-card__sub">記録件数</div>
        </div>
        <div class="mail-card">
            <div class="mail-card__label">Set</div>
            <div class="mail-card__value">{{ $summary['set'] }}</div>
            <div class="mail-card__sub">登録</div>
        </div>
        <div class="mail-card">
            <div class="mail-card__label">Update</div>
            <div class="mail-card__value">{{ $summary['update'] }}</div>
            <div class="mail-card__sub">変更</div>
        </div>
        <div class="mail-card">
            <div class="mail-card__label">Delete</div>
            <div class="mail-card__value">{{ $summary['delete'] }}</div>
            <div class="mail-card__sub">削除</div>
        </div>
    </section>

    <section class="mail-panel">
        @if ($logs->isEmpty())
            <div class="mail-empty">メール操作ログはまだありません。</div>
        @else
            <div class="table-scroll">
                <table class="mail-table">
                    <thead>
                        <tr>
                            <th>日時</th>
                            <th>対象ユーザー</th>
                            <th>操作</th>
                            <th>変更内容</th>
                            <th>操作者</th>
                            <th>メモ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td>
                                    <div class="mail-main">{{ $log->created_at?->format('Y/m/d') }}</div>
                                    <div class="mail-sub">{{ $log->created_at?->format('H:i') }}</div>
                                </td>
                                <td>
                                    <div class="mail-main">{{ $log->user?->username ?? '削除済み' }}</div>
                                    @if ($log->user?->name)
                                        <div class="mail-sub">{{ $log->user->name }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="mail-badge {{ $actionClass($log->action) }}">
                                        {{ $actionLabels[$log->action] ?? $log->action }}
                                    </span>
                                </td>
                                <td>
                                    <div class="mail-change">
                                        <span class="mail-address {{ $log->old_email ? '' : 'mail-muted' }}">
                                            {{ $log->old_email ?: '未登録' }}
                                        </span>
                                        <span class="mail-arrow"><i class="fa-solid fa-arrow-down"></i></span>
                                        <span class="mail-address {{ $log->new_email ? '' : 'mail-muted' }}">
                                            {{ $log->new_email ?: '未登録' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="mail-main">{{ $log->actor?->username ?? 'システム' }}</div>
                                    @if ($log->actor?->name)
                                        <div class="mail-sub">{{ $log->actor->name }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="mail-sub" style="margin-top:0;">{{ $log->message ?: '—' }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                {{ $logs->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
