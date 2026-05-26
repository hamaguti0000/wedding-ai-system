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

.filter-tabs { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; align-items: center; justify-content: space-between; }
.filter-tabs-left { display: flex; gap: 6px; flex-wrap: wrap; }
.filter-tab {
    padding: 7px 18px; border-radius: 20px; font-size: 0.82rem; font-weight: 500;
    text-decoration: none; border: 1px solid #e8d5b7;
    color: #b38b59; background: #fef9f0; transition: background 0.15s; white-space: nowrap;
}
.filter-tab.active, .filter-tab:hover { background: #b38b59; color: #fff; border-color: #b38b59; }
/* ── 検索・ソート ── */
.filter-btn-rsvp {
    padding: 7px 18px; border-radius: 20px; font-size: 0.82rem; font-weight: 500;
    border: 1px solid #e8d5b7; color: #b38b59; background: #fef9f0;
    cursor: pointer; transition: background 0.15s; white-space: nowrap;
}
.filter-btn-rsvp.active, .filter-btn-rsvp:hover { background: #b38b59; color: #fff; border-color: #b38b59; }
.rsvp-search-wrap { position: relative; }
.rsvp-search-wrap i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #c0b0a0; font-size: 0.85rem; pointer-events: none; }
.rsvp-search { width: 200px; padding: 8px 30px 8px 32px; border: 1px solid #e0d0bc; border-radius: 6px; font-size: 0.85rem; background: #fffdf9; box-sizing: border-box; }
.rsvp-search:focus { border-color: #b38b59; outline: none; }
.rsvp-clear { display: none; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #c0b0a0; font-size: 1rem; line-height: 1; }
.rsvp-clear.visible { display: block; }
th.sortable { cursor: pointer; user-select: none; }
th.sortable:hover { background: #f5ede0; }
.sort-icon { display: inline-block; margin-left: 4px; font-size: 0.75rem; color: #c0b0a0; }
th.sort-asc .sort-icon, th.sort-desc .sort-icon { color: #b38b59; }
.rsvp-no-results { display: none; text-align: center; padding: 40px 20px; color: #aaa; }
.rsvp-no-results.visible { display: block; }

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

    {{-- フィルター（クライアントサイド）+ 検索 + CSV --}}
    <div class="filter-tabs">
        <div class="filter-tabs-left">
            <button class="filter-btn-rsvp active" data-rsvp="all">全員（{{ $summary['total'] }}）</button>
            <button class="filter-btn-rsvp" data-rsvp="attending">出席（{{ $summary['attending'] }}）</button>
            <button class="filter-btn-rsvp" data-rsvp="declining">欠席（{{ $summary['declining'] }}）</button>
            <button class="filter-btn-rsvp" data-rsvp="pending">未回答（{{ $summary['pending'] }}）</button>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <div class="rsvp-search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" id="rsvpSearch" class="rsvp-search"
                       placeholder="名前・ユーザー名で検索" autocomplete="off">
                <button type="button" id="rsvpClear" class="rsvp-clear" aria-label="クリア">✕</button>
            </div>
            <a href="{{ route('admin.rsvp.export') }}" class="btn-csv">
                <i class="fa-solid fa-file-csv"></i> CSV出力
            </a>
        </div>
    </div>

    {{-- 詳細テーブル --}}
    <div class="rsvp-table-wrap">
        <div class="table-head">
            <span id="rsvpCount"><strong>{{ $guests->count() }}</strong>件 表示中</span>
            <span style="font-size:0.75rem;color:#bbb;margin-left:6px;">— 行をクリックで詳細表示</span>
        </div>

        @if ($guests->isEmpty())
        <div class="empty-state">該当するゲストがいません</div>
        @else
        <div class="table-scroll">
        <table id="rsvpTable">
            <thead>
                <tr>
                    <th class="sortable" data-col="name">氏名 <span class="sort-icon"><i class="fa-solid fa-sort"></i></span></th>
                    <th class="sortable" data-col="side">お立場 / ご関係 <span class="sort-icon"><i class="fa-solid fa-sort"></i></span></th>
                    <th class="sortable" data-col="status">出欠 <span class="sort-icon"><i class="fa-solid fa-sort"></i></span></th>
                    <th class="sortable" data-col="count">人数 <span class="sort-icon"><i class="fa-solid fa-sort"></i></span></th>
                    <th>アレルギー</th>
                    <th class="sortable" data-col="responded">回答日時 <span class="sort-icon"><i class="fa-solid fa-sort"></i></span></th>
                </tr>
            </thead>
            <tbody id="rsvpTbody">
                @foreach ($guests as $guest)
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
        <div class="rsvp-no-results" id="rsvpNoResults">
            <div style="font-size:2rem;margin-bottom:8px;">🔍</div>
            <p style="font-weight:600;color:#888;">該当するゲストが見つかりません</p>
            <p style="font-size:0.84rem;color:#aaa;margin-top:4px;">検索条件やフィルターを変更してみてください</p>
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
// ── 検索・フィルター・ソート ────────────────────────────────
(function () {
    const state = { q: '', rsvp: 'all', col: null, dir: 'asc' };
    const tbody    = document.getElementById('rsvpTbody');
    const searchEl = document.getElementById('rsvpSearch');
    const clearBtn = document.getElementById('rsvpClear');
    const countEl  = document.getElementById('rsvpCount');
    const noRes    = document.getElementById('rsvpNoResults');
    if (!tbody) return;

    const getRows = () => Array.from(tbody.querySelectorAll('tr[data-id]'));

    function matches(row) {
        const d = row.dataset;
        if (state.q) {
            const q = state.q;
            if (!d.name.toLowerCase().includes(q) && !d.username.toLowerCase().includes(q) && !d.furigana.includes(q)) return false;
        }
        if (state.rsvp !== 'all' && d.status !== state.rsvp) return false;
        return true;
    }

    const statusOrder = { attending: 0, declining: 1, pending: 2 };
    function compare(a, b) {
        const da = a.dataset, db = b.dataset;
        let va, vb;
        switch (state.col) {
            case 'name':      va = da.name;                    vb = db.name;                    break;
            case 'side':      va = da.side || '';              vb = db.side || '';              break;
            case 'status':    va = statusOrder[da.status] ?? 2; vb = statusOrder[db.status] ?? 2; break;
            case 'count':     va = parseInt(da.count) || 0;   vb = parseInt(db.count) || 0;   break;
            case 'responded': va = da.responded || '';         vb = db.responded || '';         break;
            default: return 0;
        }
        if (va < vb) return state.dir === 'asc' ? -1 :  1;
        if (va > vb) return state.dir === 'asc' ?  1 : -1;
        return 0;
    }

    function updateIcons() {
        document.querySelectorAll('#rsvpTable th.sortable').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
            const icon = th.querySelector('.sort-icon i');
            if (icon) icon.className = 'fa-solid fa-sort';
            if (th.dataset.col === state.col) {
                th.classList.add('sort-' + state.dir);
                if (icon) icon.className = state.dir === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down';
            }
        });
    }

    function applyAll() {
        const rows = getRows();
        let visible = 0;
        rows.forEach(row => { const show = matches(row); row.style.display = show ? '' : 'none'; if (show) visible++; });
        if (state.col) {
            rows.filter(r => r.style.display !== 'none').sort(compare).forEach(r => tbody.appendChild(r));
        }
        if (countEl) countEl.innerHTML = `<strong>${visible}</strong>件 表示中`;
        if (noRes)   noRes.classList.toggle('visible', visible === 0);
    }

    // ソートヘッダー
    document.querySelectorAll('#rsvpTable th.sortable').forEach(th => {
        th.addEventListener('click', () => {
            const col = th.dataset.col;
            state.dir = state.col === col ? (state.dir === 'asc' ? 'desc' : 'asc') : 'asc';
            state.col = col;
            updateIcons(); applyAll();
        });
    });

    // フィルターボタン
    document.querySelectorAll('.filter-btn-rsvp[data-rsvp]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-btn-rsvp[data-rsvp]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            state.rsvp = btn.dataset.rsvp;
            applyAll();
        });
    });

    // 検索
    searchEl?.addEventListener('input', () => {
        state.q = searchEl.value.toLowerCase().trim();
        clearBtn?.classList.toggle('visible', state.q.length > 0);
        applyAll();
    });
    clearBtn?.addEventListener('click', () => {
        searchEl.value = ''; state.q = '';
        clearBtn.classList.remove('visible');
        searchEl.focus(); applyAll();
    });

    applyAll();
})();

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
