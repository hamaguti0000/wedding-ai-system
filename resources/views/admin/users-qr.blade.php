@extends('layouts.app')
@section('title', '受付QR | Admin')

@push('styles')
<style>
.qr-page { display: grid; gap: 20px; }
.qr-hero {
    display: flex; justify-content: space-between; gap: 16px; align-items: flex-end;
    background: linear-gradient(180deg, #fffaf3 0%, #fff 100%);
    border: 1px solid #f0e3d2; border-radius: 18px; padding: 24px 26px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.05);
}
.qr-hero h1 { margin: 0; }
.qr-hero p { margin: 8px 0 0; color: #8d7865; font-size: .88rem; }
.qr-actions { display: flex; flex-wrap: wrap; gap: 10px; }
.qr-link {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px;
    border-radius: 999px; border: 1px solid #e3d3bf; background: #fffdf9;
    color: #5a4637; text-decoration: none; font-size: .86rem; font-weight: 600;
}
.qr-grid { display: grid; grid-template-columns: .95fr 1.05fr; gap: 18px; }
.qr-card {
    background: #fff; border: 1px solid #f0e3d2; border-radius: 16px; padding: 20px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.05);
}
.qr-card h2 { margin: 0 0 14px; font-size: 1rem; }
.qr-main {
    display: grid; gap: 14px; justify-items: center; text-align: center;
}
.qr-main img {
    width: min(100%, 360px); border-radius: 18px; background: #fff; padding: 12px;
    box-shadow: inset 0 0 0 1px #f0e3d2;
}
.qr-name { font-size: 1.35rem; font-weight: 800; color: #352820; }
.qr-meta { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; }
.qr-chip {
    display: inline-flex; align-items: center; gap: 6px; padding: 7px 11px; border-radius: 999px;
    background: #f7efe4; color: #725b47; font-size: .8rem; font-weight: 700;
}
.qr-url {
    width: 100%; padding: 10px 12px; border-radius: 12px; border: 1px solid #e4d6c3;
    background: #fff; color: #6b5847; font-size: .8rem; word-break: break-all;
}
.qr-side { display: grid; gap: 12px; }
.qr-side__box {
    padding: 14px; border-radius: 12px; background: #fcf8f2; border: 1px solid #f2e7d8;
}
.qr-side__label { color: #a08f7d; font-size: .74rem; letter-spacing: .06em; text-transform: uppercase; margin-bottom: 6px; }
.qr-side__value { color: #36281d; font-weight: 700; line-height: 1.5; }
.qr-note {
    color: #8f7d6f; font-size: .84rem; line-height: 1.7;
}
@media (max-width: 1080px) {
    .qr-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .qr-hero { flex-direction: column; align-items: flex-start; }
}
</style>
@endpush

@section('content')
@php
    $profile = $user->guestProfile;
    $checkInUrl = $profile?->checkInUrl();
    $qrUrl = $checkInUrl ? 'https://api.qrserver.com/v1/create-qr-code/?size=420x420&data=' . urlencode($checkInUrl) : null;
@endphp

<div class="qr-page">
    <section class="qr-hero">
        <div>
            <h1>受付QR</h1>
            <p>受付スタッフへ渡すための専用画面です。QRだけを大きく出し、迷わず読めるようにしています。</p>
        </div>
        <div class="qr-actions">
            <a href="{{ route('admin.users.show', $user->id) }}" class="qr-link">
                <i class="fa-solid fa-id-card"></i> 詳細
            </a>
            <a href="{{ route('admin.checkin.index') }}" class="qr-link">
                <i class="fa-solid fa-qrcode"></i> 受付画面
            </a>
            <a href="{{ route('admin.users.edit', $user->id) }}" class="qr-link">
                <i class="fa-solid fa-pen-to-square"></i> 編集
            </a>
        </div>
    </section>

    <section class="qr-grid">
        <div class="qr-card">
            <h2>表示中のQR</h2>
            <div class="qr-main">
                @if ($qrUrl)
                <img src="{{ $qrUrl }}" alt="受付QRコード">
                @endif
                <div class="qr-name">{{ $profile?->fullName() ?: $user->name }}</div>
                <div class="qr-meta">
                    <span class="qr-chip">{{ $user->username }}</span>
                    <span class="qr-chip">{{ $profile?->checkInStatusLabel() ?? '—' }}</span>
                    <span class="qr-chip">{{ $profile?->participationLabel() ?? '—' }}</span>
                </div>
                <div class="qr-url" id="qrUrlValue">{{ $checkInUrl }}</div>
                <button type="button" class="qr-link" id="copyQrUrl">
                    <i class="fa-regular fa-copy"></i> URLをコピー
                </button>
            </div>
        </div>

        <div class="qr-card">
            <h2>受付情報</h2>
            <div class="qr-side">
                <div class="qr-side__box">
                    <div class="qr-side__label">席次表</div>
                    <div class="qr-side__value">
                        @if ($user->seatAssignment?->seat?->seatingTable)
                        {{ $user->seatAssignment->seat->seatingTable->name }}
                        @if ($user->seatAssignment?->seat?->label) / {{ $user->seatAssignment->seat->label }}@endif
                        @else
                        未配置
                        @endif
                    </div>
                </div>
                <div class="qr-side__box">
                    <div class="qr-side__label">受付状態</div>
                    <div class="qr-side__value">
                        {{ $profile?->checkInStatusLabel() ?? '—' }}
                        @if ($profile?->checkedInBy)
                        / {{ $profile->checkedInBy->name }}
                        @endif
                    </div>
                </div>
                <div class="qr-side__box">
                    <div class="qr-side__label">案内</div>
                    <div class="qr-note">
                        当日はこの画面を開いておくか、QR画像を表示してください。
                        受付画面で読み取ると、受付済みの状態へ即時に反映されます。
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
(function () {
    const btn = document.getElementById('copyQrUrl');
    const value = document.getElementById('qrUrlValue')?.textContent?.trim();
    if (!btn || !value) return;
    btn.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(value);
            btn.innerHTML = '<i class="fa-solid fa-check"></i> コピーしました';
            setTimeout(() => {
                btn.innerHTML = '<i class="fa-regular fa-copy"></i> URLをコピー';
            }, 1200);
        } catch (e) {}
    });
})();
</script>
@endsection
