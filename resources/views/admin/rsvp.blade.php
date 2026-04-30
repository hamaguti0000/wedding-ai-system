@extends('layouts.app')
@section('title', '回答状況 | Admin')

@push('styles')
<style>
.rsvp-table-wrap { overflow: hidden; }
.rsvp-table-wrap .table-head {
    padding: 14px 20px;
    font-size: 0.82rem;
    color: #999;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    border-bottom: 1px solid #f5f0ea;
}
.rsvp-wrap table { min-width: 680px; }
.rsvp-wrap td { vertical-align: top; }

/* クリック可能行 */
.rsvp-wrap tbody tr { cursor: pointer; }
.rsvp-wrap tbody tr:hover td { background: #fffdf9; }

.attend-banner {
    background: #f0faf4; border: 1px solid #a8d8b9; border-radius: 10px;
    padding: 14px 20px; margin-bottom: 20px; display: flex;
    align-items: center; gap: 20px; flex-wrap: wrap; font-size: 0.9rem; color: #2d6a4f;
}
.attend-banner strong { font-size: 1.25rem; }

.filter-tabs { display: flex; gap: 6px; margin-bottom: 16px; flex-wrap: wrap; align-items: center; justify-content: space-between; }
.filter-tabs-left { display: flex; gap: 6px; flex-wrap: wrap; }
.filter-tab {
    padding: 7px 18px; border-radius: 20px; font-size: 0.82rem; font-weight: 500;
    text-decoration: none; border: 1px solid #e8d5b7;
    color: #b38b59; background: #fef9f0; transition: background 0.15s; white-space: nowrap;
}
.filter-tab.active, .filter-tab:hover { background: #b38b59; color: #fff; border-color: #b38b59; }

.btn-csv {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 16px; border-radius: 6px; font-size: 0.82rem; font-weight: 500;
    background: #27ae60; color: #fff; text-decoration: none; white-space: nowrap;
    border: none; transition: background 0.2s;
}
.btn-csv:hover { background: #1e8449; color: #fff; }

.allergy-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 600; }
.allergy-yes { background: #fff3cd; color: #856404; }
.allergy-no  { background: #f0f0f0; color: #888; }
.text-sm     { font-size: 0.8rem; color: #666; }

/* ── 詳細モーダル ── */
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
    .attend-banner { flex-direction: column; align-items: flex-start; gap: 8px; }
    .filter-tabs { flex-direction: column; align-items: flex-start; }
    .detail-modal { padding: 20px 16px; }
    .dm-grid { grid-template-columns: 1fr; }
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

    {{-- 出席者合計バナー --}}
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

    {{-- フィルタータブ + CSV --}}
    <div class="filter-tabs">
        <div class="filter-tabs-left">
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
        <a href="{{ route('admin.rsvp.export', ['filter' => $filter]) }}" class="btn-csv">
            <i class="fa-solid fa-file-csv"></i> CSV出力
        </a>
    </div>

    {{-- 詳細テーブル --}}
    <div class="rsvp-table-wrap">
        <div class="table-head">
            <span>{{ $filtered->count() }}件 表示中
                <span style="font-size:0.75rem;color:#bbb;margin-left:6px;">— 行をクリックで詳細表示</span>
            </span>
        </div>

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
                    <th>回答日時</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($filtered as $guest)
                @php $p = $guest->guestProfile; $status = $p?->participation ?? 'pending'; @endphp
                <tr data-id="{{ $guest->id }}"
                    data-name="{{ trim(($p?->last_name ?? '') . ' ' . ($p?->first_name ?? '')) ?: $guest->username }}"
                    data-username="{{ $guest->username }}"
                    data-furigana="{{ $p?->furigana() ?? '' }}"
                    data-side="{{ $p?->guestSideLabel() ?? '—' }}"
                    data-rel="{{ $p?->relationshipLabel() ?? '—' }}"
                    data-rel-detail="{{ $p?->relationship_detail ?? '' }}"
                    data-status="{{ $status }}"
                    data-status-label="{{ ['attending'=>'出席','declining'=>'欠席','pending'=>'未回答'][$status] }}"
                    data-count="{{ $p?->attending_count ?? 0 }}"
                    data-children="{{ $p?->children_count ?? 0 }}"
                    data-allergy="{{ $p?->has_allergy ? 'あり' : 'なし' }}"
                    data-allergy-notes="{{ $p?->allergy_notes ?? '' }}"
                    data-phone="{{ $p?->phone ?? '' }}"
                    data-postal="{{ $p?->postal_code ?? '' }}"
                    data-address="{{ $p?->address ?? '' }}"
                    data-notes="{{ $p?->notes ?? '' }}"
                    data-responded="{{ $p?->responded_at?->format('Y/m/d H:i') ?? '' }}"
                    onclick="openDetail(this)">
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
                    <td>
                        @if ($p?->guest_side)
                            {{ $p->guestSideLabel() }}
                            @if ($p->relationship)
                            <br><span class="text-muted">{{ $p->relationshipLabel() }}</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td><span class="badge {{ $status }}">{{ ['attending'=>'出席','declining'=>'欠席','pending'=>'未回答'][$status] }}</span></td>
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
                    <td>
                        @if ($p?->has_allergy)
                            <span class="allergy-badge allergy-yes">あり</span>
                        @elseif ($p)
                            <span class="allergy-badge allergy-no">なし</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if ($p?->responded_at)
                            {{ $p->responded_at->format('m/d') }}
                            <br><span class="text-muted">{{ $p->responded_at->format('H:i') }}</span>
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

{{-- 詳細モーダル --}}
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
function openDetail(row) {
    const d = row.dataset;
    document.getElementById('dmName').textContent       = d.name || '—';
    document.getElementById('dmUsername').textContent   = '@' + (d.username || '—');
    document.getElementById('dmStatus').innerHTML       = statusBadge(d.status, d.statusLabel);
    document.getElementById('dmCount').textContent      = d.count > 0 ? d.count + '名' : '—';
    document.getElementById('dmChildren').textContent   = d.children > 0 ? d.children + '名' : '—';
    document.getElementById('dmResponded').textContent  = d.responded || '—';
    document.getElementById('dmSide').textContent       = d.side || '—';
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
