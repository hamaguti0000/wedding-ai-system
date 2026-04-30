@extends('layouts.app')
@section('title', '回答状況 | Admin')

@push('styles')
<style>
/* ページ固有スタイル */
.rsvp-table-wrap { overflow: hidden; }
.rsvp-table-wrap .table-head { padding: 16px 20px 10px; font-size: 0.82rem; color: #999; }
.rsvp-wrap table { min-width: 640px; }
.rsvp-wrap td    { vertical-align: top; }

.attend-banner {
    background: #f0faf4;
    border: 1px solid #a8d8b9;
    border-radius: 10px;
    padding: 14px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    font-size: 0.9rem;
    color: #2d6a4f;
}
.attend-banner strong { font-size: 1.25rem; }

.filter-tabs {
    display: flex; gap: 6px; margin-bottom: 16px; flex-wrap: wrap;
}
.filter-tab {
    padding: 7px 18px; border-radius: 20px; font-size: 0.82rem; font-weight: 500;
    text-decoration: none; border: 1px solid #e8d5b7;
    color: #b38b59; background: #fef9f0; transition: background 0.15s; white-space: nowrap;
}
.filter-tab.active, .filter-tab:hover { background: #b38b59; color: #fff; border-color: #b38b59; }

.allergy-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 600; }
.allergy-yes { background: #fff3cd; color: #856404; }
.allergy-no  { background: #f0f0f0; color: #888; }
.text-sm     { font-size: 0.8rem; color: #666; }

@media (max-width: 767px) {
    .attend-banner { flex-direction: column; align-items: flex-start; gap: 8px; }
    .rsvp-table-wrap .table-head { padding: 12px 12px 8px; }
}
</style>
@endpush

@section('content')
<div class="rsvp-wrap">

    <h1>回答状況</h1>

    {{-- サマリーカード --}}
    <div class="summary-row">
        <div class="summary-card total">
            <div class="count">{{ $summary['total'] }}</div>
            <div class="label">招待総数</div>
        </div>
        <div class="summary-card attending">
            <div class="count">{{ $summary['attending'] }}</div>
            <div class="label">出席</div>
        </div>
        <div class="summary-card declining">
            <div class="count">{{ $summary['declining'] }}</div>
            <div class="label">欠席</div>
        </div>
        <div class="summary-card pending">
            <div class="count">{{ $summary['pending'] }}</div>
            <div class="label">未回答</div>
        </div>
    </div>

    {{-- 出席者合計 --}}
    @if ($summary['attending'] > 0)
    <div class="attend-banner">
        <div>
            <span>出席予定 合計：</span>
            <strong>{{ $totalAttending }}名</strong>
            @if ($totalChildren > 0)
            <span class="text-sm">（うちお子様 {{ $totalChildren }}名）</span>
            @endif
        </div>
        <div>
            <span>大人：</span>
            <strong>{{ $totalAttending - $totalChildren }}名</strong>
            @if ($totalChildren > 0)
            &nbsp;＋&nbsp;<span>お子様：</span><strong>{{ $totalChildren }}名</strong>
            @endif
        </div>
    </div>
    @endif

    {{-- フィルタータブ --}}
    <div class="filter-tabs">
        <a href="{{ route('admin.rsvp') }}"
           class="filter-tab {{ $filter === 'all' ? 'active' : '' }}">
            全員（{{ $summary['total'] }}）
        </a>
        <a href="{{ route('admin.rsvp', ['filter' => 'attending']) }}"
           class="filter-tab {{ $filter === 'attending' ? 'active' : '' }}">
            出席（{{ $summary['attending'] }}）
        </a>
        <a href="{{ route('admin.rsvp', ['filter' => 'declining']) }}"
           class="filter-tab {{ $filter === 'declining' ? 'active' : '' }}">
            欠席（{{ $summary['declining'] }}）
        </a>
        <a href="{{ route('admin.rsvp', ['filter' => 'pending']) }}"
           class="filter-tab {{ $filter === 'pending' ? 'active' : '' }}">
            未回答（{{ $summary['pending'] }}）
        </a>
    </div>

    {{-- 詳細テーブル --}}
    <div class="rsvp-table-wrap">
        <div class="table-head">{{ $filtered->count() }}件 表示中</div>

        @if ($filtered->isEmpty())
        <div class="empty-state">該当するゲストがいません</div>
        @else
        <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>氏名</th>
                    <th>お立場 / ご関係</th>
                    <th>出欠</th>
                    <th>人数</th>
                    <th>アレルギー</th>
                    <th>連絡先</th>
                    <th>回答日時</th>
                    <th>メモ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($filtered as $guest)
                @php $p = $guest->guestProfile; @endphp
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
                    </td>

                    {{-- お立場 / ご関係 --}}
                    <td>
                        @if ($p?->guest_side)
                            <span>{{ $p->guestSideLabel() }}</span>
                            @if ($p->relationship)
                            <br><span class="text-muted">{{ $p->relationshipLabel() }}</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- 出欠 --}}
                    <td>
                        @php $status = $p?->participation ?? 'pending'; @endphp
                        <span class="badge {{ $status }}">
                            {{ ['attending' => '出席', 'declining' => '欠席', 'pending' => '未回答'][$status] }}
                        </span>
                    </td>

                    {{-- 人数 --}}
                    <td>
                        @if ($p?->isAttending())
                            {{ $p->attending_count }}名
                            @if ($p->children_count > 0)
                            <br><span class="text-muted">子{{ $p->children_count }}名</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- アレルギー --}}
                    <td>
                        @if ($p?->has_allergy)
                            <span class="allergy-badge allergy-yes">あり</span>
                            @if ($p->allergy_notes)
                            <br><span class="text-sm">{{ $p->allergy_notes }}</span>
                            @endif
                        @elseif ($p)
                            <span class="allergy-badge allergy-no">なし</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- 連絡先 --}}
                    <td>
                        @if ($p?->phone)
                            {{ $p->phone }}
                        @endif
                        @if ($p?->address)
                            @if ($p->phone)<br>@endif
                            <span class="text-sm">{{ $p->address }}</span>
                        @endif
                        @if (!$p?->phone && !$p?->address)
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- 回答日時 --}}
                    <td>
                        @if ($p?->responded_at)
                            {{ $p->responded_at->format('m/d') }}
                            <br><span class="text-muted">{{ $p->responded_at->format('H:i') }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- メモ --}}
                    <td>
                        @if ($p?->notes)
                            <span class="text-sm">{{ Str::limit($p->notes, 40) }}</span>
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
