@extends('layouts.app')
@section('title', 'ゲスト詳細 | Admin')

@push('styles')
<style>
.guest-show { display: grid; gap: 20px; }
.guest-show__hero {
    display: flex; justify-content: space-between; gap: 18px; align-items: flex-start;
    background: linear-gradient(180deg, #fffaf3 0%, #fff 100%);
    border: 1px solid #f0e3d2; border-radius: 18px; padding: 24px 26px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.05);
}
.guest-show__hero-main { display: flex; gap: 18px; align-items: center; }
.guest-show .pf-avatar {
    width: 88px; height: 88px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; font-weight: 800; font-size: 2rem; color: #fff;
    background: linear-gradient(135deg, #b38b59 0%, #d4a870 100%);
    border: var(--avatar-border-width, 3px) solid var(--avatar-border-color, #f0e4d0);
    flex-shrink: 0;
}
.guest-show .pf-avatar img,
.guest-show .pf-avatar .pf-avatar-emoji {
    width: 100%; height: 100%; object-fit: cover;
}
.guest-show .pf-avatar .pf-avatar-emoji {
    display: flex; align-items: center; justify-content: center;
    font-size: 2.1rem; line-height: 1;
}
.guest-show__name { margin: 0; font-size: 1.8rem; line-height: 1.15; }
.guest-show__meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.guest-chip {
    display: inline-flex; align-items: center; gap: 6px; padding: 7px 11px; border-radius: 999px;
    background: #f7efe4; color: #725b47; font-size: .8rem; font-weight: 700;
}
.guest-chip--live { background: #e4f4ea; color: #2f6e42; }
.guest-chip--warn { background: #fbf0da; color: #8e632a; }
.guest-show__actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; }
.guest-btn {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px;
    border-radius: 12px; text-decoration: none; font-size: .86rem; font-weight: 700;
    border: 1px solid #e2d3bf; background: #fffdf9; color: #5a4637;
}
.guest-btn--primary { background: #b38b59; border-color: #b38b59; color: #fff; }
.guest-grid { display: grid; grid-template-columns: 1.1fr .9fr; gap: 18px; }
.guest-card {
    background: #fff; border: 1px solid #f0e3d2; border-radius: 16px; padding: 20px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.05);
}
.guest-card h2 { margin: 0 0 14px; font-size: 1rem; }
.guest-dl { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.guest-item { padding: 12px 14px; border-radius: 12px; background: #fcf8f2; border: 1px solid #f2e7d8; }
.guest-item dt { font-size: .74rem; color: #a08f7d; letter-spacing: .06em; text-transform: uppercase; margin-bottom: 6px; }
.guest-item dd { margin: 0; color: #36281d; font-weight: 600; line-height: 1.55; }
.guest-note { grid-column: 1 / -1; }
.qr-wrap { display: grid; gap: 14px; }
.qr-card {
    display: grid; gap: 14px; justify-items: center; text-align: center;
    padding: 18px; border-radius: 16px; background: #fcf8f2; border: 1px solid #f2e7d8;
}
.qr-card img { width: 220px; height: 220px; border-radius: 16px; background: #fff; padding: 12px; box-shadow: inset 0 0 0 1px #f0e3d2; }
.qr-url {
    width: 100%; padding: 10px 12px; border-radius: 12px; border: 1px solid #e4d6c3;
    background: #fff; color: #6b5847; font-size: .8rem; word-break: break-all;
}
.qr-empty {
    width: 220px; min-height: 220px; display: flex; align-items: center; justify-content: center;
    border-radius: 16px; border: 1px dashed #e4d6c3; color: #8a7967; background: #fff;
}
.timeline { display: grid; gap: 10px; }
.timeline-item {
    display: flex; justify-content: space-between; gap: 12px; padding: 12px 14px;
    border-radius: 12px; background: #fcf8f2; border: 1px solid #f2e7d8;
}
.timeline-item__label { font-weight: 700; color: #36281d; }
.timeline-item__sub { color: #8f7d6f; font-size: .8rem; margin-top: 4px; }
@media (max-width: 1080px) {
    .guest-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .guest-show__hero { flex-direction: column; }
    .guest-show__hero-main { align-items: flex-start; }
    .guest-dl { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
@php
    $profile = $user->guestProfile;
    $avatarType = $user->avatarType();
    $avatarEmoji = $user->avatar_emoji;
    $avatarBgColor = $user->avatarBackgroundColor();
    $avatarBorderColor = $user->avatarBorderColor();
    $avatarBorderWidth = $user->avatarBorderWidth();
    $avatarImageUrl = $user->avatarImageUrl();
    $initial = $user->avatarInitial();
    $checkInUrl = $profile ? $profile->checkInUrl() : null;
    $qrUrl = $checkInUrl ? 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($checkInUrl) : null;
@endphp

<div class="guest-show">
    <section class="guest-show__hero">
        <div class="guest-show__hero-main">
            <div class="pf-avatar" aria-hidden="true" style="--avatar-border-color: {{ $avatarBorderColor }}; --avatar-border-width: {{ $avatarBorderWidth }}px; width: 88px; height: 88px;">
                @if ($avatarType === \App\Models\User::AVATAR_PHOTO && $avatarImageUrl)
                    <img src="{{ $avatarImageUrl }}" alt="">
                @elseif ($avatarType === \App\Models\User::AVATAR_EMOJI && $avatarEmoji)
                    <span class="pf-avatar-emoji" style="background: {{ $avatarBgColor }};">{{ $avatarEmoji }}</span>
                @else
                    {{ $initial }}
                @endif
            </div>
            <div>
                <p class="guest-show__name">{{ $user->name }}</p>
                <div class="guest-show__meta">
                    <span class="guest-chip">{{ $user->username }}</span>
                    <span class="guest-chip">{{ $user->isAdmin() ? '管理者' : 'ゲスト' }}</span>
                    @if ($profile)
                    <span class="guest-chip {{ $profile->isCheckedIn() ? 'guest-chip--live' : 'guest-chip--warn' }}">
                        {{ $profile->checkInStatusLabel() }}
                    </span>
                    <span class="guest-chip">{{ $profile->participationLabel() }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="guest-show__actions">
            <a href="{{ route('admin.users') }}" class="guest-btn">
                <i class="fa-solid fa-arrow-left"></i> 一覧へ
            </a>
            <a href="{{ route('admin.users.edit', $user->id) }}" class="guest-btn guest-btn--primary">
                <i class="fa-solid fa-pen-to-square"></i> 編集する
            </a>
            @if ($profile && $checkInUrl)
            <a href="{{ route('admin.users.qr', $user->id) }}" class="guest-btn">
                <i class="fa-solid fa-qrcode"></i> 受付QR
            </a>
            @endif
            {{-- パスワードを書き換えずに、このゲストに見えている画面をそのまま確認する --}}
            @unless ($user->isAdmin())
            <form method="POST" action="{{ route('admin.users.impersonate', $user->id) }}"
                  onsubmit="return confirm('{{ $user->name }} としてサイトを表示します。よろしいですか？');">
                @csrf
                <button type="submit" class="guest-btn">
                    <i class="fa-solid fa-user-secret"></i> この人の画面を見る
                </button>
            </form>
            @endunless
        </div>
    </section>

    <section class="guest-grid">
        <div class="guest-card">
            <h2>基本情報</h2>
            <dl class="guest-dl">
                <div class="guest-item">
                    <dt>氏名</dt>
                    <dd>{{ $profile?->fullName() ?: $user->name }}</dd>
                </div>
                <div class="guest-item">
                    <dt>フリガナ</dt>
                    <dd>{{ $profile?->furigana() ?: '—' }}</dd>
                </div>
                <div class="guest-item">
                    <dt>お立場</dt>
                    <dd>{{ $profile?->guestSideLabel() ?: '—' }}</dd>
                </div>
                <div class="guest-item">
                    <dt>ご関係</dt>
                    <dd>{{ $profile?->relationshipLabel() ?: '—' }}</dd>
                </div>
                <div class="guest-item">
                    <dt>出欠</dt>
                    <dd>{{ $profile?->participationLabel() ?: '—' }}</dd>
                </div>
                <div class="guest-item">
                    <dt>回答日時</dt>
                    <dd>{{ $profile?->responded_at?->format('Y/m/d H:i') ?: '—' }}</dd>
                </div>
                <div class="guest-item">
                    <dt>電話番号</dt>
                    <dd>{{ $profile?->phone ?: '—' }}</dd>
                </div>
                <div class="guest-item">
                    <dt>受付</dt>
                    <dd>{{ $profile?->checkInStatusLabel() ?? '—' }}</dd>
                </div>
                <div class="guest-item guest-note">
                    <dt>ご住所</dt>
                    <dd>
                        @if ($profile?->postal_code)〒{{ $profile->postal_code }} @endif
                        {{ $profile?->address ?: '—' }}
                    </dd>
                </div>
                <div class="guest-item guest-note">
                    <dt>メモ</dt>
                    <dd>{{ $profile?->notes ?: '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="qr-wrap">
            <div class="guest-card">
                <h2>チェックインQR</h2>
                @if ($profile && $qrUrl)
                <div class="qr-card">
                    <img src="{{ $qrUrl }}" alt="受付QRコード">
                    <div>
                        <div style="font-weight:700;color:#36281d;">{{ $profile->checkInStatusLabel() }}</div>
                        <div style="color:#8f7d6f;font-size:.82rem;margin-top:4px;">QRを読み取ると受付画面へ進みます。</div>
                    </div>
                    <div class="qr-url" id="checkInUrl">{{ $checkInUrl }}</div>
                    <button type="button" class="guest-btn" id="copyCheckinUrl">
                        <i class="fa-regular fa-copy"></i> URLをコピー
                    </button>
                </div>
                @else
                <div class="qr-empty">受付QRを作成できません。</div>
                @endif
            </div>

            <div class="guest-card">
                <h2>席・参加情報</h2>
                <div class="timeline">
                    <div class="timeline-item">
                        <div>
                            <div class="timeline-item__label">席次表</div>
                            <div class="timeline-item__sub">
                                @if ($user->seatAssignment?->seat?->seatingTable)
                                {{ $user->seatAssignment->seat->seatingTable->name }}
                                @if ($user->seatAssignment?->seat?->label) / {{ $user->seatAssignment->seat->label }}@endif
                                @else
                                未配置
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div>
                            <div class="timeline-item__label">出席人数</div>
                            <div class="timeline-item__sub">
                                @if ($profile?->isAttending())
                                合計 {{ $profile->attending_count }}名
                                @if ($profile->children_count > 0)
                                / お子様 {{ $profile->children_count }}名
                                @endif
                                @else
                                —
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div>
                            <div class="timeline-item__label">アレルギー</div>
                            <div class="timeline-item__sub">
                                @if ($profile?->has_allergy)
                                あり
                                @if ($profile->allergy_notes) / {{ $profile->allergy_notes }}@endif
                                @else
                                なし
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div>
                            <div class="timeline-item__label">チェックイン担当</div>
                            <div class="timeline-item__sub">{{ $profile?->checkedInBy?->name ?: '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@if ($profile && $qrUrl)
<script>
(function () {
    const btn = document.getElementById('copyCheckinUrl');
    const value = document.getElementById('checkInUrl')?.textContent?.trim();
    if (!btn || !value) return;
    btn.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(value);
            btn.textContent = 'コピーしました';
            setTimeout(() => {
                btn.innerHTML = '<i class="fa-regular fa-copy"></i> URLをコピー';
            }, 1200);
        } catch (e) {}
    });
})();
</script>
@endif
@endsection
