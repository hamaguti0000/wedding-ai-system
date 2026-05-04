@extends('layouts.app')
@section('title', '受付チェックイン | Admin')

@push('styles')
<style>
.ci-wrap { display: grid; gap: 20px; min-width: 0; }
.ci-hero {
    display: flex; justify-content: space-between; gap: 16px; align-items: flex-end;
    background: linear-gradient(180deg, #fffaf3 0%, #fff 100%);
    border: 1px solid #f0e3d2; border-radius: 18px; padding: 24px 26px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.05);
}
.ci-hero h1 { margin: 0; }
.ci-hero p { margin: 8px 0 0; color: #8d7865; font-size: .88rem; line-height: 1.6; max-width: 54ch; }
.ci-quick { position: relative; }
.ci-menu {
    position: relative;
}
.ci-menu > summary {
    list-style: none;
}
.ci-menu > summary::-webkit-details-marker { display: none; }
.ci-menu__button {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px;
    border-radius: 999px; border: 1px solid #e3d3bf; background: #fffdf9;
    color: #5a4637; text-decoration: none; font-size: .86rem; font-weight: 600;
    cursor: pointer; user-select: none;
}
.ci-menu[open] .ci-menu__button { background: #f7efe4; }
.ci-menu__panel {
    position: absolute; top: calc(100% + 8px); right: 0; z-index: 20;
    min-width: 220px; padding: 8px; border-radius: 14px; background: #fffdf9;
    border: 1px solid #e6d6c4; box-shadow: 0 18px 38px rgba(0,0,0,0.12);
}
.ci-menu__panel a {
    display: flex; align-items: center; gap: 8px; padding: 10px 12px;
    border-radius: 10px; color: #5a4637; text-decoration: none; font-size: .85rem;
}
.ci-menu__panel a:hover { background: #f7efe4; }
.ci-menu__panel a i { color: #b38b59; width: 16px; text-align: center; }
.ci-link {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px;
    border-radius: 999px; border: 1px solid #e3d3bf; background: #fffdf9;
    color: #5a4637; text-decoration: none; font-size: .86rem; font-weight: 600;
}
.ci-grid { display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(0, .95fr); gap: 18px; }
.ci-card {
    background: #fff; border: 1px solid #f0e3d2; border-radius: 16px; padding: 20px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.05);
    min-width: 0;
}
.ci-card h2 { margin: 0 0 14px; font-size: 1rem; }
.ci-card__sub { margin: -6px 0 14px; color: #8f7d6f; font-size: .82rem; line-height: 1.6; }
.ci-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.ci-metric {
    border-radius: 14px; padding: 16px; border: 1px solid #f0e3d2; background: #fff;
}
.ci-metric__label { color: #b38b59; font-size: .72rem; letter-spacing: 1.6px; text-transform: uppercase; font-weight: 700; }
.ci-metric__value { margin-top: 10px; font-size: 1.9rem; font-weight: 700; line-height: 1; color: #352820; }
.ci-metric__sub { margin-top: 6px; color: #7f6a57; font-size: .82rem; }
.ci-scanner {
    display: grid; gap: 14px;
}
.ci-video-shell {
    position: relative; border-radius: 18px; overflow: hidden; border: 1px solid #e7d8c5;
    background: #1f1712; aspect-ratio: 4 / 3; min-height: 280px;
}
.ci-video-shell video {
    width: 100%; height: 100%; object-fit: cover; display: block;
}
.ci-video-overlay {
    position: absolute; inset: 14px; border: 1px solid rgba(255,255,255,0.35); border-radius: 14px;
    box-shadow: inset 0 0 0 999px rgba(0,0,0,0.08);
    pointer-events: none;
}
.ci-status {
    display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between;
    min-width: 0;
}
.ci-actions { display: flex; flex-wrap: wrap; gap: 10px; }
.ci-pill {
    display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px;
    background: #f7efe4; color: #725b47; font-size: .8rem; font-weight: 700;
}
.ci-pill.is-live { background: #e4f4ea; color: #2f6e42; }
.ci-pill.is-error { background: #fbf0da; color: #8e632a; }
.ci-form {
    display: grid; grid-template-columns: 1fr auto; gap: 10px;
}
.ci-form input {
    width: 100%; padding: 12px 14px; border-radius: 12px; border: 1px solid #e4d6c3; background: #fff;
    font-size: .92rem;
}
.ci-form input::placeholder { color: #a79280; }
.ci-btn {
    display: inline-flex; align-items: center; gap: 8px; padding: 12px 16px;
    border-radius: 12px; border: 0; background: #b38b59; color: #fff; font-weight: 700; cursor: pointer;
}
.ci-btn--ghost { background: #fffdf9; color: #5a4637; border: 1px solid #e3d3bf; }
.ci-result {
    padding: 14px; border-radius: 14px; border: 1px solid #f0e3d2; background: #fcf8f2; display: grid; gap: 8px;
    min-width: 0;
}
.ci-result__name { font-size: 1.1rem; font-weight: 700; color: #352820; }
.ci-result__meta { color: #7f6a57; font-size: .84rem; word-break: break-word; }
.ci-list { display: grid; gap: 10px; }
.ci-history {
    margin-top: 8px;
    border-top: 1px solid #f0e3d2;
    padding-top: 10px;
}
.ci-history__summary {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    list-style: none; cursor: pointer; color: #8f7d6f; font-size: .82rem; font-weight: 700;
    margin-bottom: 10px;
}
.ci-history__summary::-webkit-details-marker { display: none; }
.ci-history[open] .ci-history__summary i { transform: rotate(180deg); }
.ci-row {
    display: flex; justify-content: space-between; gap: 12px; padding: 12px 14px; border-radius: 12px;
    background: #fcf8f2; border: 1px solid #f2e7d8;
    min-width: 0;
}
.ci-row__name { font-weight: 700; color: #36281d; }
.ci-row__sub { margin-top: 4px; color: #8f7d6f; font-size: .8rem; word-break: break-word; }
.ci-empty { padding: 26px 18px; text-align: center; border-radius: 12px; background: #fcf8f2; border: 1px dashed #e4d6c3; color: #8a7967; }
@media (max-width: 1080px) {
    .ci-grid { grid-template-columns: 1fr; }
    .ci-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 640px) {
    .ci-wrap {
        gap: 12px;
        padding: 0 14px 16px;
    }
    .ci-hero {
        flex-direction: column;
        align-items: flex-start;
        padding: 14px 14px 12px;
        border-radius: 16px;
        background: linear-gradient(180deg, #fffaf3 0%, #fff 100%);
        border: 1px solid #f0e3d2;
        box-shadow: 0 4px 14px rgba(0,0,0,0.05);
    }
    .ci-quick { width: auto; }
    .ci-menu { width: auto; }
    .ci-menu__button { width: auto; justify-content: center; }
    .ci-menu__panel {
        position: static; width: 100%; min-width: 0; margin-top: 8px;
    }
    .ci-summary { display: none; }
    .ci-card {
        padding: 14px;
        border: 1px solid #f0e3d2;
        background: #fff;
        box-shadow: 0 4px 14px rgba(0,0,0,0.05);
        border-radius: 16px;
    }
    .ci-grid { gap: 12px; }
    .ci-card h2 { margin: 0 0 6px; font-size: .92rem; color: #5a4637; }
    .ci-card__sub { margin: 0 0 10px; font-size: .78rem; line-height: 1.55; }
    .ci-video-shell {
        min-height: 220px;
        border-radius: 16px;
        box-shadow: 0 10px 24px rgba(0,0,0,0.08);
    }
    .ci-status { flex-direction: column; align-items: flex-start; gap: 8px; }
    .ci-actions {
        width: 100%;
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .ci-actions .ci-btn,
    .ci-btn--ghost {
        width: 100%;
        max-width: none;
        justify-content: center;
    }
    .ci-btn--ghost { padding-inline: 12px; }
    .ci-form {
        grid-template-columns: 1fr;
        justify-items: stretch;
        gap: 8px;
        width: 100%;
    }
    .ci-form input {
        width: 100%;
        min-width: 0;
        padding: 11px 12px;
        border-radius: 10px;
        font-size: .9rem;
    }
    .ci-form .ci-btn {
        width: 100%;
        min-width: 0;
        max-width: none;
        justify-self: stretch;
        padding: 10px 14px;
    }
    .ci-form,
    .ci-form input,
    .ci-form .ci-btn {
        box-sizing: border-box;
    }
    .ci-result {
        padding: 10px 0 0;
        border: 0;
        background: transparent;
        gap: 4px;
    }
    .ci-result__name { font-size: 1rem; }
    .ci-history {
        margin-top: 10px;
        padding-top: 8px;
    }
    .ci-history__summary {
        font-size: .78rem;
        padding: 6px 0;
    }
    .ci-row {
        flex-direction: column; align-items: flex-start; gap: 6px;
        padding: 10px 0; border: 0; border-bottom: 1px solid #f0e6d8; background: transparent;
    }
    .ci-empty {
        padding: 16px 0;
        background: transparent;
        border: 0;
        border-top: 1px solid #f0e6d8;
        border-bottom: 1px solid #f0e6d8;
        border-radius: 0;
        text-align: left;
    }
}
</style>
@endpush

@section('content')
@php
    $focusProfile = $focusProfile ?? null;
    $focusUser = $focusProfile?->user;
    $focusUrl = $focusProfile ? $focusProfile->checkInUrl() : null;
@endphp

<div class="ci-wrap">
    <section class="ci-hero">
        <div>
            <h1>受付チェックイン</h1>
            <p>QRを読み取って受付済みにします。カメラが使えない場合は、下の入力欄にURLかトークンを貼り付けてください。</p>
        </div>
        <div class="ci-quick">
            <details class="ci-menu">
                <summary class="ci-menu__button">
                    <i class="fa-solid fa-bolt"></i> クイック移動
                    <i class="fa-solid fa-chevron-down"></i>
                </summary>
                <div class="ci-menu__panel">
                    <a href="{{ route('admin.ops') }}"><i class="fa-solid fa-chart-line"></i> 運営ダッシュボード</a>
                    <a href="{{ route('admin.checkin.guests') }}"><i class="fa-solid fa-clipboard-user"></i> 受付一覧</a>
                    <a href="{{ route('admin.users') }}"><i class="fa-solid fa-user-group"></i> ゲスト詳細</a>
                    <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-list-check"></i> RSVP一覧</a>
                    <a href="{{ route('admin.seating') }}"><i class="fa-solid fa-chair"></i> 席次表</a>
                </div>
            </details>
        </div>
    </section>

    <section class="ci-summary">
        <div class="ci-metric">
            <div class="ci-metric__label">Guests</div>
            <div class="ci-metric__value">{{ $summary['total'] }}</div>
            <div class="ci-metric__sub">登録済みゲスト</div>
        </div>
        <div class="ci-metric">
            <div class="ci-metric__label">Checked in</div>
            <div class="ci-metric__value">{{ $summary['checked_in'] }}</div>
            <div class="ci-metric__sub">受付済み</div>
        </div>
        <div class="ci-metric">
            <div class="ci-metric__label">Pending</div>
            <div class="ci-metric__value">{{ $summary['remaining'] }}</div>
            <div class="ci-metric__sub">受付待ちの出席者</div>
        </div>
        <div class="ci-metric">
            <div class="ci-metric__label">Attending</div>
            <div class="ci-metric__value">{{ $summary['attending'] }}</div>
            <div class="ci-metric__sub">出席回答</div>
        </div>
    </section>

    <section class="ci-grid">
        <div class="ci-card">
            <h2>スキャン</h2>
            <p class="ci-card__sub">カメラでQRを写すと、自動で読み取りを試みます。うまくいかない場合は手入力や画像アップロードでも受付できます。</p>
            <div class="ci-scanner">
                <div class="ci-video-shell">
                    <video id="scanVideo" autoplay playsinline muted></video>
                    <div class="ci-video-overlay"></div>
                </div>
                <div class="ci-status">
                    <span class="ci-pill" id="scanState">カメラ待機中</span>
                    <div class="ci-actions">
                        <button type="button" class="ci-btn ci-btn--ghost" id="startScan">
                            <i class="fa-solid fa-camera"></i> カメラ開始
                        </button>
                        <button type="button" class="ci-btn ci-btn--ghost" id="stopScan">
                            <i class="fa-solid fa-ban"></i> 停止
                        </button>
                    </div>
                </div>
                <form class="ci-form" id="manualForm">
                    <input type="text" id="manualToken" placeholder="QRのURLまたはトークンを入力">
                    <button type="submit" class="ci-btn">
                        <i class="fa-solid fa-check"></i> 受付
                    </button>
                </form>
                <label class="ci-btn ci-btn--ghost" for="captureInput" style="justify-content:center;">
                    <i class="fa-solid fa-image"></i> 画像で読む
                </label>
                <input type="file" id="captureInput" accept="image/*" capture="environment" style="display:none;">
                <div id="scanResult"></div>
            </div>
        </div>

        <div class="ci-card">
            @if ($focusProfile)
            <h2>読み取り対象</h2>
            <div class="ci-result">
                <div class="ci-result__name">{{ $focusProfile->fullName() }}</div>
                <div class="ci-result__meta">
                    {{ $focusProfile->user?->username }}
                    @if ($focusProfile->user?->username)
                    ・
                    @endif
                    {{ $focusProfile->participationLabel() }}
                    @if ($focusUser?->seatAssignment?->seat?->seatingTable)
                    ・{{ $focusUser->seatAssignment->seat->seatingTable->name }}
                    @if ($focusUser?->seatAssignment?->seat?->label) / {{ $focusUser->seatAssignment->seat->label }}@endif
                    @endif
                </div>
                <div class="ci-result__meta">
                    {{ $focusProfile->checkInStatusLabel() }}
                    @if ($focusProfile->checkedInBy)
                    ・{{ $focusProfile->checkedInBy->name }}
                    @endif
                </div>
                <form method="POST" action="{{ route('admin.checkin.store', $focusProfile->checkin_token) }}">
                    @csrf
                    <button type="submit" class="ci-btn">
                        <i class="fa-solid fa-check"></i> このゲストを受付済みにする
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.checkin.guests.cancel', $focusProfile) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="ci-btn ci-btn--ghost">
                        <i class="fa-solid fa-rotate-left"></i> 受付を取り消す
                    </button>
                </form>
            </div>
            @endif

            <details class="ci-history" id="recentHistory">
                <summary class="ci-history__summary">
                    <span>最近の受付</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </summary>
                <div class="ci-list" id="recentCheckins">
                    @forelse ($checkedInProfiles as $profile)
                    <div class="ci-row">
                        <div>
                            <div class="ci-row__name">{{ $profile->fullName() }}</div>
                            <div class="ci-row__sub">
                                {{ $profile->checked_in_at?->format('Y/m/d H:i') }}
                                @if ($profile->user?->seatAssignment?->seat?->seatingTable)
                                ・{{ $profile->user->seatAssignment->seat->seatingTable->name }}
                                @if ($profile->user?->seatAssignment?->seat?->label) / {{ $profile->user->seatAssignment->seat->label }}@endif
                                @endif
                            </div>
                        </div>
                        <span class="ci-pill is-live">受付済み</span>
                    </div>
                    @empty
                    <div class="ci-empty">まだ受付記録がありません。</div>
                    @endforelse
                </div>
            </details>
        </div>
    </section>
</div>

<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const storeUrl = @json(route('admin.checkin.store'));
    const scanState = document.getElementById('scanState');
    const scanResult = document.getElementById('scanResult');
    const manualForm = document.getElementById('manualForm');
    const manualToken = document.getElementById('manualToken');
    const captureInput = document.getElementById('captureInput');
    const startBtn = document.getElementById('startScan');
    const stopBtn = document.getElementById('stopScan');
    const video = document.getElementById('scanVideo');
    const recentHistory = document.getElementById('recentHistory');

    let stream = null;
    let canvas = document.createElement('canvas');
    let rafId = null;
    let scanning = false;

    function normalizeToken(value) {
        const raw = String(value || '').trim();
        if (!raw) return '';
        try {
            const url = new URL(raw);
            return url.pathname.split('/').filter(Boolean).pop() || raw;
        } catch (e) {
            return raw.split('/').filter(Boolean).pop() || raw;
        }
    }

    function setState(text, kind = '') {
        scanState.textContent = text;
        scanState.className = 'ci-pill' + (kind ? ' ' + kind : '');
    }

    function renderResult(profile, message) {
        const seat = profile?.table ? `${profile.table}${profile.seat ? ' / ' + profile.seat : ''}` : '未配置';
        scanResult.innerHTML = `
            <div class="ci-result">
                <div class="ci-result__name">${profile?.name ?? ''}</div>
                <div class="ci-result__meta">${message ?? ''}</div>
                <div class="ci-result__meta">${seat}</div>
                <div class="ci-result__meta">${profile?.checked_at ?? ''}</div>
            </div>
        `;
    }

    async function scanImageBlob(blob, filename = 'capture.jpg') {
        const formData = new FormData();
        formData.append('file', blob, filename);

        const res = await fetch(@json(route('admin.checkin.scan')), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf,
            },
            body: formData,
        });

        const data = await res.json().catch(() => ({}));
        return { res, data };
    }

    async function submitToken(rawValue) {
        const token = normalizeToken(rawValue);
        if (!token) {
            setState('QRコードが空です', 'is-error');
            return;
        }

        setState('受付処理中', 'is-live');

        const res = await fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ token }),
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok || !data.success) {
            setState(data.message || '受付に失敗しました', 'is-error');
            return;
        }

        renderResult(data.profile, data.message);
        setState('受付済み', 'is-live');
        recentHistory?.setAttribute('open', 'open');
        stopScanning();
    }

    async function startScanning() {
        if (!navigator.mediaDevices?.getUserMedia) {
            setState('この端末ではカメラが使えません', 'is-error');
            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } },
                audio: false,
            });
            video.srcObject = stream;
            await video.play();
            scanning = true;
            setState('読み取り中', 'is-live');
            scanLoop();
        } catch (e) {
            setState('カメラを起動できませんでした。画像読み取りか手入力を使ってください', 'is-error');
        }
    }

    function stopScanning() {
        scanning = false;
        if (rafId) clearTimeout(rafId);
        rafId = null;
        if (stream) {
            stream.getTracks().forEach((track) => track.stop());
            stream = null;
        }
        if (video) video.srcObject = null;
        if (scanState.textContent !== '受付済み') {
            setState('カメラ待機中');
        }
    }

    async function scanLoop() {
        if (!scanning || !video || !canvas) return;
        try {
            if (video.readyState >= 2 && video.videoWidth && video.videoHeight) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d', { willReadFrequently: true });
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.85));
                if (blob) {
                    const { res, data } = await scanImageBlob(blob);
                    if (res.ok && data.success && data.token) {
                        await submitToken(data.token);
                        return;
                    }
                }
            }
        } catch (e) {
            // no-op
        }
        rafId = setTimeout(scanLoop, 1400);
    }

    startBtn?.addEventListener('click', startScanning);
    stopBtn?.addEventListener('click', stopScanning);

    captureInput?.addEventListener('change', async (event) => {
        const file = event.target.files?.[0];
        if (!file) return;
        setState('画像を解析中', 'is-live');
        const { res, data } = await scanImageBlob(file, file.name || 'capture.jpg');
        if (!res.ok || !data.success || !data.token) {
            setState(data.message || 'QRコードを読み取れませんでした', 'is-error');
            return;
        }
        await submitToken(data.token);
    });

    manualForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        await submitToken(manualToken.value);
    });

    if (recentHistory && window.matchMedia('(min-width: 641px)').matches) {
        recentHistory.setAttribute('open', 'open');
    }
})();
</script>
@endsection
