@extends('layouts.app')
@section('title', 'メール操作ログ | Admin')

@push('styles')
<style>
.audit-wrap table { min-width: 860px; }
.audit-meta { color:#8f7d6f; font-size:.78rem; line-height:1.6; }
.audit-action { display:inline-flex; padding:5px 9px; border-radius:999px; background:#f7efe4; color:#7a5c30; font-size:.76rem; font-weight:700; }
.audit-email { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size:.82rem; }
</style>
@endpush

@section('content')
<div class="audit-wrap">
    <h1>メール操作ログ</h1>
    <p style="color:#8f7d6f;margin-top:-6px;margin-bottom:18px;font-size:.88rem;">
        メールアドレスの登録・変更・削除を、操作した管理者または本人と一緒に確認できます。
    </p>

    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>日時</th>
                    <th>対象ユーザー</th>
                    <th>操作</th>
                    <th>変更前</th>
                    <th>変更後</th>
                    <th>操作者</th>
                    <th>内容</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('Y/m/d H:i') }}</td>
                    <td>
                        <strong>{{ $log->user?->username ?? '削除済み' }}</strong>
                        @if ($log->user?->name)
                        <div class="audit-meta">{{ $log->user->name }}</div>
                        @endif
                    </td>
                    <td><span class="audit-action">{{ $log->action }}</span></td>
                    <td class="audit-email">{{ $log->old_email ?: '—' }}</td>
                    <td class="audit-email">{{ $log->new_email ?: '—' }}</td>
                    <td>
                        {{ $log->actor?->username ?? 'システム' }}
                        @if ($log->actor?->name)
                        <div class="audit-meta">{{ $log->actor->name }}</div>
                        @endif
                    </td>
                    <td>{{ $log->message }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;color:#8f7d6f;padding:28px;">メール操作ログはまだありません</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:18px;">
        {{ $logs->links() }}
    </div>
</div>
@endsection
