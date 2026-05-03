@php
    $isAdmin        = Auth::user()?->isAdmin();
    $homeRoute      = $isAdmin ? route('admin.dashboard') : route('dashboard');
    $currentUser    = Auth::user();
    $userName       = $currentUser?->name ?? '';
    $userInitial    = $currentUser?->avatarInitial() ?? (mb_substr($userName, 0, 1, 'UTF-8') ?: '?');
    $userAvatarType = $currentUser?->avatarType() ?? 'initial';
    $userAvatarEmoji = $currentUser?->avatar_emoji;
    $userAvatarBgColor = $currentUser?->avatarBackgroundColor();
    $userAvatarImageUrl = $currentUser?->avatarImageUrl();
    $isAttending    = !$isAdmin && Auth::user()?->guestProfile?->participation === 'attending';
@endphp

{{-- ══ ヘッダー ══════════════════════════════════════════ --}}
<header class="header" id="siteHeader">
    <div class="header__inner">

        {{-- ブランド --}}
        <a href="{{ $homeRoute }}" class="header__brand" aria-label="ホームへ">
            <span class="header__monogram">K <em>&amp;</em> M</span>
            <span class="header__tagline">Wedding 2026</span>
        </a>

        {{-- デスクトップ ナビ --}}
        <nav class="header__nav" aria-label="メインナビゲーション">
            <ul>
                @if ($isAdmin)
                {{-- ゲスト関連: ドロップダウン --}}
                <li class="header__nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                       class="{{ request()->routeIs('admin.dashboard','admin.rsvp','admin.users*','admin.login-history*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users" aria-hidden="true"></i>ゲスト
                        <i class="fa-solid fa-chevron-down dd-arrow" aria-hidden="true"></i>
                    </a>
                    <ul class="header__dropdown">
                        <li>
                            <a href="{{ route('admin.dashboard') }}"
                               class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <i class="fa-solid fa-list-check"></i>ゲスト一覧
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.rsvp') }}"
                               class="{{ request()->routeIs('admin.rsvp*') ? 'active' : '' }}">
                                <i class="fa-solid fa-envelope-open-text"></i>回答状況
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.users') }}"
                               class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                                <i class="fa-solid fa-user-pen"></i>ユーザー管理
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.login-history') }}"
                               class="{{ request()->routeIs('admin.login-history*') ? 'active' : '' }}">
                                <i class="fa-solid fa-clock-rotate-left"></i>ログイン履歴
                            </a>
                        </li>
                    </ul>
                </li>
                {{-- 席次表: 単独リンク --}}
                <li>
                    <a href="{{ route('admin.seating') }}"
                       class="{{ request()->routeIs('admin.seating*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chair" aria-hidden="true"></i>席次表
                    </a>
                </li>
                {{-- コンテンツ: ドロップダウン --}}
                <li class="header__nav-item">
                    <a href="{{ route('admin.program') }}"
                       class="{{ request()->routeIs('admin.program*','admin.faq*','admin.profiles','admin.news*','admin.tasks*') ? 'active' : '' }}">
                        <i class="fa-solid fa-file-lines" aria-hidden="true"></i>コンテンツ
                        <i class="fa-solid fa-chevron-down dd-arrow" aria-hidden="true"></i>
                    </a>
                    <ul class="header__dropdown">
                        <li>
                            <a href="{{ route('admin.news') }}"
                               class="{{ request()->routeIs('admin.news*') ? 'active' : '' }}">
                                <i class="fa-solid fa-bullhorn"></i>お知らせ
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.profiles') }}"
                               class="{{ request()->routeIs('admin.profiles') ? 'active' : '' }}">
                                <i class="fa-solid fa-heart"></i>プロフィール
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.tasks') }}"
                               class="{{ request()->routeIs('admin.tasks*') ? 'active' : '' }}">
                                <i class="fa-solid fa-clipboard-list"></i>当日の役割
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.program') }}"
                               class="{{ request()->routeIs('admin.program*') ? 'active' : '' }}">
                                <i class="fa-solid fa-list-ol"></i>プログラム
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.faq') }}"
                               class="{{ request()->routeIs('admin.faq*') ? 'active' : '' }}">
                                <i class="fa-solid fa-circle-question"></i>Q&amp;A
                            </a>
                        </li>
                    </ul>
                </li>
                {{-- 設定: 単独リンク --}}
                <li>
                    <a href="{{ route('admin.settings') }}"
                       class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                        <i class="fa-solid fa-gear" aria-hidden="true"></i>設定
                    </a>
                </li>
                @else
                {{-- ゲスト --}}
                <li>
                    <a href="{{ route('dashboard') }}"
                       class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-house" aria-hidden="true"></i>ホーム
                    </a>
                </li>
                <li>
                    <a href="{{ route('invitation') }}"
                       class="{{ request()->routeIs('invitation') ? 'active' : '' }}">
                        <i class="fa-solid fa-envelope-open-text" aria-hidden="true"></i>招待状
                    </a>
                </li>
                <li>
                    <a href="{{ route('profiles.index') }}"
                       class="{{ request()->routeIs('profiles.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-heart" aria-hidden="true"></i>プロフィール
                    </a>
                </li>
                {{-- 式について ドロップダウン --}}
                <li class="header__nav-item">
                    <a href="{{ route('program') }}"
                       class="{{ request()->routeIs('program','access','faq') ? 'active' : '' }}">
                        <i class="fa-solid fa-list-ol" aria-hidden="true"></i>式について
                        <i class="fa-solid fa-chevron-down dd-arrow" aria-hidden="true"></i>
                    </a>
                    <ul class="header__dropdown">
                        <li>
                            <a href="{{ route('program') }}"
                               class="{{ request()->routeIs('program') ? 'active' : '' }}">
                                <i class="fa-solid fa-list-ol"></i>プログラム
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('access') }}"
                               class="{{ request()->routeIs('access') ? 'active' : '' }}">
                                <i class="fa-solid fa-map-location-dot"></i>アクセス
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('faq') }}"
                               class="{{ request()->routeIs('faq') ? 'active' : '' }}">
                                <i class="fa-solid fa-circle-question"></i>Q&amp;A
                            </a>
                        </li>
                    </ul>
                </li>
                @if ($isAttending)
                <li>
                    <a href="{{ route('seating.guest') }}"
                       class="{{ request()->routeIs('seating.guest') ? 'active' : '' }}">
                        <i class="fa-solid fa-chair" aria-hidden="true"></i>席次表
                    </a>
                </li>
                @endif
                @endif
            </ul>
        </nav>

        {{-- デスクトップ アクション --}}
        <div class="header__actions">
            {{-- アバター（プロフィールリンク）--}}
            <div class="header__user" id="headerUser">
                <a href="{{ route('profile.edit') }}"
                   class="header__avatar {{ request()->routeIs('profile.*') ? 'header__avatar--active' : '' }}"
                   @if ($userAvatarType === 'emoji') style="background: {{ $userAvatarBgColor }};" @endif
                   aria-label="{{ $userName }} のプロフィール"
                   aria-haspopup="false">
                    @if ($userAvatarType === 'photo' && $userAvatarImageUrl)
                        <img src="{{ $userAvatarImageUrl }}" alt="">
                    @elseif ($userAvatarType === 'emoji' && $userAvatarEmoji)
                        <span class="header__avatar-emoji" aria-hidden="true">{{ $userAvatarEmoji }}</span>
                    @else
                        {{ $userInitial }}
                    @endif
                </a>
                {{-- 将来的なドロップダウンスロット --}}
            </div>

            {{-- ログアウト --}}
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="header__logout">
                    <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                    ログアウト
                </button>
            </form>
        </div>

        {{-- モバイル ハンバーガー --}}
        <button class="header__burger"
                aria-label="メニューを開く"
                aria-expanded="false"
                aria-controls="headerDrawer">
            <span class="header__burger-bar"></span>
            <span class="header__burger-bar"></span>
            <span class="header__burger-bar"></span>
        </button>

    </div>
