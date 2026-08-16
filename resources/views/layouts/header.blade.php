@php
    $isAdmin        = Auth::user()?->isAdmin();
    $homeRoute      = $isAdmin ? route('admin.dashboard') : route('dashboard');
    $currentUser    = Auth::user();
    $userName       = $currentUser?->name ?? '';
    $userInitial    = $currentUser?->avatarInitial() ?? (mb_substr($userName, 0, 1, 'UTF-8') ?: '?');
    $userAvatarType = $currentUser?->avatarType() ?? 'initial';
    $userAvatarEmoji = $currentUser?->avatar_emoji;
    $userAvatarBgColor = $currentUser?->avatarBackgroundColor();
    $userAvatarBorderColor = $currentUser?->avatarBorderColor() ?? '#f0e4d0';
    $userAvatarBorderWidth = $currentUser?->avatarBorderWidth() ?? 3;
    $userAvatarImageUrl = $currentUser?->avatarImageUrl();
    $isAttending    = !$isAdmin && Auth::user()?->guestProfile?->participation === 'attending';
    $isProfileBookPublic = \App\Models\WeddingSetting::first()?->is_profile_book_public ?? false;
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
                @if (!$isAdmin)
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
                <li>
                    <a href="{{ route('program') }}"
                       class="{{ request()->routeIs('program') ? 'active' : '' }}">
                        <i class="fa-solid fa-list-ol" aria-hidden="true"></i>プログラム
                    </a>
                </li>
                {{-- ギャラリー・お知らせ --}}
                <li class="header__nav-item">
                    <a href="{{ route('gallery') }}"
                       class="{{ request()->routeIs('gallery','gallery.upload','people.*','news.index','profile-book') ? 'active' : '' }}">
                        <i class="fa-solid fa-heart" aria-hidden="true"></i>楽しむ
                        <i class="fa-solid fa-chevron-down dd-arrow" aria-hidden="true"></i>
                    </a>
                    <ul class="header__dropdown">
                        <li>
                            <a href="{{ route('gallery') }}"
                               class="{{ request()->routeIs('gallery') ? 'active' : '' }}">
                                <i class="fa-solid fa-images"></i>ギャラリー
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('gallery.upload') }}"
                               class="{{ request()->routeIs('gallery.upload') ? 'active' : '' }}">
                                <i class="fa-solid fa-camera"></i>写真を投稿
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('people.index') }}"
                               class="{{ request()->routeIs('people.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-users"></i>参加者一覧
                            </a>
                        </li>
                        @if ($isProfileBookPublic)
                        <li>
                            <a href="{{ route('profile-book') }}"
                               class="{{ request()->routeIs('profile-book') ? 'active' : '' }}">
                                <i class="fa-solid fa-book-open"></i>プロフィールブック
                            </a>
                        </li>
                        @endif
                        <li>
                            <a href="{{ route('news.index') }}"
                               class="{{ request()->routeIs('news.index') ? 'active' : '' }}">
                                <i class="fa-solid fa-bullhorn"></i>お知らせ一覧
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
                   style="--avatar-border-color: {{ $userAvatarBorderColor }}; --avatar-border-width: {{ $userAvatarBorderWidth }}px; @if ($userAvatarType === 'emoji') background: {{ $userAvatarBgColor }}; @endif"
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
        <div class="header-drawer__avatar" style="--avatar-border-color: {{ $userAvatarBorderColor }}; --avatar-border-width: {{ $userAvatarBorderWidth }}px; @if ($userAvatarType === 'emoji') background: {{ $userAvatarBgColor }}; @endif">
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
            <li class="header-drawer__section-label">運営</li>
            <li>
                <a href="{{ route('admin.ops') }}"
                   class="{{ request()->routeIs('admin.ops*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line" aria-hidden="true"></i>運営ダッシュボード
                </a>
            </li>
            <li>
                <a href="{{ route('admin.login-history') }}"
                   class="{{ request()->routeIs('admin.login-history*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>ログイン履歴
                </a>
            </li>
            <li>
                <a href="{{ route('admin.audit.email') }}"
                   class="{{ request()->routeIs('admin.audit.email*') ? 'active' : '' }}">
                    <i class="fa-solid fa-envelope-circle-check" aria-hidden="true"></i>メール操作ログ
                </a>
            </li>
            <li class="header-drawer__section-label">受付</li>
            <li>
                <a href="{{ route('admin.checkin.index') }}"
                   class="{{ request()->routeIs('admin.checkin*') ? 'active' : '' }}">
                    <i class="fa-solid fa-qrcode" aria-hidden="true"></i>受付チェックイン
                </a>
            </li>
            <li>
                <a href="{{ route('admin.checkin.guests') }}"
                   class="{{ request()->routeIs('admin.checkin.guests') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-user" aria-hidden="true"></i>受付一覧
                </a>
            </li>
            <li>
                <a href="{{ route('admin.audit.checkin') }}"
                   class="{{ request()->routeIs('admin.audit.checkin*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-list" aria-hidden="true"></i>操作ログ
                </a>
            </li>
            <li class="header-drawer__section-label">ゲスト</li>
            <li>
                <a href="{{ route('admin.dashboard') }}"
                   class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-check" aria-hidden="true"></i>ゲスト一覧
                </a>
            </li>
            <li>
                <a href="{{ route('admin.users') }}"
                   class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-pen" aria-hidden="true"></i>ユーザー管理
                </a>
            </li>
            <li>
                <a href="{{ route('admin.seating') }}"
                   class="{{ request()->routeIs('admin.seating*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chair" aria-hidden="true"></i>席次表
                </a>
            </li>
            <li class="header-drawer__section-label">コンテンツ</li>
            <li>
                <a href="{{ route('admin.news') }}"
                   class="{{ request()->routeIs('admin.news*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>お知らせ
                </a>
            </li>
            <li>
                <a href="{{ route('admin.gallery') }}"
                   class="{{ request()->routeIs('admin.gallery*') ? 'active' : '' }}">
                    <i class="fa-solid fa-images" aria-hidden="true"></i>ギャラリー管理
                </a>
            </li>
            <li>
                <a href="{{ route('admin.guestbook') }}"
                   class="{{ request()->routeIs('admin.guestbook*') ? 'active' : '' }}">
                    <i class="fa-solid fa-comment-dots" aria-hidden="true"></i>ゲストブック管理
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
                <a href="{{ route('admin.reminders') }}"
                   class="{{ request()->routeIs('admin.reminders*') ? 'active' : '' }}">
                    <i class="fa-solid fa-envelope-open-text" aria-hidden="true"></i>リマインダーメール
                </a>
            </li>
            <li>
                <a href="{{ route('admin.media') }}"
                   class="{{ request()->routeIs('admin.media*') ? 'active' : '' }}">
                    <i class="fa-solid fa-photo-film" aria-hidden="true"></i>メディア
                </a>
            </li>
            <li>
                <a href="{{ route('admin.profile-book') }}"
                   class="{{ request()->routeIs('admin.profile-book*') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-open" aria-hidden="true"></i>プロフィールブック
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settings') }}"
                   class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <i class="fa-solid fa-gear" aria-hidden="true"></i>設定
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
            <li>
                <a href="{{ route('program') }}"
                   class="{{ request()->routeIs('program') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-ol" aria-hidden="true"></i>プログラム
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
            <li class="header-drawer__section-label">楽しむ</li>
            <li>
                <a href="{{ route('gallery') }}"
                   class="{{ request()->routeIs('gallery') ? 'active' : '' }}">
                    <i class="fa-solid fa-images" aria-hidden="true"></i>ギャラリー
                </a>
            </li>
            <li>
                <a href="{{ route('gallery.upload') }}"
                   class="{{ request()->routeIs('gallery.upload') ? 'active' : '' }}">
                    <i class="fa-solid fa-camera" aria-hidden="true"></i>写真を投稿
                </a>
            </li>
            <li>
                <a href="{{ route('people.index') }}"
                   class="{{ request()->routeIs('people.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users" aria-hidden="true"></i>参加者一覧
                </a>
            </li>
            @if ($isProfileBookPublic)
            <li>
                <a href="{{ route('profile-book') }}"
                   class="{{ request()->routeIs('profile-book') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-open" aria-hidden="true"></i>プロフィールブック
                </a>
            </li>
            @endif
            <li>
                <a href="{{ route('news.index') }}"
                   class="{{ request()->routeIs('news.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>お知らせ一覧
                </a>
            </li>
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
