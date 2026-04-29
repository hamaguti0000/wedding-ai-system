@php
    $isAdmin        = Auth::user()?->isAdmin();
    $homeRoute      = $isAdmin ? route('admin.dashboard') : route('dashboard');
    $userName       = Auth::user()?->name ?? '';
    $userInitial    = mb_substr($userName, 0, 1, 'UTF-8') ?: '?';
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
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                       class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-list-check" aria-hidden="true"></i>RSVP管理
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.seating') }}"
                       class="{{ request()->routeIs('admin.seating*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chair" aria-hidden="true"></i>席次表
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.users') }}"
                       class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                        <i class="fa-solid fa-user-plus" aria-hidden="true"></i>ユーザー管理
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.settings') }}"
                       class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                        <i class="fa-solid fa-gear" aria-hidden="true"></i>式の情報
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
                   aria-label="{{ $userName }} のプロフィール"
                   aria-haspopup="false">
                    {{ $userInitial }}
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
        <div class="header-drawer__avatar">{{ $userInitial }}</div>
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
                    <i class="fa-solid fa-list-check" aria-hidden="true"></i>RSVP管理
                </a>
            </li>
            <li>
                <a href="{{ route('admin.users') }}"
                   class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-plus" aria-hidden="true"></i>ユーザー管理
                </a>
            </li>
            <li>
                <a href="{{ route('admin.seating') }}"
                   class="{{ request()->routeIs('admin.seating*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chair" aria-hidden="true"></i>席次表
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settings') }}"
                   class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <i class="fa-solid fa-gear" aria-hidden="true"></i>式の情報
                </a>
            </li>
            {{-- 管理者用ログアウト（ナビ内に直接配置） --}}
            <li class="header-drawer__nav-logout">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit">
                        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>ログアウト
                    </button>
                </form>
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
            @if ($isAttending)
            <li>
                <a href="{{ route('seating.guest') }}"
                   class="{{ request()->routeIs('seating.guest') ? 'active' : '' }}">
                    <i class="fa-solid fa-chair" aria-hidden="true"></i>席次表
                </a>
            </li>
            @endif
            {{-- ゲスト用ログアウト（ナビ内に直接配置） --}}
            <li class="header-drawer__nav-logout">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit">
                        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>ログアウト
                    </button>
                </form>
            </li>
            @endif
        </ul>
    </nav>


</div>

{{-- オーバーレイ --}}
<div class="header-overlay" id="headerOverlay" aria-hidden="true"></div>
