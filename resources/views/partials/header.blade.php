<header class="header">
    <div class="header-container">
        <a href="{{ route('profile') }}" class="logo">
            <i class="fa-solid fa-user-circle"></i> プロフィール
        </a>
        <nav>
            <ul>
                <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="fa-solid fa-house"></i> ホーム</a></li>
                <li><a href="{{ route('menu') }}"><i class="fa-solid fa-utensils"></i> お食事</a></li>
                <li><a href="{{ route('invitation') }}"><i class="fa-solid fa-envelope-open-text"></i> 招待状</a></li>
            </ul>
        </nav>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="logout-button">
            <i class="fa-solid fa-right-from-bracket"></i> ログアウト
        </a>
    </div>
</header>