</header>

{{-- ══ モバイルドロワー ══════════════════════════════════ --}}
<div class="header-drawer" id="headerDrawer" aria-hidden="true" role="dialog" aria-label="ナビゲーション">

    {{-- ドロワー ユーザー情報 --}}
    <a href="{{ route('profile.edit') }}" class="header-drawer__user">
        <div class="header-drawer__avatar" @if ($userAvatarType === 'emoji') style="background: {{ $userAvatarBgColor }};" @endif>
            @if ($userAvatarType === 'photo' && $userAvatarImageUrl)
                <img src="{{ $userAvatarImageUrl }}" alt="">
            @elseif ($userAvatarType === 'emoji' && $userAvatarEmoji)
                <span class="header-drawer__avatar-emoji" aria-hidden="true">{{ $userAvatarEmoji }}</span>
            @else
                {{ $userInitial }}
            @endif
        </div>
        <div class="header-drawer__user-info">
            <p class="header-drawer__user-name">{{ $userName }}</p>
            <p class="header-drawer__user-role">{{ $isAdmin ? '管理者' : 'ゲスト' }}</p>
        </div>
        <i class="fa-solid fa-chevron-right header-drawer__user-chevron" aria-hidden="true"></i>
    </a>

    <nav class="header-drawer__nav" aria-label="モバイルナビゲーション">
        <ul>
            @if ($isAdmin)
            <li>
                <a href="{{ route('admin.dashboard') }}"
                   class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-check" aria-hidden="true"></i>ゲスト一覧
                </a>
            </li>
            <li>
                <a href="{{ route('admin.rsvp') }}"
                   class="{{ request()->routeIs('admin.rsvp*') ? 'active' : '' }}">
                    <i class="fa-solid fa-envelope-open-text" aria-hidden="true"></i>回答状況
                </a>
            </li>
            <li>
                <a href="{{ route('admin.users') }}"
                   class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users" aria-hidden="true"></i>ユーザー管理
                </a>
            </li>
            <li>
                <a href="{{ route('admin.seating') }}"
                   class="{{ request()->routeIs('admin.seating*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chair" aria-hidden="true"></i>席次表
                </a>
            </li>
            <li>
                <a href="{{ route('admin.news') }}"
                   class="{{ request()->routeIs('admin.news*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>お知らせ
                </a>
            </li>
            <li>
                <a href="{{ route('admin.profiles') }}"
                   class="{{ request()->routeIs('admin.profiles') ? 'active' : '' }}">
                    <i class="fa-solid fa-heart" aria-hidden="true"></i>プロフィール
                </a>
            </li>
            <li>
                <a href="{{ route('admin.tasks') }}"
                   class="{{ request()->routeIs('admin.tasks*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-list" aria-hidden="true"></i>当日の役割
                </a>
            </li>
            <li>
                <a href="{{ route('admin.program') }}"
                   class="{{ request()->routeIs('admin.program*') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-ol" aria-hidden="true"></i>プログラム
                </a>
            </li>
            <li>
                <a href="{{ route('admin.faq') }}"
                   class="{{ request()->routeIs('admin.faq*') ? 'active' : '' }}">
                    <i class="fa-solid fa-circle-question" aria-hidden="true"></i>Q&amp;A
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settings') }}"
                   class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <i class="fa-solid fa-gear" aria-hidden="true"></i>設定
                </a>
            </li>
            <li>
                <a href="{{ route('admin.login-history') }}"
                   class="{{ request()->routeIs('admin.login-history*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>ログイン履歴
                </a>
            </li>
            @else
            <li>
                <a href="{{ route('dashboard') }}"
                   class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-house" aria-hidden="true"></i>ホーム
                </a>
            </li>
            <li>
                <a href="{{ route('invitation') }}"
                   class="{{ request()->routeIs('invitation') ? 'active' : '' }}">
                    <i class="fa-solid fa-envelope-open-text" aria-hidden="true"></i>招待状
                </a>
            </li>
            <li>
                <a href="{{ route('profiles.index') }}"
                   class="{{ request()->routeIs('profiles.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-heart" aria-hidden="true"></i>プロフィール
                </a>
            </li>
            <li class="header-drawer__section-label">式について</li>
            <li>
                <a href="{{ route('program') }}"
                   class="{{ request()->routeIs('program') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-ol" aria-hidden="true"></i>プログラム
                </a>
            </li>
            <li>
                <a href="{{ route('access') }}"
                   class="{{ request()->routeIs('access') ? 'active' : '' }}">
                    <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>アクセス
                </a>
            </li>
            <li>
                <a href="{{ route('faq') }}"
                   class="{{ request()->routeIs('faq') ? 'active' : '' }}">
                    <i class="fa-solid fa-circle-question" aria-hidden="true"></i>Q&amp;A
                </a>
            </li>
            @if ($isAttending)
            <li>
                <a href="{{ route('seating.guest') }}"
                   class="{{ request()->routeIs('seating.guest') ? 'active' : '' }}">
                    <i class="fa-solid fa-chair" aria-hidden="true"></i>席次表
                </a>
            </li>
            @endif
            @endif
        </ul>
    </nav>

    <div class="header-drawer__footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="header-drawer__logout">
                <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>ログアウト
            </button>
        </form>
    </div>

</div>

{{-- オーバーレイ --}}
<div class="header-overlay" id="headerOverlay" aria-hidden="true"></div>
