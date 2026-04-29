<header class="header">
    <div class="header-container">

        <!-- プロフィール -->
        <a href="{{ route('profile.edit') }}" class="logo">
            <i class="fa-solid fa-user-circle"></i> プロフィール
        </a>

        <!-- ナビ -->
        <nav>
            <ul>
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-house"></i> ホーム
                    </a>
                </li>

                <li>
                    <a href="{{ route('menu') }}"
                        class="{{ request()->routeIs('menu') ? 'active' : '' }}">
                        <i class="fa-solid fa-utensils"></i> お食事
                    </a>
                </li>

                <li>
                    <a href="{{ route('invitation') }}"
                        class="{{ request()->routeIs('invitation') ? 'active' : '' }}">
                        <i class="fa-solid fa-envelope-open-text"></i> 招待状
                    </a>
                </li>
            </ul>
        </nav>

        <!-- ログアウト -->
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
            @csrf
        </form>

        <a href="#"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
            class="logout-button">
            <i class="fa-solid fa-right-from-bracket"></i> ログアウト
        </a>

    </div>
</header>