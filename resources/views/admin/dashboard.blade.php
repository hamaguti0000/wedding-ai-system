@extends('layouts.app')
@section('title', 'RSVP管理 | Admin')

@push('styles')
<style>
/* モバイルで不要列を非表示 */
@media (max-width: 767px) {
    .col-side, .col-date { display: none; }
    .col-count { white-space: nowrap; }
}
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
