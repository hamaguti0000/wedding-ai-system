<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '結婚式招待ページ | Kakeru＆Mirai')</title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ css_asset('css/design-system.css') }}">
    <link rel="stylesheet" href="{{ css_asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ css_asset('css/common.css') }}">
    @if (Auth::user()?->isAdmin())
    <link rel="stylesheet" href="{{ css_asset('css/admin.css') }}">
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @stack('styles')
</head>

@php
    $__isAdmin = Auth::user()?->isAdmin();
@endphp
@php
    $__impersonating = session()->has(\App\Http\Controllers\ImpersonationController::SESSION_KEY);
@endphp
<body @class(['has-admin-sidebar' => $__isAdmin, 'is-impersonating' => $__impersonating])>

    {{--
      代理ログイン中は「今は他人の画面を見ている」ことが一目で分かるようにする。
      これが無いと、管理者が自分の画面と取り違えて操作してしまう恐れがある。
    --}}
    @if ($__impersonating)
    <div class="impersonation-bar">
        <span class="impersonation-bar__text">
            <i class="fa-solid fa-user-secret"></i>
            <strong>{{ Auth::user()->name }}</strong> として表示中（管理者による確認モード）
        </span>
        <form method="POST" action="{{ route('impersonate.stop') }}">
            @csrf
            <button type="submit" class="impersonation-bar__btn">管理者に戻る</button>
        </form>
    </div>
    @endif

    @include('layouts.header')

    @if ($__isAdmin)
        @include('layouts.admin-sidebar')
    @endif

    <main>
        @yield('content')
    </main>

    @include('layouts.footer')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/gsap.min.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>

    @stack('scripts')

    @php
        $__cdVisible = Auth::check() && !Auth::user()->isAdmin();
        $__cdDate    = $__cdVisible ? \App\Models\WeddingSetting::value('ceremony_date') : null;
    @endphp
    @if ($__cdDate)
    <div class="countdown" id="countdownWidget">
        <span class="countdown__gem">✦</span>
        <span class="countdown__text">
            <span id="countdownDays" class="countdown__days"></span><span class="countdown__label" id="countdownLabel"></span>
        </span>
        <span class="countdown__close">✕</span>
    </div>
    <script>
    (function () {
        const widget = document.getElementById('countdownWidget');
        const daysEl = document.getElementById('countdownDays');
        const lblEl  = document.getElementById('countdownLabel');
        const target = new Date('{{ \Carbon\Carbon::parse($__cdDate)->format('Y-m-d') }}T00:00:00+09:00');

        function update() {
            const diff = target - new Date();
            if (diff <= 0) { widget.classList.remove('is-visible'); return; }
            const days = Math.floor(diff / 86400000);
            if (days === 0) {
                daysEl.textContent = '本日';
                lblEl.textContent  = ' 挙式当日';
            } else {
                daysEl.textContent = days;
                lblEl.textContent  = ' 日後';
            }
        }

        update();
        setInterval(update, 60000);

        if (!sessionStorage.getItem('cdDismissed')) {
            setTimeout(() => widget.classList.add('is-visible'), 1400);
        }
        widget.addEventListener('click', () => {
            widget.classList.remove('is-visible');
            sessionStorage.setItem('cdDismissed', '1');
        });
    })();
    </script>
    @endif

</body>

</html>
