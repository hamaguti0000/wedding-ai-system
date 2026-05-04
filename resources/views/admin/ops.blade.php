@extends('layouts.app')
@section('title', '運営ダッシュボード | Admin')

@push('styles')
<style>
.ops-wrap { display: grid; gap: 22px; }
.ops-hero {
    display: flex; justify-content: space-between; gap: 16px; align-items: flex-end;
    background: linear-gradient(180deg, #fffaf4 0%, #fff 100%);
    border: 1px solid #f0e3d2; border-radius: 16px; padding: 24px 26px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.05);
}
.ops-hero h1 { margin: 0; }
.ops-hero__sub { margin: 8px 0 0; color: #8d7865; font-size: .88rem; }
.ops-links { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-start; }
.ops-link {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px;
    border-radius: 999px; border: 1px solid #e3d3bf; background: #fffdf9;
    color: #5a4637; text-decoration: none; font-size: .86rem; font-weight: 600;
}
.ops-link--primary { background: #b38b59; color: #fff; border-color: #b38b59; }
.ops-menu {
    position: relative;
}
.ops-menu > summary {
    list-style: none;
}
.ops-menu > summary::-webkit-details-marker { display: none; }
.ops-menu__button {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px;
    border-radius: 999px; border: 1px solid #e3d3bf; background: #fffdf9;
    color: #5a4637; font-size: .86rem; font-weight: 600; cursor: pointer; user-select: none;
}
.ops-menu[open] .ops-menu__button { background: #f7efe4; }
.ops-menu__panel {
    position: absolute; top: calc(100% + 8px); left: 0; z-index: 20;
    min-width: 240px; padding: 8px; border-radius: 14px; background: #fffdf9;
    border: 1px solid #e6d6c4; box-shadow: 0 18px 38px rgba(0,0,0,0.12);
}
.ops-menu__panel a {
    display: flex; align-items: center; gap: 8px; padding: 10px 12px;
    border-radius: 10px; color: #5a4637; text-decoration: none; font-size: .85rem;
}
.ops-menu__panel a:hover { background: #f7efe4; }
.ops-menu__panel a i { color: #b38b59; width: 16px; text-align: center; }
.ops-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.ops-card {
    background: #fff; border: 1px solid #f1e5d6; border-radius: 14px; padding: 18px 18px 16px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.05); min-height: 122px;
}
.ops-card__eyebrow { color: #b38b59; font-size: .72rem; letter-spacing: 1.6px; text-transform: uppercase; font-weight: 700; }
.ops-card__value { font-size: 2rem; font-weight: 700; margin-top: 10px; color: #352820; line-height: 1; }
.ops-card__label { margin-top: 6px; color: #7f6a57; font-size: .82rem; }
.ops-card__meta { margin-top: 10px; color: #a09180; font-size: .76rem; }
.ops-panels { display: grid; grid-template-columns: 1.25fr .95fr; gap: 18px; }
.ops-panel {
    background: #fff; border: 1px solid #f0e3d2; border-radius: 16px; padding: 20px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.05);
}
.ops-panel--wide { margin-top: 18px; }
.ops-panel h2 { margin: 0 0 14px; font-size: 1rem; }
.ops-list { display: grid; gap: 10px; }
.ops-item {
    display: flex; justify-content: space-between; gap: 14px; padding: 12px 14px;
    border-radius: 12px; background: #fcf8f2; border: 1px solid #f2e7d8;
}
.ops-item--audit { align-items: flex-start; }
.ops-item__name { font-weight: 600; color: #36281d; }
.ops-item__sub { margin-top: 4px; color: #8f7d6f; font-size: .8rem; }
.ops-item__badges { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 6px; align-self: flex-start; }
.ops-pill { align-self: flex-start; white-space: nowrap; font-size: .76rem; font-weight: 700; padding: 6px 10px; border-radius: 999px; background: #efe2d1; color: #725b47; }
.ops-pill.is-checkin { background: #e4f4ea; color: #2f6e42; }
.ops-pill.is-pending { background: #fbf0da; color: #8e632a; }
.ops-pill.is-cancel { background: #fdecec; color: #b42318; }
.ops-pill.is-manual { background: #eef2ff; color: #4f46e5; }
.ops-pill.is-qr { background: #f3e8ff; color: #7c3aed; }
.ops-empty {
    padding: 28px 18px; text-align: center; border-radius: 12px;
    background: #fcf8f2; border: 1px dashed #e4d6c3; color: #8a7967;
}
@media (max-width: 1100px) {
    .ops-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ops-panels { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .ops-hero { align-items: flex-start; flex-direction: column; }
    .ops-grid { grid-template-columns: 1fr; }
    .ops-links { width: 100%; }
    .ops-menu { width: 100%; }
    .ops-menu__button,
    .ops-link { width: 100%; justify-content: center; }
    .ops-menu__panel {
        position: static; width: 100%; min-width: 0; margin-top: 8px;
    }
    .ops-panel--wide { margin-top: 12px; }
}
</style>
@endpush

@section('content')
<div class="ops-wrap">
    <section class="ops-hero">
        <div>
            <h1>運営ダッシュボード</h1>
            <p class="ops-hero__sub">受付、出欠、席次をひとつの画面で追えるようにした管理用ビューです。</p>
        </div>
        <div class="ops-links">
            <a href="{{ route('admin.checkin.index') }}" class="ops-link ops-link--primary">
                <i class="fa-solid fa-qrcode"></i> 受付チェックイン
            </a>
            <details class="ops-menu">
                <summary class="ops-menu__button">
                    <i class="fa-solid fa-layer-group"></i> 管理メニュー
                    <i class="fa-solid fa-chevron-down"></i>
                </summary>
                <div class="ops-menu__panel">
                    <a href="{{ route('admin.checkin.guests') }}"><i class="fa-solid fa-clipboard-user"></i> 受付一覧</a>
                    <a href="{{ route('admin.users') }}"><i class="fa-solid fa-user-group"></i> ゲスト詳細</a>
                    <a href="{{ route('admin.rsvp') }}"><i class="fa-solid fa-envelope-open-text"></i> RSVP一覧</a>
                    <a href="{{ route('admin.seating') }}"><i class="fa-solid fa-chair"></i> 席次表</a>
                    <a href="{{ route('admin.login-history') }}"><i class="fa-solid fa-clock-rotate-left"></i> ログイン履歴</a>
                    <a href="{{ route('admin.audit.checkin') }}"><i class="fa-solid fa-clipboard-list"></i> 操作ログ</a>
                </div>
            </details>
        </div>
    </section>

    <section class="ops-grid" id="opsSummary">
        <div class="ops-card">
            <div class="ops-card__eyebrow">Guests</div>
            <div class="ops-card__value" data-metric="total">{{ $summary['total'] }}</div>
            <div class="ops-card__label">登録済みゲスト</div>
        </div>
        <div class="ops-card">
            <div class="ops-card__eyebrow">RSVP</div>
            <div class="ops-card__value" data-metric="attending">{{ $summary['attending'] }}</div>
            <div class="ops-card__label">出席回答</div>
            <div class="ops-card__meta" data-metric="response_rate">回答率 {{ $summary['response_rate'] }}%</div>
        </div>
        <div class="ops-card">
            <div class="ops-card__eyebrow">Check-in</div>
            <div class="ops-card__value" data-metric="checked_in_guests">{{ $summary['checked_in_guests'] }}</div>
            <div class="ops-card__label">受付済みゲスト</div>
            <div class="ops-card__meta" data-metric="checkin_pending">受付待ち {{ $summary['checkin_pending'] }}名</div>
        </div>
        <div class="ops-card">
            <div class="ops-card__eyebrow">Seating</div>
            <div class="ops-card__value" data-metric="seat_assigned">{{ $summary['seat_assigned'] }}</div>
            <div class="ops-card__label">席配置済み</div>
            <div class="ops-card__meta" data-metric="checked_in_people">受付人数 {{ $summary['checked_in_people'] }}名</div>
        </div>
    </section>

    <section class="ops-panels">
        <div class="ops-panel">
            <h2>直近の受付</h2>
            <div class="ops-list" id="recentCheckins">
                @forelse ($recentCheckins as $item)
                <div class="ops-item">
                    <div>
                        <div class="ops-item__name">{{ $item['name'] }}</div>
                        <div class="ops-item__sub">
                            {{ $item['checked_at'] }}
                            @if ($item['table'])
                            ・{{ $item['table'] }}@if ($item['seat']) / {{ $item['seat'] }}@endif
                            @endif
                            @if ($item['by'])
                            ・{{ $item['by'] }}
                            @endif
                        </div>
                    </div>
                    <span class="ops-pill is-checkin">受付済み</span>
                </div>
                @empty
                <div class="ops-empty">まだ受付記録がありません。</div>
                @endforelse
            </div>
        </div>

        <div class="ops-panel">
            <h2>直近の回答</h2>
            <div class="ops-list" id="recentResponses">
                @forelse ($recentResponses as $item)
                <div class="ops-item">
                    <div>
                        <div class="ops-item__name">{{ $item['name'] }}</div>
                        <div class="ops-item__sub">
                            {{ $item['responded'] ?: '未回答' }}
                            @if ($item['table'])
                            ・{{ $item['table'] }}
                            @endif
                        </div>
                    </div>
                    <span class="ops-pill {{ $item['checkin'] === '受付済み' ? 'is-checkin' : 'is-pending' }}">
                        {{ $item['checkin'] }}
                    </span>
                </div>
                @empty
                <div class="ops-empty">まだ回答がありません。</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="ops-panel ops-panel--wide">
        <h2>操作ログ</h2>
        <div class="ops-list" id="recentOperationLogs">
            @forelse ($recentOperationLogs as $item)
            <div class="ops-item ops-item--audit">
                <div>
                    <div class="ops-item__name">{{ $item['guest'] ?? '—' }}</div>
                    <div class="ops-item__sub">
                        {{ $item['message'] }}
                        @if ($item['actor'])
                            ・{{ $item['actor'] }}
                        @endif
                        @if ($item['at'])
                            ・{{ $item['at'] }}
                        @endif
                    </div>
                </div>
                <div class="ops-item__badges">
                    <span class="ops-pill {{ $item['action'] === 'cancel' ? 'is-cancel' : ($item['action'] === 'check_in' ? 'is-checkin' : 'is-manual') }}">
                        {{ $item['action'] === 'cancel' ? '取消' : ($item['action'] === 'check_in' ? '受付' : '更新') }}
                    </span>
                    @if ($item['source'])
                        <span class="ops-pill {{ $item['source'] === 'qr' ? 'is-qr' : 'is-manual' }}">
                            {{ $item['source'] === 'qr' ? 'QR' : ($item['source'] === 'profile' ? 'プロフィール' : ($item['source'] === 'rsvp' ? 'RSVP' : '管理')) }}
                        </span>
                    @endif
                </div>
            </div>
            @empty
            <div class="ops-empty">まだ操作ログがありません。</div>
            @endforelse
        </div>
    </section>
</div>

<script>
(function () {
    const liveUrl = @json(route('admin.ops.live'));
    const summaryEls = {
        total: document.querySelector('[data-metric="total"]'),
        attending: document.querySelector('[data-metric="attending"]'),
        response_rate: document.querySelector('[data-metric="response_rate"]'),
        checked_in_guests: document.querySelector('[data-metric="checked_in_guests"]'),
        checked_in_people: document.querySelector('[data-metric="checked_in_people"]'),
        seat_assigned: document.querySelector('[data-metric="seat_assigned"]'),
        checkin_pending: document.querySelector('[data-metric="checkin_pending"]'),
    };
    const recentCheckins = document.getElementById('recentCheckins');
    const recentResponses = document.getElementById('recentResponses');
    const recentOperationLogs = document.getElementById('recentOperationLogs');

    function pillClass(label) {
        return label === '受付済み' ? 'ops-pill is-checkin' : 'ops-pill is-pending';
    }

    function operationActionClass(action) {
        if (action === 'cancel') return 'ops-pill is-cancel';
        if (action === 'check_in') return 'ops-pill is-checkin';
        return 'ops-pill is-manual';
    }

    function operationSourceClass(source) {
        return source === 'qr' ? 'ops-pill is-qr' : 'ops-pill is-manual';
    }

    function renderList(container, items, emptyText, type) {
        if (!container) return;
        if (!items || !items.length) {
            container.innerHTML = '<div class="ops-empty">' + emptyText + '</div>';
            return;
        }

        container.innerHTML = items.map((item) => {
            if (type === 'checkins') {
                return `
                    <div class="ops-item">
                        <div>
                            <div class="ops-item__name">${item.name ?? ''}</div>
                            <div class="ops-item__sub">${item.checked_at ?? ''}${item.table ? ' ・' + item.table : ''}${item.seat ? ' / ' + item.seat : ''}${item.by ? ' ・' + item.by : ''}</div>
                        </div>
                        <span class="ops-pill is-checkin">受付済み</span>
                    </div>
                `;
            }

            if (type === 'responses') {
                return `
                    <div class="ops-item">
                        <div>
                            <div class="ops-item__name">${item.name ?? ''}</div>
                            <div class="ops-item__sub">${item.responded ?? '未回答'}${item.table ? ' ・' + item.table : ''}</div>
                        </div>
                        <span class="${pillClass(item.checkin)}">${item.checkin ?? ''}</span>
                    </div>
                `;
            }

            return `
                <div class="ops-item ops-item--audit">
                    <div>
                        <div class="ops-item__name">${item.guest ?? ''}</div>
                        <div class="ops-item__sub">${item.message ?? ''}${item.actor ? ' ・' + item.actor : ''}${item.at ? ' ・' + item.at : ''}</div>
                    </div>
                    <div class="ops-item__badges">
                        <span class="${operationActionClass(item.action)}">${item.action === 'cancel' ? '取消' : (item.action === 'check_in' ? '受付' : '更新')}</span>
                        ${item.source ? `<span class="${operationSourceClass(item.source)}">${item.source === 'qr' ? 'QR' : (item.source === 'profile' ? 'プロフィール' : (item.source === 'rsvp' ? 'RSVP' : '管理'))}</span>` : ''}
                    </div>
                </div>
            `;
        }).join('');
    }

    async function refresh() {
        try {
            const res = await fetch(liveUrl, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();

            if (data.summary) {
                Object.entries(summaryEls).forEach(([key, el]) => {
                    if (!el || data.summary[key] === undefined) return;
                    if (key === 'response_rate' || key === 'checkin_pending') {
                        const suffix = key === 'response_rate' ? '%' : '名';
                        el.textContent = key === 'response_rate'
                            ? `回答率 ${data.summary[key]}%`
                            : `受付待ち ${data.summary[key]}名`;
                    } else if (key === 'checked_in_people') {
                        el.textContent = `受付人数 ${data.summary[key]}名`;
                    } else {
                        el.textContent = data.summary[key];
                    }
                });
            }

            renderList(recentCheckins, data.recentCheckins || [], 'まだ受付記録がありません。', 'checkins');
            renderList(recentResponses, data.recentResponses || [], 'まだ回答がありません。', 'responses');
            renderList(recentOperationLogs, data.recentOperationLogs || [], 'まだ操作ログがありません。', 'audit');
        } catch (e) {
            // 画面の更新は失敗しても壊さない
        }
    }

    setInterval(refresh, 10000);
    refresh();
})();
</script>
@endsection
