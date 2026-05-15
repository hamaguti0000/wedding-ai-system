@extends('layouts.app')
@section('title', 'プロフィール | Kakeru & Mirai Wedding')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endpush

@section('content')

@php
    $checkInUrl = $checkInUrl ?? null;
    $avatarType = $user->avatarType();
    $avatarEmoji = $user->avatar_emoji;
    $avatarBgColor = $user->avatarBackgroundColor();
    $avatarBorderColor = $user->avatarBorderColor();
    $avatarBorderWidth = $user->avatarBorderWidth();
    $avatarImageUrl = $user->avatarImageUrl();
    $initial = $user->avatarInitial();
    $checkInQrUrl = $checkInUrl ? 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=' . urlencode($checkInUrl) : null;
@endphp

<div class="pf-wrap">

    {{-- サクセスメッセージ --}}
    @if (session('success'))
    <div class="pf-alert-success">{{ session('success') }}</div>
    @endif

    {{-- 確認メール送信済み通知 --}}
    @if (session('email_verification_sent'))
    <div class="pf-alert-success" style="background:#f0faf4;color:#2d6a4f;border-color:#a8d8b9;">
        <strong>確認メールを送信しました。</strong>
        メールボックスをご確認いただき、リンクをクリックして認証を完了してください。
    </div>
    @endif

    {{-- メール未確認バナー --}}
    @if ($user->isEmailUnverified())
    <div class="pf-alert-success" style="background:#fff8f0;color:#7a4f2a;border-color:#e8c8a5;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <span>
            <strong>{{ $user->email }}</strong> は未確認です。
            確認メールが届いていない場合は再送できます。
        </span>
        <form method="POST" action="{{ route('email.verify.resend') }}" style="margin:0;">
            @csrf
            <button type="submit"
                style="padding:6px 16px;background:#b38b59;color:#fff;border:none;border-radius:3px;font-size:0.8rem;cursor:pointer;font-family:'Noto Sans JP',sans-serif;white-space:nowrap;">
                確認メールを再送する
            </button>
        </form>
    </div>
    @endif

    @if ($needsEmailRegistration)
    <div class="pf-alert-success" style="background:#fff7ef;color:#7a4f2a;border-color:#e8c8a5;">
        メールアドレスを登録してください。パスワード再設定や連絡に使います。
    </div>
    @endif

    {{-- ══ CARD 1: ユーザー情報 ══════════════════════════ --}}
    <div class="pf-card">
        <div class="pf-user">
            <div class="pf-avatar" aria-hidden="true" style="--avatar-border-color: {{ $avatarBorderColor }}; --avatar-border-width: {{ $avatarBorderWidth }}px;">
                @if ($avatarType === \App\Models\User::AVATAR_PHOTO && $avatarImageUrl)
                    <img src="{{ $avatarImageUrl }}" alt="">
                @elseif ($avatarType === \App\Models\User::AVATAR_EMOJI && $avatarEmoji)
                    <span class="pf-avatar-emoji" style="background: {{ $avatarBgColor }};">{{ $avatarEmoji }}</span>
                @else
                    {{ $initial }}
                @endif
            </div>
            <div class="pf-user-info">
                <p class="pf-user-name">{{ $user->name }}</p>
                <p class="pf-user-email">{{ $user->email }}</p>
                @if ($user->isAdmin())
                <span class="pf-badge pf-badge--admin">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>管理者
                </span>
                @else
                <span class="pf-badge pf-badge--guest">
                    <i class="fa-solid fa-user" aria-hidden="true"></i>ゲスト
                </span>
                @endif
            </div>
        </div>
    </div>

    <div class="pf-card">
        <span class="pf-section-en">Contact</span>
        <h2 class="pf-section-ja">メールアドレス</h2>
        <div class="pf-section-rule"></div>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div style="display:grid;gap:12px;">
                <div>
                    <label for="profile-email" style="display:block;font-size:0.82rem;font-weight:700;color:#7f6a57;margin-bottom:6px;">メールアドレス</label>
                    <input
                        id="profile-email"
                        type="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        required
                        autocomplete="email"
                        placeholder="example@example.com"
                        style="width:100%;padding:12px 14px;border-radius:12px;border:1px solid #e4d6c3;background:#fff;font-size:0.95rem;box-sizing:border-box;"
                    >
                    @error('email')
                        <div class="field-error" style="margin-top:6px;">{{ $message }}</div>
                    @enderror
                </div>
                <div style="color:#8f7d6f;font-size:0.86rem;line-height:1.7;">
                    パスワード再設定や連絡に使います。初回ログイン時に登録しておくと後から困りません。
                </div>
                <div class="pf-actions" style="justify-content:flex-start;">
                    <button type="submit" class="pf-btn pf-btn--primary">
                        <i class="fa-solid fa-envelope"></i>メールを保存
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="pf-card">
        <span class="pf-section-en">Avatar</span>
        <h2 class="pf-section-ja">アイコン設定</h2>
        <div class="pf-section-rule"></div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            @include('partials.avatar-fields', [
                'avatarType' => $avatarType,
                'avatarEmoji' => $avatarEmoji,
                'avatarBgColor' => $avatarBgColor,
                'avatarBorderColor' => $avatarBorderColor,
                'avatarBorderWidth' => $avatarBorderWidth,
                'avatarImageUrl' => $avatarImageUrl,
                'avatarInitial' => $initial,
                'avatarTitle' => 'アイコン設定',
                'avatarDesc' => '写真、絵文字、イニシャルから選べます。'
            ])

            <div class="pf-divider"></div>
            <div class="pf-actions">
                <button type="submit" class="pf-btn pf-btn--primary">
                    <i class="fa-solid fa-image-portrait" aria-hidden="true"></i>アイコンを保存
                </button>
            </div>
        </form>
    </div>

    @if ($user->isAdmin())
    <div class="pf-card">
        <div class="pf-actions">
            <a href="{{ route('admin.dashboard') }}" class="pf-btn pf-btn--primary">
                <i class="fa-solid fa-list-check" aria-hidden="true"></i>管理ダッシュボードへ
            </a>
        </div>
    </div>
    @endif

    @if (!$user->isAdmin() && $profile && $checkInUrl)
    <div class="pf-card">
        <span class="pf-section-en">Check-in QR</span>
        <h2 class="pf-section-ja">受付QR</h2>
        <div class="pf-section-rule"></div>

        <div style="display:grid;gap:14px;justify-items:center;text-align:center;">
            <div style="width:min(100%,320px);padding:16px;border-radius:16px;background:#fcf8f2;border:1px solid #f2e7d8;">
                @if ($checkInQrUrl)
                <img src="{{ $checkInQrUrl }}" alt="受付QRコード" style="width:100%;display:block;border-radius:12px;background:#fff;padding:12px;box-sizing:border-box;box-shadow:inset 0 0 0 1px #f0e3d2;">
                @endif
            </div>
            <div style="max-width:520px;color:#7f6a57;font-size:0.88rem;line-height:1.7;">
                当日はこのQRを受付でかざしてください。
                スマホに保存しておくと、受付がスムーズです。
            </div>
            <div style="width:100%;padding:10px 12px;border-radius:12px;border:1px solid #e4d6c3;background:#fff;color:#6b5847;font-size:.8rem;word-break:break-all;">
                {{ $checkInUrl }}
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;">
                <a href="{{ route('seating.guest') }}" class="pf-btn pf-btn--outline" style="min-width:160px;">
                    <i class="fa-solid fa-chair"></i> 席次表へ
                </a>
                <a href="{{ route('invitation') }}" class="pf-btn pf-btn--primary" style="min-width:160px;">
                    <i class="fa-solid fa-envelope-open-text"></i> 招待状へ
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- ゲストの場合のみ表示 --}}
    @if (!$user->isAdmin())

    {{-- ══ CARD 2: 回答状況 ══════════════════════════════ --}}
    <div class="pf-card">
        <span class="pf-section-en">RSVP Status</span>
        <h2 class="pf-section-ja">回答状況</h2>
        <div class="pf-section-rule"></div>

        @php
            $participation = $profile?->participation ?? 'pending';
            $rsvpLabels = ['attending' => '出席', 'declining' => '欠席', 'pending' => '未回答'];
            $rsvpIcons  = ['attending' => '✦', 'declining' => '✧', 'pending' => '○'];
        @endphp

        <div class="pf-rsvp-banner pf-rsvp-banner--{{ $participation }}">
            <span class="pf-rsvp-icon">{{ $rsvpIcons[$participation] }}</span>
            <div>
                <p class="pf-rsvp-label">{{ $rsvpLabels[$participation] }}</p>
                @if ($profile?->responded_at)
                <p class="pf-rsvp-date">{{ $profile->responded_at->format('Y年m月d日 H:i') }} 回答</p>
                @else
                <p class="pf-rsvp-date">まだ回答が届いていません</p>
                @endif
            </div>
        </div>

        @if ($profile && $profile->participation !== 'pending')

        {{-- ゲスト情報 --}}
        @if ($profile->guest_side || $profile->relationship)
        <dl class="pf-grid" style="margin-bottom: 18px;">
            @if ($profile->guest_side)
            <div class="pf-item">
                <dt>お立場</dt>
                <dd>
                    <div class="pf-tags">
                        <span class="pf-tag">{{ $profile->guestSideLabel() }}</span>
                    </div>
                </dd>
            </div>
            @endif
            @if ($profile->relationship)
            <div class="pf-item">
                <dt>ご関係</dt>
                <dd>
                    <div class="pf-tags">
                        <span class="pf-tag">{{ $profile->relationshipLabel() }}</span>
                    </div>
                </dd>
            </div>
            @endif
        </dl>
        <div class="pf-divider"></div>
        @endif

        {{-- 基本情報 --}}
        <dl class="pf-grid">
            <div class="pf-item">
                <dt>氏名</dt>
                <dd>
                    {{ $profile->fullName() }}
                    @if ($profile->furigana())
                    <br><small style="color:#b0a090;">{{ $profile->furigana() }}</small>
                    @endif
                </dd>
            </div>

            @if ($profile->phone)
            <div class="pf-item">
                <dt>電話番号</dt>
                <dd>{{ $profile->phone }}</dd>
            </div>
            @endif

            @if ($profile->postal_code || $profile->address)
            <div class="pf-item" style="grid-column: 1 / -1;">
                <dt>ご住所</dt>
                <dd>
                    @if ($profile->postal_code)〒{{ $profile->postal_code }}&nbsp;@endif
                    {{ $profile->address }}
                </dd>
            </div>
            @endif

            @if ($profile->isAttending())
            <div class="pf-item">
                <dt>ご出席人数</dt>
                <dd>合計 {{ $profile->attending_count }}名
                    @if ($profile->children_count > 0)
                    （うちお子様 {{ $profile->children_count }}名）
                    @endif
                </dd>
            </div>

            <div class="pf-item">
                <dt>食物アレルギー</dt>
                <dd>
                    @if ($profile->has_allergy)
                        <span style="color:#c0392b; font-weight:600;">あり</span>
                        @if ($profile->allergy_notes)
                        <br><small style="color:#666;">{{ $profile->allergy_notes }}</small>
                        @endif
                    @else
                        なし
                    @endif
                </dd>
            </div>
            @endif

            @if ($profile->notes)
            <div class="pf-item" style="grid-column: 1 / -1;">
                <dt>おふたりへのメッセージ</dt>
                <dd>{{ $profile->notes }}</dd>
            </div>
            @endif
        </dl>

        @endif {{-- profile exists and not pending --}}

        <div class="pf-divider" style="margin-top: 20px;"></div>
        <div class="pf-actions">
            @if ($profile && $profile->participation !== 'pending')
            <a href="{{ route('invitation') }}" class="pf-btn pf-btn--outline">
                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>回答を変更する
            </a>
            @else
            <a href="{{ route('invitation') }}" class="pf-btn pf-btn--primary">
                <i class="fa-solid fa-envelope-open-text" aria-hidden="true"></i>招待状に回答する
            </a>
            @endif
        </div>
    </div>

    {{-- ══ CARD 3: 当日の役割 ════════════════════════════ --}}
    @if ($tasks->isNotEmpty())
    <div class="pf-card">
        <span class="pf-section-en">Your Role</span>
        <h2 class="pf-section-ja">当日のお願い</h2>
        <div class="pf-section-rule"></div>

        @foreach ($tasks as $assignment)
        @if (!$assignment->task) @continue @endif
        <div style="background:#fff;border:1px solid #e8d5b7;border-radius:12px;margin-bottom:14px;overflow:hidden;box-shadow:0 3px 14px rgba(179,139,89,0.08);">
            {{-- ヘッダー --}}
            <div style="display:flex;align-items:center;gap:12px;padding:16px 18px 12px;background:linear-gradient(135deg,#fef9f0,#fff);border-bottom:1px solid #f5efe8;">
                <span style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#c9975b,#e8c98a,#b38b59);display:flex;align-items:center;justify-content:center;color:#fff;font-size:0.85rem;flex-shrink:0;box-shadow:0 3px 10px rgba(179,139,89,0.35);">
                    <i class="fa-solid fa-clipboard-check"></i>
                </span>
                <div>
                    <p style="font-size:1rem;font-weight:600;color:#3d2f25;margin:0 0 3px;font-family:'Playfair Display',serif;">{{ $assignment->task->name }}</p>
                    @if ($assignment->custom_time)
                    <p style="font-size:0.78rem;color:#b38b59;margin:0;display:flex;align-items:center;gap:4px;font-weight:500;">
                        <i class="fa-regular fa-clock"></i>{{ $assignment->custom_time }}
                    </p>
                    @endif
                </div>
            </div>
            {{-- ボディ --}}
            <div style="padding:14px 18px;">
                @if ($assignment->task->programItems->isNotEmpty())
                <p style="font-size:0.62rem;letter-spacing:4px;text-transform:uppercase;color:#b38b59;font-family:'Noto Sans JP',sans-serif;font-weight:700;margin-bottom:10px;">Program</p>
                @foreach ($assignment->task->programItems as $pi)
                <div style="display:flex;align-items:flex-start;gap:10px;padding:6px 0;border-bottom:1px solid #f5efe8;">
                    <span style="font-size:0.76rem;color:#b38b59;width:56px;flex-shrink:0;font-weight:600;padding-top:2px;">{{ $pi->start_time ?? '—' }}</span>
                    <span style="width:7px;height:7px;border-radius:50%;background:#b38b59;flex-shrink:0;margin-top:6px;opacity:0.55;"></span>
                    <div>
                        <p style="font-size:0.88rem;color:#3d2f25;font-weight:500;margin:0 0 2px;">{{ $pi->title }}</p>
                        @if ($pi->description)<p style="font-size:0.78rem;color:#9b8573;line-height:1.6;margin:0;">{{ $pi->description }}</p>@endif
                    </div>
                </div>
                @endforeach
                <div style="margin-bottom:10px;"></div>
                @endif
                @if ($assignment->task->description)
                <p style="font-size:0.86rem;color:#6b5b4e;line-height:1.9;margin:0 0 8px;white-space:pre-line;">{{ $assignment->task->description }}</p>
                @endif
                @if ($assignment->custom_note)
                <p style="font-size:0.82rem;color:#9b8573;background:#fef9f0;border-radius:6px;padding:8px 14px;margin:0;border-left:3px solid #d4b896;">{{ $assignment->custom_note }}</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ══ CARD 4: 式のご案内 ════════════════════════════ --}}
    @if ($setting)
    <div class="pf-card">
        <span class="pf-section-en">Wedding Info</span>
        <h2 class="pf-section-ja">式のご案内</h2>
        <div class="pf-section-rule"></div>

        <dl class="pf-wedding-grid">
            <div>
                <p class="pf-wedding-label">Date</p>
                @if ($setting->ceremony_date)
                <p class="pf-wedding-value">
                    {{ $setting->ceremonyDateJa() }}（{{ $setting->ceremonyDayOfWeek() }}）
                </p>
                <p class="pf-wedding-sub">
                    挙式 {{ $setting->ceremonyTimeFormatted() }} ／ 披露宴 {{ $setting->receptionTimeFormatted() }}〜
                </p>
                @else
                <p class="pf-wedding-value">日程調整中</p>
                @endif
            </div>
            <div>
                <p class="pf-wedding-label">Venue</p>
                <p class="pf-wedding-value">{{ $setting->venue_name }}</p>
                <p class="pf-wedding-sub">{{ $setting->venue_address }}</p>
                @if ($setting->venue_url)
                <a href="{{ $setting->venue_url }}" target="_blank" rel="noopener" class="pf-wedding-map">
                    Google Map →
                </a>
                @endif
            </div>
        </dl>
    </div>
    @endif

    @endif {{-- !isAdmin() --}}

</div>

@endsection